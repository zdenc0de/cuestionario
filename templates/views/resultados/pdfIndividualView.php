<?php
/**
 * Plantilla de contenido para el PDF de resultado individual.
 *
 * NO se renderiza con View::render() (no lleva header.php/footer.php del
 * sitio); su HTML se captura con ob_start()/ob_get_clean() en
 * resultadosController::pdf_individual() y se pasa como $content a BeePdf,
 * que internamente usa Dompdf. Por ello el CSS debe ser inline o <style>
 * embebido (Dompdf no carga hojas de estilo externas por defecto).
 *
 * Variables esperadas ($d ya viene poblada por resultadosController):
 *   $d->resultado         -- resultadoModel::por_aplicacion()
 *   $d->aplicacion        -- aplicacionModel::by_id()
 *
 * TODO (fase de Desarrollo): construir el reporte real con la calificación
 * final, nivel de riesgo y desglose por dominio/categoría, usando la
 * identidad visual de la Secretaría (ver /assets/css/nom035-variables.css).
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
  <h1>Resultado individual &mdash; NOM-035-STPS-2018</h1>
  <p class="marca">Secretaría de Cultura y Turismo del Estado de México</p>

  <!-- TODO: tabla con calificación final, nivel de riesgo y desglose -->
  <p>Pendiente de implementación (fase de Desarrollo).</p>
</body>
</html>
