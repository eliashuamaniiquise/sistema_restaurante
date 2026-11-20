<?php
declare(strict_types=1);
require_once __DIR__ . '/php/auth.php';

header('Content-Type: application/json; charset=utf-8');
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

$usuario = require_login();

function rows(mysqli $db, string $sql): array
{
    $result = $db->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function read_json(): array
{
    $data = json_decode(file_get_contents('php://input'), true);
    return is_array($data) ? $data : [];
}

switch ($action) {
    case 'dashboard':
        $hoy = $db->query("SELECT COALESCE(SUM(total),0) total, COUNT(*) cantidad FROM pedido WHERE DATE(creado_en)=CURDATE()")->fetch_assoc();
        $ocupadas = $db->query("SELECT COUNT(*) cantidad FROM mesa WHERE estado='Ocupada'")->fetch_column();
        $pendientes = $db->query("SELECT COUNT(*) cantidad FROM pedido WHERE estado IN ('Pendiente','En preparacion')")->fetch_column();
        $stock = $db->query("SELECT COUNT(*) cantidad FROM insumo WHERE stock <= stock_minimo")->fetch_column();
        json_response([
            'ventas_hoy' => round((float)$hoy['total'], 2),
            'pedidos_hoy' => (int)$hoy['cantidad'],
            'mesas_ocupadas' => (int)$ocupadas,
            'pedidos_pendientes' => (int)$pendientes,
            'stock_alertas' => (int)$stock,
            'recientes' => rows($db, "SELECT p.id,p.tipo,p.estado,p.total,p.creado_en,m.numero FROM pedido p LEFT JOIN mesa m ON m.id=p.mesa_id ORDER BY p.id DESC LIMIT 6"),
        ]);

    case 'mesas':
        json_response(['mesas' => rows($db, 'SELECT * FROM mesa ORDER BY CAST(numero AS UNSIGNED), numero'), 'reservas' => rows($db, "SELECT r.*, c.nombre cliente, c.telefono, m.numero mesa_numero FROM reserva r JOIN cliente c ON c.id=r.cliente_id JOIN mesa m ON m.id=r.mesa_id ORDER BY r.fecha_hora DESC")]);

    case 'mesa_actualizar':
        require_role(['Administrador','Recepcionista','Mesero']);
        $data = $method === 'POST' ? read_json() : [];
        $stmt = $db->prepare('UPDATE mesa SET estado=? WHERE id=?');
        $estado = (string)($data['estado'] ?? 'Disponible');
        $id = (int)($data['id'] ?? 0);
        $stmt->bind_param('si', $estado, $id);
        $stmt->execute();
        json_response(['ok' => true]);

    case 'reserva_crear':
        require_role(['Administrador','Recepcionista']);
        $data = read_json();
        $cliente = trim((string)($data['cliente'] ?? ''));
        $telefono = trim((string)($data['telefono'] ?? ''));
        if ($cliente === '' || empty($data['mesa_id']) || empty($data['fecha_hora'])) json_response(['error' => 'Datos incompletos'], 422);
        $stmt = $db->prepare('INSERT INTO cliente(nombre,telefono) VALUES (?,?)');
        $stmt->bind_param('ss', $cliente, $telefono);
        $stmt->execute();
        $clienteId = $stmt->insert_id;
        $mesaId = (int)$data['mesa_id'];
        $fecha = (string)$data['fecha_hora'];
        $personas = (int)$data['personas'];
        $observacion = trim((string)($data['observacion'] ?? ''));
        $stmt = $db->prepare('INSERT INTO reserva(cliente_id,mesa_id,fecha_hora,personas,estado,observacion) VALUES (?,?,?,?,?,?)');
        $estado = 'Pendiente';
        $stmt->bind_param('iisiss', $clienteId, $mesaId, $fecha, $personas, $estado, $observacion);
        $stmt->execute();
        $db->query("UPDATE mesa SET estado='Reservada' WHERE id=$mesaId");
        json_response(['ok' => true, 'id' => $stmt->insert_id]);

    case 'menu':
        json_response(['platos' => rows($db, 'SELECT * FROM plato ORDER BY categoria,nombre'), 'insumos' => rows($db, 'SELECT * FROM insumo ORDER BY nombre')]);

    case 'plato_guardar':
        require_role(['Administrador']);
        $data = read_json();
        $id = (int)($data['id'] ?? 0);
        $nombre = trim((string)$data['nombre']);
        $categoria = trim((string)$data['categoria']);
        $descripcion = trim((string)($data['descripcion'] ?? ''));
        $precio = round((float)$data['precio'], 2);
        $stock = (int)$data['stock'];
        if ($nombre === '' || $precio < 0) json_response(['error' => 'Datos invalidos'], 422);
        if ($id) {
            $stmt = $db->prepare('UPDATE plato SET nombre=?,categoria=?,descripcion=?,precio=?,stock=? WHERE id=?');
            $stmt->bind_param('sssdii', $nombre, $categoria, $descripcion, $precio, $stock, $id);
        } else {
            $stmt = $db->prepare('INSERT INTO plato(nombre,categoria,descripcion,precio,stock) VALUES (?,?,?,?,?)');
            $stmt->bind_param('sssdi', $nombre, $categoria, $descripcion, $precio, $stock);
        }
        $stmt->execute();
        json_response(['ok' => true]);

    case 'pedidos':
        json_response(['pedidos' => rows($db, "SELECT p.*,m.numero mesa_numero,u.nombre mesero FROM pedido p LEFT JOIN mesa m ON m.id=p.mesa_id JOIN usuario u ON u.id=p.usuario_id ORDER BY p.id DESC"), 'platos' => rows($db, "SELECT * FROM plato WHERE activo=1 ORDER BY categoria,nombre"), 'mesas' => rows($db, "SELECT * FROM mesa WHERE estado!='Mantenimiento' ORDER BY CAST(numero AS UNSIGNED)")]);

    case 'pedido_crear':
        require_role(['Administrador','Recepcionista','Mesero']);
        $data = read_json();
        $mesaId = (int)($data['mesa_id'] ?? 0) ?: null;
        $items = $data['items'] ?? [];
        if (!$items) json_response(['error' => 'Agrega al menos un plato'], 422);
        $tipo = (string)($data['tipo'] ?? 'Mesa');
        $total = 0.0;
        foreach ($items as $item) $total += round((float)$item['precio'] * (int)$item['cantidad'], 2);
        $total = round($total, 2);
        $stmt = $db->prepare('INSERT INTO pedido(mesa_id,usuario_id,tipo,total,estado) VALUES (?,?,?,?,?)');
        $estado = 'Pendiente';
        $uid = $usuario['id'];
        $stmt->bind_param('iisds', $mesaId, $uid, $tipo, $total, $estado);
        $stmt->execute();
        $pedidoId = $stmt->insert_id;
        $detalle = $db->prepare('INSERT INTO detalle_pedido(pedido_id,plato_id,cantidad,precio_unitario,subtotal) VALUES (?,?,?,?,?)');
        $stock = $db->prepare('UPDATE plato SET stock=stock-? WHERE id=?');
        foreach ($items as $item) {
            $platoId = (int)$item['id'];
            $cantidad = (int)$item['cantidad'];
            $precio = round((float)$item['precio'], 2);
            $subtotal = round($precio * $cantidad, 2);
            $detalle->bind_param('iiidd', $pedidoId, $platoId, $cantidad, $precio, $subtotal);
            $detalle->execute();
            $stock->bind_param('ii', $cantidad, $platoId);
            $stock->execute();
        }
        if ($mesaId) $db->query("UPDATE mesa SET estado='Ocupada' WHERE id=$mesaId");
        json_response(['ok' => true, 'id' => $pedidoId, 'total' => $total]);

    case 'pedido_estado':
        require_role(['Administrador','Cocinero','Mesero','Recepcionista']);
        $data = read_json();
        $id = (int)$data['id'];
        $estado = (string)$data['estado'];
        $permitidos = ['Pendiente','En preparacion','Listo','Servido','Cancelado'];
        if (!in_array($estado, $permitidos, true)) json_response(['error' => 'Estado invalido'], 422);
        $stmt = $db->prepare('UPDATE pedido SET estado=? WHERE id=?');
        $stmt->bind_param('si', $estado, $id);
        $stmt->execute();
        json_response(['ok' => true]);

    case 'cocina':
        json_response(['pedidos' => rows($db, "SELECT p.id,p.estado,p.creado_en,m.numero mesa_numero,GROUP_CONCAT(CONCAT(d.cantidad,'x ',pl.nombre) SEPARATOR ', ') detalle FROM pedido p LEFT JOIN mesa m ON m.id=p.mesa_id JOIN detalle_pedido d ON d.pedido_id=p.id JOIN plato pl ON pl.id=d.plato_id WHERE p.estado IN ('Pendiente','En preparacion','Listo') GROUP BY p.id ORDER BY p.id DESC")]);

    case 'caja':
        json_response(['pendientes' => rows($db, "SELECT p.*,m.numero mesa_numero FROM pedido p LEFT JOIN mesa m ON m.id=p.mesa_id WHERE p.estado!='Cancelado' AND p.id NOT IN (SELECT pedido_id FROM factura) ORDER BY p.id DESC"), 'facturas' => rows($db, "SELECT f.*,p.tipo FROM factura f JOIN pedido p ON p.id=f.pedido_id ORDER BY f.id DESC")]);

    case 'pagar':
        require_role(['Administrador','Recepcionista','Mesero']);
        $data = read_json();
        $pedidoId = (int)$data['pedido_id'];
        $cliente = trim((string)($data['cliente'] ?? 'Cliente general'));
        $documento = trim((string)($data['documento'] ?? ''));
        $tipoPago = (string)($data['tipo_pago'] ?? 'Efectivo');
        $pedido = $db->query("SELECT total,mesa_id FROM pedido WHERE id=$pedidoId")->fetch_assoc();
        if (!$pedido) json_response(['error' => 'Pedido no encontrado'], 404);
        $total = round((float)$pedido['total'], 2);
        $subtotal = round($total / 1.18, 2);
        $igv = round($total - $subtotal, 2);
        $stmt = $db->prepare('INSERT INTO factura(pedido_id,cliente_nombre,documento,tipo_pago,subtotal,igv,total) VALUES (?,?,?,?,?,?,?)');
        $stmt->bind_param('isssddd', $pedidoId, $cliente, $documento, $tipoPago, $subtotal, $igv, $total);
        $stmt->execute();
        $mesaId = (int)$pedido['mesa_id'];
        if ($mesaId) $db->query("UPDATE mesa SET estado='Disponible' WHERE id=$mesaId");
        $db->query("UPDATE pedido SET estado='Servido' WHERE id=$pedidoId");
        json_response(['ok' => true, 'factura' => $stmt->insert_id, 'total' => $total]);

    case 'reportes':
        require_role(['Administrador']);
        $ventas = rows($db, "SELECT DATE(creado_en) fecha,COUNT(*) pedidos,SUM(total) total FROM pedido WHERE estado!='Cancelado' GROUP BY DATE(creado_en) ORDER BY fecha DESC LIMIT 30");
        $productos = rows($db, "SELECT pl.nombre,SUM(d.cantidad) cantidad,SUM(d.subtotal) total FROM detalle_pedido d JOIN plato pl ON pl.id=d.plato_id GROUP BY pl.id ORDER BY cantidad DESC LIMIT 10");
        json_response(['ventas' => $ventas, 'productos' => $productos]);

    case 'usuarios':
        require_role(['Administrador']);
        json_response(['usuarios' => rows($db, 'SELECT id,nombre,usuario,rol,estado,creado_en FROM usuario ORDER BY id')]);

    case 'usuario_guardar':
        require_role(['Administrador']);
        $data = read_json();
        $nombre = trim((string)$data['nombre']);
        $usuarioNombre = trim((string)$data['usuario']);
        $password = (string)($data['password'] ?? '');
        $rol = (string)$data['rol'];
        if ($nombre === '' || $usuarioNombre === '' || $password === '') json_response(['error' => 'Completa nombre, usuario y contraseña'], 422);
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare('INSERT INTO usuario(nombre,usuario,password,rol) VALUES (?,?,?,?)');
        $stmt->bind_param('ssss', $nombre, $usuarioNombre, $hash, $rol);
        $stmt->execute();
        json_response(['ok' => true]);

    default:
        json_response(['error' => 'Accion no encontrada'], 404);
}
