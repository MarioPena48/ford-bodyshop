-- Agregar columna creado_por a la tabla servicios
ALTER TABLE servicios ADD COLUMN creado_por INTEGER REFERENCES usuarios(usuario_id);
