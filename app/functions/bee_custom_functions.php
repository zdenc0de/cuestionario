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
  // TODO: reemplazar get_user('id') si la estructura final de sesión de usuario difiere
  $data =
  [
    'usuario_id' => get_user('id'),
    'accion'     => $accion,
    'entidad'    => $entidad,
    'entidad_id' => $entidadId,
    'detalle'    => $detalle,
    'ip'         => get_user_ip(),
    'creado'     => now()
  ];

  return auditoriaModel::insertOne($data);
}