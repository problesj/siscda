<?php
require_once '../session_config.php';
session_start();
require_once '../config.php';
require_once dirname(__DIR__) . '/includes/auth_functions.php';

// Verificar autenticación
verificarAutenticacion();

// Verificar acceso al módulo de Cultos
verificarAccesoModulo('Cultos');

// Verificar si el usuario es Administrador del módulo
$esAdministrador = esAdministradorModulo($_SESSION['usuario_id'], 'Cultos');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    try {
        $pdo = conectarDB();
        
        if ($action == 'crear') {
            // Solo administradores pueden crear
            if (!$esAdministrador) {
                $_SESSION['error'] = 'No tienes permisos para crear cultos';
                header('Location: cultos.php');
                exit();
            }
            $fecha = $_POST['fecha'];
            $tipo_culto = limpiarDatos($_POST['tipo_culto']);
            $observaciones = limpiarDatos($_POST['observaciones']);
            
            $pdo->beginTransaction();
            
            try {
                // Crear el culto
                $stmt = $pdo->prepare("INSERT INTO cultos (FECHA, TIPO_CULTO, OBSERVACIONES, FECHA_CREACION, FECHA_ACTUALIZACION) VALUES (?, ?, ?, NOW(), NOW())");
                $stmt->execute([$fecha, $tipo_culto, $observaciones]);
                $cultoId = $pdo->lastInsertId();
                
                // Crear la ofrenda asociada al culto con todas las denominaciones en 0
                $stmt = $pdo->prepare("INSERT INTO ofrendas (monto, fecha_ofrenda, id_culto, fecha_modificacion, `20000`, `10000`, `5000`, `2000`, `1000`, `500`, `100`, `50`, `10`) VALUES (0, ?, ?, NOW(), 0, 0, 0, 0, 0, 0, 0, 0, 0)");
                $stmt->execute([$fecha, $cultoId]);
                
                $pdo->commit();
                $_SESSION['success'] = 'Culto creado exitosamente';
            } catch (PDOException $e) {
                $pdo->rollBack();
                throw $e;
            }
        } elseif ($action == 'editar') {
            // Solo administradores pueden editar
            if (!$esAdministrador) {
                $_SESSION['error'] = 'No tienes permisos para editar cultos';
                header('Location: cultos.php');
                exit();
            }
            $id = $_POST['id'];
            $fecha = $_POST['fecha'];
            $tipo_culto = limpiarDatos($_POST['tipo_culto']);
            $observaciones = limpiarDatos($_POST['observaciones']);
            
            $stmt = $pdo->prepare("UPDATE cultos SET FECHA = ?, TIPO_CULTO = ?, OBSERVACIONES = ?, FECHA_ACTUALIZACION = NOW() WHERE ID = ?");
            $stmt->execute([$fecha, $tipo_culto, $observaciones, $id]);
            
            $_SESSION['success'] = 'Culto actualizado exitosamente';
        }
        
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    
    header('Location: cultos.php');
    exit();
    
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $action = $_GET['action'] ?? '';
    
    if ($action == 'obtener') {
        $id = $_GET['id'] ?? 0;
        
        try {
            $pdo = conectarDB();
            $stmt = $pdo->prepare("SELECT * FROM cultos WHERE ID = ?");
            $stmt->execute([$id]);
            $culto = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($culto) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'culto' => $culto
                ]);
            } else {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Culto no encontrado'
                ]);
            }
        } catch (PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener el culto: ' . $e->getMessage()
            ]);
        }
        exit();
        
    } elseif ($action == 'eliminar') {
        // Solo administradores pueden eliminar
        if (!$esAdministrador) {
            $_SESSION['error'] = 'No tienes permisos para eliminar cultos';
            header('Location: cultos.php');
            exit();
        }
        $id = $_GET['id'];
        
        try {
            $pdo = conectarDB();
            $stmt = $pdo->prepare("DELETE FROM cultos WHERE ID = ?");
            $stmt->execute([$id]);
            
            $_SESSION['success'] = 'Culto eliminado exitosamente';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
        
        header('Location: cultos.php');
        exit();
    }
}
?>
