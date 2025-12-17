<?php
// Verificar que no se hayan enviado headers antes
if (headers_sent()) {
	error_log("Headers already sent in auth.php");
}

// Incluir archivos de configuración
require_once 'includes/auth_functions.php';

// Si el usuario ya está autenticado, redirigir al dashboard
verificarNoAutenticado();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	// Verificar que los datos POST estén presentes
	if (!isset($_POST['username']) || !isset($_POST['password'])) {
		$_SESSION['error'] = 'Usuario y contraseña son requeridos';
		$baseUrl = getBaseUrl();
		header('Location: ' . $baseUrl . '/index.php');
		exit();
	}
	
	$username = limpiarDatos($_POST['username']);
	$password = $_POST['password'];
	
	// Validar que los datos no estén vacíos
	if (empty($username) || empty($password)) {
		$_SESSION['error'] = 'Usuario y contraseña no pueden estar vacíos';
		$baseUrl = getBaseUrl();
		header('Location: ' . $baseUrl . '/index.php');
		exit();
	}
	
	try {
		$pdo = conectarDB();
		
		$stmt = $pdo->prepare("SELECT USUARIO_ID, USERNAME, PASSWORD, NOMBRE_COMPLETO FROM usuarios WHERE USERNAME = ?");
		$stmt->execute([$username]);
		$usuario = $stmt->fetch();
		
		if ($usuario && password_verify($password, $usuario['PASSWORD'])) {
			// Iniciar sesión
			$_SESSION['usuario_id'] = $usuario['USUARIO_ID'];
			$_SESSION['username'] = $usuario['USERNAME'];
			$_SESSION['nombre_completo'] = $usuario['NOMBRE_COMPLETO'];
			$_SESSION['ultimo_acceso'] = time();
			
			// Actualizar último acceso en la base de datos (si la columna existe)
			try {
				// Verificar si la columna existe antes de actualizar
				$stmt = $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'FECHA_ULTIMOACCESO'");
				if ($stmt->rowCount() > 0) {
					$stmt = $pdo->prepare("UPDATE usuarios SET FECHA_ULTIMOACCESO = NOW() WHERE USUARIO_ID = ?");
					$stmt->execute([$usuario['USUARIO_ID']]);
				}
			} catch (PDOException $e) {
				// Silenciar el error si la columna no existe
				error_log("Error al actualizar último acceso: " . $e->getMessage());
			}
			
			// Log de autenticación exitosa
			error_log("Usuario autenticado exitosamente: $username");
			
			// Redirigir al dashboard
			$baseUrl = getBaseUrl();
			header('Location: ' . $baseUrl . '/dashboard.php');
			exit();
		} else {
			// Log de intento fallido
			error_log("Intento de autenticación fallido para usuario: $username");
			
			$_SESSION['error'] = 'Usuario o contraseña incorrectos';
			$baseUrl = getBaseUrl();
			header('Location: ' . $baseUrl . '/index.php');
			exit();
		}
	} catch (PDOException $e) {
		// Log del error para debugging
		error_log("Error de conexión a BD en auth.php: " . $e->getMessage());
		
		// Mensaje más específico para el usuario
		if (strpos($e->getMessage(), 'Access denied') !== false) {
			$_SESSION['error'] = 'Error de acceso a la base de datos. Verifique las credenciales.';
		} elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
			$_SESSION['error'] = 'Base de datos no encontrada. Verifique la configuración.';
		} elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
			$_SESSION['error'] = 'No se puede conectar al servidor de base de datos.';
		} else {
			$_SESSION['error'] = 'Error de conexión a la base de datos.';
		}
		
		$baseUrl = getBaseUrl();
		header('Location: ' . $baseUrl . '/index.php');
		exit();
	}
}
?>
