# Sistema DR - Restaurante Don Dunay

Sistema web de punto de venta desarrollado con PHP, JavaScript, HTML5, CSS3 y MySQL.

## Requisitos

- PHP 8.0 o superior con extensión mysqli
- MySQL 8 o MariaDB
- Apache mediante XAMPP, WAMP o un servidor equivalente

## Instalación

1. Copia el proyecto dentro de `htdocs/sistema_restaurante`.
2. Importa `database.sql` desde phpMyAdmin.
3. Ajusta las variables `DB_HOST`, `DB_NAME`, `DB_USER` y `DB_PASSWORD` si fuera necesario.
4. Inicia Apache y MySQL.
5. Abre `http://localhost/sistema_restaurante/`.

## Acceso inicial

- Usuario: `admin`
- Contraseña: `admin123`

También existen usuarios de prueba `recepcion`, `mesero` y `cocina`, todos con `admin123`.

## Funciones

- Control de acceso por roles
- Dashboard con métricas
- Mesas y reservas
- Registro de pedidos
- Monitor de cocina
- Platos e insumos
- Caja y comprobantes
- Reportes
- Administración de usuarios
