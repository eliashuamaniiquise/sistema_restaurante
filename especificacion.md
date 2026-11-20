# Especificación técnica - Sistema DR

Sistema DR es un punto de venta web para Restaurante Don Dunay.

## Stack

- HTML5 y CSS3 responsive
- JavaScript y Fetch API
- PHP 8 y sesiones
- MySQL mediante mysqli

## Roles

- Administrador: administración completa
- Recepcionista: mesas, reservas y cobros
- Mesero: mesas y pedidos
- Cocinero: estado de pedidos de cocina

## Base de datos

La base `sistemdr` contiene las tablas `usuario`, `cliente`, `mesa`, `reserva`, `plato`, `insumo`, `pedido`, `detalle_pedido` y `factura`.
