<?php
/**
 * Plantilla general de modelos
 * @version 1.0.0
 *
 * Modelo de respuesta
 *
 * El valor elegido por el encuestado para cada reactivo de una aplicación.
 *
 * @see docs/ARQUITECTURA.md sección "Modelo de datos"
 */
class respuestaModel extends Model {
  /**
  * Nombre de la tabla
  */
  public static $t1 = 'respuesta';

  // Esquema del Modelo
  // TODO (fase de Diseño): confirmar si "valor" se guarda ya normalizado según polaridad (RF-05)
  // o se calcula al vuelo en resultadoModel a partir de opcion_respuesta.valor + reactivo.polaridad
  // id                  INT PK AUTO_INCREMENT
  // aplicacion_id       INT FK -> aplicacion.id
  // reactivo_id         INT FK -> reactivo.id
  // opcion_respuesta_id INT FK -> opcion_respuesta.id
  // valor               TINYINT  -- valor final considerando polaridad
  // creado              DATETIME

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
   * Regresa todas las respuestas de una aplicación (para calcular resultado)
   *
   * @param mixed $aplicacionId
   * @return array
   */
  static function por_aplicacion($aplicacionId)
  {
    $sql = sprintf('SELECT * FROM %s WHERE aplicacion_id = :aplicacion_id', self::$t1);
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
