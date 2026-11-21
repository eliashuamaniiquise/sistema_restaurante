<?php
// /php/cocina.php - Consulta cronológica de pedidos pendientes y actualización a 'listo' (RF09)
session_start();
require_once "conexion.php";
header('Content-Type: application/json; charset=utf-8');

if ($db_error) {
    echo json_encode(["status" => "error", "mensaje" => $db_error]);
    exit;
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? 'listar';

if ($accion === 'marcar_listo') {
    $id_pedido = intval($_POST['id_pedido'] ?? 0);
    $stmt = $conn->prepare("UPDATE pedido SET estado = 'listo' WHERE id_pedido = ?");
    $stmt->bind_param("i", $id_pedido);
    if ($stmt->execute()) {
        echo json_encode(["status" => "ok", "mensaje" => "Pedido #$id_pedido marcado como listo"]);
    } else {
        echo json_encode(["status" => "error", "mensaje" => "No se pudo actualizar el pedido"]);
    }
    exit;
}

$sql = "SELECT p.id_pedido, p.numero_mesa, DATE_FORMAT(p.fecha_hora, '%h:%i %p') AS hora, p.notas_generales,
               pl.nombre AS plato, d.cantidad, d.notas
        FROM pedido p
        JOIN detalle_pedido d ON p.id_pedido = d.id_pedido
        JOIN plato pl ON d.id_plato = pl.id_plato
        WHERE p.estado = 'pendiente'
        ORDER BY p.fecha_hora ASC";
$res = $conn->query($sql);
$pedidos = [];
while ($row = $res->fetch_assoc()) {
    $pid = $row['id_pedido'];
    if (!isset($pedidos[$pid])) {
        $pedidos[$pid] = [
            'id_pedido' => $pid,
            'numero_mesa' => $row['numero_mesa'],
            'hora' => $row['hora'],
            'notas' => $row['notas_generales'] ?: $row['notas'],
            'items' => []
        ];
    }
    $pedidos[$pid]['items'][] = $row['plato'] . " x " . $row['cantidad'];
}
echo json_encode(["status" => "ok", "pedidos" => array_values($pedidos)]);
?>
