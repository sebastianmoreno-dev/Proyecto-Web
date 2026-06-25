<?php
session_start();
require_once 'includes/conexion.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'agente') {
    header('Location: auth.php');
    exit;
}

$stmt = $pdo->prepare("SELECT c.*, pd.prd_titulo FROM cita c 
                       JOIN negociacion n ON c.id_negociacion = n.id_negociacion
                       JOIN propiedad p ON n.id_propiedad = p.id_propiedad
                       JOIN propiedad_datos pd ON p.id_datos = pd.id_datos
                       WHERE c.id_agente = ?");
$stmt->execute([$_SESSION['id']]); 
$citas = $stmt->fetchAll();

$titulo = "Panel Agente - EstateArch";
include 'includes/head.php';
?>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="panel-layout">
        <aside class="panel-sidebar">
            <div class="sidebar-user">
                <div class="s-nombre"><?= htmlspecialchars($_SESSION['nombre']) ?></div>
                <div class="s-rol">AGENTE INMOBILIARIO</div>
            </div>
            <a href="agente.php" class="sidebar-link active"><i class="fa-solid fa-calendar-check"></i> Mis Citas</a>
            <a href="includes/logout.php" class="sidebar-link"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</a>
        </aside>
        <main class="panel-main">
            <h1>Mis Citas Programadas</h1>
            <table class="panel-table">
                <thead><tr><th>Propiedad</th><th>Fecha</th><th>Hora</th><th>Estatus</th></tr></thead>
                <tbody>
                    <?php foreach ($citas as $cita): ?>
                    <tr>
                        <td><?= htmlspecialchars($cita['prd_titulo']) ?></td>
                        <td><?= $cita['cit_fecha'] ?></td>
                        <td><?= $cita['cit_hora'] ?></td>
                        <td><?= $cita['id_estatus_cita'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>