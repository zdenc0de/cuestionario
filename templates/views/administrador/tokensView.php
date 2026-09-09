<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<div class="row">
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

          <!-- TODO (fase de Diseño): definir vigencia por defecto o permitir capturarla -->

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
        <!-- TODO (fase de Desarrollo): tabla con $d->tokens (tokenModel::por_centro_trabajo()) -->
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
              <!-- TODO: foreach ($d->tokens as $token): ... endforeach; -->
              <tr>
                <td colspan="5" class="text-center text-muted">Sin registros (pendiente de implementación).</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once INCLUDES . 'admin/dashboardBottom.php'; ?>
