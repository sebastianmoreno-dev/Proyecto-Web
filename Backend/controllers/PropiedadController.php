<?php
// backend/controllers/PropiedadController.php
class PropiedadController {
    public static function listar($pdo, $filtros) {
        $sql = "SELECT * FROM propiedades WHERE estado = 'activa'";
        $params = [];

        if (!empty($filtros['ubicacion'])) {
            $sql .= " AND ubicacion LIKE ?"; $params[] = "%" . $filtros['ubicacion'] . "%";
        }
        if (!empty($filtros['tipo'])) {
            $sql .= " AND tipo = ?"; $params[] = $filtros['tipo'];
        }
        if (!empty($filtros['precio_max'])) {
            $sql .= " AND precio <= ?"; $params[] = $filtros['precio_max'];
        }

        $stmt = $pdo->prepare($sql); $stmt->execute($params);
        echo json_encode($stmt->fetchAll());
    }

    public static function obtenerPorId($pdo, $id) {
        $stmt = $pdo->prepare("SELECT p.*, u.nombre AS vendedor_nombre, u.apellido AS vendedor_apellido 
                               FROM propiedades p JOIN usuarios u ON p.vendedor_id = u.id WHERE p.id = ?");
        $stmt->execute([$id]);
        $prop = $stmt->fetch();
        if ($prop) echo json_encode($prop);
        else { http_response_code(404); echo json_encode(["mensaje" => "No encontrado"]); }
    }

    public static function misPropiedades($pdo, $tokenData) {
        if (!$tokenData) { http_response_code(401); echo json_encode(["mensaje" => "No autorizado"]); return; }
        $stmt = $pdo->prepare("SELECT * FROM propiedades WHERE vendedor_id = ?");
        $stmt->execute([$tokenData['id']]);
        echo json_encode($stmt->fetchAll());
    }

    public static function crear($pdo, $body, $tokenData) {
        if (!$tokenData || !in_array($tokenData['rol'], ['vendedor', 'admin'])) {
            http_response_code(401); echo json_encode(["mensaje" => "No autorizado"]); return;
        }
        $stmt = $pdo->prepare("INSERT INTO propiedades (vendedor_id, titulo, tipo, precio, area_m2, habitaciones, descripcion, ubicacion, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'activa')");
        $stmt->execute([
            $tokenData['id'], $body['titulo'], $body['tipo'], $body['precio'],
            $body['area_m2'], $body['habitaciones'], $body['descripcion'] ?? '', $body['ubicacion']
        ]);
        echo json_encode(["mensaje" => "Propiedad creada exitosamente"]);
    }
}