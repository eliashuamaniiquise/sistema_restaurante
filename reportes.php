<?php
require_once "php/auth.php";
require_once "php/conexion.php";

$facturas = [];
if (!$db_error) {
    $res = $conn->query("SELECT f.id_factura, COALESCE(c.nombre, 'Danan Dunay') AS cliente, DATE_FORMAT(f.fecha_emision, '%d/%m/%Y') AS fecha, f.metodo_pago, f.total FROM factura f JOIN pedido p ON f.id_pedido = p.id_pedido LEFT JOIN cliente c ON p.id_cliente = c.id_cliente ORDER BY f.id_factura DESC");
    while ($r = $res->fetch_assoc()) $facturas[] = $r;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reportes de Ventas - SistemDR Don Dunay</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="app-shell">
  <?php include "php/sidebar.php"; ?>

  <main class="main-area">
    <div class="page-header-bar">
      <h1 class="page-title">Reportes de ventas y administración</h1>
      <button class="mobile-menu-btn" onclick="toggleSidebar()">&#9776; Menú</button>
    </div>

    <!-- Pestañas de filtro -->
    <div class="report-tabs">
      <button type="button" class="report-tab active" onclick="seleccionarTabReporte('Ventas', this)">Ventas</button>
      <button type="button" class="report-tab" onclick="seleccionarTabReporte('Pagos', this)">Pagos</button>
      <button type="button" class="report-tab" onclick="seleccionarTabReporte('Clientes', this)">Clientes</button>
      <button type="button" class="report-tab" onclick="seleccionarTabReporte('Comidas', this)">Comidas</button>
    </div>

    <!-- 4 KPIs de Reportes -->
    <section class="kpi-grid">
      <div class="kpi-card">
        <div class="kpi-label">Ventas totales</div>
        <div class="kpi-value">S/ 35,305.00</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Pagos registrados</div>
        <div class="kpi-value">1,779</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Clientes atendidos</div>
        <div class="kpi-value">3,504</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Platos vendidos</div>
        <div class="kpi-value">120</div>
      </div>
    </section>

    <!-- Gráfico de Barras Semanal (#F5AE60) -->
    <div class="card" style="margin-bottom:20px;">
      <h2 class="card-title">Ventas semanales (S/.)</h2>
      <div class="chart-bars-wrap">
        <div class="bar-col"><div class="bar-fill" style="height:48%;"></div><span class="bar-label">Lun</span></div>
        <div class="bar-col"><div class="bar-fill" style="height:78%;"></div><span class="bar-label">Mar</span></div>
        <div class="bar-col"><div class="bar-fill" style="height:36%;"></div><span class="bar-label">Mié</span></div>
        <div class="bar-col"><div class="bar-fill" style="height:76%;"></div><span class="bar-label">Jue</span></div>
        <div class="bar-col"><div class="bar-fill" style="height:92%;"></div><span class="bar-label">Vie</span></div>
        <div class="bar-col"><div class="bar-fill" style="height:74%;"></div><span class="bar-label">Sáb</span></div>
        <div class="bar-col"><div class="bar-fill" style="height:76%;"></div><span class="bar-label">Dom</span></div>
      </div>
    </div>

    <!-- Tabla Histórica de Facturas -->
    <div class="card">
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID factura</th>
              <th>Cliente</th>
              <th>Fecha</th>
              <th>Método de pago</th>
              <th style="text-align:right;">Total (S/.)</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($facturas as $f): ?>
              <tr>
                <td><?php echo 20100 + intval($f['id_factura']); ?></td>
                <td><?php echo htmlspecialchars($f['cliente']); ?></td>
                <td><?php echo $f['fecha']; ?></td>
                <td><?php echo htmlspecialchars($f['metodo_pago']); ?></td>
                <td style="text-align:right;font-weight:600;"><?php echo number_format($f['total'], 2); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<script src="js/main.js"></script>
<script>
function seleccionarTabReporte(nombre, btn) {
  document.querySelectorAll('.report-tab').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
}
</script>
</body>
</html>
