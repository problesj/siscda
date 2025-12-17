<?php
/**
 * Script para extender la sesión del usuario
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
    echo json_encode(['success' => false, 'error' => 'Usuario no autenticado']);
    exit();
}

// Obtener el contenido JSON de la petición
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['action'])) {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit();
}

try {
    switch ($data['action']) {
        case 'extend':
            // Extender la sesión
            if (renovarSesion()) {
                // Actualizar último acceso en la base de datos (si la columna existe)
                try {
                    $pdo = conectarDB();
                    $stmt = $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'FECHA_ULTIMOACCESO'");
                    if ($stmt->rowCount() > 0) {
                        $stmt = $pdo->prepare("UPDATE usuarios SET FECHA_ULTIMOACCESO = NOW() WHERE USUARIO_ID = ?");
                        $stmt->execute([$_SESSION['usuario_id']]);
                    }
                } catch (PDOException $e) {
                    // Silenciar el error si la columna no existe
                    error_log("Error al actualizar último acceso: " . $e->getMessage());
                }
                
                echo json_encode(['success' => true, 'message' => 'Sesión extendida']);
            } else {
                echo json_encode(['success' => false, 'error' => 'No se pudo extender la sesión']);
            }
            break;
            
        case 'heartbeat':
            // Verificar que la sesión esté activa
            if (sesionValida()) {
                echo json_encode(['success' => true, 'active' => true]);
            } else {
                echo json_encode(['success' => false, 'active' => false]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Acción no reconocida']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Error en extend_session.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error interno del servidor']);
}
?>
