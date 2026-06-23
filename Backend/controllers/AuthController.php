<?php
// backend/controllers/AuthController.php
class AuthController {
    public static function registro($pdo, $body) {
        if(empty($body['nombre']) || empty($body['apellido']) || empty($body['correo']) || empty($body['contrasena'])) {
            http_response_code(400); echo json_encode(["mensaje" => "Campos faltantes."]); return;
        }
        try {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, correo, contrasena, rol) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $body['nombre'], $body['apellido'], $body['correo'],
                $body['contrasena'], $body['rol'] ?? 'comprador'
            ]);
            echo json_encode(["mensaje" => "Registro exitoso"]);
        } catch (PDOException $e) {
            http_response_code(400); echo json_encode(["mensaje" => "El correo ya se encuentra registrado."]);
        }
    }

    public static function login($pdo, $body) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = ?");
        $stmt->execute([$body['correo'] ?? '']);
        $user = $stmt->fetch();

        if ($user && ($body['contrasena'] === $user['contrasena'])) {
            $token = JWT::generar(['id' => $user['id'], 'rol' => $user['rol']]);
            echo json_encode([
                "token" => $token,
                "usuario" => [
                    "nombre" => $user['nombre'],
                    "rol" => $user['rol'],
                    "correo" => $user['correo']
                ]
            ]);
        } else {
            http_response_code(401); echo json_encode(["mensaje" => "Credenciales incorrectas."]);
        }
    }
}