<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<div class="row">
  <!-- Formulario de alta de centro de trabajo (RF-10) -->
  <div class="col-12 col-md-6 col-lg-6 col-xl-4">
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Agregar centro de trabajo</h6>
      </div>
      <div class="card-body">
        <form action="<?php echo get_base_url(); ?>administrador/post_centros_trabajo" method="post">
          <?php echo insert_inputs(); ?>

          <div class="mb-3">
            <label for="nombre" class="form-label">Nombre del centro de trabajo <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="nombre" name="nombre" required>
          </div>

          <div class="mb-3">
            <label for="num_trabajadores" class="form-label">Número de trabajadores <span class="text-danger">*</span></label>
            <!-- name="num_trabajadores": debe coincidir con la columna real en docs/DDL/ddl.sql -->
            <!-- min="1" (no 16): un centro de 15 trabajadores o menos es un caso válido,
                 sólo que no requiere cuestionario (RF-00) — no debe bloquearse en el formulario -->
            <input type="number" class="form-control" id="num_trabajadores" name="num_trabajadores" min="1" required>
            <div class="form-text">16 a 50 trabajadores aplica Guía II; más de 50, Guía III; 15 o menos no requiere cuestionario (RF-00).</div>
          </div>

          <button class="btn btn-success btn-lg w-100" type="submit">Agregar</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Listado de centros de trabajo -->
  <div class="col-12 col-md-6 col-lg-6 col-xl-8">
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Mis centros de trabajo</h6>
      </div>
      <div class="card-body">
        <!-- TODO (fase de Desarrollo): tabla con $d->centros (centroTrabajoModel::por_administrador()) -->
        <div class="table-responsive">
          <table class="table table-bordered" width="100%">
            <thead>
              <tr>
                <th>Nombre</th>
                <th># Trabajadores</th>
                <th>Guía asignada</th>
                <th>Tokens</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <!-- TODO: foreach ($d->centros as $centro): ... endforeach; -->
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
