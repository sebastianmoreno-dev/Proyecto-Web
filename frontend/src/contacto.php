<?php 
    $titulo = "Contacto - EstateArch"; 
    include 'includes/head.php'; 
?>
<body class="bg-light">

    <?php include 'includes/header.php'; ?>

    <main class="container page-content">
        <div class="page-header" style="text-align: center; max-width: 600px; margin: 0 auto 50px;">
            <p class="subtitle">ESTAMOS AQUÍ PARA AYUDARTE</p>
            <h1 class="page-title" style="color: var(--text-dark); font-size: 2.5rem;">Hablemos</h1>
            <p style="color: var(--text-gray); font-size: 1rem; line-height: 1.6;">¿Tienes dudas sobre una propiedad, quieres listar la tuya, o simplemente deseas asesoría? Escríbenos.</p>
        </div>

        <div class="contact-layout">
            <div class="contact-form-area card">
                <h2 style="margin-bottom: 25px; font-size: 1.3rem;">Envíanos un mensaje</h2>
                <div class="form-row">
                    <div class="form-group flex-1">
                        <label>NOMBRE</label>
                        <input type="text" class="form-control" placeholder="Tu nombre">
                    </div>
                    <div class="form-group flex-1">
                        <label>APELLIDO</label>
                        <input type="text" class="form-control" placeholder="Tu apellido">
                    </div>
                </div>
                <div class="form-group">
                    <label>CORREO ELECTRÓNICO</label>
                    <div class="input-with-icon">
                        <i class="fa-regular fa-envelope"></i>
                        <input type="email" class="form-control" placeholder="ejemplo@correo.com">
                    </div>
                </div>
                <div class="form-group">
                    <label>TELÉFONO (OPCIONAL)</label>
                    <div class="input-with-icon">
                        <i class="fa-solid fa-phone"></i>
                        <input type="tel" class="form-control" placeholder="+52 55 0000 0000">
                    </div>
                </div>
                <div class="form-group">
                    <label>ASUNTO</label>
                    <select class="form-control">
                        <option value="">Selecciona un asunto...</option>
                        <option>Información sobre una propiedad</option>
                        <option>Quiero vender mi propiedad</option>
                        <option>Asesoría de inversión</option>
                        <option>Soporte técnico</option>
                        <option>Otro</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>MENSAJE</label>
                    <textarea class="form-control" rows="5" placeholder="Cuéntanos en qué podemos ayudarte..."></textarea>
                </div>
                <button type="button" class="btn btn-primary w-100 btn-large" onclick="sendMessage()">
                    <i class="fa-solid fa-paper-plane"></i> Enviar Mensaje
                </button>
                <div id="success-msg" style="display:none; margin-top:15px; padding:15px; background:#F0FBF4; border-radius:6px; color:#1B4332; font-size:0.9rem; text-align:center;">
                    <i class="fa-solid fa-circle-check"></i> ¡Mensaje enviado con éxito! Te contactaremos pronto.
                </div>
            </div>

            <div class="contact-info-area">
                <div class="contact-info-card card">
                    <div class="contact-icon"><i class="fa-solid fa-location-dot"></i></div>
                    <h3>Oficina Principal</h3>
                    <p>Av. Presidente Masaryk 111<br>Polanco, CDMX, México 11560</p>
                </div>
                <div class="contact-info-card card">
                    <div class="contact-icon"><i class="fa-solid fa-phone"></i></div>
                    <h3>Teléfono</h3>
                    <p>+52 55 1234 5678<br>Lun–Vie · 9:00am – 7:00pm</p>
                </div>
                <div class="contact-info-card card">
                    <div class="contact-icon"><i class="fa-regular fa-envelope"></i></div>
                    <h3>Correo Electrónico</h3>
                    <p>contacto@estatearch.mx<br>Respuesta en menos de 24 horas</p>
                </div>
                <div class="contact-info-card card">
                    <div class="contact-icon"><i class="fa-brands fa-whatsapp"></i></div>
                    <h3>WhatsApp</h3>
                    <p>+52 55 9876 5432<br>Disponible también en fines de semana</p>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="js/navbar.js"></script>
    <script src="js/contacto.js"></script>
    <script>
        renderNavbar('contacto');
    </script>
</body>
</html>