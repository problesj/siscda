/**
 * Controlador del menú móvil simplificado y funcional
 */

class MobileMenu {
    constructor() {
        console.log('Constructor de MobileMenu ejecutado');
        
        this.menu = document.getElementById('mobileMenu');
        this.fab = document.getElementById('mobileMenuFab');
        this.overlay = document.getElementById('mobileMenuOverlay');
        this.isOpen = false;
        
        console.log('Elementos encontrados:', {
            menu: !!this.menu,
            fab: !!this.fab,
            overlay: !!this.overlay
        });
        
        // Asegurar que el menú comience cerrado
        if (this.menu) {
            this.menu.style.display = 'none';
            this.menu.style.animation = '';
        }
        if (this.overlay) {
            this.overlay.style.display = 'none';
        }
        if (this.fab) {
            this.fab.style.display = 'block';
        }
        
        // Restaurar scroll
        document.body.style.overflow = '';
        
        this.init();
    }
    
    init() {
        console.log('Inicializando MobileMenu...');
        
        // Event listeners
        if (this.fab) {
            this.fab.addEventListener('click', () => {
                console.log('FAB clickeado');
                this.open();
            });
        }
        
        // Cerrar con Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.close();
            }
        });
        
        console.log('MobileMenu inicializado correctamente');
    }
    
    open() {
        console.log('Método open() llamado, isOpen:', this.isOpen);
        
        if (this.isOpen) {
            console.log('Menú ya está abierto');
            return;
        }
        
        // Verificar si el menú está siendo cerrado por selección
        if (this.menu && this.menu.dataset.closingBySelection === 'true') {
            console.log('Menú está siendo cerrado por selección, no se puede abrir');
            return;
        }
        
        this.isOpen = true;
        
        if (this.menu) {
            this.menu.style.display = 'block';
            this.menu.style.animation = 'slideInUp 0.3s ease-out';
        }
        
        if (this.overlay) {
            this.overlay.style.display = 'block';
        }
        
        // Bloquear scroll del body
        document.body.style.overflow = 'hidden';
        
        // Ocultar botón flotante
        if (this.fab) {
            this.fab.style.display = 'none';
        }
        
        console.log('Menú móvil abierto exitosamente');
    }
    
    close() {
        console.log('Método close() llamado, isOpen:', this.isOpen);
        
        if (!this.isOpen) {
            console.log('Menú ya está cerrado');
            return;
        }
        
        this.isOpen = false;
        
        if (this.menu) {
            this.menu.style.animation = 'slideOutDown 0.3s ease-in';
            
            setTimeout(() => {
                this.menu.style.display = 'none';
                this.menu.style.animation = '';
            }, 300);
        }
        
        if (this.overlay) {
            this.overlay.style.display = 'none';
        }
        
        // Restaurar scroll del body
        document.body.style.overflow = '';
        
        // Mostrar botón flotante
        if (this.fab) {
            this.fab.style.display = 'block';
        }
        
        console.log('Menú móvil cerrado exitosamente');
    }
    
    toggle() {
        console.log('Método toggle() llamado');
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }
}

// Funciones globales para compatibilidad
function openMobileMenu() {
    if (window.mobileMenu) {
        window.mobileMenu.open();
    }
}

function closeMobileMenu() {
    if (window.mobileMenu) {
        window.mobileMenu.close();
    }
}

function toggleMobileMenu() {
    if (window.mobileMenu) {
        window.mobileMenu.toggle();
    }
}

// Función para seleccionar un elemento del menú
function selectMenuItem() {
    // Cerrar el menú móvil
    if (window.mobileMenu) {
        window.mobileMenu.close();
    }
    
    // La navegación se hará automáticamente por el href del enlace
    // pero podemos agregar un pequeño delay para que se vea la animación
    setTimeout(() => {
        // El menú ya se cerró, la página se cargará
    }, 300);
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    // Siempre inicializar el menú móvil para que esté disponible
    window.mobileMenu = new MobileMenu();
    console.log('Menú móvil inicializado');
});

// Inicializar en cualquier momento si es necesario
function initMobileMenu() {
    if (!window.mobileMenu) {
        window.mobileMenu = new MobileMenu();
        console.log('Menú móvil inicializado manualmente');
    }
}

// Exportar para uso en otros módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MobileMenu;
}
