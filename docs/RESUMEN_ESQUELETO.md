# Resumen del esqueleto generado — Cuestionario NOM-035-STPS-2018

Este documento resume lo construido en esta sesión: **únicamente scaffolding**
(stubs/boilerplate siguiendo las convenciones de Bee Framework 1.5.8), sin
lógica de negocio. El detalle de responsabilidades por módulo está en
[`docs/ARQUITECTURA.md`](ARQUITECTURA.md).

No se ejecutó ningún comando de Git ni se modificó ningún archivo del núcleo
de Bee (`app/classes/*`); todo lo agregado son archivos nuevos, salvo dos
extensiones puntuales a los archivos que el propio framework reserva para el
proyecto (detalladas más abajo).

> **Segunda pasada (hardening + documentación, sin lógica de calificación):**
> se agregó guard de rol (`requiere_rol()`), guard de verbo HTTP
> (`requiere_metodo_post()`) y se confirmó que el CSRF del formulario público
> ya usaba el mecanismo nativo de Bee. También se documentaron dos decisiones
> de diseño (autenticación/roles y modelo de token). Ver sección 0.

---

## 0. Segunda pasada — hardening y decisiones de diseño

### 0.1 Guard de rol

`Auth::validate()` sólo comprueba que exista sesión, no el rol. Se agregaron
tres funciones en `app/functions/bee_custom_functions.php`:
`obtener_usuario_actual()`, `obtener_rol_usuario_actual()` y
`requiere_rol(string $rol)` (valida sesión + rol, deniega/redirige si falla).

- `administradorController::__construct()` → `requiere_rol('administrador')`
- `superusuarioController::__construct()` → `requiere_rol('superusuario')`
- `resultadosController` (compartido por ambos roles) filtra en su lugar el
  **alcance de datos** por rol: `centrosTrabajoPermitidos()` +
  `verificarAccesoCentroTrabajo()`, con "falla cerrado" si el
  `centroTrabajoId`/`aplicacionId` solicitado no pertenece al usuario.
- `registrar_auditoria()` se ajustó para usar el id de `usuarioModel` (tabla
  de enlace) en vez del id nativo de `bee_users`, consistente con las demás FK.

### 0.2 Guard de verbo HTTP

Bee ejecuta el método de la URL sin distinguir GET de POST. Se agregó
`requiere_metodo_post()` (deniega/redirige si `$_SERVER['REQUEST_METHOD'] !== 'POST'`)
e invocada al inicio de cada método `post_*`:
`cuestionarioController::post_acceso()`/`post_responder()`,
`administradorController::post_centros_trabajo()`/`post_tokens()`,
`superusuarioController::post_administradores()`. Se verificó que cada
`<form action="...">` apunte exactamente a su ruta `post_*`. Los métodos de
borrado que no siguen la convención `post_*` (`borrar_centro_trabajo()`,
`revocar_token()`, `habilitar()`, `borrar_administrador()`) quedan marcados
con `TODO` para seguir el patrón `GET` + `Csrf::validate($_GET['_t'])` que ya
usa `adminController::borrar_usuario()`, en vez de este guard.

### 0.3 CSRF en el formulario público

Ya estaba correcto desde la primera versión: `accesoView.php` y
`cuestionarioView.php` usan `insert_inputs()` (helper nativo de Bee) para el
campo oculto `csrf`, y `post_acceso()`/`post_responder()` lo validan con
`Csrf::validate($_POST['csrf'] ?? '')`. No se creó un mecanismo nuevo.

### 0.4 Decisiones de diseño documentadas

- **A. Autenticación/roles:** `bee_users` + `Auth` es la única fuente de
  verdad de login. `usuarioModel` no duplica usuarios: es una tabla de
  enlace 1 a 1 (`bee_user_id`, `rol`, `secretaria_id`, `centro_trabajo_id`).
- **B. Modelo de token:** un token por centro de trabajo por campaña (no por
  persona), multiuso durante su vigencia (`codigo`, `centro_trabajo_id`,
  `fecha_inicio`, `fecha_fin`, `estado`). Restricción: una sola aplicación
  por `(token_id, numero_servidor_publico)`. Acceso válido = token existe +
  activo + vigente. "Habilitar cuestionario" = crear/activar un token
  vigente para el centro (definición provisional).

Detalle completo en [`docs/ARQUITECTURA.md`](ARQUITECTURA.md) secciones 7 y 8.

---

## 1. Controladores nuevos (`app/controllers/`)

| Archivo | Módulo | Autenticación |
| --- | --- | --- |
| `cuestionarioController.php` | Público (encuestado): acceso por token, flujo del cuestionario, envío | Ninguna (control por vigencia del token); CSRF + guard POST en `post_acceso()`/`post_responder()` |
| `administradorController.php` | Administrador (por centro de trabajo): alta de centros, generación de tokens, habilitar cuestionarios | `requiere_rol('administrador')` |
| `superusuarioController.php` | Súper usuario (por Secretaría): gestión de administradores, bitácora de auditoría | `requiere_rol('superusuario')` |
| `resultadosController.php` | Resultados y reportes: individual/agregado, PDF (`BeePdf`), Excel, tablero con gráficas | `Auth::validate()` + alcance de datos filtrado por rol |

## 2. Modelos nuevos (`app/models/`)

`secretariaModel`, `usuarioModel`, `centroTrabajoModel`, `tokenModel`,
`guiaModel`, `reactivoModel`, `opcionRespuestaModel`, `aplicacionModel`,
`respuestaModel`, `resultadoModel`, `auditoriaModel`.

Cada uno documenta en comentario el esquema de columnas propuesto (marcado
`TODO` donde depende de las tablas oficiales de la norma que la Secretaría
tiene en físico) e incluye los métodos de acceso a datos estándar de Bee
(`insertOne`, `by_id`, `update_by_id`, `delete_by_id`) más métodos de dominio
específicos (ej. `centroTrabajoModel::determinar_guia()`,
`tokenModel::esta_vigente()`, `resultadoModel::calcular_para_aplicacion()`).
`usuarioModel` y `tokenModel` se actualizaron en la segunda pasada para
reflejar las decisiones de diseño A y B (sección 0.4): `usuarioModel::by_bee_user_id()`
y `tokenModel::by_codigo()`/`activo_por_centro_trabajo()`/`revocar()`.

## 3. Vistas nuevas (`templates/views/`)

- `cuestionario/` → `accesoView.php`, `cuestionarioView.php`, `agradecimientoView.php`
- `administrador/` → `indexView.php`, `centrosTrabajoView.php`, `tokensView.php`
- `superusuario/` → `indexView.php`, `administradoresView.php`, `bitacoraView.php`
- `resultados/` → `indexView.php`, `individualView.php`, `agregadoView.php`,
  `tableroView.php`, `pdfIndividualView.php`, `pdfAgregadoView.php`

Las vistas de administrador/superusuario/resultados reutilizan el layout de
dashboard existente de Bee (`templates/includes/admin/dashboardTop.php` /
`dashboardBottom.php`, como ya hace `usuariosView.php`). Las vistas públicas
reutilizan `templates/includes/header.php` / `navbar.php` / `footer.php`.
Las vistas `pdf*View.php` son plantillas de contenido para `BeePdf` (Dompdf),
no se renderizan con `View::render()`.

## 4. Identidad visual

- **Nuevo:** `assets/css/nom035-variables.css` con los tokens confirmados:
  `--color-primario`, `--color-texto`, `--color-cafe`, `--color-oro`,
  `--color-arena`, `--font-titulos`, `--font-cuerpo`.
- **Extendido:** `templates/includes/styles.php` (un `<link>` agregado debajo
  de `main.css`, en la línea que el propio archivo marca para "estilos
  personalizados").

## 5. Auditoría y guards de acceso

- **Extendido:** `app/functions/bee_custom_functions.php` (el archivo que Bee
  reserva para funciones del proyecto) con:
  `registrar_auditoria($accion, $entidad, $entidadId, $detalle)` (inserta en
  `auditoriaModel`, pendiente invocarla desde cada acción sensible en la fase
  de Desarrollo), `obtener_usuario_actual()`, `obtener_rol_usuario_actual()`,
  `requiere_rol($rol)` y `requiere_metodo_post()` (ver sección 0).

## 6. Documentación

- `docs/ARQUITECTURA.md` — layout de carpetas y responsabilidad de cada módulo.
- `docs/RESUMEN_ESQUELETO.md` — este archivo.

---

## 7. Lo que queda pendiente (no incluido a propósito)

Todo lo marcado `TODO` en el código, principalmente:

- Tablas de puntaje oficiales de la norma (mapeo reactivo → dominio →
  categoría, polaridad, umbrales de Guía II y Guía III) — corresponden a la
  fase de Diseño (04/09–18/09 según el cronograma del plan de proyecto).
- Creación física de las tablas nuevas en MySQL (no se tocó `db_beeframework.sql`).
- Lógica real de cada método (actualmente todos responden con
  `Flasher::error('Funcionalidad pendiente de implementación...')` o `return null/false`).
- Alta real de un administrador (`bee_users` + enlace en `usuarioModel`).
- CSRF en las acciones `GET` de borrado/revocación (`$_GET['_t']`), pendiente
  igual que el resto de su lógica (ver sección 0.2).
- Librería de exportación a Excel (no está en `app/composer.json` actual).

## 8. Verificación realizada

Todos los archivos PHP nuevos y modificados pasaron `php -l` (sin errores de
sintaxis). No se corrió ningún comando de Git ni se realizaron commits.
