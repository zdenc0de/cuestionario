<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<!--
  Tablero principal del ADMINISTRADOR (por centro de trabajo).
  TODO (fase de Desarrollo): resumen de sus centros de trabajo, tokens
  activos/usados y avance de aplicación del cuestionario.
-->
<div class="row">
  <div class="col-12 col-md-4 mb-4">
    <div class="card shadow h-100">
      <div class="card-body">
        <h6 class="text-primary text-uppercase mb-2">Centros de trabajo</h6>
        <a href="<?php echo get_base_url(); ?>administrador/centros-trabajo" class="btn btn-outline-primary btn-sm">Administrar</a>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-4 mb-4">
    <div class="card shadow h-100">
      <div class="card-body">
        <h6 class="text-primary text-uppercase mb-2">Tokens de acceso</h6>
        <p class="small text-muted mb-2">Genera y consulta la vigencia de los tokens de tus centros de trabajo.</p>
        <!-- TODO: enlazar al centro de trabajo correspondiente -->
      </div>
    </div>
  </div>

  <div class="col-12 col-md-4 mb-4">
    <div class="card shadow h-100">
      <div class="card-body">
        <h6 class="text-primary text-uppercase mb-2">Resultados</h6>
        <a href="<?php echo get_base_url(); ?>resultados" class="btn btn-outline-primary btn-sm">Ver resultados</a>
      </div>
    </div>
  </div>
</div>

<?php require_once INCLUDES . 'admin/dashboardBottom.php'; ?>
