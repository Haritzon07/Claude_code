<?php
require_once '../includes/config.php';
require_once '../includes/Database.php';
require_once '../includes/funciones.php';

if (!estaAutenticado()) {
    redirigir('../login.php');
}

$db = new Database();
$carton_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Obtener cartón
$carton = $db->fetchOne("
    SELECT c.*, s.*, c.id as carton_id, s.id as sorteo_id, s.nombre as sorteo_nombre
    FROM cartones c
    JOIN sorteos s ON c.sorteo_id = s.id
    WHERE c.id = :carton_id AND c.usuario_id = :usuario_id
", ['carton_id' => $carton_id, 'usuario_id' => $_SESSION['usuario_id']]);

if (!$carton || $carton['estado'] !== 'pagado') {
    redirigir('index.php');
}

if ($carton['estado_sorteo'] !== 'en_curso') {
    $mensaje = 'Este sorteo aún no ha iniciado o ya finalizó.';
}

// Obtener matriz del cartón
$matriz = json_decode($carton['matriz_json'], true);

// Obtener números extraídos
$extracciones = $db->fetchAll("
    SELECT numero, letra, orden FROM extracciones
    WHERE sorteo_id = :sorteo_id
    ORDER BY orden ASC
", ['sorteo_id' => $carton['sorteo_id']]);

$numerosExtraidos = array_column($extracciones, 'numero');

// Obtener premios
$premios = json_decode($carton['premios_json'], true) ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Jugando! - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/jugador.css">
    <link rel="stylesheet" href="../css/juego.css">
</head>
<body class="juego-page">
    <!-- Canvas para confetti -->
    <canvas id="confetti-canvas"></canvas>

    <div class="juego-container">
        <!-- Panel Superior: Info y Controles -->
        <div class="juego-header">
            <div class="sorteo-info">
                <h1><?php echo htmlspecialchars($carton['sorteo_nombre']); ?></h1>
                <p>Cartón #<?php echo str_pad($carton['numero_carton'], 3, '0', STR_PAD_LEFT); ?></p>
            </div>

            <div class="juego-controles">
                <button class="btn btn-sound" id="soundToggle" onclick="toggleSound()">
                    🔊
                </button>
                <a href="index.php" class="btn btn-secondary">
                    🚪 Salir
                </a>
            </div>
        </div>

        <!-- Contenedor Principal -->
        <div class="juego-main">
            <!-- Panel Izquierdo: Mi Cartón -->
            <div class="panel-carton">
                <h2>🎫 Mi Cartón</h2>

                <div class="carton-juego">
                    <table class="bingo-table-juego">
                        <thead>
                            <tr>
                                <th class="letra-b">B</th>
                                <th class="letra-i">I</th>
                                <th class="letra-n">N</th>
                                <th class="letra-g">G</th>
                                <th class="letra-o">O</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($row = 0; $row < 5; $row++): ?>
                                <tr>
                                    <?php for ($col = 0; $col < 5; $col++): ?>
                                        <?php
                                        $valor = $matriz[$row][$col];
                                        $esFree = ($valor === 'FREE');
                                        $estaExtraido = is_numeric($valor) && in_array($valor, $numerosExtraidos);
                                        $clase = $esFree ? 'free-cell' : '';
                                        $clase .= $estaExtraido ? ' extraido' : '';
                                        ?>
                                        <td class="<?php echo $clase; ?>"
                                            data-numero="<?php echo $valor; ?>"
                                            onclick="marcarCelda(this, '<?php echo $valor; ?>')">
                                            <span class="numero-valor"><?php echo $valor; ?></span>
                                            <span class="marca">✓</span>
                                        </td>
                                    <?php endfor; ?>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Botón de BINGO -->
                <button class="btn-bingo" id="btnBingo" onclick="cantarBingo()">
                    🎊 ¡CANTAR BINGO! 🎊
                </button>
            </div>

            <!-- Panel Central: Números Extraídos -->
            <div class="panel-numeros">
                <!-- Último Número -->
                <div class="ultimo-numero-display">
                    <h3>Último Número</h3>
                    <div class="numero-grande" id="ultimoNumero">
                        <div class="numero-valor">--</div>
                        <div class="letra-valor"></div>
                    </div>
                </div>

                <!-- Últimas Bolitas -->
                <div class="ultimas-bolitas">
                    <h3>Últimos Números</h3>
                    <div class="bolitas-scroll" id="bolitasScroll">
                        <?php
                        $ultimasExtracciones = array_slice(array_reverse($extracciones), 0, 10);
                        foreach ($ultimasExtracciones as $ext):
                        ?>
                            <div class="bolita letra-<?php echo strtolower($ext['letra']); ?>">
                                <span class="bolita-letra"><?php echo $ext['letra']; ?></span>
                                <span class="bolita-numero"><?php echo $ext['numero']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Tablero de Números -->
                <div class="tablero-numeros">
                    <h3>Todos los Números (<?php echo count($numerosExtraidos); ?>/75)</h3>
                    <div class="numeros-grid">
                        <?php for ($i = 1; $i <= 75; $i++): ?>
                            <div class="numero-mini <?php echo in_array($i, $numerosExtraidos) ? 'salido' : ''; ?>"
                                 id="mini-<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <!-- Panel Derecho: Premios e Info -->
            <div class="panel-premios">
                <h2>🏆 Premios</h2>
                <div class="premios-list">
                    <?php if (empty($premios)): ?>
                        <p>No hay premios configurados</p>
                    <?php else: ?>
                        <?php foreach ($premios as $index => $premio): ?>
                            <div class="premio-card">
                                <div class="premio-numero"><?php echo $index + 1; ?>°</div>
                                <div class="premio-info">
                                    <h4><?php echo ucfirst(str_replace('_', ' ', $premio['tipo'])); ?></h4>
                                    <p><?php echo htmlspecialchars($premio['descripcion']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Estado del Juego -->
                <div class="estado-juego">
                    <h3>Estado</h3>
                    <div class="estado-item">
                        <span class="icon">🎱</span>
                        <span id="numerosSalidos"><?php echo count($numerosExtraidos); ?></span>
                        <span>números salidos</span>
                    </div>
                    <div class="estado-item">
                        <span class="icon">✓</span>
                        <span id="numerosMarcados">0</span>
                        <span>marcados en mi cartón</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de BINGO -->
    <div id="bingoModal" class="modal">
        <div class="modal-content bingo-modal">
            <h2>¿Qué premio quieres reclamar?</h2>

            <div id="premiosDisponibles" class="premios-reclamo">
                <?php foreach ($premios as $index => $premio): ?>
                    <button class="premio-boton" onclick="reclamarPremio('<?php echo $premio['tipo']; ?>')">
                        <strong><?php echo ucfirst(str_replace('_', ' ', $premio['tipo'])); ?></strong>
                        <span><?php echo htmlspecialchars($premio['descripcion']); ?></span>
                    </button>
                <?php endforeach; ?>
            </div>

            <button class="btn btn-secondary" onclick="cerrarModalBingo()">Cancelar</button>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        const cartonId = <?php echo $carton_id; ?>;
        const sorteoId = <?php echo $carton['sorteo_id']; ?>;
        const matriz = <?php echo json_encode($matriz); ?>;
        let numerosExtraidos = <?php echo json_encode($numerosExtraidos); ?>;
        let numerosMarcados = [];
        let sonidoActivo = true;
    </script>
    <script src="../js/confetti.js"></script>
    <script src="../js/juego.js"></script>
</body>
</html>
