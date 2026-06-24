<?php
// backend/controllers/PropiedadController.php
class PropiedadController {
    
    // 1. Listar para el Index
    public static function listar($pdo, $filtros) {
        $sql = "SELECT 
                    p.id_propiedad AS id,
                    'casa' AS tipo, 
                    p.pro_precio AS precio,
                    u.pru_calle AS ubicacion,
                    d.prd_habitaciones AS habitaciones,
                    d.prd_banos AS banos,
                    d.prd_area_m2_terreno AS area_m2,
                    '' AS imagen_url
                FROM propiedad p
                JOIN propiedad_datos d ON p.id_datos = d.id_datos
                JOIN propiedad_ubicacion u ON p.id_ubicacion = u.id_ubicacion
                WHERE d.id_estatus_propiedad = 1";

        $params = [];

        if (!empty($filtros['ubicacion'])) {
            $sql .= " AND u.pru_calle LIKE ?"; 
            $params[] = "%" . $filtros['ubicacion'] . "%";
        }
        if (!empty($filtros['precio_max'])) {
            $sql .= " AND p.pro_precio <= ?"; 
            $params[] = $filtros['precio_max'];
        }

        try {
            $stmt = $pdo->prepare($sql); 
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["mensaje" => "Error SQL: " . $e->getMessage()]);
        }
    }

    // 2. Detalle de propiedad
    public static function obtenerPorId($pdo, $id) {
        $stmt = $pdo->prepare("SELECT * FROM vista_propiedades WHERE id = ?");
        $stmt->execute([$id]);
        $prop = $stmt->fetch();
        
        if ($prop) {
            $prop['area_m2'] = $prop['area_terreno'];
            $prop['vendedor_apellido'] = ''; 
            echo json_encode($prop);
        } else { 
            http_response_code(404); 
            echo json_encode(["mensaje" => "Propiedad no encontrada"]); 
        }
    }

    // 3. Panel de vendedor
    public static function misPropiedades($pdo, $tokenData) {
        if (!$tokenData || $tokenData['rol'] !== 'vendedor') { 
            http_response_code(401); 
            echo json_encode(["mensaje" => "No autorizado"]); 
            return; 
        }
        
        $stmtVen = $pdo->prepare("SELECT id_vendedor FROM vendedor WHERE id_usuario = ?");
        $stmtVen->execute([$tokenData['id']]);
        $vendedor = $stmtVen->fetch();

        if (!$vendedor) {
            echo json_encode([]); return;
        }

        $stmt = $pdo->prepare("
            SELECT id, titulo, ubicacion, precio, estado_propiedad AS estado, imagen_url 
            FROM vista_propiedades 
            WHERE vendedor_correo = (SELECT usu_correo FROM usuario WHERE id_usuario = ?)
        ");
        $stmt->execute([$tokenData['id']]);
        echo json_encode($stmt->fetchAll());
    }

    // 4. Crear nueva propiedad
    public static function crear($pdo, $body, $tokenData) {
        if (!$tokenData || !in_array($tokenData['rol'], ['vendedor', 'admin'])) {
            http_response_code(401); 
            echo json_encode(["mensaje" => "No autorizado"]); 
            return;
        }

        try {
            $pdo->beginTransaction();

            $stmtVen = $pdo->prepare("SELECT id_vendedor FROM vendedor WHERE id_usuario = ?");
            $stmtVen->execute([$tokenData['id']]);
            $vendedor = $stmtVen->fetch();
            if (!$vendedor) throw new Exception("Perfil de vendedor no encontrado.");
            $idVendedor = $vendedor['id_vendedor'];

            $tipoStr = strtolower($body['tipo']);
            $idTipo = 1; 
            if ($tipoStr === 'departamento') $idTipo = 2;
            if ($tipoStr === 'terreno') $idTipo = 3;

            $stmtUbi = $pdo->prepare("INSERT INTO propiedad_ubicacion (pru_estado, pru_municipio, pru_calle) VALUES ('No especificado', 'No especificado', ?)");
            $stmtUbi->execute([$body['ubicacion']]);
            $idUbicacion = $pdo->lastInsertId();

            $stmtDatos = $pdo->prepare("INSERT INTO propiedad_datos (prd_titulo, prd_descripcion, prd_habitaciones, prd_banos, prd_area_m2_terreno, id_estatus_propiedad) VALUES (?, ?, ?, 0, ?, 1)");
            $stmtDatos->execute([
                $body['titulo'], 
                $body['descripcion'] ?? '', 
                $body['habitaciones'], 
                $body['area_m2']
            ]);
            $idDatos = $pdo->lastInsertId();

            $folio = "EA-" . date("Y") . "-" . rand(1000, 9999);

            $stmtProp = $pdo->prepare("INSERT INTO propiedad (id_vendedor, id_datos, id_ubicacion, id_pro_tipo, pro_precio, pro_folio) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtProp->execute([
                $idVendedor, $idDatos, $idUbicacion, $idTipo, $body['precio'], $folio
            ]);

            $pdo->commit();
            http_response_code(201);
            echo json_encode(["mensaje" => "Propiedad creada exitosamente", "folio" => $folio]);

        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500); 
            echo json_encode(["mensaje" => "Error al registrar la propiedad.", "error" => $e->getMessage()]);
        }
    }
}
?>