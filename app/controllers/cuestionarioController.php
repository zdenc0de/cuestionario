<?php
/**
 * Plantilla general de controladores
 * @version 1.1.0
 *
 * Controlador de cuestionario
 *
 * Módulo PÚBLICO (encuestado): acceso por token, flujo del cuestionario y
 * envío de respuestas. No requiere autenticación de Bee (Auth::validate()),
 * el "acceso" del encuestado se controla íntegramente por la vigencia del
 * token (tokenModel).
 *
 * DISEÑO — cómo se liga responder()/post_responder() a UNA aplicación en
 * curso sin cuenta de Bee: el token es multiuso (cualquier encuestado del
 * centro de trabajo puede capturarlo), así que no basta con el token de la
 * URL para saber "de quién" es la aplicación en curso. post_acceso() crea la
 * fila `aplicacion` y guarda su id en la sesión NATIVA de PHP (no Bee/Auth;
 * la sesión ya está activa para toda petición, ver Bee::init_set_up() ->
 * session_start()) bajo la llave $_SESSION['cuestionario']. Cada paso
 * siguiente revalida esa sesión contra el token de la URL — ver
 * aplicacionEnCurso().
 *
 * Rutas (ejemplos):
 *   /cuestionario                    -> index()       formulario de acceso por token
 *   /cuestionario/post_acceso        -> post_acceso()  valida el token capturado
 *   /cuestionario/responder/{token}  -> responder()    flujo de preguntas
 *   /cuestionario/post_responder     -> post_responder() envío final de respuestas
 *   /cuestionario/gracias            -> gracias()      confirmación de envío
 *
 * @see docs/ARQUITECTURA.md
 * @see docs/HANDOFF_DESARROLLO.md
 */
class cuestionarioController extends Controller implements ControllerInterface
{
  function __construct()
  {
    // Público: NO se valida sesión de Bee (Auth::validate()) en este controlador,
    // el control de acceso es por token de aplicación (tokenModel::esta_vigente()).

    // Ejecutar la funcionalidad del Controller padre
    parent::__construct();
  }

  /**
   * Formulario de acceso: captura el token y datos de identificación
   * mínimos del encuestado (RF-03: nombre y número de servidor público)
   */
  function index()
  {
    $this->setTitle('Acceso al cuestionario');
    $this->setView('acceso'); // templates/views/cuestionario/accesoView.php
    $this->render();
  }

  /**
   * Valida el token capturado y crea la aplicación en curso (RF-11).
   *
   * Orden de validación: token existe+activo+vigente (tokenModel::esta_vigente())
   * -> el centro de trabajo ligado al token sí requiere cuestionario (guard de
   * defensa en profundidad, ver docblock de guiaModel::por_numero_trabajadores();
   * en condiciones normales nunca dispara porque
   * administradorController::post_centros_trabajo() ya impide crear centros
   * de ≤15 trabajadores) -> unicidad (aplicacionModel::existe_para_token_y_servidor_publico()).
   *
   * Seguridad: valida verbo POST (Bee no lo hace por sí solo) y el token CSRF
   * nativo de Bee (clase Csrf, CSRF_TOKEN), tal cual el resto del sistema —
   * ver insert_inputs() en accesoView.php, que lo agrega como campo oculto.
   */
  function post_acceso()
  {
    // Bee no distingue verbos HTTP: rechaza cualquier acceso que no sea POST
    requiere_metodo_post();

    try {
      if (!Csrf::validate($_POST['csrf'] ?? '')) {
        throw new Exception(get_bee_message(0));
      }

      if (!check_posted_data(['token', 'nombre', 'numero_servidor_publico'], $_POST)) {
        throw new Exception('Por favor completa el formulario.');
      }

      array_map('sanitize_input', $_POST);

      $codigo                = trim($_POST['token']);
      $nombre                = trim($_POST['nombre']);
      $numeroServidorPublico = trim($_POST['numero_servidor_publico']);

      if ($codigo === '' || $nombre === '' || $numeroServidorPublico === '') {
        throw new Exception('Por favor completa el formulario.');
      }

      // RF-11: el token debe existir, estar activo y vigente
      if (!tokenModel::esta_vigente($codigo)) {
        throw new Exception('El token no es válido, está inactivo o ya venció. Verifica con tu administrador.');
      }

      $token  = tokenModel::by_codigo($codigo);
      $centro = centroTrabajoModel::by_id($token['centro_trabajo_id']);

      if (empty($centro)) {
        throw new Exception('El token no está ligado a un centro de trabajo válido.');
      }

      // Defensa en profundidad (guard NOM-035): un centro de ≤15 trabajadores
      // no debería tener token vigente porque
      // administradorController::post_centros_trabajo() ya lo impide; si de
      // todos modos ocurriera (ej. datos cargados directo a la base de
      // datos), no se continúa con el flujo del cuestionario.
      $guia = guiaModel::por_numero_trabajadores((int) $centro['num_trabajadores']);
      if ($guia === null) {
        throw new Exception('Los centros de trabajo de hasta 15 trabajadores no requieren la aplicación de este cuestionario conforme a la NOM-035.');
      }

      // RF-11 (unicidad de negocio): una sola aplicación por combinación
      // (token_id, numero_servidor_publico), sin importar si quedó
      // 'en_progreso' o 'completada' — una vez capturado el acceso, no se
      // vuelve a permitir (mismo criterio que pide el handoff, no se diseña
      // un flujo de "reanudar" que no fue solicitado).
      if (aplicacionModel::existe_para_token_y_servidor_publico($token['id'], $numeroServidorPublico)) {
        throw new Exception('Ya respondiste esta campaña con ese número de servidor público.');
      }

      $aplicacionId = aplicacionModel::insertOne([
        'token_id'                => $token['id'],
        'guia_id'                 => $guia['id'],
        'nombre'                  => $nombre,
        'numero_servidor_publico' => $numeroServidorPublico
      ]);

      if (!$aplicacionId) {
        throw new Exception('Hubo un problema al iniciar tu cuestionario. Intenta de nuevo.');
      }

      // Ver docblock de la clase: liga las siguientes peticiones a ESTA
      // aplicación sin depender de una cuenta de Bee.
      $_SESSION['cuestionario'] =
      [
        'aplicacion_id' => $aplicacionId,
        'token_codigo'  => $codigo
      ];

      Redirect::to('cuestionario/responder/' . $codigo);

    } catch (Exception $e) {
      Flasher::error($e->getMessage());
      Redirect::back();
    }
  }

  /**
   * Flujo de preguntas del cuestionario correspondiente (Guía II o III):
   * reactivos obligatorios (RF-01, escala Likert de 5 opciones) más los
   * reactivos condicionales de las dos preguntas-filtro (RF-02), que la
   * vista muestra/oculta con JS según la respuesta capturada en el momento
   * (0/1) — los que queden ocultos NO se marcan `required`, así que
   * post_responder() es quien realmente decide qué se registra.
   *
   * @param string $token
   */
  function responder($token = null)
  {
    $aplicacion = $this->aplicacionEnCurso($token);
    $guia       = guiaModel::by_id($aplicacion['guia_id']);

    $filtros = [];
    foreach (preguntaFiltroModel::por_guia($aplicacion['guia_id']) as $filtro) {
      $filtros[] =
      [
        'pregunta'  => $filtro,
        'reactivos' => reactivoModel::condicionales_por_filtro_con_categoria($filtro['id'])
      ];
    }

    $this->setTitle('Cuestionario NOM-035');
    $this->addToData('token', $token);
    $this->addToData('guia_nombre', $guia['nombre'] ?? null);
    $this->addToData('reactivos', reactivoModel::obligatorios_por_guia_con_categoria($aplicacion['guia_id']));
    $this->addToData('filtros', $filtros); // [0] = clientes (orden 1), [1] = jefe (orden 2), ver preguntaFiltroModel
    $this->addToData('opciones', opcionRespuestaModel::escala());
    $this->setView('cuestionario'); // templates/views/cuestionario/cuestionarioView.php
    $this->render();
  }

  /**
   * Recibe y persiste las respuestas del encuestado (RF-04, RF-05): valida
   * que TODOS los reactivos aplicables (obligatorios + condicionales
   * habilitados por las preguntas-filtro) tengan una opción válida
   * seleccionada, guarda las respuestas, marca la aplicación como
   * 'completada' y dispara el cálculo del resultado en el mismo momento
   * (RNF-06, resultadoModel::calcular_para_aplicacion(), motor ya
   * implementado — ver docs/HANDOFF_DESARROLLO.md §5).
   *
   * Seguridad: valida verbo POST y el token CSRF nativo de Bee, ver
   * post_acceso() arriba y insert_inputs() en cuestionarioView.php.
   *
   * Nota sobre sanitize_input(): a propósito NO se usa el
   * `array_map('sanitize_input', $_POST)` del resto del sistema aquí —
   * $_POST['respuestas'] es un arreglo anidado (id de reactivo => id de
   * opción) y sanitize_input()/trim() espera un string; aplicarlo a un
   * arreglo produce un TypeError fatal en PHP 8.2. Los campos escalares que
   * sí se usan (token, atiende_clientes, es_jefe) se validan explícitamente
   * abajo, y cada id de `respuestas` se castea a (int) antes de usarse.
   */
  function post_responder()
  {
    // Bee no distingue verbos HTTP: rechaza cualquier acceso que no sea POST
    requiere_metodo_post();

    try {
      if (!Csrf::validate($_POST['csrf'] ?? '')) {
        throw new Exception(get_bee_message(0));
      }

      $token      = trim($_POST['token'] ?? '');
      $aplicacion = $this->aplicacionEnCurso($token);

      if (!check_posted_data(['atiende_clientes', 'es_jefe', 'respuestas'], $_POST)) {
        throw new Exception('Faltan respuestas por completar.');
      }

      if (!in_array($_POST['atiende_clientes'], ['0', '1'], true) || !in_array($_POST['es_jefe'], ['0', '1'], true)) {
        throw new Exception('Respuesta inválida en las preguntas de clasificación.');
      }
      $atiendeClientes = (int) $_POST['atiende_clientes'];
      $esJefe          = (int) $_POST['es_jefe'];

      $respuestasPost = is_array($_POST['respuestas']) ? $_POST['respuestas'] : [];

      // ---- RF-02: el set EXACTO de reactivos que deben responderse depende
      // de lo que el encuestado contestó en las preguntas-filtro. Los que
      // queden fuera (filtro en 'No') simplemente no se piden ni se guardan
      // — no hay que "restarlos" en ningún lado (mismo criterio que usa
      // resultadoModel::calcular_para_aplicacion()). ----
      $reactivosEsperados = reactivoModel::obligatorios_por_guia($aplicacion['guia_id']);

      foreach (preguntaFiltroModel::por_guia($aplicacion['guia_id']) as $filtro) {
        $habilitado = ((int) $filtro['orden'] === 1) ? $atiendeClientes : $esJefe;
        if ($habilitado === 1) {
          $reactivosEsperados = array_merge($reactivosEsperados, reactivoModel::condicionales_por_filtro($filtro['id']));
        }
      }

      // No confiar en los ids de opción que llegaron por POST: deben ser
      // alguna de las 5 opciones reales de la escala Likert.
      $opcionesValidas = array_column(opcionRespuestaModel::escala(), 'id');

      $respuestasAInsertar = [];
      foreach ($reactivosEsperados as $reactivo) {
        $opcionId = $respuestasPost[$reactivo['id']] ?? null;

        if ($opcionId === null || $opcionId === '' || !in_array((int) $opcionId, $opcionesValidas, true)) {
          throw new Exception('Debes responder todas las preguntas antes de enviar el cuestionario.');
        }

        $respuestasAInsertar[] =
        [
          'aplicacion_id'       => $aplicacion['id'],
          'reactivo_id'         => $reactivo['id'],
          'opcion_respuesta_id' => (int) $opcionId
        ];
      }

      if (!respuestaModel::insertar_lote($respuestasAInsertar)) {
        throw new Exception('Hubo un problema al guardar tus respuestas. Intenta de nuevo.');
      }

      aplicacionModel::update_by_id($aplicacion['id'], [
        'atiende_clientes' => $atiendeClientes,
        'es_jefe'          => $esJefe,
        'estado'           => 'completada'
      ]);

      // RNF-06: calificar en tiempo real, en el mismo momento del envío
      resultadoModel::calcular_para_aplicacion($aplicacion['id']);

      // Se cierra la sesión de este cuestionario: ya no se puede volver a
      // usar responder()/post_responder() con esta aplicación.
      unset($_SESSION['cuestionario']);

      Redirect::to('cuestionario/gracias');

    } catch (Exception $e) {
      Flasher::error($e->getMessage());
      Redirect::back();
    }
  }

  /**
   * Pantalla de agradecimiento tras el envío exitoso. A propósito NO
   * muestra el resultado calculado: los resultados son para
   * administrador/súper usuario (resultadosController), no para el
   * trabajador que respondió.
   */
  function gracias()
  {
    $this->setTitle('Cuestionario enviado');
    $this->setView('agradecimiento'); // templates/views/cuestionario/agradecimientoView.php
    $this->render();
  }

  /**
   * Valida que exista una aplicación en curso ligada a la sesión nativa de
   * PHP (ver docblock de la clase) para el $token de la URL/formulario, que
   * el token siga vigente (se puede revocar o vencer entre el acceso inicial
   * y este punto) y que la aplicación no esté ya completada (evita
   * reenvíos por doble clic o botón "atrás"). "Falla cerrado": cualquier
   * inconsistencia deniega y redirige a index(), igual criterio que
   * resultadosController::verificarAccesoCentroTrabajo().
   *
   * @param string|null $token
   * @return array La fila de `aplicacion` en curso (garantizado 'en_progreso' si el método regresa)
   */
  private function aplicacionEnCurso($token)
  {
    $sesion = $_SESSION['cuestionario'] ?? null;

    if (empty($token) || empty($sesion) || $sesion['token_codigo'] !== $token) {
      Flasher::error('Tu sesión de acceso expiró o no es válida. Vuelve a capturar tu token.');
      Redirect::to('cuestionario');
    }

    if (!tokenModel::esta_vigente($token)) {
      unset($_SESSION['cuestionario']);
      Flasher::error('El token ya no es válido, está inactivo o venció.');
      Redirect::to('cuestionario');
    }

    $aplicacion = aplicacionModel::by_id($sesion['aplicacion_id']);

    if (empty($aplicacion)) {
      unset($_SESSION['cuestionario']);
      Flasher::error('No se encontró tu cuestionario en curso. Vuelve a capturar tu token.');
      Redirect::to('cuestionario');
    }

    if ($aplicacion['estado'] === 'completada') {
      unset($_SESSION['cuestionario']);
      Flasher::error('Ya respondiste este cuestionario.');
      Redirect::to('cuestionario');
    }

    return $aplicacion;
  }
}
