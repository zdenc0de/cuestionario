# Planificación del Proyecto
## Sistema de aplicación y evaluación de los cuestionarios de ambiente laboral (NOM-035-STPS-2018, Guías de Referencia II y III)

| | |
|---|---|
| **Proyecto** | Plataforma web para la aplicación digital y calificación automatizada de los cuestionarios de factores de riesgo psicosocial de la NOM-035 (Guía II, 46 reactivos, y Guía III, 72 reactivos), con selección del instrumento según el tamaño del centro de trabajo |
| **Dependencia** | Secretaría de Cultura y Turismo del Estado de México — Área de TI |
| **Responsable del desarrollo** | Emilio Zdenko Abarca Cruz (prestador de servicio social) |
| **Stack** | PHP 8.2 · Bee Framework 1.5.8 · MySQL/MariaDB · Apache (XAMPP) |
| **Fecha de elaboración** | Septiembre 2026 |
| **Fase actual** | Planificación del Proyecto (04/09 – 09/09) |

---

## 1. Contexto y justificación

La **NOM-035-STPS-2018** ("Factores de riesgo psicosocial en el trabajo — Identificación, análisis y prevención") es una norma oficial mexicana de **cumplimiento obligatorio** para todos los centros de trabajo del país. Establece la obligación de identificar y analizar los factores de riesgo psicosocial y de evaluar el entorno organizacional.

El proyecto abarca los **dos cuestionarios de autoevaluación** de la norma, que se aplican de forma excluyente según el tamaño del centro de trabajo:

- **Guía de Referencia II — 46 reactivos**, para centros de trabajo de **15 a 50 trabajadores**.
- **Guía de Referencia III — 72 reactivos**, para centros de trabajo de **más de 50 trabajadores**.

Ambos comparten la misma escala de respuesta tipo Likert de 5 opciones, la misma lógica de preguntas-filtro y un modelo de calificación normado que ubica los resultados en cinco niveles de riesgo; difieren en el número de reactivos, la estructura de dominios/categorías y los umbrales de calificación. Un centro de trabajo aplica **una sola** de las dos guías. El sistema deberá seleccionar automáticamente la guía correcta a partir del número de trabajadores registrado para cada centro.

Actualmente muchas organizaciones aplican este cuestionario en papel, Excel o formularios genéricos, lo que hace que la **calificación manual sea lenta, costosa y propensa a errores** (72 reactivos × N trabajadores × cálculo por dominio, categoría y global). Este proyecto busca **digitalizar la aplicación y automatizar por completo la calificación**, entregando resultados y niveles de riesgo de forma inmediata y confiable.

> **Nota de responsabilidad normativa:** al tratarse de una norma obligatoria, el sistema debe respetar fielmente la estructura, la escala y el método de calificación oficiales. El contenido de los reactivos y su clasificación **no son editables a criterio del desarrollador**: provienen del texto oficial de la norma.

---

## 2. Objetivos

### 2.1 Objetivo general
Desarrollar una plataforma web, sobre PHP y Bee Framework, que permita aplicar de forma digital los cuestionarios de la NOM-035 (Guías de Referencia II y III), capturar las respuestas de manera controlada y confidencial, y calcular automáticamente los resultados por dominio, categoría y global, ubicando cada aplicación en su nivel de riesgo correspondiente.

### 2.2 Objetivos específicos
- Digitalizar los reactivos de ambas guías con su escala Likert y su lógica de ramificación condicional (preguntas-filtro).
- Implementar el motor de calificación automatizado conforme a la norma (suma ponderada por polaridad de cada reactivo, agregación por dominio, categoría y calificación final).
- Clasificar cada aplicación en uno de los cinco niveles de riesgo definidos por la norma.
- Gestionar el acceso de los encuestados mediante **tokens únicos con vigencia** ligados a cada centro de trabajo.
- Garantizar la **confidencialidad** de las respuestas mediante control de acceso por rol y una **bitácora de auditoría**.
- Ofrecer reportes de resultados **individuales y agregados** en PDF, Excel y tablero en pantalla con gráficas.
- Entregar el sistema documentado, probado y listo para su implementación.

---

## 3. Alcance del proyecto

### 3.1 Dentro del alcance
- Soporte de **ambas guías** (II de 46 reactivos y III de 72 reactivos) con selección automática según el tamaño del centro de trabajo.
- Jerarquía de acceso de **tres perfiles**: súper usuario (por Secretaría), administrador (por centro de trabajo) y encuestado (acceso por token).
- Generación y gestión de **tokens únicos con vigencia**, ligados a cada centro de trabajo.
- Módulo de captura del cuestionario (reactivos + preguntas-filtro + datos de identificación del encuestado).
- Motor de calificación automatizado conforme a cada guía.
- Almacenamiento seguro de aplicaciones y respuestas.
- **Bitácora de auditoría** de los movimientos y consultas de los administradores, visible para el súper usuario.
- Resultados **individuales y agregados** por centro de trabajo.
- Reportes en **PDF, exportación a Excel y tablero en pantalla con gráficas**.
- Documentación técnica y de usuario.

### 3.2 Fuera del alcance (salvo indicación posterior de la Secretaría)
- El **cuestionario de acontecimientos traumáticos severos** (Guía de Referencia I) y la política de prevención de riesgos psicosociales que exige la norma.
- El **plan de acción / medidas de intervención** posteriores al diagnóstico (la norma los exige a la organización, pero son gestión, no parte del instrumento de captura).
- Integración con nómina, RH externos u otros sistemas de la Secretaría.
- Aplicación móvil nativa (el sistema será web responsivo).

> Delimitar el alcance es clave en un servicio social: evita que el proyecto crezca sin control. Todo lo listado como "fuera de alcance" puede negociarse como fase 2 si el tiempo lo permite.

---

## 4. Actores y roles del sistema

| Actor | Descripción | Acciones principales |
|---|---|---|
| **Encuestado / Trabajador** | Servidor público que responde el cuestionario. Accede con un token, sin cuenta. | Ingresar nombre y número de servidor público + token; responder el cuestionario; enviar. |
| **Administrador** (por centro de trabajo) | Responsable de un centro de trabajo, creado por el súper usuario. | Gestionar su centro de trabajo, generar y habilitar tokens/cuestionarios, consultar los resultados de su centro. |
| **Súper usuario** (por Secretaría) | Perfil de mayor jerarquía dentro de una Secretaría. | Crear y administrar administradores, consultar la **bitácora de auditoría** de movimientos y consultas, ver resultados. |

> **Multi-dependencia:** el sistema contempla que cada Secretaría tenga su propio súper usuario; este despliegue se personaliza con la identidad de la **Secretaría de Cultura y Turismo**.
>
> Bee Framework ya incluye un sistema de roles y permisos (`BeeRoleManager`, tablas `bee_roles`, `bee_permisos`, `bee_roles_permisos`), que aprovecharemos para los perfiles de súper usuario y administrador. El encuestado no requiere cuenta: entra por token.

---

## 5. Requerimientos funcionales

| ID | Requerimiento |
|---|---|
| RF-00 | El sistema selecciona automáticamente la guía a aplicar (II o III) según el número de trabajadores del centro de trabajo (15–50 → Guía II; más de 50 → Guía III). |
| RF-01 | El sistema presenta los reactivos de la guía correspondiente con su escala Likert de 5 opciones (Nunca, Casi nunca, Algunas veces, Casi siempre, Siempre). |
| RF-02 | El sistema aplica la **lógica condicional** de cada guía mediante sus dos preguntas-filtro ("¿brindo servicio a clientes/usuarios?" y "¿soy jefe de otros trabajadores?"), que habilitan u omiten los reactivos correspondientes (65–68 y 69–72 en la Guía III; 41–43 y 44–46 en la Guía II). |
| RF-03 | El sistema captura la identificación del encuestado: **nombre** y **número de servidor público**, junto con el **token** de acceso. No se solicitan datos demográficos adicionales. |
| RF-04 | El sistema valida que se respondan todos los reactivos obligatorios antes de permitir el envío. |
| RF-05 | El sistema calcula la puntuación de cada reactivo respetando su **polaridad** (reactivos normales e invertidos según la norma). |
| RF-06 | El sistema agrega las puntuaciones por **dominio**, por **categoría** y en una **calificación final**. |
| RF-07 | El sistema ubica cada resultado en su **nivel de riesgo** (nulo, bajo, medio, alto, muy alto). |
| RF-08 | El panel administrativo permite autenticación de súper usuarios y administradores. |
| RF-09 | El **súper usuario** puede crear y administrar administradores, y consultar la bitácora de auditoría. |
| RF-10 | El **administrador** puede dar de alta centros de trabajo y **generar tokens únicos con vigencia** ligados a un centro de trabajo, para habilitar la aplicación del cuestionario. |
| RF-11 | El encuestado accede con un token **válido y vigente**; el sistema rechaza tokens inexistentes, ya usados fuera de su modalidad o caducados. |
| RF-12 | El sistema registra en **bitácora** los movimientos y consultas de los administradores (quién, qué y cuándo). |
| RF-13 | El sistema presenta resultados **individuales y agregados por centro de trabajo**. |
| RF-14 | El sistema genera reportes en **PDF** (`BeePdf`), **exportación a Excel** y un **tablero en pantalla con gráficas**. |

---

## 6. Requerimientos no funcionales

| ID | Categoría | Requerimiento |
|---|---|---|
| RNF-01 | **Confidencialidad** | Las respuestas están **identificadas** (nombre y número de servidor público) y son datos sensibles; el acceso se restringe por rol y todo movimiento o consulta queda registrado en la bitácora de auditoría. |
| RNF-02 | **Seguridad** | Protección contra inyección SQL (consultas parametrizadas), XSS y CSRF (Bee incluye token CSRF), y hash de contraseñas. |
| RNF-03 | **Usabilidad** | Interfaz clara para responder 72 preguntas sin fatiga; indicador de progreso; diseño accesible. |
| RNF-04 | **Responsividad** | Funcional en escritorio y móvil (el encuestado podría responder desde su teléfono). |
| RNF-05 | **Identidad institucional** | Apego a los lineamientos gráficos de la Secretaría / Gobierno del Estado de México. |
| RNF-06 | **Rendimiento** | Cálculo de resultados en tiempo real al enviar el cuestionario. |
| RNF-07 | **Mantenibilidad** | Código organizado bajo el patrón MVC de Bee; instrumento parametrizado en base de datos, no "quemado" en código. |
| RNF-08 | **Trazabilidad** | Registro de fecha y estado de cada aplicación. |

---

## 7. Especificación de los instrumentos (NOM-035, Guías II y III)

Esta es la referencia técnica que guiará el Diseño y Desarrollo. **La fuente autoritativa es el texto oficial de la norma; los valores exactos de mapeo, polaridad y umbrales se transcribirán de las tablas oficiales (que la Secretaría posee en físico) durante la fase de Diseño.** Ambas guías comparten escala y lógica; se detalla la III por ser la más extensa y se anotan las diferencias de la II.

### 7.1 Escala de respuesta
Likert de 5 opciones. Cada opción vale de **0 a 4**. En los reactivos "normales" (que describen una condición de riesgo), *Siempre* suma más; en los reactivos "invertidos" (redactados en positivo), la puntuación se invierte. **La polaridad de cada reactivo está definida por la norma.**

### 7.2 Estructura jerárquica
El instrumento se organiza en **5 categorías → 10 dominios → 25 dimensiones → 72 reactivos**. La calificación se calcula en tres niveles: por dominio, por categoría y final (global).

| Categoría | Dominios que agrupa (según GRIII) |
|---|---|
| Ambiente de trabajo | Condiciones en el ambiente de trabajo |
| Factores propios de la actividad | Carga de trabajo · Falta de control sobre el trabajo |
| Organización del tiempo de trabajo | Jornada de trabajo · Interferencia en la relación trabajo-familia |
| Liderazgo y relaciones en el trabajo | Liderazgo · Relaciones en el trabajo · Violencia |
| Entorno organizacional | Reconocimiento del desempeño · Insuficiente sentido de pertenencia e inestabilidad |

> *La asignación exacta de cada reactivo a su dominio/categoría y su polaridad se tomará de las tablas oficiales de la norma. No se debe reconstruir de memoria.*

### 7.3 Lógica condicional (ramificación)
Ambas guías usan las mismas dos preguntas-filtro; solo cambian los números de reactivo que habilitan:

| Pregunta-filtro | Reactivos que habilita (Guía III) | Reactivos que habilita (Guía II) |
|---|---|---|
| "¿En mi trabajo debo brindar servicio a clientes o usuarios?" | 65–68 | 41–43 |
| "¿Soy jefe de otros trabajadores?" | 69–72 | 44–46 |

Si la respuesta al filtro es **No**, sus reactivos se omiten y **no cuentan** en la calificación. El segundo filtro, cuando aplica, cierra el cuestionario.

### 7.4 Niveles de riesgo (calificación final)
Cada guía tiene sus **propios umbrales** definidos por la norma. Los de la Guía III son:

| Nivel de riesgo | Rango de calificación final (Guía III) |
|---|---|
| Nulo o despreciable | menor a 50 |
| Bajo | 50 a menos de 75 |
| Medio | 75 a menos de 99 |
| Alto | 99 a menos de 140 |
| Muy alto | 140 o más |

> Los umbrales de la **Guía II** son distintos (menores, por tener menos reactivos), al igual que los rangos por dominio y por categoría de ambas guías. Todos se transcribirán en Diseño a partir de las tablas oficiales en poder de la Secretaría.

### 7.5 Diferencias de la Guía II (46 reactivos)
- Menor número de reactivos, dominios y categorías que la Guía III (la Guía II omite algunos dominios).
- Preguntas-filtro en los reactivos 41–43 y 44–46 (ver tabla anterior).
- Umbrales de calificación propios (global, por categoría y por dominio).
- La estructura exacta reactivo → dominio → categoría se transcribirá de la tabla oficial de la Guía II.

---

## 8. Modelo de datos (alto nivel)

El modelo entidad-relación detallado se elaborará en la fase de Diseño. Las entidades principales identificadas son:

- **secretaria (dependencia)** — la dependencia titular; cada una tiene su súper usuario. Este despliegue corresponde a la Secretaría de Cultura y Turismo.
- **guia** — cada versión del instrumento (Guía II y Guía III): nombre, número de reactivos, rango de trabajadores al que aplica y sus umbrales de calificación. **Es la entidad que permite soportar ambas guías sin duplicar código.**
- **usuario** — cuentas del sistema (súper usuario y administrador), con su rol y su relación a una secretaría o centro de trabajo. Reutiliza el sistema de roles nativo de Bee.
- **centro_trabajo** — datos del centro de trabajo (nombre, número de trabajadores, administrador responsable). Su plantilla determina qué **guia** se le aplica.
- **token** — clave de acceso **única y con vigencia**, ligada a un centro de trabajo; habilita a los encuestados a responder. Registra fecha de creación, vencimiento y estado.
- **aplicacion** — una instancia respondida del cuestionario; ligada a un **token** (y por tanto a un centro de trabajo y una **guia**). Guarda el **nombre** y **número de servidor público** del encuestado, fecha, estado y las respuestas a las preguntas-filtro.
- **reactivo** — los reactivos parametrizados, **cada uno asociado a su guia**: número, texto, dominio, categoría, polaridad, si es condicional.
- **opcion_respuesta** — las 5 opciones de la escala Likert y su valor.
- **respuesta** — el valor elegido por el encuestado para cada reactivo de una aplicación.
- **resultado** — calificación final, nivel de riesgo y desglose por dominio y categoría de cada aplicación.
- **auditoria (bitácora)** — registro de los movimientos y consultas de los administradores (usuario, acción, entidad afectada, fecha/hora), consultable por el súper usuario.

> Decisión de diseño clave: **el instrumento vive en la base de datos, no en el código.** Al asociar cada reactivo, dominio y umbral a una **guia**, el sistema soporta la II y la III (y cualquier futura actualización de la norma) con el mismo motor de calificación, cambiando solo los datos.

---

## 9. Arquitectura técnica

- **Patrón:** MVC, siguiendo la estructura nativa de Bee Framework (controladores en `app/controllers`, modelos en `app/models`, vistas en `app/views`).
- **Backend:** PHP 8.2 sobre Apache.
- **Base de datos:** MySQL/MariaDB, accedida mediante el ORM/modelo de Bee (consultas parametrizadas).
- **Frontend:** vistas de Bee con framework CSS (Bootstrap 5, ya integrado vía CDN en Bee) + personalización con la identidad de la Secretaría.
- **Entorno de desarrollo:** XAMPP local; proyecto versionado con Git (repositorio propio).
- **Generación de reportes:** clase `BeePdf` (dompdf) incluida en Bee.

---

## 10. Seguridad y privacidad de datos

Dado el carácter sensible de la información, este apartado es prioritario (y coincide con la fase de "Pruebas: validaciones, ataques, inyección SQL" de tu cronograma):

- **Inyección SQL:** uso exclusivo de consultas parametrizadas / ORM; nunca concatenar entrada del usuario.
- **CSRF:** aprovechar el token CSRF obligatorio de Bee en formularios y peticiones AJAX.
- **XSS:** escapado de toda salida en las vistas.
- **Autenticación:** contraseñas con hash; cambiar el usuario/contraseña por defecto de Bee (`bee` / `123456`).
- **Confidencialidad:** los datos del encuestado están identificados (nombre, número de servidor público); acceso a resultados restringido por rol y registro de toda consulta en la bitácora de auditoría.
- **Tokens:** generación de tokens únicos, validación de vigencia y estado en cada acceso.
- **Actualización de dependencias:** las librerías `dompdf` y `class.upload.php` traen advertencias de seguridad conocidas; para producción real, revisar su actualización con el área de TI.
- **Protección de directorios:** conservar los `.htaccess` que Bee coloca en rutas sensibles.

---

## 11. Identidad visual (confirmada)

La imagen del sistema seguirá la identidad institucional del Gobierno del Estado de México, personalizada para la Secretaría de Cultura y Turismo.

### 11.1 Paleta de color oficial
| Rol sugerido | Pantone | HEX | Uso |
|---|---|---|---|
| Primario (guinda) | 7420 C | `#9F2241` | Encabezados, botones principales, barra de navegación, acentos de marca. |
| Texto | Black 6 C | `#000000` | Texto principal. |
| Secundario (café) | 4635 C | `#965F36` | Apoyos, subtítulos, estados. |
| Acento (oro) | 465 C | `#BC955B` | Detalles, bordes, íconos, realces. |
| Fondo suave (arena) | 468 C | `#DDC8A4` | Fondos de sección, tarjetas, franjas. |

### 11.2 Tipografías
Las tipografías institucionales son **Gotham**, **BW Modelica** y **Corporative Sans ALT**.

> **Nota técnica:** son fuentes comerciales (de pago) y podrían no contar con licencia de uso web. Si no se dispone de las licencias, se usarán sustitutas gratuitas de aspecto muy similar como *stack* de respaldo: **Montserrat** en lugar de Gotham para títulos, y una sans humanista (**Inter** o la del sistema) para el cuerpo. Esta decisión se confirma en Diseño.

### 11.3 Referencia de diseño
La única referencia visual será el portal `https://edomex.gob.mx/`, de estética **sobria y minimalista** (poco ornamento, mucho espacio en blanco, jerarquía tipográfica clara). El sistema debe verse limpio y oficial, no recargado.

### 11.4 Tokens para desarrollo
Estos valores se centralizarán en un archivo de variables CSS (`:root`) desde el inicio, para no "quemar" colores en cada vista:

```css
:root {
  --color-primario: #9F2241;  /* guinda */
  --color-texto:    #000000;
  --color-cafe:     #965F36;
  --color-oro:      #BC955B;
  --color-arena:    #DDC8A4;

  --font-titulos: "Gotham", "Montserrat", sans-serif;
  --font-cuerpo:  "BW Modelica", "Inter", system-ui, sans-serif;
}
```

> Pendiente de insumo: los **logotipos oficiales** de la Secretaría (archivos en alta resolución / SVG) para el encabezado y los reportes.

---

## 12. Entregables por fase

| Fase | Entregable |
|---|---|
| Planificación | Este documento de planeación. |
| Diseño | Modelo entidad-relación (multi-guía) · script SQL del instrumento para **ambas guías** · digitalización de las tablas de puntaje físicas · mockups de interfaz · mapa de reactivos→dominios→categorías con su polaridad y umbrales por guía · definición de la lógica de calificación. |
| Desarrollo | Sistema funcional: captura, motor de calificación, panel administrativo, reportes. |
| Pruebas | Reporte de pruebas: validaciones, seguridad (inyección SQL, CSRF, XSS), casos de la lógica condicional y verificación del cálculo contra ejemplos. |
| Implementación y Documentación | Sistema desplegado · manual técnico · manual de usuario · documentación de cierre del servicio social. |

---

## 13. Cronograma detallado (alineado al plan de actividades)

### Capacitación y uso del framework — 28/08 al 02/09 ✔ (completada)
Instalación del entorno (XAMPP, Composer, Bee), configuración y primer arranque del framework.

### Planificación del Proyecto — 04/09 al 09/09 ← *fase actual*
| Tarea | Entregable |
|---|---|
| Análisis del instrumento y reconocimiento como NOM-035 GRIII | ✔ |
| Definición de objetivos, alcance, actores y requerimientos | Este documento |
| Identificación de riesgos y supuestos a confirmar | Secciones 14 y 15 |
| **Validación con el responsable en la Secretaría** | Respuestas a los puntos a confirmar |

### Diseño — 11/09 al 18/09
| Tarea |
|---|
| Digitalización de las tablas de puntaje físicas (ambas guías) |
| Modelo entidad-relación (multi-guía) y script SQL del instrumento |
| Transcripción oficial de reactivos → dominios/categorías + polaridad + umbrales, para Guía II y Guía III |
| Diseño de la interfaz con identidad de la Secretaría (mockups) |
| Definición del algoritmo de calificación |

### Desarrollo — 18/09 al 09/10
| Tarea |
|---|
| Semilla de la base de datos con el instrumento |
| Módulo de captura del cuestionario + lógica condicional |
| Motor de calificación (dominio, categoría, global, nivel de riesgo) |
| Panel administrativo (roles, centros de trabajo, aplicaciones) |
| Visualización de resultados y reportes PDF |

### Pruebas — 12/10 al 16/10
| Tarea |
|---|
| Pruebas de validación de formularios y lógica condicional |
| Verificación del cálculo contra casos de ejemplo calculados a mano |
| Pruebas de seguridad: inyección SQL, CSRF, XSS, control de acceso |

### Implementación y Documentación — 19/10 al 23/10
| Tarea |
|---|
| Despliegue en el entorno destino |
| Manual técnico y manual de usuario |
| Documentación de cierre del servicio social |

### Hitos
- **M1 (09/09):** Planeación validada por la Secretaría.
- **M2 (18/09):** Diseño y modelo de datos aprobados.
- **M3 (09/10):** Sistema funcional completo.
- **M4 (16/10):** Sistema probado y seguro.
- **M5 (23/10):** Sistema implementado y documentado.

---

## 14. Riesgos y mitigaciones

| Riesgo | Impacto | Mitigación |
|---|---|---|
| Interpretar mal el mapeo de reactivos o la polaridad | Alto — calificaciones incorrectas | Transcribir de las tablas oficiales de la norma y verificar con casos calculados a mano. |
| Manejo de tokens (unicidad, vigencia, reuso indebido) | Alto — integridad de datos | Generar tokens únicos, validar vigencia y estado en cada acceso, registrar uso. |
| Manejo indebido de datos identificados y sensibles | Alto — legal/ético | Acceso por rol, bitácora de auditoría, cifrado en tránsito, seguridad reforzada. |
| Complejidad de los reportes (PDF, Excel y gráficas) | Medio | Reutilizar `BeePdf`; librería de Excel y de gráficas definidas en Diseño; empezar por lo simple. |
| Dependencias con vulnerabilidades (dompdf, class.upload) | Medio | Documentar y, para producción real, coordinar actualización con TI. |
| Curva de aprendizaje de Bee (framework de nicho, poca documentación) | Medio | Apoyarse en el curso oficial de Joystick y prototipos tempranos. |
| Fatiga del encuestado (72 preguntas) | Bajo/Medio | Indicador de progreso, diseño limpio, guardado por secciones si aplica. |
| Retrasos por dudas normativas | Medio | Resolver todas las preguntas de la sección 15 al inicio. |

---

## 15. Decisiones confirmadas y puntos pendientes

### 15.1 Decisiones confirmadas con la Secretaría
- **Alcance / propiedad:** el instrumento es general (NOM-035, no exclusivo de la Secretaría), pero **este proyecto se enfoca en la Secretaría de Cultura y Turismo**; los logotipos y referencias serán de dicha Secretaría.
- **Jerarquía de acceso:** cada Secretaría tiene un **súper usuario** que consulta la bitácora de auditoría y administra a los **administradores**; cada administrador es responsable de un **centro de trabajo**, habilita cuestionarios y genera los **tokens**.
- **Acceso del encuestado:** ingresa al portal con sus datos y un **token único con vigencia**; sus respuestas quedan ligadas al token (y por tanto al centro de trabajo).
- **Identificación (confidencialidad):** se solicita **nombre** y **número de servidor público** (más el token). **No** se piden datos demográficos (sexo, área, puesto, antigüedad).
- **Resultados:** se requieren **individuales y agregados** por centro de trabajo.
- **Reportes:** **PDF, exportación a Excel y tablero en pantalla con gráficas** (los tres).
- **Instrumento:** se implementan **ambas guías** (II y III) con selección por tamaño del centro de trabajo. La Guía I (acontecimientos traumáticos severos) queda fuera de alcance.
- **Tablas de calificación:** la Secretaría las tiene en **físico**; se digitalizarán en la fase de Diseño.
- **Identidad visual:** paleta y tipografías **confirmadas** (ver sección 11).

### 15.2 Pendientes
- **Despliegue en producción:** aún no se conoce dónde se alojará el sistema (servidor de la Secretaría, hosting o intranet) ni bajo qué versión de PHP. *No bloquea el desarrollo local; se define más adelante.*
- **Logotipos oficiales** de la Secretaría en alta resolución / SVG.
- **Licencias de las tipografías** para uso web (o confirmar el uso de las sustitutas gratuitas).
- **Modelo del token** (a afinar en Diseño): un token por centro de trabajo usado por varios encuestados durante su vigencia, o un token por encuestado.

---

## 16. Glosario

- **NOM-035-STPS-2018:** norma oficial mexicana sobre factores de riesgo psicosocial en el trabajo.
- **Guía de Referencia II:** cuestionario de 46 reactivos para centros de trabajo de 15 a 50 trabajadores.
- **Guía de Referencia III:** cuestionario de 72 reactivos para centros de trabajo con más de 50 trabajadores.
- **Factores de riesgo psicosocial:** condiciones del trabajo que pueden provocar trastornos de ansiedad, estrés grave, etc.
- **Dominio / Categoría:** niveles de agrupación de los reactivos que estructuran la calificación.
- **Reactivo:** cada una de las preguntas del instrumento.
- **Polaridad:** sentido en que puntúa un reactivo (normal o invertido).
- **Nivel de riesgo:** clasificación final del resultado (nulo, bajo, medio, alto, muy alto).