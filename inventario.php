<?php
require_once "php/auth.php";
require_once "php/conexion.php";

$insumos = [];
$platos = [];
if (!$db_error) {
    $resI = $conn->query("SELECT * FROM insumo ORDER BY id_insumo ASC");
    while ($i = $resI->fetch_assoc()) $insumos[] = $i;

    $resP = $conn->query("SELECT * FROM plato ORDER BY id_plato ASC");
    while ($p = $resP->fetch_assoc()) $platos[] = $p;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Platos e Inventario - SistemDR Don Dunay</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="app-shell">
  <?php include "php/sidebar.php"; ?>

  <main class="main-area cream-theme">
    <div class="page-header-bar">
      <h1 class="page-title">Platos e inventario</h1>
      <button class="mobile-menu-btn" onclick="toggleSidebar()">&#9776; Menú</button>
    </div>

    <div class="inv-layout">
      <!-- Izquierda: Control de insumos y stock -->
      <div class="card">
        <h2 class="card-title">Control de insumos y stock</h2>

        <div id="bannerRegistroExitoso" class="banner-alert banner-success" style="display:none;">
          &#10004; Registro exitoso
        </div>

        <form id="formInsumo" onsubmit="guardarInsumo(event)">
          <div class="form-group">
            <label class="form-label">Nombre de insumo</label>
            <input type="text" name="nombre" id="invNombre" class="form-control" placeholder="Ej. Papas (kg), Pollo..." required>
          </div>

          <div class="form-row-3">
            <div class="form-group">
              <label class="form-label">Cantidad</label>
              <input type="number" step="0.01" name="cantidad" id="invCantidad" class="form-control" placeholder="0" required>
            </div>
            <div class="form-group">
              <label class="form-label">Unidad</label>
              <select name="unidad" class="form-control">
                <option value="kg">kg</option>
                <option value="unidades">unidades</option>
                <option value="litros">litros</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Fecha</label>
              <input type="date" name="fecha" class="form-control" value="<?php echo date('Y-m-d'); ?>">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Proveedor</label>
            <input type="text" name="proveedor" class="form-control" placeholder="Nombre del proveedor">
          </div>

          <div id="bannerStockLleno" class="banner-alert banner-warning" style="display:none;">
            &#9888; Stock lleno
          </div>

          <button type="submit" class="btn btn-amber btn-block" style="margin-bottom:16px;">Registrar insumo</button>
        </form>

        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>Insumo</th>
                <th>Stock Actual</th>
                <th>Stock mínimo</th>
                <th>Stock máximo</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($insumos as $in): ?>
                <?php
                  $act = floatval($in['stock_actual']);
                  $min = floatval($in['stock_minimo']);
                  $max = floatval($in['stock_maximo']);
                ?>
                <tr>
                  <td><strong><?php echo htmlspecialchars($in['nombre']); ?></strong></td>
                  <td><?php echo number_format($act, 0); ?></td>
                  <td><?php echo number_format($min, 0); ?></td>
                  <td><?php echo number_format($max, 0); ?></td>
                  <td>
                    <?php if ($act >= $max): ?>
                      <span style="color:#16803D;font-weight:600;">&#10004; full</span>
                    <?php elseif ($act <= $min): ?>
                      <span style="color:#B86607;font-weight:600;">&#9888; bajo</span>
                    <?php else: ?>
                      <span style="color:#64748B;">— normal</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Derecha: Catálogo de platos con switch disponible -->
      <div class="card">
        <h2 class="card-title">Catálogo de platos</h2>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>Plato</th>
                <th>Categoría</th>
                <th>Precio (S/.)</th>
                <th>Disponible</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($platos as $pl): ?>
                <tr>
                  <td><strong><?php echo htmlspecialchars($pl['nombre']); ?></strong></td>
                  <td><?php echo htmlspecialchars($pl['categoria']); ?></td>
                  <td>S/<?php echo number_format($pl['precio'], 2); ?></td>
                  <td>
                    <label class="switch">
                      <input type="checkbox" <?php echo $pl['disponible'] ? 'checked' : ''; ?> onchange="cambiarDisponibilidad(<?php echo $pl['id_plato']; ?>, this.checked)">
                      <span class="slider"></span>
                    </label>
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
async function guardarInsumo(e) {
  e.preventDefault();
  document.getElementById('bannerStockLleno').style.display = 'none';
  document.getElementById('bannerRegistroExitoso').style.display = 'none';

  const fd = new FormData(e.target);
  fd.append('accion', 'registrar_insumo');
  const res = await SistemaAPI.postForm('php/inventario_api.php', fd);

  if (res.status === 'warning') {
    verificarStock(100, 100); // Dispara alerta "Stock lleno" (RF07)
  } else if (res.status === 'ok') {
    document.getElementById('bannerRegistroExitoso').style.display = 'flex';
    alert('Registro exitoso');
    setTimeout(() => location.reload(), 800);
  } else {
    alert(res.mensaje);
  }
}

async function cambiarDisponibilidad(idPlato, checked) {
  const fd = new FormData();
  fd.append('accion', 'toggle_plato');
  fd.append('id_plato', idPlato);
  fd.append('disponible', checked ? 1 : 0);
  await SistemaAPI.postForm('php/inventario_api.php', fd);
}
</script>
</body>
</html>
