<?php
/**
 * Plantilla general de modelos
 * @version 1.0.0
 *
 * Modelo de secretaria
 *
 * Representa a la dependencia titular del instrumento (en este despliegue,
 * la Secretaría de Cultura y Turismo del Estado de México). Cada secretaría
 * cuenta con un súper usuario que administra a sus administradores.
 *
 * @see docs/ARQUITECTURA.md
 */
class secretariaModel extends Model {
  /**
  * Nombre de la tabla
  */
  public static $t1 = 'secretaria';

  // Esquema del Modelo
  // TODO (fase de Diseño): confirmar columnas finales (logo, siglas, colores institucionales si aplican por dependencia)
  // id               INT PK AUTO_INCREMENT
  // nombre           VARCHAR(150)  -- ej. "Secretaría de Cultura y Turismo"
  // siglas           VARCHAR(20)   NULL
  // logo             VARCHAR(255)  NULL
  // creado           DATETIME

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
    $sql = sprintf('SELECT * FROM %s ORDER BY id DESC', self::$t1);
    return ($rows = parent::query($sql)) ? $rows : [];
  }

  static function all_paginated()
  {
    // Todos los registros
    $sql = sprintf('SELECT * FROM %s ORDER BY id DESC', self::$t1);
    return PaginationHandler::paginate($sql);
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
