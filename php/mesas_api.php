<?php
session_start();
require_once "conexion.php";
header('Content-Type: application/json; charset=utf-8');

if ($db_error) {
    echo json_encode(["status" => "error", "mensaje" => $db_error]);
    exit;
}

$accion = $_POST['accion'] ?? '';

if ($accion === 'cambiar_estado') {
    $numero_mesa = intval($_POST['numero_mesa'] ?? 0);
    $estado = $_POST['estado'] ?? 'Libre';
    if (!in_array($estado, ['Libre', 'Ocupada', 'Reservada'])) {
        echo json_encode(["status" => "error", "mensaje" => "Estado inválido"]);
        exit;
    }
    $stmt = $conn->prepare("UPDATE mesa SET estado = ? WHERE numero_mesa = ?");
    $stmt->bind_param("si", $estado, $numero_mesa);
    $stmt->execute();
    echo json_encode(["status" => "ok", "mensaje" => "Mesa $numero_mesa actualizada a $estado"]);
    exit;
}

if ($accion === 'crear_reserva') {
    $nombre = trim($_POST['cliente'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $fecha_hora = trim($_POST['fecha_hora'] ?? '');
    $comensales = intval($_POST['comensales'] ?? 2);
    $comentarios = trim($_POST['comentarios'] ?? '');
    $numero_mesa = intval($_POST['numero_mesa'] ?? 1);

    if ($nombre === '' || $telefono === '' || $fecha_hora === '') {
        echo json_encode(["status" => "error", "mensaje" => "Complete cliente, teléfono y fecha/hora"]);
        exit;
    }

    $stmtCli = $conn->prepare("INSERT INTO cliente (nombre, telefono) VALUES (?, ?)");
    $stmtCli->bind_param("ss", $nombre, $telefono);
    $stmtCli->execute();
    $id_cliente = $stmtCli->insert_id;

    $stmtRes = $conn->prepare("INSERT INTO reserva (id_cliente, numero_mesa, fecha_hora, num_comensales, comentarios, estado) VALUES (?, ?, ?, ?, ?, 'Confirmada')");
    $stmtRes->bind_param("iisis", $id_cliente, $numero_mesa, $fecha_hora, $comensales, $comentarios);
    $stmtRes->execute();

    $stmtMesa = $conn->prepare("UPDATE mesa SET estado = 'Reservada' WHERE numero_mesa = ?");
    $stmtMesa->bind_param("i", $numero_mesa);
    $stmtMesa->execute();

    echo json_encode(["status" => "ok", "mensaje" => "Reserva confirmada para $nombre en Mesa $numero_mesa"]);
    exit;
}

if ($accion === 'estado_reserva') {
    $id_reserva = intval($_POST['id_reserva'] ?? 0);
    $estado = $_POST['estado'] ?? 'Confirmada';
    $stmt = $conn->prepare("UPDATE reserva SET estado = ? WHERE id_reserva = ?");
    $stmt->bind_param("si", $estado, $id_reserva);
    $stmt->execute();
    echo json_encode(["status" => "ok", "mensaje" => "Reserva actualizada a $estado"]);
    exit;
}

echo json_encode(["status" => "error", "mensaje" => "Acción no reconocida"]);
?>
