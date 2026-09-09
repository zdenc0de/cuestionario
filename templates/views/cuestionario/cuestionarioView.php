<?php require_once INCLUDES . 'header.php'; ?>
<?php require_once INCLUDES . 'navbar.php'; ?>

<!--
  Flujo de preguntas del cuestionario (Guía II o III, según el centro de
  trabajo ligado al token). RF-01: escala Likert de 5 opciones.
  RF-02: lógica condicional por preguntas-filtro.
  RF-04: validar que todos los reactivos obligatorios sean respondidos.

  TODO (fase de Desarrollo):
  - Renderizar los reactivos con reactivoModel::obligatorios_por_guia() /
    condicionales_por_filtro() cargados desde el controlador.
  - Renderizar la escala con opcionRespuestaModel::escala().
  - Agregar indicador de progreso (RNF-03) y diseño responsivo (RNF-04).
-->
<div class="container py-5 main-wrapper cuestionario-preguntas">
  <div class="row">
    <div class="col-12">
      <?php echo Flasher::flash(); ?>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-12">
      <h1 class="h4">Cuestionario NOM-035</h1>
      <!-- TODO: barra de progreso (reactivo actual / total de reactivos aplicables) -->
    </div>
  </div>

  <form action="<?php echo get_base_url(); ?>cuestionario/post_responder" method="post">
    <?php echo insert_inputs(); ?>
    <input type="hidden" name="token" value="<?php echo isset($d->token) ? htmlspecialchars($d->token) : ''; ?>">

    <!--
      TODO: iterar sobre los reactivos (foreach $d->reactivos as $reactivo) y
      renderizar cada uno con su escala Likert (radio buttons), respetando la
      lógica condicional de las preguntas-filtro (RF-02).

      Ejemplo de estructura esperada por reactivo:
      <div class="mb-4">
        <p><?php // echo $reactivo->numero . '. ' . $reactivo->texto; ?></p>
        <?php // foreach ($d->opciones as $opcion): ?>
          <label>
            <input type="radio" name="respuestas[<?php // echo $reactivo->id; ?>]" value="<?php // echo $opcion->id; ?>" required>
            <?php // echo $opcion->texto; ?>
          </label>
        <?php // endforeach; ?>
      </div>
    -->

    <div class="alert alert-info">Esqueleto de vista &mdash; el listado de reactivos se implementará en la fase de Desarrollo.</div>

    <button type="submit" class="btn btn-primary btn-lg">Enviar respuestas</button>
  </form>
</div>

<?php require_once INCLUDES . 'footer.php'; ?>
