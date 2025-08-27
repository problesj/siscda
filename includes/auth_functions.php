<?php
/**
 * Funciones de autenticación y manejo de sesiones
 * Este archivo debe ser incluido en todas las páginas protegidas
 */

// Verificar que no se hayan enviado headers antes
if (headers_sent()) {
    error_log("Headers already sent in auth_functions.php");
}

// Incluir configuración de sesión
require_once dirname(__DIR__) . '/session_config.php';

// Incluir configuración de la base de datos
require_once dirname(__DIR__) . '/config.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Función para conectar a la base de datos
 */
function conectarDB() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        error_log("Error de conexión a la base de datos: " . $e->getMessage());
        die("Error de conexión a la base de datos. Contacta al administrador.");
    }
}

/**
 * Verifica si el usuario está autenticado
 * Si no lo está, redirige al login
 */
function verificarAutenticacion() {
    // Verificar si la sesión existe y tiene datos válidos
    if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
        // Limpiar cualquier sesión residual
        session_unset();
        session_destroy();
        
        // Redirigir al login
        $baseUrl = getBaseUrl();
        header('Location: ' . $baseUrl . '/index.php');
        exit();
    }
    
    // Verificar si la sesión no ha expirado (opcional)
    if (isset($_SESSION['ultimo_acceso'])) {
        $tiempo_limite = 7200; // 2 horas en segundos
        if (time() - $_SESSION['ultimo_acceso'] > $tiempo_limite) {
            // Sesión expirada
            session_unset();
            session_destroy();
            
            $baseUrl = getBaseUrl();
            header('Location: ' . $baseUrl . '/index.php?error=sesion_expirada');
            exit();
        }
    }
    
    // Actualizar último acceso
    $_SESSION['ultimo_acceso'] = time();
}

/**
 * Verifica si el usuario está autenticado (sin redirección)
 * Retorna true si está autenticado, false en caso contrario
 */
function estaAutenticado() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

/**
 * Verifica si el usuario NO está autenticado
 * Si está autenticado, redirige al dashboard
 */
function verificarNoAutenticado() {
    if (isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id'])) {
        $baseUrl = getBaseUrl();
        header('Location: ' . $baseUrl . '/dashboard.php');
        exit();
    }
}

/**
 * Cierra la sesión del usuario
 */
function cerrarSesion() {
    // Limpiar todas las variables de sesión
    session_unset();
    
    // Destruir la sesión
    session_destroy();
    
    // Eliminar la cookie de sesión
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
}

/**
 * Obtiene la URL base del proyecto
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
 * Obtiene la ruta base del proyecto
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

/**
 * Verifica si la sesión está activa y válida
 */
function sesionValida() {
    if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
        return false;
    }
    
    // Verificar tiempo de expiración si está configurado
    if (isset($_SESSION['ultimo_acceso'])) {
        $tiempo_limite = 7200; // 2 horas
        if (time() - $_SESSION['ultimo_acceso'] > $tiempo_limite) {
            return false;
        }
    }
    
    return true;
}

/**
 * Renueva la sesión del usuario
 */
function renovarSesion() {
    if (sesionValida()) {
        $_SESSION['ultimo_acceso'] = time();
        return true;
    }
    return false;
}

/**
 * Función para limpiar datos de entrada
 */
function limpiarDatos($datos) {
    $datos = trim($datos);
    $datos = stripslashes($datos);
    $datos = htmlspecialchars($datos, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    return $datos;
}
?>
