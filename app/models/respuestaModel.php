<?php
/**
 * Plantilla general de modelos
 * @version 1.1.0
 *
 * Modelo de respuesta
 *
 * La opción elegida por el encuestado para cada reactivo de una aplicación.
 *
 * AJUSTE contra docs/DDL/ddl.sql: esta tabla NO guarda un puntaje (`valor`)
 * ya calculado — sólo guarda qué opción se eligió (opcion_respuesta_id). El
 * puntaje se deriva en el momento con reactivoModel::calcular_puntaje()
 * (reactivo.polaridad + opcion_respuesta.posicion), no se persiste aquí.
 * Ver docs/ARQUITECTURA.md si se decide agregar una columna denormalizada
 * para optimizar reportes.
 *
 * @see docs/ARQUITECTURA.md sección "Modelo de datos"
 * @see docs/DDL/ddl.sql
 */
class respuestaModel extends Model {
  /**
  * Nombre de la tabla
  */
  public static $t1 = 'respuesta';

  // Esquema del Modelo (según docs/DDL/ddl.sql)
  // id                  INT PK AUTO_INCREMENT
  // aplicacion_id       INT FK -> aplicacion.id
  // reactivo_id         INT FK -> reactivo.id
  // opcion_respuesta_id INT FK -> opcion_respuesta.id
  // UNIQUE (aplicacion_id, reactivo_id)

  function __construct()
  {
    // Constructor general
  }

  static function insertOne(array $data)
  {
    return parent::add(self::$t1, $data);
  }

  /**
   * Inserta en lote todas las respuestas de una aplicación
   * TODO: envolver en transacción (ver Db::query con opción 'transaction')
   *
   * @param array $respuestas
   * @return bool
   */
  static function insertar_lote(array $respuestas)
  {
    // TODO: iterar $respuestas e insertar cada una con insertOne(), idealmente en una sola transacción
    foreach ($respuestas as $respuesta) {
      if (!self::insertOne($respuesta)) {
        return false;
      }
    }
    return true;
  }

  static function by_id($id)
  {
    // Un registro con $id
    $sql = sprintf('SELECT * FROM %s WHERE id = :id LIMIT 1', self::$t1);
    return ($rows = parent::query($sql, ['id' => $id])) ? $rows[0] : [];
  }

  /**
   * Regresa todas las respuestas de una aplicación, con el dato del reactivo
   * (polaridad, dominio_id, categoria_id) y de la opción elegida (posicion)
   * ya incluidos vía JOIN — insumo directo para
   * resultadoModel::calcular_para_aplicacion().
   *
   * @param mixed $aplicacionId
   * @return array
   */
  static function por_aplicacion($aplicacionId)
  {
    $sql =
      'SELECT
        r.*,
        rc.polaridad, rc.dominio_id, rc.categoria_id,
        op.posicion
       FROM %s r
       INNER JOIN reactivo rc         ON rc.id = r.reactivo_id
       INNER JOIN opcion_respuesta op ON op.id = r.opcion_respuesta_id
       WHERE r.aplicacion_id = :aplicacion_id';
    $sql = sprintf($sql, self::$t1);
    return ($rows = parent::query($sql, ['aplicacion_id' => $aplicacionId])) ? $rows : [];
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
