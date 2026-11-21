<?php
session_start();
require_once "conexion.php";
header('Content-Type: application/json; charset=utf-8');

$res = $conn->query("SELECT f.id_factura, COALESCE(c.nombre, 'Cliente General') AS cliente, DATE_FORMAT(f.fecha_emision, '%d/%m/%Y') AS fecha, f.metodo_pago, f.total FROM factura f JOIN pedido p ON f.id_pedido = p.id_pedido LEFT JOIN cliente c ON p.id_cliente = c.id_cliente ORDER BY f.id_factura DESC");
$rows = [];
while ($r = $res->fetch_assoc()) {
    $rows[] = $r;
}
echo json_encode(["status" => "ok", "facturas" => $rows]);
?>
