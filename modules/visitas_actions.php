<?php
require_once dirname(__DIR__) . '/includes/auth_functions.php';

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Obtener datos POST
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

try {
    $pdo = conectarDB();
    
    switch ($action) {
        case 'agregar_visita':
            agregarVisita($pdo, $input);
            break;
            
        case 'obtener_visitas_culto':
            obtenerVisitasCulto($pdo, $input);
            break;
            
        case 'registrar_asistencia_visita':
            registrarAsistenciaVisita($pdo, $input);
            break;
            
        case 'eliminar_asistencia_visita':
            eliminarAsistenciaVisita($pdo, $input);
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

function agregarVisita($pdo, $input) {
    $nombres = limpiarDatos($input['nombres'] ?? '');
    $apellidos = limpiarDatos($input['apellidos'] ?? '');
    $observaciones = limpiarDatos($input['observaciones'] ?? '');
    $cultoId = intval($input['cultoId'] ?? 0);
    $primeraVez = intval($input['primeraVez'] ?? 0);
    $usuarioId = $_SESSION['usuario_id'];
    
    // Validar datos requeridos
    if (empty($nombres) || empty($apellidos) || $cultoId <= 0) {
        throw new Exception('Faltan datos requeridos');
    }
    
    // Verificar que el culto existe
    $stmt = $pdo->prepare("SELECT ID FROM cultos WHERE ID = ?");
    $stmt->execute([$cultoId]);
    if (!$stmt->fetch()) {
        throw new Exception('El culto seleccionado no existe');
    }
    
    $pdo->beginTransaction();
    
    try {
        // Insertar visita en la tabla visitas
        $stmt = $pdo->prepare("
            INSERT INTO visitas (NOMBRES, APELLIDOS, OBSERVACIONES) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$nombres, $apellidos, $observaciones]);
        $visitaId = $pdo->lastInsertId();
        
        // Registrar asistencia de la visita
        $stmt = $pdo->prepare("
            INSERT INTO asistencias_visitas (CULTO_ID, VISITA_ID, PRIMERA_VEZ, USUARIO_ID) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$cultoId, $visitaId, $primeraVez, $usuarioId]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Visita agregada y asistencia registrada exitosamente',
            'visita_id' => $visitaId
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function obtenerVisitasCulto($pdo, $input) {
    $cultoId = intval($input['cultoId'] ?? 0);
    
    if ($cultoId <= 0) {
        throw new Exception('ID de culto no válido');
    }
    
    $stmt = $pdo->prepare("
        SELECT 
            v.ID as visita_id,
            v.NOMBRES,
            v.APELLIDOS,
            v.OBSERVACIONES,
            av.PRIMERA_VEZ,
            av.USUARIO_ID,
            u.NOMBRES as usuario_nombre
        FROM visitas v
        INNER JOIN asistencias_visitas av ON v.ID = av.VISITA_ID
        LEFT JOIN usuarios u ON av.USUARIO_ID = u.ID
        WHERE av.CULTO_ID = ?
        ORDER BY v.APELLIDOS, v.NOMBRES
    ");
    
    $stmt->execute([$cultoId]);
    $visitas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'visitas' => $visitas
    ]);
}

function registrarAsistenciaVisita($pdo, $input) {
    $cultoId = intval($input['cultoId'] ?? 0);
    $visitaId = intval($input['visitaId'] ?? 0);
    $primeraVez = intval($input['primeraVez'] ?? 0);
    $usuarioId = $_SESSION['usuario_id'];
    
    if ($cultoId <= 0 || $visitaId <= 0) {
        throw new Exception('IDs no válidos');
    }
    
    // Verificar si ya existe la asistencia
    $stmt = $pdo->prepare("
        SELECT ID FROM asistencias_visitas 
        WHERE CULTO_ID = ? AND VISITA_ID = ?
    ");
    $stmt->execute([$cultoId, $visitaId]);
    
    if ($stmt->fetch()) {
        // Actualizar asistencia existente
        $stmt = $pdo->prepare("
            UPDATE asistencias_visitas 
            SET PRIMERA_VEZ = ?, USUARIO_ID = ? 
            WHERE CULTO_ID = ? AND VISITA_ID = ?
        ");
        $stmt->execute([$primeraVez, $usuarioId, $cultoId, $visitaId]);
    } else {
        // Crear nueva asistencia
        $stmt = $pdo->prepare("
            INSERT INTO asistencias_visitas (CULTO_ID, VISITA_ID, PRIMERA_VEZ, USUARIO_ID) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$cultoId, $visitaId, $primeraVez, $usuarioId]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Asistencia de visita registrada exitosamente'
    ]);
}

function eliminarAsistenciaVisita($pdo, $input) {
    $cultoId = intval($input['cultoId'] ?? 0);
    $visitaId = intval($input['visitaId'] ?? 0);
    
    if ($cultoId <= 0 || $visitaId <= 0) {
        throw new Exception('IDs no válidos');
    }
    
    $stmt = $pdo->prepare("
        DELETE FROM asistencias_visitas 
        WHERE CULTO_ID = ? AND VISITA_ID = ?
    ");
    $stmt->execute([$cultoId, $visitaId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Asistencia de visita eliminada exitosamente'
    ]);
}
?>
