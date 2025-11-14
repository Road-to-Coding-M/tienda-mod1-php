-- database/init.sql  — paste-all-and-rebuild

-- Extensions
CREATE EXTENSION IF NOT EXISTS pgcrypto; -- for bcrypt (crypt(..., gen_salt('bf')))

-- Reset
DROP TABLE IF EXISTS productos CASCADE;
DROP TABLE IF EXISTS categorias CASCADE;
DROP TABLE IF EXISTS usuarios CASCADE;
DROP TABLE IF EXISTS user_roles CASCADE;

-- Tables
CREATE TABLE categorias (
  id UUID PRIMARY KEY,
  nombre VARCHAR(255) UNIQUE NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  is_deleted BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE usuarios (
  id BIGSERIAL PRIMARY KEY,
  username VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  nombre VARCHAR(255) NOT NULL,
  apellidos VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  is_deleted BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE user_roles (
  user_id BIGINT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
  roles VARCHAR(50) NOT NULL
);

CREATE TABLE productos (
  id BIGINT PRIMARY KEY,
  uuid UUID NOT NULL UNIQUE,
  marca VARCHAR(255) NOT NULL,
  modelo VARCHAR(255) NOT NULL,
  precio DOUBLE PRECISION NOT NULL DEFAULT 0.0,
  stock INT NOT NULL DEFAULT 0,
  imagen TEXT DEFAULT NULL,               
  categoria_id UUID REFERENCES categorias(id),
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  is_deleted BOOLEAN NOT NULL DEFAULT FALSE
);

-- Seed: categorias (5)
INSERT INTO categorias (id, nombre) VALUES
('b7f9b0c1-7f2a-4c2d-9c3e-9b1a6d1a0de1','DEPORTES'),
('2c3f2a7e-7a4b-4e2f-8b9c-02d8a6f0c1b2','BEBIDA'),
('3a5c7e9b-1d2f-4a6c-9e8b-4d6f7a8b9c0d','COMIDA'),
('4d7e9b1c-2a3b-4c5d-8e9f-1a2b3c4d5e6f','ACCESORIOS'),
('5e9b1c2d-3a4b-5c6d-9e0f-1b2c3d4e5f60','OTROS');

-- Seed: admin / admin  (bcrypt via pgcrypto)
INSERT INTO usuarios (username,password,nombre,apellidos,email) VALUES
('admin', crypt('admin', gen_salt('bf', 10)), 'Admin','User','admin@example.com');

INSERT INTO user_roles (user_id, roles)
SELECT id, 'ADMIN' FROM usuarios WHERE username = 'admin';

-- Seed: productos (imagen = NULL)
INSERT INTO productos (id, uuid,marca,modelo,precio,stock,imagen,categoria_id) VALUES
(1, '19135792-b778-441f-87f1-4a1d1e6a0001','Iphone','17 Pro Max', 999.99,  5, NULL, '5e9b1c2d-3a4b-5c6d-9e0f-1b2c3d4e5f60'), -- OTROS
(2, '662ed342-de99-45c6-84f1-4a1d1e6a0002','Samsung','Ultra',   1229.99, 12, NULL, '5e9b1c2d-3a4b-5c6d-9e0f-1b2c3d4e5f60'), -- OTROS
(3, 'a4e1e6a0-0003-441f-87f1-19135792b778','COACH','Primavera', 1000.99, 10, NULL, '4d7e9b1c-2a3b-4c5d-8e9f-1a2b3c4d5e6f'), -- ACCESORIOS
(4, 'a4e1e6a0-0004-441f-87f1-19135792b778','New Balance','327',  300.50,  2, NULL, 'b7f9b0c1-7f2a-4c2d-9c3e-9b1a6d1a0de1'), -- DEPORTES
(5, 'a4e1e6a0-0005-441f-87f1-19135792b778','Hacendado','Galleta',   5.00, 50, NULL, '3a5c7e9b-1d2f-4a6c-9e8b-4d6f7a8b9c0d'), -- COMIDA
(6, 'a4e1e6a0-0006-441f-87f1-19135792b778','Fanta','Naranja',       0.99,100, NULL, '2c3f2a7e-7a4b-4e2f-8b9c-02d8a6f0c1b2'); -- BEBIDA
