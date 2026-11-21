<?php
session_start();
require_once "conexion.php";
header('Content-Type: application/json; charset=utf-8');

$tabla = $_POST['tabla'] ?? '';
$id = intval($_POST['id'] ?? 0);

if ($tabla === 'reserva' && $id > 0) {
    $stmt = $conn->prepare("DELETE FROM reserva WHERE id_reserva = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo json_encode(["status" => "ok", "mensaje" => "Reserva eliminada"]);
    exit;
}

echo json_encode(["status" => "error", "mensaje" => "Operación no permitida"]);
?>
