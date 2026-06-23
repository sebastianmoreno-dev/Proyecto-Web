<?php 
    $titulo = "Nueva Propiedad - EstateArch"; 
    include 'includes/head.php'; 
?>
<body class="bg-light">

    <div class="container page-content">
        <a href="vendedor.php" style="display:inline-block; margin-bottom: 20px; color: var(--primary-green); font-weight: 600; text-decoration: none;">
            <i class="fa-solid fa-arrow-left"></i> Volver a mi panel
        </a>
    </div>

    <main class="container page-content">
        <div class="page-header">
            <h1 class="page-title">Nueva Propiedad</h1>
            <p class="page-subtitle">Complete los detalles arquitectónicos y espaciales de su inmueble.</p>
        </div>

        <div class="layout-grid">
            <div class="left-column">
                <section class="card form-section">
                    <div class="section-header-flex">
                        <h2>Galería Visual</h2>
                        <span class="requirement-text">MÍNIMO 5 FOTOS</span>
                    </div>
                    <div class="upload-zone" id="upload-zone">
                        <i class="fa-solid fa-camera-retro"></i>
                        <p><strong>Arrastre sus imágenes aquí</strong></p>
                    </div>
                    <div class="thumbnails-grid" id="thumbs-grid">
                        <div class="thumbnail empty"><i class="fa-regular fa-image"></i></div>
                        <div class="thumbnail empty"><i class="fa-regular fa-image"></i></div>
                        <div class="thumbnail empty"><i class="fa-regular fa-image"></i></div>
                        <div class="thumbnail empty"><i class="fa-regular fa-image"></i></div>
                    </div>
                </section>

                <section class="card form-section">
                    <h2>Especificaciones Generales</h2>
                    <div class="form-row">
                        <div class="form-group flex-2">
                            <label>TÍTULO DE LA PUBLICACIÓN</label>
                            <input type="text" id="prop-titulo" class="form-control" placeholder="Ej: Residencia Moderna en Los Olivos">
                        </div>
                        <div class="form-group flex-1">
                            <label>TIPO DE PROPIEDAD</label>
                            <select id="prop-tipo" class="form-control">
                                <option value="casa">Casa Unifamiliar</option>
                                <option value="departamento">Departamento</option>
                                <option value="terreno">Terreno</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group flex-1">
                            <label>PRECIO (USD)</label>
                            <input type="number" id="prop-precio" class="form-control">
                        </div>
                        <div class="form-group flex-1">
                            <label>ÁREA TOTAL (m²)</label>
                            <input type="number" id="prop-area" class="form-control">
                        </div>
                        <div class="form-group flex-1">
                            <label>HABITACIONES</label>
                            <div class="counter-input">
                                <button type="button" id="btn-menos">-</button>
                                <input type="number" id="prop-habitaciones" value="0" readonly>
                                <button type="button" id="btn-mas">+</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>DESCRIPCIÓN ARQUITECTÓNICA</label>
                        <textarea id="prop-descripcion" class="form-control" rows="5"></textarea>
                    </div>
                </section>
            </div>

            <div class="right-column">
                <section class="card form-section">
                    <h2>Ubicación</h2>
                    <input type="text" id="prop-ubicacion" class="form-control" placeholder="Calle, Número, Ciudad">
                </section>
                <section class="card summary-card">
                    <h2>Resumen de Publicación</h2>
                    <button class="btn btn-white w-100" onclick="publicarPropiedad()">Publicar Ahora <i class="fa-solid fa-rocket"></i></button>
                </section>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="js/navbar.js"></script>
    <script src="js/nuevaPropiedad.js"></script>
    <script>renderNavbar('');</script>
</body>
</html>