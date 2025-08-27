<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'cda_base');
define('DB_USER', 'admincda');
define('DB_PASS', 'cda2025$');

// Configuración de la aplicación
define('APP_NAME', 'Sistema de Control de Asistencias');
define('APP_VERSION', '1.0.0');

// Configuración de sesión se maneja en session_config.php

// Función para conectar a la base de datos
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

// Función para verificar si el usuario está autenticado
function verificarAutenticacion() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: index.php');
        exit();
    }
}

// Función para limpiar datos de entrada
function limpiarDatos($datos) {
    $datos = trim($datos);
    $datos = stripslashes($datos);
    $datos = htmlspecialchars($datos);
    return $datos;
}

// Función para obtener la URL base del proyecto
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

// Función para obtener la ruta base del proyecto
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
?>
