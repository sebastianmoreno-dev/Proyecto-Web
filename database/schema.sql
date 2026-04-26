CREATE DATABASE inmobiliaria;
USE inmobiliaria;

CREATE TABLE propiedades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255),
    descripcion TEXT,
    precio DECIMAL(10,2),
    ubicacion VARCHAR(255),
    habitaciones INT,
    banos INT,
    imagen VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
