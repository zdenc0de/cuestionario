<?php require_once INCLUDES . 'header.php'; ?>
<?php require_once INCLUDES . 'navbar.php'; ?>

<!--
  Vista de acceso público al cuestionario NOM-035.
  El encuestado captura su token de acceso y su identificación mínima
  (nombre y número de servidor público, RF-03).
-->
<div class="min-h-[calc(100vh-140px)] flex items-center justify-center py-10 px-4 sm:px-6 lg:px-8">
  <div class="max-w-md w-full space-y-6">

    <!-- Flash notifications de Bee Framework -->
    <div>
      <?php echo Flasher::flash(); ?>
    </div>

    <!-- Tarjeta principal de acceso -->
    <div class="card-edomex relative overflow-hidden">
      <!-- Acento superior de color institucional -->
      <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-edomex-guinda via-edomex-cafe to-edomex-oro"></div>

      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-edomex-guinda-50 text-edomex-guinda mb-4 shadow-sm border border-edomex-guinda/10">
          <i class="fas fa-id-card-alt text-2xl"></i>
        </div>
        <h1 class="text-xl sm:text-2xl font-title font-bold text-gray-900 tracking-tight">
          Cuestionario NOM-035
        </h1>
        <p class="mt-2 text-sm text-gray-600 font-normal">
          Identificación de factores de riesgo psicosocial y evaluación del entorno organizacional
        </p>
      </div>

      <form action="<?php echo get_base_url(); ?>cuestionario/post_acceso" method="post" class="space-y-5">
        <?php echo insert_inputs(); // csrf + campos ocultos requeridos por Bee ?>

        <!-- Token de acceso -->
        <div>
          <label for="token" class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1.5">
            Token de acceso <span class="text-edomex-guinda">*</span>
          </label>
          <div class="relative rounded-xl shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
              <i class="fas fa-key text-sm"></i>
            </div>
            <input 
              type="text" 
              id="token" 
              name="token" 
              required 
              placeholder="Ej. TKN-CT-2026-X8A"
              class="input-edomex pl-10 uppercase tracking-wider font-mono text-sm font-semibold text-gray-800"
              autocomplete="off"
            >
          </div>
          <p class="mt-1 text-[11px] text-gray-500">
            Proporcionado por el administrador de tu centro de trabajo.
          </p>
        </div>

        <!-- Nombre completo -->
        <div>
          <label for="nombre" class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1.5">
            Nombre completo <span class="text-edomex-guinda">*</span>
          </label>
          <div class="relative rounded-xl shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
              <i class="fas fa-user text-sm"></i>
            </div>
            <input 
              type="text" 
              id="nombre" 
              name="nombre" 
              required 
              placeholder="Nombre(s) y Apellidos"
              class="input-edomex pl-10"
              autocomplete="name"
            >
          </div>
        </div>

        <!-- Número de servidor público -->
        <div>
          <label for="numero_servidor_publico" class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1.5">
            Número de Servidor Público <span class="text-edomex-guinda">*</span>
          </label>
          <div class="relative rounded-xl shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
              <i class="fas fa-hashtag text-sm"></i>
            </div>
            <input 
              type="text" 
              id="numero_servidor_publico" 
              name="numero_servidor_publico" 
              required 
              placeholder="Ej. 10458923"
              class="input-edomex pl-10"
              autocomplete="off"
            >
          </div>
        </div>

        <!-- Aviso de Privacidad y Confidencialidad (RNF-01) -->
        <div class="rounded-xl bg-edomex-arena-light/80 border border-edomex-arena/50 p-3.5 text-xs text-gray-700 flex items-start space-x-3">
          <i class="fas fa-shield-alt text-edomex-guinda mt-0.5 text-base flex-shrink-0"></i>
          <p class="leading-relaxed">
            <strong class="font-semibold text-gray-900">Aviso de Confidencialidad:</strong> Tus respuestas son confidenciales y serán tratadas exclusivamente con fines estadísticos y de diagnóstico conforme a la NOM-035-STPS-2018.
          </p>
        </div>

        <!-- Botón de Envío -->
        <div class="pt-2">
          <button type="submit" class="btn-edomex w-full text-base font-semibold group">
            <span>Comenzar cuestionario</span>
            <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform duration-150"></i>
          </button>
        </div>
      </form>
    </div>

    <!-- Indicador inferior de soporte -->
    <div class="text-center text-xs text-gray-500">
      ¿Tienes problemas para acceder? Contacta a la coordinación administrativa de tu centro de trabajo.
    </div>

    <!--
      Enlace secundario al login administrativo — discreto y separado del
      CTA principal a propósito, para no mezclar los dos puntos de acceso
      (encuestado por token vs. administrador/súper usuario por cuenta).
    -->
    <div class="text-center">
      <a href="<?php echo get_base_url(); ?>login" class="text-[11px] text-gray-400 hover:text-gray-600 underline">
        Acceso administrativo
      </a>
    </div>

  </div>
</div>

<?php require_once INCLUDES . 'footer.php'; ?>
