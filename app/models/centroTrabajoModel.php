<?php
/**
 * Plantilla general de modelos
 * @version 1.2.0
 *
 * Modelo de centro_trabajo
 *
 * Datos del centro de trabajo dado de alta por un administrador. Su número
 * de trabajadores determina automáticamente qué guía se le aplica (RF-00,
 * CONFIRMADO contra el texto oficial de la norma): 16 a 50 -> Guía II, más
 * de 50 -> Guía III, 15 o menos -> no requiere cuestionario (ver
 * guiaModel::por_numero_trabajadores()).
 *
 * @see docs/ARQUITECTURA.md sección "Modelo de datos"
 * @see docs/DDL/ddl.sql
 */
class centroTrabajoModel extends Model {
  /**
  * Nombre de la tabla
  */
  public static $t1 = 'centro_trabajo';

  // Esquema del Modelo (según docs/DDL/ddl.sql)
  // id                INT PK AUTO_INCREMENT
  // secretaria_id     INT FK -> secretaria.id
  // administrador_id  INT FK -> usuario.id  NULL  -- un administrador puede tener varios centros (1 a muchos)
  // nombre            VARCHAR(200)
  // num_trabajadores  INT UNSIGNED   -- OJO: se llama "num_trabajadores", NO "numero_trabajadores"
  // created_at        TIMESTAMP
  // updated_at        TIMESTAMP
  //
  // NOTA: esta tabla NO tiene guia_id. La guía se determina en el momento con
  // guiaModel::por_numero_trabajadores(num_trabajadores) y se "congela" en
  // aplicacion.guia_id al responder (no se guarda aquí para no desincronizarse
  // si el número de trabajadores cambia con el tiempo).

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

  /**
   * Regresa todos los centros de trabajo dados de alta por un administrador
   * (recibe usuario.id del administrador, no bee_users.id, ver usuarioModel).
   * Un administrador puede tener varios centros de trabajo (1 a muchos).
   *
   * @param mixed $administradorId
   * @return array
   */
  static function por_administrador($administradorId)
  {
    $sql = sprintf('SELECT * FROM %s WHERE administrador_id = :administrador_id ORDER BY id DESC', self::$t1);
    return ($rows = parent::query($sql, ['administrador_id' => $administradorId])) ? $rows : [];
  }

  /**
   * Regresa todos los centros de trabajo de una secretaría (alcance del
   * súper usuario para resultados/reportes, ver resultadosController)
   *
   * @param mixed $secretariaId
   * @return array
   */
  static function por_secretaria($secretariaId)
  {
    $sql = sprintf('SELECT * FROM %s WHERE secretaria_id = :secretaria_id ORDER BY id DESC', self::$t1);
    return ($rows = parent::query($sql, ['secretaria_id' => $secretariaId])) ? $rows : [];
  }

  /**
   * Determina qué guía corresponde según el número de trabajadores (RF-00,
   * límites ya CONFIRMADOS contra el texto oficial de la norma).
   *
   * @deprecated usar guiaModel::por_numero_trabajadores(), que consulta la
   * tabla `guia` real en vez de duplicar el rango aquí — este método sólo
   * se deja como referencia rápida de la regla, no como fuente de verdad.
   *
   * @param int $numeroTrabajadores
   * @return string|null 'GRII' | 'GRIII' (claves reales de guia.clave), o
   * `null` si $numeroTrabajadores <= 15 — NO es un error, significa que el
   * centro de trabajo no requiere aplicar ningún cuestionario (ver
   * guiaModel::por_numero_trabajadores() para el TODO de cómo mostrar esto).
   */
  static function determinar_guia(int $numeroTrabajadores)
  {
    // TODO: eliminar este método y usar guiaModel::por_numero_trabajadores() en su lugar
    if ($numeroTrabajadores >= 16 && $numeroTrabajadores <= 50) {
      return 'GRII';
    }

    if ($numeroTrabajadores > 50) {
      return 'GRIII';
    }

    // $numeroTrabajadores <= 15: no requiere cuestionario, no es un error
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
