<?php
/**
 * Plantilla general de modelos
 * @version 1.0.0
 *
 * Modelo de umbral
 *
 * Todos los rangos de nivel de riesgo (final, por categoría y por dominio)
 * de ambas guías en una sola tabla genérica — ver
 * docs/Modelo_ER_Cuestionario_NOM-035.md secciones 3 y 5.3. Es la única
 * fuente de verdad que consume resultadoModel::calcular_para_aplicacion()
 * para traducir una calificación numérica a un nivel de riesgo; NO
 * reconstruir los rangos hardcodeados en otro lugar del código.
 *
 * Convención de límites (confirmada, ver Modelo_ER.md §5.3 y la propia
 * BD sembrada): `limite_inferior <= valor < limite_superior`. El nivel más
 * bajo ('nulo') tiene `limite_inferior` NULL; el más alto ('muy_alto') tiene
 * `limite_superior` NULL.
 *
 * @see docs/ARQUITECTURA.md sección "Modelo de datos"
 * @see docs/DDL/ddl.sql
 * @see docs/Modelo_ER_Cuestionario_NOM-035.md
 */
class umbralModel extends Model {
  /**
  * Nombre de la tabla
  */
  public static $t1 = 'umbral';

  // Esquema del Modelo (según docs/DDL/ddl.sql)
  // id               INT PK AUTO_INCREMENT
  // guia_id          INT FK -> guia.id
  // nivel_agregacion ENUM('final','categoria','dominio')
  // categoria_id     INT FK -> categoria.id  NULL  -- sólo si nivel_agregacion='categoria'
  // dominio_id       INT FK -> dominio.id    NULL  -- sólo si nivel_agregacion='dominio'
  // nivel_riesgo     ENUM('nulo','bajo','medio','alto','muy_alto')
  // limite_inferior  INT NULL  -- NULL en el nivel más bajo ('nulo')
  // limite_superior  INT NULL  -- NULL en el nivel más alto ('muy_alto')

  function __construct()
  {
    // Constructor general
  }

  static function by_id($id)
  {
    // Un registro con $id
    $sql = sprintf('SELECT * FROM %s WHERE id = :id LIMIT 1', self::$t1);
    return ($rows = parent::query($sql, ['id' => $id])) ? $rows[0] : [];
  }

  /**
   * Fórmula común de los 3 métodos públicos: encuentra la fila de `umbral`
   * cuyo rango [limite_inferior, limite_superior) contiene $calificacion,
   * dentro del filtro adicional ($columnaExtra = $valorExtra) que distingue
   * final/categoria/dominio.
   *
   * @param string $nivelAgregacion 'final' | 'categoria' | 'dominio'
   * @param string|null $columnaExtra 'guia_id' | 'categoria_id' | 'dominio_id'
   * @param mixed $valorExtra
   * @param int $calificacion
   * @return string|null 'nulo'|'bajo'|'medio'|'alto'|'muy_alto', null si no hay match (no debería pasar con datos completos)
   */
  private static function buscar_nivel(string $nivelAgregacion, string $columnaExtra, $valorExtra, int $calificacion)
  {
    $sql = sprintf(
      "SELECT nivel_riesgo FROM %s
       WHERE nivel_agregacion = :nivel_agregacion
         AND %s = :valor_extra
         AND (limite_inferior IS NULL OR :calificacion_a >= limite_inferior)
         AND (limite_superior IS NULL OR :calificacion_b < limite_superior)
       LIMIT 1",
      self::$t1,
      $columnaExtra
    );

    $rows = parent::query($sql, [
      'nivel_agregacion' => $nivelAgregacion,
      'valor_extra'      => $valorExtra,
      'calificacion_a'   => $calificacion,
      'calificacion_b'   => $calificacion
    ]);

    return $rows ? $rows[0]['nivel_riesgo'] : null;
  }

  /**
   * Nivel de riesgo de la calificación FINAL de una guía (Cfinal, RF-07)
   *
   * @param mixed $guiaId
   * @param int $calificacion
   * @return string|null
   */
  static function nivel_final($guiaId, int $calificacion)
  {
    return self::buscar_nivel('final', 'guia_id', $guiaId, $calificacion);
  }

  /**
   * Nivel de riesgo de la calificación de una CATEGORÍA (Ccat)
   *
   * @param mixed $categoriaId
   * @param int $calificacion
   * @return string|null
   */
  static function nivel_categoria($categoriaId, int $calificacion)
  {
    return self::buscar_nivel('categoria', 'categoria_id', $categoriaId, $calificacion);
  }

  /**
   * Nivel de riesgo de la calificación de un DOMINIO (Cdom)
   *
   * @param mixed $dominioId
   * @param int $calificacion
   * @return string|null
   */
  static function nivel_dominio($dominioId, int $calificacion)
  {
    return self::buscar_nivel('dominio', 'dominio_id', $dominioId, $calificacion);
  }
}
