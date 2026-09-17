<?php
/**
 * Plantilla general de modelos
 * @version 1.1.0
 *
 * Modelo de reactivo
 *
 * Los reactivos (preguntas) parametrizados de cada guía. Instrumento
 * parametrizado en base de datos, no "quemado" en código (RNF-07).
 *
 * Ajustado a docs/DDL/ddl.sql: dominio/categoría NO son texto libre, son FK
 * a tablas normalizadas (`categoria`, `dominio`, `dimension`), cada guía con
 * su propio árbol categoria->dominio->dimension (ver categoriaModel,
 * dominioModel, dimensionModel — pendientes de scaffolding). `dominio_id` y
 * `categoria_id` están denormalizados directamente en `reactivo` (además de
 * `dimension_id`) para facilitar el cálculo sin tener que subir el árbol en
 * cada consulta.
 *
 * La condicionalidad (RF-02) NO es un booleano `es_condicional`: es
 * `pregunta_filtro_id` NULL (no condicional) o apuntando a un registro de la
 * tabla `pregunta_filtro` (condicional), ver preguntaFiltroModel — pendiente
 * de scaffolding.
 *
 * Fuente de la transcripción real de la Guía III (72 reactivos, polaridad,
 * dominio/categoría/dimensión por reactivo, preguntas-filtro):
 * docs/norma/Transcripcion_GuiaIII_NOM-035.md. NO reconstruir de memoria.
 *
 * @see docs/ARQUITECTURA.md sección "Modelo de datos"
 * @see docs/DDL/ddl.sql
 */
class reactivoModel extends Model {
  /**
  * Nombre de la tabla
  */
  public static $t1 = 'reactivo';

  // Esquema del Modelo (según docs/DDL/ddl.sql)
  // id                 INT PK AUTO_INCREMENT
  // guia_id            INT FK -> guia.id
  // dimension_id       INT FK -> dimension.id
  // dominio_id         INT FK -> dominio.id     -- denormalizado (facilita el cálculo)
  // categoria_id       INT FK -> categoria.id   -- denormalizado
  // numero             SMALLINT UNSIGNED        -- posición del reactivo dentro de su guía (1..46 | 1..72)
  // texto              VARCHAR(500)
  // polaridad          ENUM('normal','invertida')  -- ver opcionRespuestaModel para la fórmula de puntaje
  // pregunta_filtro_id INT FK -> pregunta_filtro.id NULL  -- NULL = no condicional

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
    $sql = sprintf('SELECT * FROM %s WHERE guia_id = :guia_id AND pregunta_filtro_id IS NULL ORDER BY numero ASC', self::$t1);
    return ($rows = parent::query($sql, ['guia_id' => $guiaId])) ? $rows : [];
  }

  /**
   * Regresa los reactivos condicionales que habilita una pregunta-filtro (RF-02)
   * (65-68 y 69-72 en la Guía III, ver docs/norma/Transcripcion_GuiaIII_NOM-035.md sección 2;
   * 41-43 y 44-46 en la Guía II, pendiente su propia transcripción)
   *
   * @param mixed $preguntaFiltroId
   * @return array
   */
  static function condicionales_por_filtro($preguntaFiltroId)
  {
    $sql = sprintf('SELECT * FROM %s WHERE pregunta_filtro_id = :pregunta_filtro_id ORDER BY numero ASC', self::$t1);
    return ($rows = parent::query($sql, ['pregunta_filtro_id' => $preguntaFiltroId])) ? $rows : [];
  }

  /**
   * Calcula el puntaje de un reactivo a partir de la posición elegida en la
   * escala Likert, respetando su polaridad (RF-05). Ver la fórmula completa
   * documentada en opcionRespuestaModel.
   *
   * @param array $reactivo Fila de esta tabla (debe incluir 'polaridad')
   * @param int $posicion 0 (Siempre) .. 4 (Nunca), ver opcion_respuesta.posicion
   * @return int
   */
  static function calcular_puntaje(array $reactivo, int $posicion)
  {
    return $reactivo['polaridad'] === 'normal' ? (4 - $posicion) : $posicion;
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
