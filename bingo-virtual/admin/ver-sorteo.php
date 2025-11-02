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

// Obtener estadísticas de cartones
$stats = $db->fetchOne("
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN estado = 'disponible' THEN 1 ELSE 0 END) as disponibles,
        SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
        SUM(CASE WHEN estado = 'pagado' THEN 1 ELSE 0 END) as pagados,
        SUM(CASE WHEN estado = 'ganador' THEN 1 ELSE 0 END) as ganadores
    FROM cartones
    WHERE sorteo_id = :sorteo_id
", ['sorteo_id' => $sorteo_id]);

// Obtener premios
$premios = json_decode($sorteo['premios_json'], true) ?? [];

// Obtener cartones
$cartones = $db->fetchAll("
    SELECT c.*, u.nombre as nombre_usuario, u.whatsapp
    FROM cartones c
    LEFT JOIN usuarios u ON c.usuario_id = u.id
    WHERE c.sorteo_id = :sorteo_id
    ORDER BY c.numero_carton
", ['sorteo_id' => $sorteo_id]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Sorteo - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="admin-panel">
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="sorteo-details">
            <div class="page-header">
                <h1><?php echo htmlspecialchars($sorteo['nombre']); ?></h1>
                <div class="header-actions">
                    <?php if ($sorteo['estado'] === 'programado'): ?>
                        <a href="iniciar-sorteo.php?id=<?php echo $sorteo['id']; ?>"
                           class="btn btn-success">▶️ Iniciar Sorteo</a>
                    <?php elseif ($sorteo['estado'] === 'en_curso'): ?>
                        <a href="sorteo-vivo.php?id=<?php echo $sorteo['id']; ?>"
                           class="btn btn-danger">🔴 Ir a Sorteo en Vivo</a>
                    <?php endif; ?>
                    <a href="editar-sorteo.php?id=<?php echo $sorteo['id']; ?>"
                       class="btn btn-primary">✏️ Editar</a>
                </div>
            </div>

            <!-- Información del sorteo -->
            <div class="info-grid">
                <div class="info-card">
                    <h3>📅 Fecha y Hora</h3>
                    <p><?php echo formatearFecha($sorteo['fecha_sorteo']) . ' - ' . date('H:i', strtotime($sorteo['hora_sorteo'])); ?></p>
                </div>

                <div class="info-card">
                    <h3>📊 Estado</h3>
                    <p><span class="badge badge-<?php echo $sorteo['estado']; ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $sorteo['estado'])); ?>
                    </span></p>
                </div>

                <div class="info-card">
                    <h3>💰 Precio</h3>
                    <p>$<?php echo formatearNumero($sorteo['precio_carton']); ?></p>
                </div>

                <div class="info-card">
                    <h3>🎫 Total Cartones</h3>
                    <p><?php echo $sorteo['cantidad_cartones']; ?></p>
                </div>
            </div>

            <!-- Estadísticas de cartones -->
            <div class="stats-section">
                <h2>Estadísticas de Cartones</h2>
                <div class="stats-grid">
                    <div class="stat-item disponible">
                        <span class="stat-number"><?php echo $stats['disponibles']; ?></span>
                        <span class="stat-label">Disponibles</span>
                    </div>
                    <div class="stat-item pendiente">
                        <span class="stat-number"><?php echo $stats['pendientes']; ?></span>
                        <span class="stat-label">Pendientes</span>
                    </div>
                    <div class="stat-item pagado">
                        <span class="stat-number"><?php echo $stats['pagados']; ?></span>
                        <span class="stat-label">Pagados</span>
                    </div>
                    <div class="stat-item ganador">
                        <span class="stat-number"><?php echo $stats['ganadores']; ?></span>
                        <span class="stat-label">Ganadores</span>
                    </div>
                </div>
            </div>

            <!-- Premios -->
            <?php if (!empty($premios)): ?>
            <div class="premios-section">
                <h2>🏆 Premios</h2>
                <div class="premios-list">
                    <?php foreach ($premios as $index => $premio): ?>
                        <div class="premio-card">
                            <span class="premio-numero"><?php echo $index + 1; ?>°</span>
                            <div class="premio-info">
                                <h4><?php echo ucfirst(str_replace('_', ' ', $premio['tipo'])); ?></h4>
                                <p><?php echo htmlspecialchars($premio['descripcion']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Descripción y reglas -->
            <?php if ($sorteo['descripcion']): ?>
            <div class="description-section">
                <h2>📝 Descripción</h2>
                <p><?php echo nl2br(htmlspecialchars($sorteo['descripcion'])); ?></p>
            </div>
            <?php endif; ?>

            <?php if ($sorteo['reglas']): ?>
            <div class="rules-section">
                <h2>📜 Reglas</h2>
                <p><?php echo nl2br(htmlspecialchars($sorteo['reglas'])); ?></p>
            </div>
            <?php endif; ?>

            <!-- Tabla de cartones -->
            <div class="cartones-section">
                <h2>🎫 Cartones (<?php echo count($cartones); ?>)</h2>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>N° Cartón</th>
                                <th>Estado</th>
                                <th>Jugador</th>
                                <th>WhatsApp</th>
                                <th>Fecha Reserva</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cartones as $carton): ?>
                                <tr>
                                    <td><strong>#<?php echo str_pad($carton['numero_carton'], 3, '0', STR_PAD_LEFT); ?></strong></td>
                                    <td>
                                        <span class="badge badge-<?php echo $carton['estado']; ?>">
                                            <?php echo ucfirst($carton['estado']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $carton['nombre_usuario'] ?? '-'; ?></td>
                                    <td><?php echo $carton['whatsapp'] ?? '-'; ?></td>
                                    <td><?php echo $carton['fecha_reserva'] ? formatearFechaHora($carton['fecha_reserva']) : '-'; ?></td>
                                    <td>
                                        <a href="ver-carton.php?id=<?php echo $carton['id']; ?>"
                                           class="btn-icon" title="Ver Cartón">👁️</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
