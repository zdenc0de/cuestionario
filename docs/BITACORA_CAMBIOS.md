# Bitácora de cambios — Cuestionario NOM-035 (sesiones con Claude)

> Documento de auditoría: enumera **todos** los archivos creados o modificados
> por el agente Claude en este proyecto hasta el 2026-09-24, en qué momento
> (de 10 pasadas de trabajo) y **por qué**, para que otro agente/desarrollador
> pueda revisarlo contra el código real. No repite la explicación funcional
> de cada módulo (eso ya está en `docs/ARQUITECTURA.md`); aquí el foco es
> **el cambio puntual y su justificación**.
>
> Contexto de repositorio en el momento de escribir esto: rama `main`, HEAD
> en `5b2335e` ("docs: documenta el bootstrap y el modulo de superusuario
> (Pasada 7)"). Las Pasadas 8, 9 y 10 (este documento) están en el working
> tree, sin commitear todavía. Claude no ejecutó ningún comando de Git en
> ningún momento; los commits fueron corridos por el usuario con comandos
> que Claude únicamente redactó como texto.

---

## 0. Las 10 pasadas de trabajo

| Pasada | Disparador | Qué produjo |
|---|---|---|
| **1. Esqueleto inicial** | "crea el esqueleto de carpetas, archivos y arquitectura del sistema" | 11 modelos, 4 controladores, 15 vistas, 1 hoja de tokens CSS, 2 docs. Sin lógica de negocio. |
| **2. Hardening** | "guard de rol, verbo HTTP, CSRF público + documentar 2 decisiones de diseño" | `requiere_rol()`, `requiere_metodo_post()`, ajustes a `tokenModel`/`usuarioModel`/`aplicacionModel`, secciones 7-8 de `ARQUITECTURA.md`. |
| **3. Verificación** | "comprueba que `docs/norma/Transcripcion_GuiaIII_NOM-035.md` y `docs/DDL/ddl.sql` cumplan y no generen problemas" | Realineación de nombres de columnas en los 11 modelos + 1 fix en `ddl.sql` + sección 10 de `ARQUITECTURA.md`. |
| **4. Segunda revisión** | Un segundo agente revisó la Pasada 3 y propuso 4 ajustes antes de commitear | Límite de Guía II resuelto (16, con el caso ≤15 documentado), columna `ip` agregada a `auditoria`, nota sobre `secretaria.logo`, refuerzo de la dependencia hacia los 6 modelos faltantes. |
| **5. Ajustes puntuales** | Prompt de "mi agente": afinar la captura de `ip` y agregar el guard funcional de ≤15 trabajadores | `registrar_auditoria()` deja de usar `get_user_ip()` (confía en cabeceras falsificables) y usa `$_SERVER['REMOTE_ADDR']`; guard real en `administradorController::post_centros_trabajo()`; TODO detallado del mismo guard en `cuestionarioController::post_acceso()`; sección 11 de `ARQUITECTURA.md`. |
| **6. Motor de calificación** | `docs/HANDOFF_DESARROLLO.md` §5 — primera tarea de desarrollo real | 4 modelos nuevos (`categoriaModel`, `dominioModel`, `umbralModel`, `resultadoDetalleModel`), `resultadoModel::calcular_para_aplicacion()` implementado de verdad (ya no es un stub), script de verificación, corrida y confirmada contra la BD real (0 fallos), sección 13 de `ARQUITECTURA.md`. |
| **7. Súper usuario** | `docs/HANDOFF_DESARROLLO.md` §3-4 — segunda tarea: cuenta raíz + módulo de súper usuario | Bootstrap SQL real (`scripts/bootstrap_superusuario.sql`, hash calculado con la fórmula real de Bee) + generador reutilizable; `superusuarioController` implementado (alta/listado de administradores, revocación, bitácora, alcance por secretaría); todo verificado con `curl` contra el servidor local real, no sólo con `php -l`. Sección 14 de `ARQUITECTURA.md`. |
| **8. Administrador** | `docs/HANDOFF_DESARROLLO.md` §3-4 — cierra la limitación de la Pasada 7 (`usuario.estado`) + tercera tarea: módulo de administrador | `scripts/alter_usuario_estado.sql` (para que el usuario lo corra, Claude no lo ejecutó) + código ya preparado para antes/después de aplicarlo; `administradorController` implementado (alta de centros con el guard ≤15, listado, generación/listado/revocación de tokens con `bin2hex(random_bytes())`); alcance por `administrador_id` (más estricto que por secretaría) verificado en vivo con dos administradores de la MISMA secretaría. Sección 15 de `ARQUITECTURA.md`. |
| **9. Encuestado** | `docs/HANDOFF_DESARROLLO.md` §3-4 — cuarta tarea: flujo completo del encuestado (`cuestionarioController`), cierra la cadena hasta el motor de calificación de la Pasada 6 | `preguntaFiltroModel` (nuevo) + 2 métodos JOIN en `reactivoModel`; `cuestionarioController` implementado de verdad (acceso por token, sesión nativa de PHP para ligar el flujo sin cuenta de Bee, lógica condicional de las preguntas-filtro, validación de completitud, cálculo automático del resultado al enviar); `cuestionarioView.php` reescrita para usar datos reales del instrumento. Verificado end-to-end con `curl` contra datos REALES capturados por el flujo (no inyectados), ambas guías, calificación confirmada a mano. Sección 16 de `ARQUITECTURA.md`. |
| **10. Navegación de punta a punta** | `docs/HANDOFF_DESARROLLO.md` — "dejar el sistema navegable de punta a punta para validación visual humana": conectar los tres flujos, resultado individual real, puntos de entrada desde la raíz | Redirección post-login por rol (bug real encontrado y corregido: el global `$Bee_User` que usa `get_user()` no se actualiza en la misma petición del login — ver 17.1); sidebar del panel admin dejó de ser el catálogo genérico de Bee y ahora es por rol; `resultadosController::individual()` implementado de verdad (identidad, calificación, desglose); enlace "aplicaciones respondidas → resultado" desde `administradorController::tokens()`; enlaces cruzados encuestado↔login; enlaces muertos removidos de `loginView.php`. Recorrido END-TO-END real con `curl` (súper usuario crea administrador → administrador crea centro y token → encuestado responde → administrador ve el resultado), datos dejados sembrados a propósito (no se limpiaron, ver sección 17.4). Sección 17 de `ARQUITECTURA.md`. |

En las 10 pasadas se respetaron las mismas restricciones: **no** se ejecutó
Git, **no** se modificó ningún archivo de `app/classes/*` (núcleo de Bee), y
todo lo agregado sigue las convenciones nativas de Bee Framework 1.5.8
(controladores `xyzController extends Controller implements ControllerInterface`,
modelos `xyzModel extends Model` con `$t1`, vistas en
`templates/views/{controlador}/{vista}View.php`).

---

## 1. Modelos (`app/models/`)

Los 11 modelos se crearon en la Pasada 1 siguiendo `templates/modules/bee/modelTemplate.txt`.
Debajo, el estado final de cada uno y qué cambió en las pasadas 2 y 3 respecto
a como se creó originalmente.

### 1.1 `secretariaModel.php`
- **Pasada 1:** creado. Esquema propuesto: `id, nombre, siglas, logo, creado`.
- **Pasada 3:** se quitaron `siglas`/`logo`/`creado` del comentario de esquema
  y se corrigió a `created_at`/`updated_at`. **Por qué:** `docs/DDL/ddl.sql`
  sólo define `id, nombre, created_at, updated_at` en la tabla `secretaria`;
  documentar columnas que no existen podía llevar a construir un `insertOne()`
  con claves que la BD rechazaría.
- **Pasada 4:** se agregó un TODO puntual sugiriendo una futura columna
  `logo`. **Por qué:** observación del segundo agente — el proyecto gira
  alrededor de la identidad de la Secretaría (logo en encabezado y en los
  reportes PDF/Excel); no urge ni se agregó a `ddl.sql`, sólo queda anotado
  para tenerlo en el radar.

### 1.2 `usuarioModel.php`
- **Pasada 1:** creado como tabla de enlace 1 a 1 con `bee_users` (decisión de
  diseño A). Esquema propuesto incluía `centro_trabajo_id` como columna de
  `usuario`.
- **Pasada 2:** se agregó `by_bee_user_id($beeUserId)` — **por qué:** es la
  consulta base que necesitan `obtener_usuario_actual()`/`requiere_rol()`
  (ver sección 4) para resolver el rol de contexto de la sesión.
- **Pasada 3:** se corrigió el docblock: la relación con centro de trabajo
  **no** es una columna en `usuario`, es al revés
  (`centro_trabajo.administrador_id` → `usuario.id`). **Por qué:** así lo
  define `docs/DDL/ddl.sql` (la tabla `usuario` no tiene columna
  `centro_trabajo_id`); además se agregó una nota sobre el choque de tipos
  `bee_user_id` (ver sección 8) que en ese momento aún no se había corregido.
- **Pasada 7:** `administradores_por_secretaria()` se le agregó el JOIN a
  `bee_users` que tenía pendiente desde la Pasada 1 (`username`/`email` no
  vivían en esta tabla) — es lo que consume
  `superusuarioController::administradores()` para el listado real.
- **Pasada 8:** `administradores_por_secretaria()` gana un segundo
  parámetro opcional `$estado` para filtrar por `usuario.estado` (columna
  nueva, ver sección 14.1) — documentado explícitamente que lanza
  excepción si se usa antes de que exista la columna, y que el llamador
  debe capturarla.

### 1.3 `centroTrabajoModel.php`
- **Pasada 1:** creado con `guia_id` como columna propuesta y
  `determinar_guia()` hardcodeando el rango 15–50/>50.
- **Pasada 2:** se agregó `por_secretaria($secretariaId)` — **por qué:**
  la necesita `resultadosController::centrosTrabajoPermitidos()` para el
  alcance de datos del rol `superusuario`.
- **Pasada 3:** se corrigió `numero_trabajadores` → `num_trabajadores`
  (nombre real de la columna en `ddl.sql`) y se quitó `guia_id` del esquema:
  **por qué:** `centro_trabajo` no guarda la guía en `ddl.sql`, se resuelve
  en el momento con `guiaModel::por_numero_trabajadores()`. `determinar_guia()`
  se marcó `@deprecated` (a favor de ese método, que sí lee la tabla real) y
  se dejó devolviendo `'GRII'`/`'GRIII'` (las claves reales de `guia.clave`,
  antes devolvía `'guia_ii'`/`'guia_iii'` inventados) — **por qué:** para que
  el valor que regresa se pueda usar directamente contra `guiaModel::by_clave()`.
- **Pasada 4:** el rango de `determinar_guia()` se corrigió de `>= 15` a
  `>= 16`, y se documentó explícitamente que un `null` de retorno para
  `$numeroTrabajadores <= 15` **no es un error**, es el caso "no requiere
  cuestionario" (ver 1.5). **Por qué:** el segundo agente confirmó contra el
  texto oficial de la norma que 16 es el límite correcto, resolviendo la
  discrepancia que había quedado pendiente en la Pasada 3.

### 1.4 `tokenModel.php`
- **Pasada 1:** creado con columnas `token`, `vigencia_inicio`/`vigencia_fin`,
  estado `('activo','usado','expirado','revocado')`.
- **Pasada 2 (decisión de diseño B):** se reescribió para reflejar "un token
  por centro de trabajo por campaña, no por persona, multiuso": columna
  `codigo` (no `token`), `fecha_inicio`/`fecha_fin` (no `vigencia_*`), se quitó
  el estado `'usado'` (el token no se consume). Se agregaron `by_codigo()`,
  `activo_por_centro_trabajo()`, `esta_vigente()`, `revocar()`. **Por qué:**
  esa fue la decisión de negocio que confirmó el usuario en esa pasada.
- **Pasada 3:** dos correcciones contra `ddl.sql`:
  1. `fecha_inicio`/`fecha_fin` son `DATE`, no `DATETIME` — `esta_vigente()`
     y `activo_por_centro_trabajo()` comparaban contra `now()` (con hora), lo
     que rompía la comparación de cadenas contra una columna `DATE` casi todo
     el día. Se cambió a comparar contra `date('Y-m-d')`. **Por qué:** era un
     bug real de tipos, no una decisión — se corrigió sin preguntar.
  2. El `estado` real en `ddl.sql` es `ENUM('activo','inactivo')`, no
     `('activo','expirado','revocado')` de la Pasada 2. Se ajustó `revocar()`
     para usar `'inactivo'`. **Por qué:** alinear con la única fuente de
     verdad del esquema (`ddl.sql`).
- **Pasada 8:** `generar_codigo()` implementado — era un stub que regresaba
  `null` desde la Pasada 1. `bin2hex(random_bytes(16))`, no
  `random_password()` (usa `rand()`, no criptográficamente seguro, no apto
  para una credencial de acceso), con verificación de colisión contra
  `by_codigo()`.

### 1.5 `guiaModel.php`
- **Pasada 1:** creado con `min_trabajadores`/`max_trabajadores` y columnas
  `umbrales_json`/`umbrales_dominio_json`/`umbrales_categoria_json`.
- **Pasada 3:** se corrigió a `trabajadores_min`/`trabajadores_max` (los
  nombres reales) — **por qué:** `por_numero_trabajadores()` filtraba por
  columnas que no existen en `ddl.sql`, hubiera fallado con "Unknown column".
  Se quitaron los `umbrales_*_json`: **por qué:** los umbrales no son JSON en
  `ddl.sql`, viven normalizados en la tabla `umbral` (que todavía no tiene
  modelo, ver sección 9). Se agregó `by_clave()` y se cambió la firma de
  `nivel_de_riesgo()` para reflejar que ahora depende de la tabla `umbral`.
- **Pasada 4:** se resolvió el límite (16, ver 1.3) y se reforzó el docblock
  de `por_numero_trabajadores()`: para `<= 15` regresa `null` **a propósito**
  (no requiere cuestionario), con un TODO explícito de que el controlador
  que lo consuma debe mostrar un mensaje informativo distinto al genérico de
  error. **Por qué:** evitar que en la fase de Desarrollo alguien trate ese
  `null` como un caso de "no encontrado"/error.
- **Pasada 6:** `nivel_de_riesgo()` dejó de ser un TODO — ahora delega en
  `umbralModel::nivel_final()`/`nivel_categoria()`/`nivel_dominio()` según
  `$nivelAgregacion`. Se quitaron las 2 referencias a "`umbralModel` —
  pendiente de scaffolding" del docblock de la clase (ya existe, ver 1.12).

### 1.6 `reactivoModel.php`
- **Pasada 1:** creado con `dominio`/`categoria` como texto libre (`VARCHAR`)
  y `es_condicional` (booleano) + `pregunta_filtro` (texto).
- **Pasada 3:** reescrito para reflejar que `ddl.sql` normaliza
  dominio/categoría/dimensión/pregunta-filtro en tablas propias
  (`categoria`, `dominio`, `dimension`, `pregunta_filtro`) con FK
  denormalizadas (`dominio_id`, `categoria_id`) directamente en `reactivo`.
  Se corrigieron `obligatorios_por_guia()` (filtraba por `es_condicional`,
  columna que no existe → ahora filtra por `pregunta_filtro_id IS NULL`) y
  `condicionales_por_filtro()` (filtraba por una columna `pregunta_filtro`
  de texto que no existe → ahora recibe `$preguntaFiltroId` y filtra por
  `pregunta_filtro_id`). **Por qué:** ambos métodos tal cual estaban habrían
  lanzado error de SQL contra el esquema real. Se agregó
  `calcular_puntaje($reactivo, $posicion)`: **por qué:** centralizar en un
  solo lugar la fórmula de puntaje (normal = `4 - posicion`, invertida =
  `posicion`) que estaba implícita en `opcion_respuesta.posicion` + la
  notación "(4→0)"/"(0→4)" de `docs/norma/Transcripcion_GuiaIII_NOM-035.md`,
  para no duplicarla entre `respuestaModel` y `resultadoModel`.
- **Pasada 9:** se agregaron `obligatorios_por_guia_con_categoria($guiaId)` y
  `condicionales_por_filtro_con_categoria($preguntaFiltroId)` — mismos JOIN
  que `obligatorios_por_guia()`/`condicionales_por_filtro()` pero agregando
  `categoria.nombre` (alias `categoria_nombre`). **Por qué:**
  `cuestionarioController::responder()` necesitaba el nombre de la categoría
  para agrupar visualmente las preguntas en la vista; no se modificaron los
  métodos existentes (los sigue usando `post_responder()`, que sólo necesita
  los ids, no el nombre) para no traer un JOIN de más donde no hace falta.

### 1.7 `opcionRespuestaModel.php`
- **Pasada 1:** creado con columnas `texto`/`valor`/`orden`.
- **Pasada 3:** corregido a `etiqueta`/`posicion` (nombres reales de
  `ddl.sql`) y se documentó explícitamente, en el docblock de la clase, que
  `posicion` es sólo el orden de despliegue (0=Siempre...4=Nunca) y **no** el
  puntaje — el puntaje depende de `reactivo.polaridad` (ver 1.6).
  **Por qué:** sin esa aclaración, alguien podría asumir que `posicion` ya
  es el puntaje final y calcular mal RF-05 para los reactivos de polaridad
  `'invertida'`.
- **Pasada 9:** sin cambios de código — `escala()` ya traía exactamente lo
  que necesitaba `cuestionarioController::responder()` (id real de cada
  opción, usado como `value` del radio en la vista, no un id 1-5 inventado).

### 1.8 `aplicacionModel.php`
- **Pasada 1:** creado con `centro_trabajo_id` (columna propia),
  `nombre_encuestado`, `filtro_servicio_clientes`/`filtro_jefe_trabajadores`,
  `fecha_inicio`/`fecha_envio`, estado `'completado'`.
- **Pasada 2 (decisión B):** se agregó `existe_para_token_y_servidor_publico()`
  para la restricción "una sola aplicación por (token_id,
  numero_servidor_publico)".
- **Pasada 3:** corrección grande contra `ddl.sql`:
  - `aplicacion` **no tiene** `centro_trabajo_id` propio (sólo `token_id`).
    Se agregó `centro_trabajo_id_de($aplicacionId)` con `INNER JOIN token` y
    se reescribió `por_centro_trabajo()` con el mismo JOIN.
    **Por qué:** `resultadosController::individual()`/`pdf_individual()` y
    `resultadoModel::agregado_por_centro_trabajo()` (ver 1.9) dependían de una
    columna que no existe; sin este fix, esas rutas habrían fallado con error
    de SQL en cuanto hubiera datos reales.
  - `nombre_encuestado` → `nombre`, `filtro_servicio_clientes`/`filtro_jefe_trabajadores`
    → `atiende_clientes`/`es_jefe`, `estado='completado'` → `'completada'`
    (femenino, coincide con el ENUM real), se quitó `fecha_envio`
    (no existe; se usa `updated_at` implícitamente). **Por qué:** son los
    nombres/valores reales de `ddl.sql`; el anterior `'completado'` habría
    fallado contra el `ENUM('en_progreso','completada')` real.
- **Pasada 9:** sin cambios de código — `insertOne()`,
  `existe_para_token_y_servidor_publico()` y `update_by_id()` (para marcar
  `estado='completada'` + `atiende_clientes`/`es_jefe` en un solo UPDATE) ya
  cubrían todo lo que `cuestionarioController` necesitaba. Verificado con
  datos reales, ver sección 16.2.

### 1.9 `resultadoModel.php`
- **Pasada 1:** creado con `calificacion_final DECIMAL(6,2)` y columnas
  `desglose_dominio_json`/`desglose_categoria_json`.
- **Pasada 3:** `calificacion_final` corregido a `INT UNSIGNED` (los umbrales
  de la norma en `docs/norma/Transcripcion_GuiaIII_NOM-035.md` sección 3 son
  enteros, nunca decimales). Se quitaron los `desglose_*_json`: **por qué**
  `ddl.sql` normaliza ese desglose en la tabla hija `resultado_detalle`, no
  como JSON. `agregado_por_centro_trabajo()` se corrigió para hacer doble
  JOIN (`resultado → aplicacion → token`) en vez de un JOIN directo a una
  columna `aplicacion.centro_trabajo_id` que no existe (mismo motivo que 1.8).
- **Pasada 6:** `calcular_para_aplicacion()` implementado de verdad (dejó de
  regresar `null`). Ver el detalle completo en la sección 12 (es el cambio
  central de esta pasada, con el hallazgo de la transacción colgada de
  `Db::query()`, la persistencia atómica e idempotente, y por qué no se usa
  `resultadoDetalleModel::insertOne()` dentro de esa transacción manual
  (usaría `transaction=true` por default y la cerraría antes de tiempo).

### 1.10 `respuestaModel.php`
- **Pasada 1:** creado con columna `valor TINYINT` (puntaje ya calculado).
- **Pasada 3:** se quitó `valor` del esquema — **por qué:** `ddl.sql` no
  tiene esa columna en `respuesta` (sólo `aplicacion_id, reactivo_id,
  opcion_respuesta_id`); el puntaje se deriva en el momento con
  `reactivoModel::calcular_puntaje()`, no se persiste. `por_aplicacion()` se
  reescribió para hacer JOIN con `reactivo` y `opcion_respuesta` y traer ya
  `polaridad`, `dominio_id`, `categoria_id`, `posicion` en la misma consulta
  — **por qué:** es el insumo directo que necesitará
  `resultadoModel::calcular_para_aplicacion()` en la fase de Desarrollo.
- **Pasada 6:** sin cambios — `por_aplicacion()` ya traía exactamente lo que
  el motor de calificación necesitaba, confirmado al usarlo de verdad.
- **Pasada 9:** sin cambios de código — `insertar_lote()` (bucle de
  `insertOne()`) fue suficiente para persistir las respuestas capturadas por
  el flujo real; el TODO de envolverlo en una transacción sigue pendiente
  (no se tocó, está fuera del alcance pedido en esta pasada).

### 1.11 `auditoriaModel.php`
- **Pasada 1:** creado con columnas `ip VARCHAR(45)` y `creado DATETIME`.
- **Pasada 3:** se quitaron del esquema documentado — **por qué:** `ddl.sql`
  no tiene columna `ip`, y la de fecha se llama `created_at` (con
  `DEFAULT CURRENT_TIMESTAMP`, no hace falta enviarla). Ver también el
  cambio correspondiente en `registrar_auditoria()` (sección 4).
- **Pasada 4:** `ip` volvió al esquema documentado, ahora sí respaldada por
  una columna real en `ddl.sql` (ver sección 7). **Por qué:** el segundo
  agente recomendó agregarla — sistema con datos identificados/sensibles,
  trazabilidad forense barata de hacer ahora y cara de reconstruir después;
  el usuario confirmó la recomendación.
- **Pasada 7:** se agregó `por_secretaria($secretariaId)` (JOIN `auditoria`
  → `usuario` → `bee_users`) — **por qué:** `superusuarioController::bitacora()`
  necesita mostrar "quién" (el `username`) y filtrar por la secretaría del
  súper usuario en sesión, no toda la bitácora del sistema (alcance por
  secretaría).

### 1.12 `categoriaModel.php` (nuevo, Pasada 6)
Sólo lectura: `by_id`, `por_guia($guiaId)`. **Por qué mínimo:** el motor de
calificación no lo necesita (usa `reactivo.categoria_id` denormalizado);
existe para nombrar renglones de reportes futuros, como pide el handoff §5.1.

### 1.13 `dominioModel.php` (nuevo, Pasada 6)
Igual que 1.12 pero para dominios: `by_id`, `por_categoria($categoriaId)`.

### 1.14 `umbralModel.php` (nuevo, Pasada 6)
`nivel_final($guiaId, $calificacion)`, `nivel_categoria($categoriaId, $calificacion)`,
`nivel_dominio($dominioId, $calificacion)` — los tres delegan en un método
privado compartido (`buscar_nivel()`) para no repetir 3 veces la misma
consulta con sólo el nombre de columna distinto. Es la única fuente de
verdad de la traducción calificación→nivel de riesgo; nadie más en el
código debe reconstruir esos rangos.

### 1.15 `resultadoDetalleModel.php` (nuevo, Pasada 6)
CRUD mínimo: `insertOne`, `by_id`, `por_resultado($resultadoId)`. **Nota:**
`resultadoModel::calcular_para_aplicacion()` NO usa `insertOne()` de este
modelo dentro de su transacción manual — ver 1.9 y sección 12.2 para el
porqué (usaría `transaction=true` por default y comitearía a medio camino).

### 1.16 `preguntaFiltroModel.php` (nuevo, Pasada 9)
Sólo lectura: `by_id`, `por_guia($guiaId)` (las dos preguntas-filtro de una
guía, ordenadas por `orden`). **Por qué `orden` y no una columna de "tipo"
separada:** `docs/DDL/ddl.sql` documenta `orden` como la clave semántica
(1=clientes, 2=jefe) — no hay una columna adicional que lo distinga, así que
el modelo no inventa una; `cuestionarioController` decide "es la pregunta de
clientes" con `(int) $filtro['orden'] === 1`, igual que hace el propio DDL.

---

## 2. Controladores (`app/controllers/`)

### 2.1 `cuestionarioController.php` (público, sin autenticación de Bee)
- **Pasada 1:** creado con `index()`, `post_acceso()`, `responder()`,
  `post_responder()`, `gracias()`. CSRF ya incluido desde el inicio
  (`Csrf::validate($_POST['csrf'] ?? '')`, reutilizando `insert_inputs()` de
  las vistas) — **por qué:** el propio Bee ya trae ese mecanismo nativo, no
  había necesidad de inventar uno nuevo (esto se confirmó explícitamente en
  la Pasada 2, sin cambios de código, sólo se documentó).
- **Pasada 2:** se agregó `requiere_metodo_post()` al inicio de `post_acceso()`
  y `post_responder()` — **por qué:** Bee ejecuta el método de la URL sin
  importar el verbo HTTP; sin este guard, visitar esas rutas por GET
  ejecutaría la lógica de todos modos.
- **Pasada 3:** sin cambios de código; se agregó un TODO señalando
  `aplicacionModel::existe_para_token_y_servidor_publico()` en `post_acceso()`.
- **Pasada 5:** se amplió el TODO de `post_acceso()` con la cadena exacta de
  llamadas para el guard de ≤15 trabajadores como defensa en profundidad
  (`tokenModel::by_codigo()` → `centroTrabajoModel::by_id()` →
  `guiaModel::por_numero_trabajadores()`), sin implementarlo — **por qué:**
  implementarlo de verdad requeriría además resolver token→centro de
  trabajo, que sigue sin existir (`tokenModel::esta_vigente()` aún es TODO);
  eso ya sería lógica nueva, fuera del alcance de este ajuste puntual.
- **Pasada 9:** implementación real completa de las 5 rutas. `index()` sin
  cambios (sólo el formulario de acceso). `post_acceso()`: valida
  `tokenModel::esta_vigente()`, el guard ≤15 (defensa en profundidad, ya no
  un TODO), `aplicacionModel::existe_para_token_y_servidor_publico()`, crea
  la `aplicacion` (`estado='en_progreso'`) y guarda su id en
  `$_SESSION['cuestionario']` (sesión NATIVA de PHP, no Bee/Auth — el
  encuestado no tiene cuenta; ver docblock de la clase). `responder($token)`:
  carga los reactivos obligatorios de la guía congelada en la aplicación
  (`aplicacionModel.guia_id`) más los reactivos condicionales de las dos
  preguntas-filtro (`preguntaFiltroModel::por_guia()`, ver 1.16), y la escala
  Likert real. `post_responder()`: determina el set exacto de reactivos que
  deben responderse según lo que se contestó en las preguntas-filtro, valida
  que TODOS tengan una opción real seleccionada (RF-04), inserta las
  respuestas, marca la aplicación `'completada'` y dispara
  `resultadoModel::calcular_para_aplicacion()` en el mismo momento (RNF-06).
  `gracias()` sin cambios — a propósito no muestra el resultado (es para
  administrador/súper usuario, no para el encuestado). Se agregó el método
  privado `aplicacionEnCurso($token)` (mismo patrón que
  `resultadosController::verificarAccesoCentroTrabajo()`) para no duplicar
  la revalidación de sesión+token+estado entre `responder()` y
  `post_responder()`. **Nota sobre `sanitize_input()`:** a propósito NO se
  usa `array_map('sanitize_input', $_POST)` en `post_responder()` — a
  diferencia de los demás controladores, aquí `$_POST['respuestas']` es un
  arreglo anidado y `sanitize_input()`/`trim()` espera un string;
  aplicarlo tal cual habría producido un `TypeError` fatal en PHP 8.2 en
  cuanto alguien enviara el formulario. Verificado end-to-end con datos
  reales, ver sección 16.2.

### 2.2 `administradorController.php` (rol `administrador`)
- **Pasada 1:** creado con `index()`, `centros_trabajo()`/`post_centros_trabajo()`,
  `tokens()`/`post_tokens()`, `habilitar()`, más `editar_centro_trabajo()`,
  `borrar_centro_trabajo()`, `revocar_token()`. Guard inicial: sólo
  `Auth::validate()`.
- **Pasada 2:** el guard del constructor cambió a `requiere_rol('administrador')`
  — **por qué:** `Auth::validate()` sólo comprueba que exista sesión, no el
  rol; sin esto, cualquier usuario de Bee autenticado (aunque fuera
  `superusuario`) podía entrar a este panel. Se agregó `requiere_metodo_post()`
  a `post_centros_trabajo()` y `post_tokens()` — **por qué:** son los únicos
  dos métodos `post_*` reales del controlador (los otros —
  `borrar_centro_trabajo`, `revocar_token`, `habilitar` — no llevan el
  prefijo `post_` y se dejaron con el patrón nativo de Bee de enlace GET +
  `Csrf::validate($_GET['_t'])`, igual que `adminController::borrar_usuario()`,
  para no inventar una convención nueva).
- **Pasada 3:** `check_posted_data(['nombre', 'numero_trabajadores'], ...)` →
  `['nombre', 'num_trabajadores']` — **por qué:** el campo del formulario y
  la columna real de `ddl.sql` se llaman `num_trabajadores` (ver 1.3); con el
  nombre viejo, el POST nunca habría llenado la columna correcta.
- **Pasada 4:** el TODO de `post_centros_trabajo()` se amplió para señalar
  que `guiaModel::por_numero_trabajadores()` puede regresar `null` para
  `num_trabajadores <= 15` sin que sea un error, y que ese caso debe
  mostrarse como "no requiere cuestionario" en vez del mensaje genérico de
  "pendiente de implementación" — **por qué:** para que quien implemente
  esto en la fase de Desarrollo no lo trate como una excepción.
- **Pasada 5:** ese TODO se convirtió en código real. `post_centros_trabajo()`
  ahora resuelve `guiaModel::por_numero_trabajadores((int) $_POST['num_trabajadores'])`
  y, si regresa `null`, corta el flujo con `Flasher::error(...)` +
  `Redirect::back()` antes de llegar al bloque de alta (que sigue siendo
  TODO). **Por qué:** el prompt pedía explícitamente un guard funcional, no
  sólo un comentario — y éste es el único punto del sistema donde
  `num_trabajadores` llega como dato crudo, sin depender de tablas que
  todavía no existen.
- **Pasada 8:** implementación completa (ver sección 14.2 para el detalle).
  `centros_trabajo()`/`post_centros_trabajo()` reales (secretaria/administrador
  siempre de la sesión, validación de entero positivo). `tokens()`/`post_tokens()`/
  `revocar_token()` reales, con alcance por `administrador_id` (no basta
  compartir secretaría) y validación de fechas (`fecha_fin >= fecha_inicio`,
  campos nuevos en el formulario). `borrar_centro_trabajo()` y `habilitar()`
  quedan igual — no los pidió esta tarea. Verificado end-to-end con `curl`,
  incluido el caso de alcance más exigente (dos administradores de la misma
  secretaría).
- **Pasada 10:** `tokens()` ahora también carga
  `aplicacionModel::por_centro_trabajo($centroTrabajoId)` (ya filtraba
  `estado='completada'`, sin cambios en el modelo) y la pasa a la vista como
  `aplicaciones` — cierra el lazo visual pedido: de la lista de tokens de un
  centro se llega a las aplicaciones respondidas y de ahí a su resultado
  individual (ver `tokensView.php` en la sección 3 y `resultadosController::individual()`
  en 2.4).

### 2.3 `superusuarioController.php` (rol `superusuario`)
- **Pasada 1:** creado con `index()`, `administradores()`/`post_administradores()`,
  `bitacora()`, `editar_administrador()`, `borrar_administrador()`. Guard
  inicial: sólo `Auth::validate()`.
- **Pasada 2:** guard cambiado a `requiere_rol('superusuario')` (misma razón
  que 2.2). `requiere_metodo_post()` agregado a `post_administradores()`
  (único método `post_*` real; `borrar_administrador()` se dejó con el
  patrón GET + CSRF por query string, misma razón que en 2.2).
- **Pasada 3:** sin cambios de código.
- **Pasada 7:** implementación real de `administradores()`, `post_administradores()`,
  `borrar_administrador()` y `bitacora()` (ver sección 13.2 para el detalle
  completo, incluido el hallazgo de `ON DELETE RESTRICT` que cambió
  `borrar_administrador()` de "borrar" a "revocar"). `editar_administrador()`
  queda igual, no era parte de esta tarea. Verificado end-to-end con `curl`
  contra el servidor local (sección 13.3), no sólo con `php -l`.
- **Pasada 8:** `administradores()` acepta filtro `?estado=` (con
  degradación a "sin filtro" si la columna no existe todavía).
  `borrar_administrador()` ahora también marca `estado='inactivo'` (en un
  `try/catch` propio que no rompe si la columna no existe) — ver
  sección 14.1.
- **Nota aparte (no relacionada con Claude):** el usuario detectó una copia
  accidental de este archivo en `docs/superusuarioController.php` (agregada
  en el commit `e73100a`, probablemente por un `git add` demasiado amplio) y
  ya la borró tras confirmar que la versión real en `app/controllers/`
  estaba intacta. Claude no tocó esa ruta en ningún momento.

### 2.4 `resultadosController.php` (comparte roles `administrador`/`superusuario`)
- **Pasada 1:** creado con `individual()`, `agregado()`, `tablero()`,
  `datos_grafica()`, `pdf_individual()`, `pdf_agregado()`, `excel()`. Guard
  inicial: sólo `Auth::validate()`.
- **Pasada 2:** se reescribió el guard: en vez de `requiere_rol()` (que
  exigiría un único rol y bloquearía a uno de los dos), se agregaron los
  métodos privados `centrosTrabajoPermitidos()` (resuelve qué
  `centro_trabajo.id` puede ver el usuario en sesión según su rol) y
  `verificarAccesoCentroTrabajo()` (deniega si el ID solicitado no está en
  ese conjunto, "falla cerrado" si es `null`). **Por qué:** es el único
  controlador pensado para ambos roles a la vez, con distinto alcance de
  datos cada uno — un guard de rol único no aplicaba aquí.
- **Pasada 3:** `individual()` y `pdf_individual()` usaban
  `aplicacionModel::by_id($aplicacionId)['centro_trabajo_id']` para resolver
  el alcance — se cambiaron a `aplicacionModel::centro_trabajo_id_de($aplicacionId)`
  (ver 1.8). **Por qué:** la columna `centro_trabajo_id` no existe en
  `aplicacion`; el código viejo habría lanzado un error de SQL ("Unknown
  column") en cuanto se ejecutara contra la base de datos real.
- **Pasada 10:** `individual()` implementado de verdad — carga
  `aplicacionModel::by_id()`, `guiaModel::by_id()`,
  `resultadoModel::por_aplicacion()` y `resultadoDetalleModel::por_resultado()`
  (nombrando cada renglón con `categoriaModel::by_id()`/`dominioModel::by_id()`),
  registra `consulta_resultado_individual` en la bitácora, y calcula un
  `volver_url` según el rol (administrador → `administrador/tokens/{centro}`;
  súper usuario → su propio tablero, no tiene todavía una página por centro).
  `verificarAccesoCentroTrabajo()` y el guard de "sin rol" del constructor
  dejaron de redirigir a `DEFAULT_CONTROLLER` (el formulario público del
  encuestado) y ahora usan `ruta_tablero_segun_rol()` — **por qué:** un
  usuario con sesión de Bee activa que pide un resultado fuera de su alcance
  no debería terminar expulsado al flujo público, sino de vuelta en su
  propio tablero (ver sección 17.1).

### 2.5 `loginController.php` (nativo de Bee, sin tocar hasta esta pasada)
- **Pasada 10:** `post_login()` y el guard del constructor ("ya hay sesión
  abierta") dejaron de redirigir siempre a `admin`/`admin/perfil` (el panel
  demo nativo de Bee) — ahora van al tablero real según el rol de contexto.
  **Hallazgo real durante la implementación:** `ruta_tablero_segun_rol()` no
  sirve dentro de `post_login()` porque depende de `get_user()`, que lee el
  global `$Bee_User` — y Bee sólo llena ese global UNA VEZ por petición,
  ANTES de despachar el controlador (`Bee::init_authentication()`); un login
  que ocurre a la mitad de esa misma petición no lo actualiza, así que
  `obtener_usuario_actual()` seguía viendo "sin sesión" inmediatamente
  después de `Auth::login()` y el redirect caía siempre al `default` ('admin').
  Se verificó el síntoma en vivo con `curl` (el súper usuario de prueba
  terminaba en `/admin` en vez de `/superusuario`) antes de diagnosticar la
  causa. Se resolvió agregando `ruta_tablero_para_rol($rol)` (variante
  parametrizada, ver sección 4) y resolviendo el rol directamente contra
  `usuarioModel::by_bee_user_id($user['id'])` en `post_login()`, sin pasar
  por el global. Ver sección 17.1 para el detalle completo.

---

## 3. Vistas (`templates/views/`)

Las 15 vistas (`cuestionario/`, `administrador/`, `superusuario/`,
`resultados/`) se crearon completas en la **Pasada 1**, reutilizando los
layouts existentes de Bee (`templates/includes/header.php`/`navbar.php`/`footer.php`
para el flujo público; `templates/includes/admin/dashboardTop.php`/`dashboardBottom.php`
para los paneles, igual que `usuariosView.php`). Ninguna se reescribió por
completo después; los únicos ajustes fueron:

- **`administrador/tokensView.php`** (Pasada 2): la columna de la tabla decía
  "Token", se cambió a "Código" — **por qué:** decisión de diseño B, el
  campo real se llama `codigo`, no `token`.
- **`administrador/centrosTrabajoView.php`** (Pasada 3): el `<input>` del
  formulario tenía `name="numero_trabajadores"`, se cambió a
  `name="num_trabajadores"` (con comentario inline explicando por qué) —
  misma razón que 2.2: debe coincidir con la columna real y con
  `check_posted_data()` del controlador.
- **`administrador/centrosTrabajoView.php`** (Pasada 4): el `<input>` tenía
  `min="15"` (heredado de la duda 15 vs 16); se cambió a `min="1"` y el texto
  de ayuda se actualizó a "16 a 50 ... Guía II; ... 15 o menos no requiere
  cuestionario". **Por qué:** con el límite ya confirmado en 16, un centro de
  15 trabajadores o menos es un dato **válido** (sólo que exento de
  cuestionario) y no debía bloquearse en el formulario.
- **`superusuario/administradoresView.php`** (Pasada 7): la tabla dejó de
  mostrar "Sin registros (pendiente)" fijo — ahora hace `foreach` real sobre
  `$d->administradores` (username, email, alta) y agrega el enlace "Revocar
  acceso" (patrón GET + `CSRF_TOKEN` en query string, igual que
  `adminController::borrar_usuario()`). Se quitó la columna "Centros de
  trabajo" que había quedado como suposición en la Pasada 1 — el método del
  modelo no regresa ese dato y no se pidió en esta tarea.
- **`superusuario/bitacoraView.php`** (Pasada 7): mismo cambio — `foreach`
  real sobre `$d->bitacora` (usuario, acción, entidad + `entidad_id`,
  detalle, IP, fecha) en vez del placeholder.
- **`superusuario/administradoresView.php`** (Pasada 8): columna "Estado"
  (badge verde/gris) + 3 enlaces de filtro (Todos/Activos/Inactivos);
  `$admin->estado ?? 'activo'` para no romper si la columna todavía no
  existe (ver sección 14.1).
- **`administrador/centrosTrabajoView.php`** (Pasada 8): de placeholder a
  `foreach` real sobre `$d->centros`, con badge de la guía asignada
  (`GRII`/`GRIII`) y enlace a "Tokens". Se quitó la columna "Tokens" que
  sólo mostraba un botón sin contar nada — se dejó como acción, no como dato.
- **`administrador/tokensView.php`** (Pasada 8): de placeholder a `foreach`
  real sobre `$d->tokens`, con badge de estado y enlace de revocar. Se
  agregaron los campos `fecha_inicio`/`fecha_fin` al formulario (antes no
  existían, las fechas se hubieran tenido que inventar en el controlador
  sin que el administrador pudiera elegirlas).
- **`cuestionario/cuestionarioView.php`** (Pasada 9): la vista traía datos de
  ejemplo "quemados" (5 preguntas y una escala Likert inventadas por el
  diseño de la Pasada 1, más 2 reactivos de filtro ficticios `f1_1`/`f2_1`)
  para poder validar el diseño antes de que existiera el controlador real.
  Se reemplazaron por `$d->reactivos`/`$d->opciones`/`$d->filtros` reales —
  **por qué:** con datos de ejemplo, el formulario habría podido "verse"
  bien pero enviaría ids de reactivo/opción que no existen en la base de
  datos, y `post_responder()` los habría rechazado a todos. El bloque de
  preguntas-filtro pasó de estar duplicado a mano (uno para clientes, otro
  para jefe) a un solo `foreach ($d->filtros as $bloque)` genérico que
  arma el `name`/`id` del campo según `$filtro->orden` — **por qué:** cada
  guía tiene su propio conjunto real de reactivos condicionales (3+3 en
  GRII, 4+4 en GRIII, ver `docs/DDL/ddl.sql`), no uno solo como mostraba el
  ejemplo. Si `$d->reactivos` llega vacío ya no se rellena con preguntas de
  relleno: se muestra un estado vacío explícito.
- **`cuestionario/accesoView.php`, `cuestionario/agradecimientoView.php`**
  (Pasada 9): sin cambios — ya capturaban/mostraban exactamente lo que pide
  el flujo real (token+nombre+numero_servidor_publico; confirmación sin
  mostrar el resultado).
- **`templates/includes/admin/sidebar.php`** (Pasada 10): dejó de ser el
  catálogo genérico de Bee (Creator, Componentes, Usuarios, Productos,
  Addons) mostrado tal cual a cualquier usuario del panel admin, y ahora
  rama por `obtener_rol_usuario_actual()`: administrador ve Dashboard +
  Centros de trabajo + Resultados; súper usuario ve Dashboard +
  Administradores + Bitácora + Resultados; una cuenta sin rol de contexto
  (ej. el usuario demo nativo "bee") sigue viendo el catálogo original sin
  cambios. **Por qué era necesario:** antes de este cambio, el panel de
  administrador/súper usuario no tenía NINGÚN enlace a sus propias
  funcionalidades reales — sólo eran alcanzables tecleando la URL a mano.
  También se corrigió el enlace del logo (antes iba siempre a
  `DEFAULT_CONTROLLER`, el formulario público, incluso con sesión de
  admin/súper usuario activa) para que vaya al tablero del rol en sesión.
- **`templates/views/login/loginView.php`** (Pasada 10): se quitaron los
  hints "Ingresa bee"/"Ingresa 123456" (credenciales del demo nativo de Bee,
  ya no válidas — este sistema usa cuentas reales) y los enlaces
  "¿Olvidaste tu contraseña?" (apuntaba a sí mismo, no existe flujo de
  recuperación) y "Crear nueva cuenta" (`bee/generate-user`, auto-registro
  que no encaja con este sistema — las cuentas se dan de alta desde
  bootstrap/súper usuario, nunca por el usuario final). Se agregó un enlace
  secundario de vuelta al acceso del encuestado.
- **`templates/views/cuestionario/accesoView.php`** (Pasada 10): se agregó
  un enlace discreto "Acceso administrativo" hacia `/login` — **por qué:**
  la raíz del sitio (formulario del encuestado) no tenía ningún punto de
  entrada visible hacia el login administrativo; se mantiene deliberadamente
  secundario/pequeño para "no mezclar los accesos" (instrucción explícita
  del prompt).

---

## 4. Funciones auxiliares (`app/functions/bee_custom_functions.php`)

Este archivo es uno de los dos puntos de extensión que Bee reserva para el
proyecto (el otro es `templates/includes/styles.php`, sección 5); por eso se
extendió aquí en vez de tocar `app/classes/*`.

- **Pasada 1:** se agregó `registrar_auditoria($accion, $entidad, $entidadId, $detalle)`
  — **por qué:** RF-09/RF-12/RNF-01 piden bitácora de movimientos y
  consultas; se centralizó en una función en vez de repetir el `insertOne()`
  en cada controlador.
- **Pasada 2:** se agregaron `obtener_usuario_actual()`, `obtener_rol_usuario_actual()`,
  `requiere_rol($rol)` y `requiere_metodo_post()` — **por qué:**
  `Auth::validate()` (núcleo de Bee) sólo valida sesión, no rol, y Bee no
  distingue verbos HTTP a nivel de enrutamiento; estas 4 funciones son el
  punto único donde se resuelven ambos huecos, consumido por los guards de
  `administradorController`/`superusuarioController`/`resultadosController`
  (secciones 2.2–2.4). `registrar_auditoria()` se ajustó para usar
  `obtener_usuario_actual()['id']` (el id de `usuarioModel`, tabla de
  enlace) en vez de `get_user('id')` (id nativo de `bee_users`) — **por
  qué:** todas las FK del esquema (`centro_trabajo.administrador_id`, etc.)
  apuntan a `usuario.id`, no a `bee_users.id`; usar el id equivocado habría
  guardado bitácoras con una referencia inconsistente.
- **Pasada 3:** dos correcciones más a `registrar_auditoria()`:
  1. Se quitaron las claves `'ip'` y `'creado'` del arreglo de inserción —
     **por qué:** `ddl.sql` no tiene columna `ip`, y la de fecha se llama
     `created_at` con `DEFAULT CURRENT_TIMESTAMP` (enviar `'creado'` habría
     causado "Unknown column" al insertar).
  2. Se agregó un guard: si `obtener_usuario_actual()` no devuelve un `id`
     (sesión sin enlace en `usuarioModel`), la función regresa `false` sin
     intentar el `insertOne()` — **por qué:** `auditoria.usuario_id` es
     `NOT NULL` en `ddl.sql`; sin este guard, ese caso límite habría lanzado
     una violación de restricción en vez de fallar de forma controlada.
     También se quitó el valor por defecto `null` del parámetro `$entidad`
     (ahora es obligatorio) — **por qué:** `auditoria.entidad` es `NOT NULL`.
- **Pasada 4:** volvió la clave `'ip' => get_user_ip()` al arreglo de
  inserción — **por qué:** ahora sí existe la columna real (ver sección 7),
  tras la recomendación del segundo agente sobre trazabilidad forense.
- **Pasada 5:** `get_user_ip()` se reemplazó por `$_SERVER['REMOTE_ADDR'] ?? null`
  — **por qué:** `get_user_ip()` confía primero en `HTTP_CLIENT_IP`/
  `HTTP_X_FORWARDED_FOR`/`HTTP_X_FORWARDED` (cabeceras que el cliente puede
  falsificar sin que haya un proxy real de por medio) antes de caer a
  `REMOTE_ADDR`; para una bitácora forense eso es contraproducente. Se
  agregó un `TODO` extenso sobre cómo manejar esto correctamente el día que
  el sistema quede detrás de un proxy real (resolver `X-Forwarded-For` sólo
  si la petición viene de una lista cerrada de proxies de confianza). La IP
  sigue sin ser parámetro de la función y sigue siendo opcional (`null` si
  no está disponible, la columna admite `NULL`); el guard de `usuario_id`
  de la Pasada 3 no se tocó.
- **Pasada 10:** se agregaron `ruta_tablero_para_rol($rol)` y
  `ruta_tablero_segun_rol()` (ver docblocks en el archivo para el porqué de
  tener las dos variantes — resume la sección 17.1: `ruta_tablero_segun_rol()`
  no sirve dentro de `loginController::post_login()` por el global `$Bee_User`
  que sólo se llena una vez por petición, antes del login). Se modificó
  `requiere_rol()`: cuando el rol NO coincide, ahora redirige a
  `ruta_tablero_segun_rol()` en vez de `DEFAULT_CONTROLLER` — **por qué:**
  expulsar al formulario público a alguien con una sesión de Bee activa
  pero el rol equivocado es una pérdida de contexto de navegación
  innecesaria; lo correcto es devolverlo a su propio tablero.

---

## 5. Include de estilos (`templates/includes/styles.php`)

- **Pasada 1:** se agregó un `<link>` a `assets/css/nom035-variables.css`
  (ver sección 6), inmediatamente debajo del que carga `main.css`, en la
  línea que el propio archivo marca con el comentario "Estilos
  personalizados deben ir en main.css o abajo de esta línea". **Por qué:**
  es el punto de extensión que Bee ya designa para esto; no había necesidad
  de crear un mecanismo de carga de CSS alterno.
- Sin cambios en las pasadas 2 y 3.

---

## 6. Identidad visual (`assets/css/nom035-variables.css`)

- **Pasada 1:** archivo nuevo con los custom properties de CSS pedidos
  explícitamente por el usuario: `--color-primario`, `--color-texto`,
  `--color-cafe`, `--color-oro`, `--color-arena`, `--font-titulos`,
  `--font-cuerpo`. Sin cambios en pasadas posteriores.

---

## 7. El DDL (`docs/DDL/ddl.sql`)

Este archivo no lo creó Claude — lo aportó el usuario en la Pasada 3 como
insumo a verificar. El único cambio que Claude aplicó, **con autorización
explícita del usuario** (no unilateral), fue:

- **`usuario.bee_user_id`**: `INT UNSIGNED` → `INT` (firmado), + comentario
  explicando el motivo en la misma línea y en el Bloque C. **Por qué:**
  `db_beeframework.sql` (núcleo de Bee) declara `bee_users.id` como
  `int(11)` **firmado**, sin `UNSIGNED`. MySQL/MariaDB exige que ambos lados
  de una FK tengan el mismo tipo; con `UNSIGNED` en un lado y firmado en el
  otro, el `ALTER TABLE usuario ADD CONSTRAINT fk_usuario_bee_user ...` del
  Bloque C habría fallado con el error 1215 ("Cannot add foreign key
  constraint") en cuanto alguien intentara aplicar el DDL completo.

Otros 3 hallazgos sobre este archivo se reportaron pero en ese momento **no**
se aplicaron (el usuario decidió cada uno explícitamente):
- `aplicacion` sin `centro_trabajo_id` propio → el usuario eligió dejarlo
  como JOIN en el código (sección 1.8), sin tocar el DDL. **Se mantiene así.**
- Límite de trabajadores para Guía II (`trabajadores_min` = 15 vs 16) →
  el usuario pidió revisar el texto oficial de la norma antes de decidir;
  en ese momento **ninguno** de los dos documentos se tocó.
- Columna `ip` en `auditoria` → quedó como recomendación, no se agregó en
  ese momento.

**Pasada 4 (segunda revisión, con autorización explícita del usuario en
ambos casos):**
- Se agregó `ip VARCHAR(45) NULL` a la tabla `auditoria` — **por qué:** el
  segundo agente recomendó agregarla por trazabilidad forense sobre datos
  identificados/sensibles (RNF-01); el usuario confirmó la recomendación.
- El límite de trabajadores **no se tocó en `ddl.sql`** — ya decía `16` y
  eso fue justamente lo que se confirmó como correcto contra el texto
  oficial de la norma; el cambio de "15" a "16" se aplicó en
  `Planificacion_Proyecto_Cuestionario_NOM-035 (1-2).md` (que sí tenía el
  valor equivocado) y en el código (`centroTrabajoModel::determinar_guia()`,
  ver 1.3), no en el DDL.

**Pasada 8 (tercera modificación):**
- Se agregó `estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo'`
  a la tabla `usuario`, justo después de `secretaria_id`. **Por qué:**
  cierra la limitación documentada en la Pasada 7 — `borrar_administrador()`
  no podía usar `DELETE` por `auditoria.usuario_id ON DELETE RESTRICT`, así
  que la "baja" quedaba invisible en el listado (ver sección 14.1).
- A diferencia de la Pasada 4, este cambio **no se aplicó contra ninguna
  base de datos**, ni siquiera la local de desarrollo — el usuario pidió
  explícitamente el `ALTER TABLE` para correrlo él mismo en phpMyAdmin. Se
  entregó como `scripts/alter_usuario_estado.sql` (mismo patrón que
  `scripts/bootstrap_superusuario.sql` de la Pasada 7: un `.sql` standalone,
  documentado, listo para ejecutarse fuera de Claude). Ver sección 14.1 y
  15 (nota de transparencia).

**Nota post-Pasada 10 (2026-09-25) — `scripts/seed_instrumento.sql` (nuevo):**
detectado al ayudar a configurar el entorno de una compañera de equipo: el
esquema (`ddl.sql`) y el contenido real del instrumento (2 guías, 9
categorías, 18 dominios, 45 dimensiones, 4 preguntas-filtro, 5 opciones de
respuesta, 118 reactivos, 145 umbrales — 346 filas) NUNCA se habían dejado
como un archivo reproducible en el repo; el handoff sólo decía "el DDL y el
seed ya se ejecutaron" (sección 3) dando por hecho que cualquiera que
clonara el repo ya tendría esos datos, lo cual es falso — Git no versiona
el contenido de una base de datos MySQL local. Se generó con
`mysqldump --no-create-info --complete-insert` desde la base local ya
sembrada (sólo datos, cero `CREATE`/`DROP TABLE`) para que cualquier
integrante del equipo pueda levantar un entorno funcional desde cero:
`db_beeframework.sql` (núcleo de Bee, ya existía en el repo) →
`docs/DDL/ddl.sql` (esquema) → `scripts/seed_instrumento.sql` (nuevo, el
contenido). No incluye datos de operación (cuentas, respuestas, centros de
trabajo, tokens) — sólo el catálogo del instrumento, que es igual en
cualquier entorno.

---

## 8. Documentación (`docs/ARQUITECTURA.md`, `docs/RESUMEN_ESQUELETO.md`)

Ambos archivos son nuevos (Pasada 1) y se actualizaron incrementalmente en
cada pasada para no quedar desincronizados con el código:

- **Pasada 1:** `ARQUITECTURA.md` con el layout de carpetas y la
  responsabilidad de cada módulo (secciones 1–6). `RESUMEN_ESQUELETO.md`
  con el resumen de lo construido, pedido explícitamente por el usuario al
  final de esa pasada ("crea un archivo .md con lo que has construido").
- **Pasada 2:** se agregaron las secciones 7 ("Hardening aplicado": guard de
  rol, guard de verbo HTTP, CSRF) y 8 ("Decisiones de diseño confirmadas":
  A. autenticación/roles, B. modelo de token) a `ARQUITECTURA.md`; sección 0
  agregada a `RESUMEN_ESQUELETO.md` resumiendo esa pasada.
- **Pasada 3:** se agregó la sección 10 ("Verificación contra docs/norma y
  docs/DDL") a `ARQUITECTURA.md`, con 3 subsecciones: 10.1 (lo corregido sin
  ambigüedad), 10.2 (las 4 decisiones que se le preguntaron al usuario, y su
  resolución final), 10.3 (las 6 tablas del DDL sin modelo Bee todavía —
  `categoria`, `dominio`, `dimension`, `pregunta_filtro`, `umbral`,
  `resultado_detalle` — que el usuario decidió no generar por ahora). Se
  agregó un párrafo puntero en `RESUMEN_ESQUELETO.md`.
- **Este mismo archivo** (`BITACORA_CAMBIOS.md`) es nuevo, de la Pasada 3,
  pedido explícitamente por el usuario para revisión cruzada con otro agente.
- **Pasada 4:** en `ARQUITECTURA.md` se actualizó la sección 10.2 marcando
  como **resueltos** los puntos 3 (límite de trabajadores, ahora 16
  confirmado + caso ≤15 documentado) y 4 (columna `ip`, ya agregada), y se
  reforzó la 10.3 explicando por qué los 6 modelos faltantes son la
  dependencia inmediata del seed del instrumento. `RESUMEN_ESQUELETO.md` no
  se volvió a tocar en esta pasada (su puntero a la sección 10 ya seguía
  siendo válido). Este mismo archivo (`BITACORA_CAMBIOS.md`) se actualizó
  para incorporar la Pasada 4 completa antes de dividir en commits.

---

## 9. Qué falta (para que el otro agente no lo marque como omisión)

Esto **no** se hizo a propósito, no es un olvido:

- No se creó lógica de calificación/cálculo real en ningún método (todos los
  controladores devuelven `Flasher::error('Funcionalidad pendiente...')` o
  `return null/false` donde correspondería la lógica de negocio) — se pidió
  explícitamente "sin lógica de negocio todavía" en la Pasada 1 y "sigue
  siendo hardening + documentación, sin lógica de calificación todavía" en
  las pasadas 2 y 3.
- ~~No se crearon los 6 modelos para `categoria`, `dominio`, `dimension`,
  `pregunta_filtro`, `umbral`, `resultado_detalle`~~ — **4 de 6 creados en
  la Pasada 6** (`categoriaModel`, `dominioModel`, `umbralModel`,
  `resultadoDetalleModel`, ver sección 12.1), porque el handoff de
  Desarrollo los pidió como parte de la primera tarea (motor de
  calificación). **`preguntaFiltroModel` creado en la Pasada 9** (ver
  sección 1.16) — lo necesitó `cuestionarioController::responder()`/
  `post_responder()` para resolver las dos preguntas-filtro reales de cada
  guía. Sigue sin modelo `dimensionModel` — nadie lo ha necesitado todavía
  (el motor de cálculo usa `reactivo.dominio_id`/`categoria_id`
  denormalizados, nunca sube hasta `dimension`).
- ~~No se resolvió el límite de trabajadores de la Guía II (15 vs 16)~~ —
  **resuelto en la Pasada 4:** es 16, confirmado contra el texto oficial de
  la norma; el caso ≤15 ("no requiere cuestionario") quedó documentado.
- ~~No se agregó la columna `ip` a `auditoria`~~ — **agregada en la Pasada 4**
  a `ddl.sql`, `auditoriaModel` y `registrar_auditoria()`.
- No se tocó `docs/norma/Transcripcion_GuiaIII_NOM-035.md` (se verificó, no
  necesitaba correcciones) ni `docs/Modelo_ER_Cuestionario_NOM-035.md`
  (existe en el repo desde el commit `76c8476`, pero nadie ha pedido a
  Claude que lo revise todavía).
- No se creó ninguna tabla nueva en la base de datos real (XAMPP/MySQL);
  `ddl.sql` sigue siendo sólo un archivo de diseño, no se ejecutó.
- No se creó el `umbralModel::nivel_para()` real (sigue como TODO en
  `guiaModel::nivel_de_riesgo()`) — depende de que exista `umbralModel`
  (ver el punto de los 6 modelos, arriba).

---

## 11. Pasada 5 — ajustes puntuales (2026-09-18)

Prompt recibido de "mi agente" (otro asistente del equipo): dos ajustes
pequeños sobre lo que dejó la Pasada 4, tomando `docs/DDL/ddl.sql` como
única fuente de verdad del esquema, sin lógica de calificación.

### 11.1 `app/functions/bee_custom_functions.php` — `registrar_auditoria()`

La columna `ip` y el docblock de esquema de `auditoriaModel` **ya
existían** desde la Pasada 4 (sección 1.11) — no hubo que tocar `ddl.sql`
ni `auditoriaModel.php` de nuevo, ambos ya cumplían lo pedido. Lo que
cambió fue exclusivamente **cómo se captura la IP** dentro de
`registrar_auditoria()`:

- Se quitó `get_user_ip()` (helper de `bee_core_functions.php`) y se
  reemplazó por `$_SERVER['REMOTE_ADDR'] ?? null`. **Por qué:** `get_user_ip()`
  confía, en ese orden, en `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR` y
  `HTTP_X_FORWARDED` **antes** de caer a `REMOTE_ADDR` — esas cabeceras las
  puede mandar el propio cliente con cualquier valor cuando no hay un proxy
  real reescribiéndolas, así que confiar en ellas a ciegas para una
  bitácora forense es contraproducente (el atacante controla el dato que
  se guarda sobre sí mismo).
- Se agregó un bloque `TODO` extenso explicando que, en producción, si el
  sistema queda detrás de un proxy/reverse proxy, `REMOTE_ADDR` pasa a ser
  la IP del proxy, y ahí sí habría que resolver `X-Forwarded-For` — pero
  únicamente si la petición viene de una lista cerrada de proxies de
  confianza, nunca a ciegas.
- La IP sigue **sin ser un parámetro nuevo** de la función (se capta
  internamente, como pedía la instrucción) y sigue siendo opcional: si
  `REMOTE_ADDR` no está disponible se guarda `null` (la columna es
  `NULL`), sin que falle el `insertOne()`. El guard existente que exige un
  `usuario_id` de enlace válido (Pasada 3) **no se tocó**.

### 11.2 Guard funcional para centros de trabajo de ≤15 trabajadores

`guiaModel::por_numero_trabajadores()` (sección 1.5) **ya regresaba `null`
de forma limpia** para `<=15` desde la Pasada 4 — se confirmó de nuevo, sin
necesidad de tocar su SQL. Lo que faltaba era que los controladores que lo
consumen **actuaran** sobre ese `null` en vez de sólo tener un comentario
`TODO` al respecto:

- **`app/controllers/administradorController.php`** (`post_centros_trabajo()`):
  se agregó el guard real — resuelve `guiaModel::por_numero_trabajadores((int) $_POST['num_trabajadores'])`
  y, si regresa `null`, corta el flujo con
  `Flasher::error('Los centros de trabajo de hasta 15 trabajadores no
  requieren la aplicación de este cuestionario conforme a la NOM-035.')` +
  `Redirect::back()` (que hace `die()` internamente, ver `Redirect.php`) —
  no llega a la parte de alta en la base de datos, que sigue como `TODO`
  pendiente de la fase de Desarrollo. **Por qué aquí primero:** es el único
  punto donde `num_trabajadores` llega como dato crudo del formulario, sin
  depender de ninguna otra tabla todavía sin implementar.
- **`app/controllers/cuestionarioController.php`** (`post_acceso()`): el
  mismo guard, como defensa en profundidad para el acceso del encuestado,
  se dejó como `TODO` **detallado** (con la cadena exacta de métodos a
  llamar: `tokenModel::by_codigo()` → `centroTrabajoModel::by_id()` →
  `guiaModel::por_numero_trabajadores()`), sin implementarlo todavía.
  **Por qué no se implementó de una vez:** hacerlo real requeriría también
  construir la resolución token→centro de trabajo, que sigue siendo `TODO`
  desde la Pasada 1 (`tokenModel::esta_vigente()` no está implementado) —
  eso ya sería lógica de negocio nueva, fuera del alcance de "dos ajustes
  pequeños" de este prompt. En condiciones normales este guard nunca
  debería activarse aquí, porque el de `administradorController` ya impide
  crear esos centros de trabajo; queda documentado por si algún día se
  cargan datos directo en la base de datos sin pasar por el administrador.
- `docs/ARQUITECTURA.md` sección 11 documenta ambos puntos.

---

## 12. Pasada 6 — motor de calificación (2026-09-21)

Primera tarea de desarrollo real marcada por `docs/HANDOFF_DESARROLLO.md`
§5. Es la primera pasada que agrega **lógica de negocio** (las 5 anteriores
fueron explícitamente "sin lógica de calificación"). Antes de tocar código
se leyó el handoff completo y, en el orden que indica su §2, los 5
documentos que referencia — más una verificación **en vivo contra
`db_beeframework`** (no sólo contra los documentos): se confirmó que el
seed ya estaba cargado exactamente como decía el handoff (2 guías, 9
categorías, 18 dominios, 45 dimensiones, 4 filtros, 118 reactivos, 145
umbrales) y se detectó que el diagrama ER de
`docs/Modelo_ER_Cuestionario_NOM-035.md` está desactualizado en 2 puntos
(un `usuario.centro_trabajo_id` que no existe, y un `umbral.referencia_id`
polimórfico que en el DDL real son `categoria_id`/`dominio_id` separados) —
se usó el DDL real, no el diagrama, como indica el propio handoff ("si algo no coincide, gana el DDL"). También se confirmó que
`docs/norma/Transcripcion_GuiaII_NOM-035.md` (referenciado por el handoff)
no existe en el repo; se compensó cruzando la polaridad y los umbrales de
Guía II directamente contra la BD sembrada, que coincidieron con el resumen
de `Modelo_ER.md` §5.1 y §5.3.

### 12.1 Modelos nuevos

- **`categoriaModel.php`, `dominioModel.php`** — sólo lectura (`by_id`,
  `por_guia`/`por_categoria`). **Por qué mínimos:** el motor de cálculo usa
  `reactivo.dominio_id`/`categoria_id` ya denormalizados (ver 1.6), no
  necesita iterar el árbol categoria→dominio; estos modelos son para
  nombrar renglones en reportes futuros, no los usa `calcular_para_aplicacion()`.
- **`umbralModel.php`** — `nivel_final()`, `nivel_categoria()`,
  `nivel_dominio()`, los tres sobre una sola implementación privada
  (`buscar_nivel()`) con la convención `limite_inferior <= valor < limite_superior`
  (confirmada en `Modelo_ER.md` §5.3 y contra las filas reales de la BD).
  **Por qué un método privado compartido:** evitar tres copias de la misma
  lógica de rango con sólo el nombre de columna distinto.
- **`resultadoDetalleModel.php`** — `insertOne`, `by_id`, `por_resultado()`.

### 12.2 `resultadoModel.php` — `calcular_para_aplicacion()` implementado

Antes era un stub que regresaba `null` con un TODO. Ahora calcula de verdad:
respuestaModel::por_aplicacion() + reactivoModel::calcular_puntaje()
(**ninguno de los dos se tocó** — ya traían exactamente lo que el algoritmo
necesitaba, construidos así desde la Pasada 3) → suma en PHP por
dominio/categoría/total → `umbralModel` → persiste en `resultado` +
`resultado_detalle`.

**Hallazgo real durante la implementación (no en los documentos, se
descubrió corriendo el código):** `Db::query()` (`app/classes/Db.php`,
núcleo, no se modifica) hace auto-commit con las opciones por default, pero
su rama `SELECT` hace `return` **antes** de llegar al `commit()` — o sea,
cualquier `SELECT` por default dentro de una misma ejecución de PHP deja la
conexión con una transacción abierta sin cerrar. Es invisible en el resto
del sistema (cada request de Bee es de corta vida). Se volvió visible aquí
porque `calcular_para_aplicacion()` hace varias lecturas por default antes
de necesitar controlar su propia transacción — al intentar
`$link->beginTransaction()` explícito, PDO tiraba
`PDOException: There is already an active transaction`. **Se corrigió
dentro de `resultadoModel`, sin tocar `Db.php`:** si `$link->inTransaction()`
es verdadero justo antes de empezar, se cierra esa transacción colgada
(`commit()` — no hay nada que perder, sólo fueron lecturas) y entonces sí
se abre la transacción real que el método controla explícitamente
(`Db::connect()->beginTransaction()`/`commit()`/`rollBack()`, con
`['transaction' => false]` en cada `Model::query()` individual para que no
vuelvan a auto-comitear a medio camino). Documentado en un comentario
extenso en el propio método para que nadie lo "arregle" pensando que es
código de más.

**Idempotencia:** se borra el `resultado` previo de la aplicación antes de
insertar (`ON DELETE CASCADE` de `resultado_detalle`, ya en `ddl.sql`, se
encarga del desglose viejo) — reemplaza, no duplica. Verificado
corriendo el cálculo dos veces sobre la misma aplicación (ver 12.3).

### 12.3 `scripts/verificar_motor_calificacion.php` (nuevo)

Script standalone por CLI, **no** es parte de la app (no agrega rutas ni
controladores). Arranca el mínimo del framework que necesitan los modelos
(config, autoloader, funciones) sin pasar por `Bee::fly()` (que despacharía
un controlador HTTP); simula `$_SERVER['REMOTE_ADDR']` para que
`bee_config.php::IS_LOCAL` tome las credenciales `LDB_*` en vez de las de
producción (vacías) — sin esto, `IS_LOCAL` sale `false` en CLI porque
`$_SERVER['REMOTE_ADDR']` no existe fuera de una petición HTTP real.

Para cada guía (GRII, GRIII) crea una cadena de datos real
(`secretaria`→`centro_trabajo`→`token`→`aplicacion`→`respuesta`) y corre 3
casos (todo "Siempre", todo "Nunca", filtros en "No") comparando contra un
valor esperado calculado por fórmula desde el conteo real de polaridad en
la BD — no hardcodeado. Al final borra todo lo que insertó, en un bloque
`finally`, para poder correrse las veces que haga falta sin ensuciar la
base de datos.

**Corrida real (2026-09-21):** 0 fallos — calificación final y cada
renglón de dominio/categoría exactos en ambas guías, idempotencia
confirmada (recalcular no duplica), exclusión de reactivos condicionales
confirmada, ningún nivel de riesgo `null`. Se confirmó además que las 8
tablas operativas quedaron en 0 filas tras la limpieza (estado idéntico al
inicial).

### 12.4 `guiaModel.php` — `nivel_de_riesgo()` implementado

Era un TODO que esperaba a que existiera `umbralModel`. Ahora es un
delegador de conveniencia hacia los tres métodos de `umbralModel` según
`$nivelAgregacion` — así `resultadoModel` no tiene que decidir cuál de los
tres invocar en cada caso (aunque en la práctica `calcular_para_aplicacion()`
sí llama a los tres métodos de `umbralModel` directamente, por claridad).
También se limpiaron las 2 referencias a "`umbralModel` — pendiente de
scaffolding" del docblock de la clase, que ya no aplican.

### 12.5 `docs/ARQUITECTURA.md` — sección 13 agregada

Documenta todo lo de 12.1–12.4 con más detalle técnico (el algoritmo
completo, el hallazgo de `Db::query()`, y qué queda fuera de esta tarea:
conectar `cuestionarioController::post_responder()` al motor, y la UI de
reportes — ambos explícitamente fuera de alcance del handoff).

---

## 13. Pasada 7 — bootstrap y módulo de súper usuario (2026-09-23)

Segunda tarea de desarrollo real (`docs/HANDOFF_DESARROLLO.md` §3-4).
Antes de tocar código se releyeron las secciones 3 y 4 del handoff, y se
leyó específicamente cómo Bee crea/valida contraseñas (`app/classes/Auth.php`,
`loginController.php`, `beeController::password()`/`generate_user()`,
`get_new_password()` en `bee_core_functions.php`) para no inventar el hash,
tal como pedía el prompt.

### 13.1 Bootstrap de la cuenta raíz (nuevo)

- **`scripts/generar_bootstrap_superusuario.php`** (nuevo, CLI, reutilizable):
  arranca el framework igual que `verificar_motor_calificacion.php`
  (Pasada 6) y llama a `get_new_password()` — la MISMA función que usa
  `beeController::generate_user()`, que a su vez hace
  `password_hash($password . AUTH_SALT, PASSWORD_BCRYPT)`, exactamente lo
  que valida `loginController::post_login()`. **Por qué reutilizarla en vez
  de escribir `password_hash()` a mano:** el prompt lo pedía explícitamente
  ("no inventes el hash"), y usar la función real de Bee garantiza cero
  desviación del algoritmo aunque cambie en el futuro.
- **`scripts/bootstrap_superusuario.sql`** (nuevo): la salida real de correr
  el generador una vez — 3 `INSERT` encadenados (`secretaria` → `bee_users`
  → `usuario` con `rol='superusuario'`) usando `SET @variable = LAST_INSERT_ID()`
  para encadenar los ids sin depender de saber los autoincrementos de
  antemano. Es el archivo que el usuario corre en phpMyAdmin — Claude
  **no** insertó estas filas reales en la BD del usuario (ver 13.3).

### 13.2 `superusuarioController.php` — de stubs a funcional

- **`administradores()`**: ya no es TODO — usa
  `usuarioModel::administradores_por_secretaria()` filtrado por la
  secretaría del súper usuario en sesión.
- **`post_administradores()`**: crea `bee_users` + enlace `usuario`
  (`rol='administrador'`). Copia las validaciones de
  `adminController::post_usuarios()` (regex username/password, email +
  `is_temporary_email()`, duplicados) — **por qué copiarlas en vez de
  reinventar:** consistencia con el resto del sistema, ya estaban probadas.
  Registra `'alta_administrador'` en la bitácora.
- **`borrar_administrador()`**: hallazgo real (no documentado en ningún
  archivo previo) al implementarlo — `auditoria.usuario_id -> usuario.id`
  es `ON DELETE RESTRICT` a propósito, así que un `DELETE` físico de la
  cuenta de un administrador puede chocar con su propio historial de
  auditoría (que existe desde el momento en que se le da de alta). Se
  cambió el diseño sobre la marcha: "baja" = revocar el acceso
  (sobrescribir la contraseña con un valor aleatorio descartado, vía
  `get_new_password()`), no borrar filas — preserva la bitácora intacta.
  Documentado extensamente en el docblock del método, incluida la
  limitación conocida (sin columna `activo`, un administrador revocado
  sigue apareciendo en el listado).
- **`bitacora()`**: nuevo `auditoriaModel::por_secretaria()`.
- **`usuarioModel::administradores_por_secretaria()`**: se le agregó el
  JOIN a `bee_users` que tenía pendiente desde la Pasada 1 (comentario
  "TODO: implementar join... para obtener username/email").

### 13.3 Verificación real contra el servidor local (no sólo `php -l`)

A diferencia de las pasadas anteriores (que verificaban con scripts PHP en
CLI), aquí hacía falta probar sesiones/cookies reales — se usó `curl` con
cookie jar contra el Apache/MariaDB de XAMPP ya corriendo, con datos de
prueba **desechables** (nombres `__test_*`, nunca las credenciales del
bootstrap real):

1. Se insertó por SQL directo una secretaría + súper usuario de prueba (con
   hash calculado igual que el generador).
2. Login real por HTTP (`POST /login/post_login` con CSRF extraído del
   formulario) → 302 a `/admin`, cookies persistentes
   `bee__cookie_id`/`bee__cookie_tkn` seteadas por `BeeSession::new_session()`.
3. `GET /superusuario` → 200 (el guard de rol pasa).
4. `POST /superusuario/post_administradores` real (formulario completo) →
   creó un administrador de prueba de verdad en la BD.
5. Login de ese administrador de prueba → 302 a `/admin`; `GET /administrador`
   → 200; `GET /superusuario` con su sesión → rechazado y redirigido (el
   guard de rol también bloquea en el sentido contrario).
6. `GET /superusuario/bitacora` → mostró la fila `alta_administrador` real,
   con el `username` correcto vía el JOIN nuevo.
7. `GET /superusuario/borrar_administrador/{id}?_t=...` → tras esto, el
   administrador de prueba **ya no pudo iniciar sesión** (mismo mensaje que
   credenciales inválidas) — confirma que la revocación (13.2) funciona.
8. Alcance por secretaría: se creó una SEGUNDA secretaría + súper usuario
   de prueba y se confirmó que ve listado de administradores y bitácora
   **vacíos** — no ve nada de la primera secretaría.
9. Limpieza total: se borraron las filas de `auditoria` (actor = el súper
   usuario de prueba, había que borrarlas primero por el mismo `ON DELETE
   RESTRICT` de 13.2), luego `usuario`, `bee_users` y `secretaria` de
   prueba. Confirmado con `SELECT COUNT(*)`: las 9 tablas operativas
   quedaron en 0 filas, igual que antes de empezar.

**Incidente durante la verificación (documentado, no oculto):** a media
prueba MariaDB se atoró (dos consultas — un `SELECT` a `bee_users` de
`BeeSession::authenticate()` sin modificar, y una a `options` que este
módulo no toca — quedaron indefinidamente en "Opening tables"/"Statistics";
`KILL` no las liberó). No hay indicio de que lo causara código de este
proyecto. Se reinició MariaDB y Apache (`taskkill` + los `.bat` de XAMPP) y
se confirmó que ningún dato se perdió (seed del instrumento: 2/9/18/45/4/118/5/145
intacto) antes de continuar la verificación donde se había quedado.

---

## 14. Pasada 8 — `usuario.estado` y módulo de administrador (2026-09-24)

Tercera tarea de desarrollo real (`docs/HANDOFF_DESARROLLO.md` §3-4), en dos
partes: cerrar la limitación de la Pasada 7 y construir el módulo de
administrador de punta a punta.

### 14.1 `usuario.estado` (cierra la limitación de la Pasada 7)

- **`docs/DDL/ddl.sql`**: se agregó la columna a la `CREATE TABLE usuario`
  (instalaciones nuevas).
- **`scripts/alter_usuario_estado.sql`** (nuevo): el `ALTER TABLE` para la
  base ya existente. **El prompt fue explícito: "no lo ejecutes tú" —
  Claude no lo corrió contra ninguna base de datos**, ni siquiera la local
  de desarrollo donde sí se insertan/borran datos de prueba en otras
  pasadas. Diferencia clave: aquí es un cambio de **esquema**, no de datos.
- **`superusuarioController::borrar_administrador()`**: intenta marcar
  `estado='inactivo'` en un `try/catch` propio que ignora el error si la
  columna no existe — la revocación real (invalidar contraseña) nunca
  depende de que el `ALTER` ya se haya aplicado.
- **`usuarioModel::administradores_por_secretaria()`**: segundo parámetro
  opcional `$estado`. **`administradores()`**: intenta filtrar por
  `?estado=` y se degrada a mostrar todos si la consulta falla.
- **`administradoresView.php`**: badge de estado + filtro Todos/Activos/Inactivos,
  con `$admin->estado ?? 'activo'` para no romper si la columna no existe.
- **Verificación:** sólo `php -l` y revisión de código para la parte de
  `estado` (no se pudo probar en vivo, ver 14.3); sí se re-verificó en vivo
  que la revocación por contraseña sigue funcionando exactamente igual que
  en la Pasada 7 (no se rompió nada existente).

### 14.2 Módulo de administrador — implementado y verificado en vivo

- **`tokenModel::generar_codigo()`**: `bin2hex(random_bytes(16))` (128 bits
  de entropía) — **no** `random_password()` de `bee_core_functions.php`
  (usa `rand()`, no apto para una credencial de acceso), con verificación
  de colisión contra `by_codigo()`.
- **`administradorController`**: `centros_trabajo()` (lista + resuelve la
  guía de cada fila una sola vez, en el controlador — no N+1 en la vista),
  `post_centros_trabajo()` (`secretaria_id`/`administrador_id` siempre del
  usuario en sesión, nunca de `$_POST`; validación de entero positivo),
  `tokens()`/`post_tokens()` (campos `fecha_inicio`/`fecha_fin` nuevos en
  el formulario, validación de fechas + `fecha_fin >= fecha_inicio`),
  `revocar_token()`. Cada método valida alcance por
  `centro_trabajo.administrador_id === usuario.id` — no basta con
  pertenecer a la misma secretaría (a diferencia del súper usuario, que sí
  comparte secretaría entre administradores).
- **`centrosTrabajoView.php`/`tokensView.php`**: de placeholders a `foreach`
  reales, con badges de guía/estado.
- **Verificado por `curl` contra el servidor local** (mismo método que la
  Pasada 7), con datos desechables limpiados al final:
  - 15 trabajadores → rechazado con el mensaje exacto pedido, nada se crea.
  - `num_trabajadores=abc` → rechazado por la validación.
  - 20 trabajadores → centro creado, aparece con badge `GRII`.
  - Token generado → `tokenModel::esta_vigente()` regresa `true` de
    inmediato (criterio de aceptación explícito).
  - `fecha_fin` anterior a `fecha_inicio` → rechazado, nada se crea.
  - Token revocado → `estado='inactivo'`, `esta_vigente()` pasa a `false`.
  - Bitácora del súper usuario muestra `alta_centro_trabajo`,
    `generar_token`, `revocar_token`.
  - **Alcance, el caso más exigente:** dos administradores de prueba en la
    **misma** secretaría — el segundo no ve el centro del primero en su
    listado, y pedir `/administrador/tokens/{id}` del centro ajeno **por
    URL directa** es rechazado y redirigido (el controlador bloquea, no
    sólo la vista oculta el enlace).
  - Limpieza total confirmada: `SELECT COUNT(*)` en 0 para las tablas
    operativas de prueba, seed del instrumento intacto (2/118/145).

### 14.3 Nota de transparencia

A diferencia de las pasadas 6 y 7, esta vez **una parte del código no se
verificó en vivo** (el filtro/marca por `usuario.estado`, sección 14.1) —
porque el prompt pidió explícitamente no ejecutar el `ALTER TABLE`. Se
prefirió dejarlo así, honesto, en vez de aplicar el cambio "sólo para
probar" y luego revertirlo — el prompt no distinguía entre "ejecutarlo
para siempre" y "ejecutarlo temporalmente", así que se tomó la lectura más
conservadora.

---

## 15. Restricciones respetadas en las 10 pasadas

- **Cero comandos de Git ejecutados por Claude.** Cuando el usuario pidió
  los 6 commits segmentados, Claude entregó los comandos como texto para que
  el usuario los corriera él mismo; nunca se invocó `git commit`/`git add`
  desde una herramienta de Claude.
- **Cero archivos de `app/classes/*` modificados** — ni siquiera para el
  hallazgo de `Db::query()` de la Pasada 6 (sección 12.2), que se resolvió
  enteramente dentro de `resultadoModel.php`.
- **Los dos únicos archivos "existentes" del núcleo/framework que se
  modificaron** siguen siendo los que Bee reserva para el proyecto:
  `app/functions/bee_custom_functions.php` y `templates/includes/styles.php`
  (secciones 4 y 5). Las pasadas 6-10 no agregaron un tercero:
  `scripts/` es una carpeta nueva, fuera de `app/` y `templates/`, sin
  rutas ni controlador propio. La Pasada 10 sí agregó funciones nuevas a
  `bee_custom_functions.php` (`ruta_tablero_para_rol()`,
  `ruta_tablero_segun_rol()`) y modificó `requiere_rol()` — sigue siendo el
  mismo archivo, el punto de extensión reservado por Bee para el proyecto.
- **`docs/DDL/ddl.sql`** se modificó tres veces (Pasada 3, Pasada 4 y
  Pasada 8), con autorización explícita en las tres. En las pasadas 6, 7, 9
  y 10 se leyó como fuente de verdad del esquema y **no** se tocó (incluido el
  hallazgo de `ON DELETE RESTRICT` en `auditoria.usuario_id` de la Pasada
  7, que se resolvió cambiando el diseño de la aplicación, no el esquema).
  En la Pasada 8 el `ALTER TABLE` correspondiente se entregó como archivo
  aparte (`scripts/alter_usuario_estado.sql`) para que lo corra el usuario
  — **no se ejecutó contra ninguna base de datos, ni la local.**
- **`Planificacion_Proyecto_Cuestionario_NOM-035 (1-2).md`** se tocó por
  primera vez en la Pasada 4 (3 líneas: el límite de la Guía II y RF-00),
  también para corregir un dato que el segundo agente confirmó como
  erróneo contra el texto oficial de la norma — no por iniciativa unilateral
  de Claude sin respaldo.
- **`php -l` en cada archivo tocado, en cada pasada, incluida ésta** (2
  modelos, 1 controlador, 2 vistas, 2 scripts en la Pasada 7; 2 modelos,
  2 controladores, 3 vistas, 1 SQL en la Pasada 8; 2 modelos, 1 controlador,
  3 vistas en la Pasada 9; 1 función auxiliar, 3 controladores, 5 vistas/includes
  en la Pasada 10).
- **La Pasada 7 sí insertó y borró datos reales en la BD del usuario**
  (datos de prueba desechables, para la verificación por `curl` de 13.3) —
  pero **no** dejó nada permanente: se confirmó con `SELECT COUNT(*)` que
  las tablas operativas quedaron en 0 filas al terminar, igual que al
  empezar. Las credenciales reales del bootstrap (13.1) se entregan como
  SQL para que el usuario las aplique él mismo, tal como pidió — Claude no
  las insertó en la base de datos del usuario. **La Pasada 8 repitió el
  mismo patrón** para verificar el módulo de administrador (14.2) — datos
  de prueba desechables, limpiados al final, confirmado con
  `SELECT COUNT(*)` — pero esta vez con una distinción explícita: los
  **datos** de prueba sí se insertaron/borraron en la BD local, el **ALTER
  TABLE** (cambio de esquema) no se ejecutó en absoluto, ni siquiera ahí
  (ver 14.1/14.3). **La Pasada 9 repitió el mismo patrón** para verificar el
  flujo del encuestado de punta a punta (16.2) — dos secretarías/centros/
  tokens de prueba (GRII y GRIII), tres aplicaciones reales respondidas vía
  `curl` (una completa GRIII, una completa GRII con filtros en "No", una
  bloqueada por el guard ≤15 antes de crear nada), todo limpiado al final y
  confirmado con `SELECT COUNT(*)` en 0, con el instrumento sembrado (2
  guías, 118 reactivos, 145 umbrales, etc.) verificado intacto después.
- **La Pasada 10 es la ÚNICA que NO limpió sus datos de prueba — a propósito,
  por instrucción explícita del usuario** ("NO borres los datos de esta
  prueba: déjalos sembrados para que YO pueda hacer el mismo recorrido a
  mano en el navegador"). Se aplicó el bootstrap del súper usuario que la
  Pasada 7 había dejado listo pero sin ejecutar
  (`scripts/bootstrap_superusuario.sql` — sigue siendo un `INSERT`, no un
  cambio de esquema) y, desde ahí, se recorrió el flujo completo con
  peticiones HTTP reales (no inserciones directas a la base de datos) para
  también ejercitar los controladores reales, no sólo los datos. Las
  credenciales y el token quedaron documentados en la sección 17.4 para el
  usuario. El `ALTER TABLE` de `usuario.estado` (Pasada 8) sigue sin
  ejecutarse — eso no cambió.
- **Se reinició MariaDB y Apache localmente** durante la Pasada 7 (13.3,
  incidente) — acción de infraestructura local de desarrollo, no
  destructiva (se confirmó integridad de datos antes/después), no
  relacionada con Git ni con el código del proyecto.

---

## 16. Pasada 9 — flujo del encuestado (2026-09-24)

Prompt recibido de "mi agente": cuarta tarea de `docs/HANDOFF_DESARROLLO.md`
— implementar el flujo completo del encuestado (`cuestionarioController`,
hasta ahora stubs), que cierra la cadena hasta el motor de calificación ya
implementado en la Pasada 6 (`resultadoModel::calcular_para_aplicacion()`).
A diferencia de los módulos de administrador/súper usuario, este flujo es
**público**: no usa cuenta de Bee, el acceso se controla íntegramente por la
vigencia del token.

### 16.1 El problema de fondo: ¿cómo se liga una petición a "su" aplicación sin cuenta de Bee?

El token es multiuso por centro de trabajo (decisión de diseño B, ver
`tokenModel`): varios encuestados del mismo centro pueden usar el mismo
código al mismo tiempo. Eso significa que la URL `responder/{token}` por sí
sola no basta para saber "de quién" es el cuestionario a medio responder —
hace falta algo que distinga a esta petición de la de cualquier otro
encuestado con el mismo token.

La solución adoptada: `post_acceso()` crea la fila `aplicacion` y guarda su
id en la **sesión nativa de PHP** (no `Auth`/Bee — esa sesión ya está activa
en cada petición, `Bee::init_set_up()` llama `session_start()`) bajo
`$_SESSION['cuestionario'] = ['aplicacion_id' => ..., 'token_codigo' => ...]`.
`responder()` y `post_responder()` revalidan esa sesión contra el token de
la URL/formulario en el método privado `aplicacionEnCurso($token)` — si no
coincide, si el token ya no está vigente, o si la aplicación ya está
`'completada'`, deniega "en falso cerrado" y regresa a `index()` (mismo
criterio que `resultadosController::verificarAccesoCentroTrabajo()`).
**Por qué no una cuenta de Bee ni un token JWT propio:** el handoff es
explícito en que este flujo "NO usa cuenta de Bee"; inventar un mecanismo de
sesión propio habría sido una abstracción nueva innecesaria cuando Bee ya
deja la sesión nativa de PHP lista para usarse.

**Consecuencia de diseño (a propósito, no un descuido):** "una sola
aplicación por (token_id, numero_servidor_publico)" se aplica de forma
literal — si ya existe una fila `aplicacion` para esa combinación, sea
`'en_progreso'` o `'completada'`, `post_acceso()` la bloquea con "ya
respondiste esta campaña". No se implementó un flujo de "reanudar" un
cuestionario a medias (el handoff no lo pidió y no hay forma de que el
encuestado demuestre que es la misma persona sin una cuenta): si alguien
cierra el navegador a medio responder, esa combinación queda agotada.

### 16.2 Verificación end-to-end (la que faltaba)

Se corrió el flujo completo contra el servidor Apache/MariaDB local real
(no sólo `php -l`), con datos de prueba desechables (secretaría → centro de
trabajo → token, prefijo `__TEST_E2E__`) insertados vía los modelos reales
(mismo patrón CLI que `scripts/verificar_motor_calificacion.php`) y
manejados con `curl` en cada paso — acceso, `responder`, envío. Todo se
limpió al final (`DELETE` en cascada desde `aplicacion`, confirmado con
`SELECT COUNT(*)` en 0) y se confirmó que el instrumento sembrado (2 guías,
118 reactivos, 145 umbrales, 9 categorías, 18 dominios, 4 preguntas-filtro,
5 opciones) quedó intacto.

Casos verificados:
- **Guía III completa (72 reactivos, ambos filtros en "Sí"):** acceso con
  token real → 72 reactivos + 2 preguntas-filtro renderizados en
  `responder()` (con nombres/textos reales, no de ejemplo) → envío con las
  72 respuestas → `aplicacion.estado='completada'` → `resultado` y
  `resultado_detalle` (15 filas: 5 categorías + 10 dominios de GRIII)
  poblados automáticamente al enviar, sin paso manual. **Verificado contra
  un cálculo a mano con los datos REALES capturados por el flujo** (no
  inyectados directo a la BD como en la Pasada 6): las 72 respuestas se
  capturaron todas en la opción "Siempre" (posición 0); con 37 reactivos de
  polaridad `normal` y 35 `invertida` en GRIII, el esperado a mano es
  `37×4 + 35×0 = 148` → el motor calculó exactamente `148`, nivel
  `muy_alto` (≥140, sección 3.1 de la transcripción de la norma).
- **Guía II completa (46 reactivos, ambos filtros en "No"):** acceso con un
  segundo token real (centro de 30 trabajadores) → envío de únicamente los
  40 reactivos obligatorios, dejando sin contestar los 6 condicionales
  (3 de la pregunta-filtro de clientes, 3 de la de jefatura) → se guardaron
  exactamente 40 `respuesta` (0 de las 6 omitidas por filtro, confirmado por
  consulta directa) → resultado calculado correctamente: 24 reactivos
  `normal` + 16 `invertida` entre los 40 obligatorios → esperado a mano
  `24×4 + 16×0 = 96` → el motor calculó exactamente `96`.
- **Envío incompleto (RF-04):** se omitió a propósito 1 de los 40 reactivos
  obligatorios de la prueba GRII — `post_responder()` rechazó el envío con
  "Debes responder todas las preguntas antes de enviar el cuestionario.",
  **no insertó ninguna fila de `respuesta`** (confirmado en 0) y la
  aplicación permaneció `'en_progreso'` (no se marcó `'completada'` a
  medias).
- **Doble respuesta (RF-11):** un segundo intento de acceso con el mismo
  token y el mismo `numero_servidor_publico` ya usado se bloqueó con "Ya
  respondiste esta campaña con ese número de servidor público." antes de
  tocar la base de datos.
- **Guard ≤15 (defensa en profundidad):** un token cargado "a mano" contra
  un centro de 10 trabajadores (simulando datos inconsistentes, ya que
  `administradorController::post_centros_trabajo()` impide crear esos
  centros desde la aplicación) fue rechazado por `post_acceso()` con el
  mensaje de "no requiere cuestionario conforme a la NOM-035" — **no se
  creó ninguna fila `aplicacion`** para ese intento (confirmado en 0).
- **Token inexistente** y **verbo GET en `post_acceso`/`post_responder`**:
  ambos rechazados como se esperaba (el segundo por `requiere_metodo_post()`,
  ya existente desde la Pasada 2).
- **La pantalla de agradecimiento no filtra el resultado:** se confirmó que
  la respuesta HTML de `post_responder`/`gracias()` no contiene la
  calificación ni el nivel de riesgo calculados — el handoff es explícito en
  que el resultado es para administrador/súper usuario, no para el
  encuestado.

---

## 17. Pasada 10 — navegación de punta a punta (2026-09-24)

Prompt recibido de "mi agente": el objetivo NO eran funcionalidades nuevas,
era dejar el sistema **navegable con clics reales** para validación visual
humana — conectar los tres flujos ya construidos (encuestado, administrador,
súper usuario), una vista mínima de resultado individual real, y puntos de
entrada claros desde la raíz del sitio.

### 17.1 El bug real: redirección post-login y el global `$Bee_User`

El primer paso fue el más importante: `loginController::post_login()`
redirigía **siempre** a `admin` (el panel demo nativo de Bee), sin importar
el rol del usuario que acababa de iniciar sesión — nunca llevaba a un
administrador o súper usuario a su propio tablero. El guard "ya hay sesión
abierta" del constructor tenía el mismo problema, hacia `admin/perfil`.

Al corregirlo con una función centralizada (`ruta_tablero_segun_rol()`,
sección 4) apareció un segundo bug, más sutil, que sólo se manifestaba
probando el login de verdad con `curl` (no se habría detectado leyendo el
código):

- `get_user()` (núcleo de Bee, `bee_core_functions.php`) NO lee
  `$_SESSION['user_session']['user']` directamente — lee el global
  `$Bee_User`, poblado UNA SOLA VEZ por petición, en
  `Bee::init_authentication()`, **antes** de que se despache el controlador.
  El comentario original en el código explica que esto es intencional (para
  reflejar cambios en la BD sin tener que cerrar sesión), pero tiene una
  consecuencia no documentada: un `Auth::login()` que ocurre a la mitad de
  la petición (como dentro de `post_login()`) actualiza `$_SESSION`, pero
  **no** actualiza el global `$Bee_User` — ese ya se leyó al principio de la
  petición, cuando todavía no había sesión.
- Resultado observado: `obtener_usuario_actual()` (que depende de
  `get_user('id')`) regresaba `[]` inmediatamente después de un login
  exitoso, así que `obtener_rol_usuario_actual()` regresaba `null`, y
  `ruta_tablero_segun_rol()` caía siempre a su valor por defecto (`admin`) —
  el mismo síntoma que el bug original, con una causa distinta y más
  profunda.
- **Solución:** se separó la función en dos (`ruta_tablero_para_rol($rol)` +
  `ruta_tablero_segun_rol()`, ver sección 4) y `post_login()` resuelve el
  rol **directamente contra la base de datos** con
  `usuarioModel::by_bee_user_id($user['id'])`, sin pasar por
  `get_user()`/el global. Verificado con `curl`: login del súper usuario de
  prueba → `Location: .../superusuario` (antes: `.../admin`).

Este mismo hallazgo se aprovechó para corregir `requiere_rol()` y
`resultadosController::verificarAccesoCentroTrabajo()` (sección 4/2.4): ya
NO usan este atajo (corren en peticiones normales donde `$Bee_User` sí está
poblado correctamente), así que sí pueden usar `ruta_tablero_segun_rol()` —
la diferencia importante es que ya no expulsan a un usuario con sesión
activa pero rol/alcance equivocado hacia el formulario público del
encuestado, sino de vuelta a su propio tablero.

### 17.2 Sidebar sin ningún enlace real (el segundo hallazgo grande)

`templates/includes/admin/sidebar.php` — compartido por
`administrador`/`superusuario`/`resultados` vía `dashboardTop.php` — seguía
siendo el catálogo genérico de la plantilla SB Admin 2 de Bee (Creator,
Componentes, Usuarios, Productos, Addons): **cero enlaces** a Centros de
trabajo, Tokens, Administradores o Bitácora. Cualquiera de esos módulos sólo
era alcanzable tecleando la URL exacta a mano; no había forma de
"navegar" hacia ellos desde el panel. Se reescribió para ramificar por
`obtener_rol_usuario_actual()` (detalle en sección 3), preservando el
catálogo original sin cambios para una cuenta de Bee sin rol de contexto
(no se quería romper la demo nativa).

### 17.3 Resultado individual real + enlace desde el listado del administrador

`resultadosController::individual()` pasó de TODO a real (identidad, guía,
calificación final + nivel de riesgo, desglose por categoría y dominio, ver
sección 2.4), y `administradorController::tokens()` ahora también carga las
aplicaciones completadas de ese centro (`aplicacionModel::por_centro_trabajo()`,
sin cambios — ya filtraba `estado='completada'`) con un enlace "Ver
resultado" por fila hacia `resultados/individual/{id}` (sección 2.2/3).

**Bug encontrado y corregido durante la verificación con datos reales (no se
habría visto sólo leyendo el código):** la primera versión de
`individualView.php` accedía a `$d->aplicacion['nombre']`,
`$d->resultado['calificacion_final']`, `$fila['nivel_agregacion']`, etc.
(sintaxis de arreglo asociativo) y produjo un **Fatal error** ("Cannot use
object of type stdClass as array") en cuanto se probó con una aplicación
real. **Causa:** `View::renderBeeTemplate()` (núcleo de Bee) convierte todo
`$data` a objetos con `json_decode(json_encode($data))` antes de exponerlo
como `$d` — las filas que vienen de un modelo (arreglos asociativos)
terminan siendo `stdClass`, no arreglos, dentro de la vista (mismo criterio
que ya usan `$centro->nombre`, `$token->estado`, etc. en las vistas de
Pasada 7/8, que esta vista nueva no siguió al escribirse). Se corrigió toda
la vista a `->` y se volvió a verificar con la misma aplicación real: la
página cargó completa (identidad, calificación `95`, badges de nivel por
categoría/dominio).

### 17.4 Verificación end-to-end real (recorrido completo, datos dejados sembrados a propósito)

Se recorrió el camino completo con peticiones HTTP reales (`curl`, no
inserciones directas a la base de datos salvo el paso 1, que es la única
excepción justificada abajo). A diferencia de las pasadas 6-9, **esta vez
los datos NO se borraron** — instrucción explícita del usuario, para poder
repetir el mismo recorrido a mano en el navegador.

1. **Cuenta raíz del súper usuario:** se aplicó el `INSERT` que
   `scripts/bootstrap_superusuario.sql` (Pasada 7) ya había dejado listo
   pero que el usuario todavía no había ejecutado — sigue siendo un
   `INSERT` puro (secretaría + `bee_users` + `usuario`), no un cambio de
   esquema, así que cae dentro de lo ya permitido. Se usó tal cual estaba
   preparado (usuario `superadmin`, contraseña `INyT0jkl`) en vez de
   inventar una cuenta de prueba nueva, para no dejar dos "cuentas raíz"
   distintas dando vueltas.
2. **Login del súper usuario** → verificado el redirect a `/superusuario`
   (17.1).
3. **Alta de un administrador real** vía `POST superusuario/post_administradores`
   (no inserción directa) — usuario `admintest1`.
4. **Login del administrador** → verificado el redirect a `/administrador`.
5. **Alta de un centro de trabajo real** (25 trabajadores, GRII) vía
   `POST administrador/post_centros_trabajo`.
6. **Generación de un token real** vía `POST administrador/post_tokens`,
   vigente 30 días.
7. **El encuestado responde el cuestionario completo** con ese token
   (`cuestionario/post_acceso` → `responder` → `post_responder`), las 46
   preguntas de GRII con respuestas variadas (no todo "Siempre", para que
   el resultado se viera realista) y ambas preguntas-filtro en "Sí".
8. **El administrador ve el resultado** en `administrador/tokens/8` (la
   aplicación aparece en "Aplicaciones respondidas") y en
   `resultados/individual/10` (calificación `95`, `muy_alto`, desglose por
   categoría y dominio).
9. Se confirmó en la bitácora del súper usuario (`superusuario/bitacora`)
   que las 4 acciones (`alta_administrador`, `alta_centro_trabajo`,
   `generar_token`, `consulta_resultado_individual`) quedaron registradas.
10. Se verificaron los dos guards de rol corregidos en 17.1: el
    administrador de prueba pidiendo `/superusuario` fue rebotado a
    `/administrador` (no al formulario público); el súper usuario de prueba
    pidiendo `/administrador/centros_trabajo` fue rebotado a
    `/superusuario`.

**Credenciales y token dejados sembrados para la revisión manual del
usuario** (ver también el mensaje final de esta pasada en el chat):
súper usuario `superadmin` / `INyT0jkl`; administrador `admintest1` /
`AdminTest123!`; token del centro "Museo Regional de Toluca" (id 8):
`2b7be7ba058d693f8f0ce58e4f9e8b29` (vigente 30 días desde 2026-09-23); la
aplicación de prueba ("Maria Lopez Encuestada", SP-FINAL-01) ya quedó
respondida con resultado calculado, así que ese token específico ya no
puede volver a usarse para responder (RF-11, una aplicación por token +
número de servidor público) — el usuario puede generar un token nuevo desde
el panel del administrador para probar el flujo del encuestado de nuevo si
lo quiere.
