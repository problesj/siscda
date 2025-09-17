<?php
// Archivo de acciones para el módulo de personas
require_once dirname(__DIR__) . '/session_config.php';
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth_functions.php';

// Verificar autenticación
verificarAutenticacion();

// Obtener la acción solicitada
$action = $_REQUEST['action'] ?? '';
    
    try {
        $pdo = conectarDB();
        
    switch ($action) {
        case 'obtener':
            // Obtener datos de una persona específica o solo roles/grupos si ID es 0
            $id = $_GET['id'] ?? 0;
            
            // Obtener roles disponibles para el formulario
            $stmtRoles = $pdo->query("SELECT id, nombre_rol FROM roles ORDER BY nombre_rol");
            $roles = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);
            
            // Obtener grupos familiares disponibles
            $stmtGrupos = $pdo->query("SELECT ID, NOMBRE FROM grupos_familiares ORDER BY NOMBRE");
            $gruposFamiliares = $stmtGrupos->fetchAll(PDO::FETCH_ASSOC);
            
            if ($id == 0) {
                // Solo retornar roles y grupos familiares para formularios nuevos
                echo json_encode([
                    'success' => true,
                    'persona' => null,
                    'roles' => $roles,
                    'gruposFamiliares' => $gruposFamiliares
                ]);
                break;
            }
            
            $sql = "SELECT p.*, gf.NOMBRE as GRUPO_FAMILIAR_NOMBRE, r.nombre_rol as ROL_NOMBRE 
                    FROM personas p 
                    LEFT JOIN grupos_familiares gf ON p.GRUPO_FAMILIAR_ID = gf.ID 
                    LEFT JOIN roles r ON p.ROL = r.id 
                    WHERE p.ID = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $persona = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$persona) {
                throw new Exception('Persona no encontrada');
            }
            
            echo json_encode([
                'success' => true,
                'persona' => $persona,
                'roles' => $roles,
                'gruposFamiliares' => $gruposFamiliares
            ]);
            break;
            
        case 'crear':
            // Crear nueva persona
            $datos = [
                'RUT' => $_POST['rut'] ?? null,
                'NOMBRES' => $_POST['nombres'] ?? '',
                'APELLIDO_PATERNO' => $_POST['apellido_paterno'] ?? '',
                'APELLIDO_MATERNO' => $_POST['apellido_materno'] ?? null,
                'SEXO' => $_POST['sexo'] ?? null,
                'FECHA_NACIMIENTO' => $_POST['fecha_nacimiento'] ?: null,
                'TELEFONO' => $_POST['telefono'] ?? null,
                'FAMILIA' => $_POST['familia'] ?? null,
                'EMAIL' => $_POST['email'] ?? null,
                'ROL' => $_POST['rol'] ?? null,
                'GRUPO_FAMILIAR_ID' => $_POST['grupo_familiar_id'] ?? null,
                'OBSERVACIONES' => $_POST['observaciones'] ?? null,
                'FECHA_CREACION' => date('Y-m-d H:i:s')
            ];
            
            // Validar campos obligatorios
            if (empty($datos['NOMBRES']) || empty($datos['APELLIDO_PATERNO'])) {
                throw new Exception('Los campos Nombres y Apellido Paterno son obligatorios');
            }
            
            // Procesar imagen si se subió
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $imagen = procesarImagen($_FILES['imagen']);
                if ($imagen) {
                    $datos['URL_IMAGEN'] = $imagen;
                }
            }
            
            // Filtrar solo los campos que no son null para la inserción
            $datosInsert = array_filter($datos, function($valor) {
                return $valor !== null && $valor !== '';
            });
            
            // Insertar en la base de datos
            $campos = implode(', ', array_keys($datosInsert));
            $placeholders = ':' . implode(', :', array_keys($datosInsert));
            
            $sql = "INSERT INTO personas ($campos) VALUES ($placeholders)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($datosInsert);
            
            $nuevoId = $pdo->lastInsertId();
            
            $_SESSION['success'] = "Persona creada exitosamente con ID: $nuevoId";
            header('Location: personas.php');
            exit();
            break;
            
        case 'editar':
            // Editar persona existente
            $id = $_POST['persona_id'] ?? 0;
            if (!$id) {
                throw new Exception('ID de persona no proporcionado');
            }
            
            $datos = [
                'RUT' => $_POST['rut'] ?? null,
                'NOMBRES' => $_POST['nombres'] ?? '',
                'APELLIDO_PATERNO' => $_POST['apellido_paterno'] ?? '',
                'APELLIDO_MATERNO' => $_POST['apellido_materno'] ?? null,
                'SEXO' => $_POST['sexo'] ?? null,
                'FECHA_NACIMIENTO' => $_POST['fecha_nacimiento'] ?: null,
                'TELEFONO' => $_POST['telefono'] ?? null,
                'FAMILIA' => $_POST['familia'] ?? null,
                'EMAIL' => $_POST['email'] ?? null,
                'ROL' => $_POST['rol'] ?? null,
                'GRUPO_FAMILIAR_ID' => $_POST['grupo_familiar_id'] ?? null,
                'OBSERVACIONES' => $_POST['observaciones'] ?? null,
                'FECHA_ACTUALIZACION' => date('Y-m-d H:i:s')
            ];
            
            // Validar campos obligatorios
            if (empty($datos['NOMBRES']) || empty($datos['APELLIDO_PATERNO'])) {
                throw new Exception('Los campos Nombres y Apellido Paterno son obligatorios');
            }
            
            // Procesar imagen si se subió
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $imagen = procesarImagen($_FILES['imagen']);
                if ($imagen) {
                    $datos['URL_IMAGEN'] = $imagen;
                }
            }
            
            // Filtrar solo los campos que no son null para la actualización
            $datosUpdate = array_filter($datos, function($valor) {
                return $valor !== null && $valor !== '';
            });
            
            // Actualizar en la base de datos
            $campos = [];
            foreach ($datosUpdate as $campo => $valor) {
                $campos[] = "$campo = :$campo";
            }
            
            $sql = "UPDATE personas SET " . implode(', ', $campos) . " WHERE ID = :id";
            $datosUpdate['id'] = $id;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($datosUpdate);
            
            $_SESSION['success'] = "Persona actualizada exitosamente";
            header('Location: personas.php');
            exit();
            break;
            
        case 'obtener_imagen':
            // Obtener la URL de la imagen de una persona
            $persona_id = $_POST['persona_id'] ?? 0;
            if (!$persona_id) {
                throw new Exception('ID de persona no proporcionado');
            }
            
            // Obtener la URL de la imagen
            $stmt = $pdo->prepare("SELECT URL_IMAGEN FROM personas WHERE ID = ?");
            $stmt->execute([$persona_id]);
            $imagen_url = $stmt->fetchColumn();
            
            if ($imagen_url) {
                echo json_encode([
                    'success' => true,
                    'imagen_url' => $imagen_url
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se encontró imagen para esta persona'
                ]);
            }
            exit();
            break;
            
        case 'eliminar':
            // Eliminar persona
            $id = $_GET['id'] ?? 0;
            if (!$id) {
                throw new Exception('ID de persona no proporcionado');
            }
            
            // Verificar si la persona existe
            $stmt = $pdo->prepare("SELECT ID, NOMBRES, APELLIDO_PATERNO FROM personas WHERE ID = ?");
            $stmt->execute([$id]);
            $persona = $stmt->fetch();
            
            if (!$persona) {
                throw new Exception('Persona no encontrada');
            }
            
            // Eliminar imagen si existe
            $stmt = $pdo->prepare("SELECT URL_IMAGEN FROM personas WHERE ID = ?");
            $stmt->execute([$id]);
            $imagen = $stmt->fetchColumn();
            
            if ($imagen && file_exists(dirname(__DIR__) . '/' . $imagen)) {
                unlink(dirname(__DIR__) . '/' . $imagen);
            }
            
            // Eliminar de la base de datos
            $stmt = $pdo->prepare("DELETE FROM personas WHERE ID = ?");
            $stmt->execute([$id]);
            
            $_SESSION['success'] = "Persona {$persona['NOMBRES']} {$persona['APELLIDO_PATERNO']} eliminada exitosamente";
            header('Location: personas.php');
        exit();
            break;
            
        case 'obtener_grupos':
            $stmt = $pdo->query("
                SELECT gf.*, COUNT(p.ID) as miembros 
                FROM grupos_familiares gf 
                LEFT JOIN personas p ON gf.ID = p.GRUPO_FAMILIAR_ID 
                GROUP BY gf.ID 
                ORDER BY gf.NOMBRE
            ");
            $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'grupos' => $grupos
            ]);
            break;
            
        case 'crear_grupo':
            $nombre = $_POST['nombre'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            
            if (empty($nombre)) {
                throw new Exception('El nombre del grupo es requerido');
            }
            
            $stmt = $pdo->prepare("INSERT INTO grupos_familiares (NOMBRE, DESCRIPCION) VALUES (?, ?)");
            $stmt->execute([$nombre, $descripcion]);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Grupo familiar creado exitosamente'
            ]);
            break;
            
        case 'editar_grupo':
            $id = $_POST['id'] ?? 0;
            $nombre = $_POST['nombre'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            
            if (empty($nombre)) {
                throw new Exception('El nombre del grupo es requerido');
            }
            
            $stmt = $pdo->prepare("UPDATE grupos_familiares SET NOMBRE = ?, DESCRIPCION = ? WHERE ID = ?");
            $stmt->execute([$nombre, $descripcion, $id]);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Grupo familiar actualizado exitosamente'
            ]);
            break;
            
        case 'eliminar_grupo':
            $id = $_POST['id'] ?? 0;
            
            // Verificar si hay personas asignadas a este grupo
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM personas WHERE GRUPO_FAMILIAR_ID = ?");
            $stmt->execute([$id]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                throw new Exception('No se puede eliminar el grupo porque tiene personas asignadas');
            }
            
            $stmt = $pdo->prepare("DELETE FROM grupos_familiares WHERE ID = ?");
            $stmt->execute([$id]);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Grupo familiar eliminado exitosamente'
            ]);
            break;
            
        case 'obtener_roles':
            $stmt = $pdo->query("SELECT * FROM roles ORDER BY nombre_rol");
            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'roles' => $roles
            ]);
            break;
            
        case 'crear_rol':
            $nombre_rol = $_POST['nombre_rol'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            
            if (empty($nombre_rol)) {
                throw new Exception('El nombre del rol es requerido');
            }
            
            $stmt = $pdo->prepare("INSERT INTO roles (nombre_rol, descripcion) VALUES (?, ?)");
            $stmt->execute([$nombre_rol, $descripcion]);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Rol creado exitosamente'
            ]);
            break;
            
        case 'editar_rol':
            $id = $_POST['id'] ?? 0;
            $nombre_rol = $_POST['nombre_rol'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            
            if (empty($nombre_rol)) {
                throw new Exception('El nombre del rol es requerido');
            }
            
            $stmt = $pdo->prepare("UPDATE roles SET nombre_rol = ?, descripcion = ? WHERE id = ?");
            $stmt->execute([$nombre_rol, $descripcion, $id]);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Rol actualizado exitosamente'
            ]);
            break;
            
        case 'eliminar_rol':
            $id = $_POST['id'] ?? 0;
            
            // Verificar si hay personas asignadas a este rol
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM personas WHERE ROL = ?");
            $stmt->execute([$id]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                throw new Exception('No se puede eliminar el rol porque tiene personas asignadas');
            }
            
            $stmt = $pdo->prepare("DELETE FROM roles WHERE id = ?");
            $stmt->execute([$id]);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Rol eliminado exitosamente'
            ]);
            break;
            
        default:
            throw new Exception('Acción no válida');
}

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
header('Location: personas.php');
exit();
}

/**
 * Función para procesar y guardar imágenes subidas
 */
function procesarImagen($archivo) {
    $directorioDestino = dirname(__DIR__) . '/uploads/personas/';
    
    // Crear directorio si no existe
    if (!is_dir($directorioDestino)) {
        mkdir($directorioDestino, 0755, true);
    }
    
    // Validar tipo de archivo
    $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!in_array($archivo['type'], $tiposPermitidos)) {
        throw new Exception('Tipo de archivo no permitido. Solo se permiten JPG y PNG.');
    }
    
    // Validar tamaño (500KB máximo)
    $tamanioMaximo = 500 * 1024;
    if ($archivo['size'] > $tamanioMaximo) {
        throw new Exception('El archivo es demasiado grande. Tamaño máximo: 500KB');
    }
    
    // Generar nombre único
    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    $nombreArchivo = uniqid() . '_' . time() . '.' . $extension;
    $rutaCompleta = $directorioDestino . $nombreArchivo;
    
    // Mover archivo
    if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
        throw new Exception('Error al guardar la imagen');
    }
    
    // Retornar ruta relativa
    return 'uploads/personas/' . $nombreArchivo;
}
?>
