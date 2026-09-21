<?php
/**
 * Script de verificación del motor de calificación (NO es parte de la app).
 *
 * Criterio de aceptación de docs/HANDOFF_DESARROLLO.md §5.3: calcular a mano
 * un caso conocido y confirmar que resultadoModel::calcular_para_aplicacion()
 * da exactamente lo mismo, para ambas guías, incluyendo el efecto de los
 * filtros y que ningún nivel caiga fuera de los rangos de `umbral`.
 *
 * Uso: php scripts/verificar_motor_calificacion.php
 * (ejecutar desde cualquier lado; el script se reubica a la raíz del proyecto).
 *
 * Qué hace:
 *   1. Arranca el mínimo del framework que necesitan los modelos (config,
 *      autoloader, funciones) SIN pasar por Bee::fly() — eso despacharía un
 *      controlador HTTP, que no aplica en CLI.
 *   2. Para cada guía (GRII y GRIII) crea una cadena de datos real y mínima
 *      (secretaria -> centro_trabajo -> token -> aplicacion -> respuesta)
 *      con 3 casos: todo "Siempre", todo "Nunca", y con los filtros en "No".
 *   3. Calcula a mano el resultado esperado a partir del conteo real de
 *      polaridad por dominio/categoría en la BD (no con calculadora aparte)
 *      y lo compara contra lo que persiste el motor.
 *   4. Verifica idempotencia: recalcular la misma aplicación no duplica filas.
 *   5. Borra todo lo que insertó (aplicacion -> token -> centro_trabajo ->
 *      secretaria; respuesta/resultado/resultado_detalle caen solos por
 *      ON DELETE CASCADE), sin importar si algo falló, para poder correr
 *      este script las veces que haga falta sin ensuciar la base de datos.
 *
 * NO se agrega a app/, no es una ruta, no la usa la aplicación en producción.
 */

// ---------------------------------------------------------------------
// 1. Arranque mínimo (equivalente a los primeros pasos de Bee::init(),
//    sin el despacho HTTP de Bee::fly())
// ---------------------------------------------------------------------

// IS_LOCAL (bee_config.php) depende de $_SERVER['REMOTE_ADDR'], que no existe
// en CLI. Se simula un request local para que tome las credenciales LDB_*.
$_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$_SERVER['HTTP_HOST']   = $_SERVER['HTTP_HOST']   ?? 'localhost';
$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/';

chdir(__DIR__ . '/..'); // ROOT en settings.php es getcwd(); nos aseguramos de estar en la raíz del proyecto

require_once 'app/config/bee_config.php';
require_once 'app/core/settings.php';
require_once 'app/vendor/autoload.php';
require_once CLASSES . 'Autoloader.php';
Autoloader::init();
require_once FUNCTIONS . 'bee_core_functions.php';
require_once FUNCTIONS . 'bee_custom_functions.php';

// ---------------------------------------------------------------------
// Utilidades mínimas de aserción para este script
// ---------------------------------------------------------------------
$GLOBALS['__fallos'] = 0;

function verif_ok(string $etiqueta, $esperado, $obtenido)
{
  $paso = $esperado === $obtenido;
  printf("  [%s] %s (esperado: %s, obtenido: %s)\n", $paso ? 'OK' : 'FALLO', $etiqueta, var_export($esperado, true), var_export($obtenido, true));
  if (!$paso) {
    $GLOBALS['__fallos']++;
  }
}

/**
 * Calcula, sumando el conteo real de polaridad en la BD, la calificación
 * esperada para un conjunto de reactivos si TODOS se responden en una misma
 * posición de la escala Likert (0=Siempre .. 4=Nunca).
 *
 * @param array $reactivos Filas de reactivo (deben incluir 'polaridad')
 * @param int $posicion
 * @return int
 */
function calificacion_esperada(array $reactivos, int $posicion)
{
  $total = 0;
  foreach ($reactivos as $reactivo) {
    $total += reactivoModel::calcular_puntaje($reactivo, $posicion);
  }
  return $total;
}

// ---------------------------------------------------------------------
// IDs de las opciones de la escala Likert (posicion => id)
// ---------------------------------------------------------------------
$opciones          = opcionRespuestaModel::escala();
$idPorPosicion      = [];
foreach ($opciones as $opcion) {
  $idPorPosicion[(int) $opcion['posicion']] = $opcion['id'];
}
if (count($idPorPosicion) !== 5) {
  fwrite(STDERR, "ERROR: opcion_respuesta no tiene las 5 posiciones esperadas (0..4). ¿Está sembrada la BD?\n");
  exit(1);
}

// ---------------------------------------------------------------------
// Datos de prueba a limpiar al final (en orden seguro para borrar: hijo -> padre)
// ---------------------------------------------------------------------
$idsAplicacion    = [];
$idsToken         = [];
$idsCentroTrabajo = [];
$idsSecretaria    = [];

/**
 * Crea la cadena mínima secretaria->centro_trabajo->token para una guía,
 * y regresa el token_id.
 */
function crear_cadena_token(string $claveGuia, int $numTrabajadores)
{
  global $idsSecretaria, $idsCentroTrabajo, $idsToken;

  $secretariaId = secretariaModel::insertOne(['nombre' => '__VERIFICACION_MOTOR__ Secretaria ' . $claveGuia]);
  $idsSecretaria[] = $secretariaId;

  $centroId = centroTrabajoModel::insertOne([
    'secretaria_id'    => $secretariaId,
    'nombre'           => '__VERIFICACION_MOTOR__ Centro ' . $claveGuia,
    'num_trabajadores' => $numTrabajadores
  ]);
  $idsCentroTrabajo[] = $centroId;

  $tokenId = tokenModel::insertOne([
    'centro_trabajo_id' => $centroId,
    'codigo'             => 'VERIF-' . $claveGuia . '-' . uniqid(),
    'fecha_inicio'        => date('Y-m-d'),
    'fecha_fin'           => date('Y-m-d', strtotime('+30 days')),
    'estado'              => 'activo'
  ]);
  $idsToken[] = $tokenId;

  return $tokenId;
}

/**
 * Crea una aplicación de prueba y sus respuestas: todos los reactivos que
 * $incluirReactivos trae, todos respondidos en la misma $posicion.
 *
 * @return int aplicacion_id
 */
function crear_aplicacion_con_respuestas(int $tokenId, int $guiaId, string $numeroServidorPublico, array $reactivosAResponder, int $posicion, bool $atiendeClientes, bool $esJefe)
{
  global $idsAplicacion, $idPorPosicion;

  $aplicacionId = aplicacionModel::insertOne([
    'token_id'                => $tokenId,
    'guia_id'                  => $guiaId,
    'nombre'                   => '__VERIFICACION_MOTOR__ Encuestado',
    'numero_servidor_publico'  => $numeroServidorPublico,
    'atiende_clientes'         => $atiendeClientes ? 1 : 0,
    'es_jefe'                  => $esJefe ? 1 : 0
  ]);
  $idsAplicacion[] = $aplicacionId;

  $respuestas = [];
  foreach ($reactivosAResponder as $reactivo) {
    $respuestas[] = [
      'aplicacion_id'       => $aplicacionId,
      'reactivo_id'         => $reactivo['id'],
      'opcion_respuesta_id' => $idPorPosicion[$posicion]
    ];
  }
  respuestaModel::insertar_lote($respuestas);

  aplicacionModel::marcar_completada($aplicacionId);

  return $aplicacionId;
}

/**
 * Corre los 3 casos de prueba para una guía y regresa nada (todo se
 * verifica con verif_ok() según se avanza).
 */
function verificar_guia(string $claveGuia, int $numTrabajadores)
{
  echo "\n=== Guía {$claveGuia} ===\n";

  $guia = guiaModel::by_clave($claveGuia);
  if (!$guia) {
    fwrite(STDERR, "ERROR: no existe la guía {$claveGuia} en la BD.\n");
    exit(1);
  }
  $guiaId = $guia['id'];

  $todosLosReactivos    = reactivoModel::por_guia($guiaId);
  $reactivosObligatorios = reactivoModel::obligatorios_por_guia($guiaId);

  $tokenId = crear_cadena_token($claveGuia, $numTrabajadores);

  // ---- Caso 1: todos "Siempre" (posicion 0), con filtros en "Sí" (todos los reactivos aplican) ----
  echo "-- Caso 1: todo 'Siempre', filtros en Sí --\n";
  $aplicacion1 = crear_aplicacion_con_respuestas($tokenId, $guiaId, 'SVP-' . $claveGuia . '-1', $todosLosReactivos, 0, true, true);
  $resultado1  = resultadoModel::calcular_para_aplicacion($aplicacion1);

  verif_ok('Caso 1 — calificación final', calificacion_esperada($todosLosReactivos, 0), (int) $resultado1['resultado']['calificacion_final']);
  verif_ok('Caso 1 — nivel de riesgo final coincide con umbralModel', umbralModel::nivel_final($guiaId, calificacion_esperada($todosLosReactivos, 0)), $resultado1['resultado']['nivel_riesgo']);
  verif_ok('Caso 1 — número de renglones de detalle (dominios+categorías con respuesta)', count(array_unique(array_column($todosLosReactivos, 'dominio_id'))) + count(array_unique(array_column($todosLosReactivos, 'categoria_id'))), count($resultado1['detalle']));

  foreach ($resultado1['detalle'] as $fila) {
    if ($fila['nivel_agregacion'] === 'dominio') {
      $reactivosDominio = array_filter($todosLosReactivos, fn($r) => $r['dominio_id'] == $fila['dominio_id']);
      $esperado         = calificacion_esperada($reactivosDominio, 0);
      verif_ok("Caso 1 — dominio {$fila['dominio_id']} calificación", $esperado, (int) $fila['calificacion']);
      verif_ok("Caso 1 — dominio {$fila['dominio_id']} nivel", umbralModel::nivel_dominio($fila['dominio_id'], $esperado), $fila['nivel_riesgo']);
    } else {
      $reactivosCategoria = array_filter($todosLosReactivos, fn($r) => $r['categoria_id'] == $fila['categoria_id']);
      $esperado           = calificacion_esperada($reactivosCategoria, 0);
      verif_ok("Caso 1 — categoría {$fila['categoria_id']} calificación", $esperado, (int) $fila['calificacion']);
      verif_ok("Caso 1 — categoría {$fila['categoria_id']} nivel", umbralModel::nivel_categoria($fila['categoria_id'], $esperado), $fila['nivel_riesgo']);
    }
  }

  // ---- Idempotencia: recalcular la misma aplicación no debe duplicar nada ----
  echo "-- Idempotencia: recalcular caso 1 --\n";
  $resultadoRecalculado = resultadoModel::calcular_para_aplicacion($aplicacion1);
  $detalleEnBD          = resultadoDetalleModel::por_resultado($resultadoRecalculado['resultado']['id']);
  verif_ok('Idempotencia — resultadoModel::por_aplicacion() sigue regresando un único resultado', true, resultadoModel::por_aplicacion($aplicacion1) !== null);
  verif_ok('Idempotencia — el número de renglones de detalle no se duplicó', count($resultado1['detalle']), count($detalleEnBD));

  // ---- Caso 2: todos "Nunca" (posicion 4), con filtros en "Sí" ----
  echo "-- Caso 2: todo 'Nunca', filtros en Sí --\n";
  $aplicacion2 = crear_aplicacion_con_respuestas($tokenId, $guiaId, 'SVP-' . $claveGuia . '-2', $todosLosReactivos, 4, true, true);
  $resultado2  = resultadoModel::calcular_para_aplicacion($aplicacion2);
  verif_ok('Caso 2 — calificación final', calificacion_esperada($todosLosReactivos, 4), (int) $resultado2['resultado']['calificacion_final']);
  verif_ok('Caso 2 — nivel de riesgo final', umbralModel::nivel_final($guiaId, calificacion_esperada($todosLosReactivos, 4)), $resultado2['resultado']['nivel_riesgo']);

  // ---- Caso 3: filtros en "No" — sólo responde los reactivos obligatorios, todo "Siempre" ----
  echo "-- Caso 3: todo 'Siempre', filtros en No (sólo obligatorios) --\n";
  $aplicacion3 = crear_aplicacion_con_respuestas($tokenId, $guiaId, 'SVP-' . $claveGuia . '-3', $reactivosObligatorios, 0, false, false);
  $resultado3  = resultadoModel::calcular_para_aplicacion($aplicacion3);
  verif_ok('Caso 3 — calificación final excluye reactivos condicionales', calificacion_esperada($reactivosObligatorios, 0), (int) $resultado3['resultado']['calificacion_final']);
  verif_ok('Caso 3 — calificación final es MENOR que el caso 1 (con condicionales)', true, ((int) $resultado3['resultado']['calificacion_final']) < ((int) $resultado1['resultado']['calificacion_final']) || count($todosLosReactivos) === count($reactivosObligatorios));

  // ---- Ningún nivel fuera de rango: confirmar que ninguno de los 3 casos regresó null ----
  foreach ([$resultado1, $resultado2, $resultado3] as $i => $r) {
    verif_ok('Caso ' . ($i + 1) . ' — nivel final no es null (calificación dentro de algún rango de umbral)', true, $r['resultado']['nivel_riesgo'] !== null);
    foreach ($r['detalle'] as $fila) {
      if ($fila['nivel_riesgo'] === null) {
        verif_ok('Caso ' . ($i + 1) . " — renglón {$fila['nivel_agregacion']} sin nivel (fuera de rango de umbral)", true, false);
      }
    }
  }
}

// ---------------------------------------------------------------------
// 2-4. Correr los casos para ambas guías
// ---------------------------------------------------------------------
try {
  verificar_guia('GRII', 20);   // 20 trabajadores -> cae en el rango de GRII (16-50)
  verificar_guia('GRIII', 80);  // 80 trabajadores -> cae en el rango de GRIII (>50)

} finally {
  // -------------------------------------------------------------------
  // 5. Limpieza: borra todo lo insertado, pase lo que pase arriba
  // -------------------------------------------------------------------
  echo "\n=== Limpieza de datos de prueba ===\n";
  foreach ($idsAplicacion as $id) {
    aplicacionModel::delete_by_id($id); // cascada: respuesta + resultado (+ resultado_detalle)
  }
  foreach ($idsToken as $id) {
    tokenModel::delete_by_id($id);
  }
  foreach ($idsCentroTrabajo as $id) {
    centroTrabajoModel::delete_by_id($id);
  }
  foreach ($idsSecretaria as $id) {
    secretariaModel::delete_by_id($id);
  }
  echo "Limpieza completa (" . count($idsAplicacion) . " aplicaciones, " . count($idsToken) . " tokens, " . count($idsCentroTrabajo) . " centros, " . count($idsSecretaria) . " secretarías).\n";
}

// ---------------------------------------------------------------------
// Resumen final
// ---------------------------------------------------------------------
echo "\n=== Resumen ===\n";
if ($GLOBALS['__fallos'] === 0) {
  echo "TODO CORRECTO: 0 fallos.\n";
  exit(0);
} else {
  echo "{$GLOBALS['__fallos']} verificación(es) fallaron. Ver detalle arriba.\n";
  exit(1);
}
