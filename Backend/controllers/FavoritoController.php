<?php
// backend/controllers/FavoritoController.php
class FavoritoController {
    
    // 1. Agregar a Favoritos
    public static function agregar($pdo, $propiedadId, $tokenData) {
        // Solo los compradores (clientes en BD) pueden guardar favoritos
        if (!$tokenData || $tokenData['rol'] !== 'comprador') { 
            http_response_code(401); 
            echo json_encode(["mensaje" => "Solo los compradores pueden realizar esta acción."]);
            return; 
        }
        
        try {
            // Obtener el id_cliente físico asociado a esta cuenta de usuario
            $stmtCli = $pdo->prepare("SELECT id_cliente FROM cliente WHERE id_usuario = ?");
            $stmtCli->execute([$tokenData['id']]);
            $cliente = $stmtCli->fetch();
            
            if ($cliente) {
                // Usamos INSERT IGNORE por si el usuario presiona el botón dos veces rápido
                $stmt = $pdo->prepare("INSERT IGNORE INTO favorito (id_cliente, id_propiedad) VALUES (?, ?)");
                $stmt->execute([$cliente['id_cliente'], $propiedadId]);
                echo json_encode(["mensaje" => "Añadido a favoritos"]);
            } else {
                http_response_code(404);
                echo json_encode(["mensaje" => "Perfil de cliente no encontrado."]);
            }
        } catch(PDOException $e) { 
            http_response_code(500); 
            echo json_encode(["mensaje" => "Error al guardar el favorito.", "error" => $e->getMessage()]);
        }
    }

    // 2. Eliminar de Favoritos
    public static function eliminar($pdo, $propiedadId, $tokenData) {
        if (!$tokenData || $tokenData['rol'] !== 'comprador') { 
            http_response_code(401); 
            return; 
        }

        try {
            // Localizar al cliente
            $stmtCli = $pdo->prepare("SELECT id_cliente FROM cliente WHERE id_usuario = ?");
            $stmtCli->execute([$tokenData['id']]);
            $cliente = $stmtCli->fetch();

            if ($cliente) {
                // Eliminar el registro cruzando el id_cliente y el id_propiedad
                $stmt = $pdo->prepare("DELETE FROM favorito WHERE id_cliente = ? AND id_propiedad = ?");
                $stmt->execute([$cliente['id_cliente'], $propiedadId]);
                echo json_encode(["mensaje" => "Eliminado de favoritos"]);
            }
        } catch(PDOException $e) { 
            http_response_code(500); 
        }
    }
}
?>