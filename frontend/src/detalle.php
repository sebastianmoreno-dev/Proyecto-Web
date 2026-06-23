<?php 
    $titulo = "Detalle de Propiedad - EstateArch"; 
    include 'includes/head.php'; 
?>
<body class="bg-light">

    <?php include 'includes/header.php'; ?>

    <main class="container page-content">
        <div class="breadcrumb">
            <a href="index.php"><i class="fa-solid fa-house"></i> Inicio</a>
            <i class="fa-solid fa-chevron-right"></i>
            <a href="index.php">Propiedades</a>
            <i class="fa-solid fa-chevron-right"></i>
            <span>Residencia en Polanco</span>
        </div>

        <div class="gallery-grid">
            <div class="gallery-main">
                <img id="main-img" src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1200&q=80" alt="Foto principal">
                <span class="badge badge-detail">DESTACADO</span>
            </div>
            <div class="gallery-thumbs">
                <img src="https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?w=400&q=80" alt="Foto 2" onclick="changeImg(this)">
                <img src="https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=400&q=80" alt="Foto 3" onclick="changeImg(this)">
                <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=400&q=80" alt="Foto 4" onclick="changeImg(this)">
                <div class="gallery-more" onclick="changeImg(this, 'https://images.unsplash.com/photo-1613490908676-e1ceb62eb8aa?w=400&q=80')">
                    <img src="https://images.unsplash.com/photo-1613490908676-e1ceb62eb8aa?w=400&q=80" alt="Foto 5">
                    <span>+3 fotos</span>
                </div>
            </div>
        </div>

        <div class="detail-layout">
            <div class="detail-left">
                <div class="card">
                    <div class="detail-title-row">
                        <div>
                            <h1 class="detail-title">Residencia Moderna en Polanco</h1>
                            <p class="location"><i class="fa-solid fa-location-dot"></i> Polanco, Ciudad de México, México</p>
                        </div>
                        <button class="btn-heart-lg" onclick="toggleHeartDetail(this)">
                            <i class="fa-solid fa-heart"></i> Guardar
                        </button>
                    </div>
                    <div class="detail-amenities-row">
                        <div class="amenity-box">
                            <i class="fa-solid fa-bed"></i>
                            <span class="amenity-num">4</span>
                            <span class="amenity-label">Habitaciones</span>
                        </div>
                        <div class="amenity-box">
                            <i class="fa-solid fa-bath"></i>
                            <span class="amenity-num">5</span>
                            <span class="amenity-label">Baños</span>
                        </div>
                        <div class="amenity-box">
                            <i class="fa-solid fa-ruler-combined"></i>
                            <span class="amenity-num">420</span>
                            <span class="amenity-label">m² Total</span>
                        </div>
                        <div class="amenity-box">
                            <i class="fa-solid fa-car"></i>
                            <span class="amenity-num">2</span>
                            <span class="amenity-label">Estacionamientos</span>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h2 class="section-title-detail">Descripción</h2>
                    <p class="detail-desc">Esta impresionante residencia en el corazón de Polanco combina arquitectura contemporánea con los más altos estándares de construcción...</p>
                </div>

                <div class="card">
                    <h2 class="section-title-detail">Características</h2>
                    <div class="features-grid">
                        <div class="feature-item"><i class="fa-solid fa-check"></i> Alberca privada</div>
                        <div class="feature-item"><i class="fa-solid fa-check"></i> Seguridad 24/7</div>
                        <div class="feature-item"><i class="fa-solid fa-check"></i> Cocina equipada</div>
                        <div class="feature-item"><i class="fa-solid fa-check"></i> Terraza</div>
                    </div>
                </div>
            </div>

            <div class="detail-right">
                <div class="card price-card">
                    <p class="price-label">PRECIO DE VENTA</p>
                    <h2 class="detail-price">$2,450,000</h2>
                    <p class="price-sub">USD · Precio negociable</p>
                    <button class="btn btn-primary w-100 btn-large" style="margin-top: 20px;"><i class="fa-solid fa-calendar-check"></i> Agendar Visita</button>
                </div>
                <div class="card agent-card">
                    <p class="agent-label">AGENTE RESPONSABLE</p>
                    <div class="agent-info">
                        <div class="agent-avatar"><i class="fa-solid fa-user"></i></div>
                        <div>
                            <h3 class="agent-name">Carlos Mendoza</h3>
                            <p class="agent-role">Agente Senior · EstateArch</p>
                        </div>
                    </div>
                    <a href="contacto.php" class="btn btn-outline w-100" style="display:block; text-align:center;">Contactar Agente</a>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="js/navbar.js"></script>
    <script src="js/detalle.js"></script>
    <script>
        renderNavbar('propiedades');
    </script>
</body>
</html>