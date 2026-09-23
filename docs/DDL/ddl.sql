-- =====================================================================
-- DDL — Sistema NOM-035 (Guías II y III)
-- Bee Framework · MySQL/MariaDB · InnoDB · utf8mb4
-- Orden de creación respeta las dependencias de FK (sin ciclos).
-- =====================================================================

-- ---------------------------------------------------------------------
-- BLOQUE A: EL INSTRUMENTO (catálogo, se llena con el seed)
-- ---------------------------------------------------------------------

CREATE TABLE opcion_respuesta (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  etiqueta   VARCHAR(30)      NOT NULL,          -- Siempre, Casi siempre, ...
  posicion   TINYINT UNSIGNED NOT NULL,          -- 0=Siempre ... 4=Nunca
  UNIQUE KEY uq_opcion_posicion (posicion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE guia (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  clave            VARCHAR(10)  NOT NULL,         -- GRII / GRIII
  nombre           VARCHAR(150) NOT NULL,
  num_reactivos    SMALLINT UNSIGNED NOT NULL,
  trabajadores_min INT UNSIGNED NOT NULL,         -- 16 (GRII) / 51 (GRIII)
  trabajadores_max INT UNSIGNED NULL,             -- 50 (GRII) / NULL (GRIII)
  UNIQUE KEY uq_guia_clave (clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categoria (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  guia_id   INT UNSIGNED NOT NULL,
  nombre    VARCHAR(150) NOT NULL,
  orden     TINYINT UNSIGNED NOT NULL,
  CONSTRAINT fk_categoria_guia FOREIGN KEY (guia_id)
    REFERENCES guia(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE dominio (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  categoria_id  INT UNSIGNED NOT NULL,
  nombre        VARCHAR(150) NOT NULL,
  orden         TINYINT UNSIGNED NOT NULL,
  CONSTRAINT fk_dominio_categoria FOREIGN KEY (categoria_id)
    REFERENCES categoria(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE dimension (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  dominio_id  INT UNSIGNED NOT NULL,
  nombre      VARCHAR(200) NOT NULL,
  CONSTRAINT fk_dimension_dominio FOREIGN KEY (dominio_id)
    REFERENCES dominio(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pregunta_filtro (
  id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  guia_id  INT UNSIGNED NOT NULL,
  texto    VARCHAR(255) NOT NULL,
  orden    TINYINT UNSIGNED NOT NULL,             -- 1=clientes, 2=jefe
  CONSTRAINT fk_filtro_guia FOREIGN KEY (guia_id)
    REFERENCES guia(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reactivo (
  id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  guia_id            INT UNSIGNED NOT NULL,
  dimension_id       INT UNSIGNED NOT NULL,
  dominio_id         INT UNSIGNED NOT NULL,        -- denormalizado (facilita el cálculo)
  categoria_id       INT UNSIGNED NOT NULL,        -- denormalizado
  numero             SMALLINT UNSIGNED NOT NULL,   -- 1..46 / 1..72
  texto              VARCHAR(500) NOT NULL,
  polaridad          ENUM('normal','invertida') NOT NULL,
  pregunta_filtro_id INT UNSIGNED NULL,            -- NULL = no condicional
  UNIQUE KEY uq_reactivo_guia_num (guia_id, numero),
  CONSTRAINT fk_reactivo_guia      FOREIGN KEY (guia_id)            REFERENCES guia(id)            ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_reactivo_dimension FOREIGN KEY (dimension_id)       REFERENCES dimension(id)       ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_reactivo_dominio   FOREIGN KEY (dominio_id)         REFERENCES dominio(id)         ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_reactivo_categoria FOREIGN KEY (categoria_id)       REFERENCES categoria(id)       ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_reactivo_filtro    FOREIGN KEY (pregunta_filtro_id) REFERENCES pregunta_filtro(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE umbral (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  guia_id          INT UNSIGNED NOT NULL,
  nivel_agregacion ENUM('final','categoria','dominio') NOT NULL,
  categoria_id     INT UNSIGNED NULL,              -- se usa si nivel_agregacion='categoria'
  dominio_id       INT UNSIGNED NULL,              -- se usa si nivel_agregacion='dominio'
  nivel_riesgo     ENUM('nulo','bajo','medio','alto','muy_alto') NOT NULL,
  limite_inferior  INT NULL,                       -- NULL en el nivel más bajo
  limite_superior  INT NULL,                       -- NULL en el nivel más alto (inferior<=v<superior)
  CONSTRAINT fk_umbral_guia      FOREIGN KEY (guia_id)      REFERENCES guia(id)      ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_umbral_categoria FOREIGN KEY (categoria_id) REFERENCES categoria(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_umbral_dominio   FOREIGN KEY (dominio_id)   REFERENCES dominio(id)   ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- BLOQUE B: LA OPERACIÓN (datos del uso diario)
-- ---------------------------------------------------------------------

CREATE TABLE secretaria (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre     VARCHAR(200) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE usuario (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  bee_user_id   INT NOT NULL,                       -- enlace a la cuenta nativa de Bee (bee_users.id) — FIRMADO a propósito: bee_users.id es int(11) sin UNSIGNED (ver db_beeframework.sql), la FK del Bloque C exige tipos idénticos
  rol           ENUM('superusuario','administrador') NOT NULL,
  secretaria_id INT UNSIGNED NOT NULL,
  estado        ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',  -- agregada 2026-09-24: cierra la limitación de la Pasada 7 (borrar_administrador() no podía usar DELETE por auditoria.usuario_id ON DELETE RESTRICT; ahora "baja" = estado='inactivo', ver docs/ARQUITECTURA.md)
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_usuario_bee_user (bee_user_id),
  CONSTRAINT fk_usuario_secretaria FOREIGN KEY (secretaria_id)
    REFERENCES secretaria(id) ON DELETE RESTRICT ON UPDATE CASCADE
  -- NOTA: la FK a bee_users se agrega al final (ver bloque C), por si acaso.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE centro_trabajo (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  secretaria_id    INT UNSIGNED NOT NULL,
  administrador_id INT UNSIGNED NULL,              -- usuario responsable (rol administrador)
  nombre           VARCHAR(200) NOT NULL,
  num_trabajadores INT UNSIGNED NOT NULL,          -- determina la guía aplicable
  created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_centro_secretaria    FOREIGN KEY (secretaria_id)    REFERENCES secretaria(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_centro_administrador FOREIGN KEY (administrador_id) REFERENCES usuario(id)    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE token (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  centro_trabajo_id INT UNSIGNED NOT NULL,
  codigo            VARCHAR(64) NOT NULL,
  fecha_inicio      DATE NOT NULL,
  fecha_fin         DATE NOT NULL,
  estado            ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_token_codigo (codigo),
  CONSTRAINT fk_token_centro FOREIGN KEY (centro_trabajo_id)
    REFERENCES centro_trabajo(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE aplicacion (
  id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  token_id                INT UNSIGNED NOT NULL,
  guia_id                 INT UNSIGNED NOT NULL,    -- guía congelada al momento de responder
  nombre                  VARCHAR(200) NOT NULL,
  numero_servidor_publico VARCHAR(50)  NOT NULL,
  atiende_clientes        TINYINT(1) NULL,          -- respuesta al filtro F1
  es_jefe                 TINYINT(1) NULL,          -- respuesta al filtro F2
  estado                  ENUM('en_progreso','completada') NOT NULL DEFAULT 'en_progreso',
  created_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_aplicacion_token_servidor (token_id, numero_servidor_publico),
  KEY idx_aplicacion_servidor (numero_servidor_publico),
  CONSTRAINT fk_aplicacion_token FOREIGN KEY (token_id) REFERENCES token(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_aplicacion_guia  FOREIGN KEY (guia_id)  REFERENCES guia(id)  ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE respuesta (
  id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  aplicacion_id       INT UNSIGNED NOT NULL,
  reactivo_id         INT UNSIGNED NOT NULL,
  opcion_respuesta_id INT UNSIGNED NOT NULL,
  UNIQUE KEY uq_respuesta_aplicacion_reactivo (aplicacion_id, reactivo_id),
  CONSTRAINT fk_respuesta_aplicacion FOREIGN KEY (aplicacion_id)       REFERENCES aplicacion(id)       ON DELETE CASCADE  ON UPDATE CASCADE,
  CONSTRAINT fk_respuesta_reactivo   FOREIGN KEY (reactivo_id)         REFERENCES reactivo(id)         ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_respuesta_opcion     FOREIGN KEY (opcion_respuesta_id) REFERENCES opcion_respuesta(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE resultado (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  aplicacion_id     INT UNSIGNED NOT NULL,
  calificacion_final INT UNSIGNED NOT NULL,
  nivel_riesgo      ENUM('nulo','bajo','medio','alto','muy_alto') NOT NULL,
  fecha_calculo     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_resultado_aplicacion (aplicacion_id),
  CONSTRAINT fk_resultado_aplicacion FOREIGN KEY (aplicacion_id)
    REFERENCES aplicacion(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE resultado_detalle (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  resultado_id     INT UNSIGNED NOT NULL,
  nivel_agregacion ENUM('categoria','dominio') NOT NULL,
  categoria_id     INT UNSIGNED NULL,
  dominio_id       INT UNSIGNED NULL,
  calificacion     INT UNSIGNED NOT NULL,
  nivel_riesgo     ENUM('nulo','bajo','medio','alto','muy_alto') NOT NULL,
  CONSTRAINT fk_detalle_resultado FOREIGN KEY (resultado_id) REFERENCES resultado(id) ON DELETE CASCADE  ON UPDATE CASCADE,
  CONSTRAINT fk_detalle_categoria FOREIGN KEY (categoria_id) REFERENCES categoria(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_detalle_dominio   FOREIGN KEY (dominio_id)   REFERENCES dominio(id)   ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE auditoria (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  accion     VARCHAR(100) NOT NULL,
  entidad    VARCHAR(100) NOT NULL,
  entidad_id INT UNSIGNED NULL,
  detalle    TEXT NULL,
  ip         VARCHAR(45) NULL,                  -- IPv4/IPv6 del solicitante (RNF-01: trazabilidad forense de datos sensibles)
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_auditoria_usuario (usuario_id),
  CONSTRAINT fk_auditoria_usuario FOREIGN KEY (usuario_id)
    REFERENCES usuario(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- BLOQUE C: Enlace con la tabla nativa de Bee
-- Ejecuta esta línea SOLO si bee_users es InnoDB (verifícalo primero;
-- en este proyecto sí lo es, ver db_beeframework.sql).
-- Si tu instalación tuviera bee_users en MyISAM, omítela y deja
-- bee_user_id como columna indexada sin FK dura.
--
-- usuario.bee_user_id se declaró INT (firmado, sin UNSIGNED) arriba
-- específicamente para que esta FK no falle: MySQL/MariaDB exige que ambos
-- lados de una FK tengan el mismo tipo, y bee_users.id es int(11) firmado.
-- ---------------------------------------------------------------------
ALTER TABLE usuario
  ADD CONSTRAINT fk_usuario_bee_user FOREIGN KEY (bee_user_id)
  REFERENCES bee_users(id) ON DELETE RESTRICT ON UPDATE CASCADE;