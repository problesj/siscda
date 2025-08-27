/**
 * Controlador del sidebar para ocultar/mostrar en pantallas desktop/tablet
 */

// Estado del sidebar
let sidebarVisible = true;

// Función para ocultar/mostrar el sidebar
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const toggleIcon = document.getElementById('sidebarToggleIcon');
    const body = document.body;
    
    if (!sidebar || !mainContent) {
        console.log('Elementos del sidebar no encontrados');
        return;
    }
    
    console.log('Estado actual del sidebar:', sidebarVisible ? 'visible' : 'oculto');
    console.log('Clases actuales del mainContent:', mainContent.className);
    
    if (sidebarVisible) {
        // Ocultar sidebar usando CSS
        body.classList.add('sidebar-hidden');
        
        toggleIcon.className = 'fas fa-chevron-right';
        sidebarVisible = false;
        
        // Guardar estado en localStorage
        localStorage.setItem('sidebarVisible', 'false');
        
        console.log('Sidebar ocultado usando CSS');
        console.log('Clases del body:', body.className);
    } else {
        // Mostrar sidebar removiendo CSS
        body.classList.remove('sidebar-hidden');
        
        toggleIcon.className = 'fas fa-bars';
        sidebarVisible = true;
        
        // Guardar estado en localStorage
        localStorage.setItem('sidebarVisible', 'true');
        
        console.log('Sidebar mostrado usando CSS');
        console.log('Clases del body:', body.className);
    }
}

// Función para restaurar el estado del sidebar
function restoreSidebarState() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const toggleIcon = document.getElementById('sidebarToggleIcon');
    const body = document.body;
    
    if (!sidebar || !mainContent) {
        return;
    }
    
    const savedState = localStorage.getItem('sidebarVisible');
    
    if (savedState === 'false') {
        // Restaurar estado oculto usando CSS
        body.classList.add('sidebar-hidden');
        toggleIcon.className = 'fas fa-chevron-right';
        sidebarVisible = false;
        console.log('Estado del sidebar restaurado: oculto usando CSS');
    } else {
        // Restaurar estado visible (por defecto)
        body.classList.remove('sidebar-hidden');
        toggleIcon.className = 'fas fa-bars';
        sidebarVisible = true;
        console.log('Estado del sidebar restaurado: visible usando CSS');
    }
}

// Función para manejar cambios de tamaño de ventana
function handleResize() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const toggleIcon = document.getElementById('sidebarToggleIcon');
    const body = document.body;
    
    if (!sidebar || !mainContent) {
        return;
    }
    
    // En pantallas pequeñas, siempre mostrar el sidebar
    if (window.innerWidth < 768) {
        body.classList.remove('sidebar-hidden');
        toggleIcon.className = 'fas fa-bars';
        sidebarVisible = true;
    } else {
        // En pantallas medianas y grandes, restaurar el estado guardado
        restoreSidebarState();
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    console.log('Sidebar toggle inicializado');
    
    // Restaurar estado guardado
    restoreSidebarState();
    
    // Agregar listener para cambios de tamaño de ventana
    window.addEventListener('resize', handleResize);
    
    // Verificar estado inicial
    console.log('Estado inicial del sidebar:', sidebarVisible ? 'visible' : 'oculto');
});

// Exportar funciones para uso global
window.toggleSidebar = toggleSidebar;
window.restoreSidebarState = restoreSidebarState;
