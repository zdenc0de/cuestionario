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
        <h6 class="m-0 font-weight-bold text-primary">Administradores</h6>
      </div>
      <div class="card-body">
        <!-- TODO (fase de Desarrollo): tabla con $d->administradores (usuarioModel::administradores_por_secretaria()) -->
        <div class="table-responsive">
          <table class="table table-bordered" width="100%">
            <thead>
              <tr>
                <th>Usuario</th>
                <th>Correo</th>
                <th>Centros de trabajo</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <!-- TODO: foreach ($d->administradores as $admin): ... endforeach; -->
              <tr>
                <td colspan="4" class="text-center text-muted">Sin registros (pendiente de implementación).</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once INCLUDES . 'admin/dashboardBottom.php'; ?>
