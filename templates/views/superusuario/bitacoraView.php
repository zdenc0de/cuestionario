<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<div class="row">
  <div class="col-12">
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Bitácora de auditoría de tu secretaría</h6>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered" width="100%">
            <thead>
              <tr>
                <th>Usuario</th>
                <th>Acción</th>
                <th>Entidad</th>
                <th>Detalle</th>
                <th>IP</th>
                <th>Fecha</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($d->bitacora)): ?>
                <tr>
                  <td colspan="6" class="text-center text-muted">Sin movimientos registrados todavía.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($d->bitacora as $registro): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($registro->username); ?></td>
                    <td><?php echo htmlspecialchars($registro->accion); ?></td>
                    <td>
                      <?php echo htmlspecialchars($registro->entidad); ?>
                      <?php if (!empty($registro->entidad_id)): ?>
                        <span class="text-muted">#<?php echo (int) $registro->entidad_id; ?></span>
                      <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($registro->detalle ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($registro->ip ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($registro->created_at); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <!-- TODO (fase de Desarrollo): paginación con PaginationHandler si la bitácora crece mucho (ver adminController/usuariosView.php) -->
      </div>
    </div>
  </div>
</div>

<?php require_once INCLUDES . 'admin/dashboardBottom.php'; ?>
