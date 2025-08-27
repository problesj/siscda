<?php
/**
 * Script de configuración de la base de datos
 * Este archivo te permite configurar fácilmente las credenciales
 * de la base de datos en el servidor remoto
 */

// Verificar si ya existe config.php
if (file_exists('config.php')) {
    echo "<h2>Configuración Actual</h2>";
    echo "<p>El archivo <code>config.php</code> ya existe.</p>";
    
    // Leer configuración actual
    $currentConfig = file_get_contents('config.php');
    echo "<h3>Contenido Actual:</h3>";
    echo "<pre>" . htmlspecialchars($currentConfig) . "</pre>";
    
    echo "<hr>";
    echo "<h3>Opciones:</h3>";
    echo "<p><strong>1.</strong> <a href='?action=backup'>Hacer backup del config.php actual</a></p>";
    echo "<p><strong>2.</strong> <a href='?action=update'>Actualizar configuración</a></p>";
    echo "<p><strong>3.</strong> <a href='?action=test'>Probar conexión actual</a></p>";
    
} else {
    echo "<h2>Configuración Inicial</h2>";
    echo "<p>El archivo <code>config.php</code> no existe. Se creará uno nuevo.</p>";
    echo "<p><a href='?action=create'>Crear configuración inicial</a></p>";
}

// Procesar acciones
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    switch ($action) {
        case 'backup':
            if (file_exists('config.php')) {
                $backupName = 'config_backup_' . date('Y-m-d_H-i-s') . '.php';
                if (copy('config.php', $backupName)) {
                    echo "<div class='alert alert-success'>✅ Backup creado: <code>$backupName</code></div>";
                } else {
                    echo "<div class='alert alert-danger'>❌ Error al crear backup</div>";
                }
            }
            break;
            
        case 'update':
            showConfigForm();
            break;
            
        case 'create':
            showConfigForm();
            break;
            
        case 'test':
            testCurrentConnection();
            break;
            
        case 'save':
            saveConfig();
            break;
    }
}

function showConfigForm() {
    $currentConfig = [];
    
    if (file_exists('config.php')) {
        // Leer configuración actual
        include 'config.php';
        $currentConfig = [
            'DB_HOST' => defined('DB_HOST') ? DB_HOST : '',
            'DB_NAME' => defined('DB_NAME') ? DB_NAME : '',
            'DB_USER' => defined('DB_USER') ? DB_USER : '',
            'DB_PASS' => defined('DB_PASS') ? DB_PASS : ''
        ];
    }
    
    echo "<h2>Configurar Base de Datos</h2>";
    echo "<form method='POST' action='?action=save'>";
    echo "<div class='mb-3'>";
    echo "<label for='DB_HOST' class='form-label'>Host de la Base de Datos:</label>";
    echo "<input type='text' class='form-control' name='DB_HOST' value='" . htmlspecialchars($currentConfig['DB_HOST'] ?? 'localhost') . "' required>";
    echo "<small class='form-text text-muted'>Ej: localhost, 127.0.0.1, o IP del servidor</small>";
    echo "</div>";
    
    echo "<div class='mb-3'>";
    echo "<label for='DB_NAME' class='form-label'>Nombre de la Base de Datos:</label>";
    echo "<input type='text' class='form-control' name='DB_NAME' value='" . htmlspecialchars($currentConfig['DB_NAME'] ?? '') . "' required>";
    echo "<small class='form-text text-muted'>Ej: siscda_db, cda_base</small>";
    echo "</div>";
    
    echo "<div class='mb-3'>";
    echo "<label for='DB_USER' class='form-label'>Usuario de la Base de Datos:</label>";
    echo "<input type='text' class='form-control' name='DB_USER' value='" . htmlspecialchars($currentConfig['DB_USER'] ?? '') . "' required>";
    echo "<small class='form-text text-muted'>Ej: siscda_user, admincda</small>";
    echo "</div>";
    
    echo "<div class='mb-3'>";
    echo "<label for='DB_PASS' class='form-label'>Contraseña de la Base de Datos:</label>";
    echo "<input type='password' class='form-control' name='DB_PASS' value='" . htmlspecialchars($currentConfig['DB_PASS'] ?? '') . "' required>";
    echo "<small class='form-text text-muted'>La contraseña del usuario de la BD</small>";
    echo "</div>";
    
    echo "<div class='mb-3'>";
    echo "<button type='submit' class='btn btn-primary'>Guardar Configuración</button>";
    echo " <a href='setup_config.php' class='btn btn-secondary'>Cancelar</a>";
    echo "</div>";
    echo "</form>";
    
    echo "<hr>";
    echo "<h3>Configuraciones Predefinidas</h3>";
    echo "<p><strong>Servidor Local:</strong> <a href='?action=preset&preset=local'>Usar configuración local</a></p>";
    echo "<p><strong>Servidor Remoto:</strong> <a href='?action=preset&preset=remote'>Usar configuración remota</a></p>";
}

function saveConfig() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $dbHost = $_POST['DB_HOST'] ?? '';
        $dbName = $_POST['DB_NAME'] ?? '';
        $dbUser = $_POST['DB_USER'] ?? '';
        $dbPass = $_POST['DB_PASS'] ?? '';
        
        if (empty($dbHost) || empty($dbName) || empty($dbUser)) {
            echo "<div class='alert alert-danger'>❌ Todos los campos son obligatorios</div>";
            return;
        }
        
        // Crear contenido del archivo config.php
        $configContent = "<?php
// Configuración de la base de datos
define('DB_HOST', '" . addslashes($dbHost) . "');
define('DB_NAME', '" . addslashes($dbName) . "');
define('DB_USER', '" . addslashes($dbUser) . "');
define('DB_PASS', '" . addslashes($dbPass) . "');

// Configuración de la aplicación
define('APP_NAME', 'Sistema de Control de Asistencias');
define('APP_VERSION', '1.0.0');

// Configuración de sesión se maneja en session_config.php
// Las funciones se manejan en includes/auth_functions.php
?>
";
        
        // Guardar archivo
        if (file_put_contents('config.php', $configContent)) {
            echo "<div class='alert alert-success'>✅ Archivo <code>config.php</code> guardado exitosamente</div>";
            echo "<p><a href='?action=test'>Probar conexión</a></p>";
        } else {
            echo "<div class='alert alert-danger'>❌ Error al guardar el archivo</div>";
        }
    }
}

function testCurrentConnection() {
    if (!file_exists('config.php')) {
        echo "<div class='alert alert-warning'>⚠️ No existe archivo de configuración</div>";
        return;
    }
    
    try {
        include 'config.php';
        
        if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
            echo "<div class='alert alert-danger'>❌ Configuración incompleta</div>";
            return;
        }
        
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        echo "<div class='alert alert-success'>✅ Conexión exitosa a la base de datos</div>";
        echo "<p><strong>Host:</strong> " . DB_HOST . "</p>";
        echo "<p><strong>Base de datos:</strong> " . DB_NAME . "</p>";
        echo "<p><strong>Usuario:</strong> " . DB_USER . "</p>";
        
        // Probar consulta simple
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
        $result = $stmt->fetch();
        echo "<p><strong>Total de usuarios:</strong> " . $result['total'] . "</p>";
        
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>❌ Error de conexión: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Procesar presets
if (isset($_GET['action']) && $_GET['action'] === 'preset' && isset($_GET['preset'])) {
    $preset = $_GET['preset'];
    
    if ($preset === 'local') {
        $configContent = "<?php
// Configuración de la base de datos (LOCAL)
define('DB_HOST', 'localhost');
define('DB_NAME', 'cda_base');
define('DB_USER', 'admincda');
define('DB_PASS', 'cda2025$');

// Configuración de la aplicación
define('APP_NAME', 'Sistema de Control de Asistencias');
define('APP_VERSION', '1.0.0');

// Configuración de sesión se maneja en session_config.php
// Las funciones se manejan en includes/auth_functions.php
?>
";
        file_put_contents('config.php', $configContent);
        echo "<div class='alert alert-success'>✅ Configuración local aplicada</div>";
        
    } elseif ($preset === 'remote') {
        $configContent = "<?php
// Configuración de la base de datos (REMOTO)
define('DB_HOST', 'localhost');
define('DB_NAME', 'siscda_db');
define('DB_USER', 'siscda_user');
define('DB_PASS', 'siscda_pwd_2025#');

// Configuración de la aplicación
define('APP_NAME', 'Sistema de Control de Asistencias');
define('APP_VERSION', '1.0.0');

// Configuración de sesión se maneja en session_config.php
// Las funciones se manejan en includes/auth_functions.php
?>
";
        file_put_contents('config.php', $configContent);
        echo "<div class='alert alert-success'>✅ Configuración remota aplicada</div>";
    }
    
    echo "<p><a href='setup_config.php'>Volver al inicio</a></p>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Base de Datos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>🔧 Configuración del Sistema</h1>
        <p class="lead">Configura las credenciales de la base de datos para tu servidor</p>
        
        <div class="alert alert-info">
            <strong>💡 Tip:</strong> Este script te permite configurar fácilmente la base de datos.
            Usa los presets para configuraciones rápidas o personaliza manualmente.
        </div>
        
        <?php
        // El contenido PHP ya está arriba
        ?>
        
        <hr>
        <div class="mt-4">
            <h4>📋 Instrucciones:</h4>
            <ol>
                <li><strong>Hacer backup:</strong> Si ya tienes configuración, haz backup primero</li>
                <li><strong>Elegir preset:</strong> Usa 'Servidor Remoto' para tu servidor actual</li>
                <li><strong>Personalizar:</strong> O configura manualmente los valores</li>
                <li><strong>Probar:</strong> Verifica que la conexión funcione</li>
                <li><strong>Eliminar:</strong> Borra este archivo después de configurar</li>
            </ol>
        </div>
        
        <div class="mt-4">
            <h4>⚠️ Seguridad:</h4>
            <p>Después de configurar correctamente la base de datos, <strong>elimina este archivo</strong> 
            para evitar que otros puedan ver las credenciales.</p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
