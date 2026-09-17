<?php
/**
 * Plantilla general de modelos
 * @version 1.2.0
 *
 * Modelo de token
 *
 * DECISIÓN DE DISEÑO (confirmada, ver docs/ARQUITECTURA.md sección "Decisiones
 * de diseño — B. Modelo de token"): el token es **por centro de trabajo y por
 * campaña**, NO por persona. Es multiuso durante su vigencia: cualquier
 * encuestado del centro de trabajo puede capturarlo para acceder (RF-10,
 * RF-11). Lo que distingue a cada encuestado es su identidad declarada
 * (nombre + numero_servidor_publico, capturada en aplicacionModel), no el
 * token en sí. La guía a aplicar NO se guarda en el token (ver docs/DDL/ddl.sql:
 * `centro_trabajo` tampoco la guarda; se determina en el momento con
 * guiaModel::por_numero_trabajadores() y se "congela" en aplicacion.guia_id).
 *
 * "Habilitar cuestionario" (RF-10) se define, de forma provisional y sujeta a
 * validación con la Secretaría, como: crear/activar un token vigente para el
 * centro de trabajo. Mientras no exista un token con estado 'activo' y
 * vigente, el centro de trabajo no tiene cuestionario habilitado.
 *
 * Restricción de negocio (aplicada en aplicacionModel, no aquí): una sola
 * aplicación por combinación (token_id, numero_servidor_publico).
 *
 * IMPORTANTE — tipo de fecha: según docs/DDL/ddl.sql, `fecha_inicio`/`fecha_fin`
 * son de tipo DATE (no DATETIME), es decir vigencia por DÍA completo. Por eso
 * esta clase compara contra date('Y-m-d') y NO contra now() (que incluye hora
 * y rompería la comparación de cadenas contra una columna DATE).
 *
 * @see docs/ARQUITECTURA.md
 * @see docs/DDL/ddl.sql
 */
class tokenModel extends Model {
  /**
  * Nombre de la tabla
  */
  public static $t1 = 'token';

  // Esquema del Modelo (según docs/DDL/ddl.sql)
  // id                INT PK AUTO_INCREMENT
  // centro_trabajo_id INT FK -> centro_trabajo.id
  // codigo            VARCHAR(64) UNIQUE  -- clave única generada, multiuso durante su vigencia
  // fecha_inicio      DATE  -- vigencia por día completo, NO DATETIME (ver docblock de la clase)
  // fecha_fin         DATE
  // estado            ENUM('activo','inactivo')
  // created_at        TIMESTAMP
  // updated_at        TIMESTAMP
  // TODO (fase de Diseño): confirmar vigencia por defecto de una campaña (¿días?)

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
   * Busca un token por su código único
   *
   * @param string $codigo
   * @return array|null
   */
  static function by_codigo(string $codigo)
  {
    $sql = sprintf('SELECT * FROM %s WHERE codigo = :codigo LIMIT 1', self::$t1);
    return ($rows = parent::query($sql, ['codigo' => $codigo])) ? $rows[0] : null;
  }

  static function por_centro_trabajo($centroTrabajoId)
  {
    $sql = sprintf('SELECT * FROM %s WHERE centro_trabajo_id = :centro_trabajo_id ORDER BY id DESC', self::$t1);
    return ($rows = parent::query($sql, ['centro_trabajo_id' => $centroTrabajoId])) ? $rows : [];
  }

  /**
   * Regresa el token activo y vigente de un centro de trabajo, si existe
   * (equivalente a "el cuestionario está habilitado para este centro")
   *
   * @param mixed $centroTrabajoId
   * @return array|null
   */
  static function activo_por_centro_trabajo($centroTrabajoId)
  {
    $sql = sprintf(
      "SELECT * FROM %s WHERE centro_trabajo_id = :centro_trabajo_id AND estado = 'activo' AND :hoy BETWEEN fecha_inicio AND fecha_fin LIMIT 1",
      self::$t1
    );
    return ($rows = parent::query($sql, ['centro_trabajo_id' => $centroTrabajoId, 'hoy' => date('Y-m-d')])) ? $rows[0] : null;
  }

  /**
   * Genera un código único para un token
   * TODO (fase de Diseño): confirmar longitud/formato requerido (¿alfanumérico legible para captura manual?)
   *
   * @return string
   */
  static function generar_codigo()
  {
    // TODO: usar random_password() de bee_core_functions.php o bin2hex(random_bytes())
    // y validar que no exista colisión contra by_codigo() antes de insertar
    return null;
  }

  /**
   * Valida si un código de token existe, está activo y vigente (RF-11).
   * Acceso válido = token existe + activo + vigente (ver decisión de diseño
   * en el docblock de esta clase). NO valida aquí la unicidad de aplicación
   * por servidor público: eso lo hace aplicacionModel.
   *
   * @param string $codigo
   * @return bool
   */
  static function esta_vigente(string $codigo)
  {
    if (!$token = self::by_codigo($codigo)) {
      return false;
    }

    if ($token['estado'] !== 'activo') {
      return false;
    }

    // fecha_inicio/fecha_fin son DATE: comparar solo la fecha (sin hora), ver docblock de la clase
    $hoy = date('Y-m-d');
    return $hoy >= $token['fecha_inicio'] && $hoy <= $token['fecha_fin'];
  }

  /**
   * Revoca un token (equivalente a inactivo, no existe un estado 'revocado'
   * separado en docs/DDL/ddl.sql: el ENUM es sólo ('activo','inactivo'))
   *
   * @param mixed $id
   * @return bool
   */
  static function revocar($id)
  {
    return parent::update(self::$t1, ['id' => $id], ['estado' => 'inactivo']);
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
