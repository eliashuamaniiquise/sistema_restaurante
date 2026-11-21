<?php
require_once "php/auth.php";
require_once "php/conexion.php";

$mesas_ocupadas = 3;
$total_mesas = 8;
$pedidos_pend = 3;
$ventas_dia = 1250.00;
$alertas_stock = 2;

if (!$db_error) {
    $r1 = $conn->query("SELECT COUNT(*) AS oc FROM mesa WHERE estado = 'Ocupada'")->fetch_assoc();
    $r2 = $conn->query("SELECT COUNT(*) AS tot FROM mesa")->fetch_assoc();
    $r3 = $conn->query("SELECT COUNT(*) AS pend FROM pedido WHERE estado = 'pendiente'")->fetch_assoc();
    $r4 = $conn->query("SELECT COALESCE(SUM(total), 0) AS v FROM factura")->fetch_assoc();
    $r5 = $conn->query("SELECT COUNT(*) AS bajos FROM insumo WHERE stock_actual <= stock_minimo")->fetch_assoc();

    $mesas_ocupadas = $r1['oc'];
    $total_mesas    = $r2['tot'];
    $pedidos_pend   = $r3['pend'];
    $ventas_dia     = floatval($r4['v']) > 0 ? floatval($r4['v']) : 1250.00;
    $alertas_stock  = $r5['bajos'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel Principal - SistemDR Don Dunay</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="app-shell">
  <?php include "php/sidebar.php"; ?>

  <main class="main-area">
    <div class="page-header-bar">
      <h1 class="page-title">Panel principal - <?php echo htmlspecialchars(etiquetaRol($usuario_rol)); ?></h1>
      <button class="mobile-menu-btn" onclick="toggleSidebar()">&#9776; Menú</button>
    </div>

    <!-- 4 Tarjetas KPI -->
    <section class="kpi-grid">
      <div class="kpi-card">
        <div class="kpi-label">Mesas ocupadas</div>
        <div class="kpi-value"><?php echo "$mesas_ocupadas/$total_mesas"; ?></div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Pedidos pendientes</div>
        <div class="kpi-value"><?php echo $pedidos_pend; ?></div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Venta del Día</div>
        <div class="kpi-value">S/ <?php echo number_format($ventas_dia, 2); ?></div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Alertas de Stock</div>
        <div class="kpi-value"><?php echo $alertas_stock; ?> Bajos</div>
      </div>
    </section>

    <!-- Alertas del Sistema + Accesos Rápidos Azules -->
    <section class="dashboard-split">
      <div class="card">
        <h2 class="card-title">Alertas del Sistema</h2>

        <div class="alert-row">
          <div class="alert-row-left">
            <span style="color:#B86607;font-size:18px;">&#9888;</span>
            <span><strong>Stock bajo:</strong> Pollo deshilachado (5 kg rest.)</span>
          </div>
          <a href="inventario.php" class="btn btn-sm" style="background:#EA580C;color:#FFF;">Agregar</a>
        </div>

        <div class="alert-row">
          <div class="alert-row-left">
            <span style="color:#2458D9;font-size:18px;">&#128337;</span>
            <span><strong>Pedido pendiente:</strong> Mesa 8 espera &gt; 15 min.</span>
          </div>
          <a href="cocina_vista.php" class="btn btn-soft-blue btn-sm">Ver cocina</a>
        </div>

        <div class="alert-row">
          <div class="alert-row-left">
            <span style="color:#B86607;font-size:18px;">&#9888;</span>
            <span><strong>Stock bajo:</strong> Papas fritas (8 kg rest.)</span>
          </div>
          <a href="inventario.php" class="btn btn-outline btn-sm">Revisar</a>
        </div>
      </div>

      <div class="quick-actions-col">
        <a href="pedidos.php" class="quick-action-card">
          <div class="quick-action-icon">&#128203;</div>
          <div>
            <div class="quick-action-title">Gestionar pedidos</div>
            <div class="quick-action-sub">Gestionar pedidos y enviarlos a cocina.</div>
          </div>
        </a>

        <a href="mesas.php" class="quick-action-card">
          <div class="quick-action-icon">&#128506;</div>
          <div>
            <div class="quick-action-title">Mapa de mesas</div>
            <div class="quick-action-sub">Consultar mesas y reservas.</div>
          </div>
        </a>

        <a href="reportes.php" class="quick-action-card">
          <div class="quick-action-icon">&#128202;</div>
          <div>
            <div class="quick-action-title">Consultar reportes</div>
            <div class="quick-action-sub">Consultar ventas y pagos.</div>
          </div>
        </a>
      </div>
    </section>
  </main>
</div>
<script src="js/api.js"></script>
<script src="js/main.js"></script>
</body>
</html>
