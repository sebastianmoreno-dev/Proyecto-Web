<?php
// backend/controllers/AdminController.php
class AdminController {
    public static function getStats($pdo) {
        $u = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
        $p = $pdo->query("SELECT COUNT(*) FROM propiedades")->fetchColumn();
        $a = $pdo->query("SELECT COUNT(*) FROM propiedades WHERE estado = 'activa'")->fetchColumn();
        $v = $pdo->query("SELECT COUNT(*) FROM propiedades WHERE estado = 'vendida'")->fetchColumn();
        echo json_encode(["total_usuarios" => $u, "total_propiedades" => $p, "total_activas" => $a, "total_vendidas" => $v]);
    }

    public static function getUsuarios($pdo) {
        echo json_encode($pdo->query("SELECT id, nombre, apellido, correo, rol FROM usuarios")->fetchAll());
    }

    public static function cambiarRol($pdo, $id, $body) {
        $stmt = $pdo->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
        $stmt->execute([$body['rol'], $id]);
        echo json_encode(["mensaje" => "Rol actualizado"]);
    }

    public static function eliminarUsuario($pdo, $id) {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(["mensaje" => "Usuario eliminado"]);
    }

    public static function getPropiedades($pdo) {
        echo json_encode($pdo->query("SELECT p.id, p.titulo, p.precio, p.estado, u.nombre AS vendedor_nombre FROM propiedades p JOIN usuarios u ON p.vendedor_id = u.id")->fetchAll());
    }

    public static function cambiarEstado($pdo, $id, $body) {
        $stmt = $pdo->prepare("UPDATE propiedades SET estado = ? WHERE id = ?");
        $stmt->execute([$body['estado'], $id]);
        echo json_encode(["mensaje" => "Estado actualizado"]);
    }
}