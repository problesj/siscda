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
            $error_imagen = null;
            
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
                $resultado_imagen = procesarImagen($_FILES['imagen']);
                if (is_array($resultado_imagen) && isset($resultado_imagen['error'])) {
                    $error_imagen = $resultado_imagen['error'];
                } else {
                    $url_imagen = $resultado_imagen;
                }
            } elseif (isset($_FILES['imagen']) && $_FILES['imagen']['error'] != 0) {
                $error_imagen = obtenerMensajeErrorArchivo($_FILES['imagen']['error']);
            }
            
            // Si hay error en la imagen, no continuar
            if ($error_imagen) {
                $_SESSION['error'] = $error_imagen;
                header('Location: personas.php');
                exit();
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
                $resultado_imagen = procesarImagen($_FILES['imagen']);
                if (is_array($resultado_imagen) && isset($resultado_imagen['error'])) {
                    $_SESSION['error'] = $resultado_imagen['error'];
                    header('Location: personas.php');
                    exit();
                }
                
                // Eliminar imagen anterior si existe
                if ($url_imagen && file_exists('..' . $url_imagen)) {
                    unlink('..' . $url_imagen);
                }
                $url_imagen = $resultado_imagen;
            } elseif (isset($_FILES['imagen']) && $_FILES['imagen']['error'] != 0) {
                $_SESSION['error'] = obtenerMensajeErrorArchivo($_FILES['imagen']['error']);
                header('Location: personas.php');
                exit();
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

/**
 * Obtiene mensaje de error específico para errores de archivo de PHP
 * @param int $codigo_error Código de error de $_FILES['imagen']['error']
 * @return string Mensaje de error descriptivo
 */
function obtenerMensajeErrorArchivo($codigo_error) {
    switch ($codigo_error) {
        case UPLOAD_ERR_INI_SIZE:
            return 'El archivo excede el tamaño máximo permitido por el servidor (php.ini).';
        case UPLOAD_ERR_FORM_SIZE:
            return 'El archivo excede el tamaño máximo permitido por el formulario HTML.';
        case UPLOAD_ERR_PARTIAL:
            return 'El archivo se subió solo parcialmente. Intenta nuevamente.';
        case UPLOAD_ERR_NO_FILE:
            return 'No se seleccionó ningún archivo.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Error del servidor: No existe directorio temporal.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Error del servidor: No se pudo escribir el archivo en disco.';
        case UPLOAD_ERR_EXTENSION:
            return 'Error del servidor: Una extensión de PHP detuvo la subida del archivo.';
        default:
            return 'Error desconocido al subir el archivo. Código: ' . $codigo_error;
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
    // Verificar que sea un archivo válido
    if (!is_uploaded_file($archivo['tmp_name'])) {
        return ['error' => 'Archivo no válido o no fue subido correctamente.'];
    }
    
    // Verificar tipo de archivo
    $tipos_permitidos = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!in_array($archivo['type'], $tipos_permitidos)) {
        return ['error' => 'Tipo de archivo no permitido. Solo se permiten JPG y PNG. El archivo subido es: ' . $archivo['type']];
    }
    
    // Verificar extensión del archivo
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $extensiones_permitidas = ['jpg', 'jpeg', 'png'];
    if (!in_array($extension, $extensiones_permitidas)) {
        return ['error' => 'Extensión de archivo no permitida. Solo se permiten: ' . implode(', ', $extensiones_permitidas) . '. El archivo subido tiene extensión: ' . $extension];
    }
    
    // Verificar tamaño (500KB máximo)
    $tamanio_maximo = 500 * 1024; // 500KB en bytes
    if ($archivo['size'] > $tamanio_maximo) {
        $tamanio_mb = round($archivo['size'] / (1024 * 1024), 2);
        return ['error' => 'El archivo es demasiado grande. Tamaño máximo: 500KB. El archivo subido tiene: ' . $tamanio_mb . 'MB'];
    }
    
    // Verificar que el archivo no esté vacío
    if ($archivo['size'] == 0) {
        return ['error' => 'El archivo está vacío. Selecciona una imagen válida.'];
    }
    
    // Crear directorio si no existe
    $directorio_destino = '../assets/images/personas/';
    if (!is_dir($directorio_destino)) {
        if (!mkdir($directorio_destino, 0755, true)) {
            return ['error' => 'No se pudo crear el directorio de destino para las imágenes.'];
        }
    }
    
    // Verificar permisos de escritura
    if (!is_writable($directorio_destino)) {
        return ['error' => 'No hay permisos de escritura en el directorio de imágenes.'];
    }
    
    // Generar nombre único para el archivo
    $nombre_archivo = uniqid() . '_' . time() . '.' . $extension;
    $ruta_completa = $directorio_destino . $nombre_archivo;
    
    // Mover archivo
    if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
        // Verificar que el archivo se movió correctamente
        if (file_exists($ruta_completa)) {
            // Retornar ruta relativa para la base de datos
            return 'assets/images/personas/' . $nombre_archivo;
        } else {
            return ['error' => 'Error: El archivo no se guardó correctamente en el servidor.'];
        }
    } else {
        return ['error' => 'Error al mover el archivo subido. Verifica los permisos del servidor.'];
    }
}
?>
