const API = '/Backend/api';
// Protección de acceso: solo vendedores
requireRol('vendedor', 'admin');

// Inicialización al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    renderNavbar(); // Dibujar navbar universal
    document.getElementById('sidebar-nombre').textContent = Auth.getNombre();
    document.getElementById('sidebar-rol').textContent = Auth.getRol().toUpperCase();
    cargarMisPropiedades();
});

// ── Cambiar sección ──────────────────────────────────
function mostrarSeccion(nombre, btn) {
    document.querySelectorAll('.panel-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
    
    document.getElementById(`sec-${nombre}`).classList.add('active');
    btn.classList.add('active');
    
    if (nombre === 'mis-propiedades') {
        cargarMisPropiedades();
        if (typeof Chat !== 'undefined') Chat.detener();
    }
    if (nombre === 'mensajes' && typeof Chat !== 'undefined') {
        Chat.init();
    }
}

// ── Cargar mis propiedades ───────────────────────────
async function cargarMisPropiedades() {
    const lista = document.getElementById('lista-propiedades');
    try {
        const res = await fetch(`${API}/propiedades/mis-propiedades`, {
            headers: { Authorization: `Bearer ${Auth.getToken()}` }
        });
        
        const data = await res.json();
        document.getElementById('st-propiedades').textContent = data.length || 0;

        if (!data || !data.length) {
            lista.innerHTML = `<div class="empty-state">No tienes propiedades publicadas.</div>`;
            return;
        }

        lista.innerHTML = data.map(p => `
            <div class="prop-row">
                <img src="${p.imagen_url || 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=200&q=80'}" alt="${p.titulo}">
                <div class="prop-row-info">
                    <h4>${p.titulo}</h4>
                    <p>${p.ubicacion}</p>
                </div>
                <div class="prop-row-precio">$${Number(p.precio).toLocaleString()}</div>
                <span class="badge-estado-prop badge-${p.estado.toLowerCase()}">${p.estado.toUpperCase()}</span>
            </div>`).join('');
            
    } catch(e) { 
        console.error(e);
    }
}