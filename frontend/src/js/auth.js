// js/auth.js

const API = 'http://localhost:3000/api';

// ── Si ya hay sesión, redirigir automáticamente ──────────────
if (localStorage.getItem('token')) {
    const rolUsuario = localStorage.getItem('rol') || localStorage.getItem('role');
    
    // Redirección inteligente al cargar la página si ya existe token
    if (rolUsuario === 'admin') {
        window.location.href = 'admin.html';
    } else if (rolUsuario === 'vendedor') {
        window.location.href = 'vendedor.html';
    } else {
        window.location.href = 'index.html';
    }
}

// ── Mostrar mensaje ──────────────────────────────────────────
function mostrarMsg(texto, tipo) {
    const msg = document.getElementById('auth-msg');
    msg.textContent = texto;
    msg.style.display = 'block';
    msg.style.backgroundColor = tipo === 'error' ? '#FFE8E8' : '#F0FBF4';
    msg.style.color           = tipo === 'error' ? '#C0392B' : '#1B4332';
    msg.style.border          = tipo === 'error' ? '1px solid #F5C6CB' : '1px solid #A8D5B5';
}

// ── Cambiar tab (Login / Registro) ───────────────────────────
function switchTab(tabName, btnElement) {
    // Quitar clase active de todos los botones
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    
    // Agregar clase active al botón seleccionado
    if (btnElement) {
        btnElement.classList.add('active');
    }

    // Ocultar mensaje de error anterior si existía
    document.getElementById('auth-msg').style.display = 'none';
    
    // Mostrar u ocultar formularios
    document.getElementById('login-form').style.display    = tabName === 'login'    ? 'block' : 'none';
    document.getElementById('register-form').style.display = tabName === 'register' ? 'block' : 'none';
}

// ── Iniciar Sesión ───────────────────────────────────────────
async function iniciarSesion() {
    const correo     = document.getElementById('login-correo').value.trim();
    const contrasena = document.getElementById('login-password').value;
    const btn        = document.getElementById('btn-login');

    if (!correo || !contrasena) {
        mostrarMsg('Por favor completa todos los campos.', 'error');
        return;
    }

    btn.textContent = 'Cargando...';
    btn.disabled = true;

    try {
        const res  = await fetch(`${API}/auth/login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ correo, contrasena })
        });
        const data = await res.json();

        if (!res.ok) {
            mostrarMsg(data.mensaje || 'Error al iniciar sesión.', 'error');
            return;
        }

        // Guardar token y datos del usuario
        localStorage.setItem('token',   data.token);
        localStorage.setItem('rol',     data.usuario.rol);
        localStorage.setItem('nombre',  data.usuario.nombre);
        localStorage.setItem('usuario', JSON.stringify(data.usuario));

        mostrarMsg(`¡Bienvenido, ${data.usuario.nombre}! Redirigiendo...`, 'exito');

        // ── REDIRECCIÓN INTELIGENTE ──────────────────────────
        setTimeout(() => {
            const rol = data.usuario.rol;
            if (rol === 'admin') {
                window.location.href = 'admin.html';
            } else if (rol === 'vendedor') {
                window.location.href = 'vendedor.html';
            } else {
                window.location.href = 'index.html';
            }
        }, 1000);

    } catch (err) {
        mostrarMsg('No se pudo conectar al servidor. ¿Está corriendo el backend?', 'error');
        console.error(err);
    } finally {
        btn.textContent = 'Iniciar Sesión';
        btn.disabled = false;
    }
}

// ── Registrarse ──────────────────────────────────────────────
async function registrarse() {
    const nombre     = document.getElementById('reg-nombre').value.trim();
    const apellido   = document.getElementById('reg-apellido').value.trim();
    const correo     = document.getElementById('reg-correo').value.trim();
    const rol        = document.getElementById('reg-rol').value;
    const contrasena = document.getElementById('reg-password').value;
    const btn        = document.getElementById('btn-registro');

    if (!nombre || !apellido || !correo || !contrasena) {
        mostrarMsg('Por favor completa todos los campos.', 'error');
        return;
    }

    btn.textContent = 'Creando cuenta...';
    btn.disabled = true;

    try {
        const res  = await fetch(`${API}/auth/registro`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nombre, apellido, correo, contrasena, rol })
        });
        const data = await res.json();

        if (!res.ok) {
            mostrarMsg(data.mensaje || 'Error al registrarse.', 'error');
            return;
        }

        mostrarMsg('¡Cuenta creada con éxito! Ahora inicia sesión.', 'exito');
        setTimeout(() => switchTab('login'), 1500);

    } catch (err) {
        mostrarMsg('No se pudo conectar al servidor.', 'error');
        console.error(err);
    } finally {
        btn.textContent = 'Crear Cuenta';
        btn.disabled = false;
    }
}