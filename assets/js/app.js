const content = document.getElementById('content');
const pageTitle = document.getElementById('pageTitle');
const pageSubtitle = document.getElementById('pageSubtitle');
const toast = document.getElementById('toast');
const modal = document.getElementById('modal');
const rol = document.body.dataset.rol;

const titles = {
  dashboard: ['Dashboard', 'Resumen general del restaurante'],
  mesas: ['Mesas y reservas', 'Control de capacidad y reservas'],
  pedidos: ['Pedidos', 'Registro y seguimiento de órdenes'],
  cocina: ['Cocina', 'Monitor de preparación'],
  menu: ['Platos e insumos', 'Catálogo e inventario'],
  caja: ['Caja', 'Cobros y comprobantes'],
  reportes: ['Reportes', 'Ventas y productos'],
  usuarios: ['Usuarios', 'Administración de cuentas'],
};

async function api(action, data = null) {
  const options = data ? { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) } : {};
  const response = await fetch(`api.php?action=${encodeURIComponent(action)}`, options);
  const payload = await response.json();
  if (!response.ok) throw new Error(payload.error || 'Error en la operación');
  return payload;
}

function notify(message, isError = false) {
  toast.textContent = message;
  toast.className = `toast show ${isError ? 'error' : ''}`;
  setTimeout(() => toast.className = 'toast', 2600);
}

function money(value) {
  return `S/ ${Number(value || 0).toFixed(2)}`;
}

function esc(value) {
  return String(value ?? '').replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char]));
}

function openModal(html) {
  modal.innerHTML = html;
  modal.hidden = false;
  modal.onclick = event => { if (event.target === modal) closeModal(); };
  modal.querySelectorAll('[data-close-modal]').forEach(button => button.onclick = closeModal);
}

function closeModal() {
  modal.hidden = true;
  modal.innerHTML = '';
}

async function loadView(name) {
  document.querySelectorAll('.nav-item').forEach(item => item.classList.toggle('active', item.dataset.view === name));
  pageTitle.textContent = titles[name][0];
  pageSubtitle.textContent = titles[name][1];
  content.innerHTML = '<div class="loading"><span></span>Cargando...</div>';
  try {
    if (name === 'dashboard') await renderDashboard();
    if (name === 'mesas') await renderMesas();
    if (name === 'pedidos') await renderPedidos();
    if (name === 'cocina') await renderCocina();
    if (name === 'menu') await renderMenu();
    if (name === 'caja') await renderCaja();
    if (name === 'reportes') await renderReportes();
    if (name === 'usuarios') await renderUsuarios();
  } catch (error) {
    content.innerHTML = `<div class="empty-state"><b>No se pudo cargar</b><p>${esc(error.message)}</p></div>`;
  }
  document.getElementById('sidebar').classList.remove('open');
}

async function renderDashboard() {
  const data = await api('dashboard');
  content.innerHTML = `
    <div class="kpi-grid">
      ${kpi('Ventas de hoy', money(data.ventas_hoy), '↑ Operación', 'green')}
      ${kpi('Pedidos', data.pedidos_hoy, 'Pedidos registrados', 'blue')}
      ${kpi('Mesas ocupadas', data.mesas_ocupadas, 'En servicio', 'orange')}
      ${kpi('Cocina', data.pedidos_pendientes, 'Por atender', 'purple')}
    </div>
    ${data.stock_alertas ? `<div class="alert-banner"><strong>Inventario bajo</strong><span>Hay ${data.stock_alertas} insumos por reponer.</span></div>` : ''}
    <section class="panel"><div class="panel-header"><div><h3>Pedidos recientes</h3><p>Últimas operaciones registradas</p></div><button class="button secondary" data-go="pedidos">Ver pedidos</button></div>
      <div class="table-wrap"><table><thead><tr><th>#</th><th>Mesa/tipo</th><th>Estado</th><th>Total</th><th>Hora</th></tr></thead><tbody>${data.recientes.map(p => `<tr><td>#${p.id}</td><td>${p.tipo === 'Mesa' ? `Mesa ${esc(p.numero)}` : esc(p.tipo)}</td><td><span class="status ${estadoClass(p.estado)}">${esc(p.estado)}</span></td><td>${money(p.total)}</td><td>${esc(p.creado_en?.slice(11,16) || '')}</td></tr>`).join('') || '<tr><td colspan="5">Sin pedidos</td></tr>'}</tbody></table></div>
    </section>`;
  content.querySelector('[data-go]').onclick = () => loadView('pedidos');
}

function kpi(label, value, detail, color) {
  return `<article class="kpi ${color}"><div class="kpi-label">${esc(label)}</div><strong>${esc(value)}</strong><span>${esc(detail)}</span></article>`;
}

function estadoClass(value) {
  return ({ Disponible:'success', Pendiente:'warning', 'En preparacion':'info', Listo:'success', Servido:'neutral', Cancelado:'danger', Ocupada:'danger', Reservada:'warning', Confirmada:'success' }[value] || 'neutral');
}

async function renderMesas() {
  const data = await api('mesas');
  content.innerHTML = `
    <div class="toolbar"><div class="segmented"><button class="active">Mesas</button><button id="showReservations">Reservas</button></div><button class="button primary" id="newReserva">+ Nueva reserva</button></div>
    <div id="mesaArea" class="mesa-grid">${data.mesas.map(m => `<article class="mesa-card ${estadoClass(m.estado)}"><div><strong>Mesa ${esc(m.numero)}</strong><span>${m.capacidad} personas</span></div><span class="status ${estadoClass(m.estado)}">${esc(m.estado)}</span><select data-mesa="${m.id}"><option ${m.estado === 'Disponible' ? 'selected' : ''}>Disponible</option><option ${m.estado === 'Ocupada' ? 'selected' : ''}>Ocupada</option><option ${m.estado === 'Reservada' ? 'selected' : ''}>Reservada</option><option ${m.estado === 'Mantenimiento' ? 'selected' : ''}>Mantenimiento</option></select></article>`).join('')}</div>
    <section id="reservationArea" class="panel" hidden><div class="panel-header"><div><h3>Reservas</h3><p>Próximas visitas</p></div></div><div class="table-wrap"><table><thead><tr><th>Cliente</th><th>Mesa</th><th>Fecha y hora</th><th>Personas</th><th>Estado</th></tr></thead><tbody>${data.reservas.map(r => `<tr><td>${esc(r.cliente)}<br><small>${esc(r.telefono)}</small></td><td>Mesa ${esc(r.mesa_numero)}</td><td>${esc(r.fecha_hora)}</td><td>${r.personas}</td><td><span class="status ${estadoClass(r.estado)}">${esc(r.estado)}</span></td></tr>`).join('') || '<tr><td colspan="5">No hay reservas</td></tr>'}</tbody></table></div></section>`;
  content.querySelectorAll('[data-mesa]').forEach(select => select.onchange = async () => {
    await api('mesa_actualizar', { id: select.dataset.mesa, estado: select.value });
    notify('Estado de mesa actualizado');
    loadView('mesas');
  });
  document.getElementById('showReservations').onclick = event => {
    content.querySelectorAll('.segmented button').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
    document.getElementById('mesaArea').hidden = true;
    document.getElementById('reservationArea').hidden = false;
  };
  content.querySelector('.segmented button').onclick = () => loadView('mesas');
  document.getElementById('newReserva').onclick = () => openReserva(data.mesas);
}

function openReserva(mesas) {
  openModal(`<div class="modal-card"><div class="modal-header"><h3>Nueva reserva</h3><button data-close-modal>×</button></div><form id="reservationForm" class="form-grid"><label>Cliente<input name="cliente" required></label><label>Teléfono<input name="telefono"></label><label>Mesa<select name="mesa_id">${mesas.map(m => `<option value="${m.id}">Mesa ${esc(m.numero)} - ${m.capacidad} personas</option>`).join('')}</select></label><label>Fecha y hora<input name="fecha_hora" type="datetime-local" required></label><label>Personas<input name="personas" type="number" min="1" value="2" required></label><label>Observación<input name="observacion"></label><div class="form-actions"><button type="button" class="button secondary" data-close-modal>Cancelar</button><button class="button primary">Guardar reserva</button></div></form></div>`);
  const form = document.getElementById('reservationForm');
  form.onsubmit = async event => {
    event.preventDefault();
    const data = Object.fromEntries(new FormData(form));
    data.personas = Number(data.personas);
    await api('reserva_crear', data);
    closeModal(); notify('Reserva creada'); loadView('mesas');
  };
}

async function renderPedidos() {
  const data = await api('pedidos');
  content.innerHTML = `<div class="toolbar"><div><h3>Órdenes activas</h3></div><button class="button primary" id="newPedido">+ Nuevo pedido</button></div><section class="panel"><div class="table-wrap"><table><thead><tr><th>#</th><th>Servicio</th><th>Mesero</th><th>Estado</th><th>Total</th><th></th></tr></thead><tbody>${data.pedidos.map(p => `<tr><td>#${p.id}</td><td>${p.tipo === 'Mesa' ? `Mesa ${esc(p.mesa_numero)}` : esc(p.tipo)}</td><td>${esc(p.mesero)}</td><td><span class="status ${estadoClass(p.estado)}">${esc(p.estado)}</span></td><td>${money(p.total)}</td><td><select class="inline-select" data-pedido="${p.id}"><option>Pendiente</option><option>En preparacion</option><option>Listo</option><option>Servido</option><option>Cancelado</option></select></td></tr>`).join('') || '<tr><td colspan="6">Sin pedidos</td></tr>'}</tbody></table></div></section>`;
  content.querySelectorAll('[data-pedido]').forEach(select => {
    const row = select.closest('tr');
    const status = row.querySelector('.status').textContent;
    select.value = status;
    select.onchange = async () => { await api('pedido_estado', { id: select.dataset.pedido, estado: select.value }); notify('Pedido actualizado'); loadView('pedidos'); };
  });
  document.getElementById('newPedido').onclick = () => openPedido(data.platos, data.mesas);
}

function openPedido(platos, mesas) {
  openModal(`<div class="modal-card wide"><div class="modal-header"><h3>Nuevo pedido</h3><button data-close-modal>×</button></div><form id="pedidoForm"><div class="form-grid"><label>Tipo de servicio<select name="tipo" id="tipoPedido"><option>Mesa</option><option>Llevar</option><option>Delivery</option></select></label><label id="mesaField">Mesa<select name="mesa_id">${mesas.map(m => `<option value="${m.id}">${esc(m.numero)}</option>`).join('')}</select></label></div><div class="product-picker">${platos.map(p => `<label class="product-item"><input type="checkbox" value="${p.id}" data-name="${esc(p.nombre)}" data-price="${p.precio}" data-stock="${p.stock}"><div><strong>${esc(p.nombre)}</strong><span>${money(p.precio)} · Stock ${p.stock}</span></div></label>`).join('')}</div><div class="form-actions"><strong id="pedidoTotal">S/ 0.00</strong><button type="button" class="button secondary" data-close-modal>Cancelar</button><button class="button primary">Registrar pedido</button></div></form></div>`);
  const form = document.getElementById('pedidoForm');
  document.getElementById('tipoPedido').onchange = event => document.getElementById('mesaField').hidden = event.target.value !== 'Mesa';
  form.querySelectorAll('input[type=checkbox]').forEach(input => input.onchange = () => calculatePedido(form));
  form.onsubmit = async event => {
    event.preventDefault();
    const formData = new FormData(form);
    const items = [...form.querySelectorAll('input[type=checkbox]:checked')].map(input => ({ id:Number(input.value), cantidad:1, precio:Number(input.dataset.price) }));
    if (!items.length) return notify('Selecciona al menos un plato', true);
    const data = { tipo:formData.get('tipo'), mesa_id:formData.get('tipo') === 'Mesa' ? Number(formData.get('mesa_id')) : null, items };
    await api('pedido_crear', data); closeModal(); notify('Pedido registrado'); loadView('pedidos');
  };
}

function calculatePedido(form) {
  const total = [...form.querySelectorAll('input[type=checkbox]:checked')].reduce((sum, input) => sum + Number(input.dataset.price), 0);
  document.getElementById('pedidoTotal').textContent = money(total);
}

async function renderCocina() {
  const data = await api('cocina');
  content.innerHTML = `<div class="kitchen-grid">${data.pedidos.map(p => `<article class="ticket ${estadoClass(p.estado)}"><div class="ticket-head"><strong>#${p.id}</strong><span>${p.mesa_numero ? `Mesa ${esc(p.mesa_numero)}` : 'Servicio'}</span></div><p>${esc(p.detalle)}</p><div class="ticket-foot"><span class="status ${estadoClass(p.estado)}">${esc(p.estado)}</span><select data-cocina="${p.id}"><option>Pendiente</option><option>En preparacion</option><option>Listo</option><option>Servido</option></select></div></article>`).join('') || '<div class="empty-state"><b>No hay pedidos pendientes</b><p>La cocina está al día.</p></div>'}</div>`;
  content.querySelectorAll('[data-cocina]').forEach(select => {
    const card = select.closest('.ticket');
    select.value = card.querySelector('.status').textContent;
    select.onchange = async () => { await api('pedido_estado', { id:select.dataset.cocina, estado:select.value }); notify('Estado enviado'); loadView('cocina'); };
  });
}

async function renderMenu() {
  const data = await api('menu');
  content.innerHTML = `<div class="toolbar"><div class="segmented"><button class="active" id="platosTab">Platos</button><button id="insumosTab">Insumos</button></div>${rol === 'Administrador' ? '<button class="button primary" id="newPlato">+ Nuevo plato</button>' : ''}</div>
    <section id="platosArea" class="panel"><div class="card-grid">${data.platos.map(p => `<article class="dish-card"><div class="dish-icon">🍽</div><div><span>${esc(p.categoria)}</span><h3>${esc(p.nombre)}</h3><p>${esc(p.descripcion)}</p><strong>${money(p.precio)}</strong></div><small>Stock: ${p.stock}</small></article>`).join('')}</div></section>
    <section id="insumosArea" class="panel" hidden><div class="table-wrap"><table><thead><tr><th>Insumo</th><th>Unidad</th><th>Stock</th><th>Mínimo</th><th>Estado</th></tr></thead><tbody>${data.insumos.map(i => `<tr><td>${esc(i.nombre)}</td><td>${esc(i.unidad)}</td><td>${i.stock}</td><td>${i.stock_minimo}</td><td><span class="status ${Number(i.stock)<=Number(i.stock_minimo)?'danger':'success'}">${Number(i.stock)<=Number(i.stock_minimo)?'Reponer':'Disponible'}</span></td></tr>`).join('')}</tbody></table></div></section>`;
  document.getElementById('platosTab').onclick = event => {
    content.querySelectorAll('.segmented button').forEach(b => b.classList.remove('active')); event.target.classList.add('active');
    document.getElementById('platosArea').hidden = false; document.getElementById('insumosArea').hidden = true;
  };
  document.getElementById('insumosTab').onclick = event => {
    content.querySelectorAll('.segmented button').forEach(b => b.classList.remove('active')); event.target.classList.add('active');
    document.getElementById('platosArea').hidden = true; document.getElementById('insumosArea').hidden = false;
  };
  document.getElementById('newPlato')?.addEventListener('click', () => openPlato());
}

function openPlato() {
  openModal(`<div class="modal-card"><div class="modal-header"><h3>Nuevo plato</h3><button data-close-modal>×</button></div><form id="platoForm" class="form-grid"><label>Nombre<input name="nombre" required></label><label>Categoría<input name="categoria" required></label><label>Descripción<input name="descripcion"></label><label>Precio<input name="precio" type="number" min="0" step="0.01" required></label><label>Stock inicial<input name="stock" type="number" min="0" value="0" required></label><div class="form-actions"><button type="button" class="button secondary" data-close-modal>Cancelar</button><button class="button primary">Guardar</button></div></form></div>`);
  document.getElementById('platoForm').onsubmit = async event => {
    event.preventDefault();
    const data = Object.fromEntries(new FormData(event.target));
    data.precio = Number(data.precio); data.stock = Number(data.stock);
    await api('plato_guardar', data); closeModal(); notify('Plato guardado'); loadView('menu');
  };
}

async function renderCaja() {
  const data = await api('caja');
  content.innerHTML = `<div class="toolbar"><div><h3>Cuentas pendientes</h3></div></div><section class="panel"><div class="table-wrap"><table><thead><tr><th>Pedido</th><th>Servicio</th><th>Total</th><th></th></tr></thead><tbody>${data.pendientes.map(p => `<tr><td>#${p.id}</td><td>${p.tipo === 'Mesa' ? `Mesa ${esc(p.mesa_numero)}` : esc(p.tipo)}</td><td>${money(p.total)}</td><td><button class="button secondary" data-pagar="${p.id}" data-total="${p.total}">Cobrar</button></td></tr>`).join('') || '<tr><td colspan="4">No hay cuentas pendientes</td></tr>'}</tbody></table></div></section><section class="panel"><div class="panel-header"><div><h3>Comprobantes</h3><p>Últimos pagos registrados</p></div></div><div class="table-wrap"><table><thead><tr><th>Comprobante</th><th>Cliente</th><th>Tipo</th><th>Total</th></tr></thead><tbody>${data.facturas.map(f => `<tr><td>#${f.id}</td><td>${esc(f.cliente_nombre)}</td><td>${esc(f.tipo_pago)}</td><td>${money(f.total)}</td></tr>`).join('') || '<tr><td colspan="4">Sin comprobantes</td></tr>'}</tbody></table></div></section>`;
  content.querySelectorAll('[data-pagar]').forEach(button => button.onclick = () => openPago(button.dataset.pagar, button.dataset.total));
}

function openPago(id, total) {
  openModal(`<div class="modal-card"><div class="modal-header"><h3>Registrar pago</h3><button data-close-modal>×</button></div><form id="pagoForm" class="form-grid"><label>Cliente<input name="cliente" value="Cliente general"></label><label>Documento<input name="documento"></label><label>Tipo de pago<select name="tipo_pago"><option>Efectivo</option><option>Tarjeta</option></select></label><div class="pay-total"><span>Total a cobrar</span><strong>${money(total)}</strong></div><div class="form-actions"><button type="button" class="button secondary" data-close-modal>Cancelar</button><button class="button primary">Confirmar cobro</button></div></form></div>`);
  document.getElementById('pagoForm').onsubmit = async event => {
    event.preventDefault();
    const data = Object.fromEntries(new FormData(event.target)); data.pedido_id = Number(id);
    await api('pagar', data); closeModal(); notify('Pago registrado'); loadView('caja');
  };
}

async function renderReportes() {
  const data = await api('reportes');
  content.innerHTML = `<div class="report-grid"><section class="panel"><div class="panel-header"><div><h3>Ventas por día</h3><p>Últimos 30 días con registros</p></div></div><div class="table-wrap"><table><thead><tr><th>Fecha</th><th>Pedidos</th><th>Total</th></tr></thead><tbody>${data.ventas.map(v => `<tr><td>${esc(v.fecha)}</td><td>${v.pedidos}</td><td>${money(v.total)}</td></tr>`).join('') || '<tr><td colspan="3">Sin datos</td></tr>'}</tbody></table></div></section><section class="panel"><div class="panel-header"><div><h3>Productos vendidos</h3><p>Top 10</p></div></div><div class="table-wrap"><table><thead><tr><th>Plato</th><th>Cantidad</th><th>Total</th></tr></thead><tbody>${data.productos.map(p => `<tr><td>${esc(p.nombre)}</td><td>${p.cantidad}</td><td>${money(p.total)}</td></tr>`).join('') || '<tr><td colspan="3">Sin datos</td></tr>'}</tbody></table></div></section></div>`;
}

async function renderUsuarios() {
  const data = await api('usuarios');
  content.innerHTML = `<div class="toolbar"><div><h3>Equipo del restaurante</h3></div><button class="button primary" id="newUsuario">+ Nuevo usuario</button></div><section class="panel"><div class="table-wrap"><table><thead><tr><th>Nombre</th><th>Usuario</th><th>Rol</th><th>Estado</th></tr></thead><tbody>${data.usuarios.map(u => `<tr><td>${esc(u.nombre)}</td><td>${esc(u.usuario)}</td><td>${esc(u.rol)}</td><td><span class="status ${u.estado?'success':'danger'}">${u.estado?'Activo':'Inactivo'}</span></td></tr>`).join('')}</tbody></table></div></section>`;
  document.getElementById('newUsuario').onclick = () => openUsuario();
}

function openUsuario() {
  openModal(`<div class="modal-card"><div class="modal-header"><h3>Nuevo usuario</h3><button data-close-modal>×</button></div><form id="usuarioForm" class="form-grid"><label>Nombre completo<input name="nombre" required></label><label>Usuario<input name="usuario" required></label><label>Contraseña<input name="password" type="password" minlength="6" required></label><label>Rol<select name="rol"><option>Administrador</option><option>Recepcionista</option><option>Mesero</option><option>Cocinero</option></select></label><div class="form-actions"><button type="button" class="button secondary" data-close-modal>Cancelar</button><button class="button primary">Crear usuario</button></div></form></div>`);
  document.getElementById('usuarioForm').onsubmit = async event => {
    event.preventDefault();
    await api('usuario_guardar', Object.fromEntries(new FormData(event.target)));
    closeModal(); notify('Usuario creado'); loadView('usuarios');
  };
}

document.querySelectorAll('.nav-item').forEach(item => item.onclick = () => loadView(item.dataset.view));
document.getElementById('menuToggle').onclick = () => document.getElementById('sidebar').classList.toggle('open');

if (rol !== 'Administrador') document.querySelectorAll('.admin-only').forEach(item => item.remove());
loadView('dashboard');
