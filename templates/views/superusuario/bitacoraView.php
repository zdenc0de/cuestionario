<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<div class="row">
  <div class="col-12">
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Bitácora de auditoría</h6>
      </div>
      <div class="card-body">
        <!-- TODO (fase de Desarrollo): tabla con $d->bitacora (auditoriaModel::all_paginated()) -->
        <div class="table-responsive">
          <table class="table table-bordered" width="100%">
            <thead>
              <tr>
                <th>Usuario</th>
                <th>Acción</th>
                <th>Entidad</th>
                <th>Detalle</th>
                <th>Fecha</th>
              </tr>
            </thead>
            <tbody>
              <!-- TODO: foreach ($d->bitacora as $registro): ... endforeach; -->
              <tr>
                <td colspan="5" class="text-center text-muted">Sin registros (pendiente de implementación).</td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- TODO: paginación con PaginationHandler (ver adminController/usuariosView.php) -->
      </div>
    </div>
  </div>
</div>

<?php require_once INCLUDES . 'admin/dashboardBottom.php'; ?>
