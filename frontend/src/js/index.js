const API_URL = '/4CV3/moreseba/Proyecto-Web/Backend/api';

// ── Función puente para el botón de búsqueda ─────────────────
function aplicarFiltros() {
    // Al presionar "Buscar", simplemente volvemos a ejecutar la carga
    // La función leerá los filtros seleccionados automáticamente.
    cargarPropiedades();
}

// ── Cargar Propiedades Dinámicamente ─────────────────────────
async function cargarPropiedades() {
    const grid = document.getElementById('cards-grid');
    const ubicacion = document.getElementById('filtro-ubicacion').value || '';
    const tipo = document.getElementById('filtro-tipo').value || '';
    const precio = document.getElementById('filtro-precio').value || '';

    // Mostrar estado de carga
    grid.innerHTML = `<div style="text-align:center;padding:60px;color:var(--text-gray);grid-column:1/-1;">
        <i class="fa-solid fa-spinner fa-spin" style="font-size:2rem;margin-bottom:15px;display:block;"></i>Cargando...</div>`;

    // Construir la URL con parámetros de búsqueda
    let url = `${API_URL}/propiedades?`;
    if (ubicacion) url += `ubicacion=${encodeURIComponent(ubicacion)}&`;
    if (tipo)      url += `tipo=${encodeURIComponent(tipo)}&`;
    if (precio)    url += `precio_max=${encodeURIComponent(precio)}&`;

    // Limpiamos el '&' o '?' del final por estética de la URL
    url = url.endsWith('&') ? url.slice(0, -1) : url;
    url = url.endsWith('?') ? url.slice(0, -1) : url;

    try {
        const res  = await fetch(url);
        const data = await res.json(); // Leemos la memoria JSON antes de juzgar el HTTP status

        // Si PHP mandó un error crítico real y el JSON no tiene propiedades, abortamos
        if (!res.ok && data.mensaje) {
            throw new Error(`Servidor: ${res.status} - ${data.mensaje}`);
        }

        // Normalización: Aseguramos que la data siempre sea un arreglo para que .map() no colapse
        const arrayData = Array.isArray(data) ? data : [data];

        // Si no hay resultados con esos filtros
        if (!data || !data.length) {
            grid.innerHTML = `<div style="text-align:center;padding:60px;color:var(--text-gray);grid-column:1/-1;">
                <i class="fa-solid fa-house-circle-xmark" style="font-size:2rem;margin-bottom:15px;display:block;"></i>
                No se encontraron propiedades con estos filtros. Intenta con otra búsqueda.</div>`;
            return;
        }

        // Pintar las tarjetas
                    <button class="btn-heart" onclick="toggleFavorito(this, ${p.id})">
                        <i class="fa-solid fa-heart"></i>
                    </button>
                    <img src="${rutaFoto}"
                         alt="${tituloLimpio}"
                         onerror="this.src='https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800&q=80'">
                </div>
                <div class="card-content">
                    <h3 class="price">$${Number(p.precio || 0).toLocaleString()}</h3>
                    <p class="location"><i class="fa-solid fa-location-dot"></i> ${ubicacionLimpia}</p>
                    <div class="amenities">
                        <span><i class="fa-solid fa-bed"></i> ${p.habitaciones || 0}</span>
                        <span><i class="fa-solid fa-bath"></i> ${p.banos || 0}</span>
                        <span><i class="fa-solid fa-ruler-combined"></i> ${p.area_m2 || 0} m²</span>
                    </div>
                    <a href="detalle.php?id=${p.id}" class="btn btn-outline">Ver Detalles</a>
                </div>
            </div>`;
        }).join('');

    } catch (err) {
        console.error("Error al cargar propiedades:", err);
        grid.innerHTML = `<div style="text-align:center;padding:60px;color:#C0392B;grid-column:1/-1;">
            <i class="fa-solid fa-triangle-exclamation" style="font-size:2rem;margin-bottom:15px;display:block;"></i>
            Ocurrió un problema: ${err.message}</div>`;
    }
}

// ── Lógica de Favoritos ──────────────────────────────────────
function toggleFavorito(btn, propiedadId) {
    if (!Auth.isLoggedIn()) {
        window.location.href = 'auth.php';
        return;
    }
    
    if (Auth.getRol() !== 'comprador') {
        alert('Solo los compradores pueden guardar propiedades en favoritos.');
        return;
    }
    
    btn.classList.toggle('active');
    const activo = btn.classList.contains('active');
    
    // Cambios visuales
    btn.style.color           = activo ? '#E74C3C' : '';
    btn.style.backgroundColor = activo ? '#FFE8E8' : '';
    
    // Petición al backend
    const method = activo ? 'POST' : 'DELETE';
    fetch(`${API_URL}/favoritos/${propiedadId}`, {
        method,
        headers: { 'Authorization': `Bearer ${Auth.getToken()}` }
    }).catch(err => console.error("Error al guardar favorito:", err));
}

// Ejecutar automáticamente al cargar la página
cargarPropiedades();