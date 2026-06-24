const API = '/Backend/api';
const adminToken = localStorage.getItem('token');
const adminRol = localStorage.getItem('role') || localStorage.getItem('rol'); // Respaldo por si usas 'role' o 'rol'

// ── Verificar que sea admin al cargar la página ─────────────
if (!adminToken || adminRol !== 'admin') {
    alert('Acceso denegado. Esta sección es exclusiva para administradores.');
    window.location.href = 'auth.html';
} else {
    // Si pasa la validación, pintamos su nombre y cargamos las stats
    const nombreUsuario = localStorage.getItem('nombre') || 'Usuario';
    document.getElementById('admin-nombre').textContent = `Administrador: ${nombreUsuario}`;
    cargarStats();
}

// ── Mostrar panel (Lógica de pestañas) ───────────────────────
function mostrarPanel(nombre, btnElement) {
    // Ocultar todos los paneles y quitar clase active de los botones
    document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
    
    // Mostrar el panel solicitado
    document.getElementById(`panel-${nombre}`).classList.add('active');
    
    // Asignar clase active al botón que se presionó (si existe)
    if (btnElement) {
        btnElement.classList.add('active');
    }

    // Cargar los datos correspondientes
    if (nombre === 'usuarios')     cargarUsuarios();
    if (nombre === 'propiedades')  cargarPropiedades();
}

// ── Cargar estadísticas (Dashboard) ──────────────────────────
async function cargarStats() {
    try {
        const res  = await fetch(`${API_ADMIN}/admin/stats`, { 
            headers: { Authorization: `Bearer ${adminToken}` } 
        });
        if (!res.ok) throw new Error("Error al obtener estadísticas");
        
        const data = await res.json();
        document.getElementById('s-usuarios').textContent    = data.total_usuarios || 0;
        document.getElementById('s-propiedades').textContent = data.total_propiedades || 0;
        document.getElementById('s-activas').textContent     = data.total_activas || 0;
        document.getElementById('s-vendidas').textContent    = data.total_vendidas || 0;
    } catch (error) {
        console.error("Error Stats:", error);
    }
}

// ── Cargar tabla de usuarios ─────────────────────────────────
async function cargarUsuarios() {
    const tbody = document.getElementById('tabla-usuarios');
    try {
        const res  = await fetch(`${API_ADMIN}/admin/usuarios`, { 
            headers: { Authorization: `Bearer ${adminToken}` } 
        });
        if (!res.ok) throw new Error("Error al cargar usuarios");
        
        const data = await res.json();
        tbody.innerHTML = data.map(u => `
            <tr>
                <td>${u.id}</td>
                <td>${u.nombre} ${u.apellido || ''}</td>
                <td>${u.correo}</td>
                <td><span class="badge-rol ${u.rol}">${u.rol}</span></td>
                <td>
                    <select class="inline" onchange="cambiarRol(${u.id}, this.value)">
                        <option ${u.rol === 'comprador' ? 'selected' : ''} value="comprador">Comprador</option>
                        <option ${u.rol === 'vendedor' ? 'selected' : ''}  value="vendedor">Vendedor</option>
                        <option ${u.rol === 'admin' ? 'selected' : ''}     value="admin">Admin</option>
                    </select>
                </td>
                <td>
                    <button class="btn-sm btn-danger" onclick="eliminarUsuario(${u.id})">
                        <i class="fa-solid fa-trash"></i> Eliminar
                    </button>
                </td>
            </tr>
        `).join('');
    } catch (error) {
        console.error(error);
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; color:red;">No se pudieron cargar los usuarios.</td></tr>`;
    }
}

// ── Cambiar rol de usuario ───────────────────────────────────
async function cambiarRol(id, nuevoRol) {
    try {
        const res = await fetch(`${API_ADMIN}/admin/usuarios/${id}/rol`, {
            method: 'PUT',
            headers: { 
                'Content-Type': 'application/json', 
                Authorization: `Bearer ${adminToken}` 
            },
            body: JSON.stringify({ rol: nuevoRol })
        });
        
        if (!res.ok) throw new Error("Error al actualizar el rol");
        cargarUsuarios(); // Refrescar la tabla
    } catch (error) {
        alert("Ocurrió un error al cambiar el rol.");
        console.error(error);
    }
}

// ── Eliminar usuario ─────────────────────────────────────────
async function eliminarUsuario(id) {
    if (!confirm('¿Seguro que deseas eliminar este usuario de forma permanente?')) return;
    
    try {
        const res = await fetch(`${API_ADMIN}/admin/usuarios/${id}`, {
            method: 'DELETE',
            headers: { Authorization: `Bearer ${adminToken}` }
        });
        
        if (!res.ok) throw new Error("Error al eliminar usuario");
        cargarUsuarios(); 
        cargarStats(); // Actualizar números del dashboard
    } catch (error) {
        alert("Ocurrió un error al intentar eliminar el usuario.");
        console.error(error);
    }
}

// ── Cargar tabla de propiedades ──────────────────────────────
async function cargarPropiedades() {
    const tbody = document.getElementById('tabla-propiedades');
    try {
        const res  = await fetch(`${API_ADMIN}/admin/propiedades`, { 
            headers: { Authorization: `Bearer ${adminToken}` } 
        });
        if (!res.ok) throw new Error("Error al cargar propiedades");
        
        const data = await res.json();
        tbody.innerHTML = data.map(p => `
            <tr>
                <td>${p.id}</td>
                <td>${p.titulo}</td>
                <td>$${Number(p.precio).toLocaleString()}</td>
                <td>${p.vendedor_nombre}</td>
                <td><span class="badge-estado ${p.estado}">${p.estado}</span></td>
                <td>
                    <select class="inline" onchange="cambiarEstado(${p.id}, this.value)">
                        <option ${p.estado === 'activa' ? 'selected' : ''}   value="activa">Activa</option>
                        <option ${p.estado === 'inactiva' ? 'selected' : ''} value="inactiva">Inactiva</option>
                        <option ${p.estado === 'vendida' ? 'selected' : ''}  value="vendida">Vendida</option>
                    </select>
                </td>
            </tr>
        `).join('');
    } catch (error) {
        console.error(error);
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; color:red;">No se pudieron cargar las propiedades.</td></tr>`;
    }
}

// ── Cambiar estado de propiedad ──────────────────────────────
async function cambiarEstado(id, estado) {
    try {
        const res = await fetch(`${API_ADMIN}/admin/propiedades/${id}/estado`, {
            method: 'PUT',
            headers: { 
                'Content-Type': 'application/json', 
                Authorization: `Bearer ${adminToken}` 
            },
            body: JSON.stringify({ estado })
        });
        
        if (!res.ok) throw new Error("Error al actualizar estado");
        cargarStats(); // Actualizar dashboard visualmente
        cargarPropiedades(); // Refrescar para ver el cambio de color en el badge
    } catch (error) {
        alert("Ocurrió un error al cambiar el estado de la propiedad.");
        console.error(error);
    }
}

// ── Cerrar sesión ────────────────────────────────────────────
function cerrarSesionAdmin() {
    localStorage.clear();
    window.location.href = 'auth.html';
}