<?php
require_once 'config.php';

echo "=== DEBUG DE CARGA DE PERSONAS ===\n\n";

try {
    echo "1. Conectando a la base de datos...\n";
    $pdo = conectarDB();
    echo "✅ Conexión exitosa\n\n";
    
    echo "2. Verificando si la tabla personas existe...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'personas'");
    $tablaExiste = $stmt->fetch();
    if ($tablaExiste) {
        echo "✅ Tabla 'personas' existe\n\n";
    } else {
        echo "❌ Tabla 'personas' NO existe\n";
        exit();
    }
    
    echo "3. Verificando estructura de la tabla personas...\n";
    $stmt = $pdo->query("DESCRIBE personas");
    $columnas = $stmt->fetchAll();
    echo "Columnas encontradas:\n";
    foreach ($columnas as $columna) {
        echo "  - " . $columna['Field'] . " (" . $columna['Type'] . ")\n";
    }
    echo "\n";
    
    echo "4. Contando total de personas...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM personas");
    $total = $stmt->fetch();
    echo "Total de personas: " . $total['total'] . "\n\n";
    
    echo "5. Verificando consulta principal...\n";
    $query = "SELECT p.*, gf.NOMBRE as grupo_familiar, r.nombre_rol as rol_nombre
              FROM personas p 
              LEFT JOIN grupos_familiares gf ON p.GRUPO_FAMILIAR_ID = gf.ID 
              LEFT JOIN roles r ON p.ROL = r.id
              ORDER BY p.ID
              LIMIT 5";
    
    echo "Query: " . $query . "\n";
    $stmt = $pdo->query($query);
    $personas = $stmt->fetchAll();
    
    echo "Personas encontradas: " . count($personas) . "\n";
    if (count($personas) > 0) {
        echo "Primera persona:\n";
        $primera = $personas[0];
        foreach ($primera as $key => $value) {
            echo "  $key: " . ($value ?? 'NULL') . "\n";
        }
    }
    echo "\n";
    
    echo "6. Verificando tablas relacionadas...\n";
    
    // Verificar grupos_familiares
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM grupos_familiares");
    $totalGF = $stmt->fetch();
    echo "Total grupos_familiares: " . $totalGF['total'] . "\n";
    
    // Verificar roles
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM roles");
    $totalRoles = $stmt->fetch();
    echo "Total roles: " . $totalRoles['total'] . "\n\n";
    
    echo "7. Verificando permisos de usuario...\n";
    $stmt = $pdo->query("SELECT USER(), DATABASE()");
    $permisos = $stmt->fetch();
    echo "Usuario actual: " . $permisos['USER()'] . "\n";
    echo "Base de datos: " . $permisos['DATABASE()'] . "\n\n";
    
    echo "8. Verificando configuración de PHP...\n";
    echo "display_errors: " . ini_get('display_errors') . "\n";
    echo "error_reporting: " . ini_get('error_reporting') . "\n";
    echo "log_errors: " . ini_get('log_errors') . "\n";
    echo "error_log: " . ini_get('error_log') . "\n\n";
    
    echo "=== FIN DEL DEBUG ===\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
?>
