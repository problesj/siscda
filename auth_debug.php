<?php
echo "=== DEBUG AUTH.PHP ===\n";

// Verificar archivos de inclusión
echo "1. Verificando archivos de inclusión...\n";
if (file_exists('session_config.php')) {
    echo "✓ session_config.php existe\n";
} else {
    echo "✗ session_config.php NO existe\n";
}

if (file_exists('config.php')) {
    echo "✓ config.php existe\n";
} else {
    echo "✗ config.php NO existe\n";
}

// Incluir archivos paso a paso
echo "\n2. Incluyendo session_config.php...\n";
if (include_once 'session_config.php') {
    echo "✓ session_config.php incluido correctamente\n";
} else {
    echo "✗ Error al incluir session_config.php\n";
}

echo "\n3. Iniciando sesión...\n";
session_start();
echo "✓ Sesión iniciada\n";

echo "\n4. Incluyendo config.php...\n";
if (include_once 'config.php') {
    echo "✓ config.php incluido correctamente\n";
} else {
    echo "✗ Error al incluir config.php\n";
}

echo "\n5. Verificando funciones...\n";
if (function_exists('limpiarDatos')) {
    echo "✓ Función limpiarDatos() está disponible\n";
} else {
    echo "✗ Función limpiarDatos() NO está disponible\n";
}

if (function_exists('conectarDB')) {
    echo "✓ Función conectarDB() está disponible\n";
} else {
    echo "✗ Función conectarDB() NO está disponible\n";
}

echo "\n6. Verificando método de solicitud...\n";
echo "Método: " . $_SERVER['REQUEST_METHOD'] . "\n";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo "✓ Es una solicitud POST\n";
    
    echo "\n7. Verificando datos POST...\n";
    if (isset($_POST['username'])) {
        echo "✓ username está presente: " . $_POST['username'] . "\n";
    } else {
        echo "✗ username NO está presente\n";
    }
    
    if (isset($_POST['password'])) {
        echo "✓ password está presente: " . str_repeat('*', strlen($_POST['password'])) . "\n";
    } else {
        echo "✗ password NO está presente\n";
    }
    
    echo "\n8. Probando función limpiarDatos...\n";
    if (function_exists('limpiarDatos')) {
        $username = limpiarDatos($_POST['username']);
        echo "✓ Username limpiado: '$username'\n";
    } else {
        echo "✗ Función limpiarDatos() no disponible\n";
        exit;
    }
    
    echo "\n9. Conectando a base de datos...\n";
    try {
        $pdo = conectarDB();
        echo "✓ Conexión a base de datos exitosa\n";
        
        echo "\n10. Consultando usuario...\n";
        $stmt = $pdo->prepare("SELECT USUARIO_ID, USERNAME, PASSWORD, NOMBRE_COMPLETO FROM usuarios WHERE USERNAME = ?");
        $stmt->execute([$username]);
        $usuario = $stmt->fetch();
        
        if ($usuario) {
            echo "✓ Usuario encontrado: " . $usuario['NOMBRE_COMPLETO'] . "\n";
            
            if (password_verify($_POST['password'], $usuario['PASSWORD'])) {
                echo "✓ Contraseña correcta\n";
                echo "✓ Autenticación exitosa\n";
            } else {
                echo "✗ Contraseña incorrecta\n";
            }
        } else {
            echo "✗ Usuario no encontrado\n";
        }
        
    } catch (PDOException $e) {
        echo "✗ Error de base de datos: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "✗ No es una solicitud POST\n";
}

echo "\n=== FIN DEBUG ===\n";
?>
