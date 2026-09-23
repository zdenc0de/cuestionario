<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<div class="row">
  <!-- Alta de administrador (RF-09) -->
  <div class="col-12 col-md-6 col-lg-6 col-xl-4">
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Agregar administrador</h6>
      </div>
      <div class="card-body">
        <form action="<?php echo get_base_url(); ?>superusuario/post_administradores" method="post">
          <?php echo insert_inputs(); ?>

          <div class="mb-3">
            <label for="username" class="form-label">Nombre de usuario <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="username" name="username" required>
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="email" name="email" required>
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
            <input type="password" class="form-control" id="password" name="password" required>
          </div>

          <button class="btn btn-success btn-lg w-100" type="submit">Agregar administrador</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Listado de administradores -->
  <div class="col-12 col-md-6 col-lg-6 col-xl-8">
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Administradores de tu secretaría</h6>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered" width="100%">
            <thead>
              <tr>
                <th>Usuario</th>
                <th>Correo</th>
                <th>Alta</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($d->administradores)): ?>
                <tr>
                  <td colspan="4" class="text-center text-muted">Aún no hay administradores dados de alta.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($d->administradores as $admin): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($admin->username); ?></td>
                    <td><?php echo htmlspecialchars($admin->email); ?></td>
                    <td><?php echo htmlspecialchars($admin->created_at); ?></td>
                    <td>
                      <!-- Patrón de Bee: enlace GET + token CSRF en query string (ver adminController::borrar_usuario()) -->
                      <a href="<?php echo get_base_url(); ?>superusuario/borrar_administrador/<?php echo $admin->id; ?>?_t=<?php echo CSRF_TOKEN; ?>"
                         class="btn btn-sm btn-outline-danger confirmar"
                         title="Revoca el acceso (no borra su historial de auditoría, ver docblock de borrar_administrador())">
                        Revocar acceso
                      </a>
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
