<?php
// Obtener módulos del usuario actual
$modulosUsuario = [];
if (isset($_SESSION['usuario_id'])) {
    $modulosUsuario = obtenerModulosUsuario($_SESSION['usuario_id']);
}

// Mapeo de módulos a URLs e iconos
$modulosDisponibles = [
    'Usuarios' => ['url' => '/siscda/modules/usuarios.php', 'icono' => 'fa-users'],
    'Personas' => ['url' => '/siscda/modules/personas.php', 'icono' => 'fa-user-friends'],
    'Cultos' => ['url' => '/siscda/modules/cultos.php', 'icono' => 'fa-church'],
    'Asistencias' => ['url' => '/siscda/modules/asistencias.php', 'icono' => 'fa-clipboard-check'],
    'Reportes' => ['url' => '/siscda/modules/reportes.php', 'icono' => 'fa-chart-bar'],
    'Ofrendas' => ['url' => '/siscda/modules/ofrendas.php', 'icono' => 'fa-hand-holding-usd'],
    'Diezmos' => ['url' => '/siscda/modules/diezmos.php', 'icono' => 'fa-coins']
];
?>

<!-- Header del sidebar con logo y botón de cerrar -->
<div class="sidebar-header py-3 mb-3 border-bottom">
    <div class="sidebar-logo text-center">
        <i class="fas fa-church fa-2x text-primary"></i>
        <h6 class="sidebar-title text-muted mb-0">Sistema CDA</h6>
    </div>
</div>

<div class="position-sticky pt-3">
    <ul class="nav flex-column">
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <li class="nav-item">
                <a class="nav-link" href="/siscda/dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            
            <?php 
            // Ordenar módulos según el orden deseado
            $ordenModulos = ['Usuarios', 'Personas', 'Cultos', 'Asistencias', 'Reportes', 'Ofrendas', 'Diezmos'];
            $modulosOrdenados = [];
            
            // Primero agregar módulos en el orden especificado
            foreach ($ordenModulos as $nombreOrdenado) {
                foreach ($modulosUsuario as $modulo) {
                    if ($modulo['nombre_modulo'] === $nombreOrdenado && isset($modulosDisponibles[$nombreOrdenado])) {
                        $modulosOrdenados[] = $modulo;
                        break;
                    }
                }
            }
            
            // Luego agregar cualquier otro módulo que no esté en la lista
            foreach ($modulosUsuario as $modulo) {
                if (!in_array($modulo['nombre_modulo'], $ordenModulos) && isset($modulosDisponibles[$modulo['nombre_modulo']])) {
                    $modulosOrdenados[] = $modulo;
                }
            }
            
            // Mostrar módulos a los que el usuario tiene acceso
            foreach ($modulosOrdenados as $modulo): 
                $nombreModulo = $modulo['nombre_modulo'];
                if (isset($modulosDisponibles[$nombreModulo])):
                    $moduloInfo = $modulosDisponibles[$nombreModulo];
                    $badge = $modulo['privilegio'] === 'Administrador' 
                        ? '<span class="badge bg-danger ms-1" style="font-size: 0.6rem;">Admin</span>' 
                        : '';
            ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $moduloInfo['url']; ?>">
                        <i class="fas <?php echo $moduloInfo['icono']; ?>"></i> <?php echo $nombreModulo; ?><?php echo $badge; ?>
                    </a>
                </li>
            <?php 
                endif;
            endforeach; 
            
            // Mostrar módulo de administración después de todos los módulos
            // Mostrar módulo de administración solo para el usuario admin
            if (isset($_SESSION['username']) && strtolower($_SESSION['username']) === 'admin'): 
            ?>
                <li class="nav-item">
                    <a class="nav-link" href="/siscda/modules/modulos_privilegios.php">
                        <i class="fas fa-cogs"></i> Módulos y Privilegios
                    </a>
                </li>
            <?php 
            endif;
            ?>
        <?php endif; ?>
        
        <!-- Separador -->
        <li class="nav-item">
            <hr class="dropdown-divider">
        </li>
        
        <!-- Opciones de Usuario -->
        <li class="nav-item">
            <a class="nav-link" href="/siscda/perfil.php">
                <i class="fas fa-user-cog"></i> Mi Perfil
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?php echo getBaseUrl(); ?>/logout.php">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </li>
    </ul>
</div>
