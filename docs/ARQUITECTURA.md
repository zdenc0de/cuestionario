# Arquitectura del sistema — Cuestionario NOM-035-STPS-2018

> Secretaría de Cultura y Turismo del Estado de México
> Bee Framework 1.5.8 · PHP 8.2 · MySQL/MariaDB · XAMPP

Este documento describe el layout de carpetas del proyecto y la responsabilidad
de cada módulo agregado para el sistema de cuestionarios NOM-035 (Guías de
Referencia II y III). Es un mapa de la arquitectura, **no** una guía de
diseño del instrumento: los valores de puntaje, mapeo reactivo→dominio→categoría
y umbrales se documentan en la fase de Diseño (ver los `TODO` marcados en el
código y en `Planificacion_Proyecto_Cuestionario_NOM-035 (1-2).md`).

Todo lo agregado en este esqueleto respeta el patrón MVC nativo de Bee y sus
convenciones de nombres; no se modificó ningún archivo del núcleo del
framework (`app/classes/*`), únicamente se agregaron archivos nuevos y se
extendieron los dos puntos de extensión que el propio framework designa para
ello: `app/functions/bee_custom_functions.php` y `templates/includes/styles.php`.

> **Actualización (hardening):** se agregó control de acceso por rol, guard de
> verbo HTTP en los formularios y se confirmaron dos decisiones de diseño
> (autenticación/roles y modelo de token). Ver secciones 7 y 8. Sigue sin
> haber lógica de calificación/puntaje — eso permanece en la fase de Diseño.

---

## 1. Cómo enruta Bee (contexto necesario para ubicar cada módulo)

Bee resuelve la URL como `/{controlador}/{método}/{parámetros...}` (ver
`app/classes/Bee.php::init_set_defaults()`), vía `.htaccess` → `index.php?uri=...`.
El controlador de errores por defecto es el que atrapa rutas o métodos no
encontrados (`app/config/bee_config.php` + `app/core/settings.php`).

- **Controlador** `xyzController` (`app/controllers/xyzController.php`) → clase
  `xyzController extends Controller implements ControllerInterface`.
- **Modelo** `xyzModel` (`app/models/xyzModel.php`) → clase `xyzModel extends Model`,
  con `public static $t1` como nombre de tabla y métodos estáticos de acceso a datos.
- **Vista** `templates/views/{controlador en minúsculas}/{vista}View.php`,
  renderizada por `View::render()` con el motor `bee` (PHP plano) o `twig`.
- El **autoloader** (`app/classes/Autoloader.php`) resuelve clases por sufijo
  buscando en `CLASSES`, `CONTROLLERS` y `MODELS`; no requiere `use`/namespaces.

---

## 2. Módulos agregados

### 2.1 Público (encuestado) — `cuestionarioController`

Archivo: [`app/controllers/cuestionarioController.php`](../app/controllers/cuestionarioController.php)
Vistas: [`templates/views/cuestionario/`](../templates/views/cuestionario/)

Responsabilidad: acceso por token, flujo de preguntas del cuestionario y envío
de respuestas. **No** usa `Auth::validate()` (no requiere cuenta de Bee); el
control de acceso es puramente por vigencia de `tokenModel`.

| Método | Vista | RF relacionado |
|---|---|---|
| `index()` | `accesoView.php` | Formulario de captura de token + identificación (RF-03) |
| `post_acceso()` | — | Valida el token (RF-11) |
| `responder($token)` | `cuestionarioView.php` | Flujo de reactivos con lógica condicional (RF-01, RF-02) |
| `post_responder()` | — | Persiste respuestas y dispara el cálculo de resultado (RF-04, RF-05, RF-06) |
| `gracias()` | `agradecimientoView.php` | Confirmación de envío |

### 2.2 Administrador (por centro de trabajo) — `administradorController`

Archivo: [`app/controllers/administradorController.php`](../app/controllers/administradorController.php)
Vistas: [`templates/views/administrador/`](../templates/views/administrador/)

Responsabilidad: alta de centros de trabajo, generación de tokens únicos con
vigencia y habilitación del cuestionario (RF-10). Requiere sesión de Bee Y
rol de contexto `administrador`, comprobado con `requiere_rol('administrador')`
al inicio del constructor (ver sección 7.1).

| Método | Vista | Propósito |
|---|---|---|
| `index()` | `indexView.php` | Tablero del administrador |
| `centros_trabajo()` / `post_centros_trabajo()` | `centrosTrabajoView.php` | Alta/listado de centros de trabajo |
| `tokens($centroTrabajoId)` / `post_tokens()` | `tokensView.php` | Generación/listado de tokens |
| `habilitar($centroTrabajoId)` | — | Habilita la aplicación del cuestionario |

### 2.3 Súper usuario (por Secretaría) — `superusuarioController`

Archivo: [`app/controllers/superusuarioController.php`](../app/controllers/superusuarioController.php)
Vistas: [`templates/views/superusuario/`](../templates/views/superusuario/)

Responsabilidad: gestión de administradores y consulta de la bitácora de
auditoría (RF-09, RF-12). Requiere sesión de Bee Y rol de contexto
`superusuario`, comprobado con `requiere_rol('superusuario')` al inicio del
constructor (ver sección 7.1).

| Método | Vista | Propósito |
|---|---|---|
| `index()` | `indexView.php` | Tablero del súper usuario |
| `administradores()` / `post_administradores()` | `administradoresView.php` | Alta/listado de administradores |
| `bitacora()` | `bitacoraView.php` | Consulta de auditoría |

### 2.4 Resultados y reportes — `resultadosController`

Archivo: [`app/controllers/resultadosController.php`](../app/controllers/resultadosController.php)
Vistas: [`templates/views/resultados/`](../templates/views/resultados/)

Responsabilidad: resultados individuales y agregados, reportes en PDF
(`BeePdf`), exportación a Excel y tablero con gráficas (RF-13, RF-14).
Compartido entre administrador y súper usuario — a propósito NO usa
`requiere_rol()` (exigiría un único rol), sino que **filtra el alcance de
datos** según el rol de quien consulta (ver `centrosTrabajoPermitidos()` /
`verificarAccesoCentroTrabajo()` en el controlador y sección 7.1):
administrador ve solo los centros de trabajo que dio de alta; súper usuario
ve todos los de su secretaría. Un `centroTrabajoId`/`aplicacionId` fuera de
ese alcance se deniega ("falla cerrado").

| Método | Vista/salida | Propósito |
|---|---|---|
| `individual($aplicacionId)` | `individualView.php` | Resultado de un encuestado |
| `agregado($centroTrabajoId)` | `agregadoView.php` | Resultado agregado del centro de trabajo |
| `tablero()` | `tableroView.php` | Gráficas en pantalla (Chart.js) |
| `datos_grafica($centroTrabajoId)` | JSON | Datos que alimentan el tablero |
| `pdf_individual($aplicacionId)` | PDF (`BeePdf`) | Usa `pdfIndividualView.php` como contenido |
| `pdf_agregado($centroTrabajoId)` | PDF (`BeePdf`) | Usa `pdfAgregadoView.php` como contenido |
| `excel($centroTrabajoId)` | Descarga | **Pendiente**: agregar `phpoffice/phpspreadsheet` a `composer.json` o exportar CSV |

> Las vistas `pdfIndividualView.php` y `pdfAgregadoView.php` **no** se renderizan
> con `View::render()`: se capturan con `ob_start()`/`ob_get_clean()` y se
> pasan como contenido a `new BeePdf($html, ...)`, que usa Dompdf internamente
> (por eso llevan su propio `<html>` con estilos embebidos).

---

## 3. Modelos (stubs)

Todos en `app/models/`, siguiendo el patrón `Model` de Bee (`$t1`, `insertOne()`,
`by_id()`, `update_by_id()`, `delete_by_id()`, más métodos de dominio propios).
Cada archivo documenta en comentario el esquema de columnas propuesto y marca
con `TODO` los datos que dependen de la fase de Diseño.

| Modelo | Tabla (`$t1`) | Responsabilidad |
|---|---|---|
| `secretariaModel` | `secretaria` | Dependencia titular (Secretaría de Cultura y Turismo) |
| `usuarioModel` | `usuario` | Relación de cuentas de Bee (`bee_users`) con rol/secretaría/centro de trabajo |
| `centroTrabajoModel` | `centro_trabajo` | Centros de trabajo; determina la guía aplicable (RF-00) |
| `tokenModel` | `token` | Tokens únicos con vigencia (RF-10, RF-11) |
| `guiaModel` | `guia` | Guía II / Guía III: reactivos, rangos y umbrales |
| `reactivoModel` | `reactivo` | Reactivos parametrizados por guía (dominio, categoría, polaridad) |
| `opcionRespuestaModel` | `opcion_respuesta` | Escala Likert de 5 opciones |
| `aplicacionModel` | `aplicacion` | Una instancia respondida del cuestionario |
| `respuestaModel` | `respuesta` | Valor elegido por reactivo dentro de una aplicación |
| `resultadoModel` | `resultado` | Calificación final, nivel de riesgo y desglose |
| `auditoriaModel` | `auditoria` | Bitácora de movimientos/consultas (RF-09, RF-12) |

Las tablas aún no existen en la base de datos (`db_beeframework.sql` no fue
modificado); se crearán en la fase de Desarrollo, ya sea con SQL directo o con
`TableSchema`/`Model::createTable()` (utilidad ya incluida en Bee).

---

## 4. Identidad visual

Archivo: [`assets/css/nom035-variables.css`](../assets/css/nom035-variables.css)

Contiene los custom properties de CSS (`--color-primario`, `--color-texto`,
`--color-cafe`, `--color-oro`, `--color-arena`, `--font-titulos`,
`--font-cuerpo`) confirmados con la Secretaría. Se carga desde
`templates/includes/styles.php` (el punto que Bee reserva para hojas de
estilo del proyecto), **debajo** de `main.css`, sin modificar ningún archivo
del núcleo.

---

## 5. Auditoría (bitácora)

Función auxiliar `registrar_auditoria()` agregada en
[`app/functions/bee_custom_functions.php`](../app/functions/bee_custom_functions.php)
(el archivo que el propio Bee reserva para funciones del proyecto). Inserta un
registro en `auditoriaModel`. **Pendiente** (fase de Desarrollo): invocarla
desde cada acción sensible de `administradorController`, `superusuarioController`
y `resultadosController`.

---

## 7. Hardening aplicado

Bee resuelve la ruta puramente por URL (ver sección 1); no ofrece por sí solo
ni verificación de rol ni distinción de verbo HTTP. Se agregaron tres
mecanismos para cerrar esos huecos, todos como funciones auxiliares en
`app/functions/bee_custom_functions.php` (el punto de extensión que el propio
Bee reserva para el proyecto) — no se tocó ninguna clase del núcleo.

### 7.1 Guard de rol — `requiere_rol()`

`Auth::validate()` únicamente comprueba que exista una sesión de Bee válida,
**no** el rol del usuario. `requiere_rol(string $rolRequerido)` encadena esa
validación con la comprobación del rol de contexto (ver decisión de diseño A,
sección 8) y deniega + redirige si cualquiera de las dos falla:

```php
function requiere_rol(string $rolRequerido)
{
  if (!Auth::validate()) { Flasher::new(...); Redirect::to('login'); }
  if (obtener_rol_usuario_actual() !== $rolRequerido) { Flasher::deny(2); Redirect::to(DEFAULT_CONTROLLER); }
  return true;
}
```

- `administradorController::__construct()` → `requiere_rol('administrador')`.
- `superusuarioController::__construct()` → `requiere_rol('superusuario')`.
- `resultadosController` es compartido por ambos roles, así que en vez de
  `requiere_rol()` filtra el **alcance de datos** por rol (ver sección 2.4):
  `centrosTrabajoPermitidos()` resuelve los `centro_trabajo.id` visibles para
  el usuario en sesión y `verificarAccesoCentroTrabajo()` deniega cualquier
  ID fuera de ese conjunto, en cada método (`individual`, `agregado`,
  `pdf_individual`, `pdf_agregado`, `excel`, `datos_grafica`).
- `registrar_auditoria()` también se actualizó para usar `obtener_usuario_actual()['id']`
  (el id de `usuarioModel`, la tabla de enlace) en vez de `get_user('id')`
  (que es el id nativo de `bee_users`), consistente con las demás FK del
  esquema (ej. `centro_trabajo.administrador_id`).

### 7.2 Guard de verbo HTTP — `requiere_metodo_post()`

Bee ejecuta el método indicado en la URL sin importar el verbo HTTP: visitar
`/administrador/post_tokens` por GET ejecutaría igual el método. Todo método
`post_*` de los controladores del proyecto invoca `requiere_metodo_post()` al
inicio, que deniega y redirige si `$_SERVER['REQUEST_METHOD'] !== 'POST'`:

- `cuestionarioController::post_acceso()` y `post_responder()`
- `administradorController::post_centros_trabajo()` y `post_tokens()`
- `superusuarioController::post_administradores()`

Los métodos de borrado/acción que **no** siguen la convención `post_*`
(`borrar_centro_trabajo()`, `revocar_token()`, `habilitar()`,
`borrar_administrador()`) no usan este guard: siguen el patrón ya existente
en Bee para esas acciones — enlace `GET` + token CSRF en query string, tal
como `adminController::borrar_usuario()` (`Csrf::validate($_GET['_t'])`) —
marcado con `TODO` para implementarse igual en la fase de Desarrollo.

Se verificó que el `action` de cada `<form>` apunte exactamente a su ruta
`post_*` correspondiente (`accesoView.php` → `cuestionario/post_acceso`,
`cuestionarioView.php` → `cuestionario/post_responder`,
`centrosTrabajoView.php` → `administrador/post_centros_trabajo`,
`tokensView.php` → `administrador/post_tokens`,
`administradoresView.php` → `superusuario/post_administradores`).

### 7.3 CSRF en el formulario público

El flujo público (`cuestionarioController`) ya incluía el token CSRF nativo
de Bee desde la primera versión del esqueleto: `accesoView.php` y
`cuestionarioView.php` usan `insert_inputs()` (helper nativo de
`bee_core_functions.php`) que agrega el campo oculto `csrf` con `CSRF_TOKEN`,
y `post_acceso()`/`post_responder()` lo validan con `Csrf::validate($_POST['csrf'] ?? '')`
antes de procesar — el mismo mecanismo que usa el resto del sistema (ej.
`adminController::post_usuarios()`, `loginController::post_login()`). No se
creó un mecanismo CSRF alterno.

---

## 8. Decisiones de diseño confirmadas (sin lógica de datos aún)

Estas dos decisiones ya están tomadas y documentadas en el código (ver los
docblocks de `usuarioModel` y `tokenModel`); lo que falta es implementar su
lógica de datos una vez existan las tablas (fase de Desarrollo).

### A. Autenticación y roles

El sistema **nativo** de Bee (`bee_users` + clase `Auth`) es la **única
fuente de verdad** de identidad y login. `usuarioModel` **no duplica
usuarios**: es una tabla de **enlace 1 a 1** con `bee_users` que agrega el
contexto que Bee no modela de forma nativa:

| Columna | Descripción |
| --- | --- |
| `bee_user_id` | FK única → `bee_users.id` |
| `rol` | `'superusuario'` \| `'administrador'` |
| `secretaria_id` | obligatorio si `rol = 'superusuario'` |
| `centro_trabajo_id` | obligatorio si `rol = 'administrador'` |

No se usan `bee_roles`/`bee_permisos` para este gate porque esas tablas
modelan roles y permisos granulares genéricos y **no tienen columna de enlace
a `bee_users`** en el esquema actual de Bee (`db_beeframework.sql`); el rol de
contexto de este proyecto es binario (administrador/superusuario) y vive en
`usuarioModel`. El punto único donde se resuelve esta relación son
`obtener_usuario_actual()` / `obtener_rol_usuario_actual()` en
`bee_custom_functions.php` — el resto del sistema no debe volver a consultar
`bee_users`/`usuarioModel` manualmente para esto.

### B. Modelo de token

**Un token por centro de trabajo por campaña, NO por persona.** Es multiuso
mientras esté vigente: cualquier encuestado del centro de trabajo puede
capturarlo para acceder.

| Columna | Descripción |
| --- | --- |
| `codigo` | único, generado por el sistema |
| `centro_trabajo_id` | FK → `centro_trabajo.id` |
| `fecha_inicio` / `fecha_fin` | vigencia |
| `estado` | `'activo'` \| `'expirado'` \| `'revocado'` (no incluye "usado": el token no se consume) |

La identidad de cada encuestado (nombre + `numero_servidor_publico`, en
`aplicacionModel`) es lo que lo distingue, no el token. **Restricción de
negocio:** una sola aplicación por combinación `(token_id, numero_servidor_publico)`
— `aplicacionModel::existe_para_token_y_servidor_publico()` la valida antes
de crear una nueva aplicación. **Acceso válido:** token existe, con
`estado = 'activo'` y vigente (`tokenModel::esta_vigente()`).

**"Habilitar cuestionario" (RF-10)** se define, de forma **provisional y
sujeta a validación con la Secretaría**, como: crear/activar un token vigente
para el centro de trabajo (`tokenModel::activo_por_centro_trabajo()`).
Mientras un centro de trabajo no tenga un token `'activo'` y vigente, se
considera sin cuestionario habilitado.

---

## 9. Lo que falta (fuera de este esqueleto)

Este esqueleto **no** incluye lógica de negocio ni de calificación. Quedan
pendientes para las fases de Diseño/Desarrollo (marcados con `TODO` en el
código):

- Tablas de puntaje oficiales (mapeo reactivo → dominio → categoría, polaridad).
- Umbrales de calificación final, por dominio y por categoría (Guía II y Guía III).
- Formato/longitud exacta del código de token (`tokenModel::generar_codigo()`)
  y vigencia por defecto de una campaña.
- Alta real de un administrador: creación en `bee_users` + registro de enlace
  en `usuarioModel` (`superusuarioController::post_administradores()`).
- CSRF en las acciones `GET` de borrado/revocación (`$_GET['_t']`, patrón de
  `adminController::borrar_usuario()`) — ver sección 7.2.
- Elección de librería de exportación a Excel (no está en `composer.json` actual).
- Creación de las tablas nuevas en la base de datos.

---

## 10. Verificación contra `docs/norma/` y `docs/DDL/` (2026-09-17)

Al incorporarse `docs/norma/Transcripcion_GuiaIII_NOM-035.md` (transcripción
oficial de la Guía III: 72 reactivos, polaridad, dominio/categoría/dimensión,
preguntas-filtro y umbrales) y `docs/DDL/ddl.sql` (DDL real del esquema), se
verificaron ambos documentos entre sí y contra todo el scaffolding de la
sección 3. Resultado: **la transcripción es internamente consistente**
(reconté las 72 filas: los 10 dominios suman 72, agrupan correctamente en las
5 categorías de la sección 7.2 del plan original, y las 25 dimensiones
declaradas coinciden exactamente con las que aparecen en la tabla). El DDL,
en cambio, tenía varios puntos que **no cuadraban con el scaffolding ya
escrito** — la mayoría porque los modelos se escribieron antes de que
existiera el DDL. Se corrigieron los que no requerían una decisión de
negocio; los que sí, quedaron pendientes de confirmar con el equipo.

### 10.1 Ya corregido en el código (alineación con `ddl.sql`, sin ambigüedad)

- **`opcionRespuestaModel`**: columnas reales son `etiqueta`/`posicion`, no
  `texto`/`valor`/`orden`. Se documentó explícitamente la fórmula de puntaje
  que estaba implícita en el DDL: `posicion` es sólo el orden de despliegue
  (0=Siempre...4=Nunca); el puntaje real depende de `reactivo.polaridad`
  (`normal` → `4 - posicion`; `invertida` → `posicion`). Nueva
  `reactivoModel::calcular_puntaje()` centraliza esta fórmula.
- **`reactivoModel`**: dominio/categoría/dimensión y pregunta-filtro NO son
  texto libre, son FK a tablas normalizadas (`categoria`, `dominio`,
  `dimension`, `pregunta_filtro`). No existe `es_condicional`: la
  condicionalidad es `pregunta_filtro_id IS NULL`. Métodos corregidos.
- **`guiaModel`**: columnas reales `trabajadores_min`/`trabajadores_max` (no
  `min_trabajadores`/`max_trabajadores` — esto rompía
  `por_numero_trabajadores()`). No existen columnas `umbrales_*_json`: los
  umbrales viven en la tabla normalizada `umbral`.
- **`tokenModel`**: `fecha_inicio`/`fecha_fin` son `DATE`, no `DATETIME` — se
  corrigió `esta_vigente()`/`activo_por_centro_trabajo()` para comparar contra
  `date('Y-m-d')` en vez de `now()` (comparar un `DATE` contra un `DATETIME`
  completo por cadena fallaba casi todo el día). El `estado` real es
  `ENUM('activo','inactivo')`, no `('activo','expirado','revocado')`.
- **`centroTrabajoModel`**: la columna real es `num_trabajadores`, no
  `numero_trabajadores` (corregido también en `post_centros_trabajo()` y
  `centrosTrabajoView.php`). `centro_trabajo` **no** tiene `guia_id`: la guía
  se resuelve con `guiaModel::por_numero_trabajadores()`, no se guarda aquí.
- **`usuarioModel`**: la relación con centro de trabajo NO es una columna en
  `usuario` — es al revés, `centro_trabajo.administrador_id` → `usuario.id`,
  lo que permite que un administrador tenga varios centros (1 a muchos).
  Corregido el docblock de la decisión de diseño A.
- **`aplicacionModel`**: columnas reales `nombre` (no `nombre_encuestado`),
  `atiende_clientes`/`es_jefe` (no `filtro_servicio_clientes`/`filtro_jefe_trabajadores`),
  `estado` es `'completada'` (femenino), no `'completado'`. No existe
  `fecha_envio` (se usa `updated_at`). Se agregó
  `centro_trabajo_id_de($aplicacionId)` (JOIN con `token`) porque `aplicacion`
  no tiene `centro_trabajo_id` propio — usado ahora por `resultadosController`.
- **`resultadoModel`**: `calificacion_final` es `INT UNSIGNED`, no
  `DECIMAL(6,2)` (los umbrales de la norma son enteros). No existen columnas
  `desglose_*_json`: el desglose vive en la tabla hija `resultado_detalle`.
- **`respuestaModel`**: no existe columna `valor`; el puntaje se deriva en el
  momento (`reactivoModel::calcular_puntaje()`), no se persiste en `respuesta`.
- **`auditoriaModel`** / `registrar_auditoria()`: no existen columnas `ip` ni
  `creado` (es `created_at`, con `DEFAULT CURRENT_TIMESTAMP`). `usuario_id` y
  `entidad` son `NOT NULL`; `registrar_auditoria()` ahora exige `$entidad` y
  no intenta el insert si no hay un usuario de enlace válido en sesión.

### 10.2 Decisiones tomadas por el equipo (2026-09-17)

1. **FK con choque de signo — RESUELTO.** Se corrigió `docs/DDL/ddl.sql`:
   `usuario.bee_user_id` pasó de `INT UNSIGNED` a `INT` (firmado), para
   coincidir exactamente con `bee_users.id` (`int(11)` firmado, ver
   `db_beeframework.sql`). El Bloque C (`ALTER TABLE ... fk_usuario_bee_user`)
   ya puede aplicarse sin el error 1215.
2. **`aplicacion` sin `centro_trabajo_id` propio — se deja como JOIN.** No se
   denormaliza en `ddl.sql`; `aplicacionModel::centro_trabajo_id_de()` (JOIN
   con `token`, ver 10.1) es la forma definitiva de resolverlo.
3. **Límite de trabajadores para Guía II — RESUELTO (2026-09-17, segunda
   revisión).** Confirmado contra el texto oficial de la norma (campo de
   aplicación): **16** es el valor correcto para `guia.trabajadores_min` de
   GRII (como ya decía `ddl.sql`); el plan original ("15 a 50") tenía el
   error, ya corregido ahí y en RF-00. Se agregó el caso que faltaba
   documentar: un centro de trabajo de **15 trabajadores o menos** tiene
   obligaciones ligeras y **no requiere aplicar ningún cuestionario** — no
   es un error del sistema, `guiaModel::por_numero_trabajadores()` regresa
   `null` a propósito en ese caso y así debe mostrarse en la UI ("no
   requiere cuestionario"), nunca como mensaje de error genérico. Ver el
   docblock de `guiaModel::por_numero_trabajadores()` y
   `centroTrabajoModel::determinar_guia()`, y el TODO agregado en
   `administradorController::post_centros_trabajo()`. El `min="1"` del
   input en `centrosTrabajoView.php` refleja que un centro de ≤15
   trabajadores es un dato válido, no debe bloquearse en el formulario.
4. **Columna `ip` en `auditoria` — AGREGADA (2026-09-17, segunda revisión).**
   Se agregó `ip VARCHAR(45) NULL` a `docs/DDL/ddl.sql`, al esquema de
   `auditoriaModel` y `registrar_auditoria()` ya la puebla con
   `get_user_ip()` (helper nativo de `bee_core_functions.php`). Justificación
   del cambio de postura: para un sistema con datos identificados y
   sensibles (RNF-01), la trazabilidad forense es barata de agregar ahora y
   cara de reconstruir después si se necesita más adelante.

### 10.3 Aún sin scaffold (tablas nuevas en `ddl.sql` sin modelo Bee)

Decisión del equipo (se reconfirmó en la segunda revisión): **no** generar
todavía los stubs. `ddl.sql` agrega 6 tablas para las que sigue sin existir
un modelo: `categoria`, `dominio`, `dimension`, `pregunta_filtro`, `umbral`,
`resultado_detalle`. **Esto no es un problema hoy, pero es la dependencia
inmediata en cuanto arranque la lógica de cálculo** (el motor de
calificación leerá `umbral` para asignar niveles de riesgo y escribirá
`resultado_detalle` referenciando `categoria`/`dominio`) — con el esquema ya
firme, el siguiente paso natural del proyecto es justamente el **seed del
instrumento** (cargar guías, categorías, dominios, dimensiones, reactivos,
preguntas-filtro y umbrales reales), momento en el que estos 6 modelos se
volverán necesarios. Se referencian
ya desde los TODO de `reactivoModel`, `guiaModel` y `resultadoModel` (para
que quede constancia de qué falta), pero los stubs en sí se crearán más
adelante, cuando el equipo lo pida.

---

## 11. Ajustes puntuales (2026-09-18)

Dos ajustes pequeños, sin lógica de calificación, ordenados por el equipo
para cerrar por completo lo dejado pendiente en la sección 10.

### 11.1 Columna `ip` en la bitácora de auditoría

La columna (`docs/DDL/ddl.sql`) y el docblock de esquema (`auditoriaModel`)
ya existían desde la revisión de la sección 10.2 punto 4. Lo que se ajustó
ahora fue **cómo se captura**, dentro de `registrar_auditoria()`
(`app/functions/bee_custom_functions.php`):

- Antes: `get_user_ip()` (helper de `bee_core_functions.php`), que confía en
  cabeceras `X-Forwarded-For`/`X-Forwarded`/`Client-IP` **antes** de caer a
  `REMOTE_ADDR` — esas cabeceras las puede falsificar el propio cliente
  cuando no hay un proxy real reescribiéndolas.
- Ahora: `$_SERVER['REMOTE_ADDR'] ?? null` directamente (la IP de la
  conexión TCP real). Sigue siendo un parámetro interno, no se agregó como
  argumento nuevo de la función. Es opcional: si no está disponible, se
  guarda `null` (la columna es `NULL`), sin que falle el `insertOne()`. El
  guard existente que exige un `usuario_id` de enlace válido no se tocó.
- Se dejó un `TODO` explícito para producción: si el sistema queda detrás de
  un proxy/reverse proxy, `REMOTE_ADDR` pasará a ser la IP del proxy, no la
  del cliente — en ese momento hay que resolver la IP real desde
  `X-Forwarded-For`, pero **sólo** si la petición viene de una lista cerrada
  de proxies de confianza, nunca confiando en ese header a ciegas.

### 11.2 Guard explícito para centros de trabajo de ≤15 trabajadores

Regla NOM-035 (Campo de aplicación), ya resuelta correctamente por
`guiaModel::por_numero_trabajadores()` desde la sección 10.2 punto 3: **≤15
trabajadores → ninguna guía aplica (no requiere cuestionario) / 16–50 →
Guía II / >50 → Guía III.** Se confirmó que el método ya regresaba `null`
de forma limpia para ese caso (no hizo falta tocar su lógica SQL); lo que
faltaba era que **quien lo consume actúe sobre ese `null`**, en vez de
seguir de largo:

- **`administradorController::post_centros_trabajo()`** — se agregó el
  guard real: resuelve `guiaModel::por_numero_trabajadores((int) $_POST['num_trabajadores'])`
  y, si regresa `null`, corta el flujo con
  `Flasher::error('Los centros de trabajo de hasta 15 trabajadores no
  requieren la aplicación de este cuestionario conforme a la NOM-035.')` y
  `Redirect::back()` — no llega a la parte de alta (que sigue como `TODO`,
  pendiente de la fase de Desarrollo).
- **`cuestionarioController::post_acceso()`** — el mismo guard, como
  defensa en profundidad para el acceso del encuestado, quedó documentado
  como `TODO` detallado (no implementado todavía): una vez resuelto el
  centro de trabajo del token, hay que volver a validar
  `guiaModel::por_numero_trabajadores()` sobre su `num_trabajadores`. En
  condiciones normales nunca debería activarse aquí (el guard de arriba ya
  impide crear esos centros de trabajo), pero cubre el caso de datos
  cargados directo en la base de datos sin pasar por el administrador.

---

## 13. Motor de calificación (2026-09-21)

Primera tarea de desarrollo real (`docs/HANDOFF_DESARROLLO.md` §5), sobre un
esquema ya sembrado y verificado en `db_beeframework` (2 guías, 9 categorías,
18 dominios, 45 dimensiones, 4 preguntas-filtro, 118 reactivos, 145 umbrales
— conteo confirmado en vivo contra la BD, no sólo contra el DDL). Con esto
queda **implementada de verdad** la lógica de cálculo; hasta ahora todo el
esqueleto sólo tenía placeholders.

### 13.1 Modelos nuevos

- **`categoriaModel`** y **`dominioModel`** — sólo lectura (`by_id`,
  `por_guia`/`por_categoria`). El motor de cálculo no los necesita para
  calcular (usa `reactivo.dominio_id`/`categoria_id` denormalizados, ver
  §3), son para nombrar renglones en reportes futuros.
- **`umbralModel`** — traduce una calificación a nivel de riesgo. Tres
  métodos públicos (`nivel_final`, `nivel_categoria`, `nivel_dominio`) sobre
  una sola implementación privada (`buscar_nivel()`) que aplica la
  convención `limite_inferior <= valor < limite_superior`. Es la única
  fuente de verdad de esta traducción — no se duplican los rangos en
  ningún otro lugar del código.
- **`resultadoDetalleModel`** — CRUD mínimo sobre la tabla hija de
  `resultado` (`insertOne`, `by_id`, `por_resultado`).

### 13.2 `resultadoModel::calcular_para_aplicacion($aplicacionId)`

Ya implementado (antes era un stub que regresaba `null`). Algoritmo (ver
también el docblock del método, que es la referencia más detallada):

1. `respuestaModel::por_aplicacion()` trae cada respuesta con su reactivo y
   su opción ya unidos (`polaridad`, `dominio_id`, `categoria_id`,
   `posicion`) — no hizo falta tocar ese método, ya traía justo lo necesario.
2. `reactivoModel::calcular_puntaje()` da el puntaje de cada respuesta
   (tampoco se tocó).
3. Suma en PHP (no en SQL) por dominio, por categoría y el total. Un
   reactivo omitido por un filtro simplemente no tiene fila en `respuesta`,
   así que no aporta a ninguna suma.
4. `umbralModel` traduce cada suma a nivel de riesgo.
5. Persistencia **atómica e idempotente**.

**Sobre la atomicidad — un hallazgo importante en el núcleo de Bee (sin
modificarlo):** `Db::query()` (`app/classes/Db.php`) hace auto-commit por
query cuando se le llama con las opciones por default (abre transacción,
ejecuta, cierra transacción) — pero su rama para `SELECT` hace `return`
**antes** de llegar al `commit()`, así que *cualquier* `SELECT` con opciones
por default deja la conexión con una transacción abierta sin cerrar. Esto
es invisible en el resto del sistema (cada request de Bee es de corta vida,
y la siguiente escritura por default termina cerrándola sin que nadie note
nada raro), pero se vuelve un problema real en cuanto un mismo método
necesita controlar su propia transacción de varias sentencias — como aquí.
`calcular_para_aplicacion()` ya hizo varias lecturas por default antes de
llegar a la parte de escritura (`aplicacionModel::by_id()`,
`respuestaModel::por_aplicacion()`, los `umbralModel::nivel_*()`), así que
al llegar ahí la conexión puede estar "en transacción" sin que el método
lo haya pedido. Se resuelve **dentro de `resultadoModel`, sin tocar
`Db.php`**: si `$link->inTransaction()` es verdadero antes de empezar, se
hace `commit()` de esa transacción colgada (no hay nada que perder, sólo
fueron lecturas) y luego sí se abre la transacción real que el método
controla explícitamente (`Db::connect()->beginTransaction()`/`commit()`/
`rollBack()`, con `['transaction' => false]` en cada `Model::query()`
individual para que no vuelvan a auto-comitear a medio camino).

**Idempotencia:** antes de insertar, se borra cualquier `resultado` previo
de la misma aplicación (`DELETE FROM resultado WHERE aplicacion_id=...`) —
el `ON DELETE CASCADE` de `resultado_detalle` (ya definido en `ddl.sql`) se
lleva su desglose viejo solo. Recalcular reemplaza, nunca duplica.

### 13.3 Verificación (`scripts/verificar_motor_calificacion.php`)

Script standalone (no es parte de la app, no agrega rutas ni controladores)
que se ejecuta por CLI (`php scripts/verificar_motor_calificacion.php`).
Arranca el mínimo del framework que necesitan los modelos (config,
autoloader, funciones) **sin** pasar por `Bee::fly()` (que despacharía un
controlador HTTP, no aplica en CLI); simula `$_SERVER['REMOTE_ADDR']` para
que `IS_LOCAL` tome las credenciales `LDB_*` correctamente.

Para cada guía (GRII y GRIII) crea una cadena de datos real y mínima
(`secretaria`→`centro_trabajo`→`token`→`aplicacion`→`respuesta`) y corre 3
casos, comparando siempre contra un valor esperado calculado por fórmula a
partir del conteo real de polaridad en la BD (no a mano con calculadora,
ni hardcodeado):

1. **Todo "Siempre", filtros en "Sí"** — verifica la calificación final Y
   cada renglón de dominio/categoría individualmente, más una recalculación
   inmediata para confirmar idempotencia (mismo número de renglones de
   detalle, no duplicados).
2. **Todo "Nunca", filtros en "Sí"** — caso inverso de polaridad.
3. **Filtros en "No"** (sólo reactivos obligatorios) — confirma que los
   reactivos condicionales (41–46/44–46 GRII, 65–68/69–72 GRIII) no suman.

Al final borra todo lo que insertó (`aplicacion` en cascada se lleva
`respuesta` + `resultado` + `resultado_detalle`; luego `token`,
`centro_trabajo`, `secretaria`), en un bloque `finally` para que corra
aunque alguna verificación falle — se puede ejecutar las veces que haga
falta sin dejar basura en la base de datos.

**Resultado de la corrida (2026-09-21):** 0 fallos, ambas guías, las 3
familias de caso, idempotencia confirmada, ningún nivel de riesgo `null`
(ninguna calificación cayó fuera de los rangos de `umbral`). Verificado
también que la limpieza dejó las 8 tablas operativas en 0 filas de nuevo.

### 13.4 Lo que falta (fuera de esta tarea)

- Wiring real de `cuestionarioController::post_responder()` para llamar
  `resultadoModel::calcular_para_aplicacion()` al enviar el cuestionario
  (RNF-06, cálculo en tiempo real) — sigue como `TODO`, es la "segunda
  tarea" del handoff (módulo de administrador) la que probablemente lo
  antecede en orden natural.
- UI de reportes (individual/agregado/tablero) que consuma `resultado` y
  `resultado_detalle` — explícitamente fuera de alcance en el handoff.
- `categoriaModel`/`dominioModel` sólo tienen los métodos mínimos usados
  hasta ahora; se ampliarán cuando los necesite el reporte.

---

## 14. Bootstrap del súper usuario y módulo de súper usuario (2026-09-23)

Segunda tarea de desarrollo real (`docs/HANDOFF_DESARROLLO.md` §3-4):
cuenta raíz + `superusuarioController` funcional.

### 14.1 Bootstrap de la cuenta raíz

**No se inventó ningún hash.** Se usó `get_new_password()`
(`app/functions/bee_core_functions.php`), que internamente hace
`password_hash($password . AUTH_SALT, PASSWORD_BCRYPT)` — exactamente la
misma fórmula que valida `loginController::post_login()`
(`password_verify($password.AUTH_SALT, $user['password'])`) y la que usa
`adminController::post_usuarios()` para dar de alta. Es la misma función
que usa `beeController::generate_user()` para las cuentas de prueba de Bee.

`scripts/generar_bootstrap_superusuario.php` (nuevo, reutilizable) arranca
el mínimo del framework (mismo patrón que `verificar_motor_calificacion.php`,
sección 13.3) y genera un bloque SQL con el hash real ya calculado contra el
`AUTH_SALT` vigente, más las credenciales en texto plano impresas aparte.
Se corrió una vez y su salida se guardó tal cual en
`scripts/bootstrap_superusuario.sql` — es el archivo que el usuario ejecuta
en phpMyAdmin (3 INSERT encadenados con `SET @variable = LAST_INSERT_ID()`:
`secretaria` → `bee_users` → `usuario` con `rol='superusuario'`).

**Verificación real, no sólo generación:** antes de entregar el SQL se
verificó el mecanismo completo contra el servidor local (XAMPP Apache +
MariaDB), con una cuenta de prueba **desechable** (no las credenciales
reales) creada directamente por SQL con el mismo algoritmo de hash, vía
`curl` con cookie jar (login real por HTTP, no una llamada directa a
`Auth::login()`): login exitoso (redirect a `/admin`, cookies persistentes
`bee__cookie_id`/`bee__cookie_tkn` seteadas por `BeeSession::new_session()`),
`GET /superusuario` responde 200 (el guard de rol pasa), y — encadenado —
la creación de un administrador de prueba, su login, y la bitácora, ver 14.2.
Al final se borraron **todos** los datos de prueba (bitácora incluida) y se
confirmó que las 9 tablas operativas quedaron en 0 filas de nuevo.

**Incidente durante la verificación (documentado, no oculto):** a media
prueba, MariaDB se quedó con dos consultas atoradas indefinidamente en
estado "Opening tables"/"Statistics" sobre `bee_users` y `options` —
`KILL` no las liberó. No hay evidencia de que lo haya causado el código de
este proyecto (son un `SELECT ... WHERE id = ?` sobre `bee_users` de
`BeeSession::authenticate()`, sin modificar, y una consulta a `options` que
tampoco toca este módulo); tiene toda la pinta de un atoro de MariaDB en
Windows (frecuente con antivirus interceptando los archivos `.ibd`). Se
reinició MariaDB y Apache (`taskkill` + los `.bat` de XAMPP) y se confirmó
que **ningún dato se perdió** (seed del instrumento y datos de prueba
intactos) antes de continuar. Vale la pena tenerlo en el radar si vuelve a
pasar durante el desarrollo.

### 14.2 `superusuarioController` — implementado

- **`administradores()`**: `usuarioModel::administradores_por_secretaria()`
  (ahora con JOIN a `bee_users` para traer `username`/`email`, antes era un
  TODO) filtrado por `obtener_usuario_actual()['secretaria_id']` — nunca
  por un id recibido del cliente.
- **`post_administradores()`**: mismas validaciones que
  `adminController::post_usuarios()` (regex de username/password, email +
  `is_temporary_email()`, duplicados contra `bee_users`) copiadas por
  consistencia, no reinventadas. Crea `bee_users` + enlace en `usuario` con
  `rol='administrador'` y `secretaria_id` = la del súper usuario en sesión
  (nunca la del formulario). Si el enlace falla tras crear la cuenta, borra
  la cuenta huérfana antes de fallar (no deja `bee_users` sin su
  `usuario` correspondiente). Registra `'alta_administrador'` en la
  bitácora con `entidad_id` = el id de enlace del nuevo administrador.
- **`borrar_administrador()`** — **NO hace `DELETE`, revoca el acceso.**
  Hallazgo real durante la implementación: `auditoria.usuario_id -> usuario.id`
  es `ON DELETE RESTRICT` a propósito (docs/DDL/ddl.sql) — el historial de
  auditoría de una cuenta no debe poder desaparecer borrándola. Como la
  propia alta de un administrador ya dejó una fila de auditoría con el
  súper usuario como actor, y cualquier acción futura del administrador
  quedaría igual, un `DELETE` físico de `usuario`/`bee_users` puede chocar
  con esa restricción en cuanto hay historial — y aunque no la hubiera,
  borrar la cuenta de alguien que sí actuó destruye el "quién" de esas
  acciones. La "baja" aquí es: sobrescribir la contraseña con un valor
  aleatorio que nadie conoce (`get_new_password()`, mismo algoritmo), la
  cuenta deja de poder iniciar sesión pero su fila y su historial se
  conservan. Verificado en vivo: el administrador de prueba pudo iniciar
  sesión antes de la revocación y **no pudo** después (mismo mensaje que
  credenciales inválidas). **Limitación conocida, documentada en el
  método:** no hay columna de estado (`activo`) en `usuario`/`bee_users`
  en el DDL actual, así que un administrador revocado sigue apareciendo en
  el listado sin marca visual — agregar esa columna requiere tocar
  `docs/DDL/ddl.sql`, fuera del alcance de esta tarea.
- **`bitacora()`**: nuevo `auditoriaModel::por_secretaria()` (JOIN
  `auditoria` → `usuario` → `bee_users`) filtrado por la secretaría del
  súper usuario en sesión.
- **Alcance por secretaría, verificado en vivo:** se creó una segunda
  secretaría de prueba con su propio súper usuario y se confirmó que ve un
  listado de administradores vacío y una bitácora vacía — no ve nada de la
  primera secretaría, a pesar de que ambas cuentas conviven en la misma
  base de datos.

### 14.3 Lo que falta (fuera de esta tarea)

- Columna de estado (`activo`) en `usuario`/`bee_users` para distinguir
  visualmente a un administrador con el acceso revocado en el listado
  (ver 14.2) — requiere una decisión de DDL, no se tomó unilateralmente.
- `editar_administrador()` sigue siendo un stub (no pedido por esta tarea).
- Módulo de administrador (alta de centros de trabajo, tokens) — "segunda
  tarea" explícita del handoff §6, para después.

---

## 15. Columna `usuario.estado` + módulo de administrador (2026-09-24)

### 15.1 Cierre de la limitación de la Pasada 7

- **`docs/DDL/ddl.sql`**: se agregó `estado ENUM('activo','inactivo') NOT NULL
  DEFAULT 'activo'` a la `CREATE TABLE usuario` (para instalaciones nuevas).
- **`scripts/alter_usuario_estado.sql`** (nuevo): el `ALTER TABLE` para la
  base ya existente — **el usuario lo ejecuta en phpMyAdmin, Claude no lo
  corrió contra ninguna base de datos**, ni siquiera la local de desarrollo
  (a diferencia de los datos de prueba desechables de otras pasadas, aquí
  se trata de un cambio de esquema y se respetó la instrucción explícita).
  Esto significa que la pieza de "marcar/filtrar por estado" de esta
  sección **no se pudo verificar en vivo** todavía — sólo se verificó con
  `php -l` y revisión de código; sí se verificó en vivo que la revocación
  de acceso (invalidar la contraseña) sigue funcionando exactamente igual
  que antes (no se rompió nada existente).
- **`superusuarioController::borrar_administrador()`**: ahora, además de
  invalidar la contraseña (ya suficiente por sí sola), intenta marcar
  `estado='inactivo'` — envuelto en su propio `try/catch` que ignora el
  error si la columna todavía no existe, para que la revocación real nunca
  dependa de que el `ALTER` ya se haya aplicado.
- **`usuarioModel::administradores_por_secretaria()`**: acepta un segundo
  parámetro opcional `$estado` para filtrar. **`superusuarioController::administradores()`**
  intenta usarlo si viene `?estado=activo|inactivo` en la URL, y si la
  consulta falla (columna inexistente) se degrada automáticamente a mostrar
  todos sin filtro — nunca truena la página.
- **`administradoresView.php`**: columna "Estado" con badge
  verde/gris + tres enlaces de filtro (Todos/Activos/Inactivos). Si
  `$admin->estado` no viene (columna no aplicada todavía), se asume
  `'activo'` de forma segura (`?? 'activo'`), sin warnings.

### 15.2 Módulo de administrador — implementado

Todo verificado en vivo con `curl` contra el servidor local (mismo método
que la Pasada 7), con datos desechables, limpiados al final.

- **`centroTrabajoModel`**: sin cambios de código — ya tenía todo lo
  necesario desde la Pasada 3/4 (`insertOne`, `por_administrador()`,
  y la guía se resuelve con `guiaModel::por_numero_trabajadores()`, nunca
  se guarda en la tabla).
- **`tokenModel::generar_codigo()`**: implementado —
  `bin2hex(random_bytes(16))` (128 bits de entropía), no `random_password()`
  (usa `rand()`, no apto para una credencial de acceso), con verificación de
  colisión contra `by_codigo()` antes de regresar.
- **`administradorController::centros_trabajo()`**: lista
  `centroTrabajoModel::por_administrador()` y enriquece cada fila con la
  guía ya resuelta (una sola vez en el controlador, no N+1 en la vista).
- **`post_centros_trabajo()`**: `secretaria_id`/`administrador_id` SIEMPRE
  del usuario en sesión, nunca de `$_POST`. Validación de entero positivo
  (`ctype_digit`). El guard NOM-035 (≤15 → sin campaña, con el mensaje
  exacto pedido) ya existía desde antes (Pasada 5); ahora si pasa, sí
  persiste el centro. Verificado en vivo: 15 trabajadores → rechazado, no
  se crea nada; 20 trabajadores → creado, aparece en el listado con badge
  `GRII`; `num_trabajadores=abc` → rechazado con el mensaje de validación.
- **`tokens()` / `post_tokens()` / `revocar_token()`**: alcance verificado
  en tres niveles — el centro debe pertenecer al administrador en sesión
  (`centro_trabajo.administrador_id === usuario.id`), no sólo a su
  secretaría (más estricto que el súper usuario, que sí comparte
  secretaría entre administradores). `post_tokens()` agrega campos
  `fecha_inicio`/`fecha_fin` al formulario (antes no existían) con
  validación de fechas válidas + `fecha_fin >= fecha_inicio`. Verificado en
  vivo: token generado con código de 32 hex, `tokenModel::esta_vigente()`
  regresa `true` de inmediato; `fecha_fin` anterior a `fecha_inicio` →
  rechazado, no se crea; revocado → `estado='inactivo'` y
  `esta_vigente()` pasa a `false`.
- **Auditoría**: `alta_centro_trabajo`, `generar_token`, `revocar_token`
  registrados — confirmado leyendo la bitácora real del súper usuario de
  prueba tras la corrida.
- **Alcance por administrador, verificado en vivo (el punto más estricto
  del criterio de aceptación):** se crearon DOS administradores de prueba
  en la **misma** secretaría (a propósito, para probar el caso más
  exigente) — el segundo no vio el centro de trabajo del primero en su
  listado, y al pedir `/administrador/tokens/{id}` del centro ajeno **por
  URL directa**, fue rechazado y redirigido — no basta con que la vista no
  muestre el enlace, el controlador también lo bloquea.

### 15.3 Lo que falta (fuera de esta tarea)

- Verificación en vivo de `usuario.estado` (13.2.1) — pendiente de que el
  usuario aplique `scripts/alter_usuario_estado.sql`.
- `borrar_centro_trabajo()` y `habilitar()` siguen como stubs — no los pidió
  esta tarea (que pide generar tokens directamente vía `tokens()`/`post_tokens()`,
  ya cubre el caso de uso de "habilitar").
- `editar_centro_trabajo()` sigue como stub.
