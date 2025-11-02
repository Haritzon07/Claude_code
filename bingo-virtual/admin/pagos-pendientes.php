<?php
require_once '../includes/config.php';
require_once '../includes/Database.php';
require_once '../includes/funciones.php';

if (!estaAutenticado() || !esAdmin()) {
    redirigir('../login.php');
}

$db = new Database();

// Confirmar pago
if (isset($_POST['confirmar_pago'])) {
    $pago_id = (int)$_POST['pago_id'];

    $db->beginTransaction();
    try {
        // Actualizar pago
        $sql = "UPDATE pagos SET estado = 'confirmado', fecha_confirmacion = NOW(), confirmado_por = :admin_id
                WHERE id = :pago_id";
        $db->query($sql, ['admin_id' => $_SESSION['usuario_id'], 'pago_id' => $pago_id]);

        // Obtener información del pago
        $pago = $db->fetchOne("SELECT * FROM pagos WHERE id = :id", ['id' => $pago_id]);

        // Actualizar cartón a pagado
        $sql = "UPDATE cartones SET estado = 'pagado', fecha_pago = NOW()
                WHERE id = :carton_id";
        $db->query($sql, ['carton_id' => $pago['carton_id']]);

        $db->commit();
        $mensaje = 'Pago confirmado exitosamente';
    } catch (Exception $e) {
        $db->rollback();
        $error = 'Error al confirmar pago: ' . $e->getMessage();
    }
}

// Rechazar pago
if (isset($_POST['rechazar_pago'])) {
    $pago_id = (int)$_POST['pago_id'];

    $db->beginTransaction();
    try {
        // Actualizar pago
        $sql = "UPDATE pagos SET estado = 'rechazado', confirmado_por = :admin_id
                WHERE id = :pago_id";
        $db->query($sql, ['admin_id' => $_SESSION['usuario_id'], 'pago_id' => $pago_id]);

        // Obtener información del pago
        $pago = $db->fetchOne("SELECT * FROM pagos WHERE id = :id", ['id' => $pago_id]);

        // Liberar cartón
        $sql = "UPDATE cartones SET estado = 'disponible', usuario_id = NULL
                WHERE id = :carton_id";
        $db->query($sql, ['carton_id' => $pago['carton_id']]);

        $db->commit();
        $mensaje = 'Pago rechazado. El cartón ha sido liberado.';
    } catch (Exception $e) {
        $db->rollback();
        $error = 'Error al rechazar pago: ' . $e->getMessage();
    }
}

// Obtener pagos pendientes
$pagos = $db->fetchAll("
    SELECT p.*, u.nombre, u.whatsapp, s.nombre as sorteo_nombre, c.numero_carton
    FROM pagos p
    JOIN usuarios u ON p.usuario_id = u.id
    JOIN sorteos s ON p.sorteo_id = s.id
    JOIN cartones c ON p.carton_id = c.id
    WHERE p.estado = 'pendiente'
    ORDER BY p.fecha_solicitud DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagos Pendientes - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="admin-panel">
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="pagos-container">
            <h1>💰 Pagos Pendientes</h1>

            <?php if (isset($mensaje)): ?>
                <div class="alert alert-success"><?php echo $mensaje; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (empty($pagos)): ?>
                <div class="empty-state">
                    <p>✅ No hay pagos pendientes por revisar</p>
                </div>
            <?php else: ?>
                <div class="pagos-grid">
                    <?php foreach ($pagos as $pago): ?>
                        <div class="pago-card">
                            <div class="pago-header">
                                <h3><?php echo htmlspecialchars($pago['nombre']); ?></h3>
                                <span class="badge badge-warning">Pendiente</span>
                            </div>

                            <div class="pago-info">
                                <div class="info-row">
                                    <span class="label">Sorteo:</span>
                                    <span class="value"><?php echo htmlspecialchars($pago['sorteo_nombre']); ?></span>
                                </div>

                                <div class="info-row">
                                    <span class="label">Cartón N°:</span>
                                    <span class="value">#<?php echo str_pad($pago['numero_carton'], 3, '0', STR_PAD_LEFT); ?></span>
                                </div>

                                <div class="info-row">
                                    <span class="label">Monto:</span>
                                    <span class="value">$<?php echo formatearNumero($pago['monto']); ?></span>
                                </div>

                                <div class="info-row">
                                    <span class="label">WhatsApp:</span>
                                    <span class="value">
                                        <a href="https://wa.me/57<?php echo $pago['whatsapp']; ?>" target="_blank">
                                            <?php echo $pago['whatsapp']; ?>
                                        </a>
                                    </span>
                                </div>

                                <div class="info-row">
                                    <span class="label">Método:</span>
                                    <span class="value"><?php echo $pago['metodo_pago'] ?? 'No especificado'; ?></span>
                                </div>

                                <div class="info-row">
                                    <span class="label">Fecha Solicitud:</span>
                                    <span class="value"><?php echo formatearFechaHora($pago['fecha_solicitud']); ?></span>
                                </div>

                                <?php if ($pago['notas']): ?>
                                    <div class="info-row">
                                        <span class="label">Notas:</span>
                                        <span class="value"><?php echo htmlspecialchars($pago['notas']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="pago-actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="pago_id" value="<?php echo $pago['id']; ?>">
                                    <button type="submit" name="confirmar_pago" class="btn btn-success"
                                            onclick="return confirm('¿Confirmar este pago?')">
                                        ✅ Confirmar
                                    </button>
                                </form>

                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="pago_id" value="<?php echo $pago['id']; ?>">
                                    <button type="submit" name="rechazar_pago" class="btn btn-danger"
                                            onclick="return confirm('¿Rechazar este pago? El cartón será liberado.')">
                                        ❌ Rechazar
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
