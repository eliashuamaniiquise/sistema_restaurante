<?php
// /php/caja_api.php - Consulta de cuenta por mesa y registro de factura a 2 decimales (RF05)
session_start();
require_once "conexion.php";
header('Content-Type: application/json; charset=utf-8');

if ($db_error) {
    echo json_encode(["status" => "error", "mensaje" => $db_error]);
    exit;
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? 'consultar_mesa';

if ($accion === 'consultar_mesa') {
    $mesa = intval($_GET['mesa'] ?? 3);
    $stmt = $conn->prepare("SELECT p.id_pedido, c.nombre AS cliente FROM pedido p LEFT JOIN cliente c ON p.id_cliente = c.id_cliente WHERE p.numero_mesa = ? AND p.estado IN ('pendiente','listo') ORDER BY p.id_pedido DESC LIMIT 1");
    $stmt->bind_param("i", $mesa);
    $stmt->execute();
    $ped = $stmt->get_result()->fetch_assoc();

    if (!$ped) {
        echo json_encode(["status" => "empty", "items" => [], "subtotal" => "0.00"]);
        exit;
    }

    $id_pedido = intval($ped['id_pedido']);
    $stmtDet = $conn->prepare("SELECT pl.nombre, d.cantidad, d.subtotal FROM detalle_pedido d JOIN plato pl ON d.id_plato = pl.id_plato WHERE d.id_pedido = ?");
    $stmtDet->bind_param("i", $id_pedido);
    $stmtDet->execute();
    $resDet = $stmtDet->get_result();

    $items = [];
    $subtotal = 0.0;
    while ($r = $resDet->fetch_assoc()) {
        $items[] = $r;
        $subtotal += floatval($r['subtotal']);
    }

    echo json_encode([
        "status" => "ok",
        "id_pedido" => $id_pedido,
        "cliente" => $ped['cliente'] ?: "Cliente Salón Mesa $mesa",
        "items" => $items,
        "subtotal" => number_format($subtotal, 2, '.', '')
    ]);
    exit;
}

if ($accion === 'procesar_pago') {
    $id_pedido   = intval($_POST['id_pedido'] ?? 0);
    $numero_mesa = intval($_POST['numero_mesa'] ?? 0);
    $subtotal    = round(floatval($_POST['subtotal'] ?? 0), 2);
    $impuestos   = round(floatval($_POST['impuestos'] ?? 0), 2);
    $propina     = round(floatval($_POST['propina'] ?? 0), 2);
    $total       = round(floatval($_POST['total'] ?? 0), 2);
    $metodo_pago = $_POST['metodo_pago'] ?? 'Efectivo';

    if ($id_pedido <= 0 || $total <= 0) {
        echo json_encode(["status" => "error", "mensaje" => "No hay un pedido activo válido para cobrar en esta mesa."]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO factura (id_pedido, subtotal, impuestos, propina, total, metodo_pago, fecha_emision) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("idddds", $id_pedido, $subtotal, $impuestos, $propina, $total, $metodo_pago);

    if ($stmt->execute()) {
        $id_factura = $stmt->insert_id;
        $conn->query("UPDATE pedido SET estado = 'pagado' WHERE id_pedido = $id_pedido");
        if ($numero_mesa > 0) {
            $conn->query("UPDATE mesa SET estado = 'Libre' WHERE numero_mesa = $numero_mesa");
        }
        echo json_encode([
            "status" => "ok",
            "id_factura" => $id_factura,
            "mensaje" => "Pago procesado (Recibo #$id_factura) y Mesa $numero_mesa liberada."
        ]);
    } else {
        echo json_encode(["status" => "error", "mensaje" => "Este pedido ya fue facturado anteriormente."]);
    }
    exit;
}
?>
