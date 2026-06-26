var API = '/4CV3/moreseba/Proyecto-Web/Backend/api';
//var API = '/Backend/api';
const Auth = {
    getToken:   () => localStorage.getItem('token'),
    getRol:     () => localStorage.getItem('rol'),
    getNombre:  () => localStorage.getItem('nombre'),
    getUsuario: () => JSON.parse(localStorage.getItem('usuario') || 'null'),
    isLoggedIn: () => !!localStorage.getItem('token'),
    logout: () => { localStorage.clear(); window.location.href = 'index.php'; }
};

// Renderiza el navbar COMPLETO universalmente
function renderNavbar(paginaActiva = '') {
    const navbar = document.querySelector('.navbar');
    if (!navbar) return;

    const loggedIn = Auth.isLoggedIn();
    const rol      = Auth.getRol();
    const nombre   = Auth.getNombre();

    // 1. Construir el Logo
    const logoHTML = `
        <a href="index.php" style="text-decoration:none;">
            <div class="logo">Estate<span>Arch</span></div>
        </a>
    `;

    // 2. Construir los Enlaces del Centro
    const centerHTML = `
        <nav class="nav-center">
            <a href="index.php" class="${paginaActiva === 'propiedades' ? 'active' : ''}">Propiedades</a>
            <a href="nosotros.php" class="${paginaActiva === 'nosotros' ? 'active' : ''}">Nosotros</a>
            <a href="contacto.php" class="${paginaActiva === 'contacto' ? 'active' : ''}">Contacto</a>
        </nav>
    `;

    // 3. Construir los Botones de la Derecha según el usuario
    let userHTML = '';

    if (!loggedIn) {
        userHTML = `
            <div class="nav-actions">
                <a href="auth.php" class="sell-link">Vender Propiedad</a>
                <a href="auth.php" class="btn btn-primary" style="padding:10px 20px; border-radius:6px;">Iniciar Sesión</a>
            </div>
        `;
    } else {
        // Rutas separadas para vendedor y administrador
        let menuExtra = '';
        if (rol === 'comprador') {
            menuExtra = `<a href="favoritos.php" class="sell-link"><i class="fa-solid fa-heart"></i> Favoritos</a>`;
        } else if (rol === 'vendedor') {
            menuExtra = `<a href="vendedor.php" class="sell-link"><i class="fa-solid fa-chart-line"></i> Mi Panel</a>`;
        } else if (rol === 'admin') {
            menuExtra = `<a href="admin.php" class="sell-link"><i class="fa-solid fa-shield-halved"></i> Admin</a>`;
        }

        userHTML = `
            <div class="nav-actions" style="display:flex; align-items:center; gap:20px;">
                ${menuExtra}
                <div class="nav-user" style="display:flex; align-items:center; gap:8px;">
                    <span class="nav-nombre" style="font-weight:600; font-size:0.95rem;">${nombre}</span>
                    <span class="badge-rol-nav badge-${rol}" style="font-size:0.75rem;">${rol}</span>
                </div>
                <button onclick="Auth.logout()" class="btn btn-outline btn-sm-nav" style="margin-top:0; padding:6px 12px;">
                    <i class="fa-solid fa-right-from-bracket"></i> Salir
                </button>
            </div>
        `;
    }

    navbar.innerHTML = logoHTML + centerHTML + userHTML;
}

// Protege páginas que requieren login
function requireLogin(redirectUrl = 'auth.php') {
    if (!Auth.isLoggedIn()) {
        sessionStorage.setItem('redirectAfterLogin', window.location.href);
        window.location.href = redirectUrl;
        return false;
    }
    return true;
}

// Protege páginas que requieren un rol específico
function requireRol(...roles) {
    if (!Auth.isLoggedIn()) {
        sessionStorage.setItem('redirectAfterLogin', window.location.href);
        window.location.href = 'auth.php';
        return false;
    }
    if (!roles.includes(Auth.getRol())) {
        window.location.href = 'index.php';
        return false;
    }
    return true;
}