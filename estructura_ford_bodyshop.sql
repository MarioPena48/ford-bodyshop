-- Script SQL para la estructura de la base de datos Ford_bodyshop
-- Ajusta los tipos y restricciones según tus necesidades exactas

CREATE TABLE clientes (
    cliente_id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    email VARCHAR(255),
    direccion VARCHAR(255),
    ciudad VARCHAR(100),
    estado VARCHAR(50),
    codigo_postal VARCHAR(10),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE vehiculos (
    vehiculo_id SERIAL PRIMARY KEY,
    cliente_id INTEGER NOT NULL REFERENCES clientes(cliente_id),
    marca VARCHAR(50) DEFAULT 'FORD',
    modelo VARCHAR(100),
    placas VARCHAR(20),
    vin VARCHAR(255),
    color VARCHAR(50),
    kilometraje INTEGER
);

CREATE TABLE usuarios (
    usuario_id SERIAL PRIMARY KEY,
    nombre_usuario VARCHAR(50) NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    rol VARCHAR(50) NOT NULL,
    nombre VARCHAR(100),
    apellido VARCHAR(100),
    email VARCHAR(255)
);

CREATE TABLE citas (
    cita_id SERIAL PRIMARY KEY,
    cliente_id INTEGER NOT NULL REFERENCES clientes(cliente_id),
    vehiculo_id INTEGER NOT NULL REFERENCES vehiculos(vehiculo_id),
    fecha_hora_cita TIMESTAMP NOT NULL,
    tipo_cita VARCHAR(100),
    estado_cita VARCHAR(50) DEFAULT 'Programada',
    notas TEXT
);

CREATE TABLE servicios (
    servicio_id SERIAL PRIMARY KEY,
    vehiculo_id INTEGER NOT NULL REFERENCES vehiculos(vehiculo_id),
    tipo_servicio VARCHAR(100),
    descripcion_problema TEXT,
    fecha_recepcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_estimada_entrega TIMESTAMP,
    fecha_real_entrega TIMESTAMP,
    costo_estimado NUMERIC(10,2),
    costo_final NUMERIC(10,2),
    estado_servicio VARCHAR(50),
    notas TEXT
);
