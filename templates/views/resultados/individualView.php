<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<?php
// Badges de nivel de riesgo (mismo criterio de color en todo el módulo de
// resultados): 'nulo' y 'bajo' en verde/celeste, 'medio' en amarillo, 'alto'
// y 'muy_alto' en rojo — degradado de menor a mayor riesgo.
$badgesNivel =
[
  'nulo'     => 'bg-success',
  'bajo'     => 'bg-info',
  'medio'    => 'bg-warning text-dark',
  'alto'     => 'bg-danger',
  'muy_alto' => 'bg-dark'
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

<div class="row">
  <div class="col-12 mb-3 d-flex justify-content-between align-items-center">
    <a href="<?php echo get_base_url(); ?><?php echo isset($d->volver_url) ? htmlspecialchars($d->volver_url) : ''; ?>" class="btn btn-outline-secondary btn-sm">
      <i class="fas fa-arrow-left"></i> Volver
    </a>
    <a href="<?php echo get_base_url(); ?>resultados/pdf-individual/<?php echo isset($d->aplicacion_id) ? htmlspecialchars($d->aplicacion_id) : ''; ?>" class="btn btn-danger btn-sm">
      <i class="fas fa-file-pdf"></i> Descargar PDF
    </a>
  </div>

  <?php if (empty($d->aplicacion)): ?>
    <div class="col-12">
      <div class="alert alert-danger">Esta aplicación no existe.</div>
    </div>
  <?php else: ?>

    <div class="col-12">
      <div class="card shadow mb-4">
        <div class="card-header py-3">
          <h6 class="m-0 font-weight-bold text-primary">Identidad</h6>
        </div>
        <div class="card-body">
          <dl class="row mb-0">
            <dt class="col-sm-3">Nombre</dt>
            <dd class="col-sm-9"><?php echo htmlspecialchars($d->aplicacion->nombre); ?></dd>
            <dt class="col-sm-3">Número de servidor público</dt>
            <dd class="col-sm-9"><?php echo htmlspecialchars($d->aplicacion->numero_servidor_publico); ?></dd>
            <dt class="col-sm-3">Guía aplicada</dt>
            <dd class="col-sm-9"><?php echo htmlspecialchars($d->guia->nombre ?? '—'); ?></dd>
            <dt class="col-sm-3">Fecha de envío</dt>
            <dd class="col-sm-9"><?php echo htmlspecialchars($d->aplicacion->updated_at); ?></dd>
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

      <div class="col-12">
        <div class="card shadow mb-4">
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Calificación final</h6>
          </div>
          <div class="card-body d-flex align-items-center gap-3">
            <span class="h2 mb-0"><?php echo (int) $d->resultado->calificacion_final; ?></span>
            <span class="badge <?php echo $badgesNivel[$d->resultado->nivel_riesgo] ?? 'bg-secondary'; ?> p-2">
              <?php echo $etiquetasNivel[$d->resultado->nivel_riesgo] ?? htmlspecialchars($d->resultado->nivel_riesgo); ?>
            </span>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-6">
        <div class="card shadow mb-4">
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Desglose por categoría</h6>
          </div>
          <div class="card-body">
            <table class="table table-sm table-bordered mb-0">
              <thead>
                <tr>
                  <th>Categoría</th>
                  <th>Calificación</th>
                  <th>Nivel</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($categorias as $fila): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($fila->nombre); ?></td>
                    <td><?php echo (int) $fila->calificacion; ?></td>
                    <td>
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

      <div class="col-12 col-lg-6">
        <div class="card shadow mb-4">
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Desglose por dominio</h6>
          </div>
          <div class="card-body">
            <table class="table table-sm table-bordered mb-0">
              <thead>
                <tr>
                  <th>Dominio</th>
                  <th>Calificación</th>
                  <th>Nivel</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($dominios as $fila): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($fila->nombre); ?></td>
                    <td><?php echo (int) $fila->calificacion; ?></td>
                    <td>
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

    <?php endif; ?>
  <?php endif; ?>
</div>

<?php require_once INCLUDES . 'admin/dashboardBottom.php'; ?>
