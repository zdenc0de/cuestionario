<?php
/**
 * Plantilla general de modelos
 * @version 1.0.0
 *
 * Modelo de dominio
 *
 * Dominios del instrumento (catálogo, parte del seed — ver
 * docs/Modelo_ER_Cuestionario_NOM-035.md sección 3), agrupados dentro de una
 * `categoria`. Modelo mínimo de sólo lectura: el motor de calificación no
 * necesita iterarlos para calcular (usa `reactivo.dominio_id` denormalizado,
 * ver reactivoModel), sólo para nombrar renglones del desglose en reportes.
 *
 * @see docs/ARQUITECTURA.md sección "Modelo de datos"
 * @see docs/DDL/ddl.sql
 */
class dominioModel extends Model {
  /**
  * Nombre de la tabla
  */
  public static $t1 = 'dominio';

  // Esquema del Modelo (según docs/DDL/ddl.sql)
  // id           INT PK AUTO_INCREMENT
  // categoria_id INT FK -> categoria.id
  // nombre       VARCHAR(150)
  // orden        TINYINT UNSIGNED

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
   * Regresa todos los dominios de una categoría, en orden de despliegue
   *
   * @param mixed $categoriaId
   * @return array
   */
  static function por_categoria($categoriaId)
  {
    $sql = sprintf('SELECT * FROM %s WHERE categoria_id = :categoria_id ORDER BY orden ASC', self::$t1);
    return ($rows = parent::query($sql, ['categoria_id' => $categoriaId])) ? $rows : [];
  }
}
