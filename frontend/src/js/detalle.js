const API = '/Backend/api';

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
        window.location.href = 'index.html';
        return;
    }

    try {
        // 2. Hacer la petición al backend
        const response = await fetch(`${API}/propiedades/${propiedadId}`);
        
        if (!response.ok) {
            throw new Error('Propiedad no encontrada.');
        }

        const propiedad = await response.json();

        // 3. Rellenar el HTML con los datos de la base de datos
        document.querySelector('.detail-title').textContent = propiedad.titulo;
        document.querySelector('.detail-price').textContent = `$${Number(propiedad.precio).toLocaleString('en-US')}`;
        document.querySelector('.location').innerHTML = `<i class="fa-solid fa-location-dot"></i> ${propiedad.ubicacion}`;
        document.querySelector('.detail-desc').textContent = propiedad.descripcion;

        // Rellenar amenidades
        const amenidades = document.querySelectorAll('.amenity-num');
        if (amenidades.length >= 3) {
            amenidades[0].textContent = propiedad.habitaciones;
            amenidades[1].textContent = propiedad.banos;
            amenidades[2].textContent = propiedad.area_m2;
        }

        // Cambiar la imagen principal si existe
        if (propiedad.imagen_url) {
            document.getElementById('main-img').src = propiedad.imagen_url;
        }

        // Rellenar los datos del Agente Responsable
        document.querySelector('.agent-name').textContent = `${propiedad.vendedor_nombre} ${propiedad.vendedor_apellido}`;

    } catch (error) {
        console.error("Error al cargar detalles:", error);
        document.querySelector('.detail-layout').innerHTML = `<h2 style="padding: 2rem; color: red;">Error: No se pudo cargar la propiedad.</h2>`;
    }
});