<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<div class="row">
  <div class="col-12 mb-2">
    <p class="text-muted mb-0">
      Centro de trabajo: <strong><?php echo isset($d->centro->nombre) ? htmlspecialchars($d->centro->nombre) : ''; ?></strong>
      (<?php echo isset($d->centro->num_trabajadores) ? (int) $d->centro->num_trabajadores : '?'; ?> trabajadores)
    </p>
  </div>

  <!-- Generación de un nuevo token de acceso (RF-10) -->
  <div class="col-12 col-md-6 col-lg-4">
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Generar token</h6>
      </div>
      <div class="card-body">
        <form action="<?php echo get_base_url(); ?>administrador/post_tokens" method="post">
          <?php echo insert_inputs(); ?>
          <input type="hidden" name="centro_trabajo_id" value="<?php echo isset($d->centro_trabajo_id) ? htmlspecialchars($d->centro_trabajo_id) : ''; ?>">

          <div class="mb-3">
            <label for="fecha_inicio" class="form-label">Vigencia desde <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo date('Y-m-d'); ?>" required>
          </div>

          <div class="mb-3">
            <label for="fecha_fin" class="form-label">Vigencia hasta <span class="text-danger">*</span></label>
            <!-- 30 días por default (ver TODO de fase de Diseño en tokenModel.php); el administrador puede ajustarla -->
            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" required>
            <div class="form-text">Debe ser igual o posterior a la fecha de inicio.</div>
          </div>

          <button class="btn btn-success btn-lg w-100" type="submit">Generar token</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Listado de tokens del centro de trabajo -->
  <div class="col-12 col-md-6 col-lg-8">
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Tokens del centro de trabajo</h6>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered" width="100%">
            <thead>
              <tr>
                <th>Código</th>
                <th>Vigencia inicio</th>
                <th>Vigencia fin</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($d->tokens)): ?>
                <tr>
                  <td colspan="5" class="text-center text-muted">Aún no se ha generado ningún token para este centro.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($d->tokens as $token): ?>
                  <tr>
                    <td><code><?php echo htmlspecialchars($token->codigo); ?></code></td>
                    <td><?php echo htmlspecialchars($token->fecha_inicio); ?></td>
                    <td><?php echo htmlspecialchars($token->fecha_fin); ?></td>
                    <td>
                      <?php if ($token->estado === 'activo'): ?>
                        <span class="badge bg-success">Activo</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">Inactivo</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if ($token->estado === 'activo'): ?>
                        <!-- Patrón de Bee: enlace GET + token CSRF en query string (ver adminController::borrar_usuario()) -->
                        <a href="<?php echo get_base_url(); ?>administrador/revocar_token/<?php echo $token->id; ?>?_t=<?php echo CSRF_TOKEN; ?>"
                           class="btn btn-sm btn-outline-danger confirmar">
                          Revocar
                        </a>
                      <?php else: ?>
                        <span class="text-muted small">—</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once INCLUDES . 'admin/dashboardBottom.php'; ?>
