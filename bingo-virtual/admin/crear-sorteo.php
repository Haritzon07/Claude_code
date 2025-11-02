<?php
require_once '../includes/config.php';
require_once '../includes/Database.php';
require_once '../includes/funciones.php';

if (!estaAutenticado() || !esAdmin()) {
    redirigir('../login.php');
}

$db = new Database();
$error = '';
$mensaje = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verificarTokenCSRF($_POST['csrf_token'])) {
        $error = 'Token de seguridad inválido';
    } else {
        $nombre = limpiar($_POST['nombre']);
        $descripcion = limpiar($_POST['descripcion']);
        $fecha_sorteo = $_POST['fecha_sorteo'];
        $hora_sorteo = $_POST['hora_sorteo'];
        $cantidad_cartones = (int)$_POST['cantidad_cartones'];
        $precio_carton = (float)$_POST['precio_carton'];
        $reglas = limpiar($_POST['reglas']);

        // Premios
        $premios = [];
        if (isset($_POST['tipo_premio'])) {
            foreach ($_POST['tipo_premio'] as $index => $tipo) {
                if (!empty($tipo)) {
                    $premios[] = [
                        'tipo' => $tipo,
                        'descripcion' => limpiar($_POST['premio_descripcion'][$index] ?? ''),
                        'orden' => $index + 1
                    ];
                }
            }
        }

        $premios_json = json_encode($premios, JSON_UNESCAPED_UNICODE);

        if (empty($nombre) || empty($fecha_sorteo) || empty($hora_sorteo)) {
            $error = 'Por favor complete los campos obligatorios';
        } elseif ($cantidad_cartones < 1 || $cantidad_cartones > 100) {
            $error = 'La cantidad de cartones debe estar entre 1 y 100';
        } else {
            try {
                $db->beginTransaction();

                // Insertar sorteo
                $sql = "INSERT INTO sorteos (nombre, descripcion, fecha_sorteo, hora_sorteo, cantidad_cartones, precio_carton, premios_json, reglas, estado)
                        VALUES (:nombre, :descripcion, :fecha_sorteo, :hora_sorteo, :cantidad_cartones, :precio_carton, :premios_json, :reglas, 'programado')";

                $resultado = $db->query($sql, [
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                    'fecha_sorteo' => $fecha_sorteo,
                    'hora_sorteo' => $hora_sorteo,
                    'cantidad_cartones' => $cantidad_cartones,
                    'precio_carton' => $precio_carton,
                    'premios_json' => $premios_json,
                    'reglas' => $reglas
                ]);

                if ($resultado) {
                    $sorteo_id = $db->lastInsertId();

                    // Generar cartones automáticamente
                    for ($i = 1; $i <= $cantidad_cartones; $i++) {
                        $matriz = generarCartonBingo();
                        $matriz_json = json_encode($matriz);

                        $sqlCarton = "INSERT INTO cartones (sorteo_id, numero_carton, matriz_json, estado)
                                      VALUES (:sorteo_id, :numero_carton, :matriz_json, 'disponible')";

                        $db->query($sqlCarton, [
                            'sorteo_id' => $sorteo_id,
                            'numero_carton' => $i,
                            'matriz_json' => $matriz_json
                        ]);
                    }

                    $db->commit();
                    $mensaje = 'Sorteo creado exitosamente con ' . $cantidad_cartones . ' cartones';

                    // Redirigir después de 2 segundos
                    header("refresh:2;url=ver-sorteo.php?id=" . $sorteo_id);
                } else {
                    throw new Exception('Error al crear sorteo');
                }
            } catch (Exception $e) {
                $db->rollback();
                $error = 'Error al crear sorteo: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Sorteo - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="admin-panel">
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="form-container">
            <h1>➕ Crear Nuevo Sorteo</h1>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($mensaje): ?>
                <div class="alert alert-success"><?php echo $mensaje; ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="admin-form">
                <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">

                <div class="form-section">
                    <h3>Información Básica</h3>

                    <div class="form-group">
                        <label for="nombre">Nombre del Sorteo *</label>
                        <input type="text" id="nombre" name="nombre" required
                               placeholder="Ej: Bingo Navideño 2024">
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="3"
                                  placeholder="Descripción del evento"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="fecha_sorteo">Fecha del Sorteo *</label>
                            <input type="date" id="fecha_sorteo" name="fecha_sorteo" required
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="form-group">
                            <label for="hora_sorteo">Hora del Sorteo *</label>
                            <input type="time" id="hora_sorteo" name="hora_sorteo" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="cantidad_cartones">Cantidad de Cartones (Máx. 100) *</label>
                            <input type="number" id="cantidad_cartones" name="cantidad_cartones"
                                   min="1" max="100" value="50" required>
                        </div>

                        <div class="form-group">
                            <label for="precio_carton">Precio por Cartón</label>
                            <input type="number" id="precio_carton" name="precio_carton"
                                   min="0" step="0.01" value="5000">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Premios</h3>
                    <div id="premios-container">
                        <div class="premio-item">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Tipo de Premio</label>
                                    <select name="tipo_premio[]" class="form-control">
                                        <option value="">Seleccione...</option>
                                        <option value="linea_horizontal">Línea Horizontal</option>
                                        <option value="linea_vertical">Línea Vertical</option>
                                        <option value="diagonal">Diagonal</option>
                                        <option value="x">X</option>
                                        <option value="l">L</option>
                                        <option value="cuadro_central">Cuadro Central 3x3</option>
                                        <option value="cuadro_externo">Cuadro Externo</option>
                                        <option value="carton_lleno">Cartón Lleno</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Descripción del Premio</label>
                                    <input type="text" name="premio_descripcion[]"
                                           placeholder="Ej: $100.000">
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-secondary" onclick="agregarPremio()">
                        ➕ Agregar Otro Premio
                    </button>
                </div>

                <div class="form-section">
                    <h3>Reglas y Configuración</h3>

                    <div class="form-group">
                        <label for="reglas">Reglas del Juego</label>
                        <textarea id="reglas" name="reglas" rows="5"
                                  placeholder="Ingrese las reglas del sorteo..."></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success btn-lg">
                        💾 Crear Sorteo y Generar Cartones
                    </button>
                    <a href="index.php" class="btn btn-secondary btn-lg">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function agregarPremio() {
            const container = document.getElementById('premios-container');
            const premioItem = document.querySelector('.premio-item').cloneNode(true);

            // Limpiar valores
            premioItem.querySelectorAll('select, input').forEach(input => {
                input.value = '';
            });

            container.appendChild(premioItem);
        }
    </script>
</body>
</html>
