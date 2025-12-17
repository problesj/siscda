<!-- Menú móvil moderno -->
<div class="mobile-menu-container" id="mobileMenu">
            <!-- Header del menú móvil -->
        <div class="mobile-menu-header">
            <h1 class="mobile-menu-title">Menú Principal</h1>
            <button class="mobile-menu-close" onclick="closeMobileMenu()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    
    <!-- Contenido del menú -->
    <div class="mobile-menu-content">
        <!-- Grid de categorías -->
        <div class="mobile-menu-grid">
            <!-- Dashboard -->
            <a href="<?php echo getBaseUrl(); ?>/dashboard.php" class="mobile-menu-category dashboard" onclick="selectMenuItem()">
                <i class="fas fa-tachometer-alt mobile-menu-category-icon"></i>
                <p class="mobile-menu-category-text">Dashboard</p>
            </a>
            
            <?php
            // Obtener módulos del usuario actual
            $modulosUsuario = [];
            if (isset($_SESSION['usuario_id'])) {
                require_once dirname(__DIR__) . '/includes/auth_functions.php';
                $modulosUsuario = obtenerModulosUsuario($_SESSION['usuario_id']);
            }
            
            // Mapeo de módulos a URLs e iconos
            $modulosDisponibles = [
                'Usuarios' => ['url' => '/modules/usuarios.php', 'icono' => 'fa-users', 'clase' => 'usuarios'],
                'Personas' => ['url' => '/modules/personas.php', 'icono' => 'fa-user-friends', 'clase' => 'personas'],
                'Cultos' => ['url' => '/modules/cultos.php', 'icono' => 'fa-church', 'clase' => 'cultos'],
                'Asistencias' => ['url' => '/modules/asistencias.php', 'icono' => 'fa-clipboard-check', 'clase' => 'asistencias'],
                'Reportes' => ['url' => '/modules/reportes.php', 'icono' => 'fa-chart-bar', 'clase' => 'reportes'],
                'Ofrendas' => ['url' => '/modules/ofrendas.php', 'icono' => 'fa-hand-holding-usd', 'clase' => 'ofrendas'],
                'Diezmos' => ['url' => '/modules/diezmos.php', 'icono' => 'fa-coins', 'clase' => 'diezmos']
            ];
            
            // Mostrar módulos a los que el usuario tiene acceso
            foreach ($modulosUsuario as $modulo): 
                $nombreModulo = $modulo['nombre_modulo'];
                if (isset($modulosDisponibles[$nombreModulo])):
                    $moduloInfo = $modulosDisponibles[$nombreModulo];
            ?>
                <a href="<?php echo getBaseUrl() . $moduloInfo['url']; ?>" class="mobile-menu-category <?php echo $moduloInfo['clase']; ?>" onclick="selectMenuItem()">
                    <i class="fas <?php echo $moduloInfo['icono']; ?> mobile-menu-category-icon"></i>
                    <p class="mobile-menu-category-text"><?php echo $nombreModulo; ?></p>
                </a>
            <?php 
                endif;
            endforeach; 
            ?>
            
            <!-- Mi Perfil -->
            <a href="<?php echo getBaseUrl(); ?>/perfil.php" class="mobile-menu-category perfil" onclick="selectMenuItem()">
                <i class="fas fa-user-cog mobile-menu-category-icon"></i>
                <p class="mobile-menu-category-text">Mi Perfil</p>
            </a>
            
            <!-- Cerrar Sesión -->
            <a href="<?php echo getBaseUrl(); ?>/logout.php" class="mobile-menu-category logout" onclick="selectMenuItem()">
                <i class="fas fa-sign-out-alt mobile-menu-category-icon"></i>
                <p class="mobile-menu-category-text">Cerrar Sesión</p>
            </a>
        </div>
        
        <!-- Footer del menú -->
        <div class="mobile-menu-footer">
            <p class="mobile-menu-footer-text">
                Sistema de Control de Asistencias<br>
                <small>Versión <?php echo APP_VERSION ?? '1.0.0'; ?></small>
            </p>
        </div>
    </div>
</div>

<!-- Botón flotante para abrir el menú móvil -->
<button class="mobile-menu-fab" id="mobileMenuFab" onclick="openMobileMenu()" title="Abrir menú">
    <i class="fas fa-bars"></i>
</button>

<!-- Overlay para cerrar el menú -->
<div class="mobile-menu-overlay" id="mobileMenuOverlay" onclick="closeMobileMenu()"></div>

<style>
.mobile-menu-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1040;
}

@media (max-width: 768px) {
    .mobile-menu-overlay {
        display: block;
    }
}

/* Botón flotante (FAB) */
.mobile-menu-fab {
    display: none;
    position: fixed;
    top: 80px;
    right: 20px;
    width: 56px;
    height: 56px;
    background: #28a745;
    color: white;
    border: none;
    border-radius: 50%;
    font-size: 1.5rem;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    z-index: 1050;
    transition: all 0.3s ease;
}

.mobile-menu-fab:hover {
    background: #218838;
    transform: scale(1.1);
    box-shadow: 0 6px 16px rgba(0,0,0,0.4);
}

.mobile-menu-fab:active {
    transform: scale(0.95);
}

/* Mostrar FAB solo en móvil */
@media (max-width: 768px) {
    .mobile-menu-fab {
        display: block;
    }
    
    .mobile-menu-fab.hidden {
        display: none;
    }
}
</style>
