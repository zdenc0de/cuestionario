<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<?php
// Badges de nivel de riesgo (mismo criterio de color en todo el módulo de
// resultados): 'nulo' y 'bajo' en verde/celeste, 'medio' en amarillo, 'alto'
// y 'muy_alto' en rojo — degradado de menor a mayor riesgo.
$badgesNivel =
[
  'nulo'     => 'badge-riesgo-nulo',
  'bajo'     => 'badge-riesgo-bajo',
  'medio'    => 'badge-riesgo-medio',
  'alto'     => 'badge-riesgo-alto',
  'muy_alto' => 'badge-riesgo-muyalto'
];
$etiquetasNivel =
[
  'nulo'     => 'Nulo o despreciable',
  'bajo'     => 'Bajo',
  'medio'    => 'Medio',
  'alto'     => 'Alto',
  'muy_alto' => 'Muy alto'
];

// OJO: Bee convierte $data a objetos (json_decode(json_encode($data))) antes
// de exponerlo como $d — las filas de $d->detalle son stdClass, no arrays
// asociativos, por eso ->nivel_agregacion y no ['nivel_agregacion'].
$categorias = array_filter($d->detalle ?? [], fn($fila) => $fila->nivel_agregacion === 'categoria');
$dominios   = array_filter($d->detalle ?? [], fn($fila) => $fila->nivel_agregacion === 'dominio');
?>

<!-- Barra de acciones -->
<div class="row mb-3">
  <div class="col-12 d-flex justify-content-between align-items-center flex-wrap">
    <a href="<?php echo get_base_url(); ?><?php echo isset($d->volver_url) ? htmlspecialchars($d->volver_url) : ''; ?>"
       class="btn btn-sm btn-outline-secondary mb-2">
      <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
    <a href="<?php echo get_base_url(); ?>resultados/pdf-individual/<?php echo isset($d->aplicacion_id) ? htmlspecialchars($d->aplicacion_id) : ''; ?>"
       class="btn btn-sm btn-outline-danger mb-2">
      <i class="fas fa-file-pdf mr-1"></i> Descargar PDF
    </a>
  </div>
</div>

<?php if (empty($d->aplicacion)): ?>
  <div class="row">
    <div class="col-12">
      <div class="alert alert-danger">Esta aplicación no existe.</div>
    </div>
  </div>
<?php else: ?>

  <div class="row">
    <!-- Identidad -->
    <div class="col-12 mb-4">
      <div class="card">
        <div class="card-header card-header-institucional">
          <h2 class="h5 mb-0 font-weight-bold">Identidad del encuestado</h2>
        </div>
        <div class="card-body">
          <dl class="row mb-0">
            <dt class="col-sm-3 text-muted">Nombre</dt>
            <dd class="col-sm-9"><?php echo htmlspecialchars($d->aplicacion->nombre); ?></dd>

            <dt class="col-sm-3 text-muted">Número de servidor público</dt>
            <dd class="col-sm-9"><?php echo htmlspecialchars($d->aplicacion->numero_servidor_publico); ?></dd>

            <dt class="col-sm-3 text-muted">Guía aplicada</dt>
            <dd class="col-sm-9"><?php echo htmlspecialchars($d->guia->nombre ?? '—'); ?></dd>

            <dt class="col-sm-3 text-muted">Fecha de envío</dt>
            <dd class="col-sm-9 mb-0"><?php echo htmlspecialchars($d->aplicacion->updated_at); ?></dd>
          </dl>
        </div>
      </div>
    </div>

    <?php if (empty($d->resultado)): ?>
      <div class="col-12">
        <div class="alert alert-warning mb-4">
          Esta aplicación todavía no tiene un resultado calculado (estado:
          <strong><?php echo htmlspecialchars($d->aplicacion->estado); ?></strong>).
        </div>
      </div>
    <?php else: ?>

      <!-- Calificación final -->
      <div class="col-12 mb-4">
        <div class="card">
          <div class="card-header card-header-institucional">
            <h2 class="h5 mb-0 font-weight-bold">Calificación final</h2>
          </div>
          <div class="card-body">
            <div class="d-flex align-items-center flex-wrap">
              <div class="mr-4">
                <span class="display-4 font-weight-bold text-dark">
                  <?php echo (int) $d->resultado->calificacion_final; ?>
                </span>
                <span class="text-muted ml-2">puntos</span>
              </div>
              <div>
                <span class="badge badge-lg <?php echo $badgesNivel[$d->resultado->nivel_riesgo] ?? 'bg-secondary'; ?> p-3">
                  <?php echo $etiquetasNivel[$d->resultado->nivel_riesgo] ?? htmlspecialchars($d->resultado->nivel_riesgo); ?>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Desglose por categoría -->
      <div class="col-12 col-lg-6 mb-4">
        <div class="card">
          <div class="card-header card-header-institucional">
            <h2 class="h5 mb-0 font-weight-bold">Desglose por categoría</h2>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="bg-light">
                  <tr>
                    <th class="border-top-0">Categoría</th>
                    <th class="border-top-0 text-center">Calificación</th>
                    <th class="border-top-0 text-center">Nivel</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($categorias as $fila): ?>
                    <tr>
                      <td class="align-middle"><?php echo htmlspecialchars($fila->nombre); ?></td>
                      <td class="align-middle text-center font-weight-bold">
                        <?php echo (int) $fila->calificacion; ?>
                      </td>
                      <td class="align-middle text-center">
                        <span class="badge <?php echo $badgesNivel[$fila->nivel_riesgo] ?? 'bg-secondary'; ?>">
                          <?php echo $etiquetasNivel[$fila->nivel_riesgo] ?? htmlspecialchars($fila->nivel_riesgo); ?>
                        </span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Desglose por dominio -->
      <div class="col-12 col-lg-6 mb-4">
        <div class="card">
          <div class="card-header card-header-institucional">
            <h2 class="h5 mb-0 font-weight-bold">Desglose por dominio</h2>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="bg-light">
                  <tr>
                    <th class="border-top-0">Dominio</th>
                    <th class="border-top-0 text-center">Calificación</th>
                    <th class="border-top-0 text-center">Nivel</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($dominios as $fila): ?>
                    <tr>
                      <td class="align-middle"><?php echo htmlspecialchars($fila->nombre); ?></td>
                      <td class="align-middle text-center font-weight-bold">
                        <?php echo (int) $fila->calificacion; ?>
                      </td>
                      <td class="align-middle text-center">
                        <span class="badge <?php echo $badgesNivel[$fila->nivel_riesgo] ?? 'bg-secondary'; ?>">
                          <?php echo $etiquetasNivel[$fila->nivel_riesgo] ?? htmlspecialchars($fila->nivel_riesgo); ?>
                        </span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

    <?php endif; ?>
  </div>
<?php endif; ?>

<?php require_once INCLUDES . 'admin/dashboardBottom.php'; ?>