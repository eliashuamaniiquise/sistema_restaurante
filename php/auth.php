<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function current_user(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function require_login(): array
{
    $usuario = current_user();
    if (!$usuario) {
        json_response(['error' => 'Debes iniciar sesion'], 401);
    }
    return $usuario;
}

function require_role(array $roles): array
{
    $usuario = require_login();
    if (!in_array($usuario['rol'], $roles, true)) {
        json_response(['error' => 'No tienes permisos para esta accion'], 403);
    }
    return $usuario;
}
