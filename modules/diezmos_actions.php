<?php
require_once dirname(__DIR__) . '/session_config.php';
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth_functions.php';

// Verificar autenticación
verificarAutenticacion();

// Verificar acceso al módulo de Diezmos
verificarAccesoModulo('Diezmos');

// Verificar si el usuario es Administrador del módulo
$esAdministrador = esAdministradorModulo($_SESSION['usuario_id'], 'Diezmos');

// Verificar si el usuario es admin
$esAdmin = isset($_SESSION['username']) && strtolower($_SESSION['username']) === 'admin';

header('Content-Type: application/json');

try {
    $pdo = conectarDB();
    $action = $_REQUEST['action'] ?? '';
    
    switch ($action) {
        case 'obtener_diezmos':
            try {
                $stmt = $pdo->prepare("
                    SELECT 
                        d.id,
                        d.sobre,
                        d.estado_sobre,
                        d.fecha_creacion,
                        d.fecha_actualizacion,
                        GROUP_CONCAT(
                            CONCAT(p.NOMBRES, ' ', p.APELLIDO_PATERNO, ' ', IFNULL(p.APELLIDO_MATERNO, ''))
                            SEPARATOR ', '
                        ) as personas_asociadas,
                        COUNT(DISTINCT dp.id_persona) as total_personas
                    FROM diezmos d
                    LEFT JOIN diezmos_personas dp ON d.id = dp.id_diezmos
                    LEFT JOIN personas p ON dp.id_persona = p.ID
                    GROUP BY d.id, d.sobre, d.estado_sobre, d.fecha_creacion, d.fecha_actualizacion
                    ORDER BY d.fecha_creacion DESC, d.id DESC
                ");
                $stmt->execute();
                $diezmos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'diezmos' => $diezmos
                ]);
            } catch (PDOException $e) {
                error_log("Error en obtener_diezmos: " . $e->getMessage());
                throw $e;
            }
            break;
            
        case 'obtener_detalle':
            $id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
            
            if (!$id) {
                throw new Exception('ID de sobre no proporcionado');
            }
            
            // Obtener datos del sobre
            $stmt = $pdo->prepare("
                SELECT 
                    d.id,
                    d.sobre,
                    d.estado_sobre,
                    d.fecha_creacion,
                    d.fecha_actualizacion
                FROM diezmos d
                WHERE d.id = ?
            ");
            $stmt->execute([$id]);
            $sobre = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$sobre) {
                throw new Exception('Sobre no encontrado');
            }
            
            // Obtener personas asociadas
            $stmt = $pdo->prepare("
                SELECT 
                    p.ID,
                    p.NOMBRES,
                    p.APELLIDO_PATERNO,
                    p.APELLIDO_MATERNO,
                    p.RUT
                FROM diezmos_personas dp
                INNER JOIN personas p ON dp.id_persona = p.ID
                WHERE dp.id_diezmos = ?
                ORDER BY p.APELLIDO_PATERNO, p.NOMBRES
            ");
            $stmt->execute([$id]);
            $personas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Obtener pagos del año actual
            $anhoActual = date('Y');
            $stmt = $pdo->prepare("
                SELECT 
                    id,
                    anho,
                    monto_enero, fecha_pago_enero,
                    monto_febrero, fecha_pago_febrero,
                    monto_marzo, fecha_pago_marzo,
                    monto_abril, fecha_pago_abril,
                    monto_mayo, fecha_pago_mayo,
                    monto_junio, fecha_pago_junio,
                    monto_julio, fecha_pago_julio,
                    monto_agosto, fecha_pago_agosto,
                    monto_septiembre, fecha_pago_septiembre,
                    monto_octubre, fecha_pago_octubre,
                    monto_noviembre, fecha_pago_noviembre,
                    monto_diciembre, fecha_pago_diciembre
                FROM pagos_diezmos
                WHERE id_sobre_diezmo = ? AND anho = ?
            ");
            $stmt->execute([$id, $anhoActual]);
            $pagos = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Si no hay pagos, devolver un objeto vacío
            if (!$pagos || $pagos === false) {
                $pagos = new stdClass();
            }
            
            echo json_encode([
                'success' => true,
                'sobre' => $sobre,
                'personas' => $personas,
                'pagos' => $pagos
            ]);
            break;
            
        case 'buscar_personas':
            $busqueda = trim($_GET['q'] ?? $_POST['q'] ?? '');
            
            if (strlen($busqueda) < 2) {
                echo json_encode([
                    'success' => true,
                    'personas' => []
                ]);
                break;
            }
            
            $stmt = $pdo->prepare("
                SELECT 
                    p.ID,
                    p.NOMBRES,
                    p.APELLIDO_PATERNO,
                    p.APELLIDO_MATERNO,
                    p.RUT,
                    p.FAMILIA
                FROM personas p
                WHERE (
                    LOWER(CONCAT(p.NOMBRES, ' ', p.APELLIDO_PATERNO, ' ', IFNULL(p.APELLIDO_MATERNO, ''))) LIKE LOWER(?)
                    OR LOWER(p.RUT) LIKE LOWER(?)
                    OR LOWER(p.FAMILIA) LIKE LOWER(?)
                )
                ORDER BY p.APELLIDO_PATERNO, p.NOMBRES
                LIMIT 20
            ");
            $busquedaLike = "%{$busqueda}%";
            $stmt->execute([$busquedaLike, $busquedaLike, $busquedaLike]);
            $personas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'personas' => $personas
            ]);
            break;
            
        case 'guardar_sobre':
            // Solo administradores pueden crear/editar sobres
            if (!$esAdministrador && !$esAdmin) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No tienes permisos para crear o editar sobres'
                ]);
                exit();
            }
            
            $id = intval($_POST['id'] ?? 0);
            $sobre = trim($_POST['sobre'] ?? '');
            $personas_ids = $_POST['personas_ids'] ?? [];
            
            if (empty($sobre)) {
                throw new Exception('El nombre del sobre es obligatorio');
            }
            
            if (strlen($sobre) > 300) {
                throw new Exception('El nombre del sobre no puede exceder 300 caracteres');
            }
            
            // Si es una edición (id > 0), verificar que el sobre esté activo
            if ($id > 0) {
                $stmt = $pdo->prepare("SELECT estado_sobre FROM diezmos WHERE id = ?");
                $stmt->execute([$id]);
                $sobreActual = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$sobreActual) {
                    throw new Exception('Sobre no encontrado');
                }
                
                if (intval($sobreActual['estado_sobre']) === 0) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'No se puede editar un sobre inactivo. Debe activarlo primero.'
                    ]);
                    exit();
                }
            }
            
            $pdo->beginTransaction();
            
            try {
                $esNuevo = ($id == 0);
                
                if ($id > 0) {
                    // Actualizar sobre existente
                    $stmt = $pdo->prepare("
                        UPDATE diezmos 
                        SET sobre = ?, fecha_actualizacion = CURDATE()
                        WHERE id = ?
                    ");
                    $stmt->execute([$sobre, $id]);
                    
                    // Eliminar relaciones de personas existentes
                    $stmt = $pdo->prepare("DELETE FROM diezmos_personas WHERE id_diezmos = ?");
                    $stmt->execute([$id]);
                } else {
                    // Crear nuevo sobre
                    $stmt = $pdo->prepare("
                        INSERT INTO diezmos (sobre, estado_sobre, fecha_creacion, fecha_actualizacion)
                        VALUES (?, 1, CURDATE(), CURDATE())
                    ");
                    $stmt->execute([$sobre]);
                    $id = $pdo->lastInsertId();
                }
                
                // Agregar relaciones con personas
                if (!empty($personas_ids) && is_array($personas_ids)) {
                    $stmt = $pdo->prepare("
                        INSERT INTO diezmos_personas (id_persona, id_diezmos, fecha_creacion, fecha_actualizacion)
                        VALUES (?, ?, CURDATE(), CURDATE())
                    ");
                    foreach ($personas_ids as $persona_id) {
                        $persona_id = intval($persona_id);
                        if ($persona_id > 0) {
                            $stmt->execute([$persona_id, $id]);
                        }
                    }
                }
                
                $pdo->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => $esNuevo ? 'Sobre creado exitosamente' : 'Sobre actualizado exitosamente',
                    'id' => $id
                ]);
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
            
        case 'guardar_pagos':
            $id_sobre = intval($_POST['id_sobre'] ?? 0);
            $anho = intval($_POST['anho'] ?? date('Y'));
            
            if (!$id_sobre) {
                throw new Exception('ID de sobre no proporcionado');
            }
            
            // Verificar que el sobre esté activo
            $stmt = $pdo->prepare("SELECT estado_sobre FROM diezmos WHERE id = ?");
            $stmt->execute([$id_sobre]);
            $sobre = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$sobre) {
                throw new Exception('Sobre no encontrado');
            }
            
            if (intval($sobre['estado_sobre']) === 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se pueden registrar pagos en un sobre inactivo'
                ]);
                exit();
            }
            
            // Obtener montos y fechas de cada mes
            $meses = [
                'enero' => ['monto' => intval($_POST['monto_enero'] ?? 0), 'fecha' => $_POST['fecha_pago_enero'] ?? null],
                'febrero' => ['monto' => intval($_POST['monto_febrero'] ?? 0), 'fecha' => $_POST['fecha_pago_febrero'] ?? null],
                'marzo' => ['monto' => intval($_POST['monto_marzo'] ?? 0), 'fecha' => $_POST['fecha_pago_marzo'] ?? null],
                'abril' => ['monto' => intval($_POST['monto_abril'] ?? 0), 'fecha' => $_POST['fecha_pago_abril'] ?? null],
                'mayo' => ['monto' => intval($_POST['monto_mayo'] ?? 0), 'fecha' => $_POST['fecha_pago_mayo'] ?? null],
                'junio' => ['monto' => intval($_POST['monto_junio'] ?? 0), 'fecha' => $_POST['fecha_pago_junio'] ?? null],
                'julio' => ['monto' => intval($_POST['monto_julio'] ?? 0), 'fecha' => $_POST['fecha_pago_julio'] ?? null],
                'agosto' => ['monto' => intval($_POST['monto_agosto'] ?? 0), 'fecha' => $_POST['fecha_pago_agosto'] ?? null],
                'septiembre' => ['monto' => intval($_POST['monto_septiembre'] ?? 0), 'fecha' => $_POST['fecha_pago_septiembre'] ?? null],
                'octubre' => ['monto' => intval($_POST['monto_octubre'] ?? 0), 'fecha' => $_POST['fecha_pago_octubre'] ?? null],
                'noviembre' => ['monto' => intval($_POST['monto_noviembre'] ?? 0), 'fecha' => $_POST['fecha_pago_noviembre'] ?? null],
                'diciembre' => ['monto' => intval($_POST['monto_diciembre'] ?? 0), 'fecha' => $_POST['fecha_pago_diciembre'] ?? null]
            ];
            
            // Verificar si ya existe un registro para este año
            $stmt = $pdo->prepare("SELECT id FROM pagos_diezmos WHERE id_sobre_diezmo = ? AND anho = ?");
            $stmt->execute([$id_sobre, $anho]);
            $pagoExistente = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($pagoExistente) {
                // Actualizar registro existente
                $stmt = $pdo->prepare("
                    UPDATE pagos_diezmos SET
                        monto_enero = ?, fecha_pago_enero = ?,
                        monto_febrero = ?, fecha_pago_febrero = ?,
                        monto_marzo = ?, fecha_pago_marzo = ?,
                        monto_abril = ?, fecha_pago_abril = ?,
                        monto_mayo = ?, fecha_pago_mayo = ?,
                        monto_junio = ?, fecha_pago_junio = ?,
                        monto_julio = ?, fecha_pago_julio = ?,
                        monto_agosto = ?, fecha_pago_agosto = ?,
                        monto_septiembre = ?, fecha_pago_septiembre = ?,
                        monto_octubre = ?, fecha_pago_octubre = ?,
                        monto_noviembre = ?, fecha_pago_noviembre = ?,
                        monto_diciembre = ?, fecha_pago_diciembre = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $meses['enero']['monto'], $meses['enero']['fecha'] ?: null,
                    $meses['febrero']['monto'], $meses['febrero']['fecha'] ?: null,
                    $meses['marzo']['monto'], $meses['marzo']['fecha'] ?: null,
                    $meses['abril']['monto'], $meses['abril']['fecha'] ?: null,
                    $meses['mayo']['monto'], $meses['mayo']['fecha'] ?: null,
                    $meses['junio']['monto'], $meses['junio']['fecha'] ?: null,
                    $meses['julio']['monto'], $meses['julio']['fecha'] ?: null,
                    $meses['agosto']['monto'], $meses['agosto']['fecha'] ?: null,
                    $meses['septiembre']['monto'], $meses['septiembre']['fecha'] ?: null,
                    $meses['octubre']['monto'], $meses['octubre']['fecha'] ?: null,
                    $meses['noviembre']['monto'], $meses['noviembre']['fecha'] ?: null,
                    $meses['diciembre']['monto'], $meses['diciembre']['fecha'] ?: null,
                    $pagoExistente['id']
                ]);
            } else {
                // Crear nuevo registro
                // Total: 2 (anho, id_sobre_diezmo) + 24 (12 meses * 2 campos cada uno) = 26 placeholders
                $stmt = $pdo->prepare("
                    INSERT INTO pagos_diezmos (
                        anho, id_sobre_diezmo,
                        monto_enero, fecha_pago_enero,
                        monto_febrero, fecha_pago_febrero,
                        monto_marzo, fecha_pago_marzo,
                        monto_abril, fecha_pago_abril,
                        monto_mayo, fecha_pago_mayo,
                        monto_junio, fecha_pago_junio,
                        monto_julio, fecha_pago_julio,
                        monto_agosto, fecha_pago_agosto,
                        monto_septiembre, fecha_pago_septiembre,
                        monto_octubre, fecha_pago_octubre,
                        monto_noviembre, fecha_pago_noviembre,
                        monto_diciembre, fecha_pago_diciembre
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $anho, $id_sobre,
                    $meses['enero']['monto'], $meses['enero']['fecha'] ?: null,
                    $meses['febrero']['monto'], $meses['febrero']['fecha'] ?: null,
                    $meses['marzo']['monto'], $meses['marzo']['fecha'] ?: null,
                    $meses['abril']['monto'], $meses['abril']['fecha'] ?: null,
                    $meses['mayo']['monto'], $meses['mayo']['fecha'] ?: null,
                    $meses['junio']['monto'], $meses['junio']['fecha'] ?: null,
                    $meses['julio']['monto'], $meses['julio']['fecha'] ?: null,
                    $meses['agosto']['monto'], $meses['agosto']['fecha'] ?: null,
                    $meses['septiembre']['monto'], $meses['septiembre']['fecha'] ?: null,
                    $meses['octubre']['monto'], $meses['octubre']['fecha'] ?: null,
                    $meses['noviembre']['monto'], $meses['noviembre']['fecha'] ?: null,
                    $meses['diciembre']['monto'], $meses['diciembre']['fecha'] ?: null
                ]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Pagos guardados exitosamente'
            ]);
            break;
            
        case 'cambiar_estado':
            // Solo administradores o admin pueden cambiar el estado
            if (!$esAdministrador && !$esAdmin) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No tienes permisos para cambiar el estado de los sobres'
                ]);
                exit();
            }
            
            $id = intval($_POST['id'] ?? 0);
            $estado = intval($_POST['estado'] ?? 1);
            
            // Validar que el estado sea 0 o 1
            if ($estado !== 0 && $estado !== 1) {
                throw new Exception('Estado inválido');
            }
            
            if (!$id) {
                throw new Exception('ID de sobre no proporcionado');
            }
            
            $stmt = $pdo->prepare("UPDATE diezmos SET estado_sobre = ?, fecha_actualizacion = CURDATE() WHERE id = ?");
            $stmt->execute([$estado, $id]);
            
            $accion = $estado === 1 ? 'activado' : 'desactivado';
            echo json_encode([
                'success' => true,
                'message' => "Sobre {$accion} exitosamente"
            ]);
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
    
} catch (PDOException $e) {
    error_log("Error PDO en diezmos_actions.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    error_log("SQL State: " . $e->getCode());
    error_log("Action: " . ($_REQUEST['action'] ?? 'N/A'));
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Error en diezmos_actions.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    error_log("Action: " . ($_REQUEST['action'] ?? 'N/A'));
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

