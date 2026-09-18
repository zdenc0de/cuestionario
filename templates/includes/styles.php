<!-- Fuentes Institucionales: Montserrat (títulos) e Inter (cuerpo) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<!-- CSS Framework (Bootstrap 5) solo para módulos no migrados a Tailwind -->
<?php if (!defined('CONTROLLER') || CONTROLLER !== 'cuestionario'): ?>
<?php echo get_css_framework(); ?>
<?php endif; ?>

<!-- Font awesome 6 -->
<?php echo get_fontawesome(); ?>

<!-- Tailwind CSS compilado (Identidad Edoméx y NOM-035) -->
<link rel="stylesheet" href="<?php echo CSS . 'tailwind.css?v='.get_asset_version(); ?>">

<!-- Todo plugin adicional debe ir debajo de está línea -->

<!-- Sweet alert 2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.27/dist/sweetalert2.min.css">

<!-- Toastr css -->
<?php echo get_toastr('styles'); ?>

<!-- Waitme css -->
<?php echo get_waitMe('styles'); ?>

<!-- Lightbox -->
<?php echo get_lightbox('styles'); ?>

<!-- CDN Vue js 3 | definido en settings.php -->
<?php echo get_vuejs(); ?>

<!-- Tokens de identidad visual del cuestionario NOM-035 (Secretaría de Cultura y Turismo) -->
<link rel="stylesheet" href="<?php echo CSS . 'nom035-variables.css?v='.get_asset_version(); ?>">

<!-- Estilos registrados manualmente -->
<?php echo load_styles(); ?>