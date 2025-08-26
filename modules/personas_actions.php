<?php
require_once '../session_config.php';
session_start();
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    try {
        $pdo = conectarDB();
        
        if ($action == 'crear') {
            $rut = limpiarDatos($_POST['rut']);
            $nombres = limpiarDatos($_POST['nombres']);
            $apellido_paterno = limpiarDatos($_POST['apellido_paterno']);
            $apellido_materno = limpiarDatos($_POST['apellido_materno']);
            $sexo = limpiarDatos($_POST['sexo']);
            $fecha_nacimiento = !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null;
            $familia = limpiarDatos($_POST['familia']);
            $rol = !empty($_POST['rol']) ? $_POST['rol'] : null;
            $email = limpiarDatos($_POST['email']);
            $telefono = limpiarDatos($_POST['telefono']);
            $observaciones = limpiarDatos($_POST['observaciones']);
            $grupo_familiar_id = !empty($_POST['grupo_familiar_id']) ? $_POST['grupo_familiar_id'] : null;
            
            $stmt = $pdo->prepare("INSERT INTO personas (RUT, NOMBRES, APELLIDO_PATERNO, APELLIDO_MATERNO, SEXO, FECHA_NACIMIENTO, FAMILIA, ROL, EMAIL, TELEFONO, OBSERVACIONES, GRUPO_FAMILIAR_ID, FECHA_CREACION) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$rut, $nombres, $apellido_paterno, $apellido_materno, $sexo, $fecha_nacimiento, $familia, $rol, $email, $telefono, $observaciones, $grupo_familiar_id]);
            
            $_SESSION['success'] = 'Persona creada exitosamente';
        } elseif ($action == 'editar') {
            $persona_id = $_POST['persona_id'];
            $rut = limpiarDatos($_POST['rut']);
            $nombres = limpiarDatos($_POST['nombres']);
            $apellido_paterno = limpiarDatos($_POST['apellido_paterno']);
            $apellido_materno = limpiarDatos($_POST['apellido_materno']);
            $sexo = limpiarDatos($_POST['sexo']);
            $fecha_nacimiento = !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null;
            $familia = limpiarDatos($_POST['familia']);
            $rol = !empty($_POST['rol']) ? $_POST['rol'] : null;
            $email = limpiarDatos($_POST['email']);
            $telefono = limpiarDatos($_POST['telefono']);
            $observaciones = limpiarDatos($_POST['observaciones']);
            $grupo_familiar_id = !empty($_POST['grupo_familiar_id']) ? $_POST['grupo_familiar_id'] : null;
            
            $stmt = $pdo->prepare("UPDATE personas SET RUT = ?, NOMBRES = ?, APELLIDO_PATERNO = ?, APELLIDO_MATERNO = ?, SEXO = ?, FECHA_NACIMIENTO = ?, FAMILIA = ?, ROL = ?, EMAIL = ?, TELEFONO = ?, OBSERVACIONES = ?, GRUPO_FAMILIAR_ID = ?, FECHA_ACTUALIZACION = NOW() WHERE ID = ?");
            $stmt->execute([$rut, $nombres, $apellido_paterno, $apellido_materno, $sexo, $fecha_nacimiento, $familia, $rol, $email, $telefono, $observaciones, $grupo_familiar_id, $persona_id]);
            
            $_SESSION['success'] = 'Persona actualizada exitosamente';
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
            $stmt = $pdo->prepare("DELETE FROM personas WHERE ID = ?");
            $stmt->execute([$id]);
            
            $_SESSION['success'] = 'Persona eliminada exitosamente';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
    } elseif ($action == 'obtener') {
        $id = $_GET['id'];
        
        try {
            $pdo = conectarDB();
            $stmt = $pdo->prepare("SELECT ID as id, RUT, NOMBRES, APELLIDO_PATERNO, APELLIDO_MATERNO, SEXO, FECHA_NACIMIENTO, FAMILIA, ROL, EMAIL, TELEFONO, OBSERVACIONES, GRUPO_FAMILIAR_ID, FECHA_CREACION, FECHA_ACTUALIZACION FROM personas WHERE ID = ?");
            $stmt->execute([$id]);
            $persona = $stmt->fetch();
            
            if ($persona) {
                echo json_encode(['success' => true, 'persona' => $persona]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Persona no encontrada']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit();
    }
}

header('Location: personas.php');
exit();
?>
