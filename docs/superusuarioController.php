<?php
/**
 * Plantilla general de controladores
 * @version 1.1.0
 *
 * Controlador de superusuario
 *
 * Módulo SÚPER USUARIO (por Secretaría): gestión de administradores y
 * bitácora de auditoría (RF-09, RF-12). Requiere sesión de Bee activa Y rol
 * de contexto 'superusuario' (ver requiere_rol() en
 * app/functions/bee_custom_functions.php y la decisión de diseño
 * documentada en usuarioModel / docs/ARQUITECTURA.md).
 *
 * Rutas (ejemplos):
 *   /superusuario                       -> index()              tablero del súper usuario
 *   /superusuario/administradores        -> administradores()    alta/listado de administradores
 *   /superusuario/post_administradores    -> post_administradores()
 *   /superusuario/bitacora               -> bitacora()           consulta de auditoría
 *
 * @see docs/ARQUITECTURA.md
 */
class superusuarioController extends Controller implements ControllerInterface
{
  function __construct()
  {
    // Guard de acceso: valida sesión nativa de Bee Y rol de contexto
    // 'superusuario' (usuarioModel). Auth::validate() por sí solo NO
    // comprueba el rol, sólo que exista una sesión — por eso se usa
    // requiere_rol() en vez de Auth::validate() a secas.
    requiere_rol('superusuario');

    // Ejecutar la funcionalidad del Controller padre
    parent::__construct();
  }

  /**
   * Tablero principal del súper usuario
   */
  function index()
  {
    // TODO: registrar_auditoria() de la consulta (RF-12)

    $this->setTitle('Panel del súper usuario');
    $this->setView('index'); // templates/views/superusuario/indexView.php
    $this->render();
  }

  ////////////////////////////////////////////////////
  //////// ADMINISTRADORES
  ////////////////////////////////////////////////////

  /**
   * Listado de administradores de la secretaría en sesión (RF-09)
   */
  function administradores()
  {
    // TODO: $this->addToData('administradores', usuarioModel::administradores_por_secretaria(obtener_usuario_actual()['secretaria_id']));

    $this->setTitle('Administradores');
    $this->setView('administradores'); // templates/views/superusuario/administradoresView.php
    $this->render();
  }

  /**
   * Alta de un nuevo administrador (RF-09)
   * TODO (fase de Desarrollo): crear el registro en bee_users (con password_hash + AUTH_SALT,
   * siguiendo el patrón de adminController::post_usuarios()) y su relación en usuarioModel
   * con rol = 'administrador'
   */
  function post_administradores()
  {
    // Bee no distingue verbos HTTP: rechaza cualquier acceso que no sea POST
    requiere_metodo_post();

    try {
      if (!Csrf::validate($_POST['csrf'] ?? '')) {
        throw new Exception(get_bee_message(0));
      }

      if (!check_posted_data(['username', 'email', 'password'], $_POST)) {
        throw new Exception('Por favor completa el formulario.');
      }

      array_map('sanitize_input', $_POST);

      // TODO: validar duplicados (username/email) contra bee_users
      // TODO: crear usuario en bee_users
      // TODO: usuarioModel::insertOne(['bee_user_id' => $id, 'rol' => 'administrador', 'secretaria_id' => obtener_usuario_actual()['secretaria_id']])
      // TODO: registrar_auditoria('alta_administrador', 'usuario', $id)

      Flasher::error('Funcionalidad pendiente de implementación (fase de Desarrollo).');
      Redirect::back();

    } catch (Exception $e) {
      Flasher::error($e->getMessage());
      Redirect::back();
    }
  }

  function editar_administrador($id = null)
  {
    // TODO: cargar usuarioModel::by_id($id) y validar pertenencia a la secretaría en sesión
    $this->setTitle('Editar administrador');
    $this->setView('administradores');
    $this->render();
  }

  function borrar_administrador($id = null)
  {
    // No es un método post_*: sigue el patrón de Bee para acciones vía
    // enlace GET + token CSRF en query string (ver adminController::borrar_usuario())
    // TODO: if (!Csrf::validate($_GET['_t'] ?? '')) { Flasher::deny(); Redirect::back(); }
    // TODO: validar pertenencia y borrar con usuarioModel::delete_by_id($id)
    // TODO: registrar_auditoria('borrar_administrador', 'usuario', $id)
  }

  ////////////////////////////////////////////////////
  //////// BITÁCORA DE AUDITORÍA
  ////////////////////////////////////////////////////

  /**
   * Consulta de la bitácora de auditoría de los administradores (RF-12)
   */
  function bitacora()
  {
    // TODO: $this->addToData('bitacora', auditoriaModel::all_paginated());

    $this->setTitle('Bitácora de auditoría');
    $this->setView('bitacora'); // templates/views/superusuario/bitacoraView.php
    $this->render();
  }
}
