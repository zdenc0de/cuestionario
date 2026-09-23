-- =====================================================================
-- ALTER: agrega la columna `estado` a `usuario` — cierra la limitación
-- documentada en la Pasada 7 (docs/ARQUITECTURA.md sección 14.2 y
-- docs/BITACORA_CAMBIOS.md sección 13.2).
--
-- Por qué: superusuarioController::borrar_administrador() no podía usar un
-- DELETE físico porque auditoria.usuario_id -> usuario.id es ON DELETE
-- RESTRICT a propósito (preserva el historial de auditoría). La "baja" de
-- un administrador invalidaba su contraseña, pero no había forma de
-- marcarlo/filtrarlo visualmente en el listado. Esta columna lo resuelve
-- sin tocar esa restricción de integridad.
--
-- IMPORTANTE: este ALTER lo ejecuta el usuario en phpMyAdmin, Claude NO lo
-- corrió contra ninguna base de datos (ni siquiera la local de desarrollo).
-- El código ya está escrito para funcionar tanto ANTES como DESPUÉS de que
-- se aplique (ver el try/catch en superusuarioController::borrar_administrador()
-- y el fallback en administradores()), así que no hay prisa ni riesgo en
-- aplicarlo cuando sea conveniente — pero mientras no se aplique, la
-- marca visual de "inactivo" y el filtro del listado no van a funcionar
-- (la revocación del acceso en sí, invalidando la contraseña, sí funciona
-- igual, ya estaba probada en la Pasada 7).
--
-- Ejecutar tal cual, en phpMyAdmin (pestaña SQL) sobre la base db_beeframework.
-- =====================================================================

ALTER TABLE usuario
  ADD COLUMN estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo'
  AFTER secretaria_id;
