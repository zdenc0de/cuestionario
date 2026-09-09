<?php
/**
 * Plantilla general de controladores
 * @version 1.1.0
 *
 * Controlador de resultados
 *
 * Módulo de RESULTADOS Y REPORTES: individual y agregado; PDF (BeePdf),
 * exportación a Excel y tablero con gráficas (RF-13, RF-14). Compartido
 * entre administrador (resultados de su(s) centro(s) de trabajo) y
 * súper usuario (resultados de toda su secretaría).
 *
 * Requiere sesión de Bee activa (Auth::validate()); a diferencia de
 * administradorController/superusuarioController, aquí NO se exige un único
 * rol de contexto vía requiere_rol() porque ambos roles pueden entrar — en
 * su lugar, el ALCANCE de los datos (qué centros de trabajo puede ver cada
 * quién) se filtra por rol en cada método (ver centrosTrabajoPermitidos() y
 * verificarAccesoCentroTrabajo()).
 *
 * Rutas (ejemplos):
 *   /resultados/individual/{aplicacionId}     -> individual()
 *   /resultados/agregado/{centroTrabajoId}    -> agregado()
 *   /resultados/tablero                       -> tablero()          gráficas en pantalla
 *   /resultados/pdf-individual/{aplicacionId} -> pdf_individual()   descarga PDF (BeePdf)
 *   /resultados/pdf-agregado/{centroTrabajoId} -> pdf_agregado()    descarga PDF (BeePdf)
 *   /resultados/excel/{centroTrabajoId}       -> excel()            exportación a Excel
 *   /resultados/datos-grafica/{centroTrabajoId} -> datos_grafica()  JSON para el tablero
 *
 * @see docs/ARQUITECTURA.md
 */
class resultadosController extends Controller implements ControllerInterface
{
  function __construct()
  {
    // Validación de sesión de usuario. A propósito NO se usa requiere_rol()
    // aquí: tanto 'administrador' como 'superusuario' pueden consultar
    // resultados, sólo cambia su alcance de datos (ver centrosTrabajoPermitidos()).
    if (!Auth::validate()) {
      Flasher::new('Debes iniciar sesión primero.', 'danger');
      Redirect::to('login');
    }

    // Si la sesión es válida pero no tiene rol de contexto asignado
    // (usuarioModel), no hay alcance de datos posible: se deniega.
    if (obtener_rol_usuario_actual() === null) {
      Flasher::deny(2); // 'Permisos denegados.'
      Redirect::to(DEFAULT_CONTROLLER);
    }

    // Ejecutar la funcionalidad del Controller padre
    parent::__construct();
  }

  /**
   * Regresa los IDs de centro_trabajo que el usuario en sesión puede
   * consultar, según su rol de contexto (usuarioModel):
   *   - administrador: únicamente los centros de trabajo que él mismo dio de alta
   *   - superusuario: todos los centros de trabajo de su secretaría
   *
   * TODO (fase de Desarrollo): una vez existan las tablas, confirmar que
   * centroTrabajoModel::por_administrador()/por_secretaria() cubren el caso
   * de un administrador con múltiples secretarías si llegara a aplicar.
   *
   * @return array<int>
   */
  private function centrosTrabajoPermitidos()
  {
    $usuario = obtener_usuario_actual();
    $rol     = $usuario['rol'] ?? null;

    if ($rol === 'administrador') {
      $centros = centroTrabajoModel::por_administrador($usuario['id'] ?? null);
    } elseif ($rol === 'superusuario') {
      $centros = centroTrabajoModel::por_secretaria($usuario['secretaria_id'] ?? null);
    } else {
      $centros = [];
    }

    return array_column($centros, 'id');
  }

  /**
   * Deniega y redirige si el centro de trabajo solicitado no está dentro del
   * alcance del usuario en sesión (ver centrosTrabajoPermitidos()). "Falla
   * cerrado": un $centroTrabajoId nulo o no encontrado también se deniega.
   *
   * @param mixed $centroTrabajoId
   * @return void
   */
  private function verificarAccesoCentroTrabajo($centroTrabajoId)
  {
    if ($centroTrabajoId === null || !in_array($centroTrabajoId, $this->centrosTrabajoPermitidos())) {
      Flasher::deny(2); // 'Permisos denegados.'
      Redirect::to(DEFAULT_CONTROLLER);
    }
  }

  /**
   * Página de entrada del módulo de resultados
   */
  function index()
  {
    $this->setTitle('Resultados y reportes');
    $this->setView('index'); // templates/views/resultados/indexView.php
    $this->render();
  }

  /**
   * Resultado individual de una aplicación/encuestado (RF-13)
   * TODO (fase de Desarrollo): cargar con resultadoModel::por_aplicacion()
   *
   * @param mixed $aplicacionId
   */
  function individual($aplicacionId = null)
  {
    // TODO: reemplazar por la consulta real una vez exista la tabla `aplicacion`;
    // mientras tanto by_id() regresa [] y el acceso se deniega (falla cerrado)
    $aplicacion = aplicacionModel::by_id($aplicacionId);
    $this->verificarAccesoCentroTrabajo($aplicacion['centro_trabajo_id'] ?? null);

    // TODO: registrar_auditoria('consulta_resultado_individual', 'aplicacion', $aplicacionId) (RF-12, RNF-01)
    // TODO: $this->addToData('resultado', resultadoModel::por_aplicacion($aplicacionId));

    $this->setTitle('Resultado individual');
    $this->addToData('aplicacion_id', $aplicacionId);
    $this->setView('individual'); // templates/views/resultados/individualView.php
    $this->render();
  }

  /**
   * Resultado agregado de un centro de trabajo (RF-13)
   * TODO (fase de Desarrollo): cargar con resultadoModel::agregado_por_centro_trabajo()
   * y calcular estadísticos agregados (promedios, distribución por nivel de
   * riesgo, etc.)
   *
   * @param mixed $centroTrabajoId
   */
  function agregado($centroTrabajoId = null)
  {
    $this->verificarAccesoCentroTrabajo($centroTrabajoId);

    // TODO: registrar_auditoria('consulta_resultado_agregado', 'centro_trabajo', $centroTrabajoId)

    $this->setTitle('Resultado agregado');
    $this->addToData('centro_trabajo_id', $centroTrabajoId);
    $this->setView('agregado'); // templates/views/resultados/agregadoView.php
    $this->render();
  }

  /**
   * Tablero en pantalla con gráficas (RF-14)
   * TODO (fase de Desarrollo): usar BeeQuickChart o Chart.js (ya incluido en
   * assets/js/admin/Chart.min.js) alimentado por datos_grafica()
   */
  function tablero()
  {
    register_scripts([JS . 'admin/Chart.min.js'], 'Chart.js para el tablero de resultados');

    $this->setTitle('Tablero de resultados');
    $this->setView('tablero'); // templates/views/resultados/tableroView.php
    $this->render();
  }

  /**
   * Endpoint JSON con los datos para alimentar las gráficas del tablero
   * TODO (fase de Desarrollo): construir el payload (distribución por nivel de
   * riesgo, promedio por dominio/categoría) a partir de resultadoModel
   *
   * @param mixed $centroTrabajoId
   */
  function datos_grafica($centroTrabajoId = null)
  {
    $this->verificarAccesoCentroTrabajo($centroTrabajoId);

    // TODO: construir $data real a partir de resultadoModel::agregado_por_centro_trabajo()
    $data = [];

    json_output(json_build(200, $data, 'Datos de gráfica (pendiente de implementación).'));
  }

  /**
   * Genera el PDF del resultado individual usando BeePdf (RF-14)
   * TODO (fase de Desarrollo): construir el HTML del reporte (ver
   * templates/views/resultados/pdfIndividualView.php) y pasarlo a BeePdf
   *
   * @param mixed $aplicacionId
   */
  function pdf_individual($aplicacionId = null)
  {
    $aplicacion = aplicacionModel::by_id($aplicacionId);
    $this->verificarAccesoCentroTrabajo($aplicacion['centro_trabajo_id'] ?? null);

    // TODO: registrar_auditoria('descarga_pdf_individual', 'aplicacion', $aplicacionId)
    // TODO:
    // ob_start();
    // require_once VIEWS . 'resultados' . DS . 'pdfIndividualView.php';
    // $html = ob_get_clean();
    // $pdf  = new BeePdf($html, true, false); // $download = true

    Flasher::error('Funcionalidad pendiente de implementación (fase de Desarrollo).');
    Redirect::back();
  }

  /**
   * Genera el PDF del resultado agregado de un centro de trabajo usando BeePdf (RF-14)
   * TODO (fase de Desarrollo): construir el HTML del reporte (ver
   * templates/views/resultados/pdfAgregadoView.php) y pasarlo a BeePdf
   *
   * @param mixed $centroTrabajoId
   */
  function pdf_agregado($centroTrabajoId = null)
  {
    $this->verificarAccesoCentroTrabajo($centroTrabajoId);

    // TODO: registrar_auditoria('descarga_pdf_agregado', 'centro_trabajo', $centroTrabajoId)

    Flasher::error('Funcionalidad pendiente de implementación (fase de Desarrollo).');
    Redirect::back();
  }

  /**
   * Exportación a Excel de los resultados de un centro de trabajo (RF-14)
   *
   * NOTA: el composer.json actual del proyecto no incluye una librería de Excel
   * (ej. phpoffice/phpspreadsheet). TODO (fase de Desarrollo):
   *   a) composer require phpoffice/phpspreadsheet, o
   *   b) exportar como CSV compatible con Excel si no se agrega la dependencia.
   *
   * @param mixed $centroTrabajoId
   */
  function excel($centroTrabajoId = null)
  {
    $this->verificarAccesoCentroTrabajo($centroTrabajoId);

    // TODO: registrar_auditoria('descarga_excel', 'centro_trabajo', $centroTrabajoId)

    Flasher::error('Funcionalidad pendiente de implementación (fase de Desarrollo).');
    Redirect::back();
  }
}
