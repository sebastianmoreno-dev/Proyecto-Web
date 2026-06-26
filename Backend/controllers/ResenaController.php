<?php
class ResenaController {
    
    public static function listarPorPropiedad($pdo, $idPropiedad) {
        try {
            $stmt = $pdo->prepare("
                SELECT r.res_comentario, r.res_calificacion, r.res_fecha,
                       CONCAT(p.per_nombres, ' ', p.per_apat) AS cliente_nombre
                FROM resenas r
                JOIN cliente c ON r.id_cliente = c.id_cliente
                JOIN usuario u ON c.id_usuario = u.id_usuario
                JOIN persona p ON u.id_persona = p.id_persona
                WHERE r.id_propiedad = ?
                ORDER BY r.res_fecha DESC
            ");
            $stmt->execute([$idPropiedad]);
            echo json_encode($stmt->fetchAll());
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["mensaje" => "Error al obtener reseñas"]);
        }
    }

    public static function crear($pdo, $idPropiedad, $body, $tokenData) {
        if (!$tokenData || $tokenData['rol'] !== 'comprador') {
            http_response_code(403);
            echo json_encode(["mensaje" => "Solo los compradores pueden dejar reseñas."]);
            return;
        }

        try {
            $stmtCli = $pdo->prepare("SELECT id_cliente FROM cliente WHERE id_usuario = ?");
            $stmtCli->execute([$tokenData['id']]);
            $cliente = $stmtCli->fetch();

            if (!$cliente) {
                http_response_code(404);
                echo json_encode(["mensaje" => "Perfil no encontrado."]);
                return;
            }

            $stmt = $pdo->prepare("INSERT INTO resenas (id_cliente, id_propiedad, res_comentario, res_calificacion) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $cliente['id_cliente'], 
                $idPropiedad, 
                $body['comentario'], 
                (int)$body['calificacion']
            ]);
            
            http_response_code(201);
            echo json_encode(["mensaje" => "Reseña publicada."]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["mensaje" => "Error al guardar reseña."]);
        }
    }
}
?>