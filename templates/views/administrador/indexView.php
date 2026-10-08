<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<!-- Bienvenida + explicación del rol (para que cualquiera, sin contexto previo, entienda qué hace este panel) -->
<div class="card border-0 mb-4" style="background: linear-gradient(135deg, var(--edomex-guinda) 0%, var(--edomex-guinda-dark) 100%);">
  <div class="card-body text-white py-4">
    <h1 class="h4 font-weight-bold mb-2 text-white">
      <?php // get_user() regresa `false` (no `null`) si no encuentra la clave -- ?? no lo detecta, hace falta ?: ?>
      Hola, <?php echo htmlspecialchars(get_user('username') ?: 'administrador'); ?> 👋
    </h1>
    <p class="mb-0" style="opacity: 0.9;">
      Desde aquí das de alta los centros de trabajo a tu cargo, generas los
      tokens de acceso para que las personas respondan el cuestionario
      NOM-035, y consultas el resultado de cada centro.
    </p>
  </div>
</div>

<!-- Resumen en números -->
<div class="row">
  <div class="col-12 col-md-4 mb-4">
    <div class="card h-100">
      <div class="card-body d-flex align-items-center">
        <div class="icon-circle bg-guinda-50 text-guinda mr-3">
          <i class="fas fa-building"></i>
        </div>
        <div>
          <div class="h3 mb-0 font-weight-bold text-dark"><?php echo (int) ($d->total_centros ?? 0); ?></div>
          <div class="small text-muted">Centro(s) de trabajo</div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-4 mb-4">
    <div class="card h-100">
      <div class="card-body d-flex align-items-center">
        <div class="icon-circle bg-guinda-50 text-guinda mr-3">
          <i class="fas fa-key"></i>
        </div>
        <div>
          <div class="h3 mb-0 font-weight-bold text-dark"><?php echo (int) ($d->tokens_activos ?? 0); ?></div>
          <div class="small text-muted">Token(s) activo(s) y vigente(s)</div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-4 mb-4">
    <div class="card h-100">
      <div class="card-body d-flex align-items-center">
        <div class="icon-circle bg-guinda-50 text-guinda mr-3">
          <i class="fas fa-clipboard-check"></i>
        </div>
        <div>
          <div class="h3 mb-0 font-weight-bold text-dark"><?php echo (int) ($d->total_aplicaciones ?? 0); ?></div>
          <div class="small text-muted">Cuestionario(s) respondido(s)</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Guía rápida: el flujo completo en 4 pasos, para quien todavía no se
     siente seguro usando el panel -->
<div class="card mb-4">
  <div class="card-header card-header-institucional">
    <i class="fas fa-route mr-1"></i> ¿Cómo funciona?
  </div>
  <div class="card-body">
    <div class="row text-center">
      <div class="col-6 col-md-3 mb-3 mb-md-0">
        <div class="icon-circle bg-guinda-50 text-guinda mx-auto mb-2"><span class="font-weight-bold">1</span></div>
        <div class="small font-weight-bold text-dark">Da de alta un centro</div>
        <div class="small text-muted">Captura su nombre y número de trabajadores.</div>
      </div>
      <div class="col-6 col-md-3 mb-3 mb-md-0">
        <div class="icon-circle bg-guinda-50 text-guinda mx-auto mb-2"><span class="font-weight-bold">2</span></div>
        <div class="small font-weight-bold text-dark">Genera un token</div>
        <div class="small text-muted">Un código único con vigencia para ese centro.</div>
      </div>
      <div class="col-6 col-md-3 mb-3 mb-md-0">
        <div class="icon-circle bg-guinda-50 text-guinda mx-auto mb-2"><span class="font-weight-bold">3</span></div>
        <div class="small font-weight-bold text-dark">Comparte el token</div>
        <div class="small text-muted">Cada persona del centro lo usa para responder.</div>
      </div>
      <div class="col-6 col-md-3">
        <div class="icon-circle bg-guinda-50 text-guinda mx-auto mb-2"><span class="font-weight-bold">4</span></div>
        <div class="small font-weight-bold text-dark">Consulta el resultado</div>
        <div class="small text-muted">Calificación y nivel de riesgo, ya calculados.</div>
      </div>
    </div>
  </div>
</div>

<!-- Accesos principales -->
<div class="row">
  <div class="col-12 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="icon-circle bg-guinda-50 text-guinda mb-3"><i class="fas fa-building"></i></div>
        <h2 class="h5 font-weight-bold text-dark">Centros de trabajo</h2>
        <p class="small text-muted mb-3">
          Da de alta nuevos centros, genera y revoca tokens de acceso, y
          revisa quién ya respondió el cuestionario en cada uno.
        </p>
        <a href="<?php echo get_base_url(); ?>administrador/centros_trabajo" class="btn btn-primary">
          Administrar centros de trabajo
        </a>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="icon-circle bg-guinda-50 text-guinda mb-3"><i class="fas fa-chart-bar"></i></div>
        <h2 class="h5 font-weight-bold text-dark">Resultados</h2>
        <p class="small text-muted mb-3">
          Consulta la calificación y el nivel de riesgo de cada aplicación
          ya respondida en tus centros de trabajo.
        </p>
        <a href="<?php echo get_base_url(); ?>resultados" class="btn btn-outline-guinda">
          Ver resultados
        </a>
      </div>
    </div>
  </div>
</div>

<?php require_once INCLUDES . 'admin/dashboardBottom.php'; ?>
