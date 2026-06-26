const API_URL = '/4CV3/moreseba/Proyecto-Web/Backend/api';
//const API_URL = '/Backend/api';

function changeImg(el, src) {
    const mainImg = document.getElementById('main-img');
    mainImg.src = src || el.src;
}

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

// ── Lógica Principal ──
document.addEventListener('DOMContentLoaded', async () => {
    const urlParams = new URLSearchParams(window.location.search);
    const propiedadId = urlParams.get('id');

    if (!propiedadId) {
        alert("Propiedad no especificada.");
        window.location.href = 'index.php';
        return;
    }

    // Mostrar el formulario de reseña solo si es comprador
    if (Auth.isLoggedIn() && Auth.getRol() === 'comprador') {
        document.getElementById('form-resena-container').style.display = 'block';
    }

    try {
        // 1. Cargar Propiedad
        const response = await fetch(`${API_URL}/propiedades/${propiedadId}`);
        if (!response.ok) throw new Error('Propiedad no encontrada.');
        const propiedad = await response.json();

        document.getElementById('bread-titulo').textContent = propiedad.titulo;
        document.getElementById('det-titulo').textContent = propiedad.titulo;
        document.getElementById('det-tipo').textContent = (propiedad.tipo || 'CASA').toUpperCase();
        document.getElementById('det-precio').textContent = `$${Number(propiedad.precio).toLocaleString('en-US')}`;
        document.getElementById('det-ubicacion').textContent = propiedad.ubicacion || 'Ubicación no especificada';
        document.getElementById('det-descripcion').textContent = propiedad.descripcion || 'Sin descripción disponible.';
        document.getElementById('det-habitaciones').textContent = propiedad.habitaciones || '0';
        document.getElementById('det-banos').textContent = propiedad.banos || '0';
        document.getElementById('det-area').textContent = propiedad.area_terreno || '0';

        const mainImg = document.getElementById('main-img');
        const tieneFotoReal = propiedad.imagen_url && propiedad.imagen_url !== 'default.jpg';
        mainImg.src = tieneFotoReal ? `/frontend/img/${propiedad.imagen_url}` : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1200&q=80';
            
        const thumbsContainer = document.getElementById('gallery-thumbs');
        thumbsContainer.innerHTML = ''; 

        if (propiedad.galeria && propiedad.galeria.length > 0) {
            propiedad.galeria.forEach(foto => {
                if (foto.pim_url !== 'default.jpg') {
                    thumbsContainer.innerHTML += `<img src="/frontend/img/${foto.pim_url}" alt="Foto galería" onclick="changeImg(this)">`;
                }
            });
        } else {
            thumbsContainer.innerHTML = '<p style="color: #666; font-size: 0.9em;">Sin fotos adicionales</p>';
        }

        document.getElementById('det-agente-nombre').textContent = propiedad.vendedor_nombre || 'Agente de EstateArch';
        document.getElementById('det-agente-correo').textContent = propiedad.vendedor_correo || 'Contacto pendiente';

        // 2. Cargar las reseñas de esta propiedad
        cargarResenas(propiedadId);

    } catch (error) {
        console.error("Error:", error);
        document.querySelector('.detail-layout').innerHTML = `
            <div style="width: 100%; text-align: center; padding: 50px;">
                <h2 style="color: #E74C3C;">Error al cargar la propiedad.</h2>
                <a href="index.php" class="btn btn-outline" style="margin-top: 20px;">Volver al inicio</a>
            </div>`;
    }
});

// ── Funciones de Reseñas ──
async function cargarResenas(id) {
    const contenedor = document.getElementById('lista-resenas');
    try {
        const res = await fetch(`${API_URL}/propiedades/${id}/resenas`);
        const data = await res.json();
        
        if (!data || !data.length) {
            contenedor.innerHTML = '<p style="color: var(--text-gray); text-align: center; padding: 20px;">Aún no hay reseñas para esta propiedad. ¡Sé el primero en opinar!</p>';
            return;
        }
        
        contenedor.innerHTML = data.map(r => {
            const estrellasLlenas = '★'.repeat(r.res_calificacion);
            const estrellasVacias = '☆'.repeat(5 - r.res_calificacion);
            const fecha = new Date(r.res_fecha).toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' });

            return `
                <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #EAEAEA;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <strong style="color: var(--text-dark); font-size: 1.05rem;">${r.cliente_nombre}</strong>
                        <span style="color: #F1C40F; font-size: 1.1rem; letter-spacing: 2px;">${estrellasLlenas}<span style="color: #E0E0E0;">${estrellasVacias}</span></span>
                    </div>
                    <p style="font-size: 0.95rem; color: var(--text-gray); line-height: 1.6; margin-bottom: 8px;">${r.res_comentario}</p>
                    <small style="color: #A0A0A0; font-size: 0.8rem;">Publicado el ${fecha}</small>
                </div>
            `;
        }).join('');
    } catch (error) {
        contenedor.innerHTML = '<p style="color: #E74C3C; text-align: center;">Error al cargar las reseñas.</p>';
    }
}

async function enviarResena(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-enviar-resena');
    const idPropiedad = new URLSearchParams(window.location.search).get('id');
    const calificacion = document.getElementById('res-calificacion').value;
    const comentario = document.getElementById('res-comentario').value.trim();

    btn.disabled = true;
    btn.textContent = "Publicando...";

    try {
        const res = await fetch(`${API_URL}/propiedades/${idPropiedad}/resenas`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${Auth.getToken()}`
            },
            body: JSON.stringify({ calificacion, comentario })
        });
        
        const data = await res.json();
        
        if (res.ok) {
            document.getElementById('res-comentario').value = '';
            cargarResenas(idPropiedad); 
            
            btn.textContent = "Reseña Publicada ✔";
            btn.style.backgroundColor = "#27ae60";
            setTimeout(() => {
                btn.disabled = false;
                btn.textContent = "Publicar Reseña";
                btn.style.backgroundColor = "var(--primary-green)";
            }, 3000);
        } else {
            alert(data.mensaje || 'Error al guardar la reseña.');
            btn.disabled = false;
            btn.textContent = "Publicar Reseña";
        }
    } catch (error) {
        alert('Error de conexión al intentar publicar la reseña.');
        btn.disabled = false;
        btn.textContent = "Publicar Reseña";
    }
}