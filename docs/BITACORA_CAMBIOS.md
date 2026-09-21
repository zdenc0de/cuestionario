# Bitácora de cambios — Cuestionario NOM-035 (sesiones con Claude)

> Documento de auditoría: enumera **todos** los archivos creados o modificados
> por el agente Claude en este proyecto hasta el 2026-09-21, en qué momento
> (de 6 pasadas de trabajo) y **por qué**, para que otro agente/desarrollador
> pueda revisarlo contra el código real. No repite la explicación funcional
> de cada módulo (eso ya está en `docs/ARQUITECTURA.md`); aquí el foco es
> **el cambio puntual y su justificación**.
>
> Contexto de repositorio en el momento de escribir esto: rama `main`, HEAD
> en `9ca9368` ("feat(docs): add initial handoff document for NOM-035
> project" — `docs/HANDOFF_DESARROLLO.md`, no es de Claude). Las pasadas 1–5
> y el ajuste de `DEFAULT_CONTROLLER` ya están commiteados (hasta
> `f6ee735`/`ebe6efd`). La Pasada 6 (este documento) está en el working
> tree, sin commitear todavía. Claude no ejecutó ningún comando de Git en
> ningún momento; los commits fueron corridos por el usuario con comandos
> que Claude únicamente redactó como texto.

---

## 0. Las 6 pasadas de trabajo

| Pasada | Disparador | Qué produjo |
|---|---|---|
| **1. Esqueleto inicial** | "crea el esqueleto de carpetas, archivos y arquitectura del sistema" | 11 modelos, 4 controladores, 15 vistas, 1 hoja de tokens CSS, 2 docs. Sin lógica de negocio. |
| **2. Hardening** | "guard de rol, verbo HTTP, CSRF público + documentar 2 decisiones de diseño" | `requiere_rol()`, `requiere_metodo_post()`, ajustes a `tokenModel`/`usuarioModel`/`aplicacionModel`, secciones 7-8 de `ARQUITECTURA.md`. |
| **3. Verificación** | "comprueba que `docs/norma/Transcripcion_GuiaIII_NOM-035.md` y `docs/DDL/ddl.sql` cumplan y no generen problemas" | Realineación de nombres de columnas en los 11 modelos + 1 fix en `ddl.sql` + sección 10 de `ARQUITECTURA.md`. |
| **4. Segunda revisión** | Un segundo agente revisó la Pasada 3 y propuso 4 ajustes antes de commitear | Límite de Guía II resuelto (16, con el caso ≤15 documentado), columna `ip` agregada a `auditoria`, nota sobre `secretaria.logo`, refuerzo de la dependencia hacia los 6 modelos faltantes. |
| **5. Ajustes puntuales** | Prompt de "mi agente": afinar la captura de `ip` y agregar el guard funcional de ≤15 trabajadores | `registrar_auditoria()` deja de usar `get_user_ip()` (confía en cabeceras falsificables) y usa `$_SERVER['REMOTE_ADDR']`; guard real en `administradorController::post_centros_trabajo()`; TODO detallado del mismo guard en `cuestionarioController::post_acceso()`; sección 11 de `ARQUITECTURA.md`. |
| **6. Motor de calificación** | `docs/HANDOFF_DESARROLLO.md` §5 — primera tarea de desarrollo real | 4 modelos nuevos (`categoriaModel`, `dominioModel`, `umbralModel`, `resultadoDetalleModel`), `resultadoModel::calcular_para_aplicacion()` implementado de verdad (ya no es un stub), script de verificación, corrida y confirmada contra la BD real (0 fallos), sección 13 de `ARQUITECTURA.md`. |

En las 6 pasadas se respetaron las mismas restricciones: **no** se ejecutó
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
- ~~No se crearon los 6 modelos para `categoria`, `dominio`, `dimension`,
  `pregunta_filtro`, `umbral`, `resultado_detalle`~~ — **4 de 6 creados en
  la Pasada 6** (`categoriaModel`, `dominioModel`, `umbralModel`,
  `resultadoDetalleModel`, ver sección 12.1), porque el handoff de
  Desarrollo los pidió como parte de la primera tarea (motor de
  calificación). Siguen sin modelo `dimensionModel` y `preguntaFiltroModel`
  — no los necesitó el motor de cálculo (usa `reactivo.dimension_id`/
  `pregunta_filtro_id` directamente); se crearán cuando haga falta nombrar
  esos niveles en algún reporte.
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

## 13. Restricciones respetadas en las 6 pasadas

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
  (secciones 4 y 5). La Pasada 6 no agregó un tercero: `scripts/` es una
  carpeta nueva, fuera de `app/` y `templates/`, sin rutas ni controlador.
- **`docs/DDL/ddl.sql`** se modificó dos veces (Pasada 3 y Pasada 4), con
  autorización explícita en ambas. En la Pasada 6 se leyó como fuente de
  verdad del esquema y **no** se tocó — todo lo que necesitaba ya estaba.
- **`Planificacion_Proyecto_Cuestionario_NOM-035 (1-2).md`** se tocó por
  primera vez en la Pasada 4 (3 líneas: el límite de la Guía II y RF-00),
  también para corregir un dato que el segundo agente confirmó como
  erróneo contra el texto oficial de la norma — no por iniciativa unilateral
  de Claude sin respaldo.
- **`php -l` en cada archivo tocado, en cada pasada, incluida ésta** (6
  modelos + 1 script).
