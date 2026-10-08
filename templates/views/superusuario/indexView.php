<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<!-- Bienvenida + explicación del rol -->
<div class="card border-0 mb-4" style="background: linear-gradient(135deg, var(--edomex-guinda) 0%, var(--edomex-guinda-dark) 100%);">
  <div class="card-body text-white py-4">
    <h1 class="h4 font-weight-bold mb-2 text-white">
      <?php // get_user() regresa `false` (no `null`) si no encuentra la clave -- ?? no lo detecta, hace falta ?: ?>
      Hola, <?php echo htmlspecialchars(get_user('username') ?: 'súper usuario'); ?> 👋
    </h1>
    <p class="mb-0" style="opacity: 0.9;">
      Desde aquí das de alta las cuentas de los administradores de tu
      secretaría, revocas su acceso cuando haga falta, y consultas la
      bitácora con todo lo que han hecho en el sistema.
    </p>
  </div>
</div>

<!-- Resumen en números -->
<div class="row">
  <div class="col-12 col-md-4 mb-4">
    <div class="card h-100">
      <div class="card-body d-flex align-items-center">
        <div class="icon-circle bg-guinda-50 text-guinda mr-3">
          <i class="fas fa-user-shield"></i>
        </div>
        <div>
          <div class="h3 mb-0 font-weight-bold text-dark"><?php echo (int) ($d->administradores_activos ?? 0); ?></div>
          <div class="small text-muted">Administrador(es) activo(s)</div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-4 mb-4">
    <div class="card h-100">
      <div class="card-body d-flex align-items-center">
        <div class="icon-circle bg-arena text-cafe mr-3">
          <i class="fas fa-user-slash"></i>
        </div>
        <div>
          <div class="h3 mb-0 font-weight-bold text-dark"><?php echo (int) ($d->administradores_inactivos ?? 0); ?></div>
          <div class="small text-muted">Administrador(es) con acceso revocado</div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-4 mb-4">
    <div class="card h-100">
      <div class="card-body d-flex align-items-center">
        <div class="icon-circle bg-guinda-50 text-guinda mr-3">
          <i class="fas fa-history"></i>
        </div>
        <div>
          <div class="h3 mb-0 font-weight-bold text-dark"><?php echo count($d->bitacora_reciente ?? []); ?></div>
          <div class="small text-muted">Acción(es) reciente(s) mostradas abajo</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Guía rápida -->
<div class="card mb-4">
  <div class="card-header card-header-institucional">
    <i class="fas fa-route mr-1"></i> ¿Cómo funciona?
  </div>
  <div class="card-body">
    <div class="row text-center">
      <div class="col-6 col-md-3 mb-3 mb-md-0">
        <div class="icon-circle bg-guinda-50 text-guinda mx-auto mb-2"><span class="font-weight-bold">1</span></div>
        <div class="small font-weight-bold text-dark">Da de alta un administrador</div>
        <div class="small text-muted">Usuario, correo y contraseña de acceso.</div>
      </div>
      <div class="col-6 col-md-3 mb-3 mb-md-0">
        <div class="icon-circle bg-guinda-50 text-guinda mx-auto mb-2"><span class="font-weight-bold">2</span></div>
        <div class="small font-weight-bold text-dark">Él gestiona sus centros</div>
        <div class="small text-muted">Centros de trabajo, tokens y resultados.</div>
      </div>
      <div class="col-6 col-md-3 mb-3 mb-md-0">
        <div class="icon-circle bg-guinda-50 text-guinda mx-auto mb-2"><span class="font-weight-bold">3</span></div>
        <div class="small font-weight-bold text-dark">Supervisa la bitácora</div>
        <div class="small text-muted">Qué hizo cada administrador y cuándo.</div>
      </div>
      <div class="col-6 col-md-3">
        <div class="icon-circle bg-guinda-50 text-guinda mx-auto mb-2"><span class="font-weight-bold">4</span></div>
        <div class="small font-weight-bold text-dark">Revoca si hace falta</div>
        <div class="small text-muted">El acceso se desactiva, nunca se borra.</div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <!-- Accesos principales -->
  <div class="col-12 col-lg-5 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="icon-circle bg-guinda-50 text-guinda mb-3"><i class="fas fa-user-shield"></i></div>
        <h2 class="h5 font-weight-bold text-dark">Administradores</h2>
        <p class="small text-muted mb-3">
          Da de alta nuevas cuentas, filtra por estado y revoca el acceso
          de quien ya no deba tenerlo.
        </p>
        <a href="<?php echo get_base_url(); ?>superusuario/administradores" class="btn btn-primary">
          Administrar
        </a>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-5 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="icon-circle bg-guinda-50 text-guinda mb-3"><i class="fas fa-chart-bar"></i></div>
        <h2 class="h5 font-weight-bold text-dark">Resultados</h2>
        <p class="small text-muted mb-3">
          Consulta los resultados de todos los centros de trabajo de tu
          secretaría.
        </p>
        <a href="<?php echo get_base_url(); ?>resultados" class="btn btn-outline-guinda">
          Ver resultados
        </a>
      </div>
    </div>
  </div>

  <!-- Actividad reciente: para no tener que entrar a Bitácora sólo para ver qué pasó últimamente -->
  <div class="col-12">
    <div class="card">
      <div class="card-header card-header-institucional d-flex justify-content-between align-items-center">
        <span>Actividad reciente</span>
        <a href="<?php echo get_base_url(); ?>superusuario/bitacora" class="small">Ver toda la bitácora →</a>
      </div>
      <div class="card-body p-0">
        <?php if (empty($d->bitacora_reciente)): ?>
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
                  <th class="border-top-0">Fecha</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($d->bitacora_reciente as $registro): ?>
                  <tr>
                    <td class="align-middle"><?php echo htmlspecialchars($registro->username); ?></td>
                    <td class="align-middle"><?php echo htmlspecialchars($registro->accion); ?></td>
                    <td class="align-middle">
                      <?php echo htmlspecialchars($registro->entidad); ?>
                      <?php if (!empty($registro->entidad_id)): ?>
                        <span class="text-muted">#<?php echo (int) $registro->entidad_id; ?></span>
                      <?php endif; ?>
                    </td>
                    <td class="align-middle text-muted small"><?php echo htmlspecialchars($registro->created_at); ?></td>
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
