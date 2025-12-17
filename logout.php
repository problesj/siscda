<?php
/**
 * Script de cierre de sesión
 * Cierra la sesión del usuario y redirige al login
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/auth_functions.php';

// Cerrar la sesión usando la función mejorada
cerrarSesion();

// Obtener la URL base
$baseUrl = getBaseUrl();
$redirectUrl = $baseUrl . '/index.php?success=sesion_cerrada';

// Redirigir al login con mensaje de sesión cerrada
// Usar JavaScript como respaldo si los headers no funcionan
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="0;url=<?php echo htmlspecialchars($redirectUrl); ?>">
    <script>
        // Redirección inmediata con JavaScript como respaldo
        window.location.href = '<?php echo addslashes($redirectUrl); ?>';
    </script>
    <title>Cerrando sesión...</title>
</head>
<body>
    <p>Cerrando sesión... Si no eres redirigido automáticamente, <a href="<?php echo htmlspecialchars($redirectUrl); ?>">haz clic aquí</a>.</p>
</body>
</html>
<?php
// También intentar redirección con header (por si acaso)
header('Location: ' . $redirectUrl);
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
exit();
?>
