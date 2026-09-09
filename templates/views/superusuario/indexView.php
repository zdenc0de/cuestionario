<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<!--
  Tablero principal del SÚPER USUARIO (por Secretaría).
  TODO (fase de Desarrollo): resumen de administradores dados de alta y
  actividad reciente en la bitácora de auditoría.
-->
<div class="row">
  <div class="col-12 col-md-6 mb-4">
    <div class="card shadow h-100">
      <div class="card-body">
        <h6 class="text-primary text-uppercase mb-2">Administradores</h6>
        <p class="small text-muted mb-2">Alta y administración de los administradores de la Secretaría.</p>
        <a href="<?php echo get_base_url(); ?>superusuario/administradores" class="btn btn-outline-primary btn-sm">Administrar</a>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-6 mb-4">
    <div class="card shadow h-100">
      <div class="card-body">
        <h6 class="text-primary text-uppercase mb-2">Bitácora de auditoría</h6>
        <p class="small text-muted mb-2">Movimientos y consultas de los administradores.</p>
        <a href="<?php echo get_base_url(); ?>superusuario/bitacora" class="btn btn-outline-primary btn-sm">Consultar</a>
      </div>
    </div>
  </div>
</div>

<?php require_once INCLUDES . 'admin/dashboardBottom.php'; ?>
