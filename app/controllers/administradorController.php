<?php
/**
 * Plantilla general de controladores
 * @version 1.1.0
 *
 * Controlador de administrador
 *
 * Módulo ADMINISTRADOR (por centro de trabajo): alta de centros de trabajo,
 * generación de tokens únicos con vigencia y habilitación de cuestionarios
 * (RF-10). Requiere sesión de Bee activa Y rol de contexto 'administrador'
 * (ver requiere_rol() en app/functions/bee_custom_functions.php y la
 * decisión de diseño documentada en usuarioModel / docs/ARQUITECTURA.md).
 *
 * Rutas (ejemplos):
 *   /administrador                          -> index()             tablero del administrador
 *   /administrador/centros-trabajo           -> centros_trabajo()   alta/listado de centros
 *   /administrador/post_centros_trabajo      -> post_centros_trabajo()
 *   /administrador/tokens/{centroTrabajoId}  -> tokens()            generación/listado de tokens
 *   /administrador/post_tokens               -> post_tokens()
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
    // TODO: registrar_auditoria() de la consulta (RF-12)
    // TODO: cargar centroTrabajoModel::por_administrador(obtener_usuario_actual()['id'])

    $this->setTitle('Panel del administrador');
    $this->setView('index'); // templates/views/administrador/indexView.php
    $this->render();
  }

  ////////////////////////////////////////////////////
  //////// CENTROS DE TRABAJO
  ////////////////////////////////////////////////////
  function centros_trabajo()
  {
    // TODO: $this->addToData('centros', centroTrabajoModel::por_administrador(obtener_usuario_actual()['id']));

    $this->setTitle('Centros de trabajo');
    $this->setView('centrosTrabajo'); // templates/views/administrador/centrosTrabajoView.php
    $this->render();
  }

  /**
   * Alta de un nuevo centro de trabajo (RF-10)
   * TODO (fase de Desarrollo): implementar validaciones y determinar la guía
   * automáticamente con centroTrabajoModel::determinar_guia() (RF-00)
   */
  function post_centros_trabajo()
  {
    // Bee no distingue verbos HTTP: rechaza cualquier acceso que no sea POST
    requiere_metodo_post();

    try {
      if (!Csrf::validate($_POST['csrf'] ?? '')) {
        throw new Exception(get_bee_message(0));
      }

      if (!check_posted_data(['nombre', 'numero_trabajadores'], $_POST)) {
        throw new Exception('Por favor completa el formulario.');
      }

      array_map('sanitize_input', $_POST);

      // TODO: determinar guia_id con centroTrabajoModel::determinar_guia((int) $_POST['numero_trabajadores'])
      // TODO: centroTrabajoModel::insertOne([...]) incluyendo administrador_id = obtener_usuario_actual()['id']
      // TODO: registrar_auditoria('alta_centro_trabajo', 'centro_trabajo', $id)

      Flasher::error('Funcionalidad pendiente de implementación (fase de Desarrollo).');
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
  }

  ////////////////////////////////////////////////////
  //////// TOKENS
  ////////////////////////////////////////////////////

  /**
   * Listado/generación de tokens para un centro de trabajo (RF-10)
   *
   * @param mixed $centroTrabajoId
   */
  function tokens($centroTrabajoId = null)
  {
    // TODO: validar que $centroTrabajoId pertenezca al administrador en sesión
    // TODO: $this->addToData('tokens', tokenModel::por_centro_trabajo($centroTrabajoId));

    $this->setTitle('Tokens de acceso');
    $this->addToData('centro_trabajo_id', $centroTrabajoId);
    $this->setView('tokens'); // templates/views/administrador/tokensView.php
    $this->render();
  }

  /**
   * Genera un token único con vigencia para un centro de trabajo (RF-10).
   * Recordatorio del modelo de token (decisión de diseño B, ver
   * tokenModel::class): UN token por centro de trabajo por campaña, NO por
   * persona; es multiuso mientras esté vigente.
   * TODO (fase de Desarrollo): definir vigencia por defecto (¿días?) con la Secretaría
   */
  function post_tokens()
  {
    // Bee no distingue verbos HTTP: rechaza cualquier acceso que no sea POST
    requiere_metodo_post();

    try {
      if (!Csrf::validate($_POST['csrf'] ?? '')) {
        throw new Exception(get_bee_message(0));
      }

      if (!check_posted_data(['centro_trabajo_id'], $_POST)) {
        throw new Exception('Por favor completa el formulario.');
      }

      // TODO: validar que centro_trabajo_id pertenezca al administrador en sesión
      // TODO: $codigo = tokenModel::generar_codigo()
      // TODO: tokenModel::insertOne(['centro_trabajo_id' => ..., 'codigo' => $codigo, 'fecha_inicio' => now(), 'fecha_fin' => ..., 'estado' => 'activo'])
      // TODO: registrar_auditoria('generar_token', 'token', $id)

      Flasher::error('Funcionalidad pendiente de implementación (fase de Desarrollo).');
      Redirect::back();

    } catch (Exception $e) {
      Flasher::error($e->getMessage());
      Redirect::back();
    }
  }

  function revocar_token($id = null)
  {
    // No es un método post_*: sigue el patrón de Bee para acciones vía
    // enlace GET + token CSRF en query string (ver adminController::borrar_usuario())
    // TODO: if (!Csrf::validate($_GET['_t'] ?? '')) { Flasher::deny(); Redirect::back(); }
    // TODO: tokenModel::revocar($id)
    // TODO: registrar_auditoria('revocar_token', 'token', $id)
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
