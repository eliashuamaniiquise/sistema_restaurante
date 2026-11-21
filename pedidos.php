<?php
require_once "php/auth.php";
require_once "php/conexion.php";

$mesas = [];
$platos = [];
if (!$db_error) {
    $resM = $conn->query("SELECT * FROM mesa ORDER BY numero_mesa ASC");
    while ($m = $resM->fetch_assoc()) $mesas[] = $m;

    $resP = $conn->query("SELECT * FROM plato WHERE disponible = 1 ORDER BY id_plato ASC");
    while ($p = $resP->fetch_assoc()) $platos[] = $p;
}

$emojis = [
    'Entradas' => '&#129367;',
    'Platos de fondo' => '&#127835;',
    'Bebidas' => '&#129380;'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Módulo de Pedidos - SistemDR Don Dunay</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="app-shell">
  <?php include "php/sidebar.php"; ?>

  <main class="main-area">
    <div class="pos-topbar">
      <div><strong>SistemDR - Don Dunay</strong> &nbsp;|&nbsp; <?php echo htmlspecialchars(etiquetaRol($usuario_rol)); ?>: <?php echo htmlspecialchars($usuario_nombre); ?></div>
      <div>
        <span id="relojPOS"><?php echo date('h:i A'); ?></span>
        <button class="mobile-menu-btn" style="margin-left:10px;" onclick="toggleSidebar()">&#9776; Menú</button>
      </div>
    </div>

    <h1 class="page-title" style="margin-bottom:16px;">Módulo de pedidos</h1>

    <div class="pos-layout">
      <!-- Izquierda: Carta y Categorías -->
      <div class="card">
        <div class="form-group" style="max-width:220px;">
          <label class="form-label">Toma de pedidos</label>
          <select id="selectMesaPOS" class="form-control">
            <?php foreach ($mesas as $m): ?>
              <option value="<?php echo $m['numero_mesa']; ?>" <?php echo $m['numero_mesa']==3 ? 'selected' : ''; ?>>
                Mesa <?php echo $m['numero_mesa']; ?> (<?php echo $m['estado']; ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="cat-tabs">
          <button type="button" class="cat-tab" onclick="filtrarCategoriaPOS('Entradas', this)">Entradas</button>
          <button type="button" class="cat-tab active" onclick="filtrarCategoriaPOS('Platos de fondo', this)">Platos de fondo</button>
          <button type="button" class="cat-tab" onclick="filtrarCategoriaPOS('Bebidas', this)">Bebidas</button>
          <button type="button" class="cat-tab" onclick="filtrarCategoriaPOS('Todos', this)">Todos</button>
        </div>

        <div class="platos-grid">
          <?php foreach ($platos as $pl): ?>
            <?php $visible = ($pl['categoria'] === 'Platos de fondo') ? 'flex' : 'none'; ?>
            <div class="plato-card" data-categoria="<?php echo htmlspecialchars($pl['categoria']); ?>" style="display:<?php echo $visible; ?>;">
              <div class="plato-thumb"><?php echo $emojis[$pl['categoria']] ?? '&#127869;'; ?></div>
              <div class="plato-footer">
                <div class="plato-info">
                  <?php echo htmlspecialchars($pl['nombre']); ?><br>
                  S/. <?php echo number_format($pl['precio'], 2); ?>
                </div>
                <button type="button" class="btn-add-dish" onclick="agregarAlCarrito(<?php echo $pl['id_plato']; ?>, '<?php echo addslashes($pl['nombre']); ?>', <?php echo $pl['precio']; ?>)">+</button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Derecha: Detalle del Pedido -->
      <div class="card" style="display:flex;flex-direction:column;justify-content:space-between;">
        <div>
          <h2 class="card-title">Detalle del pedido</h2>
          <div id="carritoLista"></div>
        </div>

        <div style="margin-top:18px;">
          <div class="form-group">
            <label class="form-label">Notas</label>
            <textarea id="notasPOS" class="form-control" rows="3" placeholder="Ej. Sin cebolla, poco ají..."></textarea>
          </div>

          <div class="pos-total-row">
            <span>Total:</span>
            <span id="posTotalMonto">S/. 69.00</span>
          </div>

          <button type="button" class="btn btn-primary btn-block" style="height:42px;" onclick="enviarPedidoACocina()">Enviar a cocina</button>
        </div>
      </div>
    </div>
  </main>
</div>

<script src="js/api.js"></script>
<script src="js/main.js"></script>
</body>
</html>
