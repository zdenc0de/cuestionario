<?php require_once INCLUDES . 'header.php'; ?>
<?php require_once INCLUDES . 'navbar.php'; ?>

<!--
  Vista de acceso público al cuestionario NOM-035.
  El encuestado captura su token de acceso y su identificación mínima
  (nombre y número de servidor público, RF-03). NO se solicitan datos
  demográficos adicionales.
-->
<div class="container py-5 main-wrapper cuestionario-acceso">
  <div class="row">
    <div class="col-12">
      <?php echo Flasher::flash(); ?>
    </div>
  </div>

  <div class="row">
    <div class="col-12 col-md-6 offset-md-3">
      <div class="card shadow-sm">
        <div class="card-body p-4">

          <h1 class="h4 mb-3">Cuestionario de identificación de factores de riesgo psicosocial</h1>
          <p class="text-muted">NOM-035-STPS-2018 &mdash; Secretaría de Cultura y Turismo del Estado de México</p>

          <form action="<?php echo get_base_url(); ?>cuestionario/post_acceso" method="post">
            <?php echo insert_inputs(); // csrf + campos ocultos requeridos por Bee ?>

            <div class="mb-3">
              <label for="token" class="form-label">Token de acceso <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="token" name="token" required>
            </div>

            <div class="mb-3">
              <label for="nombre" class="form-label">Nombre completo <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>

            <div class="mb-3">
              <label for="numero_servidor_publico" class="form-label">Número de servidor público <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="numero_servidor_publico" name="numero_servidor_publico" required>
            </div>

            <!-- TODO (fase de Diseño/Desarrollo): aviso de privacidad / confidencialidad de datos (RNF-01) -->

            <button type="submit" class="btn btn-primary btn-lg w-100">Ingresar</button>
          </form>

        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once INCLUDES . 'footer.php'; ?>
