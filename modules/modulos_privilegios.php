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
                            <input type="text" class="form-control" id="searchInputPrivilegios" placeholder="Buscar por usuario..." oninput="filtrarPrivilegios()">
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

<!-- Modal para Asignar Privilegios (Mejorado - Todos los módulos) -->
<div class="modal fade" id="modalPrivilegio" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPrivilegioTitle">Asignar Privilegios por Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formPrivilegio">
                <div class="modal-body">
                    <div class="mb-4">
                        <label for="usuarioPrivilegio" class="form-label fw-bold">Seleccionar Usuario *</label>
                        <select class="form-select form-select-lg" id="usuarioPrivilegio" name="id_usuario" required onchange="cargarPrivilegiosUsuario()">
                            <option value="">Seleccionar usuario</option>
                        </select>
                        <div class="form-text">Selecciona un usuario para asignar o modificar sus privilegios en todos los módulos</div>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <h6 class="fw-bold mb-3">Privilegios por Módulo</h6>
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width: 50%;">Módulo</th>
                                        <th style="width: 50%;">Privilegio</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyModulosPrivilegios">
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">
                                            <i class="fas fa-info-circle"></i> Selecciona un usuario para ver los módulos
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarPrivilegiosMasivos()">
                        <i class="fas fa-save"></i> Guardar Todos los Privilegios
                    </button>
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
let modulosOrdenados = []; // Para mantener el orden de los módulos en las columnas

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
    // Primero cargar módulos activos para crear las columnas
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
            modulosOrdenados = data.modulos.filter(m => m.estado_modulo == 1).sort((a, b) => 
                a.nombre_modulo.localeCompare(b.nombre_modulo)
            );
            
            // Luego cargar privilegios
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
    })
    .catch(error => {
        console.error('Error al cargar módulos:', error);
    });
}

// Función para mostrar privilegios agrupados por usuario
function mostrarPrivilegios(privilegios) {
    const thead = document.querySelector('#tablaPrivilegios thead tr');
    const tbody = document.getElementById('tbodyPrivilegios');
    
    // Crear encabezado dinámico
    let headerHTML = '<th>Usuario</th>';
    modulosOrdenados.forEach(modulo => {
        headerHTML += `<th class="text-center">${modulo.nombre_modulo}</th>`;
    });
    headerHTML += '<th>Fecha Registro</th><th>Acciones</th>';
    thead.innerHTML = headerHTML;
    
    tbody.innerHTML = '';
    
    if (privilegios.length === 0 && modulosOrdenados.length === 0) {
        tbody.innerHTML = `<tr><td colspan="${2 + modulosOrdenados.length}" class="text-center">No hay privilegios asignados</td></tr>`;
        return;
    }
    
    // Agrupar privilegios por usuario
    const privilegiosPorUsuario = {};
    const usuariosInfo = {};
    
    privilegios.forEach(privilegio => {
        const usuarioId = privilegio.id_usuario || privilegio.USUARIO_ID;
        const nombreUsuario = privilegio.nombre_usuario || privilegio.USERNAME;
        const nombreCompleto = privilegio.NOMBRE_COMPLETO || '';
        
        if (!privilegiosPorUsuario[usuarioId]) {
            privilegiosPorUsuario[usuarioId] = {};
            usuariosInfo[usuarioId] = {
                nombre: nombreUsuario,
                nombreCompleto: nombreCompleto,
                fechaRegistro: privilegio.fecha_registro || '-'
            };
        }
        
        // Mapear módulo a privilegio
        const moduloNombre = privilegio.nombre_modulo;
        privilegiosPorUsuario[usuarioId][moduloNombre] = {
            privilegio: privilegio.privilegio,
            id: privilegio.id
        };
    });
    
    // Obtener todos los usuarios (incluso los que no tienen privilegios)
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
            const todosUsuarios = data.usuarios;
            
            // Crear filas para cada usuario
            todosUsuarios.forEach(usuario => {
                const usuarioId = usuario.USUARIO_ID;
                const privilegiosUsuario = privilegiosPorUsuario[usuarioId] || {};
                const infoUsuario = usuariosInfo[usuarioId] || {
                    nombre: usuario.USERNAME,
                    nombreCompleto: usuario.NOMBRE_COMPLETO || '',
                    fechaRegistro: '-'
                };
                
                const row = document.createElement('tr');
                let rowHTML = `<td><strong>${infoUsuario.nombre}</strong><br><small class="text-muted">${infoUsuario.nombreCompleto}</small></td>`;
                
                // Agregar columna para cada módulo
                modulosOrdenados.forEach(modulo => {
                    const privilegioModulo = privilegiosUsuario[modulo.nombre_modulo];
                    if (privilegioModulo) {
                        const rolBadge = privilegioModulo.privilegio === 'Administrador'
                            ? '<span class="badge bg-danger">Administrador</span>'
                            : '<span class="badge bg-info">Usuario</span>';
                        rowHTML += `<td class="text-center">${rolBadge}</td>`;
                    } else {
                        rowHTML += '<td class="text-center text-muted">-</td>';
                    }
                });
                
                // Fecha registro (usar la más reciente si hay múltiples)
                rowHTML += `<td>${infoUsuario.fechaRegistro}</td>`;
                
                // Acciones - botón para editar privilegios del usuario
                rowHTML += `<td>
                    <button class="btn btn-sm btn-primary" onclick="editarPrivilegiosUsuario(${usuarioId})" title="Editar Privilegios">
                        <i class="fas fa-edit"></i>
                    </button>
                </td>`;
                
                row.innerHTML = rowHTML;
                tbody.appendChild(row);
            });
            
            if (todosUsuarios.length === 0) {
                tbody.innerHTML = `<tr><td colspan="${2 + modulosOrdenados.length}" class="text-center">No hay usuarios registrados</td></tr>`;
            }
        }
    })
    .catch(error => {
        console.error('Error al cargar usuarios:', error);
        tbody.innerHTML = `<tr><td colspan="${2 + modulosOrdenados.length}" class="text-center text-danger">Error al cargar los datos</td></tr>`;
    });
}

// Función para filtrar privilegios
function filtrarPrivilegios() {
    const busqueda = document.getElementById('searchInputPrivilegios').value.toLowerCase();
    const filas = document.querySelectorAll('#tbodyPrivilegios tr');
    
    filas.forEach(fila => {
        const textoFila = fila.textContent.toLowerCase();
        if (textoFila.includes(busqueda)) {
            fila.style.display = '';
        } else {
            fila.style.display = 'none';
        }
    });
}

// Función para editar privilegios de un usuario específico
function editarPrivilegiosUsuario(usuarioId) {
    const modal = new bootstrap.Modal(document.getElementById('modalPrivilegio'));
    document.getElementById('formPrivilegio').reset();
    document.getElementById('modalPrivilegioTitle').textContent = 'Editar Privilegios de Usuario';
    
    // Deshabilitar el campo de selección de usuario
    const selectUsuario = document.getElementById('usuarioPrivilegio');
    selectUsuario.disabled = true;
    selectUsuario.style.backgroundColor = '#e9ecef';
    selectUsuario.style.cursor = 'not-allowed';
    
    // Limpiar tabla de módulos temporalmente
    document.getElementById('tbodyModulosPrivilegios').innerHTML = `
        <tr>
            <td colspan="2" class="text-center text-muted">
                <i class="fas fa-spinner fa-spin"></i> Cargando...
            </td>
        </tr>
    `;
    
    // Primero cargar las opciones del modal (usuarios, módulos, roles)
    cargarOpcionesModal().then(() => {
        // Una vez cargadas las opciones, establecer el usuario y cargar sus privilegios
        selectUsuario.value = usuarioId;
        // Disparar el evento change para cargar los privilegios automáticamente
        cargarPrivilegiosUsuario();
    });
    
    modal.show();
}

// Variables para almacenar módulos y roles
let modulosActivos = [];
let rolesSistema = [];

// Función para abrir modal de privilegio
function abrirModalPrivilegio() {
    const modal = new bootstrap.Modal(document.getElementById('modalPrivilegio'));
    document.getElementById('formPrivilegio').reset();
    const selectUsuario = document.getElementById('usuarioPrivilegio');
    selectUsuario.value = '';
    document.getElementById('modalPrivilegioTitle').textContent = 'Asignar Privilegios por Usuario';
    
    // Habilitar el campo de selección de usuario (por si estaba deshabilitado)
    selectUsuario.disabled = false;
    selectUsuario.style.backgroundColor = '';
    selectUsuario.style.cursor = '';
    
    // Limpiar tabla de módulos
    document.getElementById('tbodyModulosPrivilegios').innerHTML = `
        <tr>
            <td colspan="2" class="text-center text-muted">
                <i class="fas fa-info-circle"></i> Selecciona un usuario para ver los módulos
            </td>
        </tr>
    `;
    
    // Cargar usuarios, módulos y roles
    cargarOpcionesModal().then(() => {
        // Las opciones ya están cargadas, el usuario puede seleccionar
    });
    modal.show();
}

// Función para cargar opciones del modal
function cargarOpcionesModal() {
    // Retornar una promesa que se resuelve cuando todas las opciones están cargadas
    return Promise.all([
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
        }),
        
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
                modulosActivos = data.modulos.filter(m => m.estado_modulo == 1).sort((a, b) => 
                    a.nombre_modulo.localeCompare(b.nombre_modulo)
                );
            }
        }),
        
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
                rolesSistema = data.roles;
            }
        })
    ]);
}

// Función para cargar privilegios del usuario seleccionado
function cargarPrivilegiosUsuario() {
    const usuarioId = document.getElementById('usuarioPrivilegio').value;
    const tbody = document.getElementById('tbodyModulosPrivilegios');
    
    if (!usuarioId) {
        tbody.innerHTML = `
            <tr>
                <td colspan="2" class="text-center text-muted">
                    <i class="fas fa-info-circle"></i> Selecciona un usuario para ver los módulos
                </td>
            </tr>
        `;
        return;
    }
    
    // Cargar privilegios actuales del usuario
    fetch('modulos_privilegios_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=obtener_privilegios_usuario&id_usuario=${usuarioId}`
    })
    .then(response => response.json())
    .then(data => {
        const privilegiosUsuario = {};
        if (data.success && data.privilegios) {
            data.privilegios.forEach(p => {
                privilegiosUsuario[p.id_modulo] = p.id_rol_sistema;
            });
        }
        
        // Mostrar todos los módulos activos con sus privilegios
        tbody.innerHTML = '';
        modulosActivos.forEach(modulo => {
            const row = document.createElement('tr');
            const privilegioActual = privilegiosUsuario[modulo.id] || '';
            
            // Crear select con opciones: Sin acceso, Usuario, Administrador
            let selectHTML = '<select class="form-select form-select-sm privilegio-modulo" data-modulo-id="' + modulo.id + '">';
            selectHTML += '<option value="">Sin acceso</option>';
            
            rolesSistema.forEach(rol => {
                const selected = privilegioActual == rol.id ? 'selected' : '';
                selectHTML += `<option value="${rol.id}" ${selected}>${rol.nombre_rol}</option>`;
            });
            
            selectHTML += '</select>';
            
            row.innerHTML = `
                <td><strong>${modulo.nombre_modulo}</strong></td>
                <td>${selectHTML}</td>
            `;
            tbody.appendChild(row);
        });
        
        if (modulosActivos.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="2" class="text-center text-muted">
                        <i class="fas fa-info-circle"></i> No hay módulos activos disponibles
                    </td>
                </tr>
            `;
        }
    })
    .catch(error => {
        console.error('Error al cargar privilegios del usuario:', error);
        Swal.fire('Error', 'Error al cargar los privilegios del usuario', 'error');
    });
}

// Función para guardar todos los privilegios masivamente
function guardarPrivilegiosMasivos() {
    const usuarioId = document.getElementById('usuarioPrivilegio').value;
    
    if (!usuarioId) {
        Swal.fire('Error', 'Debes seleccionar un usuario', 'error');
        return;
    }
    
    // Recopilar todos los privilegios seleccionados
    const privilegios = [];
    const selects = document.querySelectorAll('.privilegio-modulo');
    
    selects.forEach(select => {
        const moduloId = select.getAttribute('data-modulo-id');
        const rolId = select.value;
        
        // Si tiene un valor (no es "Sin acceso"), agregarlo
        if (rolId) {
            privilegios.push({
                id_modulo: moduloId,
                id_rol_sistema: rolId
            });
        }
    });
    
    // Confirmar antes de guardar
    Swal.fire({
        title: '¿Guardar privilegios?',
        text: `Se ${privilegios.length > 0 ? 'asignarán' : 'eliminarán todos los'} privilegios para este usuario`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Enviar datos al servidor
            const data = {
                action: 'guardar_privilegios_masivos',
                id_usuario: usuarioId,
                privilegios: JSON.stringify(privilegios)
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
                Swal.fire('Error', 'Error al guardar los privilegios', 'error');
            });
        }
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

