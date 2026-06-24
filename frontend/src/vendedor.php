<?php 
    $titulo = "Mi Panel - EstateArch"; 
    include 'includes/head.php'; 
?>
<body>

    <?php include 'includes/header.php'; ?>

    <div class="panel-layout">
        <aside class="panel-sidebar">
            <div class="sidebar-user">
                <div class="s-nombre" id="sidebar-nombre">Cargando...</div>
                <div class="s-rol" id="sidebar-rol">Vendedor</div>
            </div>
            <button class="sidebar-link active" onclick="mostrarSeccion('mis-propiedades', this)"><i class="fa-solid fa-house"></i> Mis Propiedades</button>
            <button class="sidebar-link" onclick="mostrarSeccion('mensajes', this)"><i class="fa-solid fa-envelope"></i> Mensajes</button>
            <button class="sidebar-link" onclick="window.location.href='nuevaPropiedad.php'"><i class="fa-solid fa-plus"></i> Nueva Propiedad</button>
            <button class="sidebar-link" onclick="Auth.logout()"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</button>
        </aside>

        <main class="panel-main">
            <div class="panel-header"><h1>Mi Panel</h1></div>
            <div class="stats-row" id="stats-row">
                <div class="stat-box"><div class="num" id="st-propiedades">-</div><div class="lbl">MIS PROPIEDADES</div></div>
                <div class="stat-box"><div class="num" id="st-mensajes">-</div><div class="lbl">MENSAJES NUEVOS</div></div>
            </div>

            <div class="panel-section active" id="sec-mis-propiedades">
                <h2>Mis Propiedades <a href="nuevaPropiedad.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nueva</a></h2>
                <div id="lista-propiedades"><div class="empty-state">Cargando...</div></div>
            </div>

            <div class="panel-section" id="sec-mensajes">
                <h2>Mensajes</h2>
                <div class="chat-layout">
                    <aside class="chat-lista" id="chat-lista">
                        <div class="chat-vacio">Cargando chats...</div>
                    </aside>
                    <section class="chat-conversacion" id="chat-conversacion">
                        <div class="chat-vacio">Selecciona un chat para ver los mensajes.</div>
                    </section>
                </div>
            </div>
        </main>
    </div>

    <script src="js/navbar.js"></script>
    <script src="js/vendedor.js"></script>
    <script src="js/chat.js"></script>
</body>
</html>