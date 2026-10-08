<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header card-header-institucional">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
          <div>
            <h2 class="h5 mb-0 font-weight-bold">
              Bitácora de auditoría de tu secretaría
            </h2>
            <small class="text-muted">
              Registro de movimientos y consultas realizadas por los usuarios de tu secretaría.
            </small>
          </div>
          <span class="badge bg-guinda text-white">
            <?php echo count($d->bitacora); ?> registro(s)
          </span>
        </div>
      </div>
      <div class="card-body p-0">
        <?php if (empty($d->bitacora)): ?>
          <div class="text-center text-muted py-5">
            <p class="mb-0">Sin movimientos registrados todavía.</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="border-top-0">Usuario</th>
                  <th class="border-top-0">Acción</th>
                  <th class="border-top-0">Entidad</th>
                  <th class="border-top-0">Detalle</th>
                  <th class="border-top-0">IP</th>
                  <th class="border-top-0">Fecha</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($d->bitacora as $registro): ?>
                  <tr>
                    <td class="align-middle">
                      <?php echo htmlspecialchars($registro->username); ?>
                    </td>
                    <td class="align-middle">
                      <?php echo htmlspecialchars($registro->accion); ?>
                    </td>
                    <td class="align-middle">
                      <?php echo htmlspecialchars($registro->entidad); ?>
                      <?php if (!empty($registro->entidad_id)): ?>
                        <span class="text-muted">#<?php echo (int) $registro->entidad_id; ?></span>
                      <?php endif; ?>
                    </td>
                    <td class="align-middle text-muted small">
                      <?php echo htmlspecialchars($registro->detalle ?? '—'); ?>
                    </td>
                    <td class="align-middle text-muted small">
                      <?php echo htmlspecialchars($registro->ip ?? '—'); ?>
                    </td>
                    <td class="align-middle text-muted small">
                      <?php echo htmlspecialchars($registro->created_at); ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once INCLUDES . 'admin/dashboardBottom.php'; ?>