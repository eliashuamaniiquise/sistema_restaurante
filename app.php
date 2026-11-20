<?php
declare(strict_types=1);
require_once __DIR__ . '/php/auth.php';
$usuario = require_login();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sistema DR | Restaurante Don Dunay</title>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body data-rol="<?= htmlspecialchars($usuario['rol']) ?>">
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand"><div class="brand-mark small">DR</div><div><strong>Sistema DR</strong><span>Don Dunay</span></div></div>
    <nav>
      <button class="nav-item active" data-view="dashboard"><span>⌂</span>Dashboard</button>
      <button class="nav-item" data-view="mesas"><span>▦</span>Mesas y reservas</button>
      <button class="nav-item" data-view="pedidos"><span>＋</span>Pedidos</button>
      <button class="nav-item" data-view="cocina"><span>◷</span>Cocina</button>
      <button class="nav-item" data-view="menu"><span>🍽</span>Platos e insumos</button>
      <button class="nav-item" data-view="caja"><span>₲</span>Caja</button>
      <button class="nav-item admin-only" data-view="reportes"><span>▥</span>Reportes</button>
      <button class="nav-item admin-only" data-view="usuarios"><span>♙</span>Usuarios</button>
    </nav>
  </aside>
  <main class="app-main">
    <header class="topbar">
      <button class="menu-toggle" id="menuToggle" aria-label="Abrir menú">☰</button>
      <div><strong id="pageTitle">Dashboard</strong><span id="pageSubtitle">Resumen del restaurante</span></div>
      <div class="user-menu"><div class="avatar"><?= strtoupper(substr($usuario['nombre'], 0, 1)) ?></div><div><strong><?= htmlspecialchars($usuario['nombre']) ?></strong><span><?= htmlspecialchars($usuario['rol']) ?></span></div><a href="logout.php" title="Cerrar sesión">↪</a></div>
    </header>
    <section id="content" class="content" aria-live="polite"></section>
  </main>
  <div id="toast" class="toast"></div>
  <div id="modal" class="modal" hidden></div>
  <script src="assets/js/app.js"></script>
</body>
</html>
