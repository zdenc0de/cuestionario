<?php require_once INCLUDES . 'header.php'; ?>
<?php require_once INCLUDES . 'navbar.php'; ?>

<!-- Pantalla de confirmación tras el envío exitoso del cuestionario -->
<div class="min-h-[calc(100vh-140px)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
  <div class="max-w-lg w-full">

    <div class="card-edomex text-center relative overflow-hidden shadow-card-hover p-8 sm:p-10">
      <!-- Acento decorativo institucional -->
      <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-emerald-500 via-edomex-oro to-edomex-guinda"></div>

      <!-- Icono de éxito animado -->
      <div class="mx-auto w-20 h-20 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-3xl shadow-inner border border-emerald-100 mb-6">
        <i class="fas fa-check-circle"></i>
      </div>

      <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100/70 text-emerald-800 mb-3">
        <i class="fas fa-lock mr-1.5 text-emerald-600"></i> Registro Concluido
      </span>

      <h1 class="text-2xl sm:text-3xl font-title font-bold text-gray-900 tracking-tight mb-3">
        ¡Gracias por tu participación!
      </h1>

      <p class="text-sm sm:text-base text-gray-600 leading-relaxed mb-8">
        Tus respuestas han sido capturadas con éxito. Tu colaboración es fundamental para identificar factores de riesgo y promover un entorno laboral favorable en la <strong>Secretaría de Cultura y Turismo</strong>.
      </p>

      <!-- Tarjeta informativa de confidencialidad -->
      <div class="bg-gray-50 rounded-xl p-4 text-left border border-gray-200 mb-8 space-y-2.5">
        <div class="flex items-center text-xs font-semibold text-gray-700">
          <i class="fas fa-info-circle text-edomex-guinda mr-2 text-sm"></i> Información sobre tu aplicación
        </div>
        <ul class="text-xs text-gray-600 space-y-1.5 list-disc list-inside">
          <li>Los resultados individuales se evalúan de forma confidencial conforme a la norma oficial.</li>
          <li>El reporte se integrará de forma agregada para el diagnóstico de tu centro de trabajo.</li>
          <li>Ya puedes cerrar esta ventana con total seguridad.</li>
        </ul>
      </div>

      <!-- Acción final -->
      <div>
        <a href="<?php echo get_base_url(); ?>cuestionario" class="btn-edomex-secondary w-full sm:w-auto px-6 py-2.5">
          <i class="fas fa-sign-out-alt mr-2"></i> Finalizar y salir
        </a>
      </div>

    </div>

  </div>
</div>

<?php require_once INCLUDES . 'footer.php'; ?>
