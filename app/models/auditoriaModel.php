<?php
/**
 * Plantilla general de modelos
 * @version 1.0.0
 *
 * Modelo de auditoria (bitácora)
 *
 * Registro de los movimientos y consultas de los administradores (usuario,
 * acción, entidad afectada, fecha/hora), consultable por el súper usuario
 * (RF-09, RF-12, RNF-01).
 *
 * @see docs/ARQUITECTURA.md sección "Modelo de datos"
 * @see registrar_auditoria() en app/functions/bee_custom_functions.php
 */
class auditoriaModel extends Model {
  /**
  * Nombre de la tabla
  */
  public static $t1 = 'auditoria';

  // Esquema del Modelo
  // TODO (fase de Diseño): confirmar catálogo cerrado de acciones a auditar
  // (ej. 'login', 'consulta_resultados', 'generar_token', 'alta_centro_trabajo', 'alta_administrador', etc.)
  // id          INT PK AUTO_INCREMENT
  // usuario_id  INT FK -> usuario.id
  // accion      VARCHAR(100)
  // entidad     VARCHAR(100)  NULL  -- ej. 'centro_trabajo', 'token', 'resultado'
  // entidad_id  INT           NULL
  // detalle     VARCHAR(255)  NULL
  // ip          VARCHAR(45)   NULL
  // creado      DATETIME

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
    // Todos los registros, consultable por el súper usuario (RF-09)
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
   * Regresa la bitácora de un usuario administrador en específico
   *
   * @param mixed $usuarioId
   * @return array
   */
  static function por_usuario($usuarioId)
  {
    $sql = sprintf('SELECT * FROM %s WHERE usuario_id = :usuario_id ORDER BY id DESC', self::$t1);
    return ($rows = parent::query($sql, ['usuario_id' => $usuarioId])) ? $rows : [];
  }
}
