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
            
            <!-- Usuarios -->
            <a href="<?php echo getBaseUrl(); ?>/modules/usuarios.php" class="mobile-menu-category usuarios" onclick="selectMenuItem()">
                <i class="fas fa-users mobile-menu-category-icon"></i>
                <p class="mobile-menu-category-text">Usuarios</p>
            </a>
            
            <!-- Personas -->
            <a href="<?php echo getBaseUrl(); ?>/modules/personas.php" class="mobile-menu-category personas" onclick="selectMenuItem()">
                <i class="fas fa-user-friends mobile-menu-category-icon"></i>
                <p class="mobile-menu-category-text">Personas</p>
            </a>
            
            <!-- Grupos Familiares -->
            <a href="<?php echo getBaseUrl(); ?>/modules/grupos_familiares.php" class="mobile-menu-category grupos" onclick="selectMenuItem()">
                <i class="fas fa-home mobile-menu-category-icon"></i>
                <p class="mobile-menu-category-text">Grupos</p>
            </a>
            
            <!-- Roles -->
            <a href="<?php echo getBaseUrl(); ?>/modules/roles.php" class="mobile-menu-category roles" onclick="selectMenuItem()">
                <i class="fas fa-user-tag mobile-menu-category-icon"></i>
                <p class="mobile-menu-category-text">Roles</p>
            </a>
            
            <!-- Cultos -->
            <a href="<?php echo getBaseUrl(); ?>/modules/cultos.php" class="mobile-menu-category cultos" onclick="selectMenuItem()">
                <i class="fas fa-church mobile-menu-category-icon"></i>
                <p class="mobile-menu-category-text">Cultos</p>
            </a>
            
            <!-- Asistencias -->
            <a href="<?php echo getBaseUrl(); ?>/modules/asistencias.php" class="mobile-menu-category asistencias" onclick="selectMenuItem()">
                <i class="fas fa-clipboard-check mobile-menu-category-icon"></i>
                <p class="mobile-menu-category-text">Asistencias</p>
            </a>
            
            <!-- Reportes -->
            <a href="<?php echo getBaseUrl(); ?>/modules/reportes.php" class="mobile-menu-category reportes" onclick="selectMenuItem()">
                <i class="fas fa-chart-bar mobile-menu-category-icon"></i>
                <p class="mobile-menu-category-text">Reportes</p>
            </a>
            
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
