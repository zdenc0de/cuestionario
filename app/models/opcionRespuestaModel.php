<?php
/**
 * Plantilla general de modelos
 * @version 1.1.0
 *
 * Modelo de opcion_respuesta
 *
 * Las 5 opciones de la escala Likert (Nunca, Casi nunca, Algunas veces,
 * Casi siempre, Siempre) — RF-01, sección 7.1 del plan. Ajustado a
 * docs/DDL/ddl.sql (columnas reales: `etiqueta`, `posicion`).
 *
 * IMPORTANTE — cómo se deriva el puntaje (RF-05): `posicion` es fija y
 * representa únicamente el ORDEN de despliegue en la UI (0=Siempre ... 4=Nunca),
 * NO el puntaje. El puntaje real depende de la polaridad del reactivo
 * (reactivo.polaridad, ver reactivoModel) y se calcula en el momento de
 * evaluar la respuesta, NO se guarda aquí ni en `respuesta`:
 *
 *   - polaridad 'normal'    -> puntaje = 4 - posicion   (Siempre=posicion 0 -> puntaje 4, "Normal (4→0)")
 *   - polaridad 'invertida' -> puntaje = posicion        (Siempre=posicion 0 -> puntaje 0, "Invertida (0→4)")
 *
 * Esto es lo que documenta la Transcripción de la Guía III
 * (docs/norma/Transcripcion_GuiaIII_NOM-035.md) con la notación "(4→0)"/"(0→4)".
 * TODO (fase de Desarrollo): centralizar esta fórmula en un solo lugar
 * (ej. reactivoModel::calcular_puntaje($reactivo, $posicion)) para no duplicarla.
 *
 * @see docs/ARQUITECTURA.md sección "Modelo de datos"
 * @see docs/DDL/ddl.sql
 */
class opcionRespuestaModel extends Model {
  /**
  * Nombre de la tabla
  */
  public static $t1 = 'opcion_respuesta';

  // Esquema del Modelo (según docs/DDL/ddl.sql)
  // id        INT PK AUTO_INCREMENT
  // etiqueta  VARCHAR(30)      -- 'Siempre' | 'Casi siempre' | 'Algunas veces' | 'Casi nunca' | 'Nunca'
  // posicion  TINYINT UNSIGNED -- 0=Siempre ... 4=Nunca (orden de UI, NO el puntaje; ver docblock de la clase)

  function __construct()
  {
    // Constructor general
  }

  static function insertOne(array $data)
  {
    return parent::add(self::$t1, $data);
  }

  /**
   * Regresa las 5 opciones de la escala Likert ordenadas para despliegue
   *
   * @return array
   */
  static function escala()
  {
    $sql = sprintf('SELECT * FROM %s ORDER BY posicion ASC', self::$t1);
    return ($rows = parent::query($sql)) ? $rows : [];
  }

  static function by_id($id)
  {
    // Un registro con $id
    $sql = sprintf('SELECT * FROM %s WHERE id = :id LIMIT 1', self::$t1);
    return ($rows = parent::query($sql, ['id' => $id])) ? $rows[0] : [];
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
