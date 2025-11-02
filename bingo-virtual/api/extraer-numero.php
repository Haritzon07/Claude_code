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

$sorteo_id = (int)($_POST['sorteo_id'] ?? 0);
$numero = isset($_POST['numero']) ? (int)$_POST['numero'] : null;

if (!$sorteo_id) {
    jsonResponse(['success' => false, 'message' => 'Sorteo no especificado']);
}

// Verificar que el sorteo esté en curso
$sorteo = $db->fetchOne("SELECT * FROM sorteos WHERE id = :id AND estado = 'en_curso'", ['id' => $sorteo_id]);

if (!$sorteo) {
    jsonResponse(['success' => false, 'message' => 'Sorteo no válido o no está en curso']);
}

// Obtener números ya extraídos
$numerosExtraidos = $db->fetchAll("SELECT numero FROM extracciones WHERE sorteo_id = :sorteo_id", ['sorteo_id' => $sorteo_id]);
$numerosExtraidos = array_column($numerosExtraidos, 'numero');

// Si no se especifica número, generar uno aleatorio
if ($numero === null) {
    $numerosDisponibles = array_diff(range(1, 75), $numerosExtraidos);

    if (empty($numerosDisponibles)) {
        jsonResponse(['success' => false, 'message' => 'Ya se extrajeron todos los números']);
    }

    $numero = $numerosDisponibles[array_rand($numerosDisponibles)];
} else {
    // Validar número manual
    if ($numero < 1 || $numero > 75) {
        jsonResponse(['success' => false, 'message' => 'Número inválido. Debe ser entre 1 y 75']);
    }

    if (in_array($numero, $numerosExtraidos)) {
        jsonResponse(['success' => false, 'message' => 'Este número ya fue extraído']);
    }
}

// Obtener letra según el número
$letra = obtenerLetraBingo($numero);

// Calcular orden
$orden = count($numerosExtraidos) + 1;

try {
    // Insertar extracción
    $sql = "INSERT INTO extracciones (sorteo_id, numero, letra, orden) VALUES (:sorteo_id, :numero, :letra, :orden)";
    $db->query($sql, [
        'sorteo_id' => $sorteo_id,
        'numero' => $numero,
        'letra' => $letra,
        'orden' => $orden
    ]);

    jsonResponse([
        'success' => true,
        'numero' => $numero,
        'letra' => $letra,
        'orden' => $orden,
        'total_extraidos' => $orden,
        'mensaje' => $letra . '-' . $numero
    ]);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Error al registrar extracción: ' . $e->getMessage()]);
}
