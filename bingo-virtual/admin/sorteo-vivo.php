<?php
require_once '../includes/config.php';
require_once '../includes/Database.php';
require_once '../includes/funciones.php';

if (!estaAutenticado() || !esAdmin()) {
    redirigir('../login.php');
}

$db = new Database();
$sorteo_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Obtener sorteo
$sorteo = $db->fetchOne("SELECT * FROM sorteos WHERE id = :id", ['id' => $sorteo_id]);

if (!$sorteo) {
    redirigir('index.php');
}

// Iniciar sorteo si no está iniciado
if ($sorteo['estado'] === 'programado' && isset($_GET['iniciar'])) {
    $db->query("UPDATE sorteos SET estado = 'en_curso', fecha_inicio = NOW() WHERE id = :id", ['id' => $sorteo_id]);
    $sorteo['estado'] = 'en_curso';
}

// Obtener números ya extraídos
$extracciones = $db->fetchAll("
    SELECT numero, letra, orden FROM extracciones
    WHERE sorteo_id = :sorteo_id
    ORDER BY orden ASC
", ['sorteo_id' => $sorteo_id]);

$numerosExtraidos = array_column($extracciones, 'numero');

// Obtener premios
$premios = json_decode($sorteo['premios_json'], true) ?? [];

// Obtener reclamos pendientes
$reclamos = $db->fetchAll("
    SELECT r.*, u.nombre, c.numero_carton
    FROM reclamos r
    JOIN usuarios u ON r.usuario_id = u.id
    JOIN cartones c ON r.carton_id = c.id
    WHERE r.sorteo_id = :sorteo_id AND r.estado = 'pendiente'
    ORDER BY r.hora ASC
", ['sorteo_id' => $sorteo_id]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sorteo en Vivo - <?php echo htmlspecialchars($sorteo['nombre']); ?></title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/sorteo-vivo.css">
</head>
<body class="sorteo-vivo-page">
    <div class="sorteo-vivo-container">
        <!-- Panel Izquierdo: Control del Sorteo -->
        <div class="panel-izquierdo">
            <div class="sorteo-header">
                <h1><?php echo htmlspecialchars($sorteo['nombre']); ?></h1>
                <span class="badge badge-live">🔴 EN VIVO</span>
            </div>

            <!-- Extractor de Números -->
            <div class="extractor-section">
                <h2>🎱 Urna Virtual</h2>

                <div class="ultimo-numero" id="ultimoNumero">
                    <div class="numero-display">--</div>
                    <div class="letra-display"></div>
                </div>

                <!-- Controles -->
                <div class="controles">
                    <div class="modo-selector">
                        <button class="btn btn-mode" id="modoManualBtn" onclick="cambiarModo('manual')">
                            ✋ Manual
                        </button>
                        <button class="btn btn-mode" id="modoAutoBtn" onclick="cambiarModo('automatico')">
                            🤖 Automático
                        </button>
                    </div>

                    <!-- Controles Manuales -->
                    <div id="controlesManual" class="controles-modo">
                        <div class="input-group">
                            <input type="number" id="numeroManual" min="1" max="75"
                                   placeholder="Número (1-75)" class="form-control">
                            <button class="btn btn-primary" onclick="extraerNumeroManual()">
                                Agregar
                            </button>
                        </div>

                        <button class="btn btn-success btn-lg btn-block" onclick="extraerNumeroAleatorio()">
                            🎲 Sacar Siguiente Número
                        </button>
                    </div>

                    <!-- Controles Automáticos -->
                    <div id="controlesAuto" class="controles-modo" style="display: none;">
                        <div class="form-group">
                            <label>Intervalo (segundos):</label>
                            <input type="number" id="intervaloAuto" value="<?php echo $sorteo['intervalo_segundos'] ?? 5; ?>"
                                   min="1" max="30" class="form-control">
                        </div>

                        <button class="btn btn-success btn-lg btn-block" id="btnIniciarAuto" onclick="iniciarAutomatico()">
                            ▶️ Iniciar Automático
                        </button>

                        <button class="btn btn-danger btn-lg btn-block" id="btnDetenerAuto"
                                onclick="detenerAutomatico()" style="display: none;">
                            ⏸️ Detener
                        </button>
                    </div>

                    <!-- Control de Narrador -->
                    <div class="narrador-control">
                        <label class="switch">
                            <input type="checkbox" id="narradorToggle" checked onchange="toggleNarrador()">
                            <span class="slider"></span>
                        </label>
                        <label for="narradorToggle">🔊 Narrador de Voz</label>
                    </div>
                </div>
            </div>

            <!-- Reclamos Pendientes -->
            <div class="reclamos-section">
                <h2>🙋 Reclamos de BINGO (<span id="reclamosCount"><?php echo count($reclamos); ?></span>)</h2>
                <div id="reclamosList" class="reclamos-list">
                    <?php if (empty($reclamos)): ?>
                        <p class="empty-text">No hay reclamos pendientes</p>
                    <?php else: ?>
                        <?php foreach ($reclamos as $reclamo): ?>
                            <div class="reclamo-item" id="reclamo-<?php echo $reclamo['id']; ?>">
                                <div class="reclamo-info">
                                    <strong><?php echo htmlspecialchars($reclamo['nombre']); ?></strong>
                                    <span>Cartón #<?php echo $reclamo['numero_carton']; ?></span>
                                    <span class="badge"><?php echo $reclamo['tipo_premio']; ?></span>
                                </div>
                                <div class="reclamo-actions">
                                    <button class="btn btn-sm btn-primary" onclick="validarReclamo(<?php echo $reclamo['id']; ?>)">
                                        ✅ Validar
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Botones de Control -->
            <div class="control-buttons">
                <button class="btn btn-warning" onclick="finalizarSorteo()">
                    🏁 Finalizar Sorteo
                </button>
                <a href="index.php" class="btn btn-secondary">
                    🚪 Salir
                </a>
            </div>
        </div>

        <!-- Panel Derecho: Visualización -->
        <div class="panel-derecho">
            <!-- Números Extraídos -->
            <div class="numeros-extraidos-section">
                <h2>Números Extraídos (<?php echo count($numerosExtraidos); ?>/75)</h2>
                <div class="numeros-grid" id="numerosGrid">
                    <?php for ($i = 1; $i <= 75; $i++): ?>
                        <div class="numero-bola <?php echo in_array($i, $numerosExtraidos) ? 'extraido' : ''; ?>"
                             id="bola-<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Bolitas extraídas (últimas 10) -->
            <div class="ultimas-bolitas-section">
                <h3>Últimos Números</h3>
                <div class="bolitas-timeline" id="bolitasTimeline">
                    <?php
                    $ultimasExtracciones = array_slice(array_reverse($extracciones), 0, 10);
                    foreach ($ultimasExtracciones as $ext):
                    ?>
                        <div class="bolita-timeline-item">
                            <div class="bolita-numero letra-<?php echo strtolower($ext['letra']); ?>">
                                <?php echo $ext['letra'] . '-' . $ext['numero']; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Premios -->
            <div class="premios-info">
                <h3>🏆 Premios Activos</h3>
                <div class="premios-list">
                    <?php foreach ($premios as $index => $premio): ?>
                        <div class="premio-item">
                            <span class="premio-numero"><?php echo $index + 1; ?>°</span>
                            <div class="premio-detalles">
                                <strong><?php echo ucfirst(str_replace('_', ' ', $premio['tipo'])); ?></strong>
                                <p><?php echo htmlspecialchars($premio['descripcion']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        const sorteoId = <?php echo $sorteo_id; ?>;
        let narradorActivo = true;
        let modoAutomaticoActivo = false;
        let intervaloAutomatico = null;
        let numerosExtraidos = <?php echo json_encode($numerosExtraidos); ?>;
    </script>
    <script src="../js/sorteo-vivo-admin.js"></script>
</body>
</html>
