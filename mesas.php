<?php
require_once "php/auth.php";
require_once "php/conexion.php";

$mesas = [];
$reservas = [];
if (!$db_error) {
    $resM = $conn->query("SELECT * FROM mesa ORDER BY numero_mesa ASC");
    while ($m = $resM->fetch_assoc()) $mesas[] = $m;

    $resR = $conn->query("SELECT r.id_reserva, c.nombre AS cliente, r.numero_mesa, DATE_FORMAT(r.fecha_hora, '%d/%m/%Y %h:%i %p') AS fecha, r.estado FROM reserva r JOIN cliente c ON r.id_cliente = c.id_cliente ORDER BY r.id_reserva DESC LIMIT 10");
    while ($r = $resR->fetch_assoc()) $reservas[] = $r;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mesas y Reservas - SistemDR Don Dunay</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="app-shell">
  <?php include "php/sidebar.php"; ?>

  <main class="main-area">
    <div class="page-header-bar">
      <h1 class="page-title">Gestión de mesas y Reservas</h1>
      <button class="mobile-menu-btn" onclick="toggleSidebar()">&#9776; Menú</button>
    </div>

    <div class="mesas-layout">
      <!-- Izquierda: Cuadrícula de Mesas -->
      <div>
        <div class="mesas-grid">
          <?php foreach ($mesas as $m): ?>
            <div class="mesa-card">
              <span class="mesa-badge badge-<?php echo $m['estado']; ?>"><?php echo $m['estado']; ?></span>
              <div class="mesa-title">Mesa <?php echo $m['numero_mesa']; ?></div>
              <div class="mesa-cap">Capacidad: <?php echo $m['capacidad']; ?></div>
              <div class="mesa-actions">
                <button type="button" class="btn btn-green btn-sm btn-block" onclick="actualizarMesa(<?php echo $m['numero_mesa']; ?>, 'Ocupada')">Asignar cliente</button>
                <button type="button" class="btn btn-green btn-sm btn-block" onclick="actualizarMesa(<?php echo $m['numero_mesa']; ?>, 'Libre')">Liberar mesa</button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Derecha: Reservas Activas -->
      <div class="card">
        <h2 class="card-title">Reservas activas</h2>
        <form id="formReserva" onsubmit="registrarReserva(event)">
          <div class="form-group">
            <label class="form-label">Registrar nueva reserva</label>
            <input type="text" name="cliente" class="form-control" placeholder="Cliente" required>
          </div>
          <div class="form-group">
            <input type="text" name="telefono" class="form-control" placeholder="Teléfono" required>
          </div>
          <div class="form-group">
            <input type="datetime-local" name="fecha_hora" class="form-control" required>
          </div>
          <div class="form-group">
            <input type="number" name="comensales" class="form-control" placeholder="Comensales" min="1" max="12" required>
          </div>
          <div class="form-group">
            <input type="text" name="comentarios" class="form-control" placeholder="Comentarios">
          </div>
          <div class="form-group">
            <select name="numero_mesa" class="form-control">
              <?php foreach ($mesas as $m): ?>
                <option value="<?php echo $m['numero_mesa']; ?>">Mesa <?php echo $m['numero_mesa']; ?> (Cap. <?php echo $m['capacidad']; ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-primary btn-block">Confirmar reserva</button>
        </form>

        <h3 class="card-title" style="margin-top:18px;font-size:14px;">Lista de Reservas</h3>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>Cliente</th>
                <th>Mesa</th>
                <th>Fecha/Hora</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($reservas as $r): ?>
                <tr>
                  <td><?php echo htmlspecialchars($r['cliente']); ?></td>
                  <td><?php echo $r['numero_mesa']; ?></td>
                  <td><?php echo $r['fecha']; ?></td>
                  <td>
                    <span style="color:<?php echo $r['estado']==='Confirmada' ? '#2458D9' : '#64748B'; ?>;font-weight:600;">
                      <?php echo $r['estado']; ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>

<script src="js/api.js"></script>
<script src="js/main.js"></script>
<script>
async function actualizarMesa(numeroMesa, estado) {
  const fd = new FormData();
  fd.append('accion', 'cambiar_estado');
  fd.append('numero_mesa', numeroMesa);
  fd.append('estado', estado);
  const res = await SistemaAPI.postForm('php/mesas_api.php', fd);
  if (res.status === 'ok') {
    location.reload();
  } else {
    alert(res.mensaje);
  }
}

async function registrarReserva(e) {
  e.preventDefault();
  const fd = new FormData(e.target);
  fd.append('accion', 'crear_reserva');
  const res = await SistemaAPI.postForm('php/mesas_api.php', fd);
  alert(res.mensaje);
  if (res.status === 'ok') location.reload();
}
</script>
</body>
</html>
