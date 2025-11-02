<?php
require_once '../includes/config.php';
require_once '../includes/Database.php';
require_once '../includes/funciones.php';

if (!estaAutenticado()) {
    redirigir('../login.php');
}

$db = new Database();
$sorteo_id = isset($_GET['sorteo']) ? (int)$_GET['sorteo'] : 0;

// Obtener sorteo
$sorteo = $db->fetchOne("SELECT * FROM sorteos WHERE id = :id", ['id' => $sorteo_id]);

if (!$sorteo) {
    redirigir('index.php');
}

// Procesar reserva de cartón
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservar'])) {
    $carton_id = (int)$_POST['carton_id'];
    $metodo_pago = limpiar($_POST['metodo_pago']);
    $notas = limpiar($_POST['notas'] ?? '');

    $db->beginTransaction();
    try {
        // Verificar que el cartón esté disponible
        $carton = $db->fetchOne("
            SELECT * FROM cartones
            WHERE id = :id AND estado = 'disponible' AND sorteo_id = :sorteo_id
        ", ['id' => $carton_id, 'sorteo_id' => $sorteo_id]);

        if (!$carton) {
            throw new Exception('Este cartón ya no está disponible');
        }

        // Actualizar cartón
        $sql = "UPDATE cartones SET estado = 'pendiente', usuario_id = :usuario_id, fecha_reserva = NOW()
                WHERE id = :carton_id";
        $db->query($sql, ['usuario_id' => $_SESSION['usuario_id'], 'carton_id' => $carton_id]);

        // Registrar pago pendiente
        $sql = "INSERT INTO pagos (usuario_id, sorteo_id, carton_id, monto, metodo_pago, notas, estado)
                VALUES (:usuario_id, :sorteo_id, :carton_id, :monto, :metodo_pago, :notas, 'pendiente')";
        $db->query($sql, [
            'usuario_id' => $_SESSION['usuario_id'],
            'sorteo_id' => $sorteo_id,
            'carton_id' => $carton_id,
            'monto' => $sorteo['precio_carton'],
            'metodo_pago' => $metodo_pago,
            'notas' => $notas
        ]);

        $db->commit();

        $_SESSION['mensaje_exito'] = 'Cartón reservado exitosamente. Espera la confirmación del administrador.';
        redirigir('index.php');
    } catch (Exception $e) {
        $db->rollback();
        $error = $e->getMessage();
    }
}

// Obtener cartones disponibles
$cartones = $db->fetchAll("
    SELECT * FROM cartones
    WHERE sorteo_id = :sorteo_id AND estado = 'disponible'
    ORDER BY numero_carton ASC
", ['sorteo_id' => $sorteo_id]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Cartón - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/jugador.css">
</head>
<body class="jugador-panel">
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="seleccion-container">
            <h1>🎫 Seleccionar Cartón</h1>

            <div class="sorteo-info-banner">
                <h2><?php echo htmlspecialchars($sorteo['nombre']); ?></h2>
                <p>📅 <?php echo formatearFecha($sorteo['fecha_sorteo']) . ' - ' . date('H:i', strtotime($sorteo['hora_sorteo'])); ?></p>
                <p>💰 Precio: $<?php echo formatearNumero($sorteo['precio_carton']); ?></p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (empty($cartones)): ?>
                <div class="empty-state">
                    <p>😔 Lo sentimos, no hay cartones disponibles para este sorteo</p>
                    <a href="index.php" class="btn btn-primary">Volver a Sorteos</a>
                </div>
            <?php else: ?>
                <div class="cartones-disponibles">
                    <p class="info-text">Haz clic en un cartón para ver su contenido y reservarlo</p>

                    <div class="cartones-grid-seleccion">
                        <?php foreach ($cartones as $carton): ?>
                            <div class="carton-mini" onclick="mostrarCarton(<?php echo $carton['id']; ?>, <?php echo htmlspecialchars($carton['matriz_json'], ENT_QUOTES); ?>)">
                                <div class="carton-numero">
                                    #<?php echo str_pad($carton['numero_carton'], 3, '0', STR_PAD_LEFT); ?>
                                </div>
                                <div class="carton-icon">🎫</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para ver y reservar cartón -->
    <div id="cartonModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal()">&times;</span>

            <h2>Vista Previa del Cartón</h2>
            <div id="carton-preview" class="bingo-card-preview"></div>

            <form method="POST" id="reservaForm">
                <input type="hidden" name="carton_id" id="carton_id_input">

                <div class="form-group">
                    <label for="metodo_pago">Método de Pago</label>
                    <select name="metodo_pago" id="metodo_pago" required class="form-control">
                        <option value="">Seleccione...</option>
                        <option value="Transferencia Bancaria">Transferencia Bancaria</option>
                        <option value="Nequi">Nequi</option>
                        <option value="Daviplata">Daviplata</option>
                        <option value="Efectivo">Efectivo</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="notas">Notas (Opcional)</label>
                    <textarea name="notas" id="notas" rows="2" class="form-control"
                              placeholder="Ej: Transferencia realizada a las 2:30 PM"></textarea>
                </div>

                <div class="modal-actions">
                    <button type="submit" name="reservar" class="btn btn-success btn-lg">
                        ✅ Reservar Este Cartón
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="cerrarModal()">
                        Cancelar
                    </button>
                </div>

                <p class="info-text">
                    ⚠️ Al reservar, el cartón quedará pendiente de confirmación de pago por el administrador.
                </p>
            </form>
        </div>
    </div>

    <script>
        function mostrarCarton(cartonId, matriz) {
            document.getElementById('carton_id_input').value = cartonId;

            // Generar vista previa del cartón
            let html = '<table class="bingo-table">';
            html += '<thead><tr><th>B</th><th>I</th><th>N</th><th>G</th><th>O</th></tr></thead>';
            html += '<tbody>';

            for (let row = 0; row < 5; row++) {
                html += '<tr>';
                for (let col = 0; col < 5; col++) {
                    let valor = matriz[row][col];
                    let clase = valor === 'FREE' ? 'free-cell' : '';
                    html += `<td class="${clase}">${valor}</td>`;
                }
                html += '</tr>';
            }

            html += '</tbody></table>';
            document.getElementById('carton-preview').innerHTML = html;

            // Mostrar modal
            document.getElementById('cartonModal').style.display = 'block';
        }

        function cerrarModal() {
            document.getElementById('cartonModal').style.display = 'none';
        }

        // Cerrar modal al hacer clic fuera de él
        window.onclick = function(event) {
            const modal = document.getElementById('cartonModal');
            if (event.target == modal) {
                cerrarModal();
            }
        }
    </script>
</body>
</html>
