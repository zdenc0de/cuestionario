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
      <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="m-0 font-weight-bold text-primary">Administradores de tu secretaría</h6>
        <!-- Filtro por estado (columna nueva, ver scripts/alter_usuario_estado.sql;
             si todavía no se aplicó el ALTER, el controlador ignora el filtro y muestra todos) -->
        <div class="btn-group btn-group-sm" role="group">
          <a href="<?php echo get_base_url(); ?>superusuario/administradores" class="btn btn-outline-secondary <?php echo empty($d->filtro_estado) ? 'active' : ''; ?>">Todos</a>
          <a href="<?php echo get_base_url(); ?>superusuario/administradores?estado=activo" class="btn btn-outline-success <?php echo ($d->filtro_estado ?? null) === 'activo' ? 'active' : ''; ?>">Activos</a>
          <a href="<?php echo get_base_url(); ?>superusuario/administradores?estado=inactivo" class="btn btn-outline-secondary <?php echo ($d->filtro_estado ?? null) === 'inactivo' ? 'active' : ''; ?>">Inactivos</a>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered" width="100%">
            <thead>
              <tr>
                <th>Usuario</th>
                <th>Correo</th>
                <th>Estado</th>
                <th>Alta</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($d->administradores)): ?>
                <tr>
                  <td colspan="5" class="text-center text-muted">Aún no hay administradores dados de alta.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($d->administradores as $admin): ?>
                  <?php
                    // `estado` puede no venir todavía en el objeto si la
                    // columna no existe (ALTER pendiente, ver arriba) —
                    // se asume 'activo' de forma segura en ese caso.
                    $estadoAdmin = $admin->estado ?? 'activo';
                  ?>
                  <tr class="<?php echo $estadoAdmin === 'inactivo' ? 'table-secondary' : ''; ?>">
                    <td><?php echo htmlspecialchars($admin->username); ?></td>
                    <td><?php echo htmlspecialchars($admin->email); ?></td>
                    <td>
                      <?php if ($estadoAdmin === 'activo'): ?>
                        <span class="badge bg-success">Activo</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">Inactivo</span>
                      <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($admin->created_at); ?></td>
                    <td>
                      <?php if ($estadoAdmin === 'activo'): ?>
                        <!-- Patrón de Bee: enlace GET + token CSRF en query string (ver adminController::borrar_usuario()) -->
                        <a href="<?php echo get_base_url(); ?>superusuario/borrar_administrador/<?php echo $admin->id; ?>?_t=<?php echo CSRF_TOKEN; ?>"
                           class="btn btn-sm btn-outline-danger confirmar"
                           title="Revoca el acceso (no borra su historial de auditoría, ver docblock de borrar_administrador())">
                          Revocar acceso
                        </a>
                      <?php else: ?>
                        <span class="text-muted small">Acceso ya revocado</span>
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
