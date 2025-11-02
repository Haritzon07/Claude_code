<?php
// =====================================================
// FUNCIONES COMUNES DEL SISTEMA
// =====================================================

/**
 * Verificar si el usuario está autenticado
 */
function estaAutenticado() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

/**
 * Verificar si el usuario es administrador
 */
function esAdmin() {
    return estaAutenticado() && isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}

/**
 * Redirigir a una página
 */
function redirigir($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Limpiar entrada de datos
 */
function limpiar($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Generar token CSRF
 */
function generarTokenCSRF() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verificar token CSRF
 */
function verificarTokenCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generar hash de contraseña
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verificar contraseña
 */
function verificarPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Formatear fecha
 */
function formatearFecha($fecha, $formato = 'd/m/Y') {
    return date($formato, strtotime($fecha));
}

/**
 * Formatear fecha y hora
 */
function formatearFechaHora($fecha, $formato = 'd/m/Y H:i') {
    return date($formato, strtotime($fecha));
}

/**
 * Formatear número con separadores
 */
function formatearNumero($numero, $decimales = 0) {
    return number_format($numero, $decimales, ',', '.');
}

/**
 * Generar cartón de bingo aleatorio (5x5)
 */
function generarCartonBingo() {
    $carton = [];

    // B: 1-15, I: 16-30, N: 31-45, G: 46-60, O: 61-75
    $rangos = [
        'B' => range(1, 15),
        'I' => range(16, 30),
        'N' => range(31, 45),
        'G' => range(46, 60),
        'O' => range(61, 75)
    ];

    $letras = ['B', 'I', 'N', 'G', 'O'];

    for ($col = 0; $col < 5; $col++) {
        $letra = $letras[$col];
        $numeros = $rangos[$letra];
        shuffle($numeros);
        $seleccionados = array_slice($numeros, 0, 5);

        for ($row = 0; $row < 5; $row++) {
            // La casilla central (2,2) es FREE
            if ($col == 2 && $row == 2) {
                $carton[$row][$col] = 'FREE';
            } else {
                $carton[$row][$col] = $seleccionados[$row];
            }
        }
    }

    return $carton;
}

/**
 * Validar figura de bingo
 */
function validarFigura($carton, $numerosExtraidos, $tipoFigura) {
    // Convertir matriz JSON si es string
    if (is_string($carton)) {
        $carton = json_decode($carton, true);
    }

    switch ($tipoFigura) {
        case 'linea_horizontal':
            return validarLineaHorizontal($carton, $numerosExtraidos);

        case 'linea_vertical':
            return validarLineaVertical($carton, $numerosExtraidos);

        case 'diagonal':
            return validarDiagonal($carton, $numerosExtraidos);

        case 'x':
            return validarX($carton, $numerosExtraidos);

        case 'l':
            return validarL($carton, $numerosExtraidos);

        case 'cuadro_central':
            return validarCuadroCentral($carton, $numerosExtraidos);

        case 'cuadro_externo':
            return validarCuadroExterno($carton, $numerosExtraidos);

        case 'carton_lleno':
            return validarCartonLleno($carton, $numerosExtraidos);

        default:
            return false;
    }
}

function validarLineaHorizontal($carton, $numerosExtraidos) {
    for ($row = 0; $row < 5; $row++) {
        $completa = true;
        for ($col = 0; $col < 5; $col++) {
            $valor = $carton[$row][$col];
            if ($valor !== 'FREE' && !in_array($valor, $numerosExtraidos)) {
                $completa = false;
                break;
            }
        }
        if ($completa) return true;
    }
    return false;
}

function validarLineaVertical($carton, $numerosExtraidos) {
    for ($col = 0; $col < 5; $col++) {
        $completa = true;
        for ($row = 0; $row < 5; $row++) {
            $valor = $carton[$row][$col];
            if ($valor !== 'FREE' && !in_array($valor, $numerosExtraidos)) {
                $completa = false;
                break;
            }
        }
        if ($completa) return true;
    }
    return false;
}

function validarDiagonal($carton, $numerosExtraidos) {
    // Diagonal principal
    $diagonal1 = true;
    for ($i = 0; $i < 5; $i++) {
        $valor = $carton[$i][$i];
        if ($valor !== 'FREE' && !in_array($valor, $numerosExtraidos)) {
            $diagonal1 = false;
            break;
        }
    }
    if ($diagonal1) return true;

    // Diagonal secundaria
    $diagonal2 = true;
    for ($i = 0; $i < 5; $i++) {
        $valor = $carton[$i][4 - $i];
        if ($valor !== 'FREE' && !in_array($valor, $numerosExtraidos)) {
            $diagonal2 = false;
            break;
        }
    }
    return $diagonal2;
}

function validarX($carton, $numerosExtraidos) {
    return validarDiagonal($carton, $numerosExtraidos) &&
           validarDiagonal($carton, $numerosExtraidos);
}

function validarL($carton, $numerosExtraidos) {
    // Validar L (primera columna + última fila)
    $valido = true;

    // Primera columna
    for ($row = 0; $row < 5; $row++) {
        $valor = $carton[$row][0];
        if ($valor !== 'FREE' && !in_array($valor, $numerosExtraidos)) {
            $valido = false;
            break;
        }
    }

    if (!$valido) return false;

    // Última fila (excepto primera columna ya validada)
    for ($col = 1; $col < 5; $col++) {
        $valor = $carton[4][$col];
        if ($valor !== 'FREE' && !in_array($valor, $numerosExtraidos)) {
            return false;
        }
    }

    return true;
}

function validarCuadroCentral($carton, $numerosExtraidos) {
    // Cuadro 3x3 central
    for ($row = 1; $row <= 3; $row++) {
        for ($col = 1; $col <= 3; $col++) {
            $valor = $carton[$row][$col];
            if ($valor !== 'FREE' && !in_array($valor, $numerosExtraidos)) {
                return false;
            }
        }
    }
    return true;
}

function validarCuadroExterno($carton, $numerosExtraidos) {
    // Bordes externos del cartón
    $posiciones = [
        // Primera fila
        [0,0], [0,1], [0,2], [0,3], [0,4],
        // Última fila
        [4,0], [4,1], [4,2], [4,3], [4,4],
        // Columnas laterales (sin esquinas ya incluidas)
        [1,0], [2,0], [3,0], [1,4], [2,4], [3,4]
    ];

    foreach ($posiciones as $pos) {
        $valor = $carton[$pos[0]][$pos[1]];
        if ($valor !== 'FREE' && !in_array($valor, $numerosExtraidos)) {
            return false;
        }
    }
    return true;
}

function validarCartonLleno($carton, $numerosExtraidos) {
    for ($row = 0; $row < 5; $row++) {
        for ($col = 0; $col < 5; $col++) {
            $valor = $carton[$row][$col];
            if ($valor !== 'FREE' && !in_array($valor, $numerosExtraidos)) {
                return false;
            }
        }
    }
    return true;
}

/**
 * Obtener letra BINGO según el número
 */
function obtenerLetraBingo($numero) {
    if ($numero >= 1 && $numero <= 15) return 'B';
    if ($numero >= 16 && $numero <= 30) return 'I';
    if ($numero >= 31 && $numero <= 45) return 'N';
    if ($numero >= 46 && $numero <= 60) return 'G';
    if ($numero >= 61 && $numero <= 75) return 'O';
    return '';
}

/**
 * Respuesta JSON
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Logging de errores
 */
function logError($mensaje, $archivo = 'error.log') {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $mensaje" . PHP_EOL;
    error_log($logMessage, 3, BASE_PATH . "/logs/$archivo");
}
