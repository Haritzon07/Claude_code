<?php
require_once '../includes/config.php';
require_once '../includes/Database.php';
require_once '../includes/funciones.php';

if (!estaAutenticado()) {
    redirigir('../login.php');
}

$db = new Database();

// Obtener sorteos disponibles (programados y en curso)
$sorteos = $db->fetchAll("
    SELECT * FROM sorteos
    WHERE estado IN ('programado', 'en_curso')
    ORDER BY fecha_sorteo ASC, hora_sorteo ASC
");

// Obtener mis cartones
$mis_cartones = $db->fetchAll("
    SELECT c.*, s.nombre as sorteo_nombre, s.fecha_sorteo, s.hora_sorteo, s.estado as sorteo_estado
    FROM cartones c
    JOIN sorteos s ON c.sorteo_id = s.id
    WHERE c.usuario_id = :usuario_id
    ORDER BY s.fecha_sorteo DESC, c.numero_carton ASC
", ['usuario_id' => $_SESSION['usuario_id']]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Panel - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/jugador.css">
</head>
<body class="jugador-panel">
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="welcome-section">
            <h1>¡Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?>! 🎉</h1>
            <p>Selecciona un sorteo y reserva tu cartón de la suerte</p>
        </div>

        <!-- Mis Cartones -->
        <?php if (!empty($mis_cartones)): ?>
        <div class="mis-cartones-section">
            <h2>🎫 Mis Cartones</h2>
            <div class="cartones-grid">
                <?php foreach ($mis_cartones as $carton): ?>
                    <div class="carton-card estado-<?php echo $carton['estado']; ?>">
                        <div class="carton-header">
                            <h3>Cartón #<?php echo str_pad($carton['numero_carton'], 3, '0', STR_PAD_LEFT); ?></h3>
                            <span class="badge badge-<?php echo $carton['estado']; ?>">
                                <?php echo ucfirst($carton['estado']); ?>
                            </span>
                        </div>

                        <div class="carton-info">
                            <p><strong>Sorteo:</strong> <?php echo htmlspecialchars($carton['sorteo_nombre']); ?></p>
                            <p><strong>Fecha:</strong> <?php echo formatearFecha($carton['fecha_sorteo']) . ' - ' . date('H:i', strtotime($carton['hora_sorteo'])); ?></p>
                        </div>

                        <div class="carton-actions">
                            <?php if ($carton['sorteo_estado'] === 'en_curso' && $carton['estado'] === 'pagado'): ?>
                                <a href="jugar.php?id=<?php echo $carton['id']; ?>" class="btn btn-success btn-block">
                                    🎮 ¡JUGAR AHORA!
                                </a>
                            <?php elseif ($carton['estado'] === 'pendiente'): ?>
                                <p class="text-warning">⏳ Esperando confirmación de pago</p>
                            <?php else: ?>
                                <a href="ver-carton.php?id=<?php echo $carton['id']; ?>" class="btn btn-primary btn-block">
                                    👁️ Ver Cartón
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Sorteos Disponibles -->
        <div class="sorteos-section">
            <h2>🎯 Sorteos Disponibles</h2>

            <?php if (empty($sorteos)): ?>
                <div class="empty-state">
                    <p>No hay sorteos disponibles en este momento</p>
                </div>
            <?php else: ?>
                <div class="sorteos-grid">
                    <?php foreach ($sorteos as $sorteo): ?>
                        <?php
                        // Contar cartones disponibles
                        $disponibles = $db->fetchOne("
                            SELECT COUNT(*) as total FROM cartones
                            WHERE sorteo_id = :sorteo_id AND estado = 'disponible'
                        ", ['sorteo_id' => $sorteo['id']])['total'];
                        ?>

                        <div class="sorteo-card">
                            <div class="sorteo-header">
                                <h3><?php echo htmlspecialchars($sorteo['nombre']); ?></h3>
                                <?php if ($sorteo['estado'] === 'en_curso'): ?>
                                    <span class="badge badge-live">🔴 EN VIVO</span>
                                <?php endif; ?>
                            </div>

                            <div class="sorteo-info">
                                <div class="info-item">
                                    <span class="icon">📅</span>
                                    <span><?php echo formatearFecha($sorteo['fecha_sorteo']); ?></span>
                                </div>

                                <div class="info-item">
                                    <span class="icon">⏰</span>
                                    <span><?php echo date('h:i A', strtotime($sorteo['hora_sorteo'])); ?></span>
                                </div>

                                <div class="info-item">
                                    <span class="icon">💰</span>
                                    <span>$<?php echo formatearNumero($sorteo['precio_carton']); ?></span>
                                </div>

                                <div class="info-item">
                                    <span class="icon">🎫</span>
                                    <span><?php echo $disponibles; ?> cartones disponibles</span>
                                </div>
                            </div>

                            <?php if ($sorteo['descripcion']): ?>
                                <div class="sorteo-description">
                                    <p><?php echo nl2br(htmlspecialchars($sorteo['descripcion'])); ?></p>
                                </div>
                            <?php endif; ?>

                            <div class="sorteo-actions">
                                <?php if ($disponibles > 0): ?>
                                    <a href="seleccionar-carton.php?sorteo=<?php echo $sorteo['id']; ?>"
                                       class="btn btn-primary btn-block">
                                        🎫 Seleccionar Cartón
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-block" disabled>
                                        Sin cartones disponibles
                                    </button>
                                <?php endif; ?>

                                <a href="ver-sorteo-info.php?id=<?php echo $sorteo['id']; ?>"
                                   class="btn btn-outline btn-block">
                                    ℹ️ Ver Detalles
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../js/jugador.js"></script>
</body>
</html>
