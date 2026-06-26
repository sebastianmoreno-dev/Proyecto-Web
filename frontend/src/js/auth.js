// js/auth.js
const API = '/4CV3/moreseba/Proyecto-Web/Backend/api';

// ── Si ya hay sesión, redirigir automáticamente ──────────────
if (localStorage.getItem('token')) {
    const rolUsuario = localStorage.getItem('rol') || localStorage.getItem('role');
    
    if (rolUsuario === 'admin') {
        window.location.href = 'admin.php';
    } else if (rolUsuario === 'vendedor') {
        window.location.href = 'vendedor.php';
    } else if (rolUsuario === 'agente') {
        window.location.href = 'agente.php'; // <-- AGREGADO
    } else {
        window.location.href = 'index.php';
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
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    
    if (btnElement) {
        btnElement.classList.add('active');
    }

    document.getElementById('auth-msg').style.display = 'none';
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

        localStorage.setItem('token',   data.token);
        localStorage.setItem('rol',     data.usuario.rol);
        localStorage.setItem('nombre',  data.usuario.nombre);
        localStorage.setItem('usuario', JSON.stringify(data.usuario));

        mostrarMsg(`¡Bienvenido, ${data.usuario.nombre}! Redirigiendo...`, 'exito');
        setTimeout(() => {
            const rol = data.usuario.rol;
            if (rol === 'admin') {
                window.location.href = 'admin.php';
            } else if (rol === 'vendedor') {
                window.location.href = 'vendedor.php';
            } else if (rol === 'agente') {
                window.location.href = 'agente.php'; // <-- AGREGADO
            } else {
                window.location.href = 'index.php';
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
    const apat       = document.getElementById('reg-apat').value.trim();
    const amat       = document.getElementById('reg-amat').value.trim();
    const curp       = document.getElementById('reg-curp').value.trim().toUpperCase();
    const fechaNac   = document.getElementById('reg-fecha-nac').value;
    const correo     = document.getElementById('reg-correo').value.trim();
    const rol        = document.getElementById('reg-rol').value;
    const contrasena = document.getElementById('reg-password').value;
    const btn        = document.getElementById('btn-registro');

    if (!nombre || !apat || !amat || !curp || !fechaNac || !correo || !contrasena) {
        mostrarMsg('Por favor completa todos los campos.', 'error');
        return;
    }

    if (curp.length !== 18) {
        mostrarMsg('La CURP debe tener exactamente 18 caracteres.', 'error');
        return;
    }

    btn.textContent = 'Creando cuenta...';
    btn.disabled = true;

    try {
        const res  = await fetch(`${API}/auth/registro`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nombre, apat, amat, curp, fechaNac, correo, contrasena, rol })
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