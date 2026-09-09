<?php
/**
 * Plantilla general de modelos
 * @version 1.1.0
 *
 * Modelo de aplicacion
 *
 * Una instancia respondida del cuestionario, ligada a un token (y por tanto
 * a un centro de trabajo y, a través de éste, a una guía). Guarda el nombre
 * y número de servidor público del encuestado, fecha, estado y las
 * respuestas a las preguntas-filtro.
 *
 * DECISIÓN DE DISEÑO (confirmada, ver docs/ARQUITECTURA.md sección
 * "Decisiones de diseño — B. Modelo de token"): el token es multiuso por
 * centro de trabajo (no por persona), por lo que la identidad de cada
 * encuestado la distinguen nombre + numero_servidor_publico. Restricción de
 * negocio: **una sola aplicación por combinación (token_id,
 * numero_servidor_publico)** — se valida con existe_para_token_y_servidor_publico()
 * antes de crear una nueva aplicación (RF-11 implícito: evitar respuestas
 * duplicadas de la misma persona con el mismo token).
 *
 * @see docs/ARQUITECTURA.md sección "Modelo de datos"
 */
class aplicacionModel extends Model {
  /**
  * Nombre de la tabla
  */
  public static $t1 = 'aplicacion';

  // Esquema del Modelo
  // TODO (fase de Diseño): confirmar columnas finales de preguntas-filtro
  // id                       INT PK AUTO_INCREMENT
  // token_id                 INT FK -> token.id
  // centro_trabajo_id        INT FK -> centro_trabajo.id  -- desnormalizado desde token.centro_trabajo_id para consultas directas
  // nombre_encuestado        VARCHAR(150)   -- RF-03, dato identificado (confidencial)
  // numero_servidor_publico  VARCHAR(50)    -- RF-03, dato identificado (confidencial); junto con token_id, único por UNIQUE KEY (token_id, numero_servidor_publico)
  // filtro_servicio_clientes BOOLEAN NULL   -- "¿brindo servicio a clientes/usuarios?"
  // filtro_jefe_trabajadores BOOLEAN NULL   -- "¿soy jefe de otros trabajadores?"
  // fecha_inicio             DATETIME
  // fecha_envio              DATETIME NULL
  // estado                   ENUM('en_progreso','completado')

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
   * Regresa todas las aplicaciones (respuestas de encuestados) de un centro de trabajo
   * usado para el resultado agregado (RF-13)
   *
   * @param mixed $centroTrabajoId
   * @return array
   */
  static function por_centro_trabajo($centroTrabajoId)
  {
    $sql = sprintf("SELECT * FROM %s WHERE centro_trabajo_id = :centro_trabajo_id AND estado = 'completado' ORDER BY id DESC", self::$t1);
    return ($rows = parent::query($sql, ['centro_trabajo_id' => $centroTrabajoId])) ? $rows : [];
  }

  /**
   * Verifica la restricción de unicidad (token_id, numero_servidor_publico):
   * si la persona ya presentó una aplicación con este mismo token, no se le
   * permite iniciar una nueva (RF-11 implícito).
   *
   * @param mixed $tokenId
   * @param string $numeroServidorPublico
   * @return bool
   */
  static function existe_para_token_y_servidor_publico($tokenId, string $numeroServidorPublico)
  {
    $sql = sprintf('SELECT id FROM %s WHERE token_id = :token_id AND numero_servidor_publico = :numero_servidor_publico LIMIT 1', self::$t1);
    return (bool) parent::query($sql, ['token_id' => $tokenId, 'numero_servidor_publico' => $numeroServidorPublico]);
  }

  /**
   * Marca una aplicación como completada y registra la fecha de envío
   *
   * @param mixed $id
   * @return bool
   */
  static function marcar_completada($id)
  {
    return parent::update(self::$t1, ['id' => $id], ['estado' => 'completado', 'fecha_envio' => now()]);
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
