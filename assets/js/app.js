// Funcionalidades JavaScript para el Sistema CDA

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips de Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Inicializar popovers de Bootstrap
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Marcar enlace activo en la barra lateral
    const currentPath = window.location.pathname;
    const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
    
    sidebarLinks.forEach(link => {
        const linkHref = link.getAttribute('href');
        if (linkHref) {
            // Normalizar rutas eliminando barras finales
            let normalizedCurrent = currentPath.replace(/\/$/, '');
            let normalizedLink = linkHref.replace(/\/$/, '');
            
            // Extraer nombres de archivo
            const currentFile = normalizedCurrent.split('/').pop() || '';
            const linkFile = normalizedLink.split('/').pop() || '';
            
            // Comparar: ruta completa exacta, nombre de archivo, o si contiene la ruta
            let isActive = false;
            
            // 1. Comparación exacta de rutas completas (sin importar mayúsculas/minúsculas)
            if (normalizedCurrent.toLowerCase() === normalizedLink.toLowerCase()) {
                isActive = true;
            }
            // 2. Comparación por nombre de archivo (útil para módulos)
            else if (currentFile && linkFile && currentFile.toLowerCase() === linkFile.toLowerCase()) {
                isActive = true;
            }
            // 3. Si la ruta actual termina con la ruta del enlace
            else if (normalizedCurrent.toLowerCase().endsWith(normalizedLink.toLowerCase())) {
                isActive = true;
            }
            // 4. Si la ruta del enlace termina con la ruta actual
            else if (normalizedLink.toLowerCase().endsWith(normalizedCurrent.toLowerCase())) {
                isActive = true;
            }
            // 5. Comparar nombres de archivo sin extensión
            else if (currentFile.replace(/\.php$/i, '').toLowerCase() === linkFile.replace(/\.php$/i, '').toLowerCase()) {
                isActive = true;
            }
            
            if (isActive) {
                link.classList.add('active');
            }
        }
    });
    
    // Asegurar que solo un enlace esté activo a la vez
    const activeLinks = document.querySelectorAll('.sidebar .nav-link.active');
    if (activeLinks.length > 1) {
        // Si hay múltiples enlaces activos, mantener solo el primero
        for (let i = 1; i < activeLinks.length; i++) {
            activeLinks[i].classList.remove('active');
        }
    }

    // Funcionalidad para tablas responsivas
    const tables = document.querySelectorAll('.table-responsive table');
    tables.forEach(table => {
        if (table.offsetWidth > table.parentElement.offsetWidth) {
            table.parentElement.style.overflowX = 'auto';
        }
    });

    // Confirmación para acciones destructivas
    // Nota: Esta funcionalidad se ha deshabilitado porque los módulos individuales
    // manejan sus propias confirmaciones con SweetAlert2
    // Si un botón .btn-danger no tiene su propia lógica de confirmación,
    // se puede agregar aquí de forma específica

    // Auto-hide para alertas
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Validación de formularios
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Funcionalidad para búsqueda en tablas
    const searchInputs = document.querySelectorAll('.table-search');
    searchInputs.forEach(input => {
        input.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const table = this.closest('.card').querySelector('table tbody');
            const rows = table.querySelectorAll('tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });

    // Funcionalidad para ordenar tablas
    const sortableHeaders = document.querySelectorAll('.sortable');
    sortableHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const table = this.closest('table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const columnIndex = Array.from(this.parentElement.children).indexOf(this);
            
            rows.sort((a, b) => {
                const aText = a.children[columnIndex].textContent.trim();
                const bText = b.children[columnIndex].textContent.trim();
                
                if (this.classList.contains('sort-desc')) {
                    return aText.localeCompare(bText);
                } else {
                    return bText.localeCompare(aText);
                }
            });
            
            this.classList.toggle('sort-desc');
            
            rows.forEach(row => tbody.appendChild(row));
        });
    });
});

// Funciones globales
function showLoading() {
    const loadingDiv = document.createElement('div');
    loadingDiv.id = 'loading';
    loadingDiv.innerHTML = `
        <div class="position-fixed w-100 h-100 d-flex justify-content-center align-items-center" 
             style="background: rgba(0,0,0,0.5); z-index: 9999;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
    `;
    document.body.appendChild(loadingDiv);
}

function hideLoading() {
    const loadingDiv = document.getElementById('loading');
    if (loadingDiv) {
        loadingDiv.remove();
    }
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Función para exportar tabla a CSV
function exportTableToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    const rows = table.querySelectorAll('tr');
    let csv = [];
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = [];
        cols.forEach(col => {
            rowData.push('"' + col.textContent.replace(/"/g, '""') + '"');
        });
        csv.push(rowData.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    
    if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}
