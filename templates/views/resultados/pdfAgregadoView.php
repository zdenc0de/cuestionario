<?php
/**
 * Plantilla de contenido para el PDF de resultado agregado por centro de trabajo.
 *
 * NO se renderiza con View::render(); su HTML se captura con
 * ob_start()/ob_get_clean() en resultadosController::pdf_agregado() y se
 * pasa como $content a BeePdf (Dompdf). CSS inline o <style> embebido.
 *
 * Variables esperadas ($d ya viene poblada por resultadosController):
 *   $d->centro_trabajo    -- centroTrabajoModel::by_id()
 *   $d->resultados        -- resultadoModel::agregado_por_centro_trabajo()
 *
 * TODO (fase de Desarrollo): construir el reporte real con estadísticos
 * agregados (promedios, distribución por nivel de riesgo, desglose por
 * dominio/categoría), usando la identidad visual de la Secretaría.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: Arial, sans-serif; color: #000000; }
    h1   { color: #9F2241; } /* --color-primario */
    .marca { color: #965F36; } /* --color-cafe */
  </style>
</head>
<body>
  <h1>Resultado agregado &mdash; NOM-035-STPS-2018</h1>
  <p class="marca">Secretaría de Cultura y Turismo del Estado de México</p>

  <!-- TODO: tabla/resumen con estadísticos agregados del centro de trabajo -->
  <p>Pendiente de implementación (fase de Desarrollo).</p>
</body>
</html>
