<?php
session_start();
require_once 'includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['rol'] === 'agente') {
    $id_negociacion = $_POST['id_negociacion'];
    $precio_final = $_POST['precio'];
    $id_agente = $_SESSION['id'];

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO operacion_venta (id_negociacion, id_vendedor, id_agente, opv_precio_final) 
                               SELECT id_negociacion, (SELECT id_vendedor FROM propiedad WHERE id_propiedad = n.id_propiedad), ?, ? 
                               FROM negociacion n WHERE id_negociacion = ?");
        $stmt->execute([$id_agente, $precio_final, $id_negociacion]);

        $pdo->prepare("UPDATE propiedad_datos SET id_estatus_propiedad = 3 WHERE id_datos = (SELECT id_datos FROM propiedad JOIN negociacion n ON propiedad.id_propiedad = n.id_propiedad WHERE n.id_negociacion = ?)")
            ->execute([$id_negociacion]);

        $pdo->commit();
        header('Location: agente.php?success=1');
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error en la operación: " . $e->getMessage();
    }
}
?>