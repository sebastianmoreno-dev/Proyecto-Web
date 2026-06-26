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
            <span id="bread-titulo">Cargando...</span>
        </div>

        <div class="gallery-grid">
            <div class="gallery-main">
                <img id="main-img" src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1200&q=80" alt="Foto principal">
                <span class="badge badge-detail" id="det-tipo">CASA</span>
            </div>
            <div class="gallery-thumbs" id="gallery-thumbs">
                <img src="https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?w=400&q=80" alt="Foto 2" onclick="changeImg(this)">
                <img src="https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=400&q=80" alt="Foto 3" onclick="changeImg(this)">
                <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=400&q=80" alt="Foto 4" onclick="changeImg(this)">
                <div class="gallery-more" onclick="changeImg(this, 'https://images.unsplash.com/photo-1613490908676-e1ceb62eb8aa?w=400&q=80')">
                    <img src="https://images.unsplash.com/photo-1613490908676-e1ceb62eb8aa?w=400&q=80" alt="Foto 5">
                    <span>+ fotos</span>
                </div>
            </div>
        </div>

        <div class="detail-layout">
            <div class="detail-left">
                <div class="card">
                    <div class="detail-title-row">
                        <div>
                            <h1 class="detail-title" id="det-titulo">Cargando propiedad...</h1>
                            <p class="location"><i class="fa-solid fa-location-dot"></i> <span id="det-ubicacion">Buscando ubicación...</span></p>
                        </div>
                        <button class="btn-heart-lg" onclick="toggleHeartDetail(this)">
                            <i class="fa-solid fa-heart"></i> Guardar
                        </button>
                    </div>
                    <div class="detail-amenities-row">
                        <div class="amenity-box">
                            <i class="fa-solid fa-bed"></i>
                            <span class="amenity-num" id="det-habitaciones">-</span>
                            <span class="amenity-label">Habitaciones</span>
                        </div>
                        <div class="amenity-box">
                            <i class="fa-solid fa-bath"></i>
                            <span class="amenity-num" id="det-banos">-</span>
                            <span class="amenity-label">Baños</span>
                        </div>
                        <div class="amenity-box">
                            <i class="fa-solid fa-ruler-combined"></i>
                            <span class="amenity-num" id="det-area">-</span>
                            <span class="amenity-label">m² Total</span>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h2 class="section-title-detail">Descripción</h2>
                    <p class="detail-desc" id="det-descripcion">Cargando descripción de la propiedad...</p>
                </div>
            </div>

            <div class="detail-right">
                <div class="card price-card">
                    <p class="price-label">PRECIO DE VENTA</p>
                    <h2 class="detail-price" id="det-precio">$-</h2>
                    <p class="price-sub">USD · Precio negociable</p>
                    <button class="btn btn-primary w-100 btn-large" style="margin-top: 20px;"><i class="fa-solid fa-calendar-check"></i> Agendar Visita</button>
                </div>
                <div class="card agent-card">
                    <p class="agent-label">AGENTE RESPONSABLE</p>
                    <div class="agent-info">
                        <div class="agent-avatar"><i class="fa-solid fa-user"></i></div>
                        <div>
                            <h3 class="agent-name" id="det-agente-nombre">Cargando agente...</h3>
                            <p class="agent-role" id="det-agente-correo">Agente · EstateArch</p>
                        </div>
                    </div>
                    <button onclick="iniciarChat()" class="btn btn-outline w-100" style="display:block; text-align:center;">
                        <i class="fa-regular fa-comments"></i> Contactar Agente
                    </button>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="js/navbar.js"></script>
    <script src="js/detalle.js"></script>
    <script>
        renderNavbar('propiedades');

        // ── LÓGICA PARA CREAR/IR AL CHAT ──
        async function iniciarChat() {
            const token = localStorage.getItem('token') || sessionStorage.getItem('token');
            const API_URL = '/4CV3/moreseba/Proyecto-Web/Backend/api';
            
            // 1. Verificamos que el usuario haya iniciado sesión
            if (!token) {
                alert("Debes iniciar sesión para contactar a este agente.");
                window.location.href = "auth.php";
                return;
            }

            // 2. Extraemos el ID de la propiedad directamente de la URL actual
            const urlParams = new URLSearchParams(window.location.search);
            const idPropiedad = urlParams.get('id');

            if (!idPropiedad) {
                alert("Error: No se pudo identificar la propiedad.");
                return;
            }

            try {
                // Le avisamos al backend que queremos abrir un canal de chat
                const res = await fetch(`${API_URL}/chats`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify({ id_propiedad: idPropiedad })
                });

                const data = await res.json();
                
                // Buscamos el ID del chat que el backend nos acaba de crear o devolver
                const idChat = data.id_chat || data.id || '';

                if (idChat) {
                    // Si tenemos el ID, lo mandamos en la URL para que chat.php lo atrape
                    window.location.href = `chat.php?chat_id=${idChat}`;
                } else {
                    window.location.href = 'chat.php';
                }

            } catch (error) {
                console.error("Error al iniciar el chat:", error);
                window.location.href = 'chat.php';
            }
        }
    </script>
</body>
</html>