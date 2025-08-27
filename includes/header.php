<?php
// Ruta relativa del proyecto
$projectPath = dirname(__DIR__);

require_once $projectPath . '/session_config.php';
session_start();
require_once $projectPath . '/config.php';

// Verificar autenticación
if (!function_exists('verificarAutenticacion')) {
    // Fallback si la función no existe
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ../index.php');
        exit();
    }
} else {
    verificarAutenticacion();
}

// Obtener la ruta base del proyecto
$baseUrl = '';
$scriptName = $_SERVER['SCRIPT_NAME'];
if (strpos($scriptName, '/siscda') !== false) {
    $baseUrl = '/siscda';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo defined('APP_NAME') ? APP_NAME : 'Sistema CDA'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <?php if (file_exists($projectPath . '/assets/css/style.css')): ?>
    <link href="<?php echo $baseUrl; ?>/assets/css/style.css" rel="stylesheet">
    <?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body>
    <!-- Scripts de JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <?php if (file_exists($projectPath . '/assets/js/sweetalert-utils.js')): ?>
    <script src="<?php echo $baseUrl; ?>/assets/js/sweetalert-utils.js"></script>
    <?php endif; ?>
    
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo $baseUrl; ?>/dashboard.php">
                <i class="fas fa-church"></i> Sistema CDA
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? 'Usuario'); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo $baseUrl; ?>/perfil.php">
                            <i class="fas fa-user-cog"></i> Mi Perfil
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo $baseUrl; ?>/logout.php">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <?php 
                $sidebarPath = $projectPath . '/includes/sidebar.php';
                if (file_exists($sidebarPath)) {
                    include $sidebarPath;
                } else {
                    echo '<div class="alert alert-warning">Sidebar no encontrado</div>';
                }
                ?>
            </div>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
