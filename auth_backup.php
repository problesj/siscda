<?php
require_once 'session_config.php';
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = limpiarDatos($_POST['username']);
    $password = $_POST['password'];
    
    try {
        $pdo = conectarDB();
        
        $stmt = $pdo->prepare("SELECT USUARIO_ID, USERNAME, PASSWORD, NOMBRE_COMPLETO FROM usuarios WHERE USERNAME = ?");
        $stmt->execute([$username]);
        $usuario = $stmt->fetch();
        
        if ($usuario && password_verify($password, $usuario['PASSWORD'])) {
            $_SESSION['usuario_id'] = $usuario['USUARIO_ID'];
            $_SESSION['username'] = $usuario['USERNAME'];
            $_SESSION['nombre_completo'] = $usuario['NOMBRE_COMPLETO'];
            
            header('Location: dashboard.php');
            exit();
        } else {
            $_SESSION['error'] = 'Usuario o contraseña incorrectos';
            header('Location: index.php');
            exit();
        }
    } catch (PDOException $e) {
        // Log del error para debugging
        error_log("Error de conexión a BD: " . $e->getMessage());
        
        // Mensaje más específico para el usuario
        if (strpos($e->getMessage(), 'Access denied') !== false) {
            $_SESSION['error'] = 'Error de acceso a la base de datos. Verifique las credenciales.';
        } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
            $_SESSION['error'] = 'Base de datos no encontrada. Verifique la configuración.';
        } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
            $_SESSION['error'] = 'No se puede conectar al servidor de base de datos.';
        } else {
            $_SESSION['error'] = 'Error de conexión: ' . $e->getMessage();
        }
        
        header('Location: index.php');
        exit();
    }
} else {
    header('Location: index.php');
    exit();
}
?>
