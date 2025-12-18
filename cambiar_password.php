<?php
require_once 'session_config.php';
session_start();
require_once 'config.php';
require_once 'includes/auth_functions.php';

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

// Verificar que se haya enviado el formulario
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: perfil.php');
    exit();
}

// Verificar que todos los campos estén presentes
if (!isset($_POST['password_actual']) || !isset($_POST['password_nueva']) || !isset($_POST['password_confirmar'])) {
    $_SESSION['error'] = 'Todos los campos son requeridos.';
    header('Location: perfil.php');
    exit();
}

$password_actual = $_POST['password_actual'];
$password_nueva = $_POST['password_nueva'];
$password_confirmar = $_POST['password_confirmar'];

// Validaciones
if (empty($password_actual) || empty($password_nueva) || empty($password_confirmar)) {
    $_SESSION['error'] = 'Todos los campos deben estar completos.';
    header('Location: perfil.php');
    exit();
}

if ($password_nueva !== $password_confirmar) {
    $_SESSION['error'] = 'Las contraseñas nuevas no coinciden.';
    header('Location: perfil.php');
    exit();
}

if (strlen($password_nueva) < 6) {
    $_SESSION['error'] = 'La nueva contraseña debe tener al menos 6 caracteres.';
    header('Location: perfil.php');
    exit();
}

try {
    $pdo = conectarDB();
    
    // Verificar la contraseña actual
    $stmt = $pdo->prepare("SELECT PASSWORD FROM usuarios WHERE USUARIO_ID = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        $_SESSION['error'] = 'Usuario no encontrado.';
        header('Location: perfil.php');
        exit();
    }
    
    // Verificar que la contraseña actual sea correcta
    if (!password_verify($password_actual, $usuario['PASSWORD'])) {
        $_SESSION['error'] = 'La contraseña actual es incorrecta.';
        header('Location: perfil.php');
        exit();
    }
    
    // Verificar que la nueva contraseña sea diferente a la actual
    if (password_verify($password_nueva, $usuario['PASSWORD'])) {
        $_SESSION['error'] = 'La nueva contraseña debe ser diferente a la actual.';
        header('Location: perfil.php');
        exit();
    }
    
    // Generar el hash de la nueva contraseña
    $password_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
    
    // Actualizar la contraseña en la base de datos
    $stmt = $pdo->prepare("UPDATE usuarios SET PASSWORD = ?, FECHA_ACTUALIZACION = NOW() WHERE USUARIO_ID = ?");
    $stmt->execute([$password_hash, $_SESSION['usuario_id']]);
    
    if ($stmt->rowCount() > 0) {
        // Registrar el cambio en el log
        error_log("Usuario {$_SESSION['username']} (ID: {$_SESSION['usuario_id']}) cambió su contraseña exitosamente.");
        
        $_SESSION['success'] = 'Contraseña cambiada exitosamente.';
        
        // Opcional: cerrar sesión para forzar nuevo login
        // session_destroy();
        // header('Location: index.php?msg=password_changed');
        // exit();
        
    } else {
        $_SESSION['error'] = 'No se pudo actualizar la contraseña. Intenta nuevamente.';
    }
    
} catch (PDOException $e) {
    error_log("Error al cambiar contraseña: " . $e->getMessage());
    $_SESSION['error'] = 'Error interno del sistema. Intenta nuevamente más tarde.';
} catch (Exception $e) {
    error_log("Error general al cambiar contraseña: " . $e->getMessage());
    $_SESSION['error'] = 'Error interno del sistema. Intenta nuevamente más tarde.';
}

// Redirigir de vuelta al perfil
header('Location: perfil.php');
exit();
?>
