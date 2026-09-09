<?php
/**
 * Plantilla general de modelos
 * @version 1.1.0
 *
 * Modelo de usuario
 *
 * DECISIÓN DE DISEÑO (confirmada, ver docs/ARQUITECTURA.md sección
 * "Decisiones de diseño — A. Autenticación/roles"): el sistema NATIVO de Bee
 * (tabla bee_users + clase Auth) es la ÚNICA fuente de verdad de
 * identidad/login. usuarioModel **NO duplica usuarios**: es una tabla de
 * ENLACE (1 a 1 con bee_users) que agrega el contexto que Bee no modela de
 * forma nativa entre un usuario y una secretaría/centro de trabajo:
 *
 *   - rol de contexto: 'superusuario' | 'administrador'
 *   - secretaria_id: obligatorio para 'superusuario'
 *   - centro_trabajo_id: obligatorio para 'administrador'
 *
 * (No se usa bee_roles/bee_permisos para este gate porque esas tablas
 * modelan roles y permisos granulares genéricos sin columna de enlace a
 * bee_users; el rol de contexto de este proyecto es binario y vive aquí.)
 *
 * Consumido por requiere_rol() y obtener_rol_usuario_actual() en
 * app/functions/bee_custom_functions.php para el guard de acceso de
 * administradorController y superusuarioController.
 *
 * @see docs/ARQUITECTURA.md
 */
class usuarioModel extends Model {
  /**
  * Nombre de la tabla
  */
  public static $t1 = 'usuario';

  // Nombre de tablas relacionadas (núcleo de Bee, no modificar)
  // public static $t2 = 'bee_users';

  // Esquema del Modelo
  // TODO (fase de Diseño): confirmar columnas finales
  // id                INT PK AUTO_INCREMENT
  // bee_user_id       INT FK -> bee_users.id  UNIQUE (relación 1 a 1, ver docblock de la clase)
  // rol               ENUM('superusuario','administrador')
  // secretaria_id     INT FK -> secretaria.id     NULL (obligatorio si rol = 'superusuario')
  // centro_trabajo_id INT FK -> centro_trabajo.id NULL (obligatorio si rol = 'administrador')
  // creado            DATETIME

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
   * Regresa el registro de enlace correspondiente a una cuenta nativa de Bee
   * (bee_users.id). Es la consulta base del guard de roles
   * (ver requiere_rol() / obtener_rol_usuario_actual()).
   *
   * @param mixed $beeUserId
   * @return array Arreglo vacío si no existe enlace (el usuario de Bee no tiene rol de contexto asignado)
   */
  static function by_bee_user_id($beeUserId)
  {
    $sql = sprintf('SELECT * FROM %s WHERE bee_user_id = :bee_user_id LIMIT 1', self::$t1);
    return ($rows = parent::query($sql, ['bee_user_id' => $beeUserId])) ? $rows[0] : [];
  }

  /**
   * Regresa los administradores dados de alta por una secretaría
   * TODO: implementar join con bee_users para obtener username/email
   *
   * @param mixed $secretariaId
   * @return array
   */
  static function administradores_por_secretaria($secretariaId)
  {
    $sql = sprintf("SELECT * FROM %s WHERE rol = 'administrador' AND secretaria_id = :secretaria_id ORDER BY id DESC", self::$t1);
    return ($rows = parent::query($sql, ['secretaria_id' => $secretariaId])) ? $rows : [];
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
