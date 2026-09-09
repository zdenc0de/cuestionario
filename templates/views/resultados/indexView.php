<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<!-- Página de entrada del módulo de resultados y reportes (RF-13, RF-14) -->
<div class="row">
  <div class="col-12 col-md-4 mb-4">
    <div class="card shadow h-100">
      <div class="card-body">
        <h6 class="text-primary text-uppercase mb-2">Resultado agregado</h6>
        <p class="small text-muted">Por centro de trabajo.</p>
        <!-- TODO: enlazar con selector de centro de trabajo -->
      </div>
    </div>
  </div>

  <div class="col-12 col-md-4 mb-4">
    <div class="card shadow h-100">
      <div class="card-body">
        <h6 class="text-primary text-uppercase mb-2">Tablero con gráficas</h6>
        <a href="<?php echo get_base_url(); ?>resultados/tablero" class="btn btn-outline-primary btn-sm">Ver tablero</a>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-4 mb-4">
    <div class="card shadow h-100">
      <div class="card-body">
        <h6 class="text-primary text-uppercase mb-2">Reportes</h6>
        <p class="small text-muted">PDF y exportación a Excel disponibles desde cada resultado.</p>
      </div>
    </div>
  </div>
</div>

<?php require_once INCLUDES . 'admin/dashboardBottom.php'; ?>
