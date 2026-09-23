<?php
// Funciones directamente del proyecto en curso

/**
 * Ejemplo para agregar endpoints autorizados para la API
 * Esto sólo es necesario si usarás más controladores a parte de apiController como endpoints de API
 * De lo contrario no requieres anexarlos a la lista de endpoints
 */
BeeHookManager::registerHook('init_set_up', 'setUpRoutes');

function setUpRoutes(Bee $instance)
{
  // Prueba ingresando a esta URL (depende de tu ubicación del proyecto): http://localhost:8848/Bee-Framework/reportes
  $instance->addEndpoint('reportes');
  $instance->addEndpoint('citas');
  $instance->addEndpoint('sucursales');

  $instance->addAjax('ajax2'); // http://localhost:8848/Bee-Framework/ajax2
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////// Cuestionario NOM-035-STPS-2018 — funciones auxiliares del proyecto
////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * DECISIÓN DE DISEÑO (confirmada, ver docs/ARQUITECTURA.md sección
 * "Decisiones de diseño — A. Autenticación/roles"): el sistema NATIVO de Bee
 * (bee_users + clase Auth) es la ÚNICA fuente de verdad de login. usuarioModel
 * NO duplica usuarios, es una tabla de ENLACE 1 a 1 con bee_users que agrega
 * el rol de contexto ('superusuario'|'administrador') y su secretaria_id /
 * centro_trabajo_id. Las tres funciones siguientes son el punto único donde
 * se resuelve esa relación; el resto del sistema no debe volver a consultar
 * bee_users/usuarioModel manualmente para esto.
 */

/**
 * Regresa el registro de enlace (usuarioModel) del usuario de Bee en sesión.
 *
 * @return array Arreglo vacío si no hay sesión de Bee válida o si el usuario no tiene rol de contexto asignado
 */
function obtener_usuario_actual()
{
  if (!Auth::validate()) {
    return [];
  }

  if (!$beeUserId = get_user('id')) {
    return [];
  }

  return usuarioModel::by_bee_user_id($beeUserId);
}

/**
 * Regresa el rol de contexto del usuario de Bee en sesión.
 *
 * @return string|null 'superusuario' | 'administrador' | null
 */
function obtener_rol_usuario_actual()
{
  $usuario = obtener_usuario_actual();
  return $usuario['rol'] ?? null;
}

/**
 * Regresa la ruta del tablero que corresponde a un rol de contexto dado.
 * Punto único de esta decisión — ver ruta_tablero_segun_rol() para el caso
 * normal (rol de la sesión actual) y su docblock para por qué existe esta
 * variante parametrizada.
 *
 * @param string|null $rol 'administrador' | 'superusuario' | null
 * @return string 'administrador' | 'superusuario' | 'admin' (panel nativo de Bee, cuenta sin rol de contexto — ej. el usuario demo "bee")
 */
function ruta_tablero_para_rol(?string $rol)
{
  switch ($rol) {
    case 'administrador':
      return 'administrador';
    case 'superusuario':
      return 'superusuario';
    default:
      return 'admin';
  }
}

/**
 * Regresa la ruta del tablero correcto para el usuario de Bee en sesión,
 * según su rol de contexto (usuarioModel) — para no duplicar esta decisión
 * entre los guards de rol (requiere_rol(), resultadosController) que
 * necesitan "regresar a donde sí puede estar" en vez de expulsar al
 * formulario público del encuestado.
 *
 * OJO — NO usar esta función dentro de loginController::post_login()
 * inmediatamente después de Auth::login(): get_user()/obtener_usuario_actual()
 * leen el global $Bee_User, que Bee sólo llena UNA VEZ por petición, ANTES
 * de que se ejecute el controlador (ver Bee::init_authentication()). Un
 * login que ocurre a la mitad de esa misma petición no lo actualiza, así
 * que esta función vería la sesión de ANTES de iniciar sesión (ninguna) y
 * regresaría 'admin' siempre, sin importar el rol real. Para ese caso usar
 * ruta_tablero_para_rol($rol) directamente con el rol ya resuelto en la
 * misma petición (ver loginController::post_login()).
 *
 * @return string 'administrador' | 'superusuario' | 'admin'
 */
function ruta_tablero_segun_rol()
{
  return ruta_tablero_para_rol(obtener_rol_usuario_actual());
}

/**
 * Guard de acceso por rol de contexto. Valida PRIMERO la sesión nativa de
 * Bee (Auth::validate() sólo comprueba que exista sesión, NO el rol) y
 * DESPUÉS que el rol de contexto (usuarioModel) coincida con el requerido.
 * Si cualquiera falla, deniega y redirige — Redirect::to() hace die()
 * internamente, por lo que el código que sigue a la llamada nunca se
 * ejecuta en el caso de rechazo.
 *
 * Uso: invocar al inicio del __construct() de administradorController
 * (con 'administrador') y de superusuarioController (con 'superusuario').
 *
 * Si el rol NO coincide (ej. un administrador visita una URL de
 * superusuario), la redirección va a ruta_tablero_segun_rol() — SU propio
 * tablero — en vez de DEFAULT_CONTROLLER (el formulario público del
 * encuestado). **Por qué:** expulsar a un usuario con sesión activa al
 * formulario público es una pérdida de contexto de navegación confusa; lo
 * correcto es devolverlo a donde sí tiene acceso.
 *
 * @param string $rolRequerido 'administrador' | 'superusuario'
 * @return bool
 */
function requiere_rol(string $rolRequerido)
{
  if (!Auth::validate()) {
    Flasher::new('Debes iniciar sesión primero.', 'danger');
    Redirect::to('login');
  }

  if (obtener_rol_usuario_actual() !== $rolRequerido) {
    Flasher::deny(2); // 'Permisos denegados.'
    Redirect::to(ruta_tablero_segun_rol());
  }

  return true;
}

/**
 * Guard de verbo HTTP. Bee enruta únicamente por URL (controlador/método/
 * parámetros, ver app/classes/Bee.php::init_set_defaults()): un método
 * post_xyz() es perfectamente alcanzable por GET con sólo visitar esa URL,
 * el framework no distingue el verbo HTTP a nivel de enrutamiento. Todo
 * método post_* debe invocar esta función al inicio para rechazar accesos
 * que no sean por POST.
 *
 * @return bool
 */
function requiere_metodo_post()
{
  if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    Flasher::deny(1); // 'Acción no autorizada.'
    Redirect::back();
  }

  return true;
}

/**
 * Registra un movimiento o consulta de un administrador/súper usuario en la
 * bitácora de auditoría (RF-09, RF-12, RNF-01).
 *
 * Ajustado a docs/DDL/ddl.sql: `auditoria.usuario_id` y `auditoria.entidad`
 * son NOT NULL, y no existe columna `creado` (created_at ya tiene DEFAULT
 * CURRENT_TIMESTAMP, no hace falta enviarlo). `ip` sí existe (VARCHAR(45)
 * NULL, para trazabilidad forense sobre datos identificados/sensibles,
 * RNF-01) y es NULLABLE: si no se puede determinar, se envía `null` y el
 * insert no falla. Se captura internamente aquí, NO se recibe como
 * parámetro — no hay que pasarla al llamar la función.
 *
 * IP: se usa $_SERVER['REMOTE_ADDR'] directamente (la conexión TCP real),
 * NO get_user_ip() de bee_core_functions.php, porque esa función confía a
 * ciegas en cabeceras que el cliente puede falsificar (X-Forwarded-For,
 * X-Forwarded, Client-IP) cuando no hay proxy de por medio.
 * TODO (producción): si el sistema queda detrás de un proxy/reverse proxy
 * (load balancer, Cloudflare, nginx como frontend, etc.), REMOTE_ADDR pasará
 * a ser la IP del proxy, no la del cliente. En ese momento hay que resolver
 * la IP real a partir de X-Forwarded-For (o el header que use ese proxy),
 * pero SÓLO confiando en el valor si la petición viene de una lista cerrada
 * de proxies de confianza — nunca tomar ese header a ciegas de cualquier
 * origen, es trivialmente falsificable por el cliente.
 *
 * Si no hay un usuario de enlace válido en sesión (usuarioModel), NO se
 * intenta el insert — fallaría la restricción NOT NULL de usuario_id — se
 * falla en silencio y se regresa false; en la práctica no debería ocurrir
 * porque requiere_rol() ya bloquea el acceso antes de llegar aquí en
 * administradorController/superusuarioController.
 *
 * TODO (fase de Desarrollo): llamar a esta función desde cada controlador o
 * método sensible (administradorController, superusuarioController,
 * resultadosController) en el momento en que ocurre la acción.
 *
 * @param string $accion    Catálogo de acciones, ej. 'login', 'alta_centro_trabajo', 'generar_token', 'consulta_resultado_individual'
 * @param string $entidad   Entidad afectada, ej. 'centro_trabajo', 'token', 'resultado' — NOT NULL en la BD
 * @param mixed  $entidadId  ID de la entidad afectada
 * @param string|null $detalle Detalle adicional en texto libre
 * @return bool
 */
function registrar_auditoria(string $accion, string $entidad, $entidadId = null, string $detalle = null)
{
  $usuario = obtener_usuario_actual();

  if (empty($usuario['id'])) {
    return false;
  }

  $data =
  [
    'usuario_id' => $usuario['id'], // FK -> usuario.id (tabla de enlace), NO bee_users.id — ver decisión de diseño A
    'accion'     => $accion,
    'entidad'    => $entidad,
    'entidad_id' => $entidadId,
    'detalle'    => $detalle,
    'ip'         => $_SERVER['REMOTE_ADDR'] ?? null // opcional: la columna es NULL, no hay problema si no está disponible
  ];

  return auditoriaModel::insertOne($data);
}