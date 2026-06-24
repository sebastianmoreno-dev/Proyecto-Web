// ── Función para simular el envío del formulario de contacto ──

const API = '/Backend/api';
function sendMessage() {
    const inputs = document.querySelectorAll('.form-control');
    let valid = true;
    
    // Validación básica: que no estén vacíos (excepto el teléfono)
    inputs.forEach(input => {
        if (!input.value.trim() && input.type !== 'tel') {
            valid = false;
        }
    });
    
    if (!valid) {
        alert('Por favor completa todos los campos requeridos.');
        return;
    }
    
    // Mostrar mensaje de éxito
    document.getElementById('success-msg').style.display = 'block';
    
    // Limpiar los campos
    inputs.forEach(input => input.value = '');
    
    // Ocultar el mensaje después de 5 segundos
    setTimeout(() => {
        document.getElementById('success-msg').style.display = 'none';
    }, 5000);
}