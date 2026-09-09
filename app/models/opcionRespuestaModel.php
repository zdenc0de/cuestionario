<?php
/**
 * Plantilla general de modelos
 * @version 1.0.0
 *
 * Modelo de opcion_respuesta
 *
 * Las 5 opciones de la escala Likert (Nunca, Casi nunca, Algunas veces,
 * Casi siempre, Siempre) y su valor de 0 a 4 (RF-01, sección 7.1).
 *
 * @see docs/ARQUITECTURA.md sección "Modelo de datos"
 */
class opcionRespuestaModel extends Model {
  /**
  * Nombre de la tabla
  */
  public static $t1 = 'opcion_respuesta';

  // Esquema del Modelo
  // TODO (fase de Diseño): confirmar si el valor 0-4 se invierte a nivel de reactivo
  // (según polaridad) o se guarda ya invertido aquí. Definido en sección 7.1 del plan.
  // id      INT PK AUTO_INCREMENT
  // texto   VARCHAR(50)   -- 'Nunca' | 'Casi nunca' | 'Algunas veces' | 'Casi siempre' | 'Siempre'
  // valor   TINYINT       -- 0 a 4
  // orden   TINYINT       -- orden de despliegue en la escala

  function __construct()
  {
    // Constructor general
  }

  static function insertOne(array $data)
  {
    return parent::add(self::$t1, $data);
  }

  /**
   * Regresa las 5 opciones de la escala Likert ordenadas para despliegue
   *
   * @return array
   */
  static function escala()
  {
    $sql = sprintf('SELECT * FROM %s ORDER BY orden ASC', self::$t1);
    return ($rows = parent::query($sql)) ? $rows : [];
  }

  static function by_id($id)
  {
    // Un registro con $id
    $sql = sprintf('SELECT * FROM %s WHERE id = :id LIMIT 1', self::$t1);
    return ($rows = parent::query($sql, ['id' => $id])) ? $rows[0] : [];
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
