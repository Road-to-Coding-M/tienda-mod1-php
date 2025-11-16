# Tienda Online PHP (CRUD + Login/Roles)

Proyecto de ejemplo para la tarea evaluable: CRUD de productos con autenticación, roles y subida de imágenes.
- PHP puro con arquitectura simple (models / services / views).
- PostgreSQL + PDO.
- Docker + docker-compose (incluye Adminer en :8081).
- Variables de entorno con `.env` y `vlucas/phpdotenv`.

## Puesta en marcha
```bash
docker compose up -d --build
# Acceso:
# Web:       http://localhost:8080
# Adminer:   http://localhost:8081  (System: PostgreSQL, Server: postgres-db, User: admin, Pass: adminPassword123, DB: tienda)
```
Usuarios:
- admin / **admin**


## Estructura
```
public/            # index.php (listado) + uploads/
app/               # vistas/controladores (header, footer, create, update, login...)
src/
  config/Config.php
  models/ (Categoria, Producto, User)
  services/ (SessionService, UsersService, ProductosService, CategoriasService)
database/init.sql  # esquema + datos iniciales
```
