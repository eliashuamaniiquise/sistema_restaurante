<?php
// /php/auth.php - Control de sesión activa y permisos por rol (RF08, RNF02)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.html?error=sesion");
    exit;
}

$usuario_nombre = $_SESSION['nombre'] ?? 'Usuario';
$usuario_rol = $_SESSION['rol'] ?? 'administrador';
$id_usuario_activo = intval($_SESSION['id_usuario']);

function etiquetaRol($rol) {
    $map = [
        'administrador' => 'Administrador',
        'recepcionista' => 'Recepcionista',
        'mesero'        => 'Mesero',
        'cocinero'      => 'Cocinero'
    ];
    return $map[$rol] ?? 'Administrador';
}

function puedeVerModulo($rol, $modulo) {
    if ($rol === 'administrador') return true;
    $permisos = [
        'recepcionista' => ['dashboard', 'mesas', 'inventario'],
        'mesero'        => ['dashboard', 'mesas', 'pedidos', 'caja'],
        'cocinero'      => ['dashboard', 'cocina']
    ];
    return in_array($modulo, $permisos[$rol] ?? []);
}
?>
