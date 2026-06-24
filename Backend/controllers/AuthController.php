<?php
// backend/controllers/AuthController.php
class AuthController {
    
    public static function registro($pdo, $body) {
        // Validar campos obligatorios ampliados
        if(empty($body['nombre']) || empty($body['apat']) || empty($body['amat']) || 
           empty($body['curp']) || empty($body['fechaNac']) || 
           empty($body['correo']) || empty($body['contrasena'])) {
            http_response_code(400); 
            echo json_encode(["mensaje" => "Campos faltantes."]); 
            return;
        }

        try {
            // Iniciar transacción (Si ocurre un error en alguna tabla, se deshace todo)
            $pdo->beginTransaction();

            // 1. Insertar datos físicos en la tabla `persona` respetando la estructura de tu diccionario
            $stmtPersona = $pdo->prepare("INSERT INTO persona (per_nombres, per_apat, per_amat, per_curp, per_fecha_nacimiento) VALUES (?, ?, ?, ?, ?)");
            $stmtPersona->execute([
                $body['nombre'], 
                $body['apat'], 
                $body['amat'], 
                $body['curp'], 
                $body['fechaNac']
            ]);
            $idPersona = $pdo->lastInsertId();

            // 2. Mapear el texto del frontend al ID del catálogo tipo_usuario de la base de datos
            $rolFrontend = $body['rol'] ?? 'comprador';
            $idTipoUsuario = 4; // Por defecto 4 = 'cliente' (comprador)
            if ($rolFrontend === 'vendedor') $idTipoUsuario = 3; // 3 = 'vendedor'
            if ($rolFrontend === 'admin') $idTipoUsuario = 1;    // 1 = 'administrador'

            // 3. Hashear la contraseña por seguridad
            $hashPassword = password_hash($body['contrasena'], PASSWORD_BCRYPT);

            // 4. Insertar las credenciales en la tabla `usuario`
            $stmtUsuario = $pdo->prepare("INSERT INTO usuario (usu_correo, usu_password, usu_activo, id_persona, id_tipo_usuario) VALUES (?, ?, TRUE, ?, ?)");
            $stmtUsuario->execute([$body['correo'], $hashPassword, $idPersona, $idTipoUsuario]);
            $idUsuario = $pdo->lastInsertId();

            // 5. Insertar en la tabla de rol específico correspondiente garantizando restricciones NOT NULL
            if ($idTipoUsuario == 4) {
                // Iniciar cliente con un saldo base para que el INSERT no truene
                $pdo->prepare("INSERT INTO cliente (id_usuario, cli_dinero) VALUES (?, 0.00)")->execute([$idUsuario]);
            } else if ($idTipoUsuario == 3) {
                $pdo->prepare("INSERT INTO vendedor (id_usuario) VALUES (?)")->execute([$idUsuario]);
            } else if ($idTipoUsuario == 1) {
                $pdo->prepare("INSERT INTO administrador (id_usuario) VALUES (?)")->execute([$idUsuario]);
            }

            // Confirmar transacción
            $pdo->commit();
            http_response_code(201);
            echo json_encode(["mensaje" => "Registro exitoso"]);

        } catch (PDOException $e) {
            $pdo->rollBack();
            http_response_code(500); 
            // Quitamos la máscara: que MariaDB nos diga exactamente qué columna o llave falló
            echo json_encode([
                "mensaje" => "Error de Integridad (Código " . $e->getCode() . ")",
                "error_real" => $e->getMessage()
            ]);
        }
    }

    public static function login($pdo, $body) {
        // Consultar al usuario cruzando las tablas normalizadas
        $stmt = $pdo->prepare("
            SELECT 
                u.id_usuario AS id, 
                u.usu_password AS contrasena, 
                u.usu_correo AS correo, 
                u.usu_activo,
                t.tipo_descripcion AS rol_bd, 
                p.per_nombres AS nombre 
            FROM usuario u
            JOIN persona p ON u.id_persona = p.id_persona
            JOIN tipo_usuario t ON u.id_tipo_usuario = t.id_tipo_usuario
            WHERE u.usu_correo = ?
        ");
        $stmt->execute([$body['correo'] ?? '']);
        $user = $stmt->fetch();

        // Verificar que exista y que la contraseña ingresada coincida con el Hash guardado
        if ($user && password_verify($body['contrasena'], $user['contrasena'])) {
            
            // Validar soft-delete (Borrado lógico)
            if (!$user['usu_activo']) {
                http_response_code(403); 
                echo json_encode(["mensaje" => "Esta cuenta ha sido desactivada."]);
                return;
            }

            // Mapear el rol de la BD al texto que espera tu Frontend
            $rolFrontend = $user['rol_bd'];
            if ($rolFrontend === 'cliente') $rolFrontend = 'comprador';
            if ($rolFrontend === 'administrador') $rolFrontend = 'admin';

            // Generar Token JWT
            $token = JWT::generar(['id' => $user['id'], 'rol' => $rolFrontend]);
            
            echo json_encode([
                "token" => $token,
                "usuario" => [
                    "nombre" => $user['nombre'],
                    "rol" => $rolFrontend,
                    "correo" => $user['correo']
                ]
            ]);
        } else {
            http_response_code(401); 
            echo json_encode(["mensaje" => "Credenciales incorrectas."]);
        }
    }
}
?>