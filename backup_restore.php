<?php
/**
 * Script de Backup y Restauración del Sistema CDA
 * 
 * INSTRUCCIONES:
 * 1. Este script debe ejecutarse desde la línea de comandos
 * 2. Asegúrate de tener permisos de escritura en el directorio
 * 3. Para mayor seguridad, ejecuta este script fuera del directorio web
 */

// Verificar si se ejecuta desde línea de comandos
if (php_sapi_name() !== 'cli') {
    die("Este script debe ejecutarse desde la línea de comandos.\n");
}

// Incluir configuración
if (file_exists('config.php')) {
    require_once 'config.php';
} else {
    die("Error: No se encontró el archivo config.php\n");
}

// Configuración
$backupDir = 'backups/';
$maxBackups = 10; // Mantener solo los últimos 10 backups

// Crear directorio de backups si no existe
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

/**
 * Función para hacer backup de la base de datos
 */
function hacerBackup() {
    global $backupDir;
    
    try {
        $pdo = conectarDB();
        
        // Obtener información de la base de datos
        $dbName = DB_NAME;
        $timestamp = date('Y-m-d_H-i-s');
        $filename = $backupDir . "backup_{$dbName}_{$timestamp}.sql";
        
        // Comando mysqldump
        $command = sprintf(
            'mysqldump --host=%s --user=%s --password=%s --single-transaction --routines --triggers --add-drop-table --create-options --complete-insert --extended-insert --set-charset --default-character-set=utf8mb4 %s > %s',
            escapeshellarg(DB_HOST),
            escapeshellarg(DB_USER),
            escapeshellarg(DB_PASS),
            escapeshellarg($dbName),
            escapeshellarg($filename)
        );
        
        echo "Haciendo backup de la base de datos...\n";
        echo "Comando: $command\n";
        
        // Ejecutar comando
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            echo "✅ Backup completado exitosamente: $filename\n";
            
            // Comprimir el archivo
            $gzFilename = $filename . '.gz';
            $gz = gzopen($gzFilename, 'w9');
            gzwrite($gz, file_get_contents($filename));
            gzclose($gz);
            
            // Eliminar archivo sin comprimir
            unlink($filename);
            
            echo "✅ Backup comprimido: $gzFilename\n";
            
            // Limpiar backups antiguos
            limpiarBackupsAntiguos();
            
            return $gzFilename;
        } else {
            echo "❌ Error al hacer backup. Código de retorno: $returnCode\n";
            if (!empty($output)) {
                echo "Salida del comando:\n";
                foreach ($output as $line) {
                    echo "  $line\n";
                }
            }
            return false;
        }
        
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
        return false;
    }
}

/**
 * Función para restaurar la base de datos desde un backup
 */
function restaurarBackup($backupFile) {
    try {
        $pdo = conectarDB();
        
        // Verificar que el archivo existe
        if (!file_exists($backupFile)) {
            echo "❌ Error: El archivo de backup no existe: $backupFile\n";
            return false;
        }
        
        // Si es un archivo comprimido, descomprimirlo
        $tempFile = null;
        if (pathinfo($backupFile, PATHINFO_EXTENSION) === 'gz') {
            echo "Descomprimiendo archivo de backup...\n";
            $tempFile = tempnam(sys_get_temp_dir(), 'backup_');
            $gz = gzopen($backupFile, 'r');
            $content = '';
            while (!gzeof($gz)) {
                $content .= gzread($gz, 8192);
            }
            gzclose($gz);
            file_put_contents($tempFile, $content);
            $backupFile = $tempFile;
        }
        
        echo "Restaurando base de datos desde: $backupFile\n";
        
        // Comando mysql para restaurar
        $command = sprintf(
            'mysql --host=%s --user=%s --password=%s %s < %s',
            escapeshellarg(DB_HOST),
            escapeshellarg(DB_USER),
            escapeshellarg(DB_PASS),
            escapeshellarg(DB_NAME),
            escapeshellarg($backupFile)
        );
        
        echo "Comando: $command\n";
        
        // Ejecutar comando
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        // Limpiar archivo temporal
        if ($tempFile && file_exists($tempFile)) {
            unlink($tempFile);
        }
        
        if ($returnCode === 0) {
            echo "✅ Restauración completada exitosamente\n";
            return true;
        } else {
            echo "❌ Error al restaurar. Código de retorno: $returnCode\n";
            if (!empty($output)) {
                echo "Salida del comando:\n";
                foreach ($output as $line) {
                    echo "  $line\n";
                }
            }
            return false;
        }
        
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
        return false;
    }
}

/**
 * Función para listar backups disponibles
 */
function listarBackups() {
    global $backupDir;
    
    if (!is_dir($backupDir)) {
        echo "No hay directorio de backups.\n";
        return [];
    }
    
    $backups = glob($backupDir . '*.sql.gz');
    
    if (empty($backups)) {
        echo "No se encontraron backups.\n";
        return [];
    }
    
    echo "Backups disponibles:\n";
    echo str_repeat('-', 80) . "\n";
    echo sprintf("%-5s %-25s %-20s %-15s\n", "ID", "Archivo", "Fecha", "Tamaño");
    echo str_repeat('-', 80) . "\n";
    
    foreach ($backups as $index => $backup) {
        $filename = basename($backup);
        $size = formatBytes(filesize($backup));
        $date = date('Y-m-d H:i:s', filemtime($backup));
        
        echo sprintf("%-5d %-25s %-20s %-15s\n", $index + 1, $filename, $date, $size);
    }
    
    echo str_repeat('-', 80) . "\n";
    return $backups;
}

/**
 * Función para limpiar backups antiguos
 */
function limpiarBackupsAntiguos() {
    global $backupDir, $maxBackups;
    
    $backups = glob($backupDir . '*.sql.gz');
    
    if (count($backups) > $maxBackups) {
        // Ordenar por fecha de modificación (más reciente primero)
        usort($backups, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        // Eliminar backups antiguos
        $backupsToDelete = array_slice($backups, $maxBackups);
        
        foreach ($backupsToDelete as $backup) {
            unlink($backup);
            echo "🗑️  Backup eliminado: " . basename($backup) . "\n";
        }
        
        echo "✅ Limpieza de backups completada\n";
    }
}

/**
 * Función para formatear bytes en formato legible
 */
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * Función para mostrar ayuda
 */
function mostrarAyuda() {
    echo "Sistema CDA - Script de Backup y Restauración\n";
    echo str_repeat('=', 50) . "\n\n";
    echo "Uso: php backup_restore.php [COMANDO] [OPCIONES]\n\n";
    echo "Comandos disponibles:\n";
    echo "  backup                    Hacer backup de la base de datos\n";
    echo "  restore <archivo>         Restaurar desde un archivo de backup\n";
    echo "  list                      Listar backups disponibles\n";
    echo "  clean                     Limpiar backups antiguos\n";
    echo "  help                      Mostrar esta ayuda\n\n";
    echo "Ejemplos:\n";
    echo "  php backup_restore.php backup\n";
    echo "  php backup_restore.php restore backups/backup_cda_base_2025-08-26_10-30-00.sql.gz\n";
    echo "  php backup_restore.php list\n";
    echo "  php backup_restore.php clean\n\n";
}

// Procesar argumentos de línea de comandos
if ($argc < 2) {
    mostrarAyuda();
    exit(1);
}

$comando = strtolower($argv[1]);

switch ($comando) {
    case 'backup':
        echo "🔄 Iniciando proceso de backup...\n";
        $resultado = hacerBackup();
        if ($resultado) {
            echo "✅ Proceso de backup completado exitosamente\n";
            exit(0);
        } else {
            echo "❌ Proceso de backup falló\n";
            exit(1);
        }
        break;
        
    case 'restore':
        if ($argc < 3) {
            echo "❌ Error: Debes especificar el archivo de backup\n";
            echo "Uso: php backup_restore.php restore <archivo>\n";
            exit(1);
        }
        
        $archivo = $argv[2];
        echo "🔄 Iniciando proceso de restauración...\n";
        echo "⚠️  ADVERTENCIA: Esto sobrescribirá la base de datos actual\n";
        echo "¿Estás seguro? (y/N): ";
        
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
        
        if (trim(strtolower($line)) === 'y') {
            $resultado = restaurarBackup($archivo);
            if ($resultado) {
                echo "✅ Proceso de restauración completado exitosamente\n";
                exit(0);
            } else {
                echo "❌ Proceso de restauración falló\n";
                exit(1);
            }
        } else {
            echo "❌ Restauración cancelada por el usuario\n";
            exit(0);
        }
        break;
        
    case 'list':
        listarBackups();
        break;
        
    case 'clean':
        echo "🧹 Limpiando backups antiguos...\n";
        limpiarBackupsAntiguos();
        break;
        
    case 'help':
        mostrarAyuda();
        break;
        
    default:
        echo "❌ Comando desconocido: $comando\n";
        echo "Usa 'php backup_restore.php help' para ver la ayuda\n";
        exit(1);
}

echo "\n";
?>
