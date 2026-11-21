<?php
// /php/login.php - Validación de credenciales, registro y redirección por rol (RF01, RNF08)
session_start();
require_once "conexion.php";

if ($db_error) {
    header("Location: ../index.html?error=bd");
    exit;
}

$accion = $_POST['accion'] ?? 'login';

if ($accion === 'registro') {
    $nombre = trim($_POST['nombre'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $clave = trim($_POST['clave'] ?? '');
    $rol = $_POST['rol'] ?? 'mesero';

    if ($nombre === '' || $usuario === '' || $clave === '') {
        header("Location: ../index.html?error=campos");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO usuario (nombre, usuario, clave, rol) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nombre, $usuario, $clave, $rol);
    if ($stmt->execute()) {
        header("Location: ../index.html?ok=registrado");
    } else {
        header("Location: ../index.html?error=duplicado");
    }
    exit;
}

$usuario = trim($_POST['usuario'] ?? '');
$clave   = trim($_POST['clave'] ?? '');
$rol     = trim($_POST['rol'] ?? '');

if ($usuario === '' || $clave === '') {
    header("Location: ../index.html?error=campos");
    exit;
}

if ($rol !== '') {
    $stmt = $conn->prepare("SELECT id_usuario, nombre, usuario, clave, rol FROM usuario WHERE usuario = ? AND rol = ? LIMIT 1");
    $stmt->bind_param("ss", $usuario, $rol);
} else {
    $stmt = $conn->prepare("SELECT id_usuario, nombre, usuario, clave, rol FROM usuario WHERE usuario = ? LIMIT 1");
    $stmt->bind_param("s", $usuario);
}

$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    // Soporta contraseñas planas de prueba ('1234') o password_hash
    if ($clave === $row['clave'] || password_verify($clave, $row['clave'])) {
        $_SESSION['id_usuario'] = $row['id_usuario'];
        $_SESSION['nombre']     = $row['nombre'];
        $_SESSION['usuario']    = $row['usuario'];
        $_SESSION['rol']        = $row['rol'];

        // Redirección inteligente según rol
        if ($row['rol'] === 'cocinero') {
            header("Location: ../cocina_vista.php");
        } elseif ($row['rol'] === 'mesero') {
            header("Location: ../pedidos.php");
        } elseif ($row['rol'] === 'recepcionista') {
            header("Location: ../mesas.php");
        } else {
            header("Location: ../dashboard.php");
        }
        exit;
    }
}

header("Location: ../index.html?error=credenciales");
exit;
?>
