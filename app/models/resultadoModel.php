<?php
/**
 * Plantilla general de modelos
 * @version 1.0.0
 *
 * Modelo de resultado
 *
 * Calificación final, nivel de riesgo y desglose por dominio y categoría de
 * cada aplicación (RF-06, RF-07). Base de los reportes individual y agregado.
 *
 * @see docs/ARQUITECTURA.md sección "Modelo de datos"
 */
class resultadoModel extends Model {
  /**
  * Nombre de la tabla
  */
  public static $t1 = 'resultado';

  // Esquema del Modelo
  // TODO (fase de Diseño): confirmar estructura de los desgloses (JSON vs tablas normalizadas
  // resultado_dominio / resultado_categoria) al transcribir las tablas oficiales de puntaje
  // id                       INT PK AUTO_INCREMENT
  // aplicacion_id            INT FK -> aplicacion.id
  // calificacion_final       DECIMAL(6,2)
  // nivel_riesgo             ENUM('nulo','bajo','medio','alto','muy_alto')  -- ver guiaModel::nivel_de_riesgo()
  // desglose_dominio_json    TEXT/JSON   -- TODO: puntaje por cada uno de los 10 dominios (GRIII) / dominios de GRII
  // desglose_categoria_json  TEXT/JSON   -- TODO: puntaje por cada una de las 5 categorías
  // calculado_en             DATETIME

  function __construct()
  {
    // Constructor general
  }

  static function insertOne(array $data)
  {
    return parent::add(self::$t1, $data);
  }

  static function by_id($id)
  {
    // Un registro con $id
    $sql = sprintf('SELECT * FROM %s WHERE id = :id LIMIT 1', self::$t1);
    return ($rows = parent::query($sql, ['id' => $id])) ? $rows[0] : [];
  }

  /**
   * Regresa el resultado individual de una aplicación (RF-13)
   *
   * @param mixed $aplicacionId
   * @return array|null
   */
  static function por_aplicacion($aplicacionId)
  {
    $sql = sprintf('SELECT * FROM %s WHERE aplicacion_id = :aplicacion_id LIMIT 1', self::$t1);
    return ($rows = parent::query($sql, ['aplicacion_id' => $aplicacionId])) ? $rows[0] : null;
  }

  /**
   * Regresa todos los resultados de un centro de trabajo, para el resultado agregado (RF-13)
   *
   * @param mixed $centroTrabajoId
   * @return array
   */
  static function agregado_por_centro_trabajo($centroTrabajoId)
  {
    // TODO: hacer JOIN con aplicacion para filtrar por centro_trabajo_id
    $sql = 'SELECT r.* FROM %s r INNER JOIN aplicacion a ON a.id = r.aplicacion_id WHERE a.centro_trabajo_id = :centro_trabajo_id';
    $sql = sprintf($sql, self::$t1);
    return ($rows = parent::query($sql, ['centro_trabajo_id' => $centroTrabajoId])) ? $rows : [];
  }

  /**
   * Calcula y persiste el resultado de una aplicación a partir de sus respuestas (RF-05, RF-06, RF-07)
   * TODO (fase de Diseño): implementar el cálculo real con las tablas de puntaje oficiales
   * (mapeo reactivo -> dominio -> categoría, polaridad y umbrales de guiaModel)
   *
   * @param mixed $aplicacionId
   * @return array|null El resultado calculado
   */
  static function calcular_para_aplicacion($aplicacionId)
  {
    // TODO:
    // 1. Obtener respuestas con respuestaModel::por_aplicacion($aplicacionId)
    // 2. Agrupar por dominio y categoría usando reactivoModel
    // 3. Sumar puntajes respetando polaridad (RF-05)
    // 4. Determinar nivel de riesgo con guiaModel::nivel_de_riesgo()
    // 5. Persistir con self::insertOne()
    return null;
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
