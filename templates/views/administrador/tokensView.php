<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<!-- Barra superior: volver + info del centro -->
<div class="row mb-3">
  <div class="col-12">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
      <div>
        <a href="<?php echo get_base_url(); ?>administrador/centros_trabajo" 
           class="btn btn-sm btn-outline-secondary mb-2">
          <i class="fas fa-arrow-left mr-1"></i> Volver a centros de trabajo
        </a>
        <p class="text-muted mb-0 small">
          Centro de trabajo: 
          <strong class="text-dark">
            <?php echo isset($d->centro->nombre) ? htmlspecialchars($d->centro->nombre) : ''; ?>
          </strong>
          <span class="ml-1">
            (<?php echo isset($d->centro->num_trabajadores) ? (int) $d->centro->num_trabajadores : '?'; ?> trabajadores)
          </span>
        </p>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <!-- Generación de un nuevo token de acceso (RF-10) -->
  <div class="col-12 col-lg-4 mb-4">
    <div class="card">
      <div class="card-header card-header-institucional">
        <h2 class="h5 mb-0 font-weight-bold">
          Generar token
        </h2>
      </div>
      <div class="card-body">
        <form action="<?php echo get_base_url(); ?>administrador/post_tokens" method="post">
          <?php echo insert_inputs(); ?>
          <input type="hidden" name="centro_trabajo_id" 
                 value="<?php echo isset($d->centro_trabajo_id) ? htmlspecialchars($d->centro_trabajo_id) : ''; ?>">

          <div class="mb-3">
            <label for="fecha_inicio" class="form-label text-dark">
              Vigencia desde <span class="text-danger">*</span>
            </label>
            <input 
              type="date" 
              class="form-control" 
              id="fecha_inicio" 
              name="fecha_inicio" 
              value="<?php echo date('Y-m-d'); ?>" 
              required
            >
          </div>

          <div class="mb-3">
            <label for="fecha_fin" class="form-label text-dark">
              Vigencia hasta <span class="text-danger">*</span>
            </label>
            <!-- 30 días por default (ver TODO de fase de Diseño en tokenModel.php); el administrador puede ajustarla -->
            <input 
              type="date" 
              class="form-control" 
              id="fecha_fin" 
              name="fecha_fin" 
              value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" 
              required
            >
            <small class="form-text text-muted">
              Debe ser igual o posterior a la fecha de inicio.
            </small>
          </div>

          <button class="btn btn-primary btn-block" type="submit">
            Generar token
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Listado de tokens del centro de trabajo -->
  <div class="col-12 col-lg-8 mb-4">
    <div class="card">
      <div class="card-header card-header-institucional">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
          <h2 class="h5 mb-0 font-weight-bold">
            Tokens del centro de trabajo
          </h2>
          <span class="badge bg-guinda text-white">
            <?php echo count($d->tokens); ?> token(s)
          </span>
        </div>
      </div>
      <div class="card-body p-0">
        <?php if (empty($d->tokens)): ?>
          <div class="text-center text-muted py-5">
            <p class="mb-0">Aún no se ha generado ningún token para este centro.</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="border-top-0">Código</th>
                  <th class="border-top-0">Vigencia inicio</th>
                  <th class="border-top-0">Vigencia fin</th>
                  <th class="border-top-0 text-center">Estado</th>
                  <th class="border-top-0 text-right">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($d->tokens as $token): ?>
                  <tr>
                    <td class="align-middle">
                      <code class="text-dark"><?php echo htmlspecialchars($token->codigo); ?></code>
                    </td>
                    <td class="align-middle text-muted small">
                      <?php echo htmlspecialchars($token->fecha_inicio); ?>
                    </td>
                    <td class="align-middle text-muted small">
                      <?php echo htmlspecialchars($token->fecha_fin); ?>
                    </td>
                    <td class="align-middle text-center">
                      <?php if ($token->estado === 'activo'): ?>
                        <span class="badge bg-success">Activo</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">Inactivo</span>
                      <?php endif; ?>
                    </td>
                    <td class="align-middle text-right">
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
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Aplicaciones respondidas del centro de trabajo — cierra el lazo
       visual: de aquí se llega al resultado individual de cada encuestado
       que ya completó el cuestionario. -->
  <div class="col-12">
    <div class="card">
      <div class="card-header card-header-institucional">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
          <div>
            <h2 class="h5 mb-0 font-weight-bold">
              Aplicaciones respondidas
            </h2>
            <small class="text-muted">
              Encuestados que ya completaron el cuestionario en este centro.
            </small>
          </div>
          <span class="badge bg-guinda text-white">
            <?php echo count($d->aplicaciones); ?> aplicación(es)
          </span>
        </div>
      </div>
      <div class="card-body p-0">
        <?php if (empty($d->aplicaciones)): ?>
          <div class="text-center text-muted py-5">
            <p class="mb-0">Aún no hay aplicaciones respondidas en este centro.</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="border-top-0">Nombre</th>
                  <th class="border-top-0">Número de servidor público</th>
                  <th class="border-top-0">Fecha de envío</th>
                  <th class="border-top-0 text-right">Resultado</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($d->aplicaciones as $aplicacion): ?>
                  <tr>
                    <td class="align-middle">
                      <?php echo htmlspecialchars($aplicacion->nombre); ?>
                    </td>
                    <td class="align-middle text-muted small">
                      <?php echo htmlspecialchars($aplicacion->numero_servidor_publico); ?>
                    </td>
                    <td class="align-middle text-muted small">
                      <?php echo htmlspecialchars($aplicacion->updated_at); ?>
                    </td>
                    <td class="align-middle text-right">
                      <a href="<?php echo get_base_url(); ?>resultados/individual/<?php echo $aplicacion->id; ?>" 
                         class="btn btn-sm btn-outline-primary">
                        Ver resultado
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