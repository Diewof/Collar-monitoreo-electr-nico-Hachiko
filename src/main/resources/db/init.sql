-- =============================================================================
-- Hachiko Portal — Script de inicialización PostgreSQL
-- Base de datos: collar
-- Generado para Spring Boot con ddl-auto: validate
--
-- Credenciales de prueba (password = "password"):
--   admin@hachiko.com  →  ADMIN
--   juan@hachiko.com   →  USER
--   maria@hachiko.com  →  USER
--
-- Ejecución:
--   psql -U postgres -f src/main/resources/db/init.sql
-- =============================================================================

-- -----------------------------------------------------------------------------
-- 1. Crear base de datos (ejecutar como superusuario si no existe)
-- -----------------------------------------------------------------------------
SELECT 'CREATE DATABASE collar'
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'collar')\gexec

\connect collar

SET client_encoding = 'UTF8';
SET timezone = 'America/Bogota';

-- -----------------------------------------------------------------------------
-- 2. Limpiar schema existente (orden inverso a las dependencias)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS sugerencia_etologica     CASCADE;
DROP TABLE IF EXISTS registro_comportamiento  CASCADE;
DROP TABLE IF EXISTS registro_sensores        CASCADE;
DROP TABLE IF EXISTS notificacion             CASCADE;
DROP TABLE IF EXISTS collar                   CASCADE;
DROP TABLE IF EXISTS perro                    CASCADE;
DROP TABLE IF EXISTS propietario              CASCADE;
DROP TABLE IF EXISTS residencia               CASCADE;
DROP TABLE IF EXISTS login_attempts           CASCADE;
DROP TABLE IF EXISTS password_resets          CASCADE;
DROP TABLE IF EXISTS users                    CASCADE;
DROP TABLE IF EXISTS medio                    CASCADE;
DROP TABLE IF EXISTS tipo_notificacion        CASCADE;
DROP TABLE IF EXISTS tipo_patron              CASCADE;
DROP TABLE IF EXISTS emocion                  CASCADE;
DROP TABLE IF EXISTS raza                     CASCADE;
DROP TABLE IF EXISTS plan                     CASCADE;
DROP TABLE IF EXISTS ciudad                   CASCADE;
DROP TABLE IF EXISTS departamento             CASCADE;
DROP TABLE IF EXISTS pais                     CASCADE;

-- =============================================================================
-- 3. DDL — Tablas (orden: primero las independientes, luego las dependientes)
-- =============================================================================

-- -----------------------------------------------------------------------------
-- Geografía
-- -----------------------------------------------------------------------------
CREATE TABLE pais (
    pais_id   INTEGER      NOT NULL,
    nombre    VARCHAR(50),
    CONSTRAINT pk_pais PRIMARY KEY (pais_id)
);

CREATE TABLE departamento (
    departamento_id   INTEGER     NOT NULL,
    nombre            VARCHAR(50),
    pais_id           INTEGER,
    CONSTRAINT pk_departamento PRIMARY KEY (departamento_id),
    CONSTRAINT fk_departamento_pais FOREIGN KEY (pais_id) REFERENCES pais (pais_id)
);

CREATE TABLE ciudad (
    ciudad_id         INTEGER     NOT NULL,
    nombre            VARCHAR(50),
    departamento_id   INTEGER,
    CONSTRAINT pk_ciudad PRIMARY KEY (ciudad_id),
    CONSTRAINT fk_ciudad_departamento FOREIGN KEY (departamento_id) REFERENCES departamento (departamento_id)
);

-- -----------------------------------------------------------------------------
-- Catálogos independientes
-- -----------------------------------------------------------------------------
CREATE TABLE plan (
    plan_id       INTEGER         NOT NULL,
    nombre_plan   VARCHAR(30),
    descripcion   VARCHAR(255),
    costo         NUMERIC(6, 2),
    CONSTRAINT pk_plan PRIMARY KEY (plan_id)
);

CREATE TABLE raza (
    raza_id                            INTEGER      NOT NULL,
    nombre_raza                        VARCHAR(50),
    predisposicion_problemas_conducta  VARCHAR(255),
    CONSTRAINT pk_raza PRIMARY KEY (raza_id)
);

CREATE TABLE emocion (
    emocion_id      INTEGER     NOT NULL,
    nombre_emocion  VARCHAR(50),
    CONSTRAINT pk_emocion PRIMARY KEY (emocion_id)
);

CREATE TABLE tipo_patron (
    tipo_patron_id  INTEGER      NOT NULL,
    nombre_patron   VARCHAR(100),
    CONSTRAINT pk_tipo_patron PRIMARY KEY (tipo_patron_id)
);

CREATE TABLE tipo_notificacion (
    tipo_notificacion_id  INTEGER     NOT NULL,
    nombre_tipo           VARCHAR(30),
    CONSTRAINT pk_tipo_notificacion PRIMARY KEY (tipo_notificacion_id)
);

CREATE TABLE medio (
    medio_id    INTEGER      NOT NULL,
    tipo_medio  VARCHAR(100),
    ruta        VARCHAR(45),
    CONSTRAINT pk_medio PRIMARY KEY (medio_id)
);

-- -----------------------------------------------------------------------------
-- Usuarios y autenticación
-- -----------------------------------------------------------------------------
CREATE TABLE users (
    id          SERIAL          NOT NULL,
    email       VARCHAR(255)    NOT NULL,
    password    VARCHAR(255)    NOT NULL,
    role        VARCHAR(8),
    created_at  TIMESTAMP       NOT NULL,
    last_login  TIMESTAMP,
    CONSTRAINT pk_users       PRIMARY KEY (id),
    CONSTRAINT uq_users_email UNIQUE (email)
);

CREATE TABLE login_attempts (
    id            SERIAL          NOT NULL,
    email         VARCHAR(255)    NOT NULL,
    ip_address    VARCHAR(45)     NOT NULL,
    attempt_time  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_login_attempts PRIMARY KEY (id)
);
CREATE INDEX idx_login_attempts_email_ip ON login_attempts (email, ip_address);

CREATE TABLE password_resets (
    id          SERIAL          NOT NULL,
    email       VARCHAR(255)    NOT NULL,
    token       VARCHAR(100)    NOT NULL,
    expires_at  TIMESTAMP       NOT NULL,
    created_at  TIMESTAMP       NOT NULL,
    CONSTRAINT pk_password_resets       PRIMARY KEY (id),
    CONSTRAINT uq_password_resets_email UNIQUE (email)
);

-- -----------------------------------------------------------------------------
-- Propietario y residencia
-- -----------------------------------------------------------------------------
CREATE TABLE residencia (
    residencia_id  SERIAL       NOT NULL,
    ciudad_id      INTEGER,
    direccion      VARCHAR(100),
    CONSTRAINT pk_residencia PRIMARY KEY (residencia_id),
    CONSTRAINT fk_residencia_ciudad FOREIGN KEY (ciudad_id) REFERENCES ciudad (ciudad_id)
);

CREATE TABLE propietario (
    propietario_id    SERIAL       NOT NULL,
    user_id           INTEGER      NOT NULL,
    primer_nombre     VARCHAR(50),
    segundo_nombre    VARCHAR(50),
    apellido          VARCHAR(50),
    segundo_apellido  VARCHAR(50),
    telefono          VARCHAR(15),
    email             VARCHAR(100),
    residencia_id     INTEGER,
    plan_id           INTEGER,
    CONSTRAINT pk_propietario       PRIMARY KEY (propietario_id),
    CONSTRAINT uq_propietario_user  UNIQUE (user_id),
    CONSTRAINT fk_propietario_user  FOREIGN KEY (user_id)       REFERENCES users      (id)           ON DELETE CASCADE,
    CONSTRAINT fk_propietario_res   FOREIGN KEY (residencia_id) REFERENCES residencia (residencia_id),
    CONSTRAINT fk_propietario_plan  FOREIGN KEY (plan_id)       REFERENCES plan       (plan_id)
);

-- -----------------------------------------------------------------------------
-- Mascotas
-- IMPORTANTE: la columna se llama "fechanacimiento" (sin guión bajo)
-- según @Column(name = "fechanacimiento") en la entidad Perro.java
-- -----------------------------------------------------------------------------
CREATE TABLE perro (
    perro_id        SERIAL          NOT NULL,
    nombre          VARCHAR(50),
    fechanacimiento DATE,
    peso            NUMERIC(4, 2),
    genero          VARCHAR(1),
    esterilizado    BOOLEAN,
    propietario_id  INTEGER,
    raza_id         INTEGER,
    CONSTRAINT pk_perro             PRIMARY KEY (perro_id),
    CONSTRAINT fk_perro_propietario FOREIGN KEY (propietario_id) REFERENCES propietario (propietario_id) ON DELETE CASCADE,
    CONSTRAINT fk_perro_raza        FOREIGN KEY (raza_id)        REFERENCES raza        (raza_id)
);

-- -----------------------------------------------------------------------------
-- Collar y sensores
-- -----------------------------------------------------------------------------
CREATE TABLE collar (
    collar_id          INTEGER      NOT NULL,
    perro_id           INTEGER,
    version_firmware   VARCHAR(20),
    fecha_fabricacion  DATE,
    fecha_instalacion  DATE,
    bateria            INTEGER,
    CONSTRAINT pk_collar       PRIMARY KEY (collar_id),
    CONSTRAINT fk_collar_perro FOREIGN KEY (perro_id) REFERENCES perro (perro_id) ON DELETE CASCADE
);

CREATE TABLE registro_sensores (
    registro_id     BIGINT          NOT NULL,
    collar_id       INTEGER,
    decibelios      NUMERIC(4, 2),
    frecuencia      NUMERIC(6, 2),
    aceleracion_x   NUMERIC(8, 2),
    temperatura     NUMERIC(4, 1),
    pulsaciones_min INTEGER,
    marca_tiempo    TIMESTAMP,
    CONSTRAINT pk_registro_sensores       PRIMARY KEY (registro_id),
    CONSTRAINT fk_registro_sensores_collar FOREIGN KEY (collar_id) REFERENCES collar (collar_id) ON DELETE CASCADE
);

CREATE TABLE registro_comportamiento (
    registro_id     BIGINT          NOT NULL,
    collar_id       INTEGER,
    emocion_id      INTEGER,
    tipo_patron_id  INTEGER,
    certeza         NUMERIC(3, 0),
    hora_inicio     TIME,
    duracion        INTEGER,
    CONSTRAINT pk_registro_comportamiento          PRIMARY KEY (registro_id),
    CONSTRAINT fk_reg_comp_collar     FOREIGN KEY (collar_id)      REFERENCES collar      (collar_id)      ON DELETE CASCADE,
    CONSTRAINT fk_reg_comp_emocion    FOREIGN KEY (emocion_id)     REFERENCES emocion     (emocion_id),
    CONSTRAINT fk_reg_comp_patron     FOREIGN KEY (tipo_patron_id) REFERENCES tipo_patron (tipo_patron_id)
);

-- -----------------------------------------------------------------------------
-- Notificaciones
-- -----------------------------------------------------------------------------
CREATE TABLE notificacion (
    notificacion_id       BIGINT       NOT NULL,
    propietario_id        INTEGER,
    tipo_notificacion_id  INTEGER,
    mensaje               VARCHAR(500),
    estado                VARCHAR(10),     -- valores: PENDIENTE, ENVIADA, LEIDA
    fecha_generacion      TIMESTAMP,
    CONSTRAINT pk_notificacion              PRIMARY KEY (notificacion_id),
    CONSTRAINT fk_notificacion_propietario  FOREIGN KEY (propietario_id)       REFERENCES propietario       (propietario_id) ON DELETE CASCADE,
    CONSTRAINT fk_notificacion_tipo         FOREIGN KEY (tipo_notificacion_id) REFERENCES tipo_notificacion (tipo_notificacion_id)
);

-- -----------------------------------------------------------------------------
-- Sugerencias etológicas
-- -----------------------------------------------------------------------------
CREATE TABLE sugerencia_etologica (
    sug_id      INTEGER  NOT NULL,
    emocion_id  INTEGER,
    medio_id    INTEGER,
    contenido   TEXT,
    CONSTRAINT pk_sugerencia_etologica      PRIMARY KEY (sug_id),
    CONSTRAINT fk_sug_emocion FOREIGN KEY (emocion_id) REFERENCES emocion (emocion_id),
    CONSTRAINT fk_sug_medio   FOREIGN KEY (medio_id)   REFERENCES medio   (medio_id)
);

-- =============================================================================
-- 4. DML — Datos de prueba
-- =============================================================================

-- -----------------------------------------------------------------------------
-- Geografía
-- -----------------------------------------------------------------------------
INSERT INTO pais (pais_id, nombre) VALUES
    (1, 'Colombia');

INSERT INTO departamento (departamento_id, nombre, pais_id) VALUES
    (1, 'Atlántico',       1),
    (2, 'Antioquia',       1),
    (3, 'Bogotá D.C.',     1),
    (4, 'Cundinamarca',    1),
    (5, 'Valle del Cauca', 1),
    (6, 'Santander',       1);

INSERT INTO ciudad (ciudad_id, nombre, departamento_id) VALUES
    (1, 'Barranquilla', 1),
    (2, 'Medellín',     2),
    (3, 'Bogotá',       3),
    (4, 'Soacha',       4),
    (5, 'Cali',         5),
    (6, 'Bucaramanga',  6),
    (7, 'Cartagena',    1),
    (8, 'Bello',        2);

-- -----------------------------------------------------------------------------
-- Planes de suscripción
-- -----------------------------------------------------------------------------
INSERT INTO plan (plan_id, nombre_plan, descripcion, costo) VALUES
    (1, 'Plan Básico',    'Acceso limitado a funciones principales',             19.99),
    (2, 'Plan Premium',   'Acceso completo con soporte prioritario',             49.99),
    (3, 'Plan Familiar',  'Permite múltiples usuarios bajo una sola cuenta',     29.99);

-- -----------------------------------------------------------------------------
-- Razas
-- -----------------------------------------------------------------------------
INSERT INTO raza (raza_id, nombre_raza, predisposicion_problemas_conducta) VALUES
    (1,  'Labrador Retriever',  'Baja predisposición; muy sociable y dócil'),
    (2,  'Golden Retriever',    'Baja predisposición; amigable y paciente'),
    (3,  'Bulldog',             'Media; puede mostrar terquedad'),
    (4,  'Pastor Alemán',       'Media-alta si no recibe socialización temprana'),
    (5,  'Beagle',              'Media; tendencia a seguir olores e ignorar comandos'),
    (6,  'Poodle',              'Baja; muy inteligente y entrenable'),
    (7,  'Boxer',               'Media; energético, requiere estimulación constante'),
    (8,  'Chihuahua',           'Alta; propenso a ansiedad y ladridos excesivos'),
    (9,  'Husky Siberiano',     'Media-alta; independiente y de alta energía'),
    (10, 'Rottweiler',          'Alta si no recibe entrenamiento adecuado');

-- -----------------------------------------------------------------------------
-- Emociones detectables
-- -----------------------------------------------------------------------------
INSERT INTO emocion (emocion_id, nombre_emocion) VALUES
    (1, 'Alegría'),
    (2, 'Tristeza'),
    (3, 'Miedo'),
    (4, 'Agresividad'),
    (5, 'Calma'),
    (6, 'Estrés');

-- -----------------------------------------------------------------------------
-- Tipos de patrón de comportamiento
-- -----------------------------------------------------------------------------
INSERT INTO tipo_patron (tipo_patron_id, nombre_patron) VALUES
    (1, 'Ladrido sostenido'),
    (2, 'Movimiento brusco repetitivo'),
    (3, 'Quietud prolongada'),
    (4, 'Jadeo excesivo'),
    (5, 'Juego activo');

-- -----------------------------------------------------------------------------
-- Tipos de notificación
-- -----------------------------------------------------------------------------
INSERT INTO tipo_notificacion (tipo_notificacion_id, nombre_tipo) VALUES
    (1, 'Alerta de salud'),
    (2, 'Recordatorio'),
    (3, 'Reporte de conducta'),
    (4, 'Novedad del sistema');

-- -----------------------------------------------------------------------------
-- Medios de contenido
-- -----------------------------------------------------------------------------
INSERT INTO medio (medio_id, tipo_medio, ruta) VALUES
    (1, 'Video educativo',   '/media/videos'),
    (2, 'Artículo de texto', '/media/articulos'),
    (3, 'Audio guiado',      '/media/audios');

-- -----------------------------------------------------------------------------
-- Usuarios
-- Contraseña para todos: "password"
-- Hash BCrypt (strength 10): $2a$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- -----------------------------------------------------------------------------
INSERT INTO users (email, password, role, created_at, last_login) VALUES
    ('admin@hachiko.com',
     '$2a$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     'ADMIN',
     '2025-01-10 08:00:00',
     '2025-04-14 09:30:00'),
    ('juan@hachiko.com',
     '$2a$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     'USER',
     '2025-02-15 10:00:00',
     '2025-04-13 18:45:00'),
    ('maria@hachiko.com',
     '$2a$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     'USER',
     '2025-03-01 14:30:00',
     '2025-04-10 11:00:00');

-- -----------------------------------------------------------------------------
-- Residencias
-- -----------------------------------------------------------------------------
INSERT INTO residencia (ciudad_id, direccion) VALUES
    (2, 'Carrera 43A #1-50, El Poblado'),   -- residencia_id = 1
    (3, 'Calle 72 #10-34, Chapinero');       -- residencia_id = 2

-- -----------------------------------------------------------------------------
-- Propietarios
-- user_id 1 = admin (no tiene perfil), 2 = Juan, 3 = María
-- -----------------------------------------------------------------------------
INSERT INTO propietario (user_id, primer_nombre, segundo_nombre, apellido, segundo_apellido,
                         telefono, email, residencia_id, plan_id) VALUES
    (2, 'Juan',  'Diego',   'Gutiérrez', 'Otalvaro', '3023417958', 'juan@hachiko.com',  1, 2),
    (3, 'María', 'Camila',  'Ramírez',   'Torres',   '3115829034', 'maria@hachiko.com', 2, 1);
-- propietario_id 1 = Juan, 2 = María

-- -----------------------------------------------------------------------------
-- Perros
-- -----------------------------------------------------------------------------
INSERT INTO perro (nombre, fechanacimiento, peso, genero, esterilizado, propietario_id, raza_id) VALUES
    ('Hachiko',   '2021-03-15', 28.50, 'M', TRUE,  1, 1),   -- perro_id = 1, de Juan, Labrador
    ('Luna',      '2022-07-20', 22.00, 'F', TRUE,  1, 2),   -- perro_id = 2, de Juan, Golden
    ('Max',       '2020-11-05', 35.75, 'M', FALSE, 2, 4);   -- perro_id = 3, de María, Pastor Alemán

-- -----------------------------------------------------------------------------
-- Collares (IDs manuales)
-- -----------------------------------------------------------------------------
INSERT INTO collar (collar_id, perro_id, version_firmware, fecha_fabricacion, fecha_instalacion, bateria) VALUES
    (101, 1, 'v2.1.4', '2023-06-01', '2023-09-10', 87),
    (102, 2, 'v2.1.4', '2023-06-01', '2023-10-05', 64);

-- -----------------------------------------------------------------------------
-- Registros de sensores — Collar 101 (Hachiko)
-- -----------------------------------------------------------------------------
INSERT INTO registro_sensores
    (registro_id, collar_id, decibelios, frecuencia, aceleracion_x, temperatura, pulsaciones_min, marca_tiempo) VALUES
    (1001, 101, 45.20,  440.00,  0.12, 38.5,  82, '2025-04-14 08:00:00'),
    (1002, 101, 50.10,  380.50,  0.35, 38.7,  95, '2025-04-14 08:15:00'),
    (1003, 101, 60.80,  520.00,  1.20, 39.1, 110, '2025-04-14 08:30:00'),
    (1004, 101, 42.00,  310.25,  0.08, 38.4,  78, '2025-04-14 08:45:00'),
    (1005, 101, 38.50,  290.00,  0.05, 38.3,  75, '2025-04-14 09:00:00'),
    (1006, 101, 70.30,  610.00,  2.50, 39.5, 130, '2025-04-14 09:15:00'),
    (1007, 101, 55.00,  470.80,  0.90, 38.9, 100, '2025-04-14 09:30:00'),
    (1008, 101, 43.70,  350.00,  0.15, 38.6,  85, '2025-04-14 09:45:00'),
    (1009, 101, 48.90,  400.00,  0.22, 38.8,  90, '2025-04-14 10:00:00'),
    (1010, 101, 41.00,  320.00,  0.07, 38.4,  77, '2025-04-14 10:15:00');

-- Registros de sensores — Collar 102 (Luna)
INSERT INTO registro_sensores
    (registro_id, collar_id, decibelios, frecuencia, aceleracion_x, temperatura, pulsaciones_min, marca_tiempo) VALUES
    (2001, 102, 39.00,  300.00,  0.06, 38.2,  72, '2025-04-14 08:00:00'),
    (2002, 102, 44.50,  360.00,  0.18, 38.5,  80, '2025-04-14 08:15:00'),
    (2003, 102, 52.20,  430.00,  0.55, 38.8,  95, '2025-04-14 08:30:00'),
    (2004, 102, 65.10,  580.00,  1.80, 39.3, 118, '2025-04-14 08:45:00'),
    (2005, 102, 40.30,  315.00,  0.09, 38.3,  74, '2025-04-14 09:00:00'),
    (2006, 102, 47.80,  390.00,  0.28, 38.6,  86, '2025-04-14 09:15:00'),
    (2007, 102, 35.60,  270.00,  0.04, 38.1,  70, '2025-04-14 09:30:00'),
    (2008, 102, 57.40,  490.00,  1.10, 39.0, 105, '2025-04-14 09:45:00'),
    (2009, 102, 43.20,  345.00,  0.14, 38.5,  82, '2025-04-14 10:00:00'),
    (2010, 102, 50.90,  420.00,  0.40, 38.7,  92, '2025-04-14 10:15:00');

-- -----------------------------------------------------------------------------
-- Registros de comportamiento (IDs manuales)
-- -----------------------------------------------------------------------------
INSERT INTO registro_comportamiento
    (registro_id, collar_id, emocion_id, tipo_patron_id, certeza, hora_inicio, duracion) VALUES
    (3001, 101, 1, 5,  92, '08:10:00', 15),   -- Hachiko: Alegría / Juego activo
    (3002, 101, 6, 1,  85, '09:12:00', 8),    -- Hachiko: Estrés / Ladrido sostenido
    (3003, 101, 3, 4,  78, '09:18:00', 5),    -- Hachiko: Miedo / Jadeo excesivo
    (3004, 102, 1, 5,  95, '08:20:00', 20),   -- Luna: Alegría / Juego activo
    (3005, 102, 5, 3,  88, '09:35:00', 30);   -- Luna: Calma / Quietud prolongada

-- -----------------------------------------------------------------------------
-- Sugerencias etológicas
-- -----------------------------------------------------------------------------
INSERT INTO sugerencia_etologica (sug_id, emocion_id, medio_id, contenido) VALUES
    (1, 6, 1,
     'Cuando tu perro muestre signos de estrés, intenta reducir los estímulos externos. '
     'Este video te enseña técnicas de desensibilización progresiva para calmar a tu mascota.'),
    (2, 3, 3,
     'El miedo en perros puede tratarse con terapia de sonido. Escucha esta guía de audio '
     'para implementar sesiones de relajación en casa.'),
    (3, 4, 2,
     'La agresividad requiere intervención profesional. Lee este artículo sobre señales '
     'de advertencia y cómo establecer límites seguros con tu mascota.');

-- -----------------------------------------------------------------------------
-- Notificaciones
-- -----------------------------------------------------------------------------
INSERT INTO notificacion
    (notificacion_id, propietario_id, tipo_notificacion_id, mensaje, estado, fecha_generacion) VALUES
    (5001, 1, 1,
     'Se detectaron pulsaciones elevadas en Hachiko (130 bpm). Considera visitar al veterinario.',
     'LEIDA',    '2025-04-14 09:20:00'),
    (5002, 1, 3,
     'Hachiko presentó un episodio de estrés a las 09:12. Revisa el reporte de comportamiento.',
     'ENVIADA',  '2025-04-14 09:25:00'),
    (5003, 2, 1,
     'Luna superó los 39°C de temperatura corporal durante el registro de las 08:45.',
     'PENDIENTE','2025-04-14 08:50:00'),
    (5004, 2, 4,
     'Nueva versión de firmware v2.2.0 disponible para tu collar. Actualiza desde la app.',
     'PENDIENTE','2025-04-15 07:00:00');

-- =============================================================================
-- 5. Verificación rápida
-- =============================================================================
DO $$
DECLARE
    v_users         INTEGER;
    v_propietarios  INTEGER;
    v_perros        INTEGER;
    v_collares      INTEGER;
    v_sensores      INTEGER;
    v_comportamiento INTEGER;
    v_notificaciones INTEGER;
BEGIN
    SELECT COUNT(*) INTO v_users         FROM users;
    SELECT COUNT(*) INTO v_propietarios  FROM propietario;
    SELECT COUNT(*) INTO v_perros        FROM perro;
    SELECT COUNT(*) INTO v_collares      FROM collar;
    SELECT COUNT(*) INTO v_sensores      FROM registro_sensores;
    SELECT COUNT(*) INTO v_comportamiento FROM registro_comportamiento;
    SELECT COUNT(*) INTO v_notificaciones FROM notificacion;

    RAISE NOTICE '=========================================';
    RAISE NOTICE 'Inicialización completada:';
    RAISE NOTICE '  users:                    %', v_users;
    RAISE NOTICE '  propietarios:             %', v_propietarios;
    RAISE NOTICE '  perros:                   %', v_perros;
    RAISE NOTICE '  collares:                 %', v_collares;
    RAISE NOTICE '  registros de sensores:    %', v_sensores;
    RAISE NOTICE '  registros comportamiento: %', v_comportamiento;
    RAISE NOTICE '  notificaciones:           %', v_notificaciones;
    RAISE NOTICE '=========================================';
END;
$$;
