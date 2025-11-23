<?php
session_start();
require_once __DIR__ . '/../includes/auth_functions.php';

// Verificar si PhpSpreadsheet está disponible
$phpspreadsheet_available = false;
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    try {
        require_once __DIR__ . '/../vendor/autoload.php';
        $phpspreadsheet_available = true;
    } catch (Exception $e) {
        error_log("Error cargando PhpSpreadsheet: " . $e->getMessage());
        $phpspreadsheet_available = false;
    }
}

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

try {
    $pdo = conectarDB();
    
    // Obtener todas las personas con el mismo criterio de ordenamiento que asistencias
    $sql = "SELECT p.*, gf.NOMBRE as GRUPO_FAMILIAR_NOMBRE, r.nombre_rol as ROL_NOMBRE 
            FROM personas p 
            LEFT JOIN grupos_familiares gf ON p.GRUPO_FAMILIAR_ID = gf.ID 
            LEFT JOIN roles r ON p.ROL = r.id 
            ORDER BY 
                CASE WHEN gf.NOMBRE IS NOT NULL AND gf.NOMBRE != '' THEN 0 ELSE 1 END,
                gf.NOMBRE,
                CASE WHEN p.FAMILIA IS NOT NULL AND p.FAMILIA != '' THEN 0 ELSE 1 END,
                p.FAMILIA,
                CASE WHEN p.APELLIDO_PATERNO IS NOT NULL AND p.APELLIDO_PATERNO != '' THEN 0 ELSE 1 END,
                p.APELLIDO_PATERNO";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $personas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($phpspreadsheet_available) {
        // Usar PhpSpreadsheet para generar Excel
        generarExcelConPhpSpreadsheet($personas);
    } else {
        // Usar CSV como alternativa
        generarCSV($personas);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al exportar: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al generar archivo: ' . $e->getMessage()]);
}

function generarExcelConPhpSpreadsheet($personas) {
    // Crear una nueva instancia de Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Configurar encabezados
    $headers = [
        'A' => 'RUT',
        'B' => 'Nombres', 
        'C' => 'Apellido Paterno',
        'D' => 'Apellido Materno',
        'E' => 'Fecha Nacimiento',
        'F' => 'Teléfono',
        'G' => 'Email',
        'H' => 'Dirección',
        'I' => 'Familia',
        'J' => 'Grupo Familiar',
        'K' => 'Rol',
        'L' => 'Observaciones',
        'M' => 'Fecha Creación',
        'N' => 'Fecha Actualización'
    ];
    
    // Establecer encabezados
    foreach ($headers as $col => $header) {
        $sheet->setCellValue($col . '1', $header);
    }
    
    // Formatear encabezados
    $headerRange = 'A1:N1';
    $sheet->getStyle($headerRange)->applyFromArray([
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF']
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '1E3A8A']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000']
            ]
        ]
    ]);
    
    // Variables para el formato de colores (igual que en asistencias)
    $familiaActual = '';
    $colorAlternado = true;
    $row = 2; // Empezar desde la fila 2 (después de los encabezados)
    
    // Llenar datos con formato de colores
    foreach ($personas as $persona) {
        // Determinar el color de la fila según la familia (igual que en asistencias)
        $familiaPersona = $persona['FAMILIA'] ?? '';
        if ($familiaPersona !== $familiaActual) {
            $familiaActual = $familiaPersona;
            $colorAlternado = !$colorAlternado;
        }
        
        // Determinar el color hexadecimal directamente (igual que en asistencias)
        $colorHex = '';
        if ($familiaPersona === '') {
            // Sin familia: alternar gris claro y blanco
            $colorHex = $colorAlternado ? 'F8F9FA' : 'FFFFFF';
        } else {
            // Con familia: alternar verde claro y amarillo claro
            $colorHex = $colorAlternado ? 'D1ECF1' : 'FFF3CD';
        }
        
        // Establecer datos en las celdas
        $sheet->setCellValue('A' . $row, $persona['RUT'] ?? '');
        $sheet->setCellValue('B' . $row, $persona['NOMBRES'] ?? '');
        $sheet->setCellValue('C' . $row, $persona['APELLIDO_PATERNO'] ?? '');
        $sheet->setCellValue('D' . $row, $persona['APELLIDO_MATERNO'] ?? '');
        $sheet->setCellValue('E' . $row, $persona['FECHA_NACIMIENTO'] ?? '');
        $sheet->setCellValue('F' . $row, $persona['TELEFONO'] ?? '');
        $sheet->setCellValue('G' . $row, $persona['EMAIL'] ?? '');
        $sheet->setCellValue('H' . $row, $persona['DIRECCION'] ?? '');
        $sheet->setCellValue('I' . $row, $persona['FAMILIA'] ?? '');
        $sheet->setCellValue('J' . $row, $persona['GRUPO_FAMILIAR_NOMBRE'] ?? '');
        $sheet->setCellValue('K' . $row, $persona['ROL_NOMBRE'] ?? '');
        $sheet->setCellValue('L' . $row, $persona['OBSERVACIONES'] ?? '');
        $sheet->setCellValue('M' . $row, $persona['FECHA_CREACION'] ?? '');
        $sheet->setCellValue('N' . $row, $persona['FECHA_ACTUALIZACION'] ?? '');
        
        // Aplicar formato de color a la fila
        $rowRange = 'A' . $row . ':N' . $row;
        $sheet->getStyle($rowRange)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => $colorHex]
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'DEE2E6']
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        
        $row++;
    }
    
    // Ajustar ancho de columnas
    foreach (range('A', 'N') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Configurar headers para descarga
    $filename = 'personas_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');
    
    // Crear el archivo Excel
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    
    // Limpiar memoria
    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet);
}

function generarCSV($personas) {
    // Configurar headers para descarga CSV
    $filename = 'personas_' . date('Y-m-d_H-i-s') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');
    
    // Crear el archivo CSV
    $output = fopen('php://output', 'w');
    
    // Agregar BOM para UTF-8 (para que Excel abra correctamente los caracteres especiales)
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Escribir encabezados
    $headers = [
        'RUT',
        'Nombres', 
        'Apellido Paterno',
        'Apellido Materno',
        'Fecha Nacimiento',
        'Teléfono',
        'Email',
        'Dirección',
        'Familia',
        'Grupo Familiar',
        'Rol',
        'Observaciones',
        'Fecha Creación',
        'Fecha Actualización'
    ];
    fputcsv($output, $headers, ';');
    
    // Escribir datos
    foreach ($personas as $persona) {
        $row = [
            $persona['RUT'] ?? '',
            $persona['NOMBRES'] ?? '',
            $persona['APELLIDO_PATERNO'] ?? '',
            $persona['APELLIDO_MATERNO'] ?? '',
            $persona['FECHA_NACIMIENTO'] ?? '',
            $persona['TELEFONO'] ?? '',
            $persona['EMAIL'] ?? '',
            $persona['DIRECCION'] ?? '',
            $persona['FAMILIA'] ?? '',
            $persona['GRUPO_FAMILIAR_NOMBRE'] ?? '',
            $persona['ROL_NOMBRE'] ?? '',
            $persona['OBSERVACIONES'] ?? '',
            $persona['FECHA_CREACION'] ?? '',
            $persona['FECHA_ACTUALIZACION'] ?? ''
        ];
        fputcsv($output, $row, ';');
    }
    
    fclose($output);
}
?>
