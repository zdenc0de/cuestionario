<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<!--
  Resultado individual de una aplicación/encuestado (RF-13).
  TODO (fase de Desarrollo): renderizar $d->resultado (resultadoModel::por_aplicacion())
  con calificación final, nivel de riesgo y desglose por dominio/categoría.
-->
<div class="row">
  <div class="col-12 mb-3 d-flex justify-content-end gap-2">
    <a href="<?php echo get_base_url(); ?>resultados/pdf-individual/<?php echo isset($d->aplicacion_id) ? htmlspecialchars($d->aplicacion_id) : ''; ?>" class="btn btn-danger btn-sm">
      <i class="fas fa-file-pdf"></i> Descargar PDF
    </a>
  </div>

  <div class="col-12">
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Calificación final</h6>
      </div>
      <div class="card-body">
        <!-- TODO: mostrar $d->resultado->calificacion_final y $d->resultado->nivel_riesgo -->
        <p class="text-muted">Pendiente de implementación (fase de Desarrollo).</p>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Desglose por dominio y categoría</h6>
      </div>
      <div class="card-body">
        <!-- TODO: tabla/gráfica con el desglose de $d->resultado->desglose_dominio_json y desglose_categoria_json -->
        <p class="text-muted">Pendiente de implementación (fase de Desarrollo).</p>
      </div>
    </div>
  </div>
</div>

<?php require_once INCLUDES . 'admin/dashboardBottom.php'; ?>
