/**
 * Gestor de Sesiones del Cliente
 * Maneja la expiración de sesión y redirección automática
 */

class SessionManager {
    constructor() {
        this.sessionTimeout = 7200000; // 2 horas en milisegundos
        this.warningTime = 300000; // 5 minutos antes de expirar
        this.lastActivity = Date.now();
        this.warningShown = false;
        
        this.init();
    }
    
    init() {
        // Eventos para detectar actividad del usuario
        this.bindEvents();
        
        // Iniciar temporizador de sesión
        this.startSessionTimer();
        
        // Verificar actividad cada minuto
        setInterval(() => this.checkActivity(), 60000);
    }
    
    bindEvents() {
        // Eventos que indican actividad del usuario
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
        
        events.forEach(event => {
            document.addEventListener(event, () => this.updateActivity(), true);
        });
        
        // Evento de visibilidad de página
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.updateActivity();
            }
        });
    }
    
    updateActivity() {
        this.lastActivity = Date.now();
        this.warningShown = false;
        
        // Enviar heartbeat al servidor si es necesario
        this.sendHeartbeat();
    }
    
    startSessionTimer() {
        setInterval(() => {
            const timeSinceActivity = Date.now() - this.lastActivity;
            const timeUntilExpiry = this.sessionTimeout - timeSinceActivity;
            
            // Mostrar advertencia 5 minutos antes de expirar
            if (timeUntilExpiry <= this.warningTime && timeUntilExpiry > 0 && !this.warningShown) {
                this.showSessionWarning(timeUntilExpiry);
                this.warningShown = true;
            }
            
            // Redirigir si la sesión ha expirado
            if (timeUntilExpiry <= 0) {
                this.expireSession();
            }
        }, 10000); // Verificar cada 10 segundos
    }
    
    showSessionWarning(timeUntilExpiry) {
        const minutes = Math.ceil(timeUntilExpiry / 60000);
        
        Swal.fire({
            title: 'Sesión por expirar',
            html: `Su sesión expirará en <strong>${minutes} minuto${minutes > 1 ? 's' : ''}</strong>.<br><br>
                   ¿Desea extender su sesión?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Extender Sesión',
            cancelButtonText: 'Cerrar Sesión',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                this.extendSession();
            } else {
                this.logout();
            }
        });
    }
    
    extendSession() {
        // Enviar petición para extender la sesión
        fetch('extend_session.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'extend'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.lastActivity = Date.now();
                this.warningShown = false;
                
                Swal.fire({
                    title: 'Sesión extendida',
                    text: 'Su sesión ha sido extendida exitosamente.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                this.expireSession();
            }
        })
        .catch(error => {
            console.error('Error al extender sesión:', error);
            this.expireSession();
        });
    }
    
    expireSession() {
        Swal.fire({
            title: 'Sesión expirada',
            text: 'Su sesión ha expirado. Será redirigido al inicio de sesión.',
            icon: 'info',
            timer: 3000,
            showConfirmButton: false
        }).then(() => {
            this.logout();
        });
    }
    
    logout() {
        // Limpiar cualquier dato local si es necesario
        localStorage.removeItem('session_data');
        sessionStorage.clear();
        
        // Redirigir al logout
        window.location.href = 'logout.php';
    }
    
    sendHeartbeat() {
        // Enviar heartbeat cada 5 minutos para mantener la sesión activa
        const timeSinceLastHeartbeat = Date.now() - this.lastActivity;
        
        if (timeSinceLastHeartbeat >= 300000) { // 5 minutos
            fetch('heartbeat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    this.expireSession();
                }
            })
            .catch(error => {
                console.error('Error en heartbeat:', error);
            });
        }
    }
    
    checkActivity() {
        // Verificar si la página ha estado inactiva por mucho tiempo
        const timeSinceActivity = Date.now() - this.lastActivity;
        
        if (timeSinceActivity > this.sessionTimeout) {
            this.expireSession();
        }
    }
}

// Inicializar el gestor de sesiones cuando se carga la página
document.addEventListener('DOMContentLoaded', () => {
    // Solo inicializar si estamos en una página autenticada
    if (document.querySelector('.navbar') && !document.querySelector('.login-container')) {
        window.sessionManager = new SessionManager();
    }
});

// Función global para cerrar sesión manualmente
function cerrarSesion() {
    if (window.sessionManager) {
        window.sessionManager.logout();
    } else {
        window.location.href = 'logout.php';
    }
}
