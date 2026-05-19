-- ============================================================
--  EstateArch – Base de Datos
--  Ejecuta este script en MySQL antes de iniciar el servidor
-- ============================================================

CREATE DATABASE IF NOT EXISTS estatearch;
USE estatearch;

-- ─── TABLA: USUARIOS ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS usuarios (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(100) NOT NULL,
    apellido    VARCHAR(100) NOT NULL,
    correo      VARCHAR(150) NOT NULL UNIQUE,
    contrasena  VARCHAR(255) NOT NULL,
    rol         ENUM('comprador','vendedor','admin') NOT NULL DEFAULT 'comprador',
    creado_en   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ─── TABLA: PROPIEDADES ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS propiedades (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    titulo          VARCHAR(200) NOT NULL,
    descripcion     TEXT,
    precio          DECIMAL(15,2) NOT NULL,
    ubicacion       VARCHAR(255) NOT NULL,
    tipo            ENUM('casa','departamento','terreno') DEFAULT 'casa',
    habitaciones    INT DEFAULT 0,
    banos           INT DEFAULT 0,
    area_m2         DECIMAL(10,2) DEFAULT 0,
    imagen_url      VARCHAR(500),
    estado          ENUM('activa','inactiva','vendida') DEFAULT 'activa',
    vendedor_id     INT NOT NULL,
    creado_en       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vendedor_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ─── TABLA: FAVORITOS ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS favoritos (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    comprador_id    INT NOT NULL,
    propiedad_id    INT NOT NULL,
    creado_en       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unico_fav (comprador_id, propiedad_id),
    FOREIGN KEY (comprador_id)  REFERENCES usuarios(id)     ON DELETE CASCADE,
    FOREIGN KEY (propiedad_id)  REFERENCES propiedades(id)  ON DELETE CASCADE
);

-- ─── TABLA: MENSAJES / CONSULTAS ────────────────────────────
CREATE TABLE IF NOT EXISTS mensajes (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    comprador_id    INT NOT NULL,
    vendedor_id     INT NOT NULL,
    propiedad_id    INT,
    contenido       TEXT NOT NULL,
    leido           BOOLEAN DEFAULT FALSE,
    creado_en       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (comprador_id)  REFERENCES usuarios(id)     ON DELETE CASCADE,
    FOREIGN KEY (vendedor_id)   REFERENCES usuarios(id)     ON DELETE CASCADE,
    FOREIGN KEY (propiedad_id)  REFERENCES propiedades(id)  ON DELETE SET NULL
);

-- ─── DATOS DE PRUEBA ────────────────────────────────────────
-- Contraseñas (todas usan bcrypt de "password123")
-- IMPORTANTE: Reemplaza los hashes si cambias las contraseñas

INSERT INTO usuarios (nombre, apellido, correo, contrasena, rol) VALUES
('Admin',   'EstateArch', 'admin@estatearch.mx',    '$2a$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Carlos',  'Mendoza',    'vendedor@estatearch.mx',  '$2a$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendedor'),
('María',   'López',      'comprador@estatearch.mx', '$2a$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'comprador');

INSERT INTO propiedades (titulo, descripcion, precio, ubicacion, tipo, habitaciones, banos, area_m2, imagen_url, vendedor_id) VALUES
('Residencia en Polanco',       'Casa moderna con alberca y jardín.',           2450000, 'Polanco, CDMX',      'casa',         4, 5, 420, 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800', 2),
('Departamento en Santa Fe',    'Departamento de lujo con vista panorámica.',   1890000, 'Santa Fe, CDMX',     'departamento', 3, 3, 310, 'https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?w=800', 2),
('Casa en Antigua Guatemala',   'Residencia colonial restaurada.',              3100000, 'Antigua, Guatemala', 'casa',         5, 6, 580, 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800', 2);
