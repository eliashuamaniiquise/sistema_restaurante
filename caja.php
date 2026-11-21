<?php
require_once "php/auth.php";
require_once "php/conexion.php";

$mesas = [];
if (!$db_error) {
    $resM = $conn->query("SELECT * FROM mesa ORDER BY numero_mesa ASC");
    while ($m = $resM->fetch_assoc()) $mesas[] = $m;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Módulo de Caja y Facturación - SistemDR Don Dunay</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="app-shell">
  <?php include "php/sidebar.php"; ?>

  <main class="main-area">
    <div class="page-header-bar">
      <h1 class="page-title">Módulo de caja y facturación</h1>
      <button class="mobile-menu-btn" onclick="toggleSidebar()">&#9776; Menú</button>
    </div>

    <div class="caja-layout">
      <!-- Izquierda: Calcular Cuenta y Pago -->
      <div class="card">
        <h2 class="card-title">Calcular Cuenta y Pago</h2>

        <div class="form-group">
          <label class="form-label">Seleccionar mesa</label>
          <select id="cajaMesaSelect" class="form-control" onchange="cargarCuentaMesa()">
            <?php foreach ($mesas as $m): ?>
              <option value="<?php echo $m['numero_mesa']; ?>" <?php echo $m['numero_mesa']==3 ? 'selected' : ''; ?>>
                Mesa <?php echo $m['numero_mesa']; ?> (<?php echo $m['estado']; ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <input type="hidden" id="cajaIdPedido" value="1">

        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>Artículo</th>
                <th>Cantidad</th>
                <th style="text-align:right;">Importe</th>
              </tr>
            </thead>
            <tbody id="cajaItemsBody">
              <tr><td>Lomo Saltado</td><td>1</td><td style="text-align:right;">S/. 45.00</td></tr>
              <tr><td>Inca Kola 1 L</td><td>2</td><td style="text-align:right;">S/. 24.00</td></tr>
            </tbody>
          </table>
        </div>

        <div class="caja-summary-box">
          <div class="caja-line">
            <span>Subtotal:</span>
            <span id="cajaSubtotal">S/. 69.00</span>
          </div>
          <div class="caja-line">
            <span>Impuestos (18%):</span>
            <span id="cajaImpuestos">S/. 12.42</span>
          </div>
          <div class="caja-line" style="align-items:center;">
            <span>Propina (S/.):</span>
            <input type="number" step="0.50" min="0" id="cajaPropinaInput" value="5.00" oninput="recalcularTotalesCaja()" style="width:90px;text-align:right;padding:3px 6px;border:1px solid #CBD3DF;border-radius:4px;">
          </div>
          <div class="caja-line total">
            <span>Total:</span>
            <span id="cajaTotal">S/. 86.42</span>
          </div>
        </div>

        <div class="form-group" style="margin-top:14px;">
          <label class="form-label">Método de pago</label>
          <select id="cajaMetodoPago" class="form-control">
            <option value="Efectivo">Efectivo</option>
            <option value="Tarjeta">Tarjeta</option>
          </select>
        </div>

        <div class="caja-btn-row">
          <button type="button" class="btn btn-primary" onclick="procesarPagoCaja()">Procesar pago</button>
          <button type="button" class="btn btn-soft-blue" onclick="window.print()">Imprimir recibo</button>
        </div>
      </div>

      <!-- Derecha: Vista Previa del Recibo -->
      <div class="card">
        <h2 class="card-title">Vista previa del recibo</h2>

        <div class="receipt-paper" id="receiptPrintArea">
          <div class="receipt-header">Restaurante Don Dunay</div>
          <div class="receipt-row">
            <span>Fecha: <?php echo date('d/m/Y'); ?></span>
            <span>Hora: <?php echo date('H:i'); ?></span>
          </div>
          <div>ID recibo: <span id="reciboId">15200</span></div>
          <div class="receipt-divider"></div>
          <div><strong>Info de cliente:</strong></div>
          <div>Cliente: <span id="reciboCliente">Danan Dunay</span></div>
          <div>Mesa: <span id="reciboMesa">Mesa 3</span></div>
          <div class="receipt-divider"></div>

          <div id="reciboItemsLista">
            <div class="receipt-row"><span>Lomo Saltado x 1</span><span>S/. 45.00</span></div>
            <div class="receipt-row"><span>Inca Kola 1 L x 2</span><span>S/. 24.00</span></div>
          </div>

          <div class="receipt-divider"></div>
          <div class="receipt-row"><span>Subtotal:</span><span id="reciboSub">S/. 69.00</span></div>
          <div class="receipt-row"><span>Impuesto:</span><span id="reciboImp">S/. 12.42</span></div>
          <div class="receipt-row"><span>Propina:</span><span id="reciboProp">S/. 5.00</span></div>
          <div class="receipt-row" style="font-weight:700;font-size:13.5px;margin-top:6px;">
            <span>Total:</span><span id="reciboTot">S/. 86.42</span>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<script src="js/api.js"></script>
<script src="js/main.js"></script>
<script>
let subtotalActualCaja = 69.00;

function recalcularTotalesCaja() {
  const propina = document.getElementById('cajaPropinaInput').value;
  const calc = calcularTotalCaja(subtotalActualCaja, 0.18, propina);

  document.getElementById('cajaSubtotal').textContent = 'S/. ' + calc.subtotal;
  document.getElementById('cajaImpuestos').textContent = 'S/. ' + calc.impuestos;
  document.getElementById('cajaTotal').textContent = 'S/. ' + calc.total;

  document.getElementById('reciboSub').textContent = 'S/. ' + calc.subtotal;
  document.getElementById('reciboImp').textContent = 'S/. ' + calc.impuestos;
  document.getElementById('reciboProp').textContent = 'S/. ' + calc.propina;
  document.getElementById('reciboTot').textContent = 'S/. ' + calc.total;
}

async function cargarCuentaMesa() {
  const mesa = document.getElementById('cajaMesaSelect').value;
  document.getElementById('reciboMesa').textContent = 'Mesa ' + mesa;

  const res = await SistemaAPI.getJSON('php/caja_api.php?accion=consultar_mesa&mesa=' + mesa);
  const tbody = document.getElementById('cajaItemsBody');
  const recList = document.getElementById('reciboItemsLista');

  if (res.status === 'ok') {
    document.getElementById('cajaIdPedido').value = res.id_pedido;
    document.getElementById('reciboCliente').textContent = res.cliente;
    subtotalActualCaja = parseFloat(res.subtotal);

    tbody.innerHTML = res.items.map(it =>
      `<tr><td>${it.nombre}</td><td>${it.cantidad}</td><td style="text-align:right;">S/. ${parseFloat(it.subtotal).toFixed(2)}</td></tr>`
    ).join('');

    recList.innerHTML = res.items.map(it =>
      `<div class="receipt-row"><span>${it.nombre} x ${it.cantidad}</span><span>S/. ${parseFloat(it.subtotal).toFixed(2)}</span></div>`
    ).join('');
  } else {
    document.getElementById('cajaIdPedido').value = 0;
    subtotalActualCaja = 0;
    tbody.innerHTML = '<tr><td colspan="3" style="color:#64748B;">Sin pedidos pendientes de pago en esta mesa.</td></tr>';
    recList.innerHTML = '<div>Sin consumos activos</div>';
  }
  recalcularTotalesCaja();
}

async function procesarPagoCaja() {
  const idPedido = document.getElementById('cajaIdPedido').value;
  const mesa = document.getElementById('cajaMesaSelect').value;
  const propina = document.getElementById('cajaPropinaInput').value;
  const metodo = document.getElementById('cajaMetodoPago').value;
  const calc = calcularTotalCaja(subtotalActualCaja, 0.18, propina);

  const fd = new FormData();
  fd.append('accion', 'procesar_pago');
  fd.append('id_pedido', idPedido);
  fd.append('numero_mesa', mesa);
  fd.append('subtotal', calc.subtotal);
  fd.append('impuestos', calc.impuestos);
  fd.append('propina', calc.propina);
  fd.append('total', calc.total);
  fd.append('metodo_pago', metodo);

  const res = await SistemaAPI.postForm('php/caja_api.php', fd);
  alert(res.mensaje);
  if (res.status === 'ok') {
    document.getElementById('reciboId').textContent = res.id_factura;
  }
}

document.addEventListener('DOMContentLoaded', cargarCuentaMesa);
</script>
</body>
</html>
