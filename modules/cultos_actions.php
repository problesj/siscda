<?php
require_once '../session_config.php';
session_start();
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    try {
        $pdo = conectarDB();
        
        if ($action == 'crear') {
            $fecha = $_POST['fecha'];
            $tipo_culto = limpiarDatos($_POST['tipo_culto']);
            $observaciones = limpiarDatos($_POST['observaciones']);
            
            $stmt = $pdo->prepare("INSERT INTO cultos (FECHA, TIPO_CULTO, OBSERVACIONES, FECHA_CREACION, FECHA_ACTUALIZACION) VALUES (?, ?, ?, NOW(), NOW())");
            $stmt->execute([$fecha, $tipo_culto, $observaciones]);
            
            $_SESSION['success'] = 'Culto creado exitosamente';
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
            $stmt = $pdo->prepare("DELETE FROM cultos WHERE ID = ?");
            $stmt->execute([$id]);
            
            $_SESSION['success'] = 'Culto eliminado exitosamente';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
    }
}

header('Location: cultos.php');
exit();
?>
