<?php
/**
 * Plantilla general de modelos
 * @version 1.2.0
 *
 * Modelo de aplicacion
 *
 * Una instancia respondida del cuestionario, ligada a un token (y por tanto
 * a un centro de trabajo). Guarda el nombre y número de servidor público del
 * encuestado, fecha, estado y las respuestas a las preguntas-filtro.
 *
 * DECISIÓN DE DISEÑO (confirmada, ver docs/ARQUITECTURA.md sección "Decisiones
 * de diseño — B. Modelo de token"): el token es multiuso por centro de
 * trabajo, por lo que la identidad de cada encuestado la distinguen nombre +
 * numero_servidor_publico. Restricción de negocio: **una sola aplicación por
 * combinación (token_id, numero_servidor_publico)** — se valida con
 * existe_para_token_y_servidor_publico() antes de crear una nueva aplicación.
 *
 * PENDIENTE (ver docs/ARQUITECTURA.md): esta tabla NO tiene centro_trabajo_id
 * propio en docs/DDL/ddl.sql (sólo token_id). Los métodos que filtran por
 * centro de trabajo hacen JOIN con `token` mientras se decide si conviene
 * denormalizar la columna para simplificar esas consultas.
 *
 * @see docs/ARQUITECTURA.md sección "Modelo de datos"
 * @see docs/DDL/ddl.sql
 */
class aplicacionModel extends Model {
  /**
  * Nombre de la tabla
  */
  public static $t1 = 'aplicacion';

  // Esquema del Modelo (según docs/DDL/ddl.sql)
  // id                      INT PK AUTO_INCREMENT
  // token_id                INT FK -> token.id
  // guia_id                 INT FK -> guia.id  -- guía "congelada" al momento de responder
  // nombre                  VARCHAR(200)   -- RF-03, dato identificado (confidencial). OJO: se llama "nombre", no "nombre_encuestado"
  // numero_servidor_publico VARCHAR(50)    -- RF-03, dato identificado (confidencial); único junto con token_id
  // atiende_clientes        TINYINT(1) NULL  -- respuesta al filtro F1 ("¿brindo servicio a clientes/usuarios?")
  // es_jefe                 TINYINT(1) NULL  -- respuesta al filtro F2 ("¿soy jefe de otros trabajadores?")
  // estado                  ENUM('en_progreso','completada')  -- OJO: es "completada" (femenino), no "completado"
  // created_at              TIMESTAMP
  // updated_at              TIMESTAMP  -- se usa como fecha de envío implícita al pasar a 'completada'
  // UNIQUE (token_id, numero_servidor_publico)

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
   * Regresa todas las aplicaciones completadas de un centro de trabajo,
   * usado para el resultado agregado (RF-13). Hace JOIN con `token` porque
   * `aplicacion` no tiene centro_trabajo_id propio (ver docblock de la clase).
   *
   * @param mixed $centroTrabajoId
   * @return array
   */
  static function por_centro_trabajo($centroTrabajoId)
  {
    $sql =
      "SELECT a.* FROM %s a
       INNER JOIN token t ON t.id = a.token_id
       WHERE t.centro_trabajo_id = :centro_trabajo_id AND a.estado = 'completada'
       ORDER BY a.id DESC";
    $sql = sprintf($sql, self::$t1);
    return ($rows = parent::query($sql, ['centro_trabajo_id' => $centroTrabajoId])) ? $rows : [];
  }

  /**
   * Regresa el centro_trabajo_id de una aplicación (vía su token), usado por
   * resultadosController para el guard de alcance por rol.
   *
   * @param mixed $aplicacionId
   * @return mixed|null
   */
  static function centro_trabajo_id_de($aplicacionId)
  {
    $sql =
      'SELECT t.centro_trabajo_id FROM %s a
       INNER JOIN token t ON t.id = a.token_id
       WHERE a.id = :aplicacion_id LIMIT 1';
    $sql  = sprintf($sql, self::$t1);
    $rows = parent::query($sql, ['aplicacion_id' => $aplicacionId]);
    return $rows ? $rows[0]['centro_trabajo_id'] : null;
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
   * Marca una aplicación como completada (RF-04, envío final). No existe una
   * columna fecha_envio propia: updated_at (ON UPDATE CURRENT_TIMESTAMP) la
   * registra implícitamente.
   *
   * @param mixed $id
   * @return bool
   */
  static function marcar_completada($id)
  {
    return parent::update(self::$t1, ['id' => $id], ['estado' => 'completada']);
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
