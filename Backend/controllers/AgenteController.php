<?php
// backend/controllers/AgenteController.php
class AgenteController {

    // 1. Obtener Citas del Agente
    public static function listarCitas($pdo, $tokenData) {
        try {
            $stmtAgente = $pdo->prepare("SELECT id_agente FROM agente WHERE id_usuario = ?");
            $stmtAgente->execute([$tokenData['id']]);
            $agente = $stmtAgente->fetch();

            if (!$agente) {
                http_response_code(404);
                echo json_encode(["mensaje" => "Perfil de agente no encontrado."]);
                return;
            }

            $sql = "
                SELECT 
                    c.id_cita, 
                    c.cit_fecha, 
                    c.cit_hora, 
                    ec.eci_nombre as estado, 
                    c.id_estatus_cita,
                    pd.prd_titulo, 
                    p.pro_precio, 
                    n.id_negociacion
                FROM cita c
                JOIN estatus_cita ec ON c.id_estatus_cita = ec.id_estatus_cita
                JOIN negociacion n ON c.id_negociacion = n.id_negociacion
                JOIN propiedad p ON n.id_propiedad = p.id_propiedad
                JOIN propiedad_datos pd ON p.id_datos = pd.id_datos
                WHERE c.id_agente = ?
                ORDER BY c.cit_fecha DESC, c.cit_hora DESC
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$agente['id_agente']]);
            
            echo json_encode($stmt->fetchAll());
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["mensaje" => "Error al obtener las citas.", "error" => $e->getMessage()]);
        }
    }

    // 2. Modificar Estado de Cita
    public static function actualizarEstadoCita($pdo, $idCita, $body, $tokenData) {
        $idEstadoNuevo = (int)($body['id_estado'] ?? 0);
        if (!in_array($idEstadoNuevo, [2, 3, 4])) {
            http_response_code(400);
            echo json_encode(["mensaje" => "Estado no válido."]);
            return;
        }

        try {
            $stmt = $pdo->prepare("UPDATE cita SET id_estatus_cita = ? WHERE id_cita = ? AND id_agente = (SELECT id_agente FROM agente WHERE id_usuario = ?)");
            $stmt->execute([$idEstadoNuevo, $idCita, $tokenData['id']]);
            echo json_encode(["mensaje" => "Estado actualizado correctamente."]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["mensaje" => "Error al actualizar la cita.", "error" => $e->getMessage()]);
        }
    }

    // 3. Registrar Venta
    public static function registrarVenta($pdo, $body, $tokenData) {
        if (empty($body['id_negociacion']) || empty($body['precio_final'])) {
            http_response_code(400);
            echo json_encode(["mensaje" => "Faltan datos de la negociación o el precio."]);
            return;
        }

        try {
            $pdo->beginTransaction();

            $stmtAgente = $pdo->prepare("SELECT id_agente FROM agente WHERE id_usuario = ?");
            $stmtAgente->execute([$tokenData['id']]);
            $agente = $stmtAgente->fetch();
            $idAgente = $agente['id_agente'];

            $stmtVenta = $pdo->prepare("
                INSERT INTO operacion_venta (id_negociacion, id_vendedor, id_agente, opv_precio_final) 
                SELECT n.id_negociacion, p.id_vendedor, ?, ? 
                FROM negociacion n 
                JOIN propiedad p ON n.id_propiedad = p.id_propiedad
                WHERE n.id_negociacion = ?
            ");
            $stmtVenta->execute([$idAgente, $body['precio_final'], $body['id_negociacion']]);

            $stmtProp = $pdo->prepare("
                UPDATE propiedad_datos 
                SET id_estatus_propiedad = 3 
                WHERE id_datos = (
                    SELECT p.id_datos FROM propiedad p JOIN negociacion n ON p.id_propiedad = n.id_propiedad WHERE n.id_negociacion = ?
                )
            ");
            $stmtProp->execute([$body['id_negociacion']]);

            $pdo->commit();
            echo json_encode(["mensaje" => "Venta registrada con éxito. Chat cerrado."]);
        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(["mensaje" => "Error al registrar la venta.", "error" => $e->getMessage()]);
        }
    }

    // 4. Obtener Historial de Ventas del Agente (CORREGIDO: Ahora está dentro de la clase)
    public static function listarVentas($pdo, $tokenData) {
        try {
            $stmt = $pdo->prepare("
                SELECT 
                    ov.id_operacion,
                    ov.opv_precio_final,
                    ov.opv_fecha_transaccion,
                    pd.prd_titulo,
                    p.pro_folio
                FROM operacion_venta ov
                JOIN negociacion n ON ov.id_negociacion = n.id_negociacion
                JOIN propiedad p ON n.id_propiedad = p.id_propiedad
                JOIN propiedad_datos pd ON p.id_datos = pd.id_datos
                WHERE ov.id_agente = (SELECT id_agente FROM agente WHERE id_usuario = ?)
                ORDER BY ov.opv_fecha_transaccion DESC
            ");
            $stmt->execute([$tokenData['id']]);
            echo json_encode($stmt->fetchAll());
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["mensaje" => "Error al obtener historial de ventas.", "error" => $e->getMessage()]);
        }
    }
}
?>