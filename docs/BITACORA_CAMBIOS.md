# Bitácora de cambios — Cuestionario NOM-035 (sesiones con Claude)

> Documento de auditoría: enumera **todos** los archivos creados o modificados
> por el agente Claude en este proyecto hasta el 2026-09-17, en qué momento
> (de 4 pasadas de trabajo) y **por qué**, para que otro agente/desarrollador
> pueda revisarlo contra el código real. No repite la explicación funcional
> de cada módulo (eso ya está en `docs/ARQUITECTURA.md`); aquí el foco es
> **el cambio puntual y su justificación**.
>
> Contexto de repositorio en el momento de escribir esto: rama `main`,
> HEAD en `e73100a`. Buena parte de la Pasada 1 y 2 ya está commiteada
> (commits `04768f0`, `9462945`, `76c8476`, `348ffda`, `e73100a`); las
> pasadas 3 y 4 (verificación contra `docs/norma/`/`docs/DDL/` y ajustes de
> una segunda revisión) están en el working tree, sin commitear todavía.
> Claude no ejecutó ningún comando de Git en ningún momento; los commits
> fueron corridos por el usuario con comandos que Claude únicamente redactó
> como texto.

---

## 0. Las 3 pasadas de trabajo

| Pasada | Disparador | Qué produjo |
|---|---|---|
| **1. Esqueleto inicial** | "crea el esqueleto de carpetas, archivos y arquitectura del sistema" | 11 modelos, 4 controladores, 15 vistas, 1 hoja de tokens CSS, 2 docs. Sin lógica de negocio. |
| **2. Hardening** | "guard de rol, verbo HTTP, CSRF público + documentar 2 decisiones de diseño" | `requiere_rol()`, `requiere_metodo_post()`, ajustes a `tokenModel`/`usuarioModel`/`aplicacionModel`, secciones 7-8 de `ARQUITECTURA.md`. |
| **3. Verificación** | "comprueba que `docs/norma/Transcripcion_GuiaIII_NOM-035.md` y `docs/DDL/ddl.sql` cumplan y no generen problemas" | Realineación de nombres de columnas en los 11 modelos + 1 fix en `ddl.sql` + sección 10 de `ARQUITECTURA.md`. |
| **4. Segunda revisión** | Un segundo agente revisó la Pasada 3 y propuso 4 ajustes antes de commitear | Límite de Guía II resuelto (16, con el caso ≤15 documentado), columna `ip` agregada a `auditoria`, nota sobre `secretaria.logo`, refuerzo de la dependencia hacia los 6 modelos faltantes. |

En las 4 pasadas se respetaron las mismas restricciones: **no** se ejecutó
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

### 1.7 `opcionRespuestaModel.php`
- **Pasada 1:** creado con columnas `texto`/`valor`/`orden`.
- **Pasada 3:** corregido a `etiqueta`/`posicion` (nombres reales de
  `ddl.sql`) y se documentó explícitamente, en el docblock de la clase, que
  `posicion` es sólo el orden de despliegue (0=Siempre...4=Nunca) y **no** el
  puntaje — el puntaje depende de `reactivo.polaridad` (ver 1.6).
  **Por qué:** sin esa aclaración, alguien podría asumir que `posicion` ya
  es el puntaje final y calcular mal RF-05 para los reactivos de polaridad
  `'invertida'`.

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

### 2.3 `superusuarioController.php` (rol `superusuario`)
- **Pasada 1:** creado con `index()`, `administradores()`/`post_administradores()`,
  `bitacora()`, `editar_administrador()`, `borrar_administrador()`. Guard
  inicial: sólo `Auth::validate()`.
- **Pasada 2:** guard cambiado a `requiere_rol('superusuario')` (misma razón
  que 2.2). `requiere_metodo_post()` agregado a `post_administradores()`
  (único método `post_*` real; `borrar_administrador()` se dejó con el
  patrón GET + CSRF por query string, misma razón que en 2.2).
- **Pasada 3:** sin cambios de código.
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
- No se crearon los 6 modelos para `categoria`, `dominio`, `dimension`,
  `pregunta_filtro`, `umbral`, `resultado_detalle` (el usuario dijo
  explícitamente "no, por ahora solo el reporte" en la Pasada 3, y se
  reconfirmó en la Pasada 4) — **son la dependencia inmediata del seed del
  instrumento**, que es el siguiente paso natural del proyecto (ver
  `ARQUITECTURA.md` sección 10.3).
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

## 10. Restricciones respetadas en las 4 pasadas

- **Cero comandos de Git ejecutados por Claude.** Cuando el usuario pidió
  los 6 commits segmentados, Claude entregó los comandos como texto para que
  el usuario los corriera él mismo; nunca se invocó `git commit`/`git add`
  desde una herramienta de Claude.
- **Cero archivos de `app/classes/*` modificados.** Todo lo agregado usa las
  clases del núcleo tal cual existen (`Controller`, `Model`, `Auth`, `Csrf`,
  `Flasher`, `Redirect`, `PaginationHandler`, `BeePdf`, etc.), nunca se
  editó su código fuente.
- **Los dos únicos archivos "existentes" que se modificaron** fueron los que
  el propio Bee reserva para el proyecto: `app/functions/bee_custom_functions.php`
  y `templates/includes/styles.php` (secciones 4 y 5).
- **`docs/DDL/ddl.sql`** se modificó dos veces (Pasada 3 y Pasada 4), ambas
  con autorización explícita pedida antes de aplicar el cambio (sección 7).
- **`Planificacion_Proyecto_Cuestionario_NOM-035 (1-2).md`** se tocó por
  primera vez en la Pasada 4 (3 líneas: el límite de la Guía II y RF-00),
  también para corregir un dato que el segundo agente confirmó como
  erróneo contra el texto oficial de la norma — no por iniciativa unilateral
  de Claude sin respaldo.
