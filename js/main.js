/**
 * Sistema DR — Validaciones e Interactividad Frontend (Paul Tarifa & Dunay Cervantes)
 */

// 1. Toggle de menú lateral en pantallas móviles (RNF09)
function toggleSidebar() {
  const sb = document.getElementById('appSidebar');
  if (sb) sb.classList.toggle('open');
}

// 2. Validación de Stock Máximo en Inventario (RF07)
function verificarStock(actual, maximo) {
  const act = parseFloat(actual);
  const max = parseFloat(maximo);
  if (!isNaN(act) && !isNaN(max) && act >= max) {
    alert('Stock lleno');
    const warnBanner = document.getElementById('bannerStockLleno');
    if (warnBanner) warnBanner.style.display = 'flex';
    return false;
  }
  return true;
}

// 3. Cálculo financiero redondeado estrictamente a 2 decimales (RF05)
function calcularTotalCaja(subtotal, porcentajeIgv = 0.18, propina = 0) {
  const sub = Number(parseFloat(subtotal || 0).toFixed(2));
  const igv = Number((sub * porcentajeIgv).toFixed(2));
  const prop = Number(parseFloat(propina || 0).toFixed(2));
  const total = Number((sub + igv + prop).toFixed(2));
  return {
    subtotal: sub.toFixed(2),
    impuestos: igv.toFixed(2),
    propina: prop.toFixed(2),
    total: total.toFixed(2)
  };
}

// 4. Carrito del Módulo de Pedidos (POS Mesero - Pestaña 3)
let carritoPOS = [
  { id_plato: 5, nombre: 'Lomo saltado', precio: 45.00, cantidad: 1 },
  { id_plato: 13, nombre: 'Inca Kola 1 L', precio: 12.00, cantidad: 2 }
];

function filtrarCategoriaPOS(categoria, btn) {
  document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
  if (btn) btn.classList.add('active');

  document.querySelectorAll('.plato-card').forEach(card => {
    const cat = card.getAttribute('data-categoria');
    if (categoria === 'Todos' || cat === categoria) {
      card.style.display = 'flex';
    } else {
      card.style.display = 'none';
    }
  });
}

function agregarAlCarrito(idPlato, nombre, precio) {
  const existente = carritoPOS.find(item => item.id_plato === idPlato);
  if (existente) {
    existente.cantidad += 1;
  } else {
    carritoPOS.push({
      id_plato: idPlato,
      nombre: nombre,
      precio: parseFloat(precio),
      cantidad: 1
    });
  }
  renderizarCarritoPOS();
}

function cambiarCantidadPOS(idPlato, delta) {
  const idx = carritoPOS.findIndex(item => item.id_plato === idPlato);
  if (idx === -1) return;
  carritoPOS[idx].cantidad += delta;
  if (carritoPOS[idx].cantidad <= 0) {
    carritoPOS.splice(idx, 1);
  }
  renderizarCarritoPOS();
}

function renderizarCarritoPOS() {
  const contenedor = document.getElementById('carritoLista');
  const totalEl = document.getElementById('posTotalMonto');
  if (!contenedor || !totalEl) return;

  if (carritoPOS.length === 0) {
    contenedor.innerHTML = '<p style="color:#64748B;font-size:13px;padding:12px 0;">Seleccione platos de la carta para armar el pedido.</p>';
    totalEl.textContent = 'S/. 0.00';
    return;
  }

  let total = 0;
  contenedor.innerHTML = carritoPOS.map(item => {
    const sub = item.precio * item.cantidad;
    total += sub;
    return `
      <div class="cart-item-row">
        <span style="font-weight:500;">${item.nombre}</span>
        <div class="qty-controls">
          <button type="button" class="qty-btn" onclick="cambiarCantidadPOS(${item.id_plato}, -1)">-</button>
          <span>${item.cantidad}</span>
          <button type="button" class="qty-btn" onclick="cambiarCantidadPOS(${item.id_plato}, 1)">+</button>
        </div>
        <span style="font-weight:600;">S/. ${sub.toFixed(2)}</span>
      </div>
    `;
  }).join('');

  totalEl.textContent = `S/. ${total.toFixed(2)}`;
}

async function enviarPedidoACocina() {
  // Caso de prueba 3: Validar que el pedido no esté vacío antes de enviar
  if (carritoPOS.length === 0) {
    alert('Advertencia: No puede enviar un pedido vacío. Agregue al menos un plato.');
    return;
  }

  const mesa = document.getElementById('selectMesaPOS').value;
  const notas = document.getElementById('notasPOS').value.trim();

  try {
    const res = await SistemaAPI.postJSON('php/guardar_pedido.php', {
      numero_mesa: parseInt(mesa, 10),
      notas: notas,
      items: carritoPOS
    });
    if (res.status === 'ok') {
      alert(res.mensaje || 'Pedido registrado y enviado a cocina correctamente.');
      carritoPOS = [];
      document.getElementById('notasPOS').value = '';
      renderizarCarritoPOS();
    } else {
      alert(res.mensaje || 'Error al registrar el pedido.');
    }
  } catch (err) {
    alert('Error de conexión con el servidor XAMPP.');
  }
}

document.addEventListener('DOMContentLoaded', () => {
  if (document.getElementById('carritoLista')) {
    renderizarCarritoPOS();
  }
});
