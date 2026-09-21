# Handoff a Desarrollo — Sistema NOM-035 (Secretaría de Cultura y Turismo)

> Documento de arranque para el agente de desarrollo. Lee esto **primero** y
> luego los documentos que referencia. Objetivo: que entiendas de dónde parte
> el proyecto, cuál es el estado real hoy, y cuál es la primera tarea.
> Fecha de handoff: 2026-09-21.

---

## 1. Qué es el proyecto (en una frase)

Sistema web que digitaliza y **califica automáticamente** los cuestionarios de
la **NOM-035-STPS-2018** (Guías de Referencia II y III) para la Secretaría de
Cultura y Turismo del Estado de México. Stack: **PHP 8.2 · Bee Framework 1.5.8
(MVC) · MySQL/MariaDB · XAMPP**.

---

## 2. Documentación que DEBES leer antes de tocar código

Toda la verdad del proyecto está en el repo. Orden de lectura:

1. `docs/Modelo_ER_Cuestionario_NOM-035.md` — modelo de datos y, sobre todo, la
   **sección 5** (algoritmo de calificación: polaridad, ruta de cálculo, umbrales).
2. `docs/DDL/ddl.sql` — el esquema real. **Fuente de verdad de nombres de
   columnas y tipos.** Si algo en el código no coincide con esto, gana el DDL.
3. `docs/norma/Transcripcion_GuiaII_NOM-035.md` y
   `docs/norma/Transcripcion_GuiaIII_NOM-035.md` — el instrumento oficial
   (reactivos, polaridad, mapeo a dominio/categoría, umbrales).
4. `docs/ARQUITECTURA.md` — layout de módulos, convenciones de Bee, guards ya
   implementados (rol, verbo HTTP, CSRF).
5. `docs/BITACORA_CAMBIOS.md` — historial de cambios y por qué se hicieron.

---

## 3. Estado real HOY (lo que ya existe y funciona)

- **Base de datos sembrada y verificada.** El DDL y el seed **ya se ejecutaron**
  en la base `db_beeframework`. Existen las 17 tablas del sistema y el
  instrumento está cargado completo y verificado:
  - 2 guías, 9 categorías, 18 dominios, 45 dimensiones, 4 preguntas-filtro.
  - **118 reactivos** (46 GRII + 72 GRIII), con su polaridad y su mapeo a
    dimensión/dominio/categoría.
  - **145 umbrales** (10 finales + 45 por categoría + 90 por dominio).
  - Verificado: el conteo de reactivos por dominio coincide con la norma, y cada
    combinación de umbral tiene sus 5 niveles de riesgo.
- **Esqueleto + hardening ya construidos** (pasadas previas): 11 modelos, 4
  controladores, 15 vistas, guards de rol/verbo/CSRF, función
  `registrar_auditoria()`. Todo alineado a los nombres reales del DDL. **Sin
  lógica de negocio todavía**: los métodos devuelven placeholders
  (`Flasher::error('Funcionalidad pendiente...')` o `return null/false`).

### 3.1 Decisiones de diseño ya tomadas (NO reabrir)

- **A. Autenticación/roles:** `bee_users` + `Auth` nativo de Bee es la única
  fuente de login. `usuarioModel` es tabla de **enlace** (`bee_user_id`, `rol`,
  `secretaria_id`), NO duplica usuarios. Ojo: `bee_user_id` es `INT` **firmado**
  (coincide con `bee_users.id`). Las FK del sistema apuntan a `usuario.id`, no a
  `bee_users.id`.
- **B. Token:** uno por centro de trabajo por campaña, multiuso durante su
  vigencia (`codigo`, `centro_trabajo_id`, `fecha_inicio`, `fecha_fin`, `estado`
  ENUM `activo`/`inactivo`; las fechas son `DATE`, comparar contra
  `date('Y-m-d')`, no contra `now()`). Regla: **una sola aplicación por
  `(token_id, numero_servidor_publico)`**.
- **Selección de guía por tamaño:** ≤15 no aplica; 16–50 → GRII; >50 → GRIII.
  `guiaModel::por_numero_trabajadores()` devuelve `null` para ≤15 (ya hay guard
  que muestra "no requiere el cuestionario").
- **Polaridad calculada, no almacenada:** `opcion_respuesta.posicion`
  (0=Siempre … 4=Nunca) **NO** es el puntaje. Puntaje: `normal → 4 - posicion`;
  `invertida → posicion`. Ya centralizado en `reactivoModel::calcular_puntaje()`.
- **El instrumento vive en datos, no en código:** el mismo motor califica ambas
  guías; la guía se congela en `aplicacion.guia_id` al responder.
- **Auditoría:** captura la IP con `$_SERVER['REMOTE_ADDR']` (no cabeceras
  falsificables); `auditoria.usuario_id` es NOT NULL y apunta a `usuario.id`.

---

## 4. Reglas de trabajo (respetar SIEMPRE)

- **NO** ejecutar Git ni hacer commits (los corre el usuario). NO agregarte como
  colaborador ni modificar remotes.
- **NO** modificar `app/classes/*` (núcleo de Bee). Extender solo en los puntos
  que Bee reserva: `app/functions/bee_custom_functions.php` y
  `templates/includes/`.
- Seguir convenciones de Bee: `xyzController extends Controller implements
  ControllerInterface`, `xyzModel extends Model` con `public static $t1`, vistas
  en `templates/views/{controlador}/{vista}View.php`, autoloader por sufijo (sin
  namespaces).
- El **DDL es la fuente de verdad** del esquema. No inventar columnas.
- Correr `php -l` en cada archivo tocado. Documentar cambios en
  `docs/BITACORA_CAMBIOS.md`.
- Datos sensibles e identificados: mantener el acceso por rol y la bitácora en
  cada acción sensible.

---

## 5. PRIMERA TAREA: motor de calificación

**Objetivo:** dado un cuestionario respondido (`aplicacion` + sus `respuesta`),
calcular y persistir su resultado conforme a la norma. Sin tocar todavía UI de
reportes ni el módulo de administrador.

### 5.1 Modelos que faltan (crearlos primero)

El cálculo necesita leer/escribir tablas que aún no tienen modelo Bee:

- `umbralModel` — leer los rangos de riesgo por guía y nivel de agregación
  (`final` / `categoria` / `dominio`).
- `resultadoDetalleModel` — persistir el desglose por categoría y dominio.
- Lectura de `categoria` y `dominio` (modelos mínimos o métodos de consulta,
  para nombrar cada renglón del desglose).

Crear siguiendo el patrón de los modelos existentes (`$t1`, métodos estáticos,
`insertOne`, `by_id`, etc.).

### 5.2 La lógica (en `resultadoModel::calcular_para_aplicacion($aplicacionId)`)

1. Traer las respuestas de la aplicación con su reactivo y su opción.
   `respuestaModel::por_aplicacion()` ya hace el JOIN y devuelve `polaridad`,
   `dominio_id`, `categoria_id`, `posicion`.
2. Puntaje de cada respuesta con `reactivoModel::calcular_puntaje()`
   (normal → `4 - posicion`; invertida → `posicion`).
3. Sumar en tres niveles:
   - **por dominio** (Cdom = suma de sus reactivos),
   - **por categoría** (Ccat = suma de sus reactivos),
   - **final** (Cfinal = suma de todos los reactivos respondidos).
   Los reactivos omitidos por filtro simplemente no tienen `respuesta`, así que
   no suman (no hay que restarlos ni tratarlos aparte).
4. Asignar nivel de riesgo a cada suma consultando `umbral`:
   - filtrar por la guía correcta vía `aplicacion.guia_id`;
   - convención `limite_inferior <= valor < limite_superior`;
   - el nivel más bajo tiene `limite_inferior` NULL, el más alto
     `limite_superior` NULL.
5. Persistir: un registro en `resultado` (Cfinal + nivel) y N en
   `resultado_detalle` (uno por cada dominio y cada categoría, con su
   calificación y su nivel). Recalcular debe ser idempotente (si ya existe
   resultado para esa aplicación, reemplazarlo, no duplicar).

### 5.3 Criterio de aceptación (la prueba de fuego)

- **Verificar contra un caso calculado a mano.** Tomar una aplicación de prueba
  (por ejemplo, todas las respuestas en un valor conocido, como todo "Siempre"),
  calcular el resultado esperado a mano usando las tablas de la norma, y
  confirmar que el motor da EXACTAMENTE lo mismo — a nivel dominio, categoría y
  final.
- Probar **ambas guías** (II y III).
- Probar el efecto de los **filtros**: una aplicación con "no atiendo clientes /
  no soy jefe" no debe contar esos reactivos.
- Ninguna calificación debe caer fuera de los rangos definidos en `umbral`.

---

## 6. Segunda tarea (después, NO ahora): módulo de administrador

Alta de centros de trabajo (con validación ≤15 → no aplica), generación de
tokens vigentes, habilitar campañas. Se detallará al cerrar la primera tarea.

---

## 7. Fuera de alcance por ahora

- Reportes / tablero con gráficas (dependen del motor ya terminado).
- Mockups visuales finales (los trabaja otra persona en paralelo; el diseño fino
  se pulirá después).
- La Guía de Referencia I de la norma.
- Exportación a Excel (falta elegir librería; se decide más adelante).
