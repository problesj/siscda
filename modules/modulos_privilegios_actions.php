<?php
require_once dirname(__DIR__) . '/session_config.php';
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth_functions.php';

// Verificar autenticación
verificarAutenticacion();

// Verificar que solo el usuario admin pueda acceder
if (!isset($_SESSION['username']) || strtolower($_SESSION['username']) !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'No tienes permisos para realizar esta acción'
    ]);
    exit();
}

// Obtener la acción solicitada
$action = $_REQUEST['action'] ?? '';

header('Content-Type: application/json');

try {
    $pdo = conectarDB();
    
    switch ($action) {
        case 'obtener_modulos':
            $stmt = $pdo->query("SELECT * FROM modulos ORDER BY nombre_modulo");
            $modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'modulos' => $modulos
            ]);
            break;
            
        case 'crear_modulo':
            $nombre_modulo = trim($_POST['nombre_modulo'] ?? '');
            $estado_modulo = intval($_POST['estado_modulo'] ?? 1);
            
            if (empty($nombre_modulo)) {
                throw new Exception('El nombre del módulo es requerido');
            }
            
            // Verificar si ya existe un módulo con ese nombre
            $stmt = $pdo->prepare("SELECT id FROM modulos WHERE nombre_modulo = ?");
            $stmt->execute([$nombre_modulo]);
            if ($stmt->fetch()) {
                throw new Exception('Ya existe un módulo con ese nombre');
            }
            
            $stmt = $pdo->prepare("INSERT INTO modulos (nombre_modulo, estado_modulo, fecha_creacion, fecha_actualizacion) VALUES (?, ?, CURDATE(), CURDATE())");
            $stmt->execute([$nombre_modulo, $estado_modulo]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Módulo creado exitosamente'
            ]);
            break;
            
        case 'editar_modulo':
            $id = intval($_POST['id'] ?? 0);
            $nombre_modulo = trim($_POST['nombre_modulo'] ?? '');
            $estado_modulo = intval($_POST['estado_modulo'] ?? 1);
            
            if (!$id) {
                throw new Exception('ID de módulo no proporcionado');
            }
            
            if (empty($nombre_modulo)) {
                throw new Exception('El nombre del módulo es requerido');
            }
            
            // Verificar si ya existe otro módulo con ese nombre (excluyendo el actual)
            $stmt = $pdo->prepare("SELECT id FROM modulos WHERE nombre_modulo = ? AND id != ?");
            $stmt->execute([$nombre_modulo, $id]);
            if ($stmt->fetch()) {
                throw new Exception('Ya existe otro módulo con ese nombre');
            }
            
            $stmt = $pdo->prepare("UPDATE modulos SET nombre_modulo = ?, estado_modulo = ?, fecha_actualizacion = CURDATE() WHERE id = ?");
            $stmt->execute([$nombre_modulo, $estado_modulo, $id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Módulo actualizado exitosamente'
            ]);
            break;
            
        case 'cambiar_estado_modulo':
            $id = intval($_POST['id'] ?? 0);
            $estado = intval($_POST['estado'] ?? 0);
            
            if (!$id) {
                throw new Exception('ID de módulo no proporcionado');
            }
            
            $stmt = $pdo->prepare("UPDATE modulos SET estado_modulo = ?, fecha_actualizacion = CURDATE() WHERE id = ?");
            $stmt->execute([$estado, $id]);
            
            $estadoTexto = $estado == 1 ? 'activado' : 'desactivado';
            echo json_encode([
                'success' => true,
                'message' => "Módulo $estadoTexto exitosamente"
            ]);
            break;
            
        case 'obtener_privilegios':
            $stmt = $pdo->prepare("
                SELECT 
                    p.id,
                    p.id_usuario,
                    u.USUARIO_ID,
                    u.USERNAME as nombre_usuario,
                    u.NOMBRE_COMPLETO,
                    m.nombre_modulo,
                    rs.nombre_rol as privilegio,
                    p.fecha_registro
                FROM privilegios p
                INNER JOIN usuarios u ON p.id_usuario = u.USUARIO_ID
                INNER JOIN modulos m ON p.id_modulo = m.id
                INNER JOIN roles_sistema rs ON p.id_rol_sistema = rs.id
                ORDER BY u.USERNAME, m.nombre_modulo
            ");
            $stmt->execute();
            $privilegios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'privilegios' => $privilegios
            ]);
            break;
            
        case 'obtener_usuarios':
            $stmt = $pdo->query("SELECT USUARIO_ID, USERNAME, NOMBRE_COMPLETO FROM usuarios ORDER BY USERNAME");
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'usuarios' => $usuarios
            ]);
            break;
            
        case 'obtener_roles':
            $stmt = $pdo->query("SELECT id, nombre_rol FROM roles_sistema ORDER BY nombre_rol");
            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'roles' => $roles
            ]);
            break;
            
        case 'crear_privilegio':
            $id_usuario = intval($_POST['id_usuario'] ?? 0);
            $id_modulo = intval($_POST['id_modulo'] ?? 0);
            $id_rol_sistema = intval($_POST['id_rol_sistema'] ?? 0);
            
            if (!$id_usuario || !$id_modulo || !$id_rol_sistema) {
                throw new Exception('Todos los campos son requeridos');
            }
            
            // Verificar si ya existe el privilegio
            $stmt = $pdo->prepare("SELECT id FROM privilegios WHERE id_usuario = ? AND id_modulo = ?");
            $stmt->execute([$id_usuario, $id_modulo]);
            
            if ($stmt->fetch()) {
                // Actualizar el privilegio existente
                $stmt = $pdo->prepare("UPDATE privilegios SET id_rol_sistema = ?, fecha_registro = NOW() WHERE id_usuario = ? AND id_modulo = ?");
                $stmt->execute([$id_rol_sistema, $id_usuario, $id_modulo]);
                $mensaje = "Privilegio actualizado exitosamente";
            } else {
                // Crear nuevo privilegio
                $stmt = $pdo->prepare("INSERT INTO privilegios (id_usuario, id_modulo, id_rol_sistema, fecha_registro) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$id_usuario, $id_modulo, $id_rol_sistema]);
                $mensaje = "Privilegio asignado exitosamente";
            }
            
            echo json_encode([
                'success' => true,
                'message' => $mensaje
            ]);
            break;
            
        case 'eliminar_privilegio':
            $id = intval($_POST['id'] ?? 0);
            
            if (!$id) {
                throw new Exception('ID de privilegio no proporcionado');
            }
            
            $stmt = $pdo->prepare("DELETE FROM privilegios WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Privilegio eliminado exitosamente'
            ]);
            break;
            
        case 'obtener_privilegios_usuario':
            $id_usuario = intval($_POST['id_usuario'] ?? $_GET['id_usuario'] ?? 0);
            
            if (!$id_usuario) {
                throw new Exception('ID de usuario no proporcionado');
            }
            
            $stmt = $pdo->prepare("
                SELECT 
                    p.id_modulo,
                    p.id_rol_sistema
                FROM privilegios p
                WHERE p.id_usuario = ?
            ");
            $stmt->execute([$id_usuario]);
            $privilegios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'privilegios' => $privilegios
            ]);
            break;
            
        case 'guardar_privilegios_masivos':
            $id_usuario = intval($_POST['id_usuario'] ?? 0);
            $privilegiosJson = $_POST['privilegios'] ?? '[]';
            
            if (!$id_usuario) {
                throw new Exception('ID de usuario no proporcionado');
            }
            
            $privilegios = json_decode($privilegiosJson, true);
            if (!is_array($privilegios)) {
                throw new Exception('Formato de privilegios inválido');
            }
            
            // Iniciar transacción
            $pdo->beginTransaction();
            
            try {
                // Eliminar todos los privilegios actuales del usuario
                $stmt = $pdo->prepare("DELETE FROM privilegios WHERE id_usuario = ?");
                $stmt->execute([$id_usuario]);
                
                // Insertar los nuevos privilegios
                $stmt = $pdo->prepare("INSERT INTO privilegios (id_usuario, id_modulo, id_rol_sistema, fecha_registro) VALUES (?, ?, ?, NOW())");
                $privilegiosInsertados = 0;
                
                foreach ($privilegios as $privilegio) {
                    $id_modulo = intval($privilegio['id_modulo'] ?? 0);
                    $id_rol_sistema = intval($privilegio['id_rol_sistema'] ?? 0);
                    
                    if ($id_modulo && $id_rol_sistema) {
                        $stmt->execute([$id_usuario, $id_modulo, $id_rol_sistema]);
                        $privilegiosInsertados++;
                    }
                }
                
                // Confirmar transacción
                $pdo->commit();
                
                $mensaje = $privilegiosInsertados > 0 
                    ? "Se han asignado {$privilegiosInsertados} privilegio" . ($privilegiosInsertados > 1 ? 's' : '') . " exitosamente"
                    : "Todos los privilegios han sido eliminados (usuario sin acceso)";
                
                echo json_encode([
                    'success' => true,
                    'message' => $mensaje
                ]);
            } catch (Exception $e) {
                // Revertir transacción en caso de error
                $pdo->rollBack();
                throw $e;
            }
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

