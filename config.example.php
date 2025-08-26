<?php
/**
 * Archivo de Configuración de Ejemplo para el Sistema CDA
 * 
 * INSTRUCCIONES:
 * 1. Copia este archivo y renómbralo a 'config.php'
 * 2. Modifica los valores según tu configuración
 * 3. Asegúrate de que el archivo config.php no sea accesible desde el navegador
 */

// =====================================================
// CONFIGURACIÓN DE LA BASE DE DATOS
// =====================================================

// Host de la base de datos (normalmente 'localhost' o '127.0.0.1')
define('DB_HOST', 'localhost');

// Nombre de la base de datos
define('DB_NAME', 'cda_base');

// Usuario de la base de datos
define('DB_USER', 'admincda');

// Contraseña de la base de datos
define('DB_PASS', 'cda2025$');

// =====================================================
// CONFIGURACIÓN DE LA APLICACIÓN
// =====================================================

// Nombre de la aplicación
define('APP_NAME', 'Sistema de Control de Asistencias');

// Versión de la aplicación
define('APP_VERSION', '1.0.0');

// URL base de la aplicación (ajusta según tu configuración)
define('APP_URL', 'http://localhost/siscda');

// Zona horaria (ajusta según tu ubicación)
define('TIMEZONE', 'America/Santiago');

// =====================================================
// CONFIGURACIÓN DE SEGURIDAD
// =====================================================

// Clave secreta para sesiones (cambia esto por una clave única)
define('SESSION_SECRET', 'cambia_esta_clave_secreta_por_una_unica');

// Tiempo de expiración de sesión en segundos (8 horas por defecto)
define('SESSION_TIMEOUT', 28800);

// =====================================================
// FUNCIONES DE CONEXIÓN
// =====================================================

/**
 * Función para conectar a la base de datos
 * @return PDO Objeto de conexión PDO
 */
function conectarDB() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        // En producción, no mostrar detalles del error
        error_log("Error de conexión a la base de datos: " . $e->getMessage());
        die("Error de conexión a la base de datos. Contacta al administrador.");
    }
}

/**
 * Función para verificar si el usuario está autenticado
 * Redirige a la página de login si no está autenticado
 */
function verificarAutenticacion() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: index.php');
        exit();
    }
}

/**
 * Función para limpiar datos de entrada
 * Previene ataques XSS y otros problemas de seguridad
 * @param string $datos Datos a limpiar
 * @return string Datos limpios
 */
function limpiarDatos($datos) {
    $datos = trim($datos);
    $datos = stripslashes($datos);
    $datos = htmlspecialchars($datos, ENT_QUOTES, 'UTF-8');
    return $datos;
}

/**
 * Función para generar un token CSRF
 * @return string Token CSRF
 */
function generarTokenCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Función para verificar un token CSRF
 * @param string $token Token a verificar
 * @return bool True si el token es válido
 */
function verificarTokenCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Función para obtener la URL base del proyecto
 * @return string URL base del proyecto
 */
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $basePath = dirname($scriptName);
    
    // Si estamos en el directorio raíz del proyecto
    if (strpos($basePath, '/siscda') !== false) {
        $basePath = '/siscda';
    } else {
        $basePath = '';
    }
    
    return $protocol . '://' . $host . $basePath;
}

/**
 * Función para obtener la ruta base del proyecto
 * @return string Ruta base del proyecto
 */
function getBasePath() {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $basePath = dirname($scriptName);
    
    // Si estamos en el directorio raíz del proyecto
    if (strpos($basePath, '/siscda') !== false) {
        $basePath = '/siscda';
    } else {
        $basePath = '';
    }
    
    return $basePath;
}

// =====================================================
// CONFIGURACIÓN DE ZONA HORARIA
// =====================================================

// Establecer zona horaria
if (defined('TIMEZONE')) {
    date_default_timezone_set(TIMEZONE);
}

// =====================================================
// CONFIGURACIÓN DE ERRORES (SOLO PARA DESARROLLO)
// =====================================================

// En producción, comenta estas líneas
error_reporting(E_ALL);
ini_set('display_errors', 1);

// En producción, descomenta estas líneas
// error_reporting(0);
// ini_set('display_errors', 0);
// ini_set('log_errors', 1);
// ini_set('error_log', '/path/to/error.log');

// =====================================================
// VERIFICACIÓN DE CONFIGURACIÓN
// =====================================================

// Verificar que las extensiones necesarias estén habilitadas
$requiredExtensions = ['pdo', 'pdo_mysql', 'session'];
foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        die("Error: La extensión PHP '$ext' no está habilitada. Contacta al administrador del servidor.");
    }
}

// Verificar que la versión de PHP sea compatible
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    die("Error: Se requiere PHP 7.4 o superior. Versión actual: " . PHP_VERSION);
}
?>
