<?php
/**
 * Script de heartbeat para verificar el estado de la sesión
 * Responde a peticiones AJAX del gestor de sesiones del cliente
 */

header('Content-Type: application/json');

require_once 'includes/auth_functions.php';

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit();
}

// Verificar que el usuario esté autenticado
if (!estaAutenticado()) {
    echo json_encode(['success' => false, 'active' => false, 'error' => 'Usuario no autenticado']);
    exit();
}

try {
    // Verificar que la sesión esté activa
    if (sesionValida()) {
        // Actualizar último acceso
        renovarSesion();
        
        echo json_encode([
            'success' => true, 
            'active' => true, 
            'timestamp' => time(),
            'user_id' => $_SESSION['usuario_id']
        ]);
    } else {
        echo json_encode(['success' => false, 'active' => false, 'error' => 'Sesión expirada']);
    }
    
} catch (Exception $e) {
    error_log("Error en heartbeat.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'active' => false, 'error' => 'Error interno del servidor']);
}
?>
