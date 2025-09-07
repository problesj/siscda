/**
 * Controlador del sidebar con un solo botón toggle en el header
 */

// Estado del sidebar
let sidebarVisible = true;

// Función para MOSTRAR/OCULTAR el sidebar (toggle desde el header)
function toggleSidebar() {
    console.log('🎯 toggleSidebar() llamada');
    
    // Verificar que el botón esté presente
    const headerButton = document.getElementById('headerSidebarToggle');
    if (headerButton) {
        console.log('✅ Botón del header encontrado en toggleSidebar');
    } else {
        console.log('❌ Botón del header NO encontrado en toggleSidebar');
        return;
    }
    
    // En dispositivos móviles, abrir el menú móvil en lugar del sidebar
    if (window.innerWidth <= 768) {
        console.log('📱 Dispositivo móvil detectado, abriendo menú móvil');
        console.log('🔍 Estado del menú móvil:', window.mobileMenu ? 'inicializado' : 'no inicializado');
        
        // Inicializar el menú móvil si no está inicializado
        if (!window.mobileMenu) {
            console.log('🔄 Inicializando menú móvil...');
            initMobileMenu();
        }
        
        // Verificar que el menú existe
        const menuElement = document.getElementById('mobileMenu');
        console.log('🔍 Elemento del menú encontrado:', !!menuElement);
        
        if (menuElement) {
            console.log('📱 Mostrando menú móvil directamente...');
            
            // Usar múltiples métodos para asegurar visibilidad
            menuElement.style.display = 'block';
            menuElement.style.visibility = 'visible';
            menuElement.style.opacity = '1';
            menuElement.style.zIndex = '9999';
            menuElement.classList.add('show');
            menuElement.style.animation = 'slideInUp 0.3s ease-out';
            
            // Mostrar overlay
            const overlay = document.getElementById('mobileMenuOverlay');
            if (overlay) {
                overlay.style.display = 'block';
                overlay.style.zIndex = '9998';
            }
            
            // Bloquear scroll
            document.body.style.overflow = 'hidden';
            
            console.log('✅ Menú móvil mostrado');
            console.log('🔍 Estilos aplicados:', {
                display: menuElement.style.display,
                visibility: menuElement.style.visibility,
                opacity: menuElement.style.opacity,
                zIndex: menuElement.style.zIndex,
                classes: menuElement.className
            });
        } else {
            console.error('❌ Elemento del menú móvil no encontrado');
        }
        return;
    }
    
    // Comportamiento normal para desktop
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (!sidebar || !mainContent) {
        console.log('⚠️ Elementos del sidebar no encontrados');
        return;
    }
    
    if (sidebarVisible) {
        // Si está visible, ocultarlo
        console.log('❌ Ocultando sidebar...');
        body.classList.add('sidebar-hidden');
        sidebarVisible = false;
        
        // Cambiar icono a barras (para indicar que se puede abrir)
        if (toggleIcon) {
            toggleIcon.className = 'fas fa-bars';
        }
        
        // Guardar estado en localStorage
        localStorage.setItem('sidebarVisible', 'false');
        console.log('❌ Sidebar ocultado');
    } else {
        // Si está oculto, mostrarlo
        console.log('✅ Mostrando sidebar...');
        body.classList.remove('sidebar-hidden');
        sidebarVisible = true;
        
        // Cambiar icono a X (para indicar que se puede cerrar)
        if (toggleIcon) {
            toggleIcon.className = 'fas fa-times';
        }
        
        // Guardar estado en localStorage
        localStorage.setItem('sidebarVisible', 'true');
        console.log('✅ Sidebar mostrado');
    }
    
    console.log('Clases del body:', body.className);
}

// Función para restaurar el estado del sidebar
function restoreSidebarState() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (!sidebar || !mainContent) {
        console.log('⚠️ Elementos del sidebar no encontrados, saltando restauración de estado');
        return;
    }
    
    const savedState = localStorage.getItem('sidebarVisible');
    
    if (savedState === 'false') {
        // Restaurar estado oculto usando CSS
        body.classList.add('sidebar-hidden');
        sidebarVisible = false;
        
        // Actualizar icono a barras
        if (toggleIcon) {
            toggleIcon.className = 'fas fa-bars';
        }
        
        console.log('🔄 Estado del sidebar restaurado: oculto');
    } else {
        // Restaurar estado visible (por defecto)
        body.classList.remove('sidebar-hidden');
        sidebarVisible = true;
        
        // Actualizar icono a X
        if (toggleIcon) {
            toggleIcon.className = 'fas fa-times';
        }
        
        console.log('🔄 Estado del sidebar restaurado: visible');
    }
}

// Función para manejar cambios de tamaño de ventana
function handleResize() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;
    
    if (!sidebar || !mainContent) {
        console.log('⚠️ Elementos del sidebar no encontrados, saltando manejo de resize');
        return;
    }
    
    // En pantallas pequeñas, siempre mostrar el sidebar
    if (window.innerWidth < 768) {
        body.classList.remove('sidebar-hidden');
        sidebarVisible = true;
        console.log('📱 Pantalla pequeña: sidebar siempre visible');
    } else {
        // En pantallas medianas y grandes, restaurar el estado guardado
        restoreSidebarState();
    }
}

// Función para verificar si el sidebar está visible
function isSidebarVisible() {
    return sidebarVisible;
}

// Función para obtener el estado actual
function getSidebarState() {
    return {
        visible: sidebarVisible,
        bodyClasses: document.body.className,
        windowWidth: window.innerWidth
    };
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    console.log('🎯 Sidebar toggle inicializado con botón único');
    
    // Verificar que el botón esté presente
    const headerButton = document.getElementById('headerSidebarToggle');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (headerButton) {
        console.log('✅ Botón del header encontrado:', headerButton);
        console.log('📍 Posición del botón:', headerButton.offsetLeft, headerButton.offsetTop);
        console.log('📏 Dimensiones del botón:', headerButton.offsetWidth, headerButton.offsetHeight);
        console.log('🎨 Clases del botón:', headerButton.className);
        console.log('👁️ Estilo display:', window.getComputedStyle(headerButton).display);
        console.log('👁️ Estilo visibility:', window.getComputedStyle(headerButton).visibility);
        console.log('👁️ Estilo opacity:', window.getComputedStyle(headerButton).opacity);
    } else {
        console.log('❌ Botón del header NO encontrado');
    }
    
    if (toggleIcon) {
        console.log('✅ Icono del toggle encontrado:', toggleIcon);
        console.log('🎨 Clases del icono:', toggleIcon.className);
    } else {
        console.log('❌ Icono del toggle NO encontrado');
    }
    
    // Esperar un poco para asegurar que todos los elementos estén cargados
    setTimeout(() => {
        // Restaurar estado guardado
        restoreSidebarState();
        
        // Agregar listener para cambios de tamaño de ventana
        window.addEventListener('resize', handleResize);
        
        // Verificar estado inicial
        console.log('Estado inicial del sidebar:', sidebarVisible ? 'visible' : 'oculto');
        console.log('🔘 Botón del header: toggle para mostrar/ocultar sidebar');
        
        // Verificar estado final del botón
        if (headerButton) {
            console.log('🔍 Estado final del botón:');
            console.log('- Display:', window.getComputedStyle(headerButton).display);
            console.log('- Visibility:', window.getComputedStyle(headerButton).visibility);
            console.log('- Opacity:', window.getComputedStyle(headerButton).opacity);
            console.log('- Z-index:', window.getComputedStyle(headerButton).zIndex);
        }
    }, 100);
});

// Función para cerrar el menú móvil
function closeMobileMenu() {
    console.log('🔒 Cerrando menú móvil...');
    
    const menuElement = document.getElementById('mobileMenu');
    const overlay = document.getElementById('mobileMenuOverlay');
    
    if (menuElement) {
        menuElement.style.animation = 'slideOutDown 0.3s ease-in';
        menuElement.classList.remove('show');
        
        setTimeout(() => {
            menuElement.style.display = 'none';
            menuElement.style.visibility = 'hidden';
            menuElement.style.opacity = '0';
            menuElement.style.animation = '';
        }, 300);
    }
    
    if (overlay) {
        overlay.style.display = 'none';
    }
    
    // Restaurar scroll
    document.body.style.overflow = '';
    
    console.log('✅ Menú móvil cerrado');
}

// Exportar funciones para uso global
window.toggleSidebar = toggleSidebar;
window.restoreSidebarState = restoreSidebarState;
window.isSidebarVisible = isSidebarVisible;
window.getSidebarState = getSidebarState;
window.closeMobileMenu = closeMobileMenu;
