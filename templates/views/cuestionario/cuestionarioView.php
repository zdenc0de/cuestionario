<?php require_once INCLUDES . 'header.php'; ?>
<?php require_once INCLUDES . 'navbar.php'; ?>

<!--
  Flujo de preguntas del cuestionario NOM-035 (Guía II o III)
  RF-01: Escala Likert de 5 opciones (Siempre a Nunca).
  RF-02: Lógica condicional por preguntas-filtro (Clientes / Jefatura).
  RF-04: Validación de obligatoriedad antes del envío.
  RNF-03: Indicador visual de progreso y diseño para prevenir fatiga visual.
-->

<?php
// Opciones estándar de la escala Likert (5 puntos)
$opcionesLikert = [
  ['id' => 1, 'texto' => 'Siempre'],
  ['id' => 2, 'texto' => 'Casi siempre'],
  ['id' => 3, 'texto' => 'Algunas veces'],
  ['id' => 4, 'texto' => 'Casi nunca'],
  ['id' => 5, 'texto' => 'Nunca']
];

// Si aún no se inyectan reactivos desde la base de datos (fase previa al seed),
// se muestra un set representativo e interactivo para validar todo el frontend.
$reactivosList = $d->reactivos ?? [
  [
    'id' => 1,
    'numero' => 1,
    'texto' => 'El espacio donde trabajo me permite realizar mis actividades de manera segura e higiénica.',
    'categoria' => 'Ambiente de trabajo',
    'filtro' => null
  ],
  [
    'id' => 2,
    'numero' => 2,
    'texto' => 'Mi trabajo exige que esté muy concentrado.',
    'categoria' => 'Factores propios de la actividad',
    'filtro' => null
  ],
  [
    'id' => 3,
    'numero' => 3,
    'texto' => 'Por la cantidad de trabajo que tengo debo quedarme tiempo adicional a mi turno.',
    'categoria' => 'Organización del tiempo de trabajo',
    'filtro' => null
  ],
  [
    'id' => 4,
    'numero' => 4,
    'texto' => 'Mi trabajo me permite desarrollar nuevas habilidades.',
    'categoria' => 'Liderazgo y relaciones en el trabajo',
    'filtro' => null
  ],
  [
    'id' => 5,
    'numero' => 5,
    'texto' => 'En mi trabajo recibo retroalimentación sobre mi desempeño.',
    'categoria' => 'Entorno organizacional',
    'filtro' => null
  ]
];
?>

<!-- Barra de progreso flotante / sticky -->
<div class="sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-gray-200 shadow-sm transition-all duration-200">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
    <div class="flex items-center space-x-3">
      <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-edomex-guinda-50 text-edomex-guinda text-xs font-bold">
        <i class="fas fa-tasks"></i>
      </span>
      <div>
        <h2 class="text-xs sm:text-sm font-title font-bold text-gray-800 leading-none">
          <?php echo isset($d->guia_nombre) ? htmlspecialchars($d->guia_nombre) : 'Cuestionario de Clima Laboral (NOM-035)'; ?>
        </h2>
        <span class="text-[11px] text-gray-500">Avance de tu evaluación</span>
      </div>
    </div>

    <!-- Indicador de avance -->
    <div class="flex items-center space-x-3 sm:w-72">
      <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden border border-gray-200">
        <div id="progressBar" class="bg-gradient-to-r from-edomex-guinda to-edomex-oro h-2.5 rounded-full transition-all duration-300" style="width: 0%;"></div>
      </div>
      <span id="progressText" class="text-xs font-bold text-edomex-guinda font-mono min-w-[3rem] text-right">0%</span>
    </div>
  </div>
</div>

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-8 space-y-8">

  <!-- Notificaciones de Bee Framework -->
  <?php echo Flasher::flash(); ?>

  <!-- Instrucciones de aplicación -->
  <div class="card-edomex bg-white relative overflow-hidden">
    <div class="absolute top-0 left-0 right-0 h-1 bg-edomex-oro"></div>
    <div class="flex items-start space-x-4">
      <div class="w-10 h-10 rounded-xl bg-edomex-oro/15 text-edomex-cafe flex items-center justify-center text-lg flex-shrink-0 mt-0.5">
        <i class="fas fa-info-circle"></i>
      </div>
      <div class="space-y-1.5 text-sm text-gray-700">
        <h3 class="font-title font-bold text-gray-900 text-base">Instrucciones generales</h3>
        <p class="leading-relaxed">
          Para responder a las siguientes afirmaciones, considera las condiciones de tu trabajo durante las <strong>últimas cuatro semanas</strong>.
        </p>
        <p class="text-xs text-gray-500">
          Selecciona con total sinceridad la opción que mejor refleje tu situación diaria. No hay respuestas correctas ni incorrectas.
        </p>
      </div>
    </div>
  </div>

  <!-- Formulario de reactivos -->
  <form id="formCuestionario" action="<?php echo get_base_url(); ?>cuestionario/post_responder" method="post" class="space-y-6">
    <?php echo insert_inputs(); ?>
    <input type="hidden" name="token" value="<?php echo isset($d->token) ? htmlspecialchars($d->token) : ''; ?>">

    <!-- Bloque de preguntas regulares -->
    <div class="space-y-4" id="preguntasContainer">
      <?php foreach ($reactivosList as $index => $reactivo): ?>
        <div class="card-edomex transition-all duration-200 hover:shadow-card-hover pregunta-card" data-pregunta-id="<?php echo $reactivo['id']; ?>">
          
          <div class="flex items-start justify-between gap-3 mb-4">
            <div class="flex items-start space-x-3">
              <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-edomex-guinda-50 text-edomex-guinda text-xs font-bold font-mono flex-shrink-0 mt-0.5 border border-edomex-guinda/10">
                <?php echo $reactivo['numero']; ?>
              </span>
              <p class="text-sm sm:text-base font-medium text-gray-900 leading-snug">
                <?php echo htmlspecialchars($reactivo['texto']); ?>
              </p>
            </div>
            <?php if (!empty($reactivo['categoria'])): ?>
              <span class="hidden md:inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-600 flex-shrink-0">
                <?php echo htmlspecialchars($reactivo['categoria']); ?>
              </span>
            <?php endif; ?>
          </div>

          <!-- Escala Likert de 5 opciones (touch friendly) -->
          <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 sm:gap-3">
            <?php foreach ($opcionesLikert as $opcion): ?>
              <label class="cursor-pointer select-none">
                <input 
                  type="radio" 
                  name="respuestas[<?php echo $reactivo['id']; ?>]" 
                  value="<?php echo $opcion['id']; ?>" 
                  class="peer sr-only radio-respuesta" 
                  required
                >
                <div class="p-2.5 sm:p-3 rounded-xl border-2 border-gray-200 text-xs sm:text-sm font-medium text-gray-700 text-center transition-all peer-checked:border-edomex-guinda peer-checked:bg-edomex-guinda-50/70 peer-checked:text-edomex-guinda peer-checked:font-bold hover:border-edomex-guinda/40 hover:bg-gray-50 flex items-center justify-center h-full">
                  <span><?php echo $opcion['texto']; ?></span>
                </div>
              </label>
            <?php endforeach; ?>
          </div>

        </div>
      <?php endforeach; ?>
    </div>

    <!-- PREGUNTA FILTRO 1: Servicio a Clientes o Usuarios (RF-02) -->
    <div class="card-edomex border-2 border-edomex-oro/40 bg-edomex-arena-light/20 relative overflow-hidden" id="cardFiltroClientes">
      <div class="flex items-start space-x-3.5 mb-4">
        <div class="w-8 h-8 rounded-lg bg-edomex-oro/20 text-edomex-cafe flex items-center justify-center text-sm font-bold flex-shrink-0">
          <i class="fas fa-users"></i>
        </div>
        <div>
          <span class="inline-block text-[11px] font-bold uppercase tracking-wider text-edomex-cafe mb-0.5">Pregunta de Clasificación</span>
          <h4 class="text-sm sm:text-base font-semibold text-gray-900">
            ¿En mi trabajo debo brindar servicio a clientes o usuarios?
          </h4>
        </div>
      </div>

      <div class="flex items-center space-x-4 max-w-xs">
        <label class="cursor-pointer flex-1">
          <input type="radio" name="atiende_clientes" value="1" class="peer sr-only filtro-trigger" data-target="seccionClientes">
          <div class="py-2.5 px-4 rounded-xl border-2 border-gray-300 text-center text-sm font-semibold text-gray-700 transition-all peer-checked:border-edomex-guinda peer-checked:bg-edomex-guinda peer-checked:text-white hover:border-gray-400">
            Sí
          </div>
        </label>
        <label class="cursor-pointer flex-1">
          <input type="radio" name="atiende_clientes" value="0" class="peer sr-only filtro-trigger" data-target="seccionClientes" checked>
          <div class="py-2.5 px-4 rounded-xl border-2 border-gray-300 text-center text-sm font-semibold text-gray-700 transition-all peer-checked:border-gray-600 peer-checked:bg-gray-700 peer-checked:text-white hover:border-gray-400">
            No
          </div>
        </label>
      </div>

      <!-- Preguntas dependientes de Atención a Clientes (ocultas por defecto si atiende_clientes = 0) -->
      <div id="seccionClientes" class="hidden mt-6 pt-6 border-t border-edomex-oro/30 space-y-4">
        <div class="p-3 bg-white/80 rounded-xl border border-edomex-oro/30 text-xs text-gray-600">
          <i class="fas fa-info-circle text-edomex-oro mr-1"></i> Responde estas preguntas adicionales sobre la atención que brindas a usuarios:
        </div>

        <div class="card-edomex bg-white pregunta-card" data-pregunta-id="f1_1">
          <p class="text-sm font-medium text-gray-900 mb-3">
            Atiendo a clientes o usuarios muy enojados.
          </p>
          <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
            <?php foreach ($opcionesLikert as $opcion): ?>
              <label class="cursor-pointer">
                <input type="radio" name="respuestas[f1_1]" value="<?php echo $opcion['id']; ?>" class="peer sr-only radio-respuesta">
                <div class="p-2.5 rounded-lg border border-gray-200 text-xs font-medium text-gray-700 text-center peer-checked:border-edomex-guinda peer-checked:bg-edomex-guinda-50 peer-checked:text-edomex-guinda font-medium">
                  <?php echo $opcion['texto']; ?>
                </div>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- PREGUNTA FILTRO 2: Jefatura de Personal (RF-02) -->
    <div class="card-edomex border-2 border-edomex-oro/40 bg-edomex-arena-light/20 relative overflow-hidden" id="cardFiltroJefe">
      <div class="flex items-start space-x-3.5 mb-4">
        <div class="w-8 h-8 rounded-lg bg-edomex-oro/20 text-edomex-cafe flex items-center justify-center text-sm font-bold flex-shrink-0">
          <i class="fas fa-user-tie"></i>
        </div>
        <div>
          <span class="inline-block text-[11px] font-bold uppercase tracking-wider text-edomex-cafe mb-0.5">Pregunta de Clasificación</span>
          <h4 class="text-sm sm:text-base font-semibold text-gray-900">
            ¿Soy jefe de otros trabajadores?
          </h4>
        </div>
      </div>

      <div class="flex items-center space-x-4 max-w-xs">
        <label class="cursor-pointer flex-1">
          <input type="radio" name="es_jefe" value="1" class="peer sr-only filtro-trigger" data-target="seccionJefe">
          <div class="py-2.5 px-4 rounded-xl border-2 border-gray-300 text-center text-sm font-semibold text-gray-700 transition-all peer-checked:border-edomex-guinda peer-checked:bg-edomex-guinda peer-checked:text-white hover:border-gray-400">
            Sí
          </div>
        </label>
        <label class="cursor-pointer flex-1">
          <input type="radio" name="es_jefe" value="0" class="peer sr-only filtro-trigger" data-target="seccionJefe" checked>
          <div class="py-2.5 px-4 rounded-xl border-2 border-gray-300 text-center text-sm font-semibold text-gray-700 transition-all peer-checked:border-gray-600 peer-checked:bg-gray-700 peer-checked:text-white hover:border-gray-400">
            No
          </div>
        </label>
      </div>

      <!-- Preguntas dependientes de Jefatura (ocultas por defecto si es_jefe = 0) -->
      <div id="seccionJefe" class="hidden mt-6 pt-6 border-t border-edomex-oro/30 space-y-4">
        <div class="p-3 bg-white/80 rounded-xl border border-edomex-oro/30 text-xs text-gray-600">
          <i class="fas fa-info-circle text-edomex-oro mr-1"></i> Responde estas preguntas adicionales sobre tus funciones de supervisión:
        </div>

        <div class="card-edomex bg-white pregunta-card" data-pregunta-id="f2_1">
          <p class="text-sm font-medium text-gray-900 mb-3">
            Comunico oportunamente a mis colaboradores las tareas que deben realizar.
          </p>
          <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
            <?php foreach ($opcionesLikert as $opcion): ?>
              <label class="cursor-pointer">
                <input type="radio" name="respuestas[f2_1]" value="<?php echo $opcion['id']; ?>" class="peer sr-only radio-respuesta">
                <div class="p-2.5 rounded-lg border border-gray-200 text-xs font-medium text-gray-700 text-center peer-checked:border-edomex-guinda peer-checked:bg-edomex-guinda-50 peer-checked:text-edomex-guinda font-medium">
                  <?php echo $opcion['texto']; ?>
                </div>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Botón de Envío y Confirmación -->
    <div class="card-edomex bg-gradient-to-r from-gray-900 to-gray-800 text-white flex flex-col sm:flex-row items-center justify-between gap-4 p-6">
      <div>
        <h4 class="font-title font-bold text-base text-white">¿Has completado todas tus respuestas?</h4>
        <p class="text-xs text-gray-300 mt-1">Una vez enviado el cuestionario no se podrán modificar las respuestas.</p>
      </div>
      <button 
        type="submit" 
        id="btnEnviar" 
        class="btn-edomex w-full sm:w-auto px-8 py-3.5 text-base font-semibold shadow-lg hover:shadow-xl"
      >
        <i class="fas fa-paper-plane mr-2"></i> Enviar cuestionario
      </button>
    </div>

  </form>

</div>

<!-- Script interactivo para avance dinámico y lógica condicional de filtros -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('formCuestionario');
  const progressBar = document.getElementById('progressBar');
  const progressText = document.getElementById('progressText');

  // Actualizar cálculo de progreso
  function updateProgress() {
    // Tomar solo las tarjetas de preguntas actualmente visibles
    const allVisibleCards = Array.from(document.querySelectorAll('.pregunta-card')).filter(card => {
      return card.offsetParent !== null; // visible en pantalla
    });

    let answeredCount = 0;
    allVisibleCards.forEach(card => {
      const inputs = card.querySelectorAll('input[type="radio"]:checked');
      if (inputs.length > 0) {
        answeredCount++;
      }
    });

    const total = allVisibleCards.length;
    const percentage = total > 0 ? Math.round((answeredCount / total) * 100) : 0;

    if (progressBar && progressText) {
      progressBar.style.width = percentage + '%';
      progressText.textContent = percentage + '%';
    }
  }

  // Escuchar cambios en respuestas Likert
  document.querySelectorAll('.radio-respuesta').forEach(radio => {
    radio.addEventListener('change', updateProgress);
  });

  // Manejo de preguntas filtro (RF-02)
  document.querySelectorAll('.filtro-trigger').forEach(trigger => {
    trigger.addEventListener('change', (e) => {
      const targetId = e.target.getAttribute('data-target');
      const targetElement = document.getElementById(targetId);
      if (!targetElement) return;

      if (e.target.value === '1') {
        targetElement.classList.remove('hidden');
        // Hacer requeridos los radios internos
        targetElement.querySelectorAll('.radio-respuesta').forEach(r => r.setAttribute('required', 'required'));
      } else {
        targetElement.classList.add('hidden');
        // Quitar requerido y desmarcar
        targetElement.querySelectorAll('.radio-respuesta').forEach(r => {
          r.removeAttribute('required');
          r.checked = false;
        });
      }
      updateProgress();
    });
  });

  // Inicializar cálculo inicial
  updateProgress();
});
</script>

<?php require_once INCLUDES . 'footer.php'; ?>
