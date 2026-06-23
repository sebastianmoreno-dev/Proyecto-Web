const API = 'http://localhost:8000/api';
// ── Proteger Ruta (Solo Vendedores y Admins) ─────────────────
// Usamos la función de navbar.js en lugar de reescribirla
requireRol('vendedor', 'admin');
const token = Auth.getToken(); // Usamos tu objeto global Auth

// ── Contador habitaciones ────────────────────────────────────
const inputHab = document.getElementById('prop-habitaciones');
document.getElementById('btn-mas').addEventListener('click',   () => { 
    inputHab.value = parseInt(inputHab.value) + 1; 
});
document.getElementById('btn-menos').addEventListener('click', () => { 
    if (parseInt(inputHab.value) > 0) inputHab.value = parseInt(inputHab.value) - 1; 
});

// ── Upload de imágenes (Drag & Drop) ─────────────────────────
const uploadZone = document.getElementById('upload-zone');
const thumbsGrid = document.getElementById('thumbs-grid');

// 1. Clic para abrir el selector de archivos
uploadZone.addEventListener('click', () => {
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = 'image/*';
    fileInput.multiple = true;
    fileInput.onchange = (e) => handleFiles(e.target.files);
    fileInput.click();
});

// 2. Eventos de arrastre
uploadZone.addEventListener('dragover',  (e) => { 
    e.preventDefault(); 
    uploadZone.style.borderColor = 'var(--primary-green)'; 
});
uploadZone.addEventListener('dragleave', ()  => { 
    uploadZone.style.borderColor = ''; 
});
uploadZone.addEventListener('drop',      (e) => { 
    e.preventDefault(); 
    uploadZone.style.borderColor = ''; 
    handleFiles(e.dataTransfer.files); 
});

// Procesar archivos seleccionados o soltados
function handleFiles(files) {
    Array.from(files).forEach(file => {
        // Validación básica: solo procesar si es imagen
        if (!file.type.startsWith('image/')) return;
        
        const reader = new FileReader();
        reader.onload = (e) => {
            const emptyThumb = thumbsGrid.querySelector('.thumbnail.empty');
            
            // Reemplazar la cruz y el icono de borrar
            const imgHTML = `
                <img src="${e.target.result}" alt="Preview" style="width:100%; height:100%; object-fit:cover;">
                <button class="remove-btn" onclick="removeThumb(this)"><i class="fa-solid fa-xmark"></i></button>
            `;

            if (emptyThumb) {
                emptyThumb.classList.replace('empty', 'filled');
                emptyThumb.innerHTML = imgHTML;
            } else {
                // Si no hay huecos vacíos, crear un nuevo contenedor
                const newThumb = document.createElement('div');
                newThumb.className = 'thumbnail filled';
                newThumb.innerHTML = imgHTML;
                thumbsGrid.appendChild(newThumb);
            }
        };
        reader.readAsDataURL(file);
    });
}

// Botón "X" para quitar imagen
// (Es importante que esto sea global 'window.removeThumb' para que funcione el onclick del HTML generado en JS)
window.removeThumb = function(btn) {
    const thumb = btn.closest('.thumbnail');
    thumb.classList.replace('filled', 'empty');
    thumb.innerHTML = '<i class="fa-regular fa-image"></i>';
}

// ── Publicar propiedad (Versión Unificada) ───────────────────
async function publicarPropiedad(redirigirA = 'vendedor.html') {
    const titulo       = document.getElementById('prop-titulo').value.trim();
    const tipo         = document.getElementById('prop-tipo').value;
    const precio       = document.getElementById('prop-precio').value;
    const area_m2      = document.getElementById('prop-area').value;
    const habitaciones = document.getElementById('prop-habitaciones').value;
    const descripcion  = document.getElementById('prop-descripcion').value.trim();
    const ubicacion    = document.getElementById('prop-ubicacion').value.trim();

    // Validación
    if (!titulo || !precio || !ubicacion) {
        alert('Por favor completa al menos el título, precio y ubicación.');
        return;
    }

    try {
        const res = await fetch(`${API}/propiedades`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({ 
                titulo, tipo, precio, area_m2, habitaciones, descripcion, ubicacion 
            })
        });

        const data = await res.json();

        if (!res.ok) {
            alert(data.mensaje || 'Error al publicar la propiedad.');
            return;
        }

        alert('¡Propiedad publicada con éxito!');
        
        // Redirección flexible
        window.location.href = redirigirA;

    } catch (err) {
        alert('No se pudo conectar al servidor. Verifica que Node esté corriendo.');
        console.error(err);
    }
}