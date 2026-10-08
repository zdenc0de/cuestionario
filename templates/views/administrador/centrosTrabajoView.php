<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<div class="row">
  <!-- Formulario de alta de centro de trabajo (RF-10) -->
  <div class="col-12 col-lg-4 mb-4">
    <div class="card">
      <div class="card-header card-header-institucional">
        <h2 class="h5 mb-0 font-weight-bold">
          Agregar centro de trabajo
        </h2>
      </div>
      <div class="card-body">
        <form action="<?php echo get_base_url(); ?>administrador/post_centros_trabajo" method="post">
          <?php echo insert_inputs(); ?>

          <div class="mb-3">
            <label for="nombre" class="form-label text-dark">
              Nombre del centro de trabajo <span class="text-danger">*</span>
            </label>
            <input 
              type="text" 
              class="form-control" 
              id="nombre" 
              name="nombre" 
              required
            >
          </div>

          <div class="mb-3">
            <label for="num_trabajadores" class="form-label text-dark">
              Número de trabajadores <span class="text-danger">*</span>
            </label>
            <!-- name="num_trabajadores": debe coincidir con la columna real en docs/DDL/ddl.sql -->
            <!-- min="1" (no 16): un centro de 15 trabajadores o menos es un caso válido,
                 sólo que no requiere cuestionario (RF-00) — no debe bloquearse en el formulario -->
            <input 
              type="number" 
              class="form-control" 
              id="num_trabajadores" 
              name="num_trabajadores" 
              min="1" 
              required
            >
            <small class="form-text text-muted">
              16 a 50 trabajadores: Guía II. Más de 50: Guía III. 15 o menos: no requiere cuestionario (RF-00).
            </small>
          </div>

          <button class="btn btn-primary btn-block" type="submit">
            Agregar centro de trabajo
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Listado de centros de trabajo -->
  <div class="col-12 col-lg-8 mb-4">
    <div class="card">
      <div class="card-header card-header-institucional">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
          <div>
            <h2 class="h5 mb-0 font-weight-bold">
              Mis centros de trabajo
            </h2>
            <small class="text-muted">
              Centros de trabajo registrados a tu cargo.
            </small>
          </div>
          <span class="badge bg-guinda text-white">
            <?php echo count($d->centros); ?> centro(s)
          </span>
        </div>
      </div>
      <div class="card-body p-0">
        <?php if (empty($d->centros)): ?>
          <div class="text-center text-muted py-5">
            <p class="mb-0">Aún no has dado de alta ningún centro de trabajo.</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="border-top-0">Nombre</th>
                  <th class="border-top-0 text-center"># Trabajadores</th>
                  <th class="border-top-0 text-center">Guía asignada</th>
                  <th class="border-top-0 text-right">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($d->centros as $centro): ?>
                  <tr>
                    <td class="align-middle">
                      <?php echo htmlspecialchars($centro->nombre); ?>
                    </td>
                    <td class="align-middle text-center">
                      <?php echo (int) $centro->num_trabajadores; ?>
                    </td>
                    <td class="align-middle text-center">
                      <?php if (!empty($centro->guia_clave)): ?>
                        <span class="badge bg-guinda text-white" 
                              title="<?php echo htmlspecialchars($centro->guia_nombre ?? ''); ?>">
                          <?php echo htmlspecialchars($centro->guia_clave); ?>
                        </span>
                      <?php else: ?>
                        <!-- En la práctica esto no debería pasar: post_centros_trabajo() ya
                             impide crear un centro de ≤15 trabajadores (RF-00) -->
                        <span class="badge bg-secondary">No aplica</span>
                      <?php endif; ?>
                    </td>
                    <td class="align-middle text-right">
                      <a href="<?php echo get_base_url(); ?>administrador/tokens/<?php echo $centro->id; ?>" 
                         class="btn btn-sm btn-outline-primary">
                        Ver tokens
                      </a>
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