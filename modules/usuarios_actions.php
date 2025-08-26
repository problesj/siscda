<?php
require_once '../session_config.php';
session_start();
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    try {
        $pdo = conectarDB();
        
        if ($action == 'crear') {
            $username = limpiarDatos($_POST['username']);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $nombre_completo = limpiarDatos($_POST['nombre_completo']);
            $email = limpiarDatos($_POST['email']);
            $activo = isset($_POST['activo']) ? (int)$_POST['activo'] : 1;
            
            $stmt = $pdo->prepare("INSERT INTO usuarios (USERNAME, PASSWORD, NOMBRE_COMPLETO, EMAIL, ACTIVO, FECHA_CREACION, FECHA_ACTUALIZACION) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$username, $password, $nombre_completo, $email, $activo]);
            
            $_SESSION['success'] = 'Usuario creado exitosamente';
        } elseif ($action == 'editar') {
            $usuario_id = (int)$_POST['usuario_id'];
            $username = limpiarDatos($_POST['username']);
            $nombre_completo = limpiarDatos($_POST['nombre_completo']);
            $email = limpiarDatos($_POST['email']);
            $activo = isset($_POST['activo']) ? (int)$_POST['activo'] : 1;
            
            // Si se proporciona una nueva contraseña, actualizarla
            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE usuarios SET USERNAME = ?, PASSWORD = ?, NOMBRE_COMPLETO = ?, EMAIL = ?, ACTIVO = ?, FECHA_ACTUALIZACION = NOW() WHERE USUARIO_ID = ?");
                $stmt->execute([$username, $password, $nombre_completo, $email, $activo, $usuario_id]);
            } else {
                // Solo actualizar otros campos, mantener contraseña actual
                $stmt = $pdo->prepare("UPDATE usuarios SET USERNAME = ?, NOMBRE_COMPLETO = ?, EMAIL = ?, ACTIVO = ?, FECHA_ACTUALIZACION = NOW() WHERE USUARIO_ID = ?");
                $stmt->execute([$username, $nombre_completo, $email, $activo, $usuario_id]);
            }
            
            $_SESSION['success'] = 'Usuario actualizado exitosamente';
        }
        
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $action = $_GET['action'];
    
    if ($action == 'obtener') {
        $id = (int)$_GET['id'];
        
        try {
            $pdo = conectarDB();
            $stmt = $pdo->prepare("SELECT USUARIO_ID, USERNAME, NOMBRE_COMPLETO, EMAIL, ACTIVO FROM usuarios WHERE USUARIO_ID = ?");
            $stmt->execute([$id]);
            $usuario = $stmt->fetch();
            
            if ($usuario) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'usuario' => $usuario]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
            }
        } catch (PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit();
    } elseif ($action == 'eliminar') {
        $id = $_GET['id'];
        
        try {
            $pdo = conectarDB();
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE USUARIO_ID = ?");
            $stmt->execute([$id]);
            
            $_SESSION['success'] = 'Usuario eliminado exitosamente';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
    }
}

header('Location: usuarios.php');
exit();
?>
