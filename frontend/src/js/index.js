
// ── Cargar Propiedades Dinámicamente ─────────────────────────
async function cargarPropiedades() {
    const grid = document.getElementById('cards-grid');
    const ubicacion = document.getElementById('filtro-ubicacion').value;
    const tipo = document.getElementById('filtro-tipo').value;
    const precio = document.getElementById('filtro-precio').value;

    // Mostrar estado de carga
    grid.innerHTML = `<div style="text-align:center;padding:60px;color:var(--text-gray);grid-column:1/-1;">
        <i class="fa-solid fa-spinner fa-spin" style="font-size:2rem;margin-bottom:15px;display:block;"></i>Cargando...</div>`;

    let url = `${API}/propiedades?`;
    if (ubicacion) url += `ubicacion=${encodeURIComponent(ubicacion)}&`;
    if (tipo)      url += `tipo=${tipo}&`;
    if (precio)    url += `precio_max=${precio}&`;

    try {
        const res  = await fetch(url);
        
        // Si hay error en el backend, lanzar excepción
        if (!res.ok) throw new Error("Error al obtener las propiedades");
        
        const data = await res.json();

        // Si no hay resultados
        if (!data || !data.length) {
            grid.innerHTML = `<div style="text-align:center;padding:60px;color:var(--text-gray);grid-column:1/-1;">
                <i class="fa-solid fa-house-circle-xmark" style="font-size:2rem;margin-bottom:15px;display:block;"></i>
                No se encontraron propiedades con esos filtros.</div>`;
            return;
        }

        // Pintar las tarjetas
        grid.innerHTML = data.map(p => `
            <div class="card-prop">
                <div class="card-image">
                    <span class="badge">${p.tipo.toUpperCase()}</span>
                    <button class="btn-heart" onclick="toggleFavorito(this, ${p.id})">
                        <i class="fa-solid fa-heart"></i>
                    </button>
                    <img src="${p.imagen_url || 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800&q=80'}"
                         alt="${p.titulo}"
                         onerror="this.src='https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800&q=80'">
                </div>
                <div class="card-content">
                    <h3 class="price">$${Number(p.precio).toLocaleString()}</h3>
                    <p class="location"><i class="fa-solid fa-location-dot"></i> ${p.ubicacion}</p>
                    <div class="amenities">
                        <span><i class="fa-solid fa-bed"></i> ${p.habitaciones}</span>
                        <span><i class="fa-solid fa-bath"></i> ${p.banos}</span>
                        <span><i class="fa-solid fa-ruler-combined"></i> ${p.area_m2} m²</span>
                    </div>
                    <a href="detalle.html?id=${p.id}" class="btn btn-outline">Ver Detalles</a>
                </div>
            </div>`).join('');

    } catch (err) {
        console.error("Error al cargar propiedades:", err);
        grid.innerHTML = `<div style="text-align:center;padding:60px;color:#C0392B;grid-column:1/-1;">
            <i class="fa-solid fa-triangle-exclamation" style="font-size:2rem;margin-bottom:15px;display:block;"></i>
            No se pudo conectar al servidor. Verifica que Node.js esté corriendo.</div>`;
    }
}

// ── Lógica de Favoritos ──────────────────────────────────────
function toggleFavorito(btn, propiedadId) {
    if (!Auth.isLoggedIn()) {
        window.location.href = 'auth.html';
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
    fetch(`${API}/favoritos/${propiedadId}`, {
        method,
        headers: { 'Authorization': `Bearer ${Auth.getToken()}` }
    }).catch(err => console.error("Error al guardar favorito:", err));
}

// Ejecutar automáticamente al cargar la página
cargarPropiedades();