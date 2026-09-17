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
 * `resultado_detalle` (ver resultadoDetalleModel — pendiente de scaffolding),
 * una fila por cada (categoria|dominio) calculado.
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
   * respuestas (RF-05, RF-06, RF-07), incluyendo el desglose en
   * `resultado_detalle` (ver resultadoDetalleModel — pendiente de scaffolding).
   * TODO (fase de Desarrollo): implementar el cálculo real con las tablas de
   * puntaje oficiales (reactivo.polaridad + opcion_respuesta.posicion, ver
   * reactivoModel::calcular_puntaje(), y los umbrales de la tabla `umbral`).
   *
   * @param mixed $aplicacionId
   * @return array|null El resultado calculado
   */
  static function calcular_para_aplicacion($aplicacionId)
  {
    // TODO:
    // 1. Obtener respuestas con respuestaModel::por_aplicacion($aplicacionId) (join a reactivo + opcion_respuesta)
    // 2. Calcular el puntaje de cada respuesta con reactivoModel::calcular_puntaje()
    // 3. Agrupar y sumar por dominio_id y categoria_id (denormalizados en reactivo)
    // 4. Determinar nivel de riesgo con guiaModel::nivel_de_riesgo() (vía tabla `umbral`)
    // 5. Persistir el total con self::insertOne() y el desglose con resultadoDetalleModel::insertOne() por cada dominio/categoria
    return null;
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
