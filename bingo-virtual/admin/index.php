<?php
require_once '../includes/config.php';
require_once '../includes/Database.php';
require_once '../includes/funciones.php';

// Verificar autenticación y permisos
if (!estaAutenticado() || !esAdmin()) {
    redirigir('../login.php');
}

$db = new Database();

// Obtener estadísticas
$totalSorteos = $db->fetchOne("SELECT COUNT(*) as total FROM sorteos")['total'];
$sorteosActivos = $db->fetchOne("SELECT COUNT(*) as total FROM sorteos WHERE estado IN ('programado', 'en_curso')")['total'];
$totalJugadores = $db->fetchOne("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'jugador'")['total'];
$cartonesVendidos = $db->fetchOne("SELECT COUNT(*) as total FROM cartones WHERE estado = 'pagado'")['total'];

// Obtener sorteos recientes
$sorteos = $db->fetchAll("SELECT * FROM sorteos ORDER BY fecha_creacion DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="admin-panel">
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="dashboard">
            <h1>Panel de Administración</h1>

            <!-- Estadísticas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">🎯</div>
                    <div class="stat-info">
                        <h3><?php echo $totalSorteos; ?></h3>
                        <p>Total Sorteos</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">🔴</div>
                    <div class="stat-info">
                        <h3><?php echo $sorteosActivos; ?></h3>
                        <p>Sorteos Activos</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-info">
                        <h3><?php echo $totalJugadores; ?></h3>
                        <p>Jugadores</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">🎫</div>
                    <div class="stat-info">
                        <h3><?php echo $cartonesVendidos; ?></h3>
                        <p>Cartones Vendidos</p>
                    </div>
                </div>
            </div>

            <!-- Botones de acciones rápidas -->
            <div class="quick-actions">
                <h2>Acciones Rápidas</h2>
                <div class="action-buttons">
                    <a href="crear-sorteo.php" class="btn btn-success">
                        <span>➕</span> Crear Nuevo Sorteo
                    </a>
                    <a href="sorteos.php" class="btn btn-primary">
                        <span>📋</span> Ver Todos los Sorteos
                    </a>
                    <a href="pagos-pendientes.php" class="btn btn-warning">
                        <span>💰</span> Pagos Pendientes
                    </a>
                    <a href="jugadores.php" class="btn btn-info">
                        <span>👥</span> Gestionar Jugadores
                    </a>
                </div>
            </div>

            <!-- Tabla de sorteos recientes -->
            <div class="recent-raffles">
                <h2>Sorteos Recientes</h2>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Fecha/Hora</th>
                                <th>Cartones</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($sorteos): ?>
                                <?php foreach ($sorteos as $sorteo): ?>
                                    <tr>
                                        <td><?php echo $sorteo['id']; ?></td>
                                        <td><?php echo htmlspecialchars($sorteo['nombre']); ?></td>
                                        <td><?php echo formatearFecha($sorteo['fecha_sorteo']) . ' ' . date('H:i', strtotime($sorteo['hora_sorteo'])); ?></td>
                                        <td><?php echo $sorteo['cantidad_cartones']; ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $sorteo['estado']; ?>">
                                                <?php echo ucfirst($sorteo['estado']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-icons">
                                                <?php if ($sorteo['estado'] === 'programado'): ?>
                                                    <a href="iniciar-sorteo.php?id=<?php echo $sorteo['id']; ?>"
                                                       class="btn-icon" title="Iniciar Sorteo">▶️</a>
                                                <?php elseif ($sorteo['estado'] === 'en_curso'): ?>
                                                    <a href="sorteo-vivo.php?id=<?php echo $sorteo['id']; ?>"
                                                       class="btn-icon" title="Ver Sorteo en Vivo">🔴</a>
                                                <?php endif; ?>
                                                <a href="ver-sorteo.php?id=<?php echo $sorteo['id']; ?>"
                                                   class="btn-icon" title="Ver Detalles">👁️</a>
                                                <a href="editar-sorteo.php?id=<?php echo $sorteo['id']; ?>"
                                                   class="btn-icon" title="Editar">✏️</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No hay sorteos registrados</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/admin.js"></script>
</body>
</html>
