<?php 
    $titulo = "Mis Favoritos - EstateArch"; 
    include 'includes/head.php'; 
?>
<body class="bg-light">

    <main class="container page-content">
        <a href="index.php" style="display:inline-block; margin-bottom: 20px; color: var(--primary-green); font-weight: 600; text-decoration: none;">
            <i class="fa-solid fa-arrow-left"></i> Volver al catálogo
        </a>

        <div class="page-header">
            <h1 class="page-title">Mis Favoritos</h1>
            <p class="page-subtitle">Tu curaduría personal de propiedades de alto valor arquitectónico.</p>
        </div>

        <div class="grid-propiedades" id="contenedor-favoritos">
            <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: #666;">
                <i class="fa-solid fa-spinner fa-spin fa-2x"></i>
                <p style="margin-top: 10px;">Cargando tu selección...</p>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="js/navbar.js"></script>
    <script src="js/favoritos.js"></script>
    <script>renderNavbar('');</script>
</body>
</html>