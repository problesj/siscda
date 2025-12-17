<?php 
require_once dirname(__DIR__) . '/session_config.php';
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth_functions.php';

// Verificar autenticación
verificarAutenticacion();

// Verificar que solo el usuario admin pueda acceder
if (!isset($_SESSION['username']) || strtolower($_SESSION['username']) !== 'admin') {
    $_SESSION['error'] = 'No tienes permisos para acceder a esta sección';
    $baseUrl = getBaseUrl();
    header('Location: ' . $baseUrl . '/dashboard.php');
    exit();
}

include '../includes/header.php'; 
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Gestión de Módulos y Privilegios</h1>
</div>

<!-- Pestañas de navegación -->
<ul class="nav nav-tabs" id="mainTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="modulos-tab" data-bs-toggle="tab" data-bs-target="#modulos" type="button" role="tab" aria-controls="modulos" aria-selected="true">
            <i class="fas fa-cubes"></i> Módulos
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="privilegios-tab" data-bs-toggle="tab" data-bs-target="#privilegios" type="button" role="tab" aria-controls="privilegios" aria-selected="false">
            <i class="fas fa-user-shield"></i> Privilegios de Usuarios
        </button>
    </li>
</ul>

<div class="tab-content" id="mainTabsContent">
    <!-- Pestaña de Módulos -->
    <div class="tab-pane fade show active" id="modulos" role="tabpanel" aria-labelledby="modulos-tab">
        <div class="card shadow mb-4 mt-3">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Listado de Módulos</h6>
                <button class="btn btn-primary" onclick="abrirModalModulo()">
                    <i class="fas fa-plus"></i> Nuevo Módulo
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="tablaModulos" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre del Módulo</th>
                                <th>Estado</th>
                                <th>Fecha Creación</th>
                                <th>Fecha Actualización</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyModulos">
                            <!-- Se llenará dinámicamente con JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Pestaña de Privilegios -->
    <div class="tab-pane fade" id="privilegios" role="tabpanel" aria-labelledby="privilegios-tab">
        <div class="card shadow mb-4 mt-3">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Asignación de Privilegios</h6>
                <button class="btn btn-primary" onclick="abrirModalPrivilegio()">
                    <i class="fas fa-plus"></i> Asignar Privilegio
                </button>
            </div>
            <div class="card-body">
                <!-- Campo de búsqueda -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control" id="searchInputPrivilegios" placeholder="Buscar por usuario o módulo..." oninput="filtrarPrivilegios()">
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered" id="tablaPrivilegios" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Módulo</th>
                                <th>Privilegio</th>
                                <th>Fecha Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyPrivilegios">
                            <!-- Se llenará dinámicamente con JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Crear/Editar Módulo -->
<div class="modal fade" id="modalModulo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalModuloTitle">Nuevo Módulo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formModulo">
                <div class="modal-body">
                    <input type="hidden" id="moduloId" name="id">
                    <div class="mb-3">
                        <label for="nombreModulo" class="form-label">Nombre del Módulo *</label>
                        <input type="text" class="form-control" id="nombreModulo" name="nombre_modulo" required>
                    </div>
                    <div class="mb-3">
                        <label for="estadoModulo" class="form-label">Estado</label>
                        <select class="form-select" id="estadoModulo" name="estado_modulo">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarModulo()">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Asignar Privilegio -->
<div class="modal fade" id="modalPrivilegio" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPrivilegioTitle">Asignar Privilegio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formPrivilegio">
                <div class="modal-body">
                    <input type="hidden" id="privilegioId" name="id">
                    <div class="mb-3">
                        <label for="usuarioPrivilegio" class="form-label">Usuario *</label>
                        <select class="form-select" id="usuarioPrivilegio" name="id_usuario" required>
                            <option value="">Seleccionar usuario</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="moduloPrivilegio" class="form-label">Módulo *</label>
                        <select class="form-select" id="moduloPrivilegio" name="id_modulo" required>
                            <option value="">Seleccionar módulo</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="rolPrivilegio" class="form-label">Privilegio *</label>
                        <select class="form-select" id="rolPrivilegio" name="id_rol_sistema" required>
                            <option value="">Seleccionar privilegio</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarPrivilegio()">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Variables globales
let datosModulos = [];
let datosPrivilegios = [];
let datosUsuarios = [];
let datosRoles = [];

// Función para cargar módulos
function cargarModulos() {
    fetch('modulos_privilegios_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=obtener_modulos'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            datosModulos = data.modulos;
            mostrarModulos(data.modulos);
        }
    })
    .catch(error => {
        console.error('Error al cargar módulos:', error);
    });
}

// Función para mostrar módulos
function mostrarModulos(modulos) {
    const tbody = document.getElementById('tbodyModulos');
    tbody.innerHTML = '';
    
    if (modulos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">No hay módulos registrados</td></tr>';
        return;
    }
    
    modulos.forEach(modulo => {
        const row = document.createElement('tr');
        const estadoBadge = modulo.estado_modulo == 1 
            ? '<span class="badge bg-success">Activo</span>' 
            : '<span class="badge bg-secondary">Inactivo</span>';
        
        row.innerHTML = `
            <td>${modulo.id}</td>
            <td>${modulo.nombre_modulo}</td>
            <td>${estadoBadge}</td>
            <td>${modulo.fecha_creacion || '-'}</td>
            <td>${modulo.fecha_actualizacion || '-'}</td>
            <td>
                <button class="btn btn-sm btn-info me-1" onclick="editarModulo(${modulo.id})" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-warning" onclick="cambiarEstadoModulo(${modulo.id}, ${modulo.estado_modulo})" title="Cambiar estado">
                    <i class="fas fa-toggle-${modulo.estado_modulo == 1 ? 'on' : 'off'}"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Función para cargar privilegios
function cargarPrivilegios() {
    fetch('modulos_privilegios_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=obtener_privilegios'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            datosPrivilegios = data.privilegios;
            mostrarPrivilegios(data.privilegios);
        }
    })
    .catch(error => {
        console.error('Error al cargar privilegios:', error);
    });
}

// Función para mostrar privilegios
function mostrarPrivilegios(privilegios) {
    const tbody = document.getElementById('tbodyPrivilegios');
    tbody.innerHTML = '';
    
    if (privilegios.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center">No hay privilegios asignados</td></tr>';
        return;
    }
    
    privilegios.forEach(privilegio => {
        const row = document.createElement('tr');
        const rolBadge = privilegio.privilegio === 'Administrador'
            ? '<span class="badge bg-danger">Administrador</span>'
            : '<span class="badge bg-info">Usuario</span>';
        
        row.innerHTML = `
            <td>${privilegio.nombre_usuario || '-'}</td>
            <td>${privilegio.nombre_modulo || '-'}</td>
            <td>${rolBadge}</td>
            <td>${privilegio.fecha_registro || '-'}</td>
            <td>
                <button class="btn btn-sm btn-danger" onclick="eliminarPrivilegio(${privilegio.id})" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Función para filtrar privilegios
function filtrarPrivilegios() {
    const busqueda = document.getElementById('searchInputPrivilegios').value.toLowerCase();
    const privilegiosFiltrados = datosPrivilegios.filter(privilegio => 
        (privilegio.nombre_usuario || '').toLowerCase().includes(busqueda) ||
        (privilegio.nombre_modulo || '').toLowerCase().includes(busqueda) ||
        (privilegio.privilegio || '').toLowerCase().includes(busqueda)
    );
    mostrarPrivilegios(privilegiosFiltrados);
}

// Función para abrir modal de privilegio
function abrirModalPrivilegio() {
    const modal = new bootstrap.Modal(document.getElementById('modalPrivilegio'));
    document.getElementById('formPrivilegio').reset();
    document.getElementById('privilegioId').value = '';
    document.getElementById('modalPrivilegioTitle').textContent = 'Asignar Privilegio';
    
    // Cargar usuarios y módulos
    cargarOpcionesModal();
    modal.show();
}

// Función para cargar opciones del modal
function cargarOpcionesModal() {
    // Cargar usuarios
    fetch('modulos_privilegios_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=obtener_usuarios'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const select = document.getElementById('usuarioPrivilegio');
            select.innerHTML = '<option value="">Seleccionar usuario</option>';
            data.usuarios.forEach(usuario => {
                select.innerHTML += `<option value="${usuario.USUARIO_ID}">${usuario.USERNAME} - ${usuario.NOMBRE_COMPLETO || ''}</option>`;
            });
        }
    });
    
    // Cargar módulos activos
    fetch('modulos_privilegios_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=obtener_modulos'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const select = document.getElementById('moduloPrivilegio');
            select.innerHTML = '<option value="">Seleccionar módulo</option>';
            data.modulos.filter(m => m.estado_modulo == 1).forEach(modulo => {
                select.innerHTML += `<option value="${modulo.id}">${modulo.nombre_modulo}</option>`;
            });
        }
    });
    
    // Cargar roles
    fetch('modulos_privilegios_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=obtener_roles'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const select = document.getElementById('rolPrivilegio');
            select.innerHTML = '<option value="">Seleccionar privilegio</option>';
            data.roles.forEach(rol => {
                select.innerHTML += `<option value="${rol.id}">${rol.nombre_rol}</option>`;
            });
        }
    });
}

// Función para guardar privilegio
function guardarPrivilegio() {
    const form = document.getElementById('formPrivilegio');
    const formData = new FormData(form);
    
    const data = {
        action: 'crear_privilegio',
        id_usuario: formData.get('id_usuario'),
        id_modulo: formData.get('id_modulo'),
        id_rol_sistema: formData.get('id_rol_sistema')
    };
    
    fetch('modulos_privilegios_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalPrivilegio')).hide();
            cargarPrivilegios();
            Swal.fire('Éxito', result.message, 'success');
        } else {
            Swal.fire('Error', result.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'Error al guardar el privilegio', 'error');
    });
}

// Función para eliminar privilegio
function eliminarPrivilegio(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('modulos_privilegios_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'eliminar_privilegio',
                    id: id
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    cargarPrivilegios();
                    Swal.fire('Eliminado', result.message, 'success');
                } else {
                    Swal.fire('Error', result.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Error al eliminar el privilegio', 'error');
            });
        }
    });
}

// Función para cambiar estado del módulo
function cambiarEstadoModulo(id, estadoActual) {
    const nuevoEstado = estadoActual == 1 ? 0 : 1;
    const textoEstado = nuevoEstado == 1 ? 'activar' : 'desactivar';
    
    Swal.fire({
        title: `¿${textoEstado.charAt(0).toUpperCase() + textoEstado.slice(1)} el módulo?`,
        text: `¿Deseas ${textoEstado} este módulo?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: `Sí, ${textoEstado}`,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('modulos_privilegios_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'cambiar_estado_modulo',
                    id: id,
                    estado: nuevoEstado
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    cargarModulos();
                    Swal.fire('Éxito', result.message, 'success');
                } else {
                    Swal.fire('Error', result.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Error al cambiar el estado del módulo', 'error');
            });
        }
    });
}

// Función para abrir modal de módulo
function abrirModalModulo() {
    const modal = new bootstrap.Modal(document.getElementById('modalModulo'));
    document.getElementById('formModulo').reset();
    document.getElementById('moduloId').value = '';
    document.getElementById('modalModuloTitle').textContent = 'Nuevo Módulo';
    document.getElementById('estadoModulo').value = '1';
    modal.show();
}

// Función para editar módulo
function editarModulo(id) {
    const modulo = datosModulos.find(m => m.id == id);
    if (!modulo) {
        Swal.fire('Error', 'Módulo no encontrado', 'error');
        return;
    }
    
    document.getElementById('moduloId').value = modulo.id;
    document.getElementById('nombreModulo').value = modulo.nombre_modulo;
    document.getElementById('estadoModulo').value = modulo.estado_modulo;
    document.getElementById('modalModuloTitle').textContent = 'Editar Módulo';
    
    const modal = new bootstrap.Modal(document.getElementById('modalModulo'));
    modal.show();
}

// Función para guardar módulo
function guardarModulo() {
    const form = document.getElementById('formModulo');
    const formData = new FormData(form);
    
    const data = {
        action: formData.get('id') ? 'editar_modulo' : 'crear_modulo',
        id: formData.get('id') || '',
        nombre_modulo: formData.get('nombre_modulo'),
        estado_modulo: formData.get('estado_modulo')
    };
    
    fetch('modulos_privilegios_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalModulo')).hide();
            cargarModulos();
            Swal.fire('Éxito', result.message, 'success');
        } else {
            Swal.fire('Error', result.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'Error al guardar el módulo', 'error');
    });
}

// Event listeners para las pestañas
document.addEventListener('DOMContentLoaded', function() {
    const tabTriggers = document.querySelectorAll('#mainTabs button[data-bs-toggle="tab"]');
    
    tabTriggers.forEach(trigger => {
        trigger.addEventListener('shown.bs.tab', function(event) {
            const target = event.target.getAttribute('data-bs-target');
            if (target === '#modulos') {
                cargarModulos();
            } else if (target === '#privilegios') {
                cargarPrivilegios();
            }
        });
    });
    
    // Cargar módulos al inicio
    cargarModulos();
});
</script>

<?php include '../includes/footer.php'; ?>

