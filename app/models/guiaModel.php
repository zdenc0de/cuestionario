<?php
/**
 * Plantilla general de modelos
 * @version 1.0.0
 *
 * Modelo de guia
 *
 * Cada versión del instrumento (Guía II de 46 reactivos y Guía III de 72
 * reactivos). Es la entidad que permite soportar ambas guías sin duplicar
 * código (nombre, número de reactivos, rango de trabajadores, umbrales).
 *
 * @see docs/ARQUITECTURA.md sección "Modelo de datos"
 */
class guiaModel extends Model {
  /**
  * Nombre de la tabla
  */
  public static $t1 = 'guia';

  // Esquema del Modelo
  // TODO (fase de Diseño): transcribir de las tablas oficiales de la norma (Secretaría las tiene en físico)
  // id                    INT PK AUTO_INCREMENT
  // nombre                VARCHAR(50)   -- 'Guía II' | 'Guía III'
  // numero_reactivos      INT           -- 46 | 72
  // min_trabajadores      INT           -- 15
  // max_trabajadores      INT NULL      -- 50 (NULL = sin límite superior para Guía III)
  // umbrales_json         TEXT/JSON     -- TODO: tabla de rangos de calificación final por nivel de riesgo (nulo/bajo/medio/alto/muy alto)
  // umbrales_dominio_json TEXT/JSON     -- TODO: rangos de calificación por dominio
  // umbrales_categoria_json TEXT/JSON   -- TODO: rangos de calificación por categoría
  // creado                DATETIME

  function __construct()
  {
    // Constructor general
  }

  static function insertOne(array $data)
  {
    return parent::add(self::$t1, $data);
  }

  static function all()
  {
    // Todos los registros
    $sql = sprintf('SELECT * FROM %s ORDER BY id ASC', self::$t1);
    return ($rows = parent::query($sql)) ? $rows : [];
  }

  static function by_id($id)
  {
    // Un registro con $id
    $sql = sprintf('SELECT * FROM %s WHERE id = :id LIMIT 1', self::$t1);
    return ($rows = parent::query($sql, ['id' => $id])) ? $rows[0] : [];
  }

  /**
   * Regresa la guía correspondiente según el número de trabajadores del
   * centro de trabajo (RF-00)
   * TODO (fase de Diseño): confirmar límites exactos contra la norma
   *
   * @param int $numeroTrabajadores
   * @return array|null
   */
  static function por_numero_trabajadores(int $numeroTrabajadores)
  {
    $sql = sprintf(
      'SELECT * FROM %s WHERE :n >= min_trabajadores AND (max_trabajadores IS NULL OR :n2 <= max_trabajadores) LIMIT 1',
      self::$t1
    );
    return ($rows = parent::query($sql, ['n' => $numeroTrabajadores, 'n2' => $numeroTrabajadores])) ? $rows[0] : null;
  }

  /**
   * Regresa el nivel de riesgo correspondiente a una calificación final
   * TODO (fase de Diseño): implementar con los umbrales oficiales (umbrales_json)
   *
   * @param mixed $guiaId
   * @param float $calificacionFinal
   * @return string 'nulo'|'bajo'|'medio'|'alto'|'muy_alto'
   */
  static function nivel_de_riesgo($guiaId, float $calificacionFinal)
  {
    // TODO: cargar umbrales_json de la guía y comparar $calificacionFinal contra los rangos
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
