<?php 
    $titulo = "Panel Agente - EstateArch";
    include 'includes/head.php';
?>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="panel-layout">
        <aside class="panel-sidebar" style="background-color: var(--text-dark);">
            <div class="sidebar-user">
                <div class="s-nombre" id="sidebar-nombre">Cargando...</div>
                <div class="s-rol">AGENTE INMOBILIARIO</div>
            </div>
            <button class="sidebar-link active" onclick="mostrarSeccion('citas', this)">
                <i class="fa-solid fa-calendar-check"></i> Gestión de Citas
            </button>
            <button class="sidebar-link" onclick="mostrarSeccion('ventas', this)">
                <i class="fa-solid fa-file-invoice-dollar"></i> Historial y Comisiones
            </button>
            <button class="sidebar-link" onclick="Auth.logout()" style="margin-top:auto;">
                <i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión
            </button>
        </aside>

        <main class="panel-main">
            <div class="panel-header">
                <h1>Panel Operativo</h1>
                <p style="color: var(--text-gray); font-size: 0.9rem;">Control de negociaciones y transacciones cerradas.</p>
            </div>

            <div class="panel-section active" id="sec-citas">
                <h2>Citas Activas y Negociaciones</h2>
                <table class="panel-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>PROPIEDAD</th>
                            <th>FECHA</th>
                            <th>HORA</th>
                            <th>ESTATUS</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-citas">
                        <tr><td colspan="5" style="text-align:center;">Cargando citas...</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="panel-section" id="sec-ventas">
                <h2>Transacciones Concretadas</h2>
                
                <div class="stats-row" id="stats-ventas">
                    <div class="stat-box">
                        <div class="num" id="total-ventas">0</div>
                        <div class="lbl">PROPIEDADES VENDIDAS</div>
                    </div>
                    <div class="stat-box">
                        <div class="num" id="total-comisiones" style="color: #4CAF50;">$0</div>
                        <div class="lbl">COMISIONES GENERADAS (5%)</div>
                    </div>
                </div>

                <table class="panel-table" style="width: 100%; margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>FOLIO</th>
                            <th>PROPIEDAD</th>
                            <th>FECHA CIERRE</th>
                            <th>PRECIO FINAL</th>
                            <th>TU COMISIÓN</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-ventas">
                        <tr><td colspan="5" style="text-align:center;">Cargando historial...</td></tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="js/navbar.js"></script>
    <script src="js/agente.js"></script>
    <script>renderNavbar();</script>
</body>
</html>