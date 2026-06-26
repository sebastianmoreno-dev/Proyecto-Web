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
                    area_terreno AS area_m2,
                    descripcion,
                    imagen_url
                FROM vista_propiedades
                WHERE estado_propiedad = 'activa'"; 

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
            // 1. Buscamos los datos principales de la propiedad
            $stmt = $pdo->prepare("SELECT * FROM vista_propiedades WHERE id = ?");
            $stmt->execute([$id]);
            $prop = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($prop) {
                // 2. Buscamos TODAS las fotos de esta propiedad
                // Ordenamos para que la principal (pim_es_principal = 1) salga primero
                $stmtImg = $pdo->prepare("SELECT pim_url, pim_es_principal FROM propiedad_imagenes WHERE id_propiedad = ? ORDER BY pim_es_principal DESC");
                $stmtImg->execute([$id]);
                $imagenes = $stmtImg->fetchAll(PDO::FETCH_ASSOC);

                // 3. Empaquetamos la lista de imágenes dentro de la respuesta de la propiedad
                $prop['galeria'] = $imagenes;

                // Enviamos el paquete completo al Frontend
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
        // Agregamos 'agente' por si acaso usas ese rol también
        if (!$tokenData || !in_array($tokenData['rol'], ['vendedor', 'admin', 'agente'])) { 
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
        // Permitimos vendedor, admin y agente
        if (!$tokenData || !in_array($tokenData['rol'], ['vendedor', 'admin', 'agente'])) {
            http_response_code(401); 
            echo json_encode(["mensaje" => "No autorizado"]); 
            return;
        }

        try {
            $pdo->beginTransaction();

            // --- ESTA ES LA CLAVE: Leer de FormData ($_POST) ---
            $datos = !empty($_POST) ? $_POST : $body;

            $stmtVen = $pdo->prepare("SELECT id_vendedor FROM vendedor WHERE id_usuario = ?");
            $stmtVen->execute([$tokenData['id']]);
            $vendedor = $stmtVen->fetch(PDO::FETCH_ASSOC);
            if (!$vendedor) throw new Exception("Perfil de vendedor no encontrado.");
            $idVendedor = $vendedor['id_vendedor'];
            
            $tipoStr = strtolower($datos['tipo'] ?? 'casa');
            $idTipo = 1; 
            if ($tipoStr === 'departamento') $idTipo = 2;
            if ($tipoStr === 'terreno') $idTipo = 3;

            // 1. Insertar ubicación
            $stmtUbi = $pdo->prepare("
                INSERT INTO propiedad_ubicacion 
                (pru_estado, pru_municipio, pru_calle) 
                VALUES ('No especificado', 'No especificado', ?)
            ");
            $stmtUbi->execute([$datos['ubicacion'] ?? 'Sin ubicación']);
            $idUbicacion = $pdo->lastInsertId();

            // 2. Insertar datos de propiedad
            $stmtDatos = $pdo->prepare("
                INSERT INTO propiedad_datos 
                (prd_titulo, prd_descripcion, prd_habitaciones, prd_banos, prd_area_m2_terreno, id_estatus_propiedad) 
                VALUES (?, ?, ?, 0, ?, 1)
            ");
            $stmtDatos->execute([
                $datos['titulo'] ?? 'Sin título', 
                $datos['descripcion'] ?? '', 
                $datos['habitaciones'] ?? 0, 
                $datos['area_m2'] ?? 0
            ]);
            $idDatos = $pdo->lastInsertId();

            // 3. Generar folio único
            $folio = "EA-" . date("Y") . "-" . rand(10000, 99999);

            // 4. Insertar propiedad (Ya tomará el precio correcto del FormData)
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
                $datos['precio'] ?? 0, 
                $folio
            ]);
            
            // Obtenemos el ID de la propiedad que acabamos de crear
            $idPropiedad = $pdo->lastInsertId();

            // 5. PROCESAMIENTO DE IMÁGENES
                // 5. PROCESAMIENTO DE IMÁGENES
if (isset($_FILES['imagenes'])) {
    $totalImagenes = count($_FILES['imagenes']['name']);
    
    // MAGIA AQUÍ: dirname(__DIR__, 2) retrocede 2 carpetas automáticamente (sale de controllers y de Backend)
    // y nos deja exactamente en la raíz de tu Proyecto-Web. Luego solo le sumamos el resto del camino.
    $rutaBase = dirname(__DIR__, 2); 
    $directorio = $rutaBase . "/frontend/img/";
    
    for ($i = 0; $i < $totalImagenes; $i++) {
        $tmpName = $_FILES['imagenes']['tmp_name'][$i];
        
        if ($tmpName != "") {
            $nombreArchivo = uniqid() . "-" . basename($_FILES['imagenes']['name'][$i]);
            $rutaDestino = $directorio . $nombreArchivo;
            
            // INTENTAR MOVER
            if (move_uploaded_file($tmpName, $rutaDestino)) {
                // ÉXITO
                $sqlImg = "INSERT INTO propiedad_imagenes (id_propiedad, pim_url, pim_es_principal) VALUES (?, ?, ?)";
                $stmtImg = $pdo->prepare($sqlImg);
                $stmtImg->execute([$idPropiedad, $nombreArchivo, ($i === 0 ? 1 : 0)]);
            } else {
                // FALLO (Solo pasará si la carpeta img no tiene permisos 777)
                die("Error de permisos: PHP encontró la carpeta pero no lo dejan escribir. Ponle 777 a frontend/img.");
            }
        }
    }
}

            

            $pdo->commit();
            http_response_code(201);
            echo json_encode([
                "mensaje" => "Propiedad creada exitosamente", 
                "folio" => $folio,
                "id_propiedad" => $idPropiedad
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