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
                <p>Únete a nuestra comunidad y encuentra propiedades con un valor arquitectónico excepcional.</p>
            </div>
        </div>

        <div class="auth-form-area">
            <div class="auth-form-wrapper">
                
                <div class="mobile-logo logo">Estate<span>Arch</span></div>

                <div class="auth-tabs">
                    <button class="tab-btn active" onclick="switchTab('login', this)">Iniciar Sesión</button>
                    <button class="tab-btn" onclick="switchTab('register', this)">Registrarse</button>
                </div>

                <div id="auth-msg" style="display:none; padding:12px; margin-bottom:20px; border-radius:6px; text-align:center; font-size:0.9rem; font-weight:600;"></div>

                <form id="login-form" class="auth-form">
                    <div class="form-group">
                        <label>CORREO ELECTRÓNICO</label>
                        <input type="email" id="login-correo" class="form-control" placeholder="ejemplo@correo.com">
                    </div>
                    <div class="form-group">
                        <label>CONTRASEÑA</label>
                        <input type="password" id="login-password" class="form-control" placeholder="••••••••">
                    </div>
                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox"> Recordarme
                        </label>
                        <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a>
                    </div>
                    <button type="button" id="btn-login" class="btn btn-primary w-100 btn-large" onclick="iniciarSesion()">Iniciar Sesión</button>
                </form>

                <form id="register-form" class="auth-form" style="display: none;">
                    <div class="form-row">
                        <div class="form-group flex-1">
                            <label>NOMBRE(S)</label>
                            <input type="text" id="reg-nombre" class="form-control" placeholder="Tu nombre">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group flex-1">
                            <label>APELLIDO PATERNO</label>
                            <input type="text" id="reg-apat" class="form-control" placeholder="Paterno">
                        </div>
                        <div class="form-group flex-1">
                            <label>APELLIDO MATERNO</label>
                            <input type="text" id="reg-amat" class="form-control" placeholder="Materno">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group flex-1">
                            <label>CURP</label>
                            <input type="text" id="reg-curp" class="form-control" placeholder="18 caracteres" maxlength="18" style="text-transform: uppercase;">
                        </div>
                        <div class="form-group flex-1">
                            <label>FECHA NACIMIENTO</label>
                            <input type="date" id="reg-fecha-nac" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>CORREO ELECTRÓNICO</label>
                        <input type="email" id="reg-correo" class="form-control" placeholder="ejemplo@correo.com">
                    </div>
                    <div class="form-group">
                        <label>CONTRASEÑA</label>
                        <input type="password" id="reg-password" class="form-control" placeholder="Crea una contraseña segura">
                    </div>
                    
                    <input type="hidden" id="reg-rol" value="comprador">

                    <button type="button" id="btn-registro" class="btn btn-primary w-100 btn-large" onclick="registrarse()">Crear Cuenta</button>
                    
                    <p class="terms-text" style="color: var(--text-gray);">
                        Al registrarte, aceptas nuestros Términos de Servicio y Política de Privacidad.
                    </p>
                </form>

            </div>
        </div>
    </div>
    
    <script src="js/auth.js"></script>
</body>
</html>