<?php
/**
 * Plantilla general de modelos
 * @version 1.0.0
 *
 * Modelo de categoria
 *
 * Categorías del instrumento (catálogo, parte del seed — ver
 * docs/Modelo_ER_Cuestionario_NOM-035.md sección 3). Cada guía tiene su
 * propio árbol de categorías (GRII tiene 4, GRIII tiene 5 — agrega "Entorno
 * organizacional"). Modelo mínimo de sólo lectura: el motor de calificación
 * no necesita iterarlas para calcular (usa `reactivo.categoria_id`
 * denormalizado, ver reactivoModel), sólo para nombrar renglones del
 * desglose en reportes.
 *
 * @see docs/ARQUITECTURA.md sección "Modelo de datos"
 * @see docs/DDL/ddl.sql
 */
class categoriaModel extends Model {
  /**
  * Nombre de la tabla
  */
  public static $t1 = 'categoria';

  // Esquema del Modelo (según docs/DDL/ddl.sql)
  // id      INT PK AUTO_INCREMENT
  // guia_id INT FK -> guia.id
  // nombre  VARCHAR(150)
  // orden   TINYINT UNSIGNED

  function __construct()
  {
    // Constructor general
  }

  static function by_id($id)
  {
    // Un registro con $id
    $sql = sprintf('SELECT * FROM %s WHERE id = :id LIMIT 1', self::$t1);
    return ($rows = parent::query($sql, ['id' => $id])) ? $rows[0] : [];
  }

  /**
   * Regresa todas las categorías de una guía, en orden de despliegue
   *
   * @param mixed $guiaId
   * @return array
   */
  static function por_guia($guiaId)
  {
    $sql = sprintf('SELECT * FROM %s WHERE guia_id = :guia_id ORDER BY orden ASC', self::$t1);
    return ($rows = parent::query($sql, ['guia_id' => $guiaId])) ? $rows : [];
  }
}
