<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<!--
  Resultado agregado por centro de trabajo (RF-13).
  TODO (fase de Desarrollo): renderizar estadísticos agregados a partir de
  resultadoModel::agregado_por_centro_trabajo() (promedios, distribución por
  nivel de riesgo, desglose por dominio/categoría).
-->
<div class="row">
  <div class="col-12 mb-3 d-flex justify-content-end gap-2">
    <a href="<?php echo get_base_url(); ?>resultados/pdf-agregado/<?php echo isset($d->centro_trabajo_id) ? htmlspecialchars($d->centro_trabajo_id) : ''; ?>" class="btn btn-danger btn-sm">
      <i class="fas fa-file-pdf"></i> Descargar PDF
    </a>
    <a href="<?php echo get_base_url(); ?>resultados/excel/<?php echo isset($d->centro_trabajo_id) ? htmlspecialchars($d->centro_trabajo_id) : ''; ?>" class="btn btn-success btn-sm">
      <i class="fas fa-file-excel"></i> Exportar a Excel
    </a>
  </div>

  <div class="col-12">
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Resumen del centro de trabajo</h6>
      </div>
      <div class="card-body">
        <p class="text-muted">Pendiente de implementación (fase de Desarrollo).</p>
      </div>
    </div>
  </div>
</div>

<?php require_once INCLUDES . 'admin/dashboardBottom.php'; ?>
