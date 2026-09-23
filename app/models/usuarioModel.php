<?php
/**
 * Plantilla general de modelos
 * @version 1.2.0
 *
 * Modelo de usuario
 *
 * DECISIÓN DE DISEÑO (confirmada, ver docs/ARQUITECTURA.md sección
 * "Decisiones de diseño — A. Autenticación/roles"): el sistema NATIVO de Bee
 * (tabla bee_users + clase Auth) es la ÚNICA fuente de verdad de
 * identidad/login. usuarioModel **NO duplica usuarios**: es una tabla de
 * ENLACE (1 a 1 con bee_users) que agrega el rol de contexto y su secretaría.
 *
 * AJUSTE contra docs/DDL/ddl.sql: la relación con centro de trabajo NO es una
 * columna en `usuario` (a diferencia de una primera versión de este
 * docblock). Es al revés: `centro_trabajo.administrador_id` apunta a
 * `usuario.id`. Esto permite que UN administrador gestione VARIOS centros de
 * trabajo (1 a muchos), no sólo uno — ver centroTrabajoModel::por_administrador().
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
 * @see docs/DDL/ddl.sql
 */
class usuarioModel extends Model {
  /**
  * Nombre de la tabla
  */
  public static $t1 = 'usuario';

  // Nombre de tablas relacionadas (núcleo de Bee, no modificar)
  // public static $t2 = 'bee_users';

  // Esquema del Modelo (según docs/DDL/ddl.sql)
  // id            INT PK AUTO_INCREMENT
  // bee_user_id   INT FK -> bee_users.id  UNIQUE (relación 1 a 1, ver docblock de la clase)
  // rol           ENUM('superusuario','administrador')
  // secretaria_id INT FK -> secretaria.id  NOT NULL (obligatorio para ambos roles, ver docblock)
  // created_at    TIMESTAMP
  // updated_at    TIMESTAMP
  //
  // NOTA de tipos: docs/DDL/ddl.sql declara `bee_user_id` como INT UNSIGNED,
  // pero el núcleo de Bee (db_beeframework.sql) declara `bee_users.id` como
  // INT (firmado, sin UNSIGNED) — la FK del Bloque C del DDL fallará por
  // choque de signo hasta que se corrija en el DDL (ver docs/ARQUITECTURA.md).

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
   * Regresa los administradores dados de alta por una secretaría, con su
   * username/email de bee_users ya incluidos (JOIN) — es lo que consume
   * superusuarioController::administradores() para el listado (RF-09).
   *
   * @param mixed $secretariaId
   * @param string|null $estado 'activo' | 'inactivo' | null (sin filtrar).
   * OJO: si se pasa un valor distinto de null y la columna `usuario.estado`
   * todavía no existe (pendiente de scripts/alter_usuario_estado.sql, ver
   * docs/ARQUITECTURA.md sección 14.2), esta consulta lanza una excepción
   * ("Unknown column") — el llamador debe capturarla y volver a llamar sin
   * filtro (ver superusuarioController::administradores()).
   * @return array
   */
  static function administradores_por_secretaria($secretariaId, ?string $estado = null)
  {
    $sql =
      "SELECT u.*, bu.username, bu.email
       FROM %s u
       INNER JOIN bee_users bu ON bu.id = u.bee_user_id
       WHERE u.rol = 'administrador' AND u.secretaria_id = :secretaria_id";
    $params = ['secretaria_id' => $secretariaId];

    if ($estado !== null) {
      $sql             .= ' AND u.estado = :estado';
      $params['estado'] = $estado;
    }

    $sql .= ' ORDER BY u.id DESC';
    $sql  = sprintf($sql, self::$t1);
    return ($rows = parent::query($sql, $params)) ? $rows : [];
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
