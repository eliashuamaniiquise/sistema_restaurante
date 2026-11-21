<?php
// /php/conexion.php - Conexión mysqli UTF-8 con MySQL en XAMPP
mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli("localhost", "root", "", "sistemdr");

if ($conn->connect_error) {
    $db_error = "No se pudo conectar a la base de datos 'sistemdr'. Verifique que MySQL esté iniciado en XAMPP y haya importado database.sql.";
} else {
    $conn->set_charset("utf8mb4");
    $db_error = null;
}
?>
