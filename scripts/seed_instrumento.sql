-- =====================================================================
-- SEED del instrumento NOM-035 (Guías II y III) — exportado con
-- mysqldump --no-create-info --complete-insert desde la base de datos
-- local ya sembrada, el 2026-09-25.
--
-- Qué es: SOLO datos (346 INSERT), NO crea tablas. Requiere que el
-- esquema ya exista (correr primero db_beeframework.sql y luego
-- docs/DDL/ddl.sql, EN ESE ORDEN — ddl.sql tiene una FK hacia
-- bee_users que db_beeframework.sql crea).
--
-- Tablas incluidas (catálogo del instrumento, sin datos de operación):
--   guia (2), categoria (9), dominio (18), dimension (45),
--   pregunta_filtro (4), opcion_respuesta (5), reactivo (118),
--   umbral (145) — 346 filas en total, confirmado contra
--   docs/HANDOFF_DESARROLLO.md §3.
--
-- NO incluye: secretaria/bee_users/usuario/centro_trabajo/token/
-- aplicacion/respuesta/resultado/resultado_detalle/auditoria — esas son
-- datos de OPERACIÓN (cuentas, respuestas capturadas), no del
-- instrumento; cada entorno arranca esas tablas vacías. Para la cuenta
-- raíz del súper usuario, usar scripts/bootstrap_superusuario.sql aparte.
--
-- Ejecutar tal cual, de una sola vez, en phpMyAdmin (pestaña SQL) sobre
-- la base db_beeframework, después del DDL.
-- =====================================================================

-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: db_beeframework
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `guia`
--

LOCK TABLES `guia` WRITE;
/*!40000 ALTER TABLE `guia` DISABLE KEYS */;
INSERT INTO `guia` (`id`, `clave`, `nombre`, `num_reactivos`, `trabajadores_min`, `trabajadores_max`) VALUES (1,'GRII','Guía de Referencia II — Identificación y análisis de los factores de riesgo psicosocial',46,16,50);
INSERT INTO `guia` (`id`, `clave`, `nombre`, `num_reactivos`, `trabajadores_min`, `trabajadores_max`) VALUES (2,'GRIII','Guía de Referencia III — Identificación y análisis de los factores de riesgo psicosocial y evaluación del entorno organizacional',72,51,NULL);
/*!40000 ALTER TABLE `guia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `categoria`
--

LOCK TABLES `categoria` WRITE;
/*!40000 ALTER TABLE `categoria` DISABLE KEYS */;
INSERT INTO `categoria` (`id`, `guia_id`, `nombre`, `orden`) VALUES (1,1,'Ambiente de trabajo',1);
INSERT INTO `categoria` (`id`, `guia_id`, `nombre`, `orden`) VALUES (2,1,'Factores propios de la actividad',2);
INSERT INTO `categoria` (`id`, `guia_id`, `nombre`, `orden`) VALUES (3,1,'Organización del tiempo de trabajo',3);
INSERT INTO `categoria` (`id`, `guia_id`, `nombre`, `orden`) VALUES (4,1,'Liderazgo y relaciones en el trabajo',4);
INSERT INTO `categoria` (`id`, `guia_id`, `nombre`, `orden`) VALUES (5,2,'Ambiente de trabajo',1);
INSERT INTO `categoria` (`id`, `guia_id`, `nombre`, `orden`) VALUES (6,2,'Factores propios de la actividad',2);
INSERT INTO `categoria` (`id`, `guia_id`, `nombre`, `orden`) VALUES (7,2,'Organización del tiempo de trabajo',3);
INSERT INTO `categoria` (`id`, `guia_id`, `nombre`, `orden`) VALUES (8,2,'Liderazgo y relaciones en el trabajo',4);
INSERT INTO `categoria` (`id`, `guia_id`, `nombre`, `orden`) VALUES (9,2,'Entorno organizacional',5);
/*!40000 ALTER TABLE `categoria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `dominio`
--

LOCK TABLES `dominio` WRITE;
/*!40000 ALTER TABLE `dominio` DISABLE KEYS */;
INSERT INTO `dominio` (`id`, `categoria_id`, `nombre`, `orden`) VALUES (1,1,'Condiciones en el ambiente de trabajo',1);
INSERT INTO `dominio` (`id`, `categoria_id`, `nombre`, `orden`) VALUES (2,2,'Carga de trabajo',1);
INSERT INTO `dominio` (`id`, `categoria_id`, `nombre`, `orden`) VALUES (3,2,'Falta de control sobre el trabajo',2);
INSERT INTO `dominio` (`id`, `categoria_id`, `nombre`, `orden`) VALUES (4,3,'Jornada de trabajo',1);
INSERT INTO `dominio` (`id`, `categoria_id`, `nombre`, `orden`) VALUES (5,3,'Interferencia en la relación trabajo-familia',2);
INSERT INTO `dominio` (`id`, `categoria_id`, `nombre`, `orden`) VALUES (6,4,'Liderazgo',1);
INSERT INTO `dominio` (`id`, `categoria_id`, `nombre`, `orden`) VALUES (7,4,'Relaciones en el trabajo',2);
INSERT INTO `dominio` (`id`, `categoria_id`, `nombre`, `orden`) VALUES (8,4,'Violencia',3);
INSERT INTO `dominio` (`id`, `categoria_id`, `nombre`, `orden`) VALUES (9,5,'Condiciones en el ambiente de trabajo',1);
INSERT INTO `dominio` (`id`, `categoria_id`, `nombre`, `orden`) VALUES (10,6,'Carga de trabajo',1);
INSERT INTO `dominio` (`id`, `categoria_id`, `nombre`, `orden`) VALUES (11,6,'Falta de control sobre el trabajo',2);
INSERT INTO `dominio` (`id`, `categoria_id`, `nombre`, `orden`) VALUES (12,7,'Jornada de trabajo',1);
INSERT INTO `dominio` (`id`, `categoria_id`, `nombre`, `orden`) VALUES (13,7,'Interferencia en la relación trabajo-familia',2);
INSERT INTO `dominio` (`id`, `categoria_id`, `nombre`, `orden`) VALUES (14,8,'Liderazgo',1);
INSERT INTO `dominio` (`id`, `categoria_id`, `nombre`, `orden`) VALUES (15,8,'Relaciones en el trabajo',2);
INSERT INTO `dominio` (`id`, `categoria_id`, `nombre`, `orden`) VALUES (16,8,'Violencia',3);
INSERT INTO `dominio` (`id`, `categoria_id`, `nombre`, `orden`) VALUES (17,9,'Reconocimiento del desempeño',1);
INSERT INTO `dominio` (`id`, `categoria_id`, `nombre`, `orden`) VALUES (18,9,'Insuficiente sentido de pertenencia e inestabilidad',2);
/*!40000 ALTER TABLE `dominio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `dimension`
--

LOCK TABLES `dimension` WRITE;
/*!40000 ALTER TABLE `dimension` DISABLE KEYS */;
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (1,1,'Condiciones peligrosas e inseguras');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (2,1,'Condiciones deficientes e insalubres');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (3,1,'Trabajos peligrosos');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (4,2,'Cargas cuantitativas');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (5,2,'Ritmos de trabajo acelerado');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (6,2,'Carga mental');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (7,2,'Cargas psicológicas emocionales');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (8,2,'Cargas de alta responsabilidad');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (9,2,'Cargas contradictorias o inconsistentes');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (10,3,'Falta de control y autonomía sobre el trabajo');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (11,3,'Limitada o nula posibilidad de desarrollo');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (12,3,'Limitada o inexistente capacitación');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (13,4,'Jornadas de trabajo extensas');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (14,5,'Influencia del trabajo fuera del centro laboral');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (15,5,'Influencia de las responsabilidades familiares');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (16,6,'Escasa claridad de funciones');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (17,6,'Características del liderazgo');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (18,7,'Relaciones sociales en el trabajo');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (19,7,'Deficiente relación con los colaboradores que supervisa');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (20,8,'Violencia laboral');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (21,9,'Condiciones peligrosas e inseguras');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (22,9,'Condiciones deficientes e insalubres');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (23,9,'Trabajos peligrosos');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (24,10,'Cargas cuantitativas');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (25,10,'Ritmos de trabajo acelerado');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (26,10,'Carga mental');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (27,10,'Cargas psicológicas emocionales');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (28,10,'Cargas de alta responsabilidad');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (29,10,'Cargas contradictorias o inconsistentes');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (30,11,'Falta de control y autonomía sobre el trabajo');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (31,11,'Limitada o nula posibilidad de desarrollo');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (32,11,'Insuficiente participación y manejo del cambio');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (33,11,'Limitada o inexistente capacitación');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (34,12,'Jornadas de trabajo extensas');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (35,13,'Influencia del trabajo fuera del centro laboral');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (36,13,'Influencia de las responsabilidades familiares');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (37,14,'Escasa claridad de funciones');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (38,14,'Características del liderazgo');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (39,15,'Relaciones sociales en el trabajo');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (40,15,'Deficiente relación con los colaboradores que supervisa');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (41,16,'Violencia laboral');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (42,17,'Escasa o nula retroalimentación del desempeño');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (43,17,'Escaso o nulo reconocimiento y compensación');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (44,18,'Limitado sentido de pertenencia');
INSERT INTO `dimension` (`id`, `dominio_id`, `nombre`) VALUES (45,18,'Inestabilidad laboral');
/*!40000 ALTER TABLE `dimension` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `pregunta_filtro`
--

LOCK TABLES `pregunta_filtro` WRITE;
/*!40000 ALTER TABLE `pregunta_filtro` DISABLE KEYS */;
INSERT INTO `pregunta_filtro` (`id`, `guia_id`, `texto`, `orden`) VALUES (1,1,'En mi trabajo debo brindar servicio a clientes o usuarios',1);
INSERT INTO `pregunta_filtro` (`id`, `guia_id`, `texto`, `orden`) VALUES (2,1,'Soy jefe de otros trabajadores',2);
INSERT INTO `pregunta_filtro` (`id`, `guia_id`, `texto`, `orden`) VALUES (3,2,'En mi trabajo debo brindar servicio a clientes o usuarios',1);
INSERT INTO `pregunta_filtro` (`id`, `guia_id`, `texto`, `orden`) VALUES (4,2,'Soy jefe de otros trabajadores',2);
/*!40000 ALTER TABLE `pregunta_filtro` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `opcion_respuesta`
--

LOCK TABLES `opcion_respuesta` WRITE;
/*!40000 ALTER TABLE `opcion_respuesta` DISABLE KEYS */;
INSERT INTO `opcion_respuesta` (`id`, `etiqueta`, `posicion`) VALUES (1,'Siempre',0);
INSERT INTO `opcion_respuesta` (`id`, `etiqueta`, `posicion`) VALUES (2,'Casi siempre',1);
INSERT INTO `opcion_respuesta` (`id`, `etiqueta`, `posicion`) VALUES (3,'Algunas veces',2);
INSERT INTO `opcion_respuesta` (`id`, `etiqueta`, `posicion`) VALUES (4,'Casi nunca',3);
INSERT INTO `opcion_respuesta` (`id`, `etiqueta`, `posicion`) VALUES (5,'Nunca',4);
/*!40000 ALTER TABLE `opcion_respuesta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `reactivo`
--

LOCK TABLES `reactivo` WRITE;
/*!40000 ALTER TABLE `reactivo` DISABLE KEYS */;
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (1,1,2,1,1,1,'Mi trabajo me exige hacer mucho esfuerzo físico','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (2,1,1,1,1,2,'Me preocupa sufrir un accidente en mi trabajo','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (3,1,3,1,1,3,'Considero que las actividades que realizo son peligrosas','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (4,1,4,2,2,4,'Por la cantidad de trabajo que tengo debo quedarme tiempo adicional a mi turno','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (5,1,5,2,2,5,'Por la cantidad de trabajo que tengo debo trabajar sin parar','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (6,1,5,2,2,6,'Considero que es necesario mantener un ritmo de trabajo acelerado','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (7,1,6,2,2,7,'Mi trabajo exige que esté muy concentrado','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (8,1,6,2,2,8,'Mi trabajo requiere que memorice mucha información','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (9,1,4,2,2,9,'Mi trabajo exige que atienda varios asuntos al mismo tiempo','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (10,1,8,2,2,10,'En mi trabajo soy responsable de cosas de mucho valor','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (11,1,8,2,2,11,'Respondo ante mi jefe por los resultados de toda mi área de trabajo','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (12,1,9,2,2,12,'En mi trabajo me dan órdenes contradictorias','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (13,1,9,2,2,13,'Considero que en mi trabajo me piden hacer cosas innecesarias','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (14,1,13,4,3,14,'Trabajo horas extras más de tres veces a la semana','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (15,1,13,4,3,15,'Mi trabajo me exige laborar en días de descanso, festivos o fines de semana','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (16,1,14,5,3,16,'Considero que el tiempo en el trabajo es mucho y perjudica mis actividades familiares o personales','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (17,1,15,5,3,17,'Pienso en las actividades familiares o personales cuando estoy en mi trabajo','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (18,1,11,3,2,18,'Mi trabajo permite que desarrolle nuevas habilidades','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (19,1,11,3,2,19,'En mi trabajo puedo aspirar a un mejor puesto','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (20,1,10,3,2,20,'Durante mi jornada de trabajo puedo tomar pausas cuando las necesito','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (21,1,10,3,2,21,'Puedo decidir la velocidad a la que realizo mis actividades en mi trabajo','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (22,1,10,3,2,22,'Puedo cambiar el orden de las actividades que realizo en mi trabajo','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (23,1,16,6,4,23,'Me informan con claridad cuáles son mis funciones','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (24,1,16,6,4,24,'Me explican claramente los resultados que debo obtener en mi trabajo','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (25,1,16,6,4,25,'Me informan con quién puedo resolver problemas o asuntos de trabajo','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (26,1,12,3,2,26,'Me permiten asistir a capacitaciones relacionadas con mi trabajo','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (27,1,12,3,2,27,'Recibo capacitación útil para hacer mi trabajo','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (28,1,17,6,4,28,'Mi jefe tiene en cuenta mis puntos de vista y opiniones','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (29,1,17,6,4,29,'Mi jefe ayuda a solucionar los problemas que se presentan en el trabajo','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (30,1,18,7,4,30,'Puedo confiar en mis compañeros de trabajo','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (31,1,18,7,4,31,'Cuando tenemos que realizar trabajo de equipo los compañeros colaboran','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (32,1,18,7,4,32,'Mis compañeros de trabajo me ayudan cuando tengo dificultades','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (33,1,20,8,4,33,'En mi trabajo puedo expresarme libremente sin interrupciones','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (34,1,20,8,4,34,'Recibo críticas constantes a mi persona y/o trabajo','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (35,1,20,8,4,35,'Recibo burlas, calumnias, difamaciones, humillaciones o ridiculizaciones','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (36,1,20,8,4,36,'Se ignora mi presencia o se me excluye de las reuniones de trabajo y en la toma de decisiones','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (37,1,20,8,4,37,'Se manipulan las situaciones de trabajo para hacerme parecer un mal trabajador','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (38,1,20,8,4,38,'Se ignoran mis éxitos laborales y se atribuyen a otros trabajadores','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (39,1,20,8,4,39,'Me bloquean o impiden las oportunidades que tengo para obtener ascenso o mejora en mi trabajo','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (40,1,20,8,4,40,'He presenciado actos de violencia en mi centro de trabajo','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (41,1,7,2,2,41,'Atiendo clientes o usuarios muy enojados','normal',1);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (42,1,7,2,2,42,'Mi trabajo me exige atender personas muy necesitadas de ayuda o enfermas','normal',1);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (43,1,7,2,2,43,'Para hacer mi trabajo debo demostrar sentimientos distintos a los míos','normal',1);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (44,1,19,7,4,44,'Comunican tarde los asuntos de trabajo','normal',2);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (45,1,19,7,4,45,'Dificultan el logro de los resultados del trabajo','normal',2);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (46,1,19,7,4,46,'Ignoran las sugerencias para mejorar su trabajo','normal',2);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (47,2,21,9,5,1,'El espacio donde trabajo me permite realizar mis actividades de manera segura e higiénica','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (48,2,22,9,5,2,'Mi trabajo me exige hacer mucho esfuerzo físico','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (49,2,21,9,5,3,'Me preocupa sufrir un accidente en mi trabajo','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (50,2,22,9,5,4,'Considero que en mi trabajo se aplican las normas de seguridad y salud en el trabajo','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (51,2,23,9,5,5,'Considero que las actividades que realizo son peligrosas','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (52,2,24,10,6,6,'Por la cantidad de trabajo que tengo debo quedarme tiempo adicional a mi turno','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (53,2,25,10,6,7,'Por la cantidad de trabajo que tengo debo trabajar sin parar','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (54,2,25,10,6,8,'Considero que es necesario mantener un ritmo de trabajo acelerado','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (55,2,26,10,6,9,'Mi trabajo exige que esté muy concentrado','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (56,2,26,10,6,10,'Mi trabajo requiere que memorice mucha información','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (57,2,26,10,6,11,'En mi trabajo tengo que tomar decisiones difíciles muy rápido','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (58,2,24,10,6,12,'Mi trabajo exige que atienda varios asuntos al mismo tiempo','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (59,2,28,10,6,13,'En mi trabajo soy responsable de cosas de mucho valor','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (60,2,28,10,6,14,'Respondo ante mi jefe por los resultados de toda mi área de trabajo','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (61,2,29,10,6,15,'En el trabajo me dan órdenes contradictorias','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (62,2,29,10,6,16,'Considero que en mi trabajo me piden hacer cosas innecesarias','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (63,2,34,12,7,17,'Trabajo horas extras más de tres veces a la semana','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (64,2,34,12,7,18,'Mi trabajo me exige laborar en días de descanso, festivos o fines de semana','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (65,2,35,13,7,19,'Considero que el tiempo en el trabajo es mucho y perjudica mis actividades familiares o personales','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (66,2,35,13,7,20,'Debo atender asuntos de trabajo cuando estoy en casa','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (67,2,36,13,7,21,'Pienso en las actividades familiares o personales cuando estoy en mi trabajo','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (68,2,36,13,7,22,'Pienso que mis responsabilidades familiares afectan mi trabajo','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (69,2,31,11,6,23,'Mi trabajo permite que desarrolle nuevas habilidades','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (70,2,31,11,6,24,'En mi trabajo puedo aspirar a un mejor puesto','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (71,2,30,11,6,25,'Durante mi jornada de trabajo puedo tomar pausas cuando las necesito','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (72,2,30,11,6,26,'Puedo decidir cuánto trabajo realizo durante la jornada laboral','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (73,2,30,11,6,27,'Puedo decidir la velocidad a la que realizo mis actividades en mi trabajo','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (74,2,30,11,6,28,'Puedo cambiar el orden de las actividades que realizo en mi trabajo','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (75,2,32,11,6,29,'Los cambios que se presentan en mi trabajo dificultan mi labor','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (76,2,32,11,6,30,'Cuando se presentan cambios en mi trabajo se tienen en cuenta mis ideas o aportaciones','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (77,2,37,14,8,31,'Me informan con claridad cuáles son mis funciones','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (78,2,37,14,8,32,'Me explican claramente los resultados que debo obtener en mi trabajo','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (79,2,37,14,8,33,'Me explican claramente los objetivos de mi trabajo','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (80,2,37,14,8,34,'Me informan con quién puedo resolver problemas o asuntos de trabajo','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (81,2,33,11,6,35,'Me permiten asistir a capacitaciones relacionadas con mi trabajo','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (82,2,33,11,6,36,'Recibo capacitación útil para hacer mi trabajo','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (83,2,38,14,8,37,'Mi jefe ayuda a organizar mejor el trabajo','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (84,2,38,14,8,38,'Mi jefe tiene en cuenta mis puntos de vista y opiniones','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (85,2,38,14,8,39,'Mi jefe me comunica a tiempo la información relacionada con el trabajo','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (86,2,38,14,8,40,'La orientación que me da mi jefe me ayuda a realizar mejor mi trabajo','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (87,2,38,14,8,41,'Mi jefe ayuda a solucionar los problemas que se presentan en el trabajo','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (88,2,39,15,8,42,'Puedo confiar en mis compañeros de trabajo','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (89,2,39,15,8,43,'Entre compañeros solucionamos los problemas de trabajo de forma respetuosa','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (90,2,39,15,8,44,'En mi trabajo me hacen sentir parte del grupo','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (91,2,39,15,8,45,'Cuando tenemos que realizar trabajo de equipo los compañeros colaboran','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (92,2,39,15,8,46,'Mis compañeros de trabajo me ayudan cuando tengo dificultades','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (93,2,42,17,9,47,'Me informan sobre lo que hago bien en mi trabajo','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (94,2,42,17,9,48,'La forma como evalúan mi trabajo en mi centro de trabajo me ayuda a mejorar mi desempeño','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (95,2,43,17,9,49,'En mi centro de trabajo me pagan a tiempo mi salario','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (96,2,43,17,9,50,'El pago que recibo es el que merezco por el trabajo que realizo','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (97,2,43,17,9,51,'Si obtengo los resultados esperados en mi trabajo me recompensan o reconocen','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (98,2,43,17,9,52,'Las personas que hacen bien el trabajo pueden crecer laboralmente','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (99,2,45,18,9,53,'Considero que mi trabajo es estable','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (100,2,45,18,9,54,'En mi trabajo existe continua rotación de personal','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (101,2,44,18,9,55,'Siento orgullo de laborar en este centro de trabajo','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (102,2,44,18,9,56,'Me siento comprometido con mi trabajo','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (103,2,41,16,8,57,'En mi trabajo puedo expresarme libremente sin interrupciones','invertida',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (104,2,41,16,8,58,'Recibo críticas constantes a mi persona y/o trabajo','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (105,2,41,16,8,59,'Recibo burlas, calumnias, difamaciones, humillaciones o ridiculizaciones','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (106,2,41,16,8,60,'Se ignora mi presencia o se me excluye de las reuniones de trabajo y en la toma de decisiones','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (107,2,41,16,8,61,'Se manipulan las situaciones de trabajo para hacerme parecer un mal trabajador','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (108,2,41,16,8,62,'Se ignoran mis éxitos laborales y se atribuyen a otros trabajadores','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (109,2,41,16,8,63,'Me bloquean o impiden las oportunidades que tengo para obtener ascenso o mejora en mi trabajo','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (110,2,41,16,8,64,'He presenciado actos de violencia en mi centro de trabajo','normal',NULL);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (111,2,27,10,6,65,'Atiendo clientes o usuarios muy enojados','normal',3);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (112,2,27,10,6,66,'Mi trabajo me exige atender personas muy necesitadas de ayuda o enfermas','normal',3);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (113,2,27,10,6,67,'Para hacer mi trabajo debo demostrar sentimientos distintos a los míos','normal',3);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (114,2,27,10,6,68,'Mi trabajo me exige atender situaciones de violencia','normal',3);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (115,2,40,15,8,69,'Comunican tarde los asuntos de trabajo','normal',4);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (116,2,40,15,8,70,'Dificultan el logro de los resultados del trabajo','normal',4);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (117,2,40,15,8,71,'Cooperan poco cuando se necesita','normal',4);
INSERT INTO `reactivo` (`id`, `guia_id`, `dimension_id`, `dominio_id`, `categoria_id`, `numero`, `texto`, `polaridad`, `pregunta_filtro_id`) VALUES (118,2,40,15,8,72,'Ignoran las sugerencias para mejorar su trabajo','normal',4);
/*!40000 ALTER TABLE `reactivo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `umbral`
--

LOCK TABLES `umbral` WRITE;
/*!40000 ALTER TABLE `umbral` DISABLE KEYS */;
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (1,1,'final',NULL,NULL,'nulo',NULL,20);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (2,1,'final',NULL,NULL,'bajo',20,45);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (3,1,'final',NULL,NULL,'medio',45,70);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (4,1,'final',NULL,NULL,'alto',70,90);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (5,1,'final',NULL,NULL,'muy_alto',90,NULL);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (6,1,'categoria',1,NULL,'nulo',NULL,3);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (7,1,'categoria',1,NULL,'bajo',3,5);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (8,1,'categoria',1,NULL,'medio',5,7);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (9,1,'categoria',1,NULL,'alto',7,9);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (10,1,'categoria',1,NULL,'muy_alto',9,NULL);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (11,1,'categoria',2,NULL,'nulo',NULL,10);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (12,1,'categoria',2,NULL,'bajo',10,20);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (13,1,'categoria',2,NULL,'medio',20,30);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (14,1,'categoria',2,NULL,'alto',30,40);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (15,1,'categoria',2,NULL,'muy_alto',40,NULL);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (16,1,'categoria',3,NULL,'nulo',NULL,4);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (17,1,'categoria',3,NULL,'bajo',4,6);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (18,1,'categoria',3,NULL,'medio',6,9);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (19,1,'categoria',3,NULL,'alto',9,12);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (20,1,'categoria',3,NULL,'muy_alto',12,NULL);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (21,1,'categoria',4,NULL,'nulo',NULL,10);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (22,1,'categoria',4,NULL,'bajo',10,18);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (23,1,'categoria',4,NULL,'medio',18,28);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (24,1,'categoria',4,NULL,'alto',28,38);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (25,1,'categoria',4,NULL,'muy_alto',38,NULL);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (26,1,'dominio',NULL,1,'nulo',NULL,3);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (27,1,'dominio',NULL,1,'bajo',3,5);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (28,1,'dominio',NULL,1,'medio',5,7);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (29,1,'dominio',NULL,1,'alto',7,9);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (30,1,'dominio',NULL,1,'muy_alto',9,NULL);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (31,1,'dominio',NULL,2,'nulo',NULL,12);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (32,1,'dominio',NULL,2,'bajo',12,16);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (33,1,'dominio',NULL,2,'medio',16,20);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (34,1,'dominio',NULL,2,'alto',20,24);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (35,1,'dominio',NULL,2,'muy_alto',24,NULL);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (36,1,'dominio',NULL,3,'nulo',NULL,5);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (37,1,'dominio',NULL,3,'bajo',5,8);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (38,1,'dominio',NULL,3,'medio',8,11);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (39,1,'dominio',NULL,3,'alto',11,14);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (40,1,'dominio',NULL,3,'muy_alto',14,NULL);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (41,1,'dominio',NULL,4,'nulo',NULL,1);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (42,1,'dominio',NULL,4,'bajo',1,2);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (43,1,'dominio',NULL,4,'medio',2,4);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (44,1,'dominio',NULL,4,'alto',4,6);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (45,1,'dominio',NULL,4,'muy_alto',6,NULL);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (46,1,'dominio',NULL,5,'nulo',NULL,1);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (47,1,'dominio',NULL,5,'bajo',1,2);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (48,1,'dominio',NULL,5,'medio',2,4);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (49,1,'dominio',NULL,5,'alto',4,6);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (50,1,'dominio',NULL,5,'muy_alto',6,NULL);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (51,1,'dominio',NULL,6,'nulo',NULL,3);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (52,1,'dominio',NULL,6,'bajo',3,5);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (53,1,'dominio',NULL,6,'medio',5,8);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (54,1,'dominio',NULL,6,'alto',8,11);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (55,1,'dominio',NULL,6,'muy_alto',11,NULL);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (56,1,'dominio',NULL,7,'nulo',NULL,5);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (57,1,'dominio',NULL,7,'bajo',5,8);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (58,1,'dominio',NULL,7,'medio',8,11);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (59,1,'dominio',NULL,7,'alto',11,14);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (60,1,'dominio',NULL,7,'muy_alto',14,NULL);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (61,1,'dominio',NULL,8,'nulo',NULL,7);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (62,1,'dominio',NULL,8,'bajo',7,10);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (63,1,'dominio',NULL,8,'medio',10,13);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (64,1,'dominio',NULL,8,'alto',13,16);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (65,1,'dominio',NULL,8,'muy_alto',16,NULL);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (66,2,'final',NULL,NULL,'nulo',NULL,50);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (67,2,'final',NULL,NULL,'bajo',50,75);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (68,2,'final',NULL,NULL,'medio',75,99);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (69,2,'final',NULL,NULL,'alto',99,140);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (70,2,'final',NULL,NULL,'muy_alto',140,NULL);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (71,2,'categoria',5,NULL,'nulo',NULL,5);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (72,2,'categoria',5,NULL,'bajo',5,9);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (73,2,'categoria',5,NULL,'medio',9,11);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (74,2,'categoria',5,NULL,'alto',11,14);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (75,2,'categoria',5,NULL,'muy_alto',14,NULL);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (76,2,'categoria',6,NULL,'nulo',NULL,15);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (77,2,'categoria',6,NULL,'bajo',15,30);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (78,2,'categoria',6,NULL,'medio',30,45);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (79,2,'categoria',6,NULL,'alto',45,60);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (80,2,'categoria',6,NULL,'muy_alto',60,NULL);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (81,2,'categoria',7,NULL,'nulo',NULL,5);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (82,2,'categoria',7,NULL,'bajo',5,7);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (83,2,'categoria',7,NULL,'medio',7,10);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (84,2,'categoria',7,NULL,'alto',10,13);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (85,2,'categoria',7,NULL,'muy_alto',13,NULL);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (86,2,'categoria',8,NULL,'nulo',NULL,14);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (87,2,'categoria',8,NULL,'bajo',14,29);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (88,2,'categoria',8,NULL,'medio',29,42);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (89,2,'categoria',8,NULL,'alto',42,58);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (90,2,'categoria',8,NULL,'muy_alto',58,NULL);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (91,2,'categoria',9,NULL,'nulo',NULL,10);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (92,2,'categoria',9,NULL,'bajo',10,14);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (93,2,'categoria',9,NULL,'medio',14,18);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (94,2,'categoria',9,NULL,'alto',18,23);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (95,2,'categoria',9,NULL,'muy_alto',23,NULL);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (96,2,'dominio',NULL,9,'nulo',NULL,5);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (97,2,'dominio',NULL,9,'bajo',5,9);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (98,2,'dominio',NULL,9,'medio',9,11);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (99,2,'dominio',NULL,9,'alto',11,14);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (100,2,'dominio',NULL,9,'muy_alto',14,NULL);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (101,2,'dominio',NULL,10,'nulo',NULL,15);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (102,2,'dominio',NULL,10,'bajo',15,21);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (103,2,'dominio',NULL,10,'medio',21,27);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (104,2,'dominio',NULL,10,'alto',27,37);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (105,2,'dominio',NULL,10,'muy_alto',37,NULL);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (106,2,'dominio',NULL,11,'nulo',NULL,11);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (107,2,'dominio',NULL,11,'bajo',11,16);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (108,2,'dominio',NULL,11,'medio',16,21);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (109,2,'dominio',NULL,11,'alto',21,25);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (110,2,'dominio',NULL,11,'muy_alto',25,NULL);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (111,2,'dominio',NULL,12,'nulo',NULL,1);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (112,2,'dominio',NULL,12,'bajo',1,2);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (113,2,'dominio',NULL,12,'medio',2,4);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (114,2,'dominio',NULL,12,'alto',4,6);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (115,2,'dominio',NULL,12,'muy_alto',6,NULL);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (116,2,'dominio',NULL,13,'nulo',NULL,4);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (117,2,'dominio',NULL,13,'bajo',4,6);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (118,2,'dominio',NULL,13,'medio',6,8);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (119,2,'dominio',NULL,13,'alto',8,10);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (120,2,'dominio',NULL,13,'muy_alto',10,NULL);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (121,2,'dominio',NULL,14,'nulo',NULL,9);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (122,2,'dominio',NULL,14,'bajo',9,12);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (123,2,'dominio',NULL,14,'medio',12,16);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (124,2,'dominio',NULL,14,'alto',16,20);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (125,2,'dominio',NULL,14,'muy_alto',20,NULL);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (126,2,'dominio',NULL,15,'nulo',NULL,10);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (127,2,'dominio',NULL,15,'bajo',10,13);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (128,2,'dominio',NULL,15,'medio',13,17);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (129,2,'dominio',NULL,15,'alto',17,21);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (130,2,'dominio',NULL,15,'muy_alto',21,NULL);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (131,2,'dominio',NULL,16,'nulo',NULL,7);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (132,2,'dominio',NULL,16,'bajo',7,10);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (133,2,'dominio',NULL,16,'medio',10,13);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (134,2,'dominio',NULL,16,'alto',13,16);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (135,2,'dominio',NULL,16,'muy_alto',16,NULL);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (136,2,'dominio',NULL,17,'nulo',NULL,6);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (137,2,'dominio',NULL,17,'bajo',6,10);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (138,2,'dominio',NULL,17,'medio',10,14);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (139,2,'dominio',NULL,17,'alto',14,18);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (140,2,'dominio',NULL,17,'muy_alto',18,NULL);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (141,2,'dominio',NULL,18,'nulo',NULL,4);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (142,2,'dominio',NULL,18,'bajo',4,6);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (143,2,'dominio',NULL,18,'medio',6,8);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (144,2,'dominio',NULL,18,'alto',8,10);
INSERT INTO `umbral` (`id`, `guia_id`, `nivel_agregacion`, `categoria_id`, `dominio_id`, `nivel_riesgo`, `limite_inferior`, `limite_superior`) VALUES (145,2,'dominio',NULL,18,'muy_alto',10,NULL);
/*!40000 ALTER TABLE `umbral` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-25 12:51:46
