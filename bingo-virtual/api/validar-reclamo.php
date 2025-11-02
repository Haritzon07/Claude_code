<?php
require_once '../includes/config.php';
require_once '../includes/Database.php';
require_once '../includes/funciones.php';

header('Content-Type: application/json');

if (!estaAutenticado() || !esAdmin()) {
    jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
}

$db = new Database();

$reclamo_id = (int)($_POST['reclamo_id'] ?? 0);

if (!$reclamo_id) {
    jsonResponse(['success' => false, 'message' => 'Reclamo no especificado']);
}

// Obtener reclamo
$reclamo = $db->fetchOne("
    SELECT r.*, c.matriz_json, s.premios_json
    FROM reclamos r
    JOIN cartones c ON r.carton_id = c.id
    JOIN sorteos s ON r.sorteo_id = s.id
    WHERE r.id = :id
", ['id' => $reclamo_id]);

if (!$reclamo) {
    jsonResponse(['success' => false, 'message' => 'Reclamo no encontrado']);
}

// Obtener números extraídos
$extracciones = $db->fetchAll("
    SELECT numero FROM extracciones WHERE sorteo_id = :sorteo_id
", ['sorteo_id' => $reclamo['sorteo_id']]);
$numerosExtraidos = array_column($extracciones, 'numero');

// Validar figura
$matriz = json_decode($reclamo['matriz_json'], true);
$esValido = validarFigura($matriz, $numerosExtraidos, $reclamo['tipo_premio']);

try {
    if ($esValido) {
        // Obtener descripción del premio
        $premios = json_decode($reclamo['premios_json'], true);
        $premio_descripcion = '';
        foreach ($premios as $premio) {
            if ($premio['tipo'] === $reclamo['tipo_premio']) {
                $premio_descripcion = $premio['descripcion'];
                break;
            }
        }

        // Verificar orden del ganador
        $orden = $db->fetchOne("
            SELECT COUNT(*) + 1 as orden FROM ganadores
            WHERE sorteo_id = :sorteo_id AND tipo_premio = :tipo_premio
        ", ['sorteo_id' => $reclamo['sorteo_id'], 'tipo_premio' => $reclamo['tipo_premio']])['orden'];

        $db->beginTransaction();

        // Registrar ganador
        $sqlGanador = "INSERT INTO ganadores (sorteo_id, usuario_id, carton_id, reclamo_id, tipo_premio, premio_descripcion, orden)
                       VALUES (:sorteo_id, :usuario_id, :carton_id, :reclamo_id, :tipo_premio, :premio_descripcion, :orden)";

        $db->query($sqlGanador, [
            'sorteo_id' => $reclamo['sorteo_id'],
            'usuario_id' => $reclamo['usuario_id'],
            'carton_id' => $reclamo['carton_id'],
            'reclamo_id' => $reclamo_id,
            'tipo_premio' => $reclamo['tipo_premio'],
            'premio_descripcion' => $premio_descripcion,
            'orden' => $orden
        ]);

        // Actualizar reclamo
        $db->query("UPDATE reclamos SET estado = 'validado', valido = 1 WHERE id = :id", ['id' => $reclamo_id]);

        // Actualizar cartón
        $db->query("UPDATE cartones SET estado = 'ganador' WHERE id = :id", ['id' => $reclamo['carton_id']]);

        $db->commit();

        jsonResponse([
            'success' => true,
            'valido' => true,
            'message' => '¡Reclamo validado! El jugador ha ganado.'
        ]);
    } else {
        // Rechazar reclamo
        $db->query("UPDATE reclamos SET estado = 'rechazado', valido = 0, mensaje = 'El cartón no cumple con la figura requerida'
                    WHERE id = :id", ['id' => $reclamo_id]);

        jsonResponse([
            'success' => true,
            'valido' => false,
            'message' => 'Reclamo rechazado. El cartón no cumple con la figura.'
        ]);
    }
} catch (Exception $e) {
    if ($db->getConnection()->inTransaction()) {
        $db->rollback();
    }
    jsonResponse(['success' => false, 'message' => 'Error al validar reclamo: ' . $e->getMessage()]);
}
