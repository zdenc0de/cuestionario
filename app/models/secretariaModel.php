<?php
/**
 * Plantilla general de modelos
 * @version 1.1.0
 *
 * Modelo de secretaria
 *
 * Representa a la dependencia titular del instrumento (en este despliegue,
 * la Secretaría de Cultura y Turismo del Estado de México). Cada secretaría
 * cuenta con un súper usuario que administra a sus administradores.
 *
 * @see docs/ARQUITECTURA.md
 * @see docs/DDL/ddl.sql
 */
class secretariaModel extends Model {
  /**
  * Nombre de la tabla
  */
  public static $t1 = 'secretaria';

  // Esquema del Modelo (según docs/DDL/ddl.sql)
  // id         INT PK AUTO_INCREMENT
  // nombre     VARCHAR(200)  -- ej. "Secretaría de Cultura y Turismo"
  // created_at TIMESTAMP
  // updated_at TIMESTAMP
  // TODO (fase de Diseño): considerar una columna `logo` más adelante — el proyecto
  // trata sobre la identidad de la Secretaría (logo en encabezado y en los reportes
  // PDF/Excel, ver resultadosController). No urge; no está en el DDL actual, y si se
  // agrega debe ir primero en docs/DDL/ddl.sql.

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
