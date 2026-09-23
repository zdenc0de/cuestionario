<?php
/**
 * Plantilla general de controladores
 * @version 1.2.0
 *
 * Controlador de superusuario
 *
 * Módulo SÚPER USUARIO (por Secretaría): gestión de administradores y
 * bitácora de auditoría (RF-09, RF-12). Requiere sesión de Bee activa Y rol
 * de contexto 'superusuario' (ver requiere_rol() en
 * app/functions/bee_custom_functions.php y la decisión de diseño
 * documentada en usuarioModel / docs/ARQUITECTURA.md).
 *
 * Alcance por secretaría (docs/HANDOFF_DESARROLLO.md §4): un súper usuario
 * sólo ve/gestiona lo de su propia secretaria_id — todos los métodos que
 * tocan administradores u otras secretarías filtran explícitamente por
 * obtener_usuario_actual()['secretaria_id'], nunca por un ID recibido del
 * cliente sin validar.
 *
 * Rutas (ejemplos):
 *   /superusuario                       -> index()              tablero del súper usuario
 *   /superusuario/administradores        -> administradores()    alta/listado de administradores
 *   /superusuario/post_administradores    -> post_administradores()
 *   /superusuario/borrar_administrador/{id} -> borrar_administrador()  revoca el acceso (ver docblock del método)
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
    // TODO: registrar_auditoria() de la consulta (RF-12) — se dejó fuera a
    // propósito para no llenar la bitácora con una entrada en cada carga
    // del tablero; sí se registra en las acciones que mutan datos (alta/baja
    // de administradores, ver post_administradores()/borrar_administrador()).

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
    $usuarioActual = obtener_usuario_actual();

    $this->addToData('administradores', usuarioModel::administradores_por_secretaria($usuarioActual['secretaria_id']));
    $this->setTitle('Administradores');
    $this->setView('administradores'); // templates/views/superusuario/administradoresView.php
    $this->render();
  }

  /**
   * Alta de un nuevo administrador (RF-09). Crea la cuenta nativa de Bee en
   * bee_users (mismo algoritmo de hash que valida el login, ver
   * app/classes/Auth.php + loginController::post_login() +
   * adminController::post_usuarios(), del que se copian también las
   * validaciones de username/email/password) y su enlace de rol en
   * usuarioModel, con secretaria_id = la del súper usuario en sesión (RF-09
   * "alcance por secretaría": el nuevo administrador SIEMPRE queda en la
   * misma secretaría de quien lo crea, nunca se recibe un secretaria_id del
   * formulario).
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
      $username     = $_POST['username'];
      $email        = $_POST['email'];
      $password     = $_POST['password'];
      $errorMessage = '';
      $errors       = 0;

      // Duplicados contra bee_users (mismo patrón que adminController::post_usuarios())
      $sql = 'SELECT * FROM bee_users WHERE username = :username OR email = :email';
      if (userModel::query($sql, ['username' => $username, 'email' => $email])) {
        throw new Exception('Ya existe un usuario registrado con ese nombre de usuario o correo electrónico.');
      }

      // Mismas validaciones que adminController::post_usuarios(), por consistencia
      if (!preg_match('/^[a-zA-Z0-9]{5,20}$/', $username)) {
        $errorMessage .= '- El nombre de usuario debe tener entre 5 y 20 caracteres alfanuméricos.<br>';
        $errors++;
      }

      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage .= '- El correo electrónico no es válido.<br>';
        $errors++;
      }

      if (is_temporary_email($email)) {
        $errorMessage .= '- El dominio del correo electrónico no está autorizado.<br>';
        $errors++;
      }

      if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*_-])[A-Za-z\d!@#$%^&*_-]{5,20}$/', $password)) {
        $errorMessage .= '- La contraseña debe tener entre 5 y 20 caracteres, con al menos 1 minúscula, 1 mayúscula, 1 dígito y 1 caracter especial de entre <b>!@#$%^&*_-</b>.';
        $errors++;
      }

      if ($errors > 0) {
        throw new Exception($errorMessage);
      }

      $usuarioActual = obtener_usuario_actual();

      // Cuenta nativa de Bee — MISMO algoritmo de hash que valida el login
      // (password_hash($password . AUTH_SALT, PASSWORD_BCRYPT)), no se inventa nada nuevo.
      $beeUserId = userModel::add(userModel::$t1, [
        'username'   => $username,
        'email'      => $email,
        'password'   => password_hash($password . AUTH_SALT, PASSWORD_BCRYPT),
        'created_at' => now()
      ]);

      if (!$beeUserId) {
        throw new Exception('Hubo un problema al crear la cuenta del administrador.');
      }

      // Enlace de contexto: rol administrador, misma secretaría del súper
      // usuario en sesión (nunca se toma la secretaría de $_POST)
      $usuarioId = usuarioModel::insertOne([
        'bee_user_id'   => $beeUserId,
        'rol'           => 'administrador',
        'secretaria_id' => $usuarioActual['secretaria_id']
      ]);

      if (!$usuarioId) {
        // No dejar una cuenta de bee_users huérfana sin su enlace de rol
        userModel::delete_by_id($beeUserId);
        throw new Exception('Hubo un problema al asignar el rol de administrador.');
      }

      registrar_auditoria('alta_administrador', 'usuario', $usuarioId, sprintf('Administrador creado: %s (%s)', $username, $email));

      Flasher::success(sprintf('Administrador agregado con éxito:<br>Usuario: <b>%s</b><br>Contraseña: <b>%s</b>', $username, $password));
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

  /**
   * "Baja" de un administrador (RF-09) = REVOCAR su acceso, NO borrar sus
   * filas. Dos razones, ambas de fondo, no una limitación evitable:
   *
   *   1. Integridad de la bitácora: auditoria.usuario_id -> usuario.id es
   *      ON DELETE RESTRICT a propósito (docs/DDL/ddl.sql) — el historial
   *      de auditoría de una cuenta NO debe poder desaparecer borrando la
   *      cuenta. En cuanto un administrador actúa una sola vez (o incluso
   *      antes: su propia alta ya queda registrada con el súper usuario
   *      como actor), intentar un DELETE físico de `usuario`/`bee_users`
   *      puede chocar con esa restricción.
   *   2. Trazabilidad: aunque no hubiera FK de por medio, borrar la cuenta
   *      de alguien que sí actuó destruye el "quién" de esas acciones.
   *
   * Por eso "dar de baja" aquí es: invalidar la contraseña con un valor
   * aleatorio que nadie conoce (mismo algoritmo de hash que el login, vía
   * get_new_password()) — la cuenta deja de poder iniciar sesión, pero su
   * fila y su historial de auditoría se conservan intactos.
   *
   * LIMITACIÓN CONOCIDA (documentada, no oculta): no hay columna de estado
   * (`activo`) en `usuario`/`bee_users` en el DDL actual, así que un
   * administrador con el acceso revocado sigue apareciendo en el listado de
   * administradores() sin una marca visual de "revocado". Agregar esa
   * columna requiere modificar docs/DDL/ddl.sql, fuera del alcance de esta
   * tarea — queda como TODO explícito para cuando se decida.
   *
   * No es un método post_*: sigue el patrón de Bee para acciones vía
   * enlace GET + token CSRF en query string (ver adminController::borrar_usuario()).
   *
   * @param mixed $id El id de usuario (tabla de enlace), no de bee_users
   */
  function borrar_administrador($id = null)
  {
    try {
      if (!Csrf::validate($_GET['_t'] ?? '')) {
        throw new Exception(get_bee_message(0));
      }

      $usuarioActual = obtener_usuario_actual();
      $administrador = usuarioModel::by_id($id);

      // Alcance por secretaría: sólo puede revocar administradores de SU
      // propia secretaría, y sólo administradores (no a sí mismo ni a otro superusuario)
      if (
        empty($administrador) ||
        $administrador['rol'] !== 'administrador' ||
        (int) $administrador['secretaria_id'] !== (int) $usuarioActual['secretaria_id']
      ) {
        throw new Exception('No existe ese administrador o no pertenece a tu secretaría.');
      }

      $passwordRevocada = get_new_password(); // contraseña aleatoria descartada, nadie la conoce
      if (!userModel::update_by_id($administrador['bee_user_id'], ['password' => $passwordRevocada['hash']])) {
        throw new Exception('Hubo un problema al revocar el acceso del administrador.');
      }

      registrar_auditoria('baja_administrador', 'usuario', $id, 'Acceso revocado (contraseña invalidada)');

      Flasher::success('Se revocó el acceso del administrador.');
      Redirect::back();

    } catch (Exception $e) {
      Flasher::error($e->getMessage());
      Redirect::back();
    }
  }

  ////////////////////////////////////////////////////
  //////// BITÁCORA DE AUDITORÍA
  ////////////////////////////////////////////////////

  /**
   * Consulta de la bitácora de auditoría de los administradores (RF-12).
   * Alcance por secretaría: sólo la de la secretaría del súper usuario en sesión.
   */
  function bitacora()
  {
    $usuarioActual = obtener_usuario_actual();

    $this->addToData('bitacora', auditoriaModel::por_secretaria($usuarioActual['secretaria_id']));
    $this->setTitle('Bitácora de auditoría');
    $this->setView('bitacora'); // templates/views/superusuario/bitacoraView.php
    $this->render();
  }
}
