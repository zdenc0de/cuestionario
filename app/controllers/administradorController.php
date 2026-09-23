<?php
/**
 * Plantilla general de controladores
 * @version 1.2.0
 *
 * Controlador de administrador
 *
 * Módulo ADMINISTRADOR (por centro de trabajo): alta de centros de trabajo,
 * generación de tokens únicos con vigencia y habilitación de cuestionarios
 * (RF-10). Requiere sesión de Bee activa Y rol de contexto 'administrador'
 * (ver requiere_rol() en app/functions/bee_custom_functions.php y la
 * decisión de diseño documentada en usuarioModel / docs/ARQUITECTURA.md).
 *
 * Alcance (docs/HANDOFF_DESARROLLO.md §4): un administrador sólo ve/gestiona
 * SUS PROPIOS centros de trabajo — todo método que reciba un
 * centro_trabajo_id o token_id valida explícitamente que
 * centro_trabajo.administrador_id === obtener_usuario_actual()['id'],
 * nunca confía en el id recibido sin más.
 *
 * Rutas (ejemplos):
 *   /administrador                          -> index()             tablero del administrador
 *   /administrador/centros_trabajo           -> centros_trabajo()   alta/listado de centros
 *   /administrador/post_centros_trabajo      -> post_centros_trabajo()
 *   /administrador/tokens/{centroTrabajoId}  -> tokens()            generación/listado de tokens
 *   /administrador/post_tokens               -> post_tokens()
 *   /administrador/revocar_token/{id}        -> revocar_token()
 *   /administrador/habilitar/{centroTrabajoId} -> habilitar()       habilita el cuestionario del centro
 *
 * @see docs/ARQUITECTURA.md
 */
class administradorController extends Controller implements ControllerInterface
{
  function __construct()
  {
    // Guard de acceso: valida sesión nativa de Bee Y rol de contexto
    // 'administrador' (usuarioModel). Auth::validate() por sí solo NO
    // comprueba el rol, sólo que exista una sesión — por eso se usa
    // requiere_rol() en vez de Auth::validate() a secas.
    requiere_rol('administrador');

    // Ejecutar la funcionalidad del Controller padre
    parent::__construct();
  }

  /**
   * Tablero principal del administrador
   */
  function index()
  {
    // TODO: registrar_auditoria() de la consulta (RF-12) — se deja fuera a
    // propósito para no llenar la bitácora en cada carga del tablero (mismo
    // criterio que superusuarioController::index(), ver docs/ARQUITECTURA.md).

    $this->setTitle('Panel del administrador');
    $this->setView('index'); // templates/views/administrador/indexView.php
    $this->render();
  }

  ////////////////////////////////////////////////////
  //////// CENTROS DE TRABAJO
  ////////////////////////////////////////////////////

  /**
   * Listado de los centros de trabajo del administrador en sesión, con la
   * guía aplicable ya resuelta por cada uno (guiaModel::por_numero_trabajadores(),
   * calculada aquí una sola vez por fila en vez de en la vista, para no
   * repetir la consulta ni la lógica en dos lugares).
   */
  function centros_trabajo()
  {
    $usuarioActual = obtener_usuario_actual();
    $centros       = centroTrabajoModel::por_administrador($usuarioActual['id']);

    foreach ($centros as &$centro) {
      // Por construcción esto siempre resuelve a una guía real (nunca null)
      // porque post_centros_trabajo() ya impide crear un centro de ≤15
      // trabajadores — se deja la comprobación de cualquier forma, no se
      // asume ciegamente.
      $guia                = guiaModel::por_numero_trabajadores((int) $centro['num_trabajadores']);
      $centro['guia_clave'] = $guia['clave'] ?? null;
      $centro['guia_nombre'] = $guia['nombre'] ?? null;
    }
    unset($centro);

    $this->addToData('centros', $centros);
    $this->setTitle('Centros de trabajo');
    $this->setView('centrosTrabajo'); // templates/views/administrador/centrosTrabajoView.php
    $this->render();
  }

  /**
   * Alta de un nuevo centro de trabajo (RF-10)
   *
   * Regla NOM-035 (Campo de aplicación), ya resuelta por
   * guiaModel::por_numero_trabajadores(): ≤15 trabajadores no requiere este
   * cuestionario (ninguna guía aplica, NO se crea el centro) / 16–50 →
   * Guía II / >50 → Guía III.
   */
  function post_centros_trabajo()
  {
    // Bee no distingue verbos HTTP: rechaza cualquier acceso que no sea POST
    requiere_metodo_post();

    try {
      if (!Csrf::validate($_POST['csrf'] ?? '')) {
        throw new Exception(get_bee_message(0));
      }

      if (!check_posted_data(['nombre', 'num_trabajadores'], $_POST)) {
        throw new Exception('Por favor completa el formulario.');
      }

      array_map('sanitize_input', $_POST);

      $nombre = trim($_POST['nombre']);
      if ($nombre === '') {
        throw new Exception('El nombre del centro de trabajo es obligatorio.');
      }

      // Validación (handoff §4): num_trabajadores debe ser un entero positivo
      if (!ctype_digit((string) $_POST['num_trabajadores']) || (int) $_POST['num_trabajadores'] < 1) {
        throw new Exception('El número de trabajadores debe ser un entero positivo.');
      }
      $numTrabajadores = (int) $_POST['num_trabajadores'];

      // Guard NOM-035: si el número de trabajadores no cae en el rango de
      // ninguna guía (≤15), NO se crea el centro de trabajo ni se continúa
      // con el flujo del cuestionario. La guía NO se guarda en
      // centro_trabajo (ver docs/DDL/ddl.sql); se resuelve en el momento
      // con guiaModel::por_numero_trabajadores().
      $guia = guiaModel::por_numero_trabajadores($numTrabajadores);

      if ($guia === null) {
        Flasher::error('Los centros de trabajo de hasta 15 trabajadores no requieren la aplicación de este cuestionario conforme a la NOM-035.');
        Redirect::back();
      }

      $usuarioActual = obtener_usuario_actual();

      // secretaria_id y administrador_id SIEMPRE del usuario en sesión,
      // nunca de $_POST (alcance por secretaría/administrador)
      $centroId = centroTrabajoModel::insertOne([
        'secretaria_id'    => $usuarioActual['secretaria_id'],
        'administrador_id' => $usuarioActual['id'],
        'nombre'           => $nombre,
        'num_trabajadores' => $numTrabajadores
      ]);

      if (!$centroId) {
        throw new Exception('Hubo un problema al agregar el centro de trabajo.');
      }

      registrar_auditoria(
        'alta_centro_trabajo',
        'centro_trabajo',
        $centroId,
        sprintf('Centro "%s" (%d trabajadores, %s)', $nombre, $numTrabajadores, $guia['clave'])
      );

      Flasher::success(sprintf('Centro de trabajo <b>%s</b> agregado con éxito — aplica <b>%s</b>.', $nombre, $guia['nombre']));
      Redirect::back();

    } catch (Exception $e) {
      Flasher::error($e->getMessage());
      Redirect::back();
    }
  }

  function editar_centro_trabajo($id = null)
  {
    // TODO: cargar centroTrabajoModel::by_id($id) y validar pertenencia al administrador en sesión
    // (administrador_id === obtener_usuario_actual()['id']), de lo contrario Flasher::deny(2) + Redirect

    $this->setTitle('Editar centro de trabajo');
    $this->setView('centrosTrabajo');
    $this->render();
  }

  function borrar_centro_trabajo($id = null)
  {
    // No es un método post_*: sigue el patrón de Bee para acciones de borrado
    // vía enlace GET + token CSRF en query string (ver adminController::borrar_usuario())
    // TODO: if (!Csrf::validate($_GET['_t'] ?? '')) { Flasher::deny(); Redirect::back(); }
    // TODO: validar pertenencia (ver editar_centro_trabajo) y borrar con centroTrabajoModel::delete_by_id($id)
    // TODO: registrar_auditoria('borrar_centro_trabajo', 'centro_trabajo', $id)
    // No pedido en esta tarea (docs/HANDOFF_DESARROLLO.md §"TAREA PRINCIPAL"
    // sólo pide alta+listado de centros), se deja igual que estaba.
  }

  ////////////////////////////////////////////////////
  //////// TOKENS
  ////////////////////////////////////////////////////

  /**
   * Listado/generación de tokens para un centro de trabajo (RF-10). Alcance:
   * el centro de trabajo debe pertenecer al administrador en sesión.
   *
   * @param mixed $centroTrabajoId
   */
  function tokens($centroTrabajoId = null)
  {
    $usuarioActual = obtener_usuario_actual();
    $centro        = centroTrabajoModel::by_id($centroTrabajoId);

    if (empty($centro) || (int) $centro['administrador_id'] !== (int) $usuarioActual['id']) {
      Flasher::deny(2); // 'Permisos denegados.'
      Redirect::to('administrador/centros_trabajo');
    }

    $this->addToData('centro', $centro);
    $this->addToData('centro_trabajo_id', $centroTrabajoId);
    $this->addToData('tokens', tokenModel::por_centro_trabajo($centroTrabajoId));
    // aplicacionModel::por_centro_trabajo() ya filtra estado='completada' —
    // cierra el lazo visual: de aquí se enlaza a resultados/individual().
    $this->addToData('aplicaciones', aplicacionModel::por_centro_trabajo($centroTrabajoId));
    $this->setTitle('Tokens de acceso');
    $this->setView('tokens'); // templates/views/administrador/tokensView.php
    $this->render();
  }

  /**
   * Genera un token único con vigencia para un centro de trabajo (RF-10).
   * Recordatorio del modelo de token (decisión de diseño B, ver
   * tokenModel::class): UN token por centro de trabajo por campaña
   * (multiuso mientras esté vigente), NO por persona — nada impide generar
   * varios tokens (varias campañas) para el mismo centro con el tiempo.
   */
  function post_tokens()
  {
    // Bee no distingue verbos HTTP: rechaza cualquier acceso que no sea POST
    requiere_metodo_post();

    try {
      if (!Csrf::validate($_POST['csrf'] ?? '')) {
        throw new Exception(get_bee_message(0));
      }

      if (!check_posted_data(['centro_trabajo_id', 'fecha_inicio', 'fecha_fin'], $_POST)) {
        throw new Exception('Por favor completa el formulario.');
      }

      array_map('sanitize_input', $_POST);
      $centroTrabajoId = (int) $_POST['centro_trabajo_id'];
      $fechaInicio     = $_POST['fecha_inicio'];
      $fechaFin        = $_POST['fecha_fin'];

      // Validación (handoff §4): fechas válidas y fecha_fin >= fecha_inicio.
      // Comparación como cadena 'Y-m-d' es válida (orden lexicográfico =
      // orden cronológico en fechas ISO con ceros a la izquierda), mismo
      // criterio que ya usa tokenModel::esta_vigente().
      if (!DateTime::createFromFormat('Y-m-d', $fechaInicio) || !DateTime::createFromFormat('Y-m-d', $fechaFin)) {
        throw new Exception('Las fechas de vigencia no son válidas.');
      }

      if ($fechaFin < $fechaInicio) {
        throw new Exception('La fecha de fin debe ser igual o posterior a la fecha de inicio.');
      }

      $usuarioActual = obtener_usuario_actual();
      $centro        = centroTrabajoModel::by_id($centroTrabajoId);

      // Alcance: sólo puede generar tokens para SUS PROPIOS centros de trabajo
      if (empty($centro) || (int) $centro['administrador_id'] !== (int) $usuarioActual['id']) {
        throw new Exception('Ese centro de trabajo no existe o no te pertenece.');
      }

      $codigo = tokenModel::generar_codigo();

      $tokenId = tokenModel::insertOne([
        'centro_trabajo_id' => $centroTrabajoId,
        'codigo'             => $codigo,
        'fecha_inicio'        => $fechaInicio,
        'fecha_fin'           => $fechaFin,
        'estado'              => 'activo'
      ]);

      if (!$tokenId) {
        throw new Exception('Hubo un problema al generar el token.');
      }

      registrar_auditoria(
        'generar_token',
        'token',
        $tokenId,
        sprintf('Token %s para "%s", vigente %s a %s', $codigo, $centro['nombre'], $fechaInicio, $fechaFin)
      );

      Flasher::success(sprintf('Token generado con éxito: <b>%s</b> (vigente del %s al %s).', $codigo, $fechaInicio, $fechaFin));
      Redirect::back();

    } catch (Exception $e) {
      Flasher::error($e->getMessage());
      Redirect::back();
    }
  }

  /**
   * Revoca un token (RF-10) — estado='inactivo', no se borra (mismo
   * criterio que superusuarioController::borrar_administrador(): conservar
   * el registro). No es un método post_*: sigue el patrón de Bee para
   * acciones vía enlace GET + token CSRF en query string (ver
   * adminController::borrar_usuario()).
   *
   * @param mixed $id id del token, no del centro de trabajo
   */
  function revocar_token($id = null)
  {
    try {
      if (!Csrf::validate($_GET['_t'] ?? '')) {
        throw new Exception(get_bee_message(0));
      }

      $usuarioActual = obtener_usuario_actual();
      $token         = tokenModel::by_id($id);
      $centro        = !empty($token) ? centroTrabajoModel::by_id($token['centro_trabajo_id']) : [];

      // Alcance: el token debe pertenecer a un centro de trabajo del administrador en sesión
      if (empty($token) || empty($centro) || (int) $centro['administrador_id'] !== (int) $usuarioActual['id']) {
        throw new Exception('Ese token no existe o no pertenece a uno de tus centros de trabajo.');
      }

      if (!tokenModel::revocar($id)) {
        throw new Exception('Hubo un problema al revocar el token.');
      }

      registrar_auditoria('revocar_token', 'token', $id, sprintf('Token %s revocado (centro: %s)', $token['codigo'], $centro['nombre']));

      Flasher::success('Token revocado con éxito.');
      Redirect::back();

    } catch (Exception $e) {
      Flasher::error($e->getMessage());
      Redirect::back();
    }
  }

  ////////////////////////////////////////////////////
  //////// HABILITAR CUESTIONARIO
  ////////////////////////////////////////////////////

  /**
   * Habilita la aplicación del cuestionario para un centro de trabajo (RF-10).
   *
   * DEFINICIÓN PROVISIONAL (confirmada como punto de partida, sujeta a
   * validación con la Secretaría — ver decisión de diseño B en tokenModel):
   * "habilitar cuestionario" = crear/activar un token vigente para el
   * centro de trabajo (tokenModel::activo_por_centro_trabajo()). Mientras el
   * centro de trabajo no tenga un token con estado 'activo' y vigente, se
   * considera que no tiene cuestionario habilitado.
   *
   * No pedido explícitamente en esta tarea (que pide generar tokens desde
   * tokens()/post_tokens(), ya implementado arriba) — se deja igual que
   * estaba, como TODO, para no ampliar el alcance sin que se pida.
   *
   * @param mixed $centroTrabajoId
   */
  function habilitar($centroTrabajoId = null)
  {
    // No es un método post_*: sigue el patrón de Bee para acciones vía
    // enlace GET + token CSRF en query string (ver adminController::borrar_usuario())
    // TODO: if (!Csrf::validate($_GET['_t'] ?? '')) { Flasher::deny(); Redirect::back(); }
    // TODO: validar que $centroTrabajoId pertenezca al administrador en sesión
    // TODO: si no existe tokenModel::activo_por_centro_trabajo($centroTrabajoId), generar uno (ver post_tokens())
    // TODO: registrar_auditoria('habilitar_cuestionario', 'centro_trabajo', $centroTrabajoId)

    Flasher::error('Funcionalidad pendiente de implementación (fase de Desarrollo).');
    Redirect::back();
  }
}
