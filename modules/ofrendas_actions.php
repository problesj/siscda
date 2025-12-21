<?php
require_once dirname(__DIR__) . '/session_config.php';
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth_functions.php';

// Verificar autenticación
verificarAutenticacion();

// Verificar acceso al módulo de Ofrendas
verificarAccesoModulo('Ofrendas');

// Verificar si el usuario es Administrador del módulo
$esAdministrador = esAdministradorModulo($_SESSION['usuario_id'], 'Ofrendas');

// Verificar si el usuario es admin
$esAdmin = isset($_SESSION['username']) && strtolower($_SESSION['username']) === 'admin';

header('Content-Type: application/json');

try {
    $pdo = conectarDB();
    $action = $_REQUEST['action'] ?? '';
    
    switch ($action) {
        case 'obtener_ofrendas':
            try {
                $stmt = $pdo->prepare("
                    SELECT 
                        o.id,
                        o.monto,
                        o.fecha_ofrenda,
                        o.id_culto,
                        o.estado_ofrenda,
                        c.TIPO_CULTO as tipo_culto
                    FROM ofrendas o
                    LEFT JOIN cultos c ON o.id_culto = c.ID
                    ORDER BY o.fecha_ofrenda DESC, o.id DESC
                ");
                $stmt->execute();
                $ofrendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'ofrendas' => $ofrendas
                ]);
            } catch (PDOException $e) {
                error_log("Error en obtener_ofrendas: " . $e->getMessage());
                throw $e;
            }
            break;
            
        case 'obtener_detalle':
            $id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
            
            if (!$id) {
                throw new Exception('ID de ofrenda no proporcionado');
            }
            
            $stmt = $pdo->prepare("
                SELECT 
                    o.id,
                    o.monto,
                    o.fecha_ofrenda,
                    o.id_culto,
                    o.estado_ofrenda,
                    o.`20000` as cantidad_20000,
                    o.`10000` as cantidad_10000,
                    o.`5000` as cantidad_5000,
                    o.`2000` as cantidad_2000,
                    o.`1000` as cantidad_1000,
                    o.`500` as cantidad_500,
                    o.`100` as cantidad_100,
                    o.`50` as cantidad_50,
                    o.`10` as cantidad_10,
                    o.observaciones,
                    c.TIPO_CULTO as tipo_culto
                FROM ofrendas o
                LEFT JOIN cultos c ON o.id_culto = c.ID
                WHERE o.id = ?
            ");
            $stmt->execute([$id]);
            $ofrenda = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$ofrenda) {
                throw new Exception('Ofrenda no encontrada');
            }
            
            echo json_encode([
                'success' => true,
                'ofrenda' => $ofrenda
            ]);
            break;
            
        case 'guardar_ofrenda':
            // Solo administradores pueden editar ofrendas
            if (!$esAdministrador) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No tienes permisos para editar ofrendas'
                ]);
                exit();
            }
            $id = intval($_POST['id'] ?? 0);
            $id_culto = intval($_POST['id_culto'] ?? 0);
            $fecha_ofrenda = $_POST['fecha_ofrenda'] ?? '';
            $observaciones = substr(trim($_POST['observaciones'] ?? ''), 0, 300); // Limitar a 300 caracteres
            
            // Obtener cantidades de billetes y monedas
            $cantidades = [
                'cantidad_20000' => intval($_POST['cantidad_20000'] ?? 0),
                'cantidad_10000' => intval($_POST['cantidad_10000'] ?? 0),
                'cantidad_5000' => intval($_POST['cantidad_5000'] ?? 0),
                'cantidad_2000' => intval($_POST['cantidad_2000'] ?? 0),
                'cantidad_1000' => intval($_POST['cantidad_1000'] ?? 0),
                'cantidad_500' => intval($_POST['cantidad_500'] ?? 0),
                'cantidad_100' => intval($_POST['cantidad_100'] ?? 0),
                'cantidad_50' => intval($_POST['cantidad_50'] ?? 0),
                'cantidad_10' => intval($_POST['cantidad_10'] ?? 0)
            ];
            
            // Calcular el monto total
            $montos = [
                'cantidad_20000' => 20000,
                'cantidad_10000' => 10000,
                'cantidad_5000' => 5000,
                'cantidad_2000' => 2000,
                'cantidad_1000' => 1000,
                'cantidad_500' => 500,
                'cantidad_100' => 100,
                'cantidad_50' => 50,
                'cantidad_10' => 10
            ];
            
            $montoTotal = 0;
            foreach ($cantidades as $campo => $cantidad) {
                $montoTotal += $cantidad * $montos[$campo];
            }
            
            if (!$id) {
                throw new Exception('ID de ofrenda no proporcionado');
            }
            
            // Verificar si la ofrenda está bloqueada
            $stmt_check = $pdo->prepare("SELECT estado_ofrenda FROM ofrendas WHERE id = ?");
            $stmt_check->execute([$id]);
            $ofrenda_check = $stmt_check->fetch(PDO::FETCH_ASSOC);
            
            if (!$ofrenda_check) {
                throw new Exception('Ofrenda no encontrada');
            }
            
            if (intval($ofrenda_check['estado_ofrenda']) === 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Esta ofrenda está bloqueada y no se puede editar'
                ]);
                exit();
            }
            
            // Actualizar la ofrenda con monto total, cantidades por denominación y observaciones
            $stmt = $pdo->prepare("
                UPDATE ofrendas 
                SET monto = ?, 
                    fecha_modificacion = NOW(),
                    `20000` = ?,
                    `10000` = ?,
                    `5000` = ?,
                    `2000` = ?,
                    `1000` = ?,
                    `500` = ?,
                    `100` = ?,
                    `50` = ?,
                    `10` = ?,
                    observaciones = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $montoTotal,
                $cantidades['cantidad_20000'],
                $cantidades['cantidad_10000'],
                $cantidades['cantidad_5000'],
                $cantidades['cantidad_2000'],
                $cantidades['cantidad_1000'],
                $cantidades['cantidad_500'],
                $cantidades['cantidad_100'],
                $cantidades['cantidad_50'],
                $cantidades['cantidad_10'],
                $observaciones,
                $id
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Ofrenda guardada exitosamente',
                'monto' => $montoTotal
            ]);
            break;
            
        case 'cambiar_estado':
            // Solo administradores o admin pueden cambiar el estado
            if (!$esAdministrador && !$esAdmin) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No tienes permisos para cambiar el estado de las ofrendas'
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
                throw new Exception('ID de ofrenda no proporcionado');
            }
            
            $stmt = $pdo->prepare("UPDATE ofrendas SET estado_ofrenda = ? WHERE id = ?");
            $stmt->execute([$estado, $id]);
            
            $accion = $estado === 1 ? 'desbloqueada' : 'bloqueada';
            echo json_encode([
                'success' => true,
                'message' => "Ofrenda {$accion} exitosamente"
            ]);
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
    
} catch (PDOException $e) {
    error_log("Error PDO en ofrendas_actions.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    error_log("SQL State: " . $e->getCode());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Error en ofrendas_actions.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

