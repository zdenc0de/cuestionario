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
