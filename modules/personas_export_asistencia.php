<?php
session_start();
require_once __DIR__ . '/../includes/auth_functions.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

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
    
    // Crear una nueva instancia de Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Configurar encabezados para Formato Asistencia
    $headers = [
        'A' => 'N°',
        'B' => 'Nombre', 
        'C' => 'Apellido Paterno',
        'D' => 'Familia',
        'E' => 'Agrupación',
        'F' => 'Observaciones',
        'G' => 'Asistió'
    ];
    
    // Establecer encabezados
    foreach ($headers as $col => $header) {
        $sheet->setCellValue($col . '1', $header);
    }
    
    // Formatear encabezados con estilo profesional
    $headerRange = 'A1:G1';
    $sheet->getStyle($headerRange)->applyFromArray([
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
            'size' => 12
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '4472C4'] // Azul más oscuro para encabezados
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
    $numeroFila = 1;
    
    // Llenar datos con formato de colores por familia
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
            // Con familia: alternar azul claro y naranja claro (como en la imagen)
            $colorHex = $colorAlternado ? 'B4C6E7' : 'FFE699';
        }
        
        // Establecer datos en las celdas
        $sheet->setCellValue('A' . $row, $numeroFila);
        $sheet->setCellValue('B' . $row, $persona['NOMBRES'] ?? '');
        $sheet->setCellValue('C' . $row, $persona['APELLIDO_PATERNO'] ?? '');
        $sheet->setCellValue('D' . $row, $persona['FAMILIA'] ?? '');
        $sheet->setCellValue('E' . $row, $persona['GRUPO_FAMILIAR_NOMBRE'] ?? '');
        $sheet->setCellValue('F' . $row, $persona['OBSERVACIONES'] ?? '');
        $sheet->setCellValue('G' . $row, ''); // Columna Asistió vacía para marcar manualmente
        
        // Aplicar formato de color por familia a la fila
        $rowRange = 'A' . $row . ':G' . $row;
        $sheet->getStyle($rowRange)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => $colorHex]
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D0D0D0'] // Líneas más visibles como en la imagen
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'horizontal' => Alignment::HORIZONTAL_LEFT
            ],
            'font' => [
                'size' => 11
            ]
        ]);
        
        // Formato especial para la columna de número (centrado)
        $sheet->getStyle('A' . $row)->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ]);
        
        // Formato especial para la columna Asistió (centrado)
        $sheet->getStyle('G' . $row)->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ]);
        
        $row++;
        $numeroFila++;
    }
    
    // Ajustar ancho de columnas
    $sheet->getColumnDimension('A')->setWidth(8);  // N°
    $sheet->getColumnDimension('B')->setWidth(20); // Nombre
    $sheet->getColumnDimension('C')->setWidth(20); // Apellido Paterno
    $sheet->getColumnDimension('D')->setWidth(25); // Familia
    $sheet->getColumnDimension('E')->setWidth(25); // Agrupación
    $sheet->getColumnDimension('F')->setWidth(30); // Observaciones
    $sheet->getColumnDimension('G')->setWidth(12); // Asistió
    
    // Configurar headers para descarga
    $filename = 'lista_asistencia_' . date('Y-m-d_H-i-s') . '.xlsx';
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
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al exportar: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al generar Excel: ' . $e->getMessage()]);
}
?>
