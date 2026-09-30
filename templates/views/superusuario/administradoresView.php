<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<div class="row">
  <!-- Alta de administrador -->
  <div class="col-12 col-lg-4 mb-4">
    <div class="card">
      <div class="card-header bg-white border-bottom">
        <h2 class="h5 mb-0 text-dark font-weight-bold">
          Agregar administrador
        </h2>
      </div>
      <div class="card-body">
        <form action="<?php echo get_base_url(); ?>superusuario/post_administradores" method="post">
          <?php echo insert_inputs(); ?>

          <div class="mb-3">
            <label for="username" class="form-label text-dark">
              Nombre de usuario <span class="text-danger">*</span>
            </label>
            <input 
              type="text" 
              class="form-control" 
              id="username" 
              name="username" 
              required
              autocomplete="username"
            >
            <small class="form-text text-muted">
              Sin espacios ni caracteres especiales.
            </small>
          </div>

          <div class="mb-3">
            <label for="email" class="form-label text-dark">
              Correo electrónico <span class="text-danger">*</span>
            </label>
            <input 
              type="email" 
              class="form-control" 
              id="email" 
              name="email" 
              required
              autocomplete="email"
            >
            <small class="form-text text-muted">
              Debe ser un correo institucional.
            </small>
          </div>

          <div class="mb-3">
            <label for="password" class="form-label text-dark">
              Contraseña <span class="text-danger">*</span>
            </label>
            <input 
              type="password" 
              class="form-control" 
              id="password" 
              name="password" 
              required
              autocomplete="new-password"
            >
            <small class="form-text text-muted">
              Mínimo 8 caracteres.
            </small>
          </div>

          <button class="btn btn-primary btn-block" type="submit">
            Agregar administrador
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Listado de administradores -->
  <div class="col-12 col-lg-8 mb-4">
    <div class="card">
      <div class="card-header bg-white border-bottom">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
          <h2 class="h5 mb-0 text-dark font-weight-bold">
            Administradores de tu secretaría
          </h2>
          <div class="btn-group btn-group-sm" role="group">
            <a href="<?php echo get_base_url(); ?>superusuario/administradores" 
               class="btn <?php echo empty($d->filtro_estado) ? 'btn-primary' : 'btn-outline-secondary'; ?>">
              Todos
            </a>
            <a href="<?php echo get_base_url(); ?>superusuario/administradores?estado=activo" 
               class="btn <?php echo ($d->filtro_estado ?? null) === 'activo' ? 'btn-primary' : 'btn-outline-secondary'; ?>">
              Activos
            </a>
            <a href="<?php echo get_base_url(); ?>superusuario/administradores?estado=inactivo" 
               class="btn <?php echo ($d->filtro_estado ?? null) === 'inactivo' ? 'btn-primary' : 'btn-outline-secondary'; ?>">
              Inactivos
            </a>
          </div>
        </div>
      </div>
      <div class="card-body p-0">
        <?php if (empty($d->administradores)): ?>
          <div class="text-center text-muted py-5">
            <p class="mb-0">Aún no hay administradores dados de alta.</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="border-top-0">Usuario</th>
                  <th class="border-top-0">Correo</th>
                  <th class="border-top-0 text-center">Estado</th>
                  <th class="border-top-0">Alta</th>
                  <th class="border-top-0 text-right">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($d->administradores as $admin): ?>
                  <?php $estadoAdmin = $admin->estado ?? 'activo'; ?>
                  <tr>
                    <td class="align-middle">
                      <?php echo htmlspecialchars($admin->username); ?>
                    </td>
                    <td class="align-middle">
                      <?php echo htmlspecialchars($admin->email); ?>
                    </td>
                    <td class="align-middle text-center">
                      <?php if ($estadoAdmin === 'activo'): ?>
                        <span class="badge badge-success">Activo</span>
                      <?php else: ?>
                        <span class="badge badge-secondary">Inactivo</span>
                      <?php endif; ?>
                    </td>
                    <td class="align-middle text-muted small">
                      <?php echo htmlspecialchars($admin->created_at); ?>
                    </td>
                    <td class="align-middle text-right">
                      <?php if ($estadoAdmin === 'activo'): ?>
                        <a href="<?php echo get_base_url(); ?>superusuario/borrar_administrador/<?php echo $admin->id; ?>?_t=<?php echo CSRF_TOKEN; ?>"
                           class="btn btn-sm btn-outline-danger confirmar"
                           title="Revoca el acceso del administrador">
                          Revocar
                        </a>
                      <?php else: ?>
                        <span class="text-muted small">Acceso revocado</span>
                      <?php endif; ?>
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