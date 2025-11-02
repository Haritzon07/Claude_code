<?php
require_once '../includes/config.php';
require_once '../includes/Database.php';
require_once '../includes/funciones.php';

header('Content-Type: application/json');

if (!estaAutenticado()) {
    jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
}

$db = new Database();

$sorteo_id = (int)($_GET['sorteo_id'] ?? 0);
$ultimo_orden = (int)($_GET['ultimo_orden'] ?? 0);

if (!$sorteo_id) {
    jsonResponse(['success' => false, 'message' => 'Sorteo no especificado']);
}

// Obtener nuevas extracciones
$extracciones = $db->fetchAll("
    SELECT numero, letra, orden
    FROM extracciones
    WHERE sorteo_id = :sorteo_id AND orden > :ultimo_orden
    ORDER BY orden ASC
", ['sorteo_id' => $sorteo_id, 'ultimo_orden' => $ultimo_orden]);

// Obtener estado del sorteo
$sorteo = $db->fetchOne("SELECT estado FROM sorteos WHERE id = :id", ['id' => $sorteo_id]);

// Obtener reclamos pendientes (solo para admin)
$reclamos = [];
if (esAdmin()) {
    $reclamos = $db->fetchAll("
        SELECT r.id, r.tipo_premio, u.nombre, c.numero_carton, r.hora
        FROM reclamos r
        JOIN usuarios u ON r.usuario_id = u.id
        JOIN cartones c ON r.carton_id = c.id
        WHERE r.sorteo_id = :sorteo_id AND r.estado = 'pendiente'
        ORDER BY r.hora ASC
    ", ['sorteo_id' => $sorteo_id]);
}

// Verificar si el jugador tiene un reclamo validado
$mi_reclamo = null;
if (!esAdmin()) {
    $mi_reclamo = $db->fetchOne("
        SELECT r.*, g.premio_descripcion
        FROM reclamos r
        LEFT JOIN ganadores g ON r.id = g.reclamo_id
        WHERE r.sorteo_id = :sorteo_id
        AND r.usuario_id = :usuario_id
        AND r.estado = 'validado'
        ORDER BY r.hora DESC
        LIMIT 1
    ", ['sorteo_id' => $sorteo_id, 'usuario_id' => $_SESSION['usuario_id']]);
}

jsonResponse([
    'success' => true,
    'extracciones' => $extracciones,
    'estado_sorteo' => $sorteo['estado'],
    'reclamos' => $reclamos,
    'mi_reclamo' => $mi_reclamo,
    'hay_actualizaciones' => !empty($extracciones)
]);
