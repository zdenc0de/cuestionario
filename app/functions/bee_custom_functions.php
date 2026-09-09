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
    Redirect::to(DEFAULT_CONTROLLER);
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
 * TODO (fase de Desarrollo): llamar a esta función desde cada controlador o
 * método sensible (administradorController, superusuarioController,
 * resultadosController) en el momento en que ocurre la acción.
 *
 * @param string $accion    Catálogo de acciones, ej. 'login', 'alta_centro_trabajo', 'generar_token', 'consulta_resultado_individual'
 * @param string|null $entidad     Entidad afectada, ej. 'centro_trabajo', 'token', 'resultado'
 * @param mixed  $entidadId  ID de la entidad afectada
 * @param string|null $detalle Detalle adicional en texto libre
 * @return bool
 */
function registrar_auditoria(string $accion, string $entidad = null, $entidadId = null, string $detalle = null)
{
  $usuario = obtener_usuario_actual();

  $data =
  [
    'usuario_id' => $usuario['id'] ?? null, // FK -> usuario.id (tabla de enlace), NO bee_users.id — ver decisión de diseño A
    'accion'     => $accion,
    'entidad'    => $entidad,
    'entidad_id' => $entidadId,
    'detalle'    => $detalle,
    'ip'         => get_user_ip(),
    'creado'     => now()
  ];

  return auditoriaModel::insertOne($data);
}