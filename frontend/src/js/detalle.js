// Antes decía const API = ...
const API_URL = '/4CV3/moreseba/Proyecto-Web/Backend/api';

// ── Función para cambiar la imagen principal en la galería ──
function changeImg(el, src) {
    const mainImg = document.getElementById('main-img');
    mainImg.src = src || el.src;
}

// ── Función para alternar el botón de favoritos en detalles ──
function toggleHeartDetail(btn) {
    btn.classList.toggle('saved');
    if (btn.classList.contains('saved')) {
        btn.innerHTML = '<i class="fa-solid fa-heart"></i> Guardado';
        btn.style.backgroundColor = '#FFE8E8';
        btn.style.color = '#E74C3C';
        btn.style.borderColor = '#E74C3C';
    } else {
        btn.innerHTML = '<i class="fa-solid fa-heart"></i> Guardar';
        btn.style.backgroundColor = '';
        btn.style.color = '';
        btn.style.borderColor = '';
    }
}

// ── Lógica Principal para Cargar la Propiedad ──
document.addEventListener('DOMContentLoaded', async () => {
    // 1. Leer el ID de la URL
    const urlParams = new URLSearchParams(window.location.search);
    const propiedadId = urlParams.get('id');

    if (!propiedadId) {
        alert("Propiedad no especificada.");
        window.location.href = 'index.php'; // Regresar al catálogo si no hay ID
        return;
    }

    try {
        // 2. Hacer la petición al backend
        const response = await fetch(`${API_URL}/propiedades/${propiedadId}`);
        
        if (!response.ok) {
            throw new Error('Propiedad no encontrada.');
        }

        const propiedad = await response.json();

        // 3. Rellenar el HTML usando los ID específicos del nuevo diseño
        document.getElementById('bread-titulo').textContent = propiedad.titulo;
        document.getElementById('det-titulo').textContent = propiedad.titulo;
        document.getElementById('det-tipo').textContent = (propiedad.tipo || 'CASA').toUpperCase();
        
        // Formatear el precio con comas
        document.getElementById('det-precio').textContent = `$${Number(propiedad.precio).toLocaleString('en-US')}`;
        
        // Ubicación y Descripción
        document.getElementById('det-ubicacion').textContent = propiedad.ubicacion || 'Ubicación no especificada';
        document.getElementById('det-descripcion').textContent = propiedad.descripcion || 'Sin descripción disponible.';

        // Rellenar amenidades
        document.getElementById('det-habitaciones').textContent = propiedad.habitaciones || '0';
        document.getElementById('det-banos').textContent = propiedad.banos || '0';
        document.getElementById('det-area').textContent = propiedad.area_terreno || '0';

        // 4. Procesar la imagen con la ruta absoluta correcta que hicimos antes
        const mainImg = document.getElementById('main-img');
        const tieneFotoReal = propiedad.imagen_url && propiedad.imagen_url !== 'default.jpg';
        
        const rutaFoto = tieneFotoReal 
            ? `/4CV3/moreseba/Proyecto-Web/frontend/img/${propiedad.imagen_url}` 
            : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1200&q=80';
            
        mainImg.src = rutaFoto;
        // 4.5 Dibujar la galería de miniaturas dinámica
        const thumbsContainer = document.getElementById('gallery-thumbs');
        thumbsContainer.innerHTML = ''; // Borrar las imágenes de Unsplash

        if (propiedad.galeria && propiedad.galeria.length > 0) {
            propiedad.galeria.forEach(foto => {
                // Ignoramos el registro si es un 'default.jpg'
                if (foto.pim_url !== 'default.jpg') {
                    const rutaMiniatura = `/4CV3/moreseba/Proyecto-Web/frontend/img/${foto.pim_url}`;
                    
                    // Inyectamos el HTML para cada miniatura
                    thumbsContainer.innerHTML += `
                        <img src="${rutaMiniatura}" alt="Foto galería" onclick="changeImg(this)">
                    `;
                }
            });
        } else {
            // Si no hay galería en la base de datos, no mostramos nada
            thumbsContainer.innerHTML = '<p style="color: #666; font-size: 0.9em;">Sin fotos adicionales</p>';
        }

        // 5. Rellenar los datos del Agente Responsable (usando las columnas de la BD)
        document.getElementById('det-agente-nombre').textContent = propiedad.vendedor_nombre || 'Agente de EstateArch';
        document.getElementById('det-agente-correo').textContent = propiedad.vendedor_correo || 'Contacto pendiente';

    } catch (error) {
        console.error("Error al cargar detalles:", error);
        document.querySelector('.detail-layout').innerHTML = `
            <div style="width: 100%; text-align: center; padding: 50px;">
                <h2 style="color: #E74C3C;">Error: No se pudo cargar la información de esta propiedad.</h2>
                <a href="index.php" class="btn btn-outline" style="margin-top: 20px;">Volver al inicio</a>
            </div>`;
    }
});