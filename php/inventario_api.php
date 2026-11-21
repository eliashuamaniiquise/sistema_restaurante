<?php
// /php/inventario_api.php - Control de insumos (RF06, RF07) y disponibilidad de platos
session_start();
require_once "conexion.php";
header('Content-Type: application/json; charset=utf-8');

if ($db_error) {
    echo json_encode(["status" => "error", "mensaje" => $db_error]);
    exit;
}

$accion = $_POST['accion'] ?? '';

if ($accion === 'registrar_insumo') {
    $nombre    = trim($_POST['nombre'] ?? '');
    $cantidad  = floatval($_POST['cantidad'] ?? 0);
    $unidad    = trim($_POST['unidad'] ?? 'kg');
    $fecha     = $_POST['fecha'] ?? date('Y-m-d');
    $proveedor = trim($_POST['proveedor'] ?? '');

    if ($nombre === '' || $cantidad <= 0) {
        echo json_encode(["status" => "error", "mensaje" => "Ingrese el nombre del insumo y una cantidad válida."]);
        exit;
    }

    // Verificar si ya existe el insumo para sumar stock y evaluar límite máximo
    $stmtFind = $conn->prepare("SELECT id_insumo, stock_actual, stock_maximo FROM insumo WHERE nombre LIKE ? LIMIT 1");
    $like = "%$nombre%";
    $stmtFind->bind_param("s", $like);
    $stmtFind->execute();
    $res = $stmtFind->get_result();

    if ($row = $res->fetch_assoc()) {
        $nuevo_stock = $row['stock_actual'] + $cantidad;
        $maximo = floatval($row['stock_maximo']);
        if ($row['stock_actual'] >= $maximo || $nuevo_stock > $maximo) {
            echo json_encode(["status" => "warning", "mensaje" => "Stock lleno (capacidad máxima: $maximo $unidad)"]);
            exit;
        }
        $stmtUpd = $conn->prepare("UPDATE insumo SET stock_actual = ?, fecha_ingreso = ?, proveedor = ? WHERE id_insumo = ?");
        $stmtUpd->bind_param("dssi", $nuevo_stock, $fecha, $proveedor, $row['id_insumo']);
        $stmtUpd->execute();
        echo json_encode(["status" => "ok", "mensaje" => "Registro exitoso"]);
        exit;
    } else {
        $min = 15.00;
        $max = max(100.00, $cantidad * 2);
        $stmtIns = $conn->prepare("INSERT INTO insumo (nombre, stock_actual, stock_minimo, stock_maximo, unidad, fecha_ingreso, proveedor) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmtIns->bind_param("sdddsss", $nombre, $cantidad, $min, $max, $unidad, $fecha, $proveedor);
        $stmtIns->execute();
        echo json_encode(["status" => "ok", "mensaje" => "Registro exitoso"]);
        exit;
    }
}

if ($accion === 'toggle_plato') {
    $id_plato = intval($_POST['id_plato'] ?? 0);
    $disponible = intval($_POST['disponible'] ?? 1);
    $stmt = $conn->prepare("UPDATE plato SET disponible = ? WHERE id_plato = ?");
    $stmt->bind_param("ii", $disponible, $id_plato);
    $stmt->execute();
    echo json_encode(["status" => "ok", "mensaje" => "Disponibilidad actualizada"]);
    exit;
}

echo json_encode(["status" => "error", "mensaje" => "Acción no válida"]);
?>
