<?php
// backend/controllers/ChatController.php
//
// Gestor de mensajes (chat). Implementa el ciclo de vida definido en
// DiagramasSecuencia/Vendedor/CicloVidaChat.puml:
//
//   - Activo:     se crea al iniciar una negociacion.
//   - Bloqueado:  el EVENT evt_validar_estados_chat lo marca a los 7 dias
//                 sin cita agendada (pendiente o confirmada).
//   - Extendido:  trigger trg_cita_extiende_chat al confirmar una cita.
//   - Finalizado: el EVENT lo marca a los 15 dias continuos en bloqueado,
//                 o el trigger trg_venta_en_cascada al vender la propiedad.
//
// Este controlador solo expone las operaciones del usuario.
// Las transiciones de estado las maneja la BD.

class ChatController {

    // POST /api/negociaciones  body: { id_propiedad }
    public static function iniciarNegociacion($pdo, $body, $tokenData) {
        if (!$tokenData || $tokenData['rol'] !== 'comprador') {
            http_response_code(403);
            echo json_encode(["mensaje" => "Solo los compradores pueden iniciar una negociacion."]);
            return;
        }
        if (empty($body['id_propiedad'])) {
            http_response_code(400);
            echo json_encode(["mensaje" => "Falta id_propiedad."]);
            return;
        }

        $idUsuario   = (int)$tokenData['id'];
        $idPropiedad = (int)$body['id_propiedad'];

        $stmt = $pdo->prepare("SELECT id_cliente FROM cliente WHERE id_usuario = ?");
        $stmt->execute([$idUsuario]);
        $cliente = $stmt->fetch();
        if (!$cliente) {
            http_response_code(404);
            echo json_encode(["mensaje" => "No se encontro el registro de cliente."]);
            return;
        }
        $idCliente = (int)$cliente['id_cliente'];

        $stmt = $pdo->prepare(
            "SELECT v.id_usuario AS id_usuario_vendedor
               FROM propiedad p
               JOIN vendedor  v ON p.id_vendedor = v.id_vendedor
              WHERE p.id_propiedad = ?"
        );
        $stmt->execute([$idPropiedad]);
        $prop = $stmt->fetch();
        if (!$prop) {
            http_response_code(404);
            echo json_encode(["mensaje" => "Propiedad no encontrada."]);
            return;
        }
        if ((int)$prop['id_usuario_vendedor'] === $idUsuario) {
            http_response_code(400);
            echo json_encode(["mensaje" => "No puedes negociar tu propia propiedad."]);
            return;
        }

        // UNIQUE(id_cliente, id_propiedad): si ya existe la devolvemos en lugar de fallar.
        $stmt = $pdo->prepare(
            "SELECT n.id_negociacion, ch.id_chat
               FROM negociacion n
               JOIN chat ch ON ch.id_negociacion = n.id_negociacion
              WHERE n.id_cliente = ? AND n.id_propiedad = ?"
        );
        $stmt->execute([$idCliente, $idPropiedad]);
        $existente = $stmt->fetch();
        if ($existente) {
            echo json_encode([
                "mensaje"        => "Negociacion existente.",
                "id_negociacion" => (int)$existente['id_negociacion'],
                "id_chat"        => (int)$existente['id_chat'],
                "nueva"          => false
            ]);
            return;
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO negociacion (id_cliente, id_propiedad) VALUES (?, ?)");
            $stmt->execute([$idCliente, $idPropiedad]);
            $idNegociacion = (int)$pdo->lastInsertId();

            // El trigger trg_crear_chat_al_negociar ya creo el chat con estado ACTIVO.
            $stmt = $pdo->prepare("SELECT id_chat FROM chat WHERE id_negociacion = ?");
            $stmt->execute([$idNegociacion]);
            $chat = $stmt->fetch();

            $pdo->commit();

            echo json_encode([
                "mensaje"        => "Negociacion creada.",
                "id_negociacion" => $idNegociacion,
                "id_chat"        => $chat ? (int)$chat['id_chat'] : null,
                "nueva"          => true
            ]);
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            http_response_code(500);
            echo json_encode(["mensaje" => "Error al crear negociacion."]);
        }
    }

    // GET /api/chats
    public static function listar($pdo, $tokenData) {
        if (!$tokenData) {
            http_response_code(401);
            echo json_encode(["mensaje" => "No autorizado."]);
            return;
        }
        $idUsuario = (int)$tokenData['id'];
        $rol       = $tokenData['rol'];

        if ($rol === 'comprador') {
            $sql = "SELECT
                        ch.id_chat,
                        ch.id_estatus_chat,
                        ec.ech_nombre               AS estado,
                        ch.cha_fecha_inicio,
                        ch.cha_fecha_ultimo_mensaje,
                        ch.cha_fecha_bloqueo,
                        ch.cha_motivo_cierre,
                        n.id_negociacion,
                        p.id_propiedad,
                        pd.prd_titulo               AS propiedad_titulo,
                        CONCAT(per.per_nombres, ' ', per.per_apat) AS interlocutor_nombre,
                        u.usu_correo                AS interlocutor_correo,
                        (SELECT COUNT(*) FROM mensaje m
                          WHERE m.id_chat = ch.id_chat
                            AND m.id_remitente != ?
                            AND m.men_leido = FALSE) AS no_leidos
                    FROM chat ch
                    JOIN estatus_chat    ec  ON ch.id_estatus_chat = ec.id_estatus_chat
                    JOIN negociacion     n   ON ch.id_negociacion  = n.id_negociacion
                    JOIN cliente         c   ON n.id_cliente       = c.id_cliente
                    JOIN propiedad       p   ON n.id_propiedad     = p.id_propiedad
                    JOIN propiedad_datos pd  ON p.id_datos         = pd.id_datos
                    JOIN vendedor        v   ON p.id_vendedor      = v.id_vendedor
                    JOIN usuario         u   ON v.id_usuario       = u.id_usuario
                    JOIN persona         per ON u.id_persona       = per.id_persona
                    WHERE c.id_usuario = ?
                    ORDER BY ch.cha_fecha_ultimo_mensaje DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$idUsuario, $idUsuario]);
        } elseif ($rol === 'vendedor') {
            $sql = "SELECT
                        ch.id_chat,
                        ch.id_estatus_chat,
                        ec.ech_nombre               AS estado,
                        ch.cha_fecha_inicio,
                        ch.cha_fecha_ultimo_mensaje,
                        ch.cha_fecha_bloqueo,
                        ch.cha_motivo_cierre,
                        n.id_negociacion,
                        p.id_propiedad,
                        pd.prd_titulo               AS propiedad_titulo,
                        CONCAT(per.per_nombres, ' ', per.per_apat) AS interlocutor_nombre,
                        u.usu_correo                AS interlocutor_correo,
                        (SELECT COUNT(*) FROM mensaje m
                          WHERE m.id_chat = ch.id_chat
                            AND m.id_remitente != ?
                            AND m.men_leido = FALSE) AS no_leidos
                    FROM chat ch
                    JOIN estatus_chat    ec  ON ch.id_estatus_chat = ec.id_estatus_chat
                    JOIN negociacion     n   ON ch.id_negociacion  = n.id_negociacion
                    JOIN propiedad       p   ON n.id_propiedad     = p.id_propiedad
                    JOIN propiedad_datos pd  ON p.id_datos         = pd.id_datos
                    JOIN vendedor        v   ON p.id_vendedor      = v.id_vendedor
                    JOIN cliente         c   ON n.id_cliente       = c.id_cliente
                    JOIN usuario         u   ON c.id_usuario       = u.id_usuario
                    JOIN persona         per ON u.id_persona       = per.id_persona
                    WHERE v.id_usuario = ?
                    ORDER BY ch.cha_fecha_ultimo_mensaje DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$idUsuario, $idUsuario]);
        } else {
            http_response_code(403);
            echo json_encode(["mensaje" => "Rol no autorizado para ver chats."]);
            return;
        }

        echo json_encode($stmt->fetchAll());
    }

    // GET /api/chats/{id}/mensajes
    public static function obtenerMensajes($pdo, $idChat, $tokenData) {
        if (!$tokenData) {
            http_response_code(401);
            echo json_encode(["mensaje" => "No autorizado."]);
            return;
        }
        $idUsuario = (int)$tokenData['id'];
        $idChat    = (int)$idChat;

        if (!self::usuarioPerteneceAlChat($pdo, $idChat, $idUsuario)) {
            http_response_code(403);
            echo json_encode(["mensaje" => "No tienes acceso a este chat."]);
            return;
        }

        $stmt = $pdo->prepare(
            "SELECT ch.id_estatus_chat,
                    ec.ech_nombre AS estado,
                    ch.cha_motivo_cierre,
                    ch.cha_fecha_inicio,
                    ch.cha_fecha_bloqueo
               FROM chat ch
               JOIN estatus_chat ec ON ch.id_estatus_chat = ec.id_estatus_chat
              WHERE ch.id_chat = ?"
        );
        $stmt->execute([$idChat]);
        $info = $stmt->fetch();
        if (!$info) {
            http_response_code(404);
            echo json_encode(["mensaje" => "Chat no encontrado."]);
            return;
        }

        $stmt = $pdo->prepare(
            "SELECT m.id_mensaje,
                    m.id_remitente,
                    m.men_texto,
                    m.men_fecha,
                    m.men_leido,
                    CONCAT(per.per_nombres, ' ', per.per_apat) AS remitente_nombre
               FROM mensaje m
               JOIN usuario u   ON m.id_remitente = u.id_usuario
               JOIN persona per ON u.id_persona   = per.id_persona
              WHERE m.id_chat = ?
              ORDER BY m.men_fecha ASC, m.id_mensaje ASC"
        );
        $stmt->execute([$idChat]);
        $mensajes = $stmt->fetchAll();

        $upd = $pdo->prepare(
            "UPDATE mensaje SET men_leido = TRUE
              WHERE id_chat = ? AND id_remitente != ? AND men_leido = FALSE"
        );
        $upd->execute([$idChat, $idUsuario]);

        $idEstado    = (int)$info['id_estatus_chat'];
        $puedeEnviar = in_array($idEstado, [1, 3], true);

        echo json_encode([
            "id_chat"           => $idChat,
            "id_estatus_chat"   => $idEstado,
            "estado"            => $info['estado'],
            "motivo_cierre"     => $info['cha_motivo_cierre'],
            "fecha_inicio"      => $info['cha_fecha_inicio'],
            "fecha_bloqueo"     => $info['cha_fecha_bloqueo'],
            "puede_enviar"      => $puedeEnviar,
            "id_usuario_actual" => $idUsuario,
            "mensajes"          => $mensajes
        ]);
    }

    // POST /api/chats/{id}/mensajes  body: { texto }
    public static function enviarMensaje($pdo, $idChat, $body, $tokenData) {
        if (!$tokenData) {
            http_response_code(401);
            echo json_encode(["mensaje" => "No autorizado."]);
            return;
        }
        if (empty($body['texto']) || !is_string($body['texto'])) {
            http_response_code(400);
            echo json_encode(["mensaje" => "Mensaje vacio."]);
            return;
        }
        $texto = trim($body['texto']);
        if ($texto === '') {
            http_response_code(400);
            echo json_encode(["mensaje" => "Mensaje vacio."]);
            return;
        }
        if (mb_strlen($texto) > 255) {
            http_response_code(400);
            echo json_encode(["mensaje" => "El mensaje supera los 255 caracteres."]);
            return;
        }

        $idUsuario = (int)$tokenData['id'];
        $idChat    = (int)$idChat;

        if (!self::usuarioPerteneceAlChat($pdo, $idChat, $idUsuario)) {
            http_response_code(403);
            echo json_encode(["mensaje" => "No tienes acceso a este chat."]);
            return;
        }

        $stmt = $pdo->prepare("SELECT id_estatus_chat FROM chat WHERE id_chat = ?");
        $stmt->execute([$idChat]);
        $chat = $stmt->fetch();
        if (!$chat) {
            http_response_code(404);
            echo json_encode(["mensaje" => "Chat no encontrado."]);
            return;
        }
        $estado = (int)$chat['id_estatus_chat'];
        // 1 = activo, 3 = extendido. Solo en estos estados se puede mandar mensaje.
        if (!in_array($estado, [1, 3], true)) {
            $msg = $estado === 2
                ? "Chat bloqueado: agenda una cita para reactivarlo."
                : "Chat finalizado: la negociacion fue cerrada.";
            http_response_code(403);
            echo json_encode(["mensaje" => $msg]);
            return;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO mensaje (id_chat, id_remitente, men_texto) VALUES (?, ?, ?)");
            $stmt->execute([$idChat, $idUsuario, $texto]);
            // El trigger trg_actualizar_ultimo_mensaje actualiza cha_fecha_ultimo_mensaje.
            echo json_encode([
                "mensaje"    => "Mensaje enviado.",
                "id_mensaje" => (int)$pdo->lastInsertId()
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["mensaje" => "Error al enviar mensaje."]);
        }
    }

    // ------------------------------------------------------------------

    private static function usuarioPerteneceAlChat($pdo, $idChat, $idUsuario) {
        $sql = "SELECT 1
                  FROM chat ch
                  JOIN negociacion n ON ch.id_negociacion = n.id_negociacion
                  JOIN cliente     c ON n.id_cliente      = c.id_cliente
                  JOIN propiedad   p ON n.id_propiedad    = p.id_propiedad
                  JOIN vendedor    v ON p.id_vendedor     = v.id_vendedor
                 WHERE ch.id_chat = ?
                   AND (c.id_usuario = ? OR v.id_usuario = ?)
                 LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idChat, $idUsuario, $idUsuario]);
        return (bool)$stmt->fetch();
    }
}
