const API = '/4CV3/moreseba/Proyecto-Web/Backend/api';
const token = Auth.getToken();

document.addEventListener('DOMContentLoaded', () => {
    // Si no hay sesión iniciada, regresamos al login
    if (!token) {
        window.location.href = 'index.php';
        return;
    }
    cargarFavoritos();
});

async function cargarFavoritos() {
    const contenedor = document.getElementById('contenedor-favoritos');
    
    try {
        const res = await fetch(`${API}/favoritos`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        
        const favoritos = await res.json();
        contenedor.innerHTML = ''; 

        // Diseño para cuando no hay propiedades guardadas
        if (favoritos.length === 0) {
            contenedor.innerHTML = `
                <div class="empty-state card">
                    <i class="fa-regular fa-heart" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                    <h3>Aún no tienes favoritos</h3>
                    <p style="color: #666; margin-bottom: 1.5rem;">Explora el catálogo y guarda las propiedades que más te inspiren.</p>
                    <a href="index.php" class="btn btn-outline">Explorar Catálogo</a>
                </div>
            `;
            return;
        }

        // Generar las tarjetas para cada propiedad
        favoritos.forEach(prop => {
            const card = document.createElement('div');
            card.className = 'card property-card';
            
            // Asumimos que la base de datos devuelve una imagen principal
            const imagenUrl = prop.imagen ? `../../img/${prop.imagen}` : 'https://via.placeholder.com/400x250?text=Sin+Imagen';
            
            card.innerHTML = `
                <div class="card-img-container">
                    <img src="${imagenUrl}" alt="${prop.titulo}" class="card-img">
                    <button onclick="quitarFavorito(${prop.id})" class="btn-remove-fav" title="Quitar de favoritos">
                        <i class="fa-solid fa-heart-crack"></i>
                    </button>
                </div>
                <div class="card-body">
                    <span class="badge-tipo">${prop.tipo ? prop.tipo.toUpperCase() : 'PROPIEDAD'}</span>
                    <h3 class="card-title">${prop.titulo}</h3>
                    <p class="card-location"><i class="fa-solid fa-location-dot"></i> ${prop.ubicacion}</p>
                    
                    <div class="card-footer-flex">
                        <span class="card-price">$${Number(prop.precio).toLocaleString()} USD</span>
                        <a href="detalle.php?id=${prop.id}" class="btn-outline">Ver Detalles</a>
                    </div>
                </div>
            `;
            contenedor.appendChild(card);
        });

    } catch (error) {
        console.error("Error:", error);
        contenedor.innerHTML = '<p style="color: red; grid-column: 1/-1;">No se pudieron cargar los favoritos. Revisa la conexión al servidor.</p>';
    }
}

async function quitarFavorito(idPropiedad) {
    if (!confirm('¿Seguro que deseas eliminar esta propiedad de tus favoritos?')) return;
    
    try {
        const res = await fetch(`${API}/favoritos/${idPropiedad}`, {
            method: 'DELETE',
            headers: { 'Authorization': `Bearer ${token}` }
        });

        if (res.ok) {
            cargarFavoritos(); // Volvemos a dibujar la vista para que la propiedad desaparezca visualmente
        } else {
            const data = await res.json();
            alert(data.mensaje || 'Error al quitar de favoritos.');
        }
    } catch (error) {
        alert('Error de conexión con el servidor.');
    }
}