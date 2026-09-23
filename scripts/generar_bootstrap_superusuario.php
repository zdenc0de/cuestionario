<?php
/**
 * Genera el bootstrap SQL del primer súper usuario (cuenta raíz) del
 * sistema NOM-035. NO es parte de la app (no agrega rutas ni
 * controladores) — es una utilidad de línea de comandos, reutilizable
 * cada vez que haga falta un nuevo bootstrap (ej. resetear la contraseña
 * del súper usuario, o crear uno para otro entorno).
 *
 * IMPORTANTE — algoritmo de contraseña: usa get_new_password()
 * (app/functions/bee_core_functions.php), que es la MISMA función que usa
 * beeController::generate_user() para crear cuentas de prueba de Bee.
 * Internamente hace exactamente lo mismo que valida el login
 * (loginController::post_login() -> password_verify($password.AUTH_SALT, ...))
 * y lo que usa adminController::post_usuarios() para dar de alta
 * (password_hash($password . AUTH_SALT, PASSWORD_BCRYPT)). No se inventa
 * ningún hash nuevo, se reutiliza el mecanismo nativo de Bee tal cual.
 *
 * Uso: php scripts/generar_bootstrap_superusuario.php
 *      php scripts/generar_bootstrap_superusuario.php "Mi Secretaría" miusuario correo@dominio.com
 *      (los 3 argumentos son opcionales; sin ellos usa los valores por
 *      defecto del proyecto y genera una contraseña aleatoria segura)
 *
 * Salida: un bloque SQL listo para pegar en phpMyAdmin (pestaña SQL, sobre
 * la base db_beeframework) y, por separado, las credenciales en texto
 * plano (sólo se muestran una vez, aquí).
 */

$_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$_SERVER['HTTP_HOST']   = $_SERVER['HTTP_HOST']   ?? 'localhost';
$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/';

chdir(__DIR__ . '/..');

require_once 'app/config/bee_config.php';
require_once 'app/core/settings.php';
require_once 'app/vendor/autoload.php';
require_once CLASSES . 'Autoloader.php';
Autoloader::init();
require_once FUNCTIONS . 'bee_core_functions.php';
require_once FUNCTIONS . 'bee_custom_functions.php';

// ---------------------------------------------------------------------
// Parámetros (con valores por defecto del proyecto)
// ---------------------------------------------------------------------
$nombreSecretaria = $argv[1] ?? 'Secretaría de Cultura y Turismo';
$username         = $argv[2] ?? 'superadmin';
$email            = $argv[3] ?? 'superadmin@culturaturismo.edomex.gob.mx';

// get_new_password(null) genera una contraseña aleatoria de 8 caracteres
// alfanuméricos (random_password(), bee_core_functions.php) y regresa ya
// su hash con la fórmula real de Bee — ver docblock arriba.
$resultado = get_new_password();
$password  = $resultado['password'];
$hash      = $resultado['hash'];

// mysqli_real_escape_string() no requiere conexión para escapar comillas
// simples de forma segura en este contexto de generación estática de texto.
function sql_escape(string $valor): string
{
  return str_replace("'", "\\'", $valor);
}

$now                      = date('Y-m-d H:i:s');
$nombreSecretariaEscapada = sql_escape($nombreSecretaria);
$usernameEscapado         = sql_escape($username);
$emailEscapado            = sql_escape($email);

$sql = <<<SQL
-- =====================================================================
-- Bootstrap del primer súper usuario (cuenta raíz) — Sistema NOM-035
-- Generado por scripts/generar_bootstrap_superusuario.php el {$now}
--
-- El hash de abajo se calculó con la fórmula REAL de Bee
-- (password_hash(\$password . AUTH_SALT, PASSWORD_BCRYPT), ver
-- app/classes/Auth.php, app/controllers/loginController.php y
-- get_new_password() en app/functions/bee_core_functions.php) contra el
-- AUTH_SALT actual de app/core/settings.php. Ejecutar tal cual, de una
-- sola vez, en phpMyAdmin (pestaña SQL) sobre la base db_beeframework.
-- =====================================================================

INSERT INTO secretaria (nombre) VALUES ('{$nombreSecretariaEscapada}');
SET @secretaria_id = LAST_INSERT_ID();

INSERT INTO bee_users (username, email, password, created_at)
VALUES ('{$usernameEscapado}', '{$emailEscapado}', '{$hash}', NOW());
SET @bee_user_id = LAST_INSERT_ID();

INSERT INTO usuario (bee_user_id, rol, secretaria_id)
VALUES (@bee_user_id, 'superusuario', @secretaria_id);
SQL;

echo $sql . "\n\n";
echo "-- ---------------------------------------------------------------------\n";
echo "-- Credenciales en texto plano (solo se muestran aqui, guardalas ahora):\n";
echo "--   Usuario:    {$username}\n";
echo "--   Contrasena: {$password}\n";
echo "-- ---------------------------------------------------------------------\n";
