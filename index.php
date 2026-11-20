<?php
declare(strict_types=1);
require_once __DIR__ . '/php/config.php';

if (isset($_SESSION['usuario'])) {
    header('Location: app.php');
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sistema DR | Acceso</title>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="login-page">
  <main class="login-shell">
    <section class="login-brand">
      <div class="brand-mark">DR</div>
      <p>RESTAURANTE</p>
      <h1>Sistema DR</h1>
      <span>Gestión integral de restaurante</span>
    </section>
    <section class="login-card">
      <h2>Bienvenido</h2>
      <p>Ingresa tus credenciales para continuar</p>
      <form id="loginForm">
        <label for="usuario">Usuario</label>
        <input id="usuario" name="usuario" autocomplete="username" required>
        <label for="password">Contraseña</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        <div id="loginError" class="form-error" role="alert"></div>
        <button class="button primary" type="submit">Iniciar sesión</button>
      </form>
      <small>Usa admin / admin123 para probar.</small>
    </section>
  </main>
  <script src="assets/js/login.js"></script>
</body>
</html>
