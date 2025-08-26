-- Script de configuración de la base de datos para el Sistema CDA
-- Ejecutar como usuario cda_base en la base de datos cda_base

-- Crear tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre_completo VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Crear tabla de grupos familiares
CREATE TABLE IF NOT EXISTS grupos_familiares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    responsable VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Crear tabla de personas
CREATE TABLE IF NOT EXISTS personas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    telefono VARCHAR(20),
    grupo_familiar_id INT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (grupo_familiar_id) REFERENCES grupos_familiares(id) ON DELETE SET NULL
);

-- Crear tabla de cultos
CREATE TABLE IF NOT EXISTS cultos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    descripcion TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Crear tabla de asistencias
CREATE TABLE IF NOT EXISTS asistencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    persona_id INT NOT NULL,
    culto_id INT NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (persona_id) REFERENCES personas(id) ON DELETE CASCADE,
    FOREIGN KEY (culto_id) REFERENCES cultos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_asistencia (persona_id, culto_id)
);

-- Crear índices para mejorar el rendimiento
CREATE INDEX idx_personas_grupo_familiar ON personas(grupo_familiar_id);
CREATE INDEX idx_asistencias_persona ON asistencias(persona_id);
CREATE INDEX idx_asistencias_culto ON asistencias(culto_id);
CREATE INDEX idx_cultos_fecha ON cultos(fecha);

-- Insertar usuario administrador por defecto
-- Contraseña: admin123 (hash bcrypt)
INSERT INTO usuarios (username, password, nombre_completo, email, activo) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador del Sistema', 'admin@iglesia.com', 1);

-- Insertar algunos grupos familiares de ejemplo
INSERT INTO grupos_familiares (nombre, descripcion, responsable, telefono) VALUES 
('Familia González', 'Familia principal de la iglesia', 'Juan González', '555-0101'),
('Familia Rodríguez', 'Familia con niños pequeños', 'María Rodríguez', '555-0102'),
('Jóvenes Solteros', 'Grupo de jóvenes solteros', 'Carlos López', '555-0103');

-- Insertar algunas personas de ejemplo
INSERT INTO personas (nombre, apellido, email, telefono, grupo_familiar_id) VALUES 
('Juan', 'González', 'juan@email.com', '555-0101', 1),
('María', 'González', 'maria@email.com', '555-0101', 1),
('Carlos', 'Rodríguez', 'carlos@email.com', '555-0102', 2),
('Ana', 'López', 'ana@email.com', '555-0103', 3);

-- Insertar algunos cultos de ejemplo
INSERT INTO cultos (fecha, hora, tipo, descripcion) VALUES 
(CURDATE(), '09:00:00', 'Domingo', 'Culto dominical principal'),
(CURDATE(), '19:00:00', 'Miércoles', 'Culto de oración'),
(DATE_SUB(CURDATE(), INTERVAL 7 DAY), '09:00:00', 'Domingo', 'Culto dominical anterior');

-- Insertar algunas asistencias de ejemplo
INSERT INTO asistencias (persona_id, culto_id) VALUES 
(1, 1), (2, 1), (3, 1), (4, 1),
(1, 2), (3, 2),
(1, 3), (2, 3), (4, 3);
