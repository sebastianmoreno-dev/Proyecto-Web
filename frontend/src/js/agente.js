// frontend/src/js/agente.js
const API = '/Backend/api';

// 1. Protección de acceso estandarizada (igual que en vendedor.js)
requireRol('agente');
const token = Auth.getToken();

// 2. Inicialización al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    renderNavbar(); // Dibuja la barra de navegación
    document.getElementById('sidebar-nombre').textContent = Auth.getNombre();
    cargarCitas();
});

// ── Navegación de Pestañas ───────────────────────────────────
function mostrarSeccion(nombre, btn) {
    document.querySelectorAll('.panel-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
    
    document.getElementById(`sec-${nombre}`).classList.add('active');
    btn.classList.add('active');
    
    if (nombre === 'citas') cargarCitas();
    if (nombre === 'ventas') cargarVentas();
}

// ── Cargar tabla de Citas ──────────────────────────────────
async function cargarCitas() {
    const tbody = document.getElementById('tabla-citas');
    tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;"><i class="fa-solid fa-spinner fa-spin"></i> Cargando...</td></tr>`;

    try {
        const res = await fetch(`${API}/agentes/citas`, {
            headers: { Authorization: `Bearer ${token}` }
        });
        
        if (!res.ok) throw new Error("Error al obtener las citas");
        const citas = await res.json();

        if (!citas.length) {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; color: var(--text-gray);">No tienes citas programadas.</td></tr>`;
            return;
        }

        tbody.innerHTML = citas.map(c => `
            <tr>
                <td><strong>${c.prd_titulo}</strong></td>
                <td>${c.cit_fecha}</td>
                <td>${c.cit_hora}</td>
                <td><span class="badge-estado ${c.estado.toLowerCase()}">${c.estado.toUpperCase()}</span></td>
                <td>${obtenerBotonesAccion(c.id_cita, c.id_estatus_cita, c.id_negociacion, c.pro_precio)}</td>
            </tr>
        `).join('');
    } catch (error) {
        tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; color:#C0392B;">Error de conexión.</td></tr>`;
    }
}

// ── Renderizado de botones lógicos ─────────────────────────
function obtenerBotonesAccion(idCita, idEstado, idNegociacion, precio) {
    // 1 = Pendiente, 2 = Confirmada, 3 = Cancelada, 4 = Realizada
    if (idEstado === 1) {
        return `
            <button class="btn-sm btn-primary" onclick="actualizarCita(${idCita}, 2)">Confirmar</button>
            <button class="btn-sm btn-danger" onclick="actualizarCita(${idCita}, 3)">Cancelar</button>
        `;
    } else if (idEstado === 2) {
        return `<button class="btn-sm btn-outline" style="margin-top:0;" onclick="actualizarCita(${idCita}, 4)">Marcar Realizada</button>`;
    } else if (idEstado === 4) {
        return `<button class="btn-sm" style="background:#155724; color:white;" onclick="registrarVenta(${idNegociacion}, ${precio})"><i class="fa-solid fa-check-double"></i> Cerrar Venta</button>`;
    }
    return `<span style="color:#999; font-size:0.8rem;">Sin acciones</span>`;
}

// ── Actualizar estado de Cita ──────────────────────────────
async function actualizarCita(idCita, idEstado) {
    try {
        const res = await fetch(`${API}/agentes/citas/${idCita}/estado`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` },
            body: JSON.stringify({ id_estado: idEstado })
        });
        if (!res.ok) throw new Error("Fallo en la actualización");
        cargarCitas(); 
    } catch (error) {
        alert("Ocurrió un error al actualizar la cita.");
    }
}

// ── Ejecución de la Máquina de Estados (Venta) ─────────────
async function registrarVenta(idNegociacion, precioBase) {
    const precioFinal = prompt("Confirma el precio final de cierre de la transacción (USD):", precioBase);
    if (precioFinal === null || precioFinal.trim() === "") return;

    try {
        const res = await fetch(`${API}/agentes/ventas`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` },
            body: JSON.stringify({ 
                id_negociacion: idNegociacion,
                precio_final: parseFloat(precioFinal)
            })
        });
        
        const data = await res.json();
        if (!res.ok) throw new Error(data.mensaje);
        
        alert("¡Venta concretada! Los chats y citas pendientes han sido archivados automáticamente.");
        cargarCitas();
    } catch (error) {
        alert("Error de integridad: " + error.message);
    }
}

// ── Cargar tabla de Ventas y Comisiones ────────────────────
async function cargarVentas() {
    const tbody = document.getElementById('tabla-ventas');
    tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;"><i class="fa-solid fa-spinner fa-spin"></i> Procesando datos...</td></tr>`;

    try {
        const res = await fetch(`${API}/agentes/ventas`, {
            headers: { Authorization: `Bearer ${token}` }
        });
        
        if (!res.ok) throw new Error("Error al obtener el historial");
        const ventas = await res.json();

        // Cálculo de métricas
        let comisionesTotales = 0;
        document.getElementById('total-ventas').textContent = ventas.length;

        if (!ventas.length) {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; color: var(--text-gray);">Aún no has cerrado ninguna venta.</td></tr>`;
            document.getElementById('total-comisiones').textContent = '$0.00';
            return;
        }

        tbody.innerHTML = ventas.map(v => {
            const precio = parseFloat(v.opv_precio_final);
            const comision = precio * 0.05; // 5% de comisión estándar
            comisionesTotales += comision;

            return `
                <tr>
                    <td><strong>${v.pro_folio}</strong></td>
                    <td>${v.prd_titulo}</td>
                    <td>${new Date(v.opv_fecha_transaccion).toLocaleDateString()}</td>
                    <td>$${precio.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                    <td style="color: #155724; font-weight: 600;">$${comision.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                </tr>
            `;
        }).join('');

        document.getElementById('total-comisiones').textContent = `$${comisionesTotales.toLocaleString('en-US', {minimumFractionDigits: 2})}`;

    } catch (error) {
        tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; color:#C0392B;">Error de lectura en la base de datos.</td></tr>`;
    }
}