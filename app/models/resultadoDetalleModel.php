<?php
/**
 * Plantilla general de modelos
 * @version 1.0.0
 *
 * Modelo de resultado_detalle
 *
 * Desglose del resultado de una aplicación por categoría y por dominio
 * (Ccat, Cdom — ver docs/Modelo_ER_Cuestionario_NOM-035.md §5.2). Tabla hija
 * de `resultado`, con `ON DELETE CASCADE` (al borrar el `resultado` para
 * recalcular, sus renglones de detalle se borran solos — ver
 * resultadoModel::calcular_para_aplicacion()).
 *
 * @see docs/ARQUITECTURA.md sección "Modelo de datos"
 * @see docs/DDL/ddl.sql
 */
class resultadoDetalleModel extends Model {
  /**
  * Nombre de la tabla
  */
  public static $t1 = 'resultado_detalle';

  // Esquema del Modelo (según docs/DDL/ddl.sql)
  // id               INT PK AUTO_INCREMENT
  // resultado_id     INT FK -> resultado.id  (ON DELETE CASCADE)
  // nivel_agregacion ENUM('categoria','dominio')
  // categoria_id     INT FK -> categoria.id  NULL  -- sólo si nivel_agregacion='categoria'
  // dominio_id       INT FK -> dominio.id    NULL  -- sólo si nivel_agregacion='dominio'
  // calificacion     INT UNSIGNED
  // nivel_riesgo     ENUM('nulo','bajo','medio','alto','muy_alto')

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
   * Regresa el desglose completo (categorías + dominios) de un resultado
   *
   * @param mixed $resultadoId
   * @return array
   */
  static function por_resultado($resultadoId)
  {
    $sql = sprintf('SELECT * FROM %s WHERE resultado_id = :resultado_id', self::$t1);
    return ($rows = parent::query($sql, ['resultado_id' => $resultadoId])) ? $rows : [];
  }
}
