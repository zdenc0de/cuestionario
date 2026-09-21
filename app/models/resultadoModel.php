<?php
/**
 * Plantilla general de modelos
 * @version 1.2.0
 *
 * Modelo de resultado
 *
 * Calificación final y nivel de riesgo de cada aplicación (RF-06, RF-07).
 * Base de los reportes individual y agregado. El desglose por dominio y por
 * categoría NO vive aquí como JSON: está normalizado en la tabla hija
 * `resultado_detalle` (ver resultadoDetalleModel), una fila por cada
 * (categoria|dominio) calculado.
 *
 * @see docs/ARQUITECTURA.md sección "Modelo de datos"
 * @see docs/DDL/ddl.sql
 * @see docs/norma/Transcripcion_GuiaIII_NOM-035.md sección 3 (umbrales de la Guía III)
 */
class resultadoModel extends Model {
  /**
  * Nombre de la tabla
  */
  public static $t1 = 'resultado';

  // Esquema del Modelo (según docs/DDL/ddl.sql)
  // id                 INT PK AUTO_INCREMENT
  // aplicacion_id       INT FK -> aplicacion.id  UNIQUE
  // calificacion_final  INT UNSIGNED   -- entero, no decimal (los umbrales de la norma son enteros)
  // nivel_riesgo        ENUM('nulo','bajo','medio','alto','muy_alto')
  // fecha_calculo        TIMESTAMP

  function __construct()
  {
    // Constructor general
  }

  static function insertOne(array $data)
  {
    return parent::add(self::$t1, $data);
  }

  static function by_id($id)
  {
    // Un registro con $id
    $sql = sprintf('SELECT * FROM %s WHERE id = :id LIMIT 1', self::$t1);
    return ($rows = parent::query($sql, ['id' => $id])) ? $rows[0] : [];
  }

  /**
   * Regresa el resultado individual de una aplicación (RF-13)
   *
   * @param mixed $aplicacionId
   * @return array|null
   */
  static function por_aplicacion($aplicacionId)
  {
    $sql = sprintf('SELECT * FROM %s WHERE aplicacion_id = :aplicacion_id LIMIT 1', self::$t1);
    return ($rows = parent::query($sql, ['aplicacion_id' => $aplicacionId])) ? $rows[0] : null;
  }

  /**
   * Regresa todos los resultados de un centro de trabajo, para el resultado
   * agregado (RF-13). Hace doble JOIN (aplicacion -> token) porque ni
   * `resultado` ni `aplicacion` tienen centro_trabajo_id propio.
   *
   * @param mixed $centroTrabajoId
   * @return array
   */
  static function agregado_por_centro_trabajo($centroTrabajoId)
  {
    $sql =
      'SELECT r.* FROM %s r
       INNER JOIN aplicacion a ON a.id = r.aplicacion_id
       INNER JOIN token t ON t.id = a.token_id
       WHERE t.centro_trabajo_id = :centro_trabajo_id';
    $sql = sprintf($sql, self::$t1);
    return ($rows = parent::query($sql, ['centro_trabajo_id' => $centroTrabajoId])) ? $rows : [];
  }

  /**
   * Calcula y persiste el resultado de una aplicación a partir de sus
   * respuestas (RF-05, RF-06, RF-07), incluyendo el desglose por categoría y
   * por dominio en `resultado_detalle`. Motor de calificación — ver
   * docs/HANDOFF_DESARROLLO.md §5 y docs/Modelo_ER_Cuestionario_NOM-035.md §5.2.
   *
   * Algoritmo:
   *   1. respuestaModel::por_aplicacion() trae cada respuesta ya con
   *      polaridad/dominio_id/categoria_id/posicion (JOIN a reactivo + opcion_respuesta).
   *   2. reactivoModel::calcular_puntaje() da el puntaje de cada una
   *      (normal → 4-posicion, invertida → posicion).
   *   3. Se suma en PHP por dominio_id, por categoria_id y el total (Cfinal).
   *      Los reactivos omitidos por un filtro simplemente no tienen fila en
   *      `respuesta`, así que no aportan a ninguna suma — no hay que
   *      restarlos ni tratarlos aparte (así lo confirma el handoff). Un
   *      dominio/categoría sin ninguna respuesta no genera renglón de
   *      detalle; en el instrumento sembrado esto nunca ocurre en la
   *      práctica porque todo dominio/categoría condicionado por un filtro
   *      comparte reactivos incondicionales con el mismo dominio/categoría.
   *   4. umbralModel traduce cada suma a un nivel de riesgo.
   *   5. Persistencia atómica e idempotente: se usa una transacción real
   *      manejada aquí mismo (Db::connect()->beginTransaction()/commit()/
   *      rollBack(), con `['transaction' => false]` en cada Model::query()
   *      individual — Model::query() hace auto-commit por default, así que
   *      hay que desactivarlo para agrupar varios INSERT en una sola
   *      transacción, ver app/classes/Db.php). Idempotente: se borra primero
   *      cualquier `resultado` previo de esta aplicación — el `ON DELETE
   *      CASCADE` de `resultado_detalle` se encarga del desglose viejo — y
   *      se inserta todo de nuevo, en vez de intentar un UPDATE parcial.
   *
   * @param mixed $aplicacionId
   * @return array|null El resultado calculado: ['resultado' => array, 'detalle' => array[]], null si la aplicación no existe o no tiene respuestas
   * @throws Exception si falla la persistencia (la transacción se revierte completa)
   */
  static function calcular_para_aplicacion($aplicacionId)
  {
    $aplicacion = aplicacionModel::by_id($aplicacionId);
    if (empty($aplicacion)) {
      return null;
    }

    $respuestas = respuestaModel::por_aplicacion($aplicacionId);
    if (empty($respuestas)) {
      return null;
    }

    // ---- 1-3. Puntaje por respuesta y suma por dominio / categoría / total ----
    $guiaId       = $aplicacion['guia_id'];
    $final        = 0;
    $porDominio   = []; // dominio_id   => suma
    $porCategoria = []; // categoria_id => suma

    foreach ($respuestas as $respuesta) {
      $puntaje = reactivoModel::calcular_puntaje($respuesta, (int) $respuesta['posicion']);

      $final                                  += $puntaje;
      $porDominio[$respuesta['dominio_id']]     = ($porDominio[$respuesta['dominio_id']] ?? 0) + $puntaje;
      $porCategoria[$respuesta['categoria_id']] = ($porCategoria[$respuesta['categoria_id']] ?? 0) + $puntaje;
    }

    // ---- 4. Nivel de riesgo de cada suma (tabla `umbral`) ----
    $nivelFinal = umbralModel::nivel_final($guiaId, $final);

    $detalle = [];
    foreach ($porDominio as $dominioId => $calificacion) {
      $detalle[] =
      [
        'nivel_agregacion' => 'dominio',
        'categoria_id'     => null,
        'dominio_id'       => $dominioId,
        'calificacion'     => $calificacion,
        'nivel_riesgo'     => umbralModel::nivel_dominio($dominioId, $calificacion)
      ];
    }
    foreach ($porCategoria as $categoriaId => $calificacion) {
      $detalle[] =
      [
        'nivel_agregacion' => 'categoria',
        'categoria_id'     => $categoriaId,
        'dominio_id'       => null,
        'calificacion'     => $calificacion,
        'nivel_riesgo'     => umbralModel::nivel_categoria($categoriaId, $calificacion)
      ];
    }

    // ---- 5. Persistencia atómica e idempotente ----
    $link = Db::connect();

    // OJO — particularidad del núcleo de Bee (app/classes/Db.php, no se
    // modifica): Db::query() con opciones por default (transaction=true)
    // siempre hace beginTransaction() antes de ejecutar, pero en la rama
    // SELECT hace `return` ANTES del commit() de abajo — es decir, CUALQUIER
    // SELECT con opciones por default deja la conexión con una transacción
    // "colgada" sin cerrar. Como arriba ya se hicieron varias lecturas por
    // default (aplicacionModel::by_id(), respuestaModel::por_aplicacion(),
    // umbralModel::nivel_*()), el link puede llegar aquí ya "en transacción".
    // Se cierra esa transacción colgada (no hay nada que perder: sólo hubo
    // lecturas) antes de abrir la transacción real que sí vamos a controlar.
    if ($link->inTransaction()) {
      $link->commit();
    }

    try {
      $link->beginTransaction();

      // Idempotencia: borra el resultado previo de esta aplicación (si existe);
      // ON DELETE CASCADE se lleva su resultado_detalle con él.
      parent::query(
        sprintf('DELETE FROM %s WHERE aplicacion_id = :aplicacion_id', self::$t1),
        ['aplicacion_id' => $aplicacionId],
        ['transaction' => false]
      );

      $resultadoId = parent::query(
        sprintf('INSERT INTO %s (aplicacion_id, calificacion_final, nivel_riesgo) VALUES (:aplicacion_id, :calificacion_final, :nivel_riesgo)', self::$t1),
        ['aplicacion_id' => $aplicacionId, 'calificacion_final' => $final, 'nivel_riesgo' => $nivelFinal],
        ['transaction' => false]
      );

      // Nota: NO se usa resultadoDetalleModel::insertOne() aquí — ese método
      // usa Model::query() con transaction=true (auto-commit) por default,
      // lo que cerraría esta transacción antes de tiempo. Se inserta
      // directamente contra su tabla, con transaction=false, igual que el
      // resto de esta transacción manual.
      $sqlDetalle = sprintf(
        'INSERT INTO %s (resultado_id, nivel_agregacion, categoria_id, dominio_id, calificacion, nivel_riesgo)
         VALUES (:resultado_id, :nivel_agregacion, :categoria_id, :dominio_id, :calificacion, :nivel_riesgo)',
        resultadoDetalleModel::$t1
      );

      foreach ($detalle as &$fila) {
        $fila['resultado_id'] = $resultadoId;
        parent::query($sqlDetalle, $fila, ['transaction' => false]);
      }
      unset($fila);

      $link->commit();

    } catch (Exception $e) {
      if ($link->inTransaction()) {
        $link->rollBack();
      }
      throw $e;
    }

    return
    [
      'resultado' => self::by_id($resultadoId),
      'detalle'   => $detalle
    ];
  }

  static function update_by_id($id, $params)
  {
    return parent::update(self::$t1, ['id' => $id], $params);
  }

  static function delete_by_id($id)
  {
    return parent::remove(self::$t1, ['id' => $id]);
  }
}
