<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<!--
  Tablero en pantalla con gráficas (RF-14).
  TODO (fase de Desarrollo): consumir el endpoint resultados/datos-grafica/{centroTrabajoId}
  (JSON) y graficar con Chart.js (ya cargado, ver resultadosController::tablero())
  o con BeeQuickChart si se prefiere generar imágenes estáticas.
-->
<div class="row">
  <div class="col-12 col-md-6 mb-4">
    <div class="card shadow">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Distribución por nivel de riesgo</h6>
      </div>
      <div class="card-body">
        <canvas id="graficaNivelRiesgo"></canvas>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-6 mb-4">
    <div class="card shadow">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Promedio por dominio</h6>
      </div>
      <div class="card-body">
        <canvas id="graficaDominios"></canvas>
      </div>
    </div>
  </div>
</div>

<script>
  // TODO (fase de Desarrollo): fetch('<?php echo get_base_url(); ?>resultados/datos-grafica/{centroTrabajoId}')
  //   .then(r => r.json())
  //   .then(payload => { /* construir Chart.js con payload.data */ });
</script>

<?php require_once INCLUDES . 'admin/dashboardBottom.php'; ?>
