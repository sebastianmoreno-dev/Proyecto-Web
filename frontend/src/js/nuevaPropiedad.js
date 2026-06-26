var API = '/4CV3/moreseba/Proyecto-Web/Backend/api';

const token = localStorage.getItem('token') || sessionStorage.getItem('token');

// ── Contadores automáticos (Habitaciones y Baños) ─────────────────────────
// Esto busca automáticamente todos los contenedores y les da vida a sus botones
document.querySelectorAll('.counter-input').forEach(container => {
    const btnMenos = container.querySelector('.btn-menos');
    const btnMas = container.querySelector('.btn-mas');
    const input = container.querySelector('input'); // Encuentra prop-habitaciones o prop-banos

    if (btnMas && btnMenos && input) {
        btnMas.addEventListener('click', () => { 
            input.value = parseInt(input.value) + 1; 
        });
        btnMenos.addEventListener('click', () => { 
            if (parseInt(input.value) > 0) input.value = parseInt(input.value) - 1; 
        });
    }
});


// ── Upload de imágenes (Drag & Drop) ─────────────────────────
const uploadZone = document.getElementById('upload-zone');
const thumbsGrid = document.getElementById('thumbs-grid');
let archivosParaSubir = [];

uploadZone.addEventListener('click', () => {
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = 'image/*';
    fileInput.multiple = true;
    fileInput.onchange = (e) => handleFiles(e.target.files);
    fileInput.click();
});

uploadZone.addEventListener('dragover',  (e) => { e.preventDefault(); uploadZone.style.borderColor = 'var(--primary-green)'; });
uploadZone.addEventListener('dragleave', ()  => { uploadZone.style.borderColor = ''; });
uploadZone.addEventListener('drop',      (e) => { e.preventDefault(); uploadZone.style.borderColor = ''; handleFiles(e.dataTransfer.files); });

function handleFiles(files) {
    Array.from(files).forEach(file => {
        if (!file.type.startsWith('image/')) return;
        archivosParaSubir.push(file);

        const reader = new FileReader();
        reader.onload = (e) => {
            const emptyThumb = thumbsGrid.querySelector('.thumbnail.empty');
            const imgHTML = `
                <img src="${e.target.result}" alt="Preview" style="width:100%; height:100%; object-fit:cover;">
                <button class="remove-btn" onclick="removeThumb(this, '${file.name}')"><i class="fa-solid fa-xmark"></i></button>
            `;
            if (emptyThumb) {
                emptyThumb.classList.replace('empty', 'filled');
                emptyThumb.innerHTML = imgHTML;
            } else {
                const newThumb = document.createElement('div');
                newThumb.className = 'thumbnail filled';
                newThumb.innerHTML = imgHTML;
                thumbsGrid.appendChild(newThumb);
            }
        };
        reader.readAsDataURL(file);
    });
}

window.removeThumb = function(btn, nombreArchivo) {
    archivosParaSubir = archivosParaSubir.filter(f => f.name !== nombreArchivo);
    const thumb = btn.closest('.thumbnail');
    thumb.classList.replace('filled', 'empty');
    thumb.innerHTML = '<i class="fa-regular fa-image"></i>';
}

// ── Publicar propiedad ───────────────────────────────────────
async function publicarPropiedad(redirigirA = 'vendedor.php') {
    const titulo       = document.getElementById('prop-titulo').value.trim();
    const tipo         = document.getElementById('prop-tipo').value;
    const precio       = document.getElementById('prop-precio').value;
    const area_m2      = document.getElementById('prop-area').value;
    const habitaciones = document.getElementById('prop-habitaciones').value;
    const banos        = document.getElementById('prop-banos').value; // Capturamos los baños
    const descripcion  = document.getElementById('prop-descripcion').value.trim();
    const ubicacion    = document.getElementById('prop-ubicacion').value; // Ahora viene del select

    if (!titulo || !precio || !ubicacion) {
        alert('Por favor completa el título, precio y selecciona una alcaldía.');
        return;
    }

    if (archivosParaSubir.length === 0) {
        alert('Debes subir al menos una imagen de la propiedad.');
        return;
    }

    const formData = new FormData();
    formData.append('titulo', titulo);
    formData.append('tipo', tipo);
    formData.append('precio', precio);
    formData.append('area_m2', area_m2);
    formData.append('habitaciones', habitaciones);
    formData.append('banos', banos); // Enviamos los baños
    formData.append('descripcion', descripcion);
    formData.append('ubicacion', ubicacion);

    archivosParaSubir.forEach((archivo) => {
        formData.append('imagenes[]', archivo);
    });

    try {
        const res = await fetch(`${API}/propiedades`, {
            method: 'POST',
            headers: { 'Authorization': `Bearer ${token}` },
            body: formData
        });

        const data = await res.json();
        if (!res.ok) {
            alert(data.mensaje || 'Error al publicar.');
            return;
        }

        alert('¡Propiedad publicada con éxito!');
        window.location.href = redirigirA;
    } catch (err) {
        alert('Error de conexión con el servidor.');
        console.error(err);
    }
}