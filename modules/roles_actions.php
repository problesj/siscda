<?php
require_once '../session_config.php';
session_start();
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    try {
        $pdo = conectarDB();
        
        if ($action == 'crear') {
            $nombre_rol = limpiarDatos($_POST['nombre_rol']);
            $descripcion = isset($_POST['descripcion']) ? limpiarDatos($_POST['descripcion']) : '';
            
            $stmt = $pdo->prepare("INSERT INTO roles (nombre_rol, descripcion) VALUES (?, ?)");
            $stmt->execute([$nombre_rol, $descripcion]);
            
            $_SESSION['success'] = 'Rol creado exitosamente';
        } elseif ($action == 'editar') {
            $rol_id = $_POST['rol_id'];
            $nombre_rol = limpiarDatos($_POST['nombre_rol']);
            $descripcion = isset($_POST['descripcion']) ? limpiarDatos($_POST['descripcion']) : '';
            
            $stmt = $pdo->prepare("UPDATE roles SET nombre_rol = ?, descripcion = ? WHERE id = ?");
            $stmt->execute([$nombre_rol, $descripcion, $rol_id]);
            
            $_SESSION['success'] = 'Rol actualizado exitosamente';
        }
        
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $action = $_GET['action'];
    
    if ($action == 'eliminar') {
        $id = $_GET['id'];
        
        try {
            $pdo = conectarDB();
            
            // Verificar si hay personas usando este rol
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM personas WHERE ROL = ?");
            $stmt->execute([$id]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                $_SESSION['error'] = 'No se puede eliminar el rol porque hay ' . $count . ' persona(s) asignada(s)';
            } else {
                            $stmt = $pdo->prepare("DELETE FROM roles WHERE id = ?");
            $stmt->execute([$id]);
                
                $_SESSION['success'] = 'Rol eliminado exitosamente';
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
    } elseif ($action == 'obtener') {
        $id = $_GET['id'];
        
        try {
            $pdo = conectarDB();
            $stmt = $pdo->prepare("SELECT id, nombre_rol, descripcion FROM roles WHERE id = ?");
            $stmt->execute([$id]);
            $rol = $stmt->fetch();
            
            if ($rol) {
                echo json_encode(['success' => true, 'rol' => $rol]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Rol no encontrado']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit();
    }
}

header('Location: roles.php');
exit();
?>
