<?php
/**
 * Script para inicializar los módulos y roles del sistema
 * Ejecutar una sola vez para poblar las tablas iniciales
 */

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth_functions.php';

try {
    $pdo = conectarDB();
    $pdo->beginTransaction();
    
    // 1. Insertar roles del sistema
    echo "Insertando roles del sistema...\n";
    $roles = ['Administrador', 'Usuario'];
    
    foreach ($roles as $rol) {
        // Verificar si ya existe
        $stmt = $pdo->prepare("SELECT id FROM roles_sistema WHERE nombre_rol = ?");
        $stmt->execute([$rol]);
        
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO roles_sistema (nombre_rol) VALUES (?)");
            $stmt->execute([$rol]);
            echo "  ✓ Rol '$rol' insertado\n";
        } else {
            echo "  → Rol '$rol' ya existe, omitiendo\n";
        }
    }
    
    // 2. Insertar módulos
    echo "\nInsertando módulos...\n";
    $modulos = [
        'Usuarios',
        'Personas',
        'Cultos',
        'Asistencias',
        'Reportes',
        'Ofrendas',
        'Diezmos'
    ];
    
    foreach ($modulos as $modulo) {
        // Verificar si ya existe
        $stmt = $pdo->prepare("SELECT id FROM modulos WHERE nombre_modulo = ?");
        $stmt->execute([$modulo]);
        
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO modulos (nombre_modulo, estado_modulo, fecha_creacion, fecha_actualizacion) VALUES (?, 1, CURDATE(), CURDATE())");
            $stmt->execute([$modulo]);
            $idInsertado = $pdo->lastInsertId();
            echo "  ✓ Módulo '$modulo' insertado (ID: $idInsertado)\n";
        } else {
            echo "  → Módulo '$modulo' ya existe, omitiendo\n";
        }
    }
    
    // 3. Asignar privilegios de administrador al usuario admin
    echo "\nAsignando privilegios al usuario admin...\n";
    
    // Buscar el usuario admin (puede ser 'admin' o 'ADMIN')
    $stmt = $pdo->prepare("SELECT USUARIO_ID FROM usuarios WHERE LOWER(USERNAME) = 'admin' LIMIT 1");
    $stmt->execute();
    $usuarioAdmin = $stmt->fetch();
    
    if ($usuarioAdmin) {
        $usuarioAdminId = (int)$usuarioAdmin['USUARIO_ID'];
        echo "  → Usuario admin encontrado (ID: $usuarioAdminId)\n";
        
        // Obtener ID del rol Administrador
        $stmt = $pdo->prepare("SELECT id FROM roles_sistema WHERE nombre_rol = 'Administrador'");
        $stmt->execute();
        $rolAdmin = $stmt->fetch();
        
        if ($rolAdmin) {
            $rolAdminId = (int)$rolAdmin['id'];
            echo "  → Rol Administrador encontrado (ID: $rolAdminId)\n";
            
            // Obtener todos los módulos activos
            $stmt = $pdo->query("SELECT id FROM modulos WHERE estado_modulo = 1");
            $modulos = $stmt->fetchAll();
            
            $privilegiosAsignados = 0;
            foreach ($modulos as $modulo) {
                $moduloId = (int)$modulo['id'];
                
                // Verificar si ya existe el privilegio
                $stmt = $pdo->prepare("SELECT id FROM privilegios WHERE id_usuario = ? AND id_modulo = ?");
                $stmt->execute([$usuarioAdminId, $moduloId]);
                
                if (!$stmt->fetch()) {
                    // Insertar el privilegio
                    $stmt = $pdo->prepare("INSERT INTO privilegios (id_usuario, id_modulo, id_rol_sistema, fecha_registro) VALUES (?, ?, ?, NOW())");
                    $stmt->execute([$usuarioAdminId, $moduloId, $rolAdminId]);
                    $privilegiosAsignados++;
                    
                    // Obtener nombre del módulo para el mensaje
                    $stmtNombre = $pdo->prepare("SELECT nombre_modulo FROM modulos WHERE id = ?");
                    $stmtNombre->execute([$moduloId]);
                    $moduloNombre = $stmtNombre->fetch();
                    echo "    ✓ Privilegio asignado: " . ($moduloNombre['nombre_modulo'] ?? "Módulo ID $moduloId") . "\n";
                } else {
                    // Obtener nombre del módulo para el mensaje
                    $stmtNombre = $pdo->prepare("SELECT nombre_modulo FROM modulos WHERE id = ?");
                    $stmtNombre->execute([$moduloId]);
                    $moduloNombre = $stmtNombre->fetch();
                    echo "    → Privilegio ya existe: " . ($moduloNombre['nombre_modulo'] ?? "Módulo ID $moduloId") . "\n";
                }
            }
            
            echo "  ✓ Total de privilegios asignados/verificados: $privilegiosAsignados\n";
        } else {
            echo "  ✗ Error: No se encontró el rol 'Administrador'\n";
        }
    } else {
        echo "  ⚠ Advertencia: No se encontró el usuario 'admin'. Verifica que el usuario exista en la tabla usuarios.\n";
    }
    
    $pdo->commit();
    echo "\n✓ Proceso completado exitosamente!\n";
    echo "\nNota: El usuario admin ahora tiene acceso de administrador a todos los módulos.\n";
    echo "Puedes asignar privilegios a otros usuarios desde la interfaz de administración.\n";
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>

