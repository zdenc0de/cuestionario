<?php if (defined('CONTROLLER') && CONTROLLER === 'cuestionario'): ?>
</main>
<footer class="mt-auto bg-white border-t border-gray-200 py-6 text-xs text-gray-500">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left">
    <div>
      <p class="font-medium text-gray-700">&copy; <?php echo date('Y'); ?> Secretaría de Cultura y Turismo &mdash; Gobierno del Estado de México.</p>
      <p class="text-gray-400 mt-0.5">Plataforma de Aplicación y Evaluación NOM-035-STPS-2018 &middot; Cumplimiento normativo confidencial</p>
    </div>
    <div class="flex items-center space-x-4 text-gray-400">
      <span class="inline-flex items-center"><i class="fas fa-lock text-edomex-oro mr-1.5"></i> Datos Protegidos</span>
      <span class="inline-flex items-center"><i class="fas fa-user-shield text-edomex-guinda mr-1.5"></i> Estricta Confidencialidad</span>
    </div>
  </div>
</footer>
<?php else: ?>
</div>
<footer class="bg-light py-3 border-top">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center">
      <span>Desarrollado con <i class="fas fa-heart text-danger"></i> por <a class="text-decoration-none text-primary" href="https://www.academy.joystick.com.mx">Joystick</a>.</span>
      <span><?php echo sprintf('Hecho con %s %s', get_bee_name(), get_bee_version()); ?>.</span>
    </div>
  </div>
</footer>
<?php endif; ?>

<!-- footer.php -->
<?php require_once INCLUDES . 'scripts.php'; ?>
</body>

</html>