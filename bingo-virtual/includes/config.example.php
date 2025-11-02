<?php
// =====================================================
// ARCHIVO DE CONFIGURACIÓN DE EJEMPLO
// =====================================================
//
// INSTRUCCIONES:
// 1. Copia este archivo y renómbralo a "config.php"
// 2. Ajusta los valores según tu configuración local
// 3. NO compartas este archivo en repositorios públicos
//

// Configuración de la base de datos
// IMPORTANTE: Cambia estos valores según tu configuración local
define('DB_HOST', 'localhost');              // Normalmente 'localhost'
define('DB_NAME', 'bingo_virtual');          // Nombre de tu base de datos
define('DB_USER', 'root');                   // Tu usuario de MySQL (root por defecto en XAMPP)
define('DB_PASS', '');                       // Tu contraseña de MySQL (vacío por defecto en XAMPP)
define('DB_CHARSET', 'utf8mb4');

// Configuración del sitio
define('SITE_NAME', 'Bingo Virtual');
define('SITE_URL', 'http://localhost/bingo-virtual');  // Ajusta según tu URL
define('BASE_PATH', dirname(__DIR__));

// Zona horaria (ajusta según tu ubicación)
// Ejemplos: 'America/Bogota', 'America/Mexico_City', 'America/Lima', 'Europe/Madrid'
date_default_timezone_set('America/Bogota');

// Configuración de errores
// IMPORTANTE: Cambiar a 0 en producción
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
