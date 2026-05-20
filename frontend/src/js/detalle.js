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