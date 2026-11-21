<?php
// /php/sidebar.php - Barra lateral maestra (#19376E) con 7 pestañas según Figma
$pagina_actual = basename($_SERVER['PHP_SELF']);
$rol_label = etiquetaRol($usuario_rol);

$menu_items = [
    ['id' => 'dashboard',  'file' => 'dashboard.php',    'label' => 'Panel principal',  'icon' => '<path d="M3 10.5L12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9.5z" fill="none" stroke="currentColor" stroke-width="2"/>'],
    ['id' => 'mesas',      'file' => 'mesas.php',        'label' => 'Mesas y Reservas', 'icon' => '<rect x="3" y="4" width="18" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M3 10h18M9 4v16" stroke="currentColor" stroke-width="2"/>'],
    ['id' => 'pedidos',    'file' => 'pedidos.php',      'label' => 'Pedidos',          'icon' => '<path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2" fill="none" stroke="currentColor" stroke-width="2"/>'],
    ['id' => 'cocina',     'file' => 'cocina_vista.php', 'label' => 'Cocina',           'icon' => '<path d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6Z" fill="none" stroke="currentColor" stroke-width="2"/>'],
    ['id' => 'inventario', 'file' => 'inventario.php',   'label' => 'Inventario',       'icon' => '<path d="M21 8l-9-5-9 5v8l9 5 9-5V8zM3 8l9 5 9-5M12 13v8" fill="none" stroke="currentColor" stroke-width="2"/>'],
    ['id' => 'caja',       'file' => 'caja.php',         'label' => 'Caja',             'icon' => '<rect x="2" y="5" width="20" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M2 10h20" stroke="currentColor" stroke-width="2"/>'],
    ['id' => 'reportes',   'file' => 'reportes.php',     'label' => 'Reportes',         'icon' => '<path d="M18 20V10M12 20V4M6 20v-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>']
];
?>
<aside class="sidebar" id="appSidebar">
  <div class="sidebar-brand">SistemDR<br>Don Dunay</div>

  <div class="sidebar-role-pill">
    <span class="sidebar-role-avatar">&#128100;</span>
    <span><?php echo htmlspecialchars($rol_label); ?></span>
  </div>

  <nav class="sidebar-nav">
    <?php foreach ($menu_items as $item): ?>
      <?php if (puedeVerModulo($usuario_rol, $item['id'])): ?>
        <a href="<?php echo $item['file']; ?>" class="nav-item <?php echo ($pagina_actual === $item['file']) ? 'active' : ''; ?>">
          <svg viewBox="0 0 24 24"><?php echo $item['icon']; ?></svg>
          <span><?php echo $item['label']; ?></span>
        </a>
      <?php endif; ?>
    <?php endforeach; ?>
  </nav>

  <a href="php/logout.php" class="sidebar-logout">Cerrar sesión</a>
</aside>
