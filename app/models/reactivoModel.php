<?php
/**
 * Plantilla general de modelos
 * @version 1.0.0
 *
 * Modelo de reactivo
 *
 * Los reactivos (preguntas) parametrizados de cada guía: número, texto,
 * dominio, categoría, polaridad y si es condicional (ligado a una
 * pregunta-filtro). Instrumento parametrizado en base de datos, no
 * "quemado" en código (RNF-07).
 *
 * @see docs/ARQUITECTURA.md sección "Modelo de datos"
 */
class reactivoModel extends Model {
  /**
  * Nombre de la tabla
  */
  public static $t1 = 'reactivo';

  // Esquema del Modelo
  // TODO (fase de Diseño): mapeo reactivo -> dominio -> categoría y polaridad se transcriben
  // de las tablas oficiales de la norma (Secretaría las tiene en físico). NO reconstruir de memoria.
  // id                 INT PK AUTO_INCREMENT
  // guia_id            INT FK -> guia.id
  // numero             INT           -- posición del reactivo dentro de su guía (1..46 | 1..72)
  // texto              TEXT
  // dominio            VARCHAR(150)  -- TODO: catálogo oficial de dominios por guía
  // categoria          VARCHAR(150)  -- TODO: catálogo oficial de categorías por guía
  // polaridad          ENUM('normal','invertido')  -- TODO: confirmar por reactivo según la norma (RF-05)
  // es_condicional     BOOLEAN       -- true si depende de una pregunta-filtro
  // pregunta_filtro    VARCHAR(255) NULL -- texto de la pregunta-filtro que lo habilita, si aplica
  // creado             DATETIME

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
    $sql = sprintf('SELECT * FROM %s ORDER BY numero ASC', self::$t1);
    return ($rows = parent::query($sql)) ? $rows : [];
  }

  static function by_id($id)
  {
    // Un registro con $id
    $sql = sprintf('SELECT * FROM %s WHERE id = :id LIMIT 1', self::$t1);
    return ($rows = parent::query($sql, ['id' => $id])) ? $rows[0] : [];
  }

  /**
   * Regresa todos los reactivos de una guía, ordenados por número
   *
   * @param mixed $guiaId
   * @return array
   */
  static function por_guia($guiaId)
  {
    $sql = sprintf('SELECT * FROM %s WHERE guia_id = :guia_id ORDER BY numero ASC', self::$t1);
    return ($rows = parent::query($sql, ['guia_id' => $guiaId])) ? $rows : [];
  }

  /**
   * Regresa únicamente los reactivos obligatorios (no condicionales) de una guía
   *
   * @param mixed $guiaId
   * @return array
   */
  static function obligatorios_por_guia($guiaId)
  {
    $sql = sprintf("SELECT * FROM %s WHERE guia_id = :guia_id AND es_condicional = 0 ORDER BY numero ASC", self::$t1);
    return ($rows = parent::query($sql, ['guia_id' => $guiaId])) ? $rows : [];
  }

  /**
   * Regresa los reactivos condicionales que habilita una pregunta-filtro (RF-02)
   * TODO (fase de Diseño): confirmar los rangos exactos (65-68 y 69-72 en Guía III;
   * 41-43 y 44-46 en Guía II) al transcribir de la norma
   *
   * @param mixed $guiaId
   * @param string $preguntaFiltro
   * @return array
   */
  static function condicionales_por_filtro($guiaId, string $preguntaFiltro)
  {
    $sql = sprintf('SELECT * FROM %s WHERE guia_id = :guia_id AND pregunta_filtro = :filtro ORDER BY numero ASC', self::$t1);
    return ($rows = parent::query($sql, ['guia_id' => $guiaId, 'filtro' => $preguntaFiltro])) ? $rows : [];
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
