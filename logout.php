<?php
/**
 * Script de cierre de sesión
 * Cierra la sesión del usuario y redirige al login
 */

require_once 'includes/auth_functions.php';

// Cerrar la sesión usando la función mejorada
cerrarSesion();

// Redirigir al login con mensaje de sesión cerrada
$baseUrl = getBaseUrl();
header('Location: ' . $baseUrl . '/index.php?success=sesion_cerrada');
exit();
?>
