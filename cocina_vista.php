<?php
require_once "php/auth.php";
require_once "php/conexion.php";

$pedidos = [];
if (!$db_error) {
    $sql = "SELECT p.id_pedido, p.numero_mesa, DATE_FORMAT(p.fecha_hora, '%h:%i %p') AS hora, p.notas_generales,
                   pl.nombre AS plato, d.cantidad, d.notas
            FROM pedido p
            JOIN detalle_pedido d ON p.id_pedido = d.id_pedido
            JOIN plato pl ON d.id_plato = pl.id_plato
            WHERE p.estado = 'pendiente'
            ORDER BY p.fecha_hora ASC";
    $res = $conn->query($sql);
    while ($row = $res->fetch_assoc()) {
        $pid = $row['id_pedido'];
        if (!isset($pedidos[$pid])) {
            $pedidos[$pid] = [
                'id_pedido' => $pid,
                'numero_mesa' => $row['numero_mesa'],
                'hora' => $row['hora'],
                'notas' => $row['notas_generales'] ?: ($row['notas'] ?: 'Normal'),
                'items' => []
            ];
        }
        $pedidos[$pid]['items'][] = $row['plato'] . " x " . $row['cantidad'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pantalla de Cocina - SistemDR Don Dunay</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="app-shell">
  <?php include "php/sidebar.php"; ?>

  <main class="main-area">
    <div class="page-header-bar">
      <h1 class="page-title">Pantalla de cocina - Pedidos pendientes</h1>
      <div>
        <button class="btn btn-outline btn-sm" onclick="location.reload()">&#8635; Actualizar</button>
        <button class="mobile-menu-btn" onclick="toggleSidebar()">&#9776; Menú</button>
      </div>
    </div>

    <div class="cocina-list">
      <?php if (empty($pedidos)): ?>
        <div class="card" style="text-align:center;padding:36px;color:#64748B;">
          No hay pedidos pendientes en cocina en este momento.
        </div>
      <?php else: ?>
        <?php foreach ($pedidos as $ped): ?>
          <div class="cocina-card" id="pedidoCard_<?php echo $ped['id_pedido']; ?>">
            <div>
              <div class="cocina-card-header">
                Pedido #<?php echo str_pad($ped['id_pedido'], 2, '0', STR_PAD_LEFT); ?> - Mesa <?php echo $ped['numero_mesa']; ?> - <?php echo $ped['hora']; ?>
              </div>
              <ul class="cocina-items">
                <?php foreach ($ped['items'] as $it): ?>
                  <li><?php echo htmlspecialchars($it); ?></li>
                <?php endforeach; ?>
              </ul>
              <div class="cocina-notes">Notas: <?php echo htmlspecialchars($ped['notas']); ?></div>
            </div>

            <div class="cocina-right">
              <span class="pill-pendiente">Pendiente</span>
              <button type="button" class="btn btn-soft-blue" onclick="marcarPedidoListo(<?php echo $ped['id_pedido']; ?>)">Pedido listo</button>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </main>
</div>

<script src="js/api.js"></script>
<script src="js/main.js"></script>
<script>
async function marcarPedidoListo(idPedido) {
  const fd = new FormData();
  fd.append('accion', 'marcar_listo');
  fd.append('id_pedido', idPedido);
  const res = await SistemaAPI.postForm('php/cocina.php', fd);
  if (res.status === 'ok') {
    const card = document.getElementById('pedidoCard_' + idPedido);
    if (card) card.remove();
    alert(res.mensaje);
  }
}
</script>
</body>
</html>
