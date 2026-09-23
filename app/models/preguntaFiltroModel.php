<?php
/**
 * Plantilla general de modelos
 * @version 1.0.0
 *
 * Modelo de pregunta_filtro
 *
 * Las dos preguntas de clasificación (RF-02) que ramifican el cuestionario:
 * "¿brindo servicio a clientes/usuarios?" y "¿soy jefe de otros
 * trabajadores?". Cada guía tiene sus propias 2 filas (misma pregunta,
 * distinto id, porque habilita reactivos distintos según la guía). La
 * columna `orden` ES la clave semántica que distingue cuál es cuál — no hay
 * una columna de "tipo" separada (ver docs/DDL/ddl.sql): 1=clientes, 2=jefe.
 *
 * @see docs/ARQUITECTURA.md sección "Modelo de datos"
 * @see docs/DDL/ddl.sql
 */
class preguntaFiltroModel extends Model {
  /**
  * Nombre de la tabla
  */
  public static $t1 = 'pregunta_filtro';

  // Esquema del Modelo (según docs/DDL/ddl.sql)
  // id      INT PK AUTO_INCREMENT
  // guia_id INT FK -> guia.id
  // texto   VARCHAR(255)
  // orden   TINYINT UNSIGNED  -- 1=clientes, 2=jefe (ver docblock de la clase)

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
   * Regresa las dos preguntas-filtro de una guía, ordenadas por `orden`
   * (1=clientes, 2=jefe — ver docblock de la clase). Insumo directo para
   * cuestionarioController::responder()/post_responder().
   *
   * @param mixed $guiaId
   * @return array
   */
  static function por_guia($guiaId)
  {
    $sql = sprintf('SELECT * FROM %s WHERE guia_id = :guia_id ORDER BY orden ASC', self::$t1);
    return ($rows = parent::query($sql, ['guia_id' => $guiaId])) ? $rows : [];
  }
}
