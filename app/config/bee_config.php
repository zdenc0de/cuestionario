<?php 

/**
 * Constantes migradas de settings.php
 * a este archivo para cuando se deba realizar una actualización del sistema
 * o corrección, las credenciales de la base de datos no queden expuestas ni
 * sean modificadas en el proceso por accidente así como el basepath y otras constantes que requieran
 * configuración especial en producción
 */
define('IS_LOCAL'     , in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']));
define('DEV_PATH', '/cuestionario/');
define('LIVE_PATH'    , '/'); // Ruta del proyecto en producción
define('BASEPATH'     , IS_LOCAL ? DEV_PATH : LIVE_PATH);
define('IS_DEMO'      , false); // Si es requerida añadir funcionalidad DEMO en tu sistema, puedes usarlo con esta constante

// En caso de implementación de pagos en línea para definir si se está trabajando con pasarelas en modo sandbox / prueba o producción
define('SANDBOX'      , true); // true o false para ambientes live/producción o sandbox/pruebas

// Set para conexión en producción o servidor real
// Servidor de pruebas (Pasada 11/12) — credenciales compartidas por el
// usuario el 2026-10-02 para la base practicas-scyt en 192.168.100.21.
define('DB_ENGINE'    , 'mysql');
define('DB_HOST'      , '192.168.100.21');
define('DB_NAME'      , 'practicas-scyt');
define('DB_USER'      , 'practicas-scyt');
define('DB_PASS'      , '8eqEwLB2vo.');
define('DB_CHARSET'   , 'utf8mb4'); // coincide con docs/DDL/ddl.sql (utf8mb4_unicode_ci), no con LDB_CHARSET local ('utf8', heredado del scaffold de Bee)