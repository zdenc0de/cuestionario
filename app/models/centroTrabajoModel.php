<?php
/**
 * Plantilla general de modelos
 * @version 1.0.0
 *
 * Modelo de centro_trabajo
 *
 * Datos del centro de trabajo dado de alta por un administrador. Su número
 * de trabajadores determina automáticamente qué guía se le aplica (RF-00):
 * 15 a 50 -> Guía II, más de 50 -> Guía III.
 *
 * @see docs/ARQUITECTURA.md
 */
class centroTrabajoModel extends Model {
  /**
  * Nombre de la tabla
  */
  public static $t1 = 'centro_trabajo';

  // Esquema del Modelo
  // TODO (fase de Diseño): confirmar columnas finales
  // id                  INT PK AUTO_INCREMENT
  // secretaria_id       INT FK -> secretaria.id
  // administrador_id    INT FK -> usuario.id (administrador responsable)
  // nombre              VARCHAR(150)
  // numero_trabajadores INT
  // guia_id             INT FK -> guia.id  -- se determina con base en numero_trabajadores
  // creado              DATETIME

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
   * (recibe usuario.id del administrador, no bee_users.id, ver usuarioModel)
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
   * Determina qué guía corresponde según el número de trabajadores (RF-00)
   * TODO (fase de Diseño): confirmar rangos exactos y mover a guiaModel::por_numero_trabajadores()
   * si se requiere lógica más elaborada (ej. límites exactos, guía I fuera de alcance)
   *
   * @param int $numeroTrabajadores
   * @return string|null 'guia_ii' | 'guia_iii'
   */
  static function determinar_guia(int $numeroTrabajadores)
  {
    // TODO: reemplazar por consulta a guiaModel con los rangos oficiales
    if ($numeroTrabajadores >= 15 && $numeroTrabajadores <= 50) {
      return 'guia_ii';
    }

    if ($numeroTrabajadores > 50) {
      return 'guia_iii';
    }

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
