-- =====================================================================
-- Bootstrap del primer súper usuario (cuenta raíz) — Sistema NOM-035
-- Generado por scripts/generar_bootstrap_superusuario.php el 2026-09-23 11:00:48
--
-- El hash de abajo se calculó con la fórmula REAL de Bee
-- (password_hash($password . AUTH_SALT, PASSWORD_BCRYPT), ver
-- app/classes/Auth.php, app/controllers/loginController.php y
-- get_new_password() en app/functions/bee_core_functions.php) contra el
-- AUTH_SALT actual de app/core/settings.php. Ejecutar tal cual, de una
-- sola vez, en phpMyAdmin (pestaña SQL) sobre la base db_beeframework.
-- =====================================================================

INSERT INTO secretaria (nombre) VALUES ('Secretaría de Cultura y Turismo');
SET @secretaria_id = LAST_INSERT_ID();

INSERT INTO bee_users (username, email, password, created_at)
VALUES ('superadmin', 'superadmin@culturaturismo.edomex.gob.mx', '$2y$10$4tuBfloQPjSo0sTnE.VHmOWUdNKcZpgv22F3JAnec8W2p.s9c8o0C', NOW());
SET @bee_user_id = LAST_INSERT_ID();

INSERT INTO usuario (bee_user_id, rol, secretaria_id)
VALUES (@bee_user_id, 'superusuario', @secretaria_id);

-- ---------------------------------------------------------------------
-- Credenciales en texto plano (solo se muestran aqui, guardalas ahora):
--   Usuario:    superadmin
--   Contrasena: INyT0jkl
-- ---------------------------------------------------------------------
