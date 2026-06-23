<?php 
    $titulo = "Acceso - EstateArch"; 
    include 'includes/head.php'; 
?>
<body>
    <div class="auth-layout">
        <div class="auth-cover">
            <div class="auth-overlay"></div>
            <div class="auth-cover-content">
                <div class="logo logo-light">Estate<span>Arch</span></div>
                <h1>Descubre tu próximo espacio.</h1>
            </div>
        </div>

        <div class="auth-form-area">
            <div class="auth-form-wrapper">
                <div class="auth-tabs">
                    <button class="tab-btn active" onclick="switchTab('login', this)">Iniciar Sesión</button>
                    <button class="tab-btn" onclick="switchTab('register', this)">Registrarse</button>
                </div>
                <form id="login-form" class="auth-form">
                    <button type="button" id="btn-login" class="btn btn-primary w-100" onclick="iniciarSesion()">Iniciar Sesión</button>
                </form>
            </div>
        </div>
    </div>
    <script src="js/auth.js"></script>
</body>
</html>