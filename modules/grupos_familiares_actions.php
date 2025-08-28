<?php
require_once '../session_config.php';
session_start();
require_once '../config.php';
require_once dirname(__DIR__) . '/includes/auth_functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    try {
        $pdo = conectarDB();
        
        if ($action == 'crear') {
            $nombre = limpiarDatos($_POST['nombre']);
            $descripcion = limpiarDatos($_POST['descripcion']);
            
            $stmt = $pdo->prepare("INSERT INTO grupos_familiares (NOMBRE, DESCRIPCION, FECHA_CREACION, FECHA_ACTUALIZACION) VALUES (?, ?, NOW(), NOW())");
            $stmt->execute([$nombre, $descripcion]);
            
            $_SESSION['success'] = 'Grupo familiar creado exitosamente';
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
            $stmt = $pdo->prepare("DELETE FROM grupos_familiares WHERE ID = ?");
            $stmt->execute([$id]);
            
            $_SESSION['success'] = 'Grupo familiar eliminado exitosamente';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
    }
}

header('Location: grupos_familiares.php');
exit();
?>
