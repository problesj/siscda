<?php
require_once __DIR__ . '/session_config.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth_functions.php';

// Verificar autenticación
verificarAutenticacion();

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    $pdo = conectarDB();
    
    switch ($action) {
        case 'asistencias_domingos':
            // Obtener asistencias de domingos por mes
            // Primero intentar con domingos, si no hay datos, mostrar todos los cultos
            $sql = "SELECT 
                        DATE_FORMAT(c.FECHA, '%Y-%m') as mes,
                        DATE_FORMAT(c.FECHA, '%b %Y') as mes_nombre,
                        COUNT(a.PERSONA_ID) as total_asistencias
                    FROM cultos c
                    INNER JOIN asistencias a ON c.ID = a.CULTO_ID
                    WHERE DAYOFWEEK(c.FECHA) = 1  -- Domingo = 1 en MySQL
                    AND c.FECHA >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                    GROUP BY DATE_FORMAT(c.FECHA, '%Y-%m'), DATE_FORMAT(c.FECHA, '%b %Y')
                    ORDER BY mes ASC";
            
            $stmt = $pdo->query($sql);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Si no hay datos de domingos, obtener todas las asistencias por mes
            if (empty($datos)) {
                $sql = "SELECT 
                            DATE_FORMAT(c.FECHA, '%Y-%m') as mes,
                            DATE_FORMAT(c.FECHA, '%b %Y') as mes_nombre,
                            COUNT(a.PERSONA_ID) as total_asistencias
                        FROM cultos c
                        INNER JOIN asistencias a ON c.ID = a.CULTO_ID
                        WHERE c.FECHA >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                        GROUP BY DATE_FORMAT(c.FECHA, '%Y-%m')
                        ORDER BY mes ASC";
                
                $stmt = $pdo->query($sql);
                $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            // Log para debugging (remover en producción si es necesario)
            error_log("Asistencias domingos - Datos encontrados: " . count($datos));
            if (!empty($datos)) {
                error_log("Primer registro: " . json_encode($datos[0]));
            }
            
            echo json_encode([
                'success' => true,
                'data' => $datos
            ], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'ofrendas_mes':
            // Obtener ofrendas por mes basado en la fecha del culto
            $sql = "SELECT 
                        DATE_FORMAT(c.FECHA, '%Y-%m') as mes,
                        DATE_FORMAT(c.FECHA, '%b %Y') as mes_nombre,
                        COALESCE(SUM(o.monto), 0) as total_ofrendas
                    FROM ofrendas o
                    INNER JOIN cultos c ON o.id_culto = c.ID
                    WHERE c.FECHA >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                    GROUP BY DATE_FORMAT(c.FECHA, '%Y-%m'),DATE_FORMAT(c.FECHA, '%b %Y')
                    HAVING SUM(o.monto) > 0
                    ORDER BY mes ASC";
            
            $stmt = $pdo->query($sql);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Log para debugging (remover en producción si es necesario)
            error_log("Ofrendas por mes - Datos encontrados: " . count($datos));
            if (!empty($datos)) {
                error_log("Primer registro: " . json_encode($datos[0]));
            }
            
            echo json_encode([
                'success' => true,
                'data' => $datos
            ], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'diezmos_mes':
            // Obtener diezmos por mes
            $sql = "SELECT 
                        CONCAT(anho, '-', LPAD(mes_num, 2, '0')) as mes,
                        DATE_FORMAT(STR_TO_DATE(CONCAT(anho, '-', LPAD(mes_num, 2, '0'), '-01'), '%Y-%m-%d'), '%b %Y') as mes_nombre,
                        SUM(monto) as total_diezmos
                    FROM (
                        SELECT anho, 1 as mes_num, COALESCE(monto_enero, 0) as monto FROM pagos_diezmos WHERE monto_enero > 0
                        UNION ALL
                        SELECT anho, 2 as mes_num, COALESCE(monto_febrero, 0) as monto FROM pagos_diezmos WHERE monto_febrero > 0
                        UNION ALL
                        SELECT anho, 3 as mes_num, COALESCE(monto_marzo, 0) as monto FROM pagos_diezmos WHERE monto_marzo > 0
                        UNION ALL
                        SELECT anho, 4 as mes_num, COALESCE(monto_abril, 0) as monto FROM pagos_diezmos WHERE monto_abril > 0
                        UNION ALL
                        SELECT anho, 5 as mes_num, COALESCE(monto_mayo, 0) as monto FROM pagos_diezmos WHERE monto_mayo > 0
                        UNION ALL
                        SELECT anho, 6 as mes_num, COALESCE(monto_junio, 0) as monto FROM pagos_diezmos WHERE monto_junio > 0
                        UNION ALL
                        SELECT anho, 7 as mes_num, COALESCE(monto_julio, 0) as monto FROM pagos_diezmos WHERE monto_julio > 0
                        UNION ALL
                        SELECT anho, 8 as mes_num, COALESCE(monto_agosto, 0) as monto FROM pagos_diezmos WHERE monto_agosto > 0
                        UNION ALL
                        SELECT anho, 9 as mes_num, COALESCE(monto_septiembre, 0) as monto FROM pagos_diezmos WHERE monto_septiembre > 0
                        UNION ALL
                        SELECT anho, 10 as mes_num, COALESCE(monto_octubre, 0) as monto FROM pagos_diezmos WHERE monto_octubre > 0
                        UNION ALL
                        SELECT anho, 11 as mes_num, COALESCE(monto_noviembre, 0) as monto FROM pagos_diezmos WHERE monto_noviembre > 0
                        UNION ALL
                        SELECT anho, 12 as mes_num, COALESCE(monto_diciembre, 0) as monto FROM pagos_diezmos WHERE monto_diciembre > 0
                    ) as diezmos_mensuales
                    WHERE STR_TO_DATE(CONCAT(anho, '-', LPAD(mes_num, 2, '0'), '-01'), '%Y-%m-%d') >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                    GROUP BY anho, mes_num
                    ORDER BY mes ASC";
            
            $stmt = $pdo->query($sql);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $datos
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Acción no válida'
            ]);
            break;
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>

