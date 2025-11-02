<?php
// =====================================================
// CONFIGURACIÓN GENERAL DEL SISTEMA
// =====================================================

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'bingo_virtual');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configuración del sitio
define('SITE_NAME', 'Bingo Virtual');
define('SITE_URL', 'http://localhost/bingo-virtual');
define('BASE_PATH', dirname(__DIR__));

// Zona horaria
date_default_timezone_set('America/Bogota');

// Configuración de errores (cambiar a 0 en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 si usa HTTPS

// Tiempo de inactividad de sesión (30 minutos)
define('SESSION_TIMEOUT', 1800);

// Verificar timeout de sesión
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['LAST_ACTIVITY'] = time();
