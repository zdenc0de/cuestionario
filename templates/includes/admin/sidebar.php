<?php
// Navegación por rol de contexto (usuarioModel), NO por el catálogo genérico
// de Bee: 'administrador' y 'superusuario' ven únicamente las secciones
// reales del sistema NOM-035. Una cuenta de Bee sin rol de contexto (ej. el
// usuario demo nativo "bee") sigue viendo el catálogo original de Bee tal
// cual, sin romper esa demo. Ver ruta_tablero_segun_rol() y
// obtener_rol_usuario_actual() en app/functions/bee_custom_functions.php.
$rolSidebar = obtener_rol_usuario_actual();
?>
<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

  <!-- Sidebar - Brand -->
  <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo get_base_url() . ($rolSidebar !== null ? ruta_tablero_segun_rol() : ''); ?>">
    <img src="<?php echo get_bee_logo(); ?>" alt="<?php echo get_bee_name(); ?>" width="100px">
  </a>

  <!-- Divider -->
  <hr class="sidebar-divider my-0">

  <?php if ($rolSidebar === 'administrador'): ?>

    <li class="nav-item <?php echo (defined('CONTROLLER') && CONTROLLER === 'administrador' && METHOD === 'index') ? 'active' : ''; ?>">
      <a class="nav-link" href="<?php echo get_base_url(); ?>administrador">
        <i class="fas fa-fw fa-tachometer"></i>
        <span>Dashboard</span>
      </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
      Gestión
    </div>

    <li class="nav-item <?php echo (defined('CONTROLLER') && CONTROLLER === 'administrador' && METHOD === 'centros_trabajo') ? 'active' : ''; ?>">
      <a class="nav-link" href="<?php echo get_base_url(); ?>administrador/centros_trabajo">
        <i class="fas fa-fw fa-building"></i>
        <span>Centros de trabajo</span>
      </a>
    </li>

    <li class="nav-item <?php echo (defined('CONTROLLER') && CONTROLLER === 'resultados') ? 'active' : ''; ?>">
      <a class="nav-link" href="<?php echo get_base_url(); ?>resultados">
        <i class="fas fa-fw fa-chart-bar"></i>
        <span>Resultados</span>
      </a>
    </li>

  <?php elseif ($rolSidebar === 'superusuario'): ?>

    <li class="nav-item <?php echo (defined('CONTROLLER') && CONTROLLER === 'superusuario' && METHOD === 'index') ? 'active' : ''; ?>">
      <a class="nav-link" href="<?php echo get_base_url(); ?>superusuario">
        <i class="fas fa-fw fa-tachometer"></i>
        <span>Dashboard</span>
      </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
      Gestión
    </div>

    <li class="nav-item <?php echo (defined('CONTROLLER') && CONTROLLER === 'superusuario' && METHOD === 'administradores') ? 'active' : ''; ?>">
      <a class="nav-link" href="<?php echo get_base_url(); ?>superusuario/administradores">
        <i class="fas fa-fw fa-user-shield"></i>
        <span>Administradores</span>
      </a>
    </li>

    <li class="nav-item <?php echo (defined('CONTROLLER') && CONTROLLER === 'superusuario' && METHOD === 'bitacora') ? 'active' : ''; ?>">
      <a class="nav-link" href="<?php echo get_base_url(); ?>superusuario/bitacora">
        <i class="fas fa-fw fa-history"></i>
        <span>Bitácora</span>
      </a>
    </li>

    <li class="nav-item <?php echo (defined('CONTROLLER') && CONTROLLER === 'resultados') ? 'active' : ''; ?>">
      <a class="nav-link" href="<?php echo get_base_url(); ?>resultados">
        <i class="fas fa-fw fa-chart-bar"></i>
        <span>Resultados</span>
      </a>
    </li>

  <?php else: ?>

    <!-- Cuenta de Bee sin rol de contexto (usuarioModel) — catálogo nativo
         del framework, sin cambios, para no romper la demo original. -->
    <li class="nav-item active">
      <a class="nav-link" href="admin">
        <i class="fas fa-fw fa-tachometer"></i>
        <span>Dashboard</span>
      </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
      Bee framework
    </div>

    <li class="nav-item">
      <a class="nav-link" href="creator">
        <i class="fas fa-fw fa-pen"></i>
        <span>Creator</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
        <i class="fas fa-fw fa-cog"></i>
        <span>Componentes</span>
      </a>
      <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
          <h6 class="collapse-header">SB Admin 2</h6>
          <a class="collapse-item" href="https://startbootstrap.com/theme/sb-admin-2" target="_blank">Template original</a>
          <a class="collapse-item" href="admin/botones">Botones</a>
          <a class="collapse-item" href="admin/cartas">Cartas</a>
        </div>
      </div>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
      Gestión
    </div>

    <li class="nav-item">
      <a class="nav-link" href="admin/usuarios">
        <i class="fas fa-fw fa-users"></i>
        <span>Usuarios</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="admin/productos">
        <i class="fas fa-fw fa-tag"></i>
        <span>Productos</span>
      </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
      Addons
    </div>

    <li class="nav-item">
      <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages" aria-expanded="true" aria-controls="collapsePages">
        <i class="fas fa-fw fa-folder"></i>
        <span>Páginas</span>
      </a>
      <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
          <a class="collapse-item" href="login">Login</a>
          <a class="collapse-item" href="registro">Registro</a>
          <a class="collapse-item" href="admin/perfil">Perfil</a>
          <a class="collapse-item" href="vuejs">Vue3</a>
        </div>
      </div>
    </li>

  <?php endif; ?>

  <!-- Divider -->
  <hr class="sidebar-divider d-none d-md-block">

  <!-- Sidebar Toggler (Sidebar) -->
  <div class="text-center d-none d-md-inline">
    <button class="rounded-circle border-0" id="sidebarToggle"></button>
  </div>

</ul>
<!-- End of Sidebar -->