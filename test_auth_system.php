<?php
/**
 * Script de prueba para verificar el sistema de autenticación
 * Solo para desarrollo y testing
 */

echo "<h2>Prueba del Sistema de Autenticación</h2>";

// Incluir funciones de autenticación
require_once 'includes/auth_functions.php';

echo "<h3>Estado de la Sesión</h3>";
echo "<p><strong>ID de Sesión:</strong> " . session_id() . "</p>";
echo "<p><strong>Estado de Sesión:</strong> " . session_status() . "</p>";
echo "<p><strong>Usuario Autenticado:</strong> " . (estaAutenticado() ? 'Sí' : 'No') . "</p>";

if (estaAutenticado()) {
    echo "<p><strong>ID de Usuario:</strong> " . $_SESSION['usuario_id'] . "</p>";
    echo "<p><strong>Username:</strong> " . $_SESSION['username'] . "</p>";
    echo "<p><strong>Nombre Completo:</strong> " . $_SESSION['nombre_completo'] . "</p>";
    echo "<p><strong>Último Acceso:</strong> " . (isset($_SESSION['ultimo_acceso']) ? date('Y-m-d H:i:s', $_SESSION['ultimo_acceso']) : 'No registrado') . "</p>";
    
    echo "<h3>Verificaciones</h3>";
    echo "<p><strong>Sesión Válida:</strong> " . (sesionValida() ? 'Sí' : 'No') . "</p>";
    
    echo "<h3>Acciones</h3>";
    echo "<p><a href='logout.php'>Cerrar Sesión</a></p>";
    echo "<p><a href='dashboard.php'>Ir al Dashboard</a></p>";
} else {
    echo "<h3>Acciones</h3>";
    echo "<p><a href='index.php'>Ir al Login</a></p>";
}

echo "<h3>Información del Sistema</h3>";
echo "<p><strong>Base URL:</strong> " . getBaseUrl() . "</p>";
echo "<p><strong>Base Path:</strong> " . getBasePath() . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Script Name:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";

echo "<h3>Configuración de Sesión</h3>";
echo "<p><strong>session.gc_maxlifetime:</strong> " . ini_get('session.gc_maxlifetime') . " segundos</p>";
echo "<p><strong>session.cookie_lifetime:</strong> " . ini_get('session.cookie_lifetime') . " segundos</p>";
echo "<p><strong>session.use_strict_mode:</strong> " . (ini_get('session.use_strict_mode') ? 'Activado' : 'Desactivado') . "</p>";

echo "<hr>";
echo "<p><em>Este archivo debe ser eliminado en producción</em></p>";
?>
