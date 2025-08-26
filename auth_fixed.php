<?php
// Verificar que no se hayan enviado headers antes
if (headers_sent()) {
    error_log("Headers already sent in auth.php");
}

// Incluir archivos de configuración
require_once 'session_config.php';
session_start();
require_once 'config.php';

// Verificar que las funciones necesarias estén disponibles
if (!function_exists('limpiarDatos')) {
    error_log("Función limpiarDatos() no está disponible en auth.php");
    die("Error: Función de limpieza de datos no disponible");
}

if (!function_exists('conectarDB')) {
    error_log("Función conectarDB() no está disponible en auth.php");
    die("Error: Función de conexión a base de datos no disponible");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificar que los datos POST estén presentes
    if (!isset($_POST['username']) || !isset($_POST['password'])) {
        $_SESSION['error'] = 'Usuario y contraseña son requeridos';
        header('Location: index.php');
        exit();
    }
    
    $username = limpiarDatos($_POST['username']);
    $password = $_POST['password'];
    
    // Validar que los datos no estén vacíos
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Usuario y contraseña no pueden estar vacíos';
        header('Location: index.php');
        exit();
    }
    
    try {
        $pdo = conectarDB();
        
        $stmt = $pdo->prepare("SELECT USUARIO_ID, USERNAME, PASSWORD, NOMBRE_COMPLETO FROM usuarios WHERE USERNAME = ?");
        $stmt->execute([$username]);
        $usuario = $stmt->fetch();
        
        if ($usuario && password_verify($password, $usuario['PASSWORD'])) {
            $_SESSION['usuario_id'] = $usuario['USUARIO_ID'];
            $_SESSION['username'] = $usuario['USERNAME'];
            $_SESSION['nombre_completo'] = $usuario['NOMBRE_COMPLETO'];
            
            // Log de autenticación exitosa
            error_log("Usuario autenticado exitosamente: $username");
            
            header('Location: dashboard.php');
            exit();
        } else {
            // Log de intento fallido
            error_log("Intento de autenticación fallido para usuario: $username");
            
            $_SESSION['error'] = 'Usuario o contraseña incorrectos';
            header('Location: index.php');
            exit();
        }
    } catch (PDOException $e) {
        // Log del error para debugging
        error_log("Error de conexión a BD en auth.php: " . $e->getMessage());
        
        // Mensaje más específico para el usuario
        if (strpos($e->getMessage(), 'Access denied') !== false) {
            $_SESSION['error'] = 'Error de acceso a la base de datos. Verifique las credenciales.';
        } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
            $_SESSION['error'] = 'Base de datos no encontrada. Verifique la configuración.';
        } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
            $_SESSION['error'] = 'No se puede conectar al servidor de base de datos.';
        } else {
            $_SESSION['error'] = 'Error de conexión a la base de datos.';
        }
        
        header('Location: index.php');
        exit();
    } catch (Exception $e) {
        // Log de otros errores
        error_log("Error general en auth.php: " . $e->getMessage());
        $_SESSION['error'] = 'Error interno del sistema.';
        header('Location: index.php');
        exit();
    }
} else {
    // Si no es POST, redirigir al inicio
    header('Location: index.php');
    exit();
}
?>
