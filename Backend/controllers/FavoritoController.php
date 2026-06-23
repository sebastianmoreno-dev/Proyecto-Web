<?php
// backend/controllers/FavoritoController.php
class FavoritoController {
    public static function agregar($pdo, $propiedadId, $tokenData) {
        if (!$tokenData) { http_response_code(401); return; }
        try {
            $stmt = $pdo->prepare("INSERT INTO favoritos (usuario_id, propiedad_id) VALUES (?, ?)");
            $stmt->execute([$tokenData['id'], $propiedadId]);
            echo json_encode(["mensaje" => "Añadido a favoritos"]);
        } catch(PDOException $e) { http_response_code(400); }
    }

    public static function eliminar($pdo, $propiedadId, $tokenData) {
        if (!$tokenData) { http_response_code(401); return; }
        $stmt = $pdo->prepare("DELETE FROM favoritos WHERE usuario_id = ? AND propiedad_id = ?");
        $stmt->execute([$tokenData['id'], $propiedadId]);
        echo json_encode(["mensaje" => "Eliminado de favoritos"]);
    }
}