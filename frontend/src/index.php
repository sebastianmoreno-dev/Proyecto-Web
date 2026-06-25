<?php 
    $titulo = "EstateArch - Inicio"; 
    include 'includes/head.php'; 
?>
<body>

    <?php include 'includes/header.php'; ?>

    <section class="hero">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Arquitectónicamente Sólido.<br><span class="text-green">Excepcionalmente Tuyo.</span></h1>
            <p>Descubre una curaduría exclusiva de propiedades donde la excelencia estructural se encuentra con el diseño de vanguardia.</p>
        </div>
        <form class="search-bar" onsubmit="event.preventDefault(); aplicarFiltros();">
            <div class="search-item">
                <i class="fa-solid fa-location-dot"></i>
                <div class="search-text">
                    <span class="label">UBICACIÓN</span>
                    <select id="filtro-ubicacion" style="border:none; background:transparent; font-family:inherit; font-size:0.9rem; color:var(--text-dark); cursor:pointer; width:100%; outline:none;">
                        <option value="">Todas las ubicaciones</option>
                        <option value="Alvaro Obregon">Álvaro Obregón</option>
                        <option value="Azcapotzalco">Azcapotzalco</option>
                        <option value="Benito Juarez">Benito Juárez</option>
                        <option value="Coyoacan">Coyoacán</option>
                        <option value="Cuajimalpa">Cuajimalpa de Morelos</option>
                        <option value="Cuauhtemoc">Cuauhtémoc</option>
                        <option value="Gustavo A. Madero">Gustavo A. Madero</option>
                        <option value="Iztacalco">Iztacalco</option>
                        <option value="Iztapalapa">Iztapalapa</option>
                        <option value="Magdalena Contreras">La Magdalena Contreras</option>
                        <option value="Miguel Hidalgo">Miguel Hidalgo</option>
                        <option value="Milpa Alta">Milpa Alta</option>
                        <option value="Tlahuac">Tláhuac</option>
                        <option value="Tlalpan">Tlalpan</option>
                        <option value="Venustiano Carranza">Venustiano Carranza</option>
                        <option value="Xochimilco">Xochimilco</option>
                    </select>
                </div>
            </div>
            <div class="divider"></div>
            <div class="search-item">
                <i class="fa-solid fa-house"></i>
                <div class="search-text">
                    <span class="label">TIPO</span>
                    <select id="filtro-tipo" style="border:none; background:transparent; font-family:inherit; font-size:0.9rem; color:var(--text-dark); cursor:pointer; width:100%; outline:none;">
                        <option value="">Todos</option>
                        <option value="casa">Casa</option>
                        <option value="departamento">Departamento</option>
                    </select>
                </div>
            </div>
            <div class="divider"></div>
            <div class="search-item">
                <i class="fa-solid fa-money-bill"></i>
                <div class="search-text">
                    <span class="label">PRECIO MÁXIMO</span>
                    <select id="filtro-precio" style="border:none; background:transparent; font-family:inherit; font-size:0.9rem; color:var(--text-dark); cursor:pointer; width:100%; outline:none;">
                        <option value="">Cualquier precio</option>
                        <option value="1000000">Hasta $1M USD</option>
                        <option value="2000000">Hasta $2M USD</option>
                        <option value="3000000">Hasta $3M USD</option>
                        <option value="4000000">Hasta $4M USD</option>
                        <option value="5000000">Hasta $5M USD</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-search">
                <i class="fa-solid fa-magnifying-glass"></i> Buscar
            </button>
        </form>
    </section>

    <section class="properties">
        <div class="section-header">
            <div>
                <p class="subtitle">SELECCIÓN ELITE</p>
                <h2>Propiedades Destacadas</h2>
            </div>
            <a href="#" class="view-all">Ver todas <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="cards-grid" id="cards-grid">
            <div style="text-align:center;padding:60px;color:var(--text-gray);grid-column:1/-1;">
                <i class="fa-solid fa-spinner fa-spin" style="font-size:2rem;margin-bottom:15px;display:block;"></i>
                Cargando propiedades...
            </div>
        </div>
    </section>

    <section class="expertise">
        <div class="expertise-image">
            <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=800&q=80" alt="Agente mostrando casa">
        </div>
        <div class="expertise-text">
            <h2>Expertise en Arquitectura & Mercado</h2>
            <p>En EstateArch, no solo vendemos casas; curamos espacios que resisten la prueba del tiempo. Nuestra evaluación de propiedades incluye un análisis estructural y arquitectónico riguroso para garantizar tu inversión.</p>
            <div class="stats">
                <div class="stat-item"><h3>15+</h3><p>AÑOS DE HERENCIA</p></div>
                <div class="stat-item"><h3>500+</h3><p>JOYAS VENDIDAS</p></div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="js/navbar.js"></script>
    <script src="js/index.js"></script>
    <script>
        // Esta línea ya debería pintar tu barra correctamente basándonos en los ajustes anteriores
        renderNavbar('propiedades');
    </script>
</body>
</html>