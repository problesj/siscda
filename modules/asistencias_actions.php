<?php
// Log de debug para verificar que el archivo se está ejecutando
error_log("asistencias_actions.php ejecutándose - Método: " . $_SERVER['REQUEST_METHOD'] . ", POST data: " . json_encode($_POST));

require_once dirname(__DIR__) . '/session_config.php';
session_start();
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth_functions.php';

// Verificar que la función conectarDB esté disponible
if (!function_exists('conectarDB')) {
    error_log("ERROR: La función conectarDB no está disponible después de incluir auth_functions.php");
    error_log("Archivos incluidos: " . implode(', ', get_included_files()));
} else {
    error_log("✅ La función conectarDB está disponible");
}

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    // Si es una petición AJAX, devolver JSON con error de sesión
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'error' => 'session_expired',
            'message' => 'La sesión ha caducado. Por favor, inicie sesión nuevamente.',
            'redirect' => dirname($_SERVER['REQUEST_URI']) . '/../login.php'
        ]);
        exit();
    } else {
        // Si es una petición normal, redirigir al login
        header('Location: ' . dirname($_SERVER['REQUEST_URI']) . '/../login.php');
        exit();
    }
}

// Función para manejar la acción de agregar persona
function agregarPersona($datos) {
    try {
        $pdo = conectarDB();
        
        // Obtener datos
        $nombres = $datos['nombres'] ?? '';
        $apellidos = $datos['apellidos'] ?? '';
        $primeraVez = $datos['primeraVez'] ?? 0;
        $familia = $datos['familia'] ?? '';
        $observaciones = $datos['observaciones'] ?? '';
        $culto_id = $datos['culto_id'] ?? null;
        
        // Validar datos requeridos
        if (empty($nombres) || empty($apellidos)) {
            return ['success' => false, 'message' => 'Nombres y apellidos son obligatorios'];
        }
        
        // Insertar nueva persona
        $stmt = $pdo->prepare("INSERT INTO personas (NOMBRES, APELLIDO_PATERNO, FAMILIA, OBSERVACIONES, FECHA_CREACION) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$nombres, $apellidos, $familia, $observaciones]);
        $persona_id = $pdo->lastInsertId();
        
        // Si hay culto_id, agregar asistencia
        if ($culto_id && $culto_id !== 'null') {
            $stmt = $pdo->prepare("INSERT INTO asistencias (PERSONA_ID, CULTO_ID, PRIMERA_VEZ, USUARIO_ID) VALUES (?, ?, ?, ?)");
            $stmt->execute([$persona_id, $culto_id, $primeraVez, $_SESSION['usuario_id'] ?? 1]);
        }
        
        return ['success' => true, 'message' => 'Persona agregada correctamente'];
        
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
    }
}

// Verificar si es una petición AJAX
if (isset($_SERVER['HTTP_CONTENT_TYPE']) && $_SERVER['HTTP_CONTENT_TYPE'] === 'application/json') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    if ($action == 'agregar_persona') {
        header('Content-Type: application/json');
        $resultado = agregarPersona($input);
        echo json_encode($resultado);
        exit();
    }
}

// Procesar formularios tradicionales y FormData
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'agregar_persona') {
        // Manejar petición FormData
        header('Content-Type: application/json');
        $resultado = agregarPersona($_POST);
        echo json_encode($resultado);
        exit();
    }
    
    if ($action == 'verificar_sesion') {
        // Verificar el estado de la sesión
        if (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        if (isset($_SESSION['usuario_id'])) {
            echo json_encode(['success' => true, 'message' => 'Sesión válida']);
        } else {
                    echo json_encode([
            'success' => false, 
            'error' => 'session_expired',
            'message' => 'La sesión ha caducado',
            'redirect' => dirname($_SERVER['REQUEST_URI']) . '/../login.php'
        ]);
        }
        exit();
    }
    
    if ($action == 'test') {
        // Función de prueba para verificar que el archivo funciona
        if (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        echo json_encode([
            'success' => true, 
            'message' => 'asistencias_actions.php funcionando correctamente',
            'timestamp' => date('Y-m-d H:i:s'),
            'session_id' => session_id()
        ]);
        exit();
    }
    
    if ($action == 'consultar_asistencias') {
        // Consultar el estado actual de las asistencias
        // Limpiar cualquier salida previa
        if (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        try {
            $pdo = conectarDB();
            
            $culto_id = $_POST['culto_id'] ?? null;
            $personas_ids = $_POST['personas_ids'] ?? null;
            
            if (!$culto_id || !$personas_ids) {
                echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
                exit();
            }
            
            // Decodificar array de IDs
            $ids = json_decode($personas_ids, true);
            if (!is_array($ids)) {
                echo json_encode(['success' => false, 'message' => 'Formato de IDs inválido']);
                exit();
            }
            
            // Crear placeholders para la consulta IN
            $placeholders = str_repeat('?,', count($ids) - 1) . '?';
            
            // Consultar asistencias existentes
            $stmt = $pdo->prepare("SELECT PERSONA_ID FROM asistencias WHERE CULTO_ID = ? AND PERSONA_ID IN ($placeholders)");
            $params = array_merge([$culto_id], $ids);
            $stmt->execute($params);
            
            $asistencias = [];
            while ($row = $stmt->fetch()) {
                $asistencias[$row['PERSONA_ID']] = true;
            }
            
            // Crear respuesta con todos los IDs (true si asistió, false si no)
            $resultado = [];
            foreach ($ids as $id) {
                $resultado[$id] = isset($asistencias[$id]);
            }
            
            $response = [
                'success' => true, 
                'asistencias' => $resultado,
                'message' => 'Estados de asistencia consultados correctamente'
            ];
            
            error_log("Respuesta consultar_asistencias: " . json_encode($response));
            echo json_encode($response);
            
        } catch (PDOException $e) {
            error_log("Error en consultar_asistencias: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit();
    }
    
    if ($action == 'guardar_asistencia_individual') {
        // Manejar guardado individual de asistencia
        // Limpiar cualquier salida previa
        if (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        // Debug: log de la petición
        error_log("Guardando asistencia individual: " . json_encode($_POST));
        
        try {
            $pdo = conectarDB();
            
            $persona_id = $_POST['persona_id'] ?? null;
            $culto_id = $_POST['culto_id'] ?? null;
            $asistio = $_POST['asistio'] ?? '0';
            
            error_log("Datos procesados: persona_id=$persona_id, culto_id=$culto_id, asistio=$asistio");
            
            if (!$persona_id || !$culto_id) {
                error_log("Datos incompletos: persona_id=$persona_id, culto_id=$culto_id");
                echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
                exit();
            }
            
            if ($asistio == '1') {
                // Marcar asistencia
                $stmt = $pdo->prepare("INSERT INTO asistencias (PERSONA_ID, CULTO_ID, PRIMERA_VEZ, USUARIO_ID) VALUES (?, ?, 0, ?) ON DUPLICATE KEY UPDATE USUARIO_ID = ?");
                $stmt->execute([$persona_id, $culto_id, $_SESSION['usuario_id'] ?? 1, $_SESSION['usuario_id'] ?? 1]);
                error_log("Asistencia marcada para persona $persona_id en culto $culto_id");
            } else {
                // Quitar asistencia
                $stmt = $pdo->prepare("DELETE FROM asistencias WHERE PERSONA_ID = ? AND CULTO_ID = ?");
                $stmt->execute([$persona_id, $culto_id]);
                error_log("Asistencia removida para persona $persona_id en culto $culto_id");
            }
            
            $response = ['success' => true, 'message' => 'Asistencia actualizada'];
            error_log("Respuesta guardar_asistencia_individual: " . json_encode($response));
            echo json_encode($response);
            
        } catch (PDOException $e) {
            error_log("Error en guardar_asistencia_individual: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit();
    }
    
    if ($action == 'guardar_asistencias') {
        $culto_id = $_POST['culto_id'];
        $asistencias = isset($_POST['asistencias']) ? $_POST['asistencias'] : [];
        
        try {
            $pdo = conectarDB();
            
            // Primero eliminar todas las asistencias del culto
            $stmt = $pdo->prepare("DELETE FROM asistencias WHERE CULTO_ID = ?");
            $stmt->execute([$culto_id]);
            
            // Luego insertar las nuevas asistencias
            if (!empty($asistencias)) {
                $stmt = $pdo->prepare("INSERT INTO asistencias (PERSONA_ID, CULTO_ID, PRIMERA_VEZ, USUARIO_ID) VALUES (?, ?, 0, ?)");
                foreach ($asistencias as $persona_id) {
                    $stmt->execute([$persona_id, $culto_id, $_SESSION['usuario_id'] ?? 1]);
                }
            }
            
            $_SESSION['success'] = 'Asistencias guardadas exitosamente';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
        
        header('Location: asistencias.php?culto_id=' . $culto_id);
        exit();
    }
    
    if ($action == 'obtener_todas_personas') {
        // Obtener TODAS las personas de la base de datos para el contador
        // Limpiar cualquier salida previa
        if (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        error_log("Obteniendo todas las personas para culto: " . json_encode($_POST));
        
        try {
            $pdo = conectarDB();
            
            $culto_id = $_POST['culto_id'] ?? null;
            
            if (!$culto_id) {
                echo json_encode(['success' => false, 'message' => 'ID de culto requerido']);
                exit();
            }
            
            // Consultar TODAS las personas con su estado de asistencia para este culto
            $sql = "SELECT 
                        p.ID,
                        p.NOMBRES,
                        p.APELLIDO_PATERNO,
                        p.FAMILIA,
                        COALESCE(gf.NOMBRE, '') as GRUPO_FAMILIAR,
                        CASE WHEN a.PERSONA_ID IS NOT NULL THEN 1 ELSE 0 END as asistio
                    FROM personas p
                    LEFT JOIN grupos_familiares gf ON p.GRUPO_FAMILIAR_ID = gf.ID
                    LEFT JOIN asistencias a ON p.ID = a.PERSONA_ID AND a.CULTO_ID = ?
                    ORDER BY 
                        CASE WHEN gf.NOMBRE IS NOT NULL AND gf.NOMBRE != '' THEN 1 ELSE 2 END,
                        gf.NOMBRE ASC,
                        CASE WHEN p.FAMILIA IS NOT NULL AND p.FAMILIA != '' THEN 1 ELSE 2 END,
                        p.FAMILIA ASC,
                        CASE WHEN p.APELLIDO_PATERNO IS NOT NULL AND p.APELLIDO_PATERNO != '' THEN 1 ELSE 2 END,
                        p.APELLIDO_PATERNO ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$culto_id]);
            $personas = $stmt->fetchAll();
            
            // Formatear los datos para el frontend
            $personasFormateadas = [];
            foreach ($personas as $persona) {
                $personasFormateadas[] = [
                    'id' => $persona['ID'],
                    'nombres' => $persona['NOMBRES'],
                    'apellidoPaterno' => $persona['APELLIDO_PATERNO'],
                    'familia' => $persona['FAMILIA'] ?: '',
                    'grupoFamiliar' => $persona['GRUPO_FAMILIAR'] ?: '',
                    'asistio' => (bool)$persona['asistio']
                ];
            }
            
            $response = [
                'success' => true,
                'personas' => $personasFormateadas,
                'total' => count($personasFormateadas),
                'message' => 'Todas las personas obtenidas correctamente'
            ];
            
            error_log("Respuesta obtener_todas_personas: " . count($personasFormateadas) . " personas encontradas");
            echo json_encode($response);
            
        } catch (PDOException $e) {
            error_log("Error en obtener_todas_personas: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit();
    }
    
    if ($action == 'obtener_asistentes_culto') {
        // Obtener asistentes de un culto específico
        // Limpiar cualquier salida previa
        if (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        error_log("Obteniendo asistentes del culto: " . json_encode($_POST));
        
        try {
            $pdo = conectarDB();
            
            $culto_id = $_POST['culto_id'] ?? null;
            
            if (!$culto_id) {
                echo json_encode(['success' => false, 'message' => 'ID de culto requerido']);
                exit();
            }
            
            // Obtener asistentes del culto (personas regulares)
            $stmt = $pdo->prepare("
                SELECT 
                    p.NOMBRES,
                    p.APELLIDO_PATERNO,
                    p.APELLIDO_MATERNO,
                    p.FAMILIA,
                    gf.NOMBRE as GRUPO_FAMILIAR,
                    p.OBSERVACIONES,
                    'persona' as TIPO
                FROM asistencias a
                INNER JOIN personas p ON a.PERSONA_ID = p.ID
                LEFT JOIN grupos_familiares gf ON p.GRUPO_FAMILIAR_ID = gf.ID
                WHERE a.CULTO_ID = ?
            ");
            $stmt->execute([$culto_id]);
            $asistentesPersonas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Obtener visitas del culto
            $stmt = $pdo->prepare("
                SELECT 
                    v.NOMBRES,
                    v.APELLIDOS as APELLIDO_PATERNO,
                    '' as APELLIDO_MATERNO,
                    '' as FAMILIA,
                    '' as GRUPO_FAMILIAR,
                    v.OBSERVACIONES,
                    'visita' as TIPO,
                    av.PRIMERA_VEZ
                FROM asistencias_visitas av
                INNER JOIN visitas v ON av.VISITA_ID = v.ID
                WHERE av.CULTO_ID = ?
            ");
            $stmt->execute([$culto_id]);
            $asistentesVisitas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Combinar asistentes y visitas
            $todosAsistentes = array_merge($asistentesPersonas, $asistentesVisitas);
            
            // Ordenar por apellido
            usort($todosAsistentes, function($a, $b) {
                $apellidoA = $a['APELLIDO_PATERNO'] ?? '';
                $apellidoB = $b['APELLIDO_PATERNO'] ?? '';
                return strcasecmp($apellidoA, $apellidoB);
            });
            
            // Formatear los datos para la respuesta
            $asistentesFormateados = [];
            foreach ($todosAsistentes as $asistente) {
                $asistentesFormateados[] = [
                    'nombres' => $asistente['NOMBRES'],
                    'apellidos' => trim($asistente['APELLIDO_PATERNO'] . ' ' . $asistente['APELLIDO_MATERNO']),
                    'familia' => $asistente['FAMILIA'],
                    'grupo_familiar' => $asistente['GRUPO_FAMILIAR'],
                    'observaciones' => $asistente['OBSERVACIONES'],
                    'tipo' => $asistente['TIPO'],
                    'primera_vez' => $asistente['PRIMERA_VEZ'] ?? null
                ];
            }
            
            $response = [
                'success' => true,
                'asistentes' => $asistentesFormateados,
                'total' => count($asistentesFormateados),
                'message' => 'Asistentes obtenidos correctamente'
            ];
            
            error_log("Respuesta obtener_asistentes_culto: " . count($asistentesFormateados) . " asistentes encontrados");
            echo json_encode($response);
            
        } catch (PDOException $e) {
            error_log("Error en obtener_asistentes_culto: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit();
    }
    
    if ($action == 'obtener_cultos_activos') {
        // Obtener cultos activos para el select de visitas
        try {
            $pdo = conectarDB();
            
            $stmt = $pdo->prepare("SELECT ID, TIPO_CULTO, FECHA FROM cultos ORDER BY FECHA DESC");
            $stmt->execute();
            $cultos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Formatear fechas
            $cultosFormateados = [];
            foreach ($cultos as $culto) {
                $cultosFormateados[] = [
                    'ID' => $culto['ID'],
                    'TIPO_CULTO' => $culto['TIPO_CULTO'],
                    'FECHA' => $culto['FECHA'],
                    'FECHA_FORMATEADA' => date('d/m/Y', strtotime($culto['FECHA']))
                ];
            }
            
            echo json_encode([
                'success' => true,
                'cultos' => $cultosFormateados
            ]);
            
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al cargar cultos: ' . $e->getMessage()
            ]);
        }
        exit();
    }
    
    if ($action == 'obtener_conteo_visitas_culto') {
        // Obtener conteo de visitas del culto actual
        try {
            $pdo = conectarDB();
            $cultoId = intval($_POST['culto_id'] ?? 0);
            
            if ($cultoId <= 0) {
                throw new Exception('ID de culto no válido');
            }
            
            // Contar visitas del culto
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total_visitas
                FROM asistencias_visitas 
                WHERE CULTO_ID = ?
            ");
            $stmt->execute([$cultoId]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'total_visitas' => intval($resultado['total_visitas'])
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener conteo de visitas: ' . $e->getMessage()
            ]);
        }
        exit();
    }
    
    if ($action == 'agregar_multiples_visitas') {
        // Agregar múltiples visitas con distribución de género
        try {
            $pdo = conectarDB();
            
            // Obtener datos
            $cultoId = intval($_POST['culto_id'] ?? 0);
            $totalVisitas = intval($_POST['total_visitas'] ?? 0);
            $hombres = intval($_POST['hombres'] ?? 0);
            $mujeres = intval($_POST['mujeres'] ?? 0);
            $ninos = intval($_POST['ninos'] ?? 0);
            $observaciones = trim($_POST['observaciones'] ?? '');
            
            // Validar datos
            if ($cultoId <= 0) {
                throw new Exception('ID de culto no válido');
            }
            
            if ($totalVisitas <= 0 || $totalVisitas > 10) {
                throw new Exception('Número de visitas debe estar entre 1 y 10');
            }
            
            if ($hombres + $mujeres + $ninos !== $totalVisitas) {
                throw new Exception('La suma de hombres, mujeres y niños debe ser igual al total de visitas');
            }
            
            // Verificar que el culto existe
            $stmt = $pdo->prepare("SELECT ID FROM cultos WHERE ID = ?");
            $stmt->execute([$cultoId]);
            if (!$stmt->fetch()) {
                throw new Exception('El culto especificado no existe');
            }
            
            // Iniciar transacción
            $pdo->beginTransaction();
            
            $visitasCreadas = 0;
            $contadorVisita = 1;
            
            // Crear visitas de hombres
            for ($i = 1; $i <= $hombres; $i++) {
                $stmt = $pdo->prepare("
                    INSERT INTO visitas (NOMBRES, APELLIDOS, OBSERVACIONES) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([
                    "visita " . $contadorVisita,
                    "hombre " . $i,
                    $observaciones
                ]);
                
                $visitaId = $pdo->lastInsertId();
                
                // Registrar asistencia
                $stmt = $pdo->prepare("
                    INSERT INTO asistencias_visitas (CULTO_ID, VISITA_ID, PRIMERA_VEZ, USUARIO_ID) 
                    VALUES (?, ?, 1, ?)
                ");
                $stmt->execute([$cultoId, $visitaId, $_SESSION['usuario_id']]);
                
                $visitasCreadas++;
                $contadorVisita++;
            }
            
            // Crear visitas de mujeres
            for ($i = 1; $i <= $mujeres; $i++) {
                $stmt = $pdo->prepare("
                    INSERT INTO visitas (NOMBRES, APELLIDOS, OBSERVACIONES) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([
                    "visita " . $contadorVisita,
                    "mujer " . $i,
                    $observaciones
                ]);
                
                $visitaId = $pdo->lastInsertId();
                
                // Registrar asistencia
                $stmt = $pdo->prepare("
                    INSERT INTO asistencias_visitas (CULTO_ID, VISITA_ID, PRIMERA_VEZ, USUARIO_ID) 
                    VALUES (?, ?, 1, ?)
                ");
                $stmt->execute([$cultoId, $visitaId, $_SESSION['usuario_id']]);
                
                $visitasCreadas++;
                $contadorVisita++;
            }
            
            // Crear visitas de niños
            for ($i = 1; $i <= $ninos; $i++) {
                $stmt = $pdo->prepare("
                    INSERT INTO visitas (NOMBRES, APELLIDOS, OBSERVACIONES) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([
                    "visita " . $contadorVisita,
                    "niño " . $i,
                    $observaciones
                ]);
                
                $visitaId = $pdo->lastInsertId();
                
                // Registrar asistencia
                $stmt = $pdo->prepare("
                    INSERT INTO asistencias_visitas (CULTO_ID, VISITA_ID, PRIMERA_VEZ, USUARIO_ID) 
                    VALUES (?, ?, 1, ?)
                ");
                $stmt->execute([$cultoId, $visitaId, $_SESSION['usuario_id']]);
                
                $visitasCreadas++;
                $contadorVisita++;
            }
            
            // Confirmar transacción
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Visitas creadas correctamente',
                'visitas_creadas' => $visitasCreadas,
                'distribucion' => [
                    'hombres' => $hombres,
                    'mujeres' => $mujeres,
                    'ninos' => $ninos
                ]
            ]);
            
        } catch (Exception $e) {
            // Rollback en caso de error
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollback();
            }
            
            echo json_encode([
                'success' => false,
                'message' => 'Error al crear múltiples visitas: ' . $e->getMessage()
            ]);
        }
        exit();
    }
}

header('Location: asistencias.php');
exit();
?>
