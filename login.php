<?php
declare(strict_types=1);
require_once __DIR__ . '/php/config.php';

$usuario = trim((string)($_POST['usuario'] ?? ''));
$password = (string)($_POST['password'] ?? '');

if ($usuario === '' || $password === '') {
    json_response(['error' => 'Completa todos los campos'], 422);
}

$stmt = $db->prepare('SELECT id, nombre, usuario, password, rol, estado FROM usuario WHERE usuario = ? LIMIT 1');
$stmt->bind_param('s', $usuario);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || (int)$user['estado'] !== 1 || !password_verify($password, $user['password'])) {
    json_response(['error' => 'Usuario o contraseña incorrectos'], 401);
}

session_regenerate_id(true);
$_SESSION['usuario'] = [
    'id' => (int)$user['id'],
    'nombre' => $user['nombre'],
    'usuario' => $user['usuario'],
    'rol' => $user['rol'],
];

json_response(['ok' => true, 'usuario' => $_SESSION['usuario']]);
