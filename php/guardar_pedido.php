<?php
// /php/guardar_pedido.php - Inserta pedido y detalle_pedido con estado 'pendiente' (RF02)
session_start();
require_once "conexion.php";
header('Content-Type: application/json; charset=utf-8');

if ($db_error) {
    echo json_encode(["status" => "error", "mensaje" => $db_error]);
    exit;
}

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$numero_mesa = intval($data['numero_mesa'] ?? ($_POST['numero_mesa'] ?? 0));
$notas       = trim($data['notas'] ?? ($_POST['notas'] ?? ''));
$items       = $data['items'] ?? [];
$id_usuario  = intval($_SESSION['id_usuario'] ?? 2);

if ($numero_mesa <= 0 || empty($items)) {
    echo json_encode(["status" => "error", "mensaje" => "Seleccione una mesa y al menos un plato."]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO pedido (numero_mesa, id_usuario, fecha_hora, notas_generales, estado) VALUES (?, ?, NOW(), ?, 'pendiente')");
$stmt->bind_param("iis", $numero_mesa, $id_usuario, $notas);

if ($stmt->execute()) {
    $id_pedido = $stmt->insert_id;

    $stmtDet = $conn->prepare("INSERT INTO detalle_pedido (id_pedido, id_plato, cantidad, subtotal, notas) VALUES (?, ?, ?, ?, ?)");
    foreach ($items as $it) {
        $id_plato = intval($it['id_plato']);
        $cantidad = intval($it['cantidad']);
        $precio   = floatval($it['precio']);
        $subtotal = round($precio * $cantidad, 2);
        $stmtDet->bind_param("iiids", $id_pedido, $id_plato, $cantidad, $subtotal, $notas);
        $stmtDet->execute();
    }

    // Marcar la mesa como Ocupada
    $stmtMesa = $conn->prepare("UPDATE mesa SET estado = 'Ocupada' WHERE numero_mesa = ?");
    $stmtMesa->bind_param("i", $numero_mesa);
    $stmtMesa->execute();

    echo json_encode([
        "status" => "ok",
        "id_pedido" => $id_pedido,
        "mensaje" => "Pedido #$id_pedido enviado a cocina correctamente."
    ]);
} else {
    echo json_encode(["status" => "error", "mensaje" => "Error al registrar el pedido en MySQL."]);
}
?>
