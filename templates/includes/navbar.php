<?php if (defined('CONTROLLER') && CONTROLLER === 'cuestionario'): ?>
<!-- Encabezado Institucional Edoméx - Módulo Cuestionario -->
<header class="bg-edomex-guinda text-white shadow-md border-b-4 border-edomex-oro">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex flex-col sm:flex-row items-center justify-between gap-3">
    <div class="flex items-center space-x-3.5">
      <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-edomex-oro text-xl border border-white/10 shadow-inner">
        <i class="fas fa-clipboard-check"></i>
      </div>
      <div>
        <span class="block text-[11px] uppercase tracking-wider text-edomex-arena font-semibold">Gobierno del Estado de México</span>
        <span class="block text-sm sm:text-base font-title font-bold text-white tracking-tight">Secretaría de Cultura y Turismo</span>
      </div>
    </div>
    <div class="flex items-center space-x-2">
      <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-white/15 text-edomex-arena-light border border-edomex-oro/40">
        <i class="fas fa-shield-alt mr-1.5 text-edomex-oro"></i> NOM-035-STPS-2018
      </span>
      <span class="hidden md:inline-block text-xs text-gray-200">Factores de Riesgo Psicosocial</span>
    </div>
  </div>
</header>
<main class="flex-grow">
<?php else: ?>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container">
    <a class="navbar-brand" href="<?php echo get_base_url(); ?>">
      <img src="<?php echo get_logo(); ?>" alt="<?php echo get_sitename(); ?>" width="100px">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" aria-current="page" href="<?php echo get_base_url(); ?>">Inicio</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="tienda">Tienda</a>
        </li>
        <li class="nav-item">
          <a class="nav-link position-relative" href="carrito"><i class="fas fa-shopping-cart fa-fw"></i>
            <span class="position-absolute start-100 translate-middle badge rounded-pill bg-danger d-none d-lg-inline" style="top: 10px;">
              <?php echo $d->cart->totalItems; ?>
              <span class="visually-hidden">Carrito de compras</span>
            </span>
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="main_wrapper" style="min-height: 95vh;">
<?php endif; ?>