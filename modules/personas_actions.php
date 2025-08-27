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
            
            // Procesar imagen si se subió una
            $url_imagen = null;
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
                $url_imagen = procesarImagen($_FILES['imagen']);
            }
            
            $stmt = $pdo->prepare("INSERT INTO personas (RUT, NOMBRES, APELLIDO_PATERNO, APELLIDO_MATERNO, SEXO, FECHA_NACIMIENTO, FAMILIA, ROL, EMAIL, TELEFONO, OBSERVACIONES, GRUPO_FAMILIAR_ID, URL_IMAGEN, FECHA_CREACION) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$rut, $nombres, $apellido_paterno, $apellido_materno, $sexo, $fecha_nacimiento, $familia, $rol, $email, $telefono, $observaciones, $grupo_familiar_id, $url_imagen]);
            
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
            
            // Obtener imagen actual
            $stmt = $pdo->prepare("SELECT URL_IMAGEN FROM personas WHERE ID = ?");
            $stmt->execute([$persona_id]);
            $persona_actual = $stmt->fetch();
            $url_imagen = $persona_actual['URL_IMAGEN'];
            
            // Procesar nueva imagen si se subió una
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
                // Eliminar imagen anterior si existe
                if ($url_imagen && file_exists('..' . $url_imagen)) {
                    unlink('..' . $url_imagen);
                }
                $url_imagen = procesarImagen($_FILES['imagen']);
            }
            
            $stmt = $pdo->prepare("UPDATE personas SET RUT = ?, NOMBRES = ?, APELLIDO_PATERNO = ?, APELLIDO_MATERNO = ?, SEXO = ?, FECHA_NACIMIENTO = ?, FAMILIA = ?, ROL = ?, EMAIL = ?, TELEFONO = ?, OBSERVACIONES = ?, GRUPO_FAMILIAR_ID = ?, URL_IMAGEN = ?, FECHA_ACTUALIZACION = NOW() WHERE ID = ?");
            $stmt->execute([$rut, $nombres, $apellido_paterno, $apellido_materno, $sexo, $fecha_nacimiento, $familia, $rol, $email, $telefono, $observaciones, $grupo_familiar_id, $url_imagen, $persona_id]);
            
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
            
            // Obtener imagen antes de eliminar
            $stmt = $pdo->prepare("SELECT URL_IMAGEN FROM personas WHERE ID = ?");
            $stmt->execute([$id]);
            $persona = $stmt->fetch();
            
            // Eliminar imagen si existe
            if ($persona && $persona['URL_IMAGEN'] && file_exists('..' . $persona['URL_IMAGEN'])) {
                unlink('..' . $persona['URL_IMAGEN']);
            }
            
            // Eliminar persona
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
            $stmt = $pdo->prepare("SELECT ID as id, RUT, NOMBRES, APELLIDO_PATERNO, APELLIDO_MATERNO, SEXO, FECHA_NACIMIENTO, FAMILIA, ROL, EMAIL, TELEFONO, OBSERVACIONES, GRUPO_FAMILIAR_ID, URL_IMAGEN, FECHA_CREACION, FECHA_ACTUALIZACION FROM personas WHERE ID = ?");
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

/**
 * Procesa y guarda la imagen subida
 * @param array $archivo Archivo subido ($_FILES['imagen'])
 * @return string|null Ruta de la imagen guardada o null si hay error
 */
function procesarImagen($archivo) {
    // Verificar tipo de archivo
    $tipos_permitidos = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!in_array($archivo['type'], $tipos_permitidos)) {
        $_SESSION['error'] = 'Tipo de archivo no permitido. Solo se permiten JPG y PNG.';
        return null;
    }
    
    // Verificar tamaño (500KB máximo)
    if ($archivo['size'] > 500 * 1024) {
        $_SESSION['error'] = 'El archivo es demasiado grande. Máximo 500KB.';
        return null;
    }
    
    // Crear directorio si no existe
    $directorio_destino = '../assets/images/personas/';
    if (!is_dir($directorio_destino)) {
        mkdir($directorio_destino, 0755, true);
    }
    
    // Generar nombre único para el archivo
    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    $nombre_archivo = uniqid() . '.' . $extension;
    $ruta_completa = $directorio_destino . $nombre_archivo;
    
    // Mover archivo
    if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
        // Retornar ruta relativa para la base de datos
        return 'assets/images/personas/' . $nombre_archivo;
    } else {
        $_SESSION['error'] = 'Error al subir la imagen.';
        return null;
    }
}
?>
