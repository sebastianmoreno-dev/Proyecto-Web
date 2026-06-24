<?php
// backend/controllers/AdminController.php
class AdminController {
    
    // 1. Estadísticas del Dashboard Administrativo
    public static function getStats($pdo) {
        $u = $pdo->query("SELECT COUNT(*) FROM usuario WHERE usu_activo = TRUE")->fetchColumn();
        $p = $pdo->query("SELECT COUNT(*) FROM propiedad")->fetchColumn();
        $a = $pdo->query("SELECT COUNT(*) FROM propiedad_datos WHERE id_estatus_propiedad = 1")->fetchColumn();
        $v = $pdo->query("SELECT COUNT(*) FROM propiedad_datos WHERE id_estatus_propiedad = 3")->fetchColumn();
        
        echo json_encode([
            "total_usuarios" => $u, 
            "total_propiedades" => $p, 
            "total_activas" => $a, 
            "total_vendidas" => $v
        ]);
    }

    // 2. Listado de Usuarios
    public static function getUsuarios($pdo) {
        $stmt = $pdo->query("
            SELECT 
                u.id_usuario AS id, 
                p.per_nombres AS nombre, 
                p.per_apat AS apellido, 
                u.usu_correo AS correo, 
                t.tipo_descripcion AS rol_bd 
            FROM usuario u
            JOIN persona p ON u.id_persona = p.id_persona
            JOIN tipo_usuario t ON u.id_tipo_usuario = t.id_tipo_usuario
            WHERE u.usu_activo = TRUE
        ");
        $usuarios = $stmt->fetchAll();
        
        foreach ($usuarios as &$u) {
            if ($u['rol_bd'] === 'cliente') $u['rol'] = 'comprador';
            else if ($u['rol_bd'] === 'administrador') $u['rol'] = 'admin';
            else $u['rol'] = $u['rol_bd']; 
        }
        
        echo json_encode($usuarios);
    }

    // 3. Modificación de Roles
    public static function cambiarRol($pdo, $id, $body) {
        $rol = $body['rol'];
        $idTipo = 4; 
        if ($rol === 'admin') $idTipo = 1;
        if ($rol === 'vendedor') $idTipo = 3;

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE usuario SET id_tipo_usuario = ? WHERE id_usuario = ?");
            $stmt->execute([$idTipo, $id]);
            $pdo->commit();
            echo json_encode(["mensaje" => "Rol actualizado exitosamente"]);
        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(["mensaje" => "Error al cambiar rol"]);
        }
    }

    // 4. Eliminación de Usuario (Borrado Lógico)
    public static function eliminarUsuario($pdo, $id) {
        $stmt = $pdo->prepare("UPDATE usuario SET usu_activo = FALSE WHERE id_usuario = ?");
        $stmt->execute([$id]);
        echo json_encode(["mensaje" => "Usuario desactivado del sistema"]);
    }

    // 5. Listado de Propiedades
    public static function getPropiedades($pdo) {
        $stmt = $pdo->query("
            SELECT id, titulo, precio, estado_propiedad AS estado, vendedor_nombre 
            FROM vista_propiedades
        ");
        echo json_encode($stmt->fetchAll());
    }

    // 6. Cambio de Estado de Propiedad
    public static function cambiarEstado($pdo, $id, $body) {
        $estado = strtolower($body['estado']);
        $idEstatus = 1; 
        if ($estado === 'inactiva') $idEstatus = 2;
        if ($estado === 'vendida') $idEstatus = 3;

        $stmt = $pdo->prepare("
            UPDATE propiedad_datos 
            SET id_estatus_propiedad = ? 
            WHERE id_datos = (SELECT id_datos FROM propiedad WHERE id_propiedad = ?)
        ");
        $stmt->execute([$idEstatus, $id]);
        
        echo json_encode(["mensaje" => "Estado de propiedad actualizado"]);
    }
}
?>