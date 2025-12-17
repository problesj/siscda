<?php
// Incluir funciones de conexión a la base de datos
require_once '../includes/auth_functions.php';

// Configurar headers para JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Verificar que se recibió una acción
if (!isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Acción no especificada']);
    exit();
}

$action = $_POST['action'];

try {
    $pdo = conectarDB();
    
    // Log para debug
    error_log("Reportes Action: " . $action);
    error_log("POST data: " . json_encode($_POST));
    
    if ($action == 'obtener_datos_reporte') {
        $fecha_inicio = $_POST['fecha_inicio'] ?? null;
        $fecha_fin = $_POST['fecha_fin'] ?? null;
        $grupo_familiar = $_POST['grupo_familiar'] ?? null;
        
        // Consulta para obtener todas las personas
        $sql = "SELECT 
                    p.ID as id, p.NOMBRES, p.APELLIDO_PATERNO, p.APELLIDO_MATERNO, 
                    COALESCE(p.FAMILIA, 'Sin familia') as grupo_familiar
                 FROM personas p";
        
        $params = [];
        
        if ($grupo_familiar && $grupo_familiar !== '') {
            $sql .= " WHERE p.FAMILIA = ?";
            $params[] = $grupo_familiar;
        }
        
        $sql .= " ORDER BY p.APELLIDO_PATERNO, p.NOMBRES";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $personas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $personas_formateadas = [];
        
        foreach ($personas as $persona) {
            // Construir nombre completo
            $nombre_completo = $persona['NOMBRES'];
            if ($persona['APELLIDO_PATERNO']) {
                $nombre_completo .= " " . $persona['APELLIDO_PATERNO'];
            }
            if ($persona['APELLIDO_MATERNO']) {
                $nombre_completo .= " " . $persona['APELLIDO_MATERNO'];
            }
            
            // Obtener estadísticas de asistencia para esta persona
            $sql_asistencias = "SELECT COUNT(*) as total_asistencias, MAX(c.FECHA) as ultima_asistencia
                               FROM asistencias a 
                               JOIN cultos c ON a.CULTO_ID = c.ID 
                               WHERE a.PERSONA_ID = ? AND c.FECHA BETWEEN ? AND ?";
            
            $stmt_asistencias = $pdo->prepare($sql_asistencias);
            $stmt_asistencias->execute([$persona['id'], $fecha_inicio, $fecha_fin]);
            $stats = $stmt_asistencias->fetch(PDO::FETCH_ASSOC);
            
            $total_asistencias = $stats ? $stats['total_asistencias'] : 0;
            $ultima_asistencia = $stats ? $stats['ultima_asistencia'] : null;
            
            // Calcular porcentaje (necesitamos el total de cultos en el período)
            $sql_total_cultos = "SELECT COUNT(*) as total FROM cultos WHERE FECHA BETWEEN ? AND ?";
            $stmt_total = $pdo->prepare($sql_total_cultos);
            $stmt_total->execute([$fecha_inicio, $fecha_fin]);
            $total_cultos = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];
            
            $porcentaje = $total_cultos > 0 ? round(($total_asistencias / $total_cultos) * 100, 1) : 0;
            
            $personas_formateadas[] = [
                'id' => $persona['id'],
                'nombre_completo' => $nombre_completo,
                'grupo_familiar' => $persona['grupo_familiar'],
                'total_asistencias' => $total_asistencias,
                'porcentaje' => $porcentaje,
                'ultima_asistencia' => $ultima_asistencia ? date('d/m/Y', strtotime($ultima_asistencia)) : 'Nunca'
            ];
        }
        
        echo json_encode([
            'success' => true,
            'personas' => $personas_formateadas,
            'total' => count($personas_formateadas)
        ]);
        
    } else if ($action == 'obtener_detalle_asistencias_persona') {
        $persona_id = intval($_POST['persona_id'] ?? 0);
        $fecha_inicio = $_POST['fecha_inicio'] ?? null;
        $fecha_fin = $_POST['fecha_fin'] ?? null;
        
        if ($persona_id <= 0) {
            throw new Exception('ID de persona no válido');
        }
        
        // Obtener información de la persona
        $stmt = $pdo->prepare("
            SELECT 
                p.ID,
                CONCAT(p.NOMBRES, ' ', COALESCE(p.APELLIDO_PATERNO, ''), ' ', COALESCE(p.APELLIDO_MATERNO, '')) as nombre_completo,
                COALESCE(p.FAMILIA, 'Sin familia') as grupo_familiar
            FROM personas p 
            WHERE p.ID = ?
        ");
        $stmt->execute([$persona_id]);
        $persona = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$persona) {
            throw new Exception('Persona no encontrada');
        }
        
        // Construir consulta de asistencias con filtros de fecha
        $sql_asistencias = "
            SELECT 
                c.FECHA,
                DATE_FORMAT(c.FECHA, '%d/%m/%Y') as fecha_formateada,
                CASE 
                    WHEN DAYOFWEEK(c.FECHA) = 1 THEN 'Domingo'
                    WHEN DAYOFWEEK(c.FECHA) = 2 THEN 'Lunes'
                    WHEN DAYOFWEEK(c.FECHA) = 3 THEN 'Martes'
                    WHEN DAYOFWEEK(c.FECHA) = 4 THEN 'Miércoles'
                    WHEN DAYOFWEEK(c.FECHA) = 5 THEN 'Jueves'
                    WHEN DAYOFWEEK(c.FECHA) = 6 THEN 'Viernes'
                    WHEN DAYOFWEEK(c.FECHA) = 7 THEN 'Sábado'
                END as dia_semana,
                c.TIPO_CULTO
            FROM asistencias a
            INNER JOIN cultos c ON a.CULTO_ID = c.ID
            WHERE a.PERSONA_ID = ?
        ";
        
        $params = [$persona_id];
        
        // Agregar filtros de fecha si están disponibles
        if ($fecha_inicio) {
            $sql_asistencias .= " AND c.FECHA >= ?";
            $params[] = $fecha_inicio;
        }
        
        if ($fecha_fin) {
            $sql_asistencias .= " AND c.FECHA <= ?";
            $params[] = $fecha_fin;
        }
        
        $sql_asistencias .= " ORDER BY c.FECHA DESC";
        
        error_log("SQL Asistencias: " . $sql_asistencias);
        error_log("Params: " . json_encode($params));
        
        $stmt = $pdo->prepare($sql_asistencias);
        $stmt->execute($params);
        $asistencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Asistencias encontradas: " . count($asistencias));
        
        // Formatear las asistencias
        $asistencias_formateadas = [];
        foreach ($asistencias as $asistencia) {
            $asistencias_formateadas[] = [
                'fecha' => $asistencia['fecha_formateada'],
                'dia_semana' => $asistencia['dia_semana'],
                'tipo_culto' => $asistencia['TIPO_CULTO']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'persona' => $persona,
            'asistencias' => $asistencias_formateadas,
            'total_asistencias' => count($asistencias_formateadas)
        ]);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
    }
    
} catch (Exception $e) {
    error_log("Error en reportes_actions.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
