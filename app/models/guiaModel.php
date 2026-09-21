<?php
/**
 * Plantilla general de modelos
 * @version 1.1.0
 *
 * Modelo de guia
 *
 * Cada versión del instrumento (Guía II de 46 reactivos y Guía III de 72
 * reactivos). Es la entidad que permite soportar ambas guías sin duplicar
 * código (nombre, número de reactivos, rango de trabajadores, umbrales).
 *
 * Los umbrales de calificación NO se guardan aquí como JSON: viven
 * normalizados en la tabla `umbral` (ver umbralModel) con una fila por
 * (guia_id, nivel_agregacion, categoria_id|dominio_id, nivel_riesgo).
 * Ver docs/DDL/ddl.sql.
 *
 * RF-00 — CONFIRMADO contra el texto oficial de la norma (campo de
 * aplicación): 16 a 50 trabajadores -> Guía II; más de 50 -> Guía III;
 * 15 trabajadores o menos -> el centro tiene obligaciones ligeras y NO
 * requiere aplicar ningún cuestionario. Ver por_numero_trabajadores().
 *
 * @see docs/ARQUITECTURA.md sección "Modelo de datos"
 * @see docs/DDL/ddl.sql
 * @see docs/norma/Transcripcion_GuiaIII_NOM-035.md sección 3 (umbrales de la Guía III)
 */
class guiaModel extends Model {
  /**
  * Nombre de la tabla
  */
  public static $t1 = 'guia';

  // Esquema del Modelo (según docs/DDL/ddl.sql)
  // id                INT PK AUTO_INCREMENT
  // clave             VARCHAR(10)   UNIQUE  -- 'GRII' | 'GRIII'
  // nombre            VARCHAR(150)
  // num_reactivos     SMALLINT UNSIGNED     -- 46 | 72
  // trabajadores_min  INT UNSIGNED          -- 16 (GRII), CONFIRMADO contra el texto oficial de la norma
  // trabajadores_max  INT UNSIGNED NULL     -- 50 (GRII) / NULL (GRIII, sin límite superior)

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
    $sql = sprintf('SELECT * FROM %s ORDER BY id ASC', self::$t1);
    return ($rows = parent::query($sql)) ? $rows : [];
  }

  static function by_id($id)
  {
    // Un registro con $id
    $sql = sprintf('SELECT * FROM %s WHERE id = :id LIMIT 1', self::$t1);
    return ($rows = parent::query($sql, ['id' => $id])) ? $rows[0] : [];
  }

  /**
   * Busca una guía por su clave corta ('GRII' | 'GRIII')
   *
   * @param string $clave
   * @return array|null
   */
  static function by_clave(string $clave)
  {
    $sql = sprintf('SELECT * FROM %s WHERE clave = :clave LIMIT 1', self::$t1);
    return ($rows = parent::query($sql, ['clave' => $clave])) ? $rows[0] : null;
  }

  /**
   * Regresa la guía correspondiente según el número de trabajadores del
   * centro de trabajo (RF-00). Es la ÚNICA fuente de verdad para esta regla
   * (no duplicar el rango en otro lugar del código, ver nota en
   * centroTrabajoModel::determinar_guia(), pendiente de consolidar aquí).
   *
   * IMPORTANTE: para $numeroTrabajadores <= 15 este método regresa `null` A
   * PROPÓSITO — no es un error ni un caso sin resolver. Un centro de trabajo
   * de 15 trabajadores o menos tiene obligaciones ligeras según la norma y
   * NO requiere aplicar ningún cuestionario (ni Guía II ni III). TODO (fase
   * de Desarrollo): en el controlador que consuma este método (ej.
   * administradorController::post_centros_trabajo()), un resultado `null`
   * debe mostrarse como "este centro de trabajo no requiere cuestionario",
   * nunca como un mensaje de error genérico.
   *
   * @param int $numeroTrabajadores
   * @return array|null null si no aplica ninguna guía (<=15 trabajadores)
   */
  static function por_numero_trabajadores(int $numeroTrabajadores)
  {
    $sql = sprintf(
      'SELECT * FROM %s WHERE :n >= trabajadores_min AND (trabajadores_max IS NULL OR :n2 <= trabajadores_max) LIMIT 1',
      self::$t1
    );
    return ($rows = parent::query($sql, ['n' => $numeroTrabajadores, 'n2' => $numeroTrabajadores])) ? $rows[0] : null;
  }

  /**
   * Regresa el nivel de riesgo correspondiente a una calificación, contra la
   * tabla `umbral`. Punto de entrada único de conveniencia; internamente
   * delega en el método específico de umbralModel según $nivelAgregacion
   * (que es lo que usa directamente resultadoModel::calcular_para_aplicacion()
   * para no tener que decidir aquí cuál de los tres invocar).
   *
   * @param mixed $guiaId Sólo se usa si $nivelAgregacion = 'final'
   * @param int $calificacion
   * @param string $nivelAgregacion 'final' | 'categoria' | 'dominio'
   * @param mixed $categoriaODominioId NULL si $nivelAgregacion = 'final'; el id de categoria/dominio en los otros dos casos
   * @return string|null 'nulo'|'bajo'|'medio'|'alto'|'muy_alto'
   */
  static function nivel_de_riesgo($guiaId, int $calificacion, string $nivelAgregacion = 'final', $categoriaODominioId = null)
  {
    switch ($nivelAgregacion) {
      case 'categoria':
        return umbralModel::nivel_categoria($categoriaODominioId, $calificacion);

      case 'dominio':
        return umbralModel::nivel_dominio($categoriaODominioId, $calificacion);

      case 'final':
      default:
        return umbralModel::nivel_final($guiaId, $calificacion);
    }
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
