<?php
require_once '../includes/config.php';
require_once '../includes/Database.php';
require_once '../includes/funciones.php';

header('Content-Type: application/json');

if (!estaAutenticado()) {
    jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
}

$db = new Database();

$carton_id = (int)($_POST['carton_id'] ?? 0);
$tipo_premio = limpiar($_POST['tipo_premio'] ?? '');
$numeros_marcados = json_decode($_POST['numeros_marcados'] ?? '[]', true);

if (!$carton_id || !$tipo_premio) {
    jsonResponse(['success' => false, 'message' => 'Datos incompletos']);
}

// Obtener cartón y sorteo
$carton = $db->fetchOne("
    SELECT c.*, s.estado as sorteo_estado, s.premios_json
    FROM cartones c
    JOIN sorteos s ON c.sorteo_id = s.id
    WHERE c.id = :id AND c.usuario_id = :usuario_id
", ['id' => $carton_id, 'usuario_id' => $_SESSION['usuario_id']]);

if (!$carton) {
    jsonResponse(['success' => false, 'message' => 'Cartón no válido']);
}

if ($carton['sorteo_estado'] !== 'en_curso') {
    jsonResponse(['success' => false, 'message' => 'El sorteo no está en curso']);
}

// Verificar que el jugador no haya cantado BINGO ya
$reclamoExistente = $db->fetchOne("
    SELECT id FROM reclamos
    WHERE carton_id = :carton_id AND tipo_premio = :tipo_premio
", ['carton_id' => $carton_id, 'tipo_premio' => $tipo_premio]);

if ($reclamoExistente) {
    jsonResponse(['success' => false, 'message' => 'Ya has cantado BINGO para este premio']);
}

// Obtener números extraídos
$extracciones = $db->fetchAll("
    SELECT numero FROM extracciones WHERE sorteo_id = :sorteo_id
", ['sorteo_id' => $carton['sorteo_id']]);
$numerosExtraidos = array_column($extracciones, 'numero');

// Validar si el cartón cumple con el tipo de premio
$matriz = json_decode($carton['matriz_json'], true);
$esValido = validarFigura($matriz, $numerosExtraidos, $tipo_premio);

try {
    // Registrar reclamo
    $sql = "INSERT INTO reclamos (sorteo_id, usuario_id, carton_id, tipo_premio, valido, estado, numeros_marcados_json)
            VALUES (:sorteo_id, :usuario_id, :carton_id, :tipo_premio, :valido, :estado, :numeros_marcados)";

    $db->query($sql, [
        'sorteo_id' => $carton['sorteo_id'],
        'usuario_id' => $_SESSION['usuario_id'],
        'carton_id' => $carton_id,
        'tipo_premio' => $tipo_premio,
        'valido' => $esValido ? 1 : 0,
        'estado' => 'pendiente',
        'numeros_marcados' => json_encode($numeros_marcados)
    ]);

    $reclamo_id = $db->lastInsertId();

    // Si es válido, registrar como ganador automáticamente
    if ($esValido) {
        // Obtener descripción del premio
        $premios = json_decode($carton['premios_json'], true);
        $premio_descripcion = '';
        foreach ($premios as $premio) {
            if ($premio['tipo'] === $tipo_premio) {
                $premio_descripcion = $premio['descripcion'];
                break;
            }
        }

        // Verificar cuántos ganadores hay para este tipo de premio
        $orden = $db->fetchOne("
            SELECT COUNT(*) + 1 as orden FROM ganadores
            WHERE sorteo_id = :sorteo_id AND tipo_premio = :tipo_premio
        ", ['sorteo_id' => $carton['sorteo_id'], 'tipo_premio' => $tipo_premio])['orden'];

        // Registrar ganador
        $sqlGanador = "INSERT INTO ganadores (sorteo_id, usuario_id, carton_id, reclamo_id, tipo_premio, premio_descripcion, orden)
                       VALUES (:sorteo_id, :usuario_id, :carton_id, :reclamo_id, :tipo_premio, :premio_descripcion, :orden)";

        $db->query($sqlGanador, [
            'sorteo_id' => $carton['sorteo_id'],
            'usuario_id' => $_SESSION['usuario_id'],
            'carton_id' => $carton_id,
            'reclamo_id' => $reclamo_id,
            'tipo_premio' => $tipo_premio,
            'premio_descripcion' => $premio_descripcion,
            'orden' => $orden
        ]);

        // Actualizar reclamo como validado
        $db->query("UPDATE reclamos SET estado = 'validado' WHERE id = :id", ['id' => $reclamo_id]);

        // Actualizar cartón como ganador
        $db->query("UPDATE cartones SET estado = 'ganador' WHERE id = :id", ['id' => $carton_id]);

        jsonResponse([
            'success' => true,
            'valido' => true,
            'message' => '¡FELICITACIONES! ¡HAS GANADO!',
            'premio' => $premio_descripcion,
            'mostrar_confetti' => true
        ]);
    } else {
        jsonResponse([
            'success' => true,
            'valido' => false,
            'message' => 'Tu reclamo está pendiente de validación por el administrador'
        ]);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Error al registrar reclamo: ' . $e->getMessage()]);
}
