<?php
// backend/controllers/PropiedadController.php
class PropiedadController {
    
    public static function listar($pdo, $filtros) {
        $sql = "SELECT 
                    id,
                    titulo,
                    tipo,
                    precio,
                    ubicacion,
                    habitaciones,
                    banos,
                    area_m2,
                    imagen_url
                FROM vista_propiedades
                WHERE 1=1"; //Se removio la condicional 

        $params = [];

        if (!empty($filtros['ubicacion'])) {
            $sql .= " AND ubicacion LIKE ?"; 
            $params[] = "%" . $filtros['ubicacion'] . "%";
        }
        if (!empty($filtros['precio_max'])) {
            $sql .= " AND precio <= ?"; 
            $params[] = $filtros['precio_max'];
        }
        if (!empty($filtros['tipo'])) {
            $sql .= " AND tipo = ?"; 
            $params[] = $filtros['tipo'];
        }

        try {
            $stmt = $pdo->prepare($sql); 
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["mensaje" => "Error SQL: " . $e->getMessage()]);
        }
    }


    public static function obtenerPorId($pdo, $id) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM vista_propiedades WHERE id = ?");
            $stmt->execute([$id]);
            $prop = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($prop) {
                echo json_encode($prop);
            } else { 
                http_response_code(404); 
                echo json_encode(["mensaje" => "Propiedad no encontrada"]); 
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["mensaje" => "Error: " . $e->getMessage()]);
        }
    }

    public static function misPropiedades($pdo, $tokenData) {
        if (!$tokenData || $tokenData['rol'] !== 'vendedor') { 
            http_response_code(401); 
            echo json_encode(["mensaje" => "No autorizado"]); 
            return; 
        }
        
        try {
            $stmtVen = $pdo->prepare("SELECT id_vendedor FROM vendedor WHERE id_usuario = ?");
            $stmtVen->execute([$tokenData['id']]);
            $vendedor = $stmtVen->fetch(PDO::FETCH_ASSOC);

            if (!$vendedor) {
                echo json_encode([]); 
                return;
            }

            $stmt = $pdo->prepare("
                SELECT 
                    id, 
                    titulo, 
                    ubicacion, 
                    precio, 
                    estado_propiedad AS estado, 
                    imagen_url 
                FROM vista_propiedades 
                WHERE id_vendedor = ?
            ");
            $stmt->execute([$vendedor['id_vendedor']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["mensaje" => "Error: " . $e->getMessage()]);
        }
    }

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
            $vendedor = $stmtVen->fetch(PDO::FETCH_ASSOC);
            if (!$vendedor) throw new Exception("Perfil de vendedor no encontrado.");
            $idVendedor = $vendedor['id_vendedor'];
            
            $tipoStr = strtolower($body['tipo'] ?? 'casa');
            $idTipo = 1; 
            if ($tipoStr === 'departamento') $idTipo = 2;
            if ($tipoStr === 'terreno') $idTipo = 3;

            // 1. Insertar ubicación
            $stmtUbi = $pdo->prepare("
                INSERT INTO propiedad_ubicacion 
                (pru_estado, pru_municipio, pru_calle) 
                VALUES ('No especificado', 'No especificado', ?)
            ");
            $stmtUbi->execute([$body['ubicacion'] ?? 'Sin ubicación']);
            $idUbicacion = $pdo->lastInsertId();

            // 2. Insertar datos de propiedad
            $stmtDatos = $pdo->prepare("
                INSERT INTO propiedad_datos 
                (prd_titulo, prd_descripcion, prd_habitaciones, prd_banos, prd_area_m2_terreno, id_estatus_propiedad) 
                VALUES (?, ?, ?, 0, ?, 1)
            ");
            $stmtDatos->execute([
                $body['titulo'] ?? 'Sin título', 
                $body['descripcion'] ?? '', 
                $body['habitaciones'] ?? 0, 
                $body['area_m2'] ?? 0
            ]);
            $idDatos = $pdo->lastInsertId();

            // 3. Generar folio único
            $folio = "EA-" . date("Y") . "-" . rand(10000, 99999);

            // 4. Insertar propiedad
            $stmtProp = $pdo->prepare("
                INSERT INTO propiedad 
                (id_vendedor, id_datos, id_ubicacion, id_pro_tipo, pro_precio, pro_folio) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmtProp->execute([
                $idVendedor, 
                $idDatos, 
                $idUbicacion, 
                $idTipo, 
                $body['precio'] ?? 0, 
                $folio
            ]);

            $pdo->commit();
            http_response_code(201);
            echo json_encode([
                "mensaje" => "Propiedad creada exitosamente", 
                "folio" => $folio,
                "id_propiedad" => $pdo->lastInsertId()
            ]);

        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500); 
            echo json_encode([
                "mensaje" => "Error al registrar la propiedad.", 
                "error" => $e->getMessage()
            ]);
        }
    }
}
?>