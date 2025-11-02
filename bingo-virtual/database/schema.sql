-- =====================================================
-- BINGO VIRTUAL - DATABASE SCHEMA
-- =====================================================

CREATE DATABASE IF NOT EXISTS bingo_virtual CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bingo_virtual;

-- Tabla de usuarios (administradores y jugadores)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    whatsapp VARCHAR(20) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    password VARCHAR(255) DEFAULT NULL,
    rol ENUM('admin', 'jugador') DEFAULT 'jugador',
    estado ENUM('activo', 'inactivo', 'bloqueado') DEFAULT 'activo',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_whatsapp (whatsapp),
    INDEX idx_email (email),
    INDEX idx_rol (rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de sorteos
CREATE TABLE IF NOT EXISTS sorteos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    fecha_sorteo DATE NOT NULL,
    hora_sorteo TIME NOT NULL,
    cantidad_cartones INT DEFAULT 100,
    precio_carton DECIMAL(10,2) DEFAULT 0.00,
    estado ENUM('programado', 'en_curso', 'finalizado', 'cancelado') DEFAULT 'programado',
    modo_extraccion ENUM('manual', 'automatico') DEFAULT 'manual',
    intervalo_segundos INT DEFAULT 5,
    narrador_activo BOOLEAN DEFAULT TRUE,
    premios_json TEXT,
    reglas TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_inicio TIMESTAMP NULL,
    fecha_fin TIMESTAMP NULL,
    INDEX idx_estado (estado),
    INDEX idx_fecha_sorteo (fecha_sorteo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de cartones
CREATE TABLE IF NOT EXISTS cartones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sorteo_id INT NOT NULL,
    numero_carton INT NOT NULL,
    matriz_json TEXT NOT NULL,
    estado ENUM('disponible', 'pendiente', 'pagado', 'ganador', 'anulado') DEFAULT 'disponible',
    usuario_id INT DEFAULT NULL,
    fecha_reserva TIMESTAMP NULL,
    fecha_pago TIMESTAMP NULL,
    FOREIGN KEY (sorteo_id) REFERENCES sorteos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    UNIQUE KEY unique_carton_sorteo (sorteo_id, numero_carton),
    INDEX idx_estado (estado),
    INDEX idx_sorteo (sorteo_id),
    INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de extracciones (números que han salido)
CREATE TABLE IF NOT EXISTS extracciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sorteo_id INT NOT NULL,
    numero INT NOT NULL,
    letra VARCHAR(1) DEFAULT NULL,
    orden INT NOT NULL,
    hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sorteo_id) REFERENCES sorteos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_numero_sorteo (sorteo_id, numero),
    INDEX idx_sorteo (sorteo_id),
    INDEX idx_orden (orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de reclamos de BINGO
CREATE TABLE IF NOT EXISTS reclamos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sorteo_id INT NOT NULL,
    usuario_id INT NOT NULL,
    carton_id INT NOT NULL,
    tipo_premio VARCHAR(50) NOT NULL,
    estado ENUM('pendiente', 'validado', 'rechazado') DEFAULT 'pendiente',
    valido BOOLEAN DEFAULT FALSE,
    mensaje VARCHAR(255) DEFAULT NULL,
    numeros_marcados_json TEXT,
    hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sorteo_id) REFERENCES sorteos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (carton_id) REFERENCES cartones(id) ON DELETE CASCADE,
    INDEX idx_sorteo (sorteo_id),
    INDEX idx_estado (estado),
    INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de pagos
CREATE TABLE IF NOT EXISTS pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    sorteo_id INT NOT NULL,
    carton_id INT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    metodo_pago VARCHAR(50) DEFAULT NULL,
    comprobante VARCHAR(255) DEFAULT NULL,
    estado ENUM('pendiente', 'confirmado', 'rechazado') DEFAULT 'pendiente',
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_confirmacion TIMESTAMP NULL,
    confirmado_por INT DEFAULT NULL,
    notas TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (sorteo_id) REFERENCES sorteos(id) ON DELETE CASCADE,
    FOREIGN KEY (carton_id) REFERENCES cartones(id) ON DELETE CASCADE,
    FOREIGN KEY (confirmado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_usuario (usuario_id),
    INDEX idx_estado (estado),
    INDEX idx_sorteo (sorteo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de ganadores
CREATE TABLE IF NOT EXISTS ganadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sorteo_id INT NOT NULL,
    usuario_id INT NOT NULL,
    carton_id INT NOT NULL,
    reclamo_id INT NOT NULL,
    tipo_premio VARCHAR(50) NOT NULL,
    premio_descripcion TEXT,
    orden INT DEFAULT 1,
    fecha_ganador TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sorteo_id) REFERENCES sorteos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (carton_id) REFERENCES cartones(id) ON DELETE CASCADE,
    FOREIGN KEY (reclamo_id) REFERENCES reclamos(id) ON DELETE CASCADE,
    INDEX idx_sorteo (sorteo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de configuración del sistema
CREATE TABLE IF NOT EXISTS configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT,
    descripcion VARCHAR(255),
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar usuario administrador por defecto
INSERT INTO usuarios (nombre, whatsapp, email, password, rol, estado) VALUES
('Administrador', '3000000000', 'admin@bingo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'activo');
-- Password por defecto: password

-- Insertar configuración inicial
INSERT INTO configuracion (clave, valor, descripcion) VALUES
('sitio_nombre', 'Bingo Virtual', 'Nombre del sitio'),
('narrador_velocidad', '1.0', 'Velocidad del narrador (0.1 a 2.0)'),
('narrador_idioma', 'es-ES', 'Idioma del narrador'),
('sonido_activo', '1', 'Activar sonidos del sistema'),
('confetti_activo', '1', 'Activar efecto confetti');
