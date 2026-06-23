<?php 
    $titulo = "Panel Admin - EstateArch"; 
    include 'includes/head.php'; 
?>
<body>

    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="logo">Estate<span>Arch</span></div>
            <div class="sidebar-link active" onclick="mostrarPanel('stats', this)"><i class="fa-solid fa-chart-pie"></i> Dashboard</div>
            <div class="sidebar-link" onclick="mostrarPanel('usuarios', this)"><i class="fa-solid fa-users"></i> Usuarios</div>
            <div class="sidebar-link" onclick="mostrarPanel('propiedades', this)"><i class="fa-solid fa-house"></i> Propiedades</div>
            <div class="sidebar-link" onclick="cerrarSesionAdmin()" style="margin-top:auto;"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</div>
        </aside>

        <main class="admin-main">
            <div class="admin-header">
                <h1>Panel de Administración</h1>
                <p id="admin-nombre">Cargando...</p>
            </div>
            <div class="panel active" id="panel-stats">
                <h2>Resumen General</h2>
            </div>
        </main>
    </div>

    <script src="js/admin.js"></script>
</body>
</html>