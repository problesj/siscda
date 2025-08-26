/**
 * Utilidades de SweetAlert2 para el Sistema CDA
 * Proporciona funciones predefinidas para alertas comunes del sistema
 */

// Configuración global de SweetAlert2
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});

// Función para mostrar alertas de éxito
function showSuccess(message, title = '¡Éxito!') {
    return Swal.fire({
        icon: 'success',
        title: title,
        text: message,
        confirmButtonText: 'Aceptar',
        confirmButtonColor: '#28a745'
    });
}

// Función para mostrar alertas de error
function showError(message, title = 'Error') {
    return Swal.fire({
        icon: 'error',
        title: title,
        text: message,
        confirmButtonText: 'Aceptar',
        confirmButtonColor: '#dc3545'
    });
}

// Función para mostrar alertas de advertencia
function showWarning(message, title = 'Advertencia') {
    return Swal.fire({
        icon: 'warning',
        title: title,
        text: message,
        confirmButtonText: 'Aceptar',
        confirmButtonColor: '#ffc107'
    });
}

// Función para mostrar alertas de información
function showInfo(message, title = 'Información') {
    return Swal.fire({
        icon: 'info',
        title: title,
        text: message,
        confirmButtonText: 'Aceptar',
        confirmButtonColor: '#17a2b8'
    });
}

// Función para mostrar alertas de confirmación
function showConfirm(message, title = 'Confirmar', confirmText = 'Sí', cancelText = 'No') {
    return Swal.fire({
        icon: 'question',
        title: title,
        text: message,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        reverseButtons: true
    });
}

// Función para mostrar alertas de confirmación de eliminación
function showDeleteConfirm(itemName = 'este elemento') {
    return Swal.fire({
        icon: 'warning',
        title: '¿Está seguro?',
        text: `¿Realmente desea eliminar ${itemName}? Esta acción no se puede deshacer.`,
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        reverseButtons: true
    });
}

// Función para mostrar toast de éxito
function showSuccessToast(message) {
    Toast.fire({
        icon: 'success',
        title: message
    });
}

// Función para mostrar toast de error
function showErrorToast(message) {
    Toast.fire({
        icon: 'error',
        title: message
    });
}

// Función para mostrar toast de advertencia
function showWarningToast(message) {
    Toast.fire({
        icon: 'warning',
        title: message
    });
}

// Función para mostrar toast de información
function showInfoToast(message) {
    Toast.fire({
        icon: 'info',
        title: message
    });
}

// Función para mostrar loading
function showLoading(message = 'Procesando...') {
    return Swal.fire({
        title: message,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

// Función para cerrar loading
function closeLoading() {
    Swal.close();
}

// Función para mostrar formulario personalizado
function showCustomForm(title, html, confirmText = 'Guardar', cancelText = 'Cancelar') {
    return Swal.fire({
        title: title,
        html: html,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        reverseButtons: true,
        focusConfirm: false,
        preConfirm: () => {
            // Aquí puedes agregar validación del formulario si es necesario
            return true;
        }
    });
}

// Función para mostrar alerta de éxito con redirección
function showSuccessAndRedirect(message, redirectUrl, title = '¡Éxito!') {
    Swal.fire({
        icon: 'success',
        title: title,
        text: message,
        confirmButtonText: 'Aceptar',
        confirmButtonColor: '#28a745'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = redirectUrl;
        }
    });
}

// Función para mostrar alerta de error con redirección
function showErrorAndRedirect(message, redirectUrl, title = 'Error') {
    Swal.fire({
        icon: 'error',
        title: title,
        text: message,
        confirmButtonText: 'Aceptar',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = redirectUrl;
        }
    });
}

// Función para validar formularios antes de enviar
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form.checkValidity()) {
        form.reportValidity();
        return false;
    }
    return true;
}

// Función para mostrar errores de validación
function showValidationError(fieldName, message) {
    showError(`Error en el campo "${fieldName}": ${message}`);
}

// Función para manejar errores de AJAX
function handleAjaxError(error, defaultMessage = 'Error en la operación') {
    console.error('Error AJAX:', error);
    let message = defaultMessage;
    
    if (error.responseJSON && error.responseJSON.error) {
        message = error.responseJSON.error;
    } else if (error.statusText) {
        message = `${defaultMessage}: ${error.statusText}`;
    }
    
    showError(message);
}

// Función para mostrar confirmación antes de enviar formulario
function confirmBeforeSubmit(formId, message = '¿Está seguro de que desea continuar?') {
    const form = document.getElementById(formId);
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        showConfirm(message).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
}

// Función para mostrar confirmación antes de eliminar
function confirmBeforeDelete(deleteUrl, itemName = 'este elemento') {
    showDeleteConfirm(itemName).then((result) => {
        if (result.isConfirmed) {
            window.location.href = deleteUrl;
        }
    });
}

// Función para mostrar alerta de sesión expirada
function showSessionExpired() {
    Swal.fire({
        icon: 'warning',
        title: 'Sesión Expirada',
        text: 'Su sesión ha expirado. Será redirigido al login.',
        confirmButtonText: 'Aceptar',
        confirmButtonColor: '#ffc107',
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then(() => {
        window.location.href = '../index.php';
    });
}

// Función para mostrar alerta de permisos insuficientes
function showInsufficientPermissions() {
    Swal.fire({
        icon: 'error',
        title: 'Permisos Insuficientes',
        text: 'No tiene permisos para realizar esta acción.',
        confirmButtonText: 'Aceptar',
        confirmButtonColor: '#dc3545'
    });
}

// Exportar funciones para uso global
window.SwalUtils = {
    showSuccess,
    showError,
    showWarning,
    showInfo,
    showConfirm,
    showDeleteConfirm,
    showSuccessToast,
    showErrorToast,
    showWarningToast,
    showInfoToast,
    showLoading,
    closeLoading,
    showCustomForm,
    showSuccessAndRedirect,
    showErrorAndRedirect,
    validateForm,
    showValidationError,
    handleAjaxError,
    confirmBeforeSubmit,
    confirmBeforeDelete,
    showSessionExpired,
    showInsufficientPermissions
};
