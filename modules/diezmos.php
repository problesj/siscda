<?php 
require_once dirname(__DIR__) . '/session_config.php';
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth_functions.php';

// Verificar autenticación
verificarAutenticacion();

// Verificar acceso al módulo de Diezmos
verificarAccesoModulo('Diezmos');

// Verificar si el usuario es Administrador del módulo
$esAdministrador = esAdministradorModulo($_SESSION['usuario_id'], 'Diezmos');

// Verificar si el usuario es admin
$esAdmin = isset($_SESSION['username']) && strtolower($_SESSION['username']) === 'admin';

include '../includes/header.php'; 
?>

<script>
// Variable global para verificar si el usuario es administrador
const esAdministradorDiezmos = <?php echo $esAdministrador ? 'true' : 'false'; ?>;
// Variable global para verificar si el usuario es admin
const esAdmin = <?php echo $esAdmin ? 'true' : 'false'; ?>;
// Variable global para verificar si puede cambiar estado (admin o administrador)
const puedeCambiarEstado = <?php echo ($esAdmin || $esAdministrador) ? 'true' : 'false'; ?>;
</script>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Gestión de Diezmos</h1>
    <?php if ($esAdministrador || $esAdmin): ?>
    <button class="btn btn-primary" onclick="nuevoSobre()">
        <i class="fas fa-plus"></i> Nuevo Sobre
    </button>
    <?php endif; ?>
</div>

<!-- Tabla de sobres de diezmos -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Listado de Sobres de Diezmos</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="tablaDiezmos" width="100%" cellspacing="0">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Sobre</th>
                        <th>Personas Asociadas</th>
                        <th>Estado</th>
                        <th>Fecha Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyDiezmos">
                    <!-- Se llenará dinámicamente con JavaScript -->
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <nav aria-label="Paginación de diezmos">
            <ul class="pagination justify-content-center" id="paginacionDiezmos">
                <!-- Se generará dinámicamente -->
            </ul>
        </nav>
    </div>
</div>

<!-- Modal para Crear/Editar Sobre -->
<div class="modal fade" id="modalSobre" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSobreTitle">Nuevo Sobre de Diezmo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formSobre">
                <div class="modal-body">
                    <input type="hidden" id="sobreId" name="id">
                    
                    <div class="mb-3">
                        <label for="sobreNombre" class="form-label">Nombre del Sobre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="sobreNombre" name="sobre" maxlength="300" required placeholder="Ingrese el nombre de la persona o familia">
                        <div class="form-text">Máximo 300 caracteres</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Vincular Personas (Opcional)</label>
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" id="buscarPersona" placeholder="Buscar por nombre, apellido, RUT o familia..." onkeyup="buscarPersonas()">
                            <button type="button" class="btn btn-outline-secondary" onclick="buscarPersonas()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <div id="resultadosBusqueda" class="list-group" style="max-height: 200px; overflow-y: auto; display: none;"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Personas Vinculadas</label>
                        <div id="personasVinculadas" class="border rounded p-2" style="min-height: 50px;">
                            <p class="text-muted mb-0">No hay personas vinculadas</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Ver Detalles del Sobre -->
<div class="modal fade" id="modalVerSobre" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles del Sobre de Diezmo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="contenidoDetalleSobre">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Registrar Pagos -->
<div class="modal fade" id="modalPagos" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPagosTitle">Registrar Pagos de Diezmos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formPagos">
                <div class="modal-body">
                    <input type="hidden" id="pagosSobreId" name="id_sobre">
                    <input type="hidden" id="pagosAnho" name="anho">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label"><strong>Sobre:</strong> <span id="pagosSobreNombre"></span></label>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><strong>Año:</strong> <span id="pagosAnhoDisplay"></span></label>
                        </div>
                    </div>
                    
                    <div class="alert alert-info" id="alertaEstadoSobre" style="display: none;"></div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Mes</th>
                                    <th>Monto</th>
                                    <th>Fecha de Pago</th>
                                </tr>
                            </thead>
                            <tbody id="tablaPagosMensuales">
                                <!-- Se llenará dinámicamente -->
                            </tbody>
                            <tfoot class="table-success">
                                <tr>
                                    <th class="text-end">TOTAL ANUAL:</th>
                                    <th class="text-end" id="totalAnual">$0</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarPagos">Guardar Pagos</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Variables globales
let datosDiezmos = [];
let datosFiltrados = [];
let paginaActual = 1;
const elementosPorPagina = 10;
let personasSeleccionadas = [];
let timeoutBusqueda = null;

// Meses del año
const meses = [
    { nombre: 'Enero', campo: 'enero' },
    { nombre: 'Febrero', campo: 'febrero' },
    { nombre: 'Marzo', campo: 'marzo' },
    { nombre: 'Abril', campo: 'abril' },
    { nombre: 'Mayo', campo: 'mayo' },
    { nombre: 'Junio', campo: 'junio' },
    { nombre: 'Julio', campo: 'julio' },
    { nombre: 'Agosto', campo: 'agosto' },
    { nombre: 'Septiembre', campo: 'septiembre' },
    { nombre: 'Octubre', campo: 'octubre' },
    { nombre: 'Noviembre', campo: 'noviembre' },
    { nombre: 'Diciembre', campo: 'diciembre' }
];

// Función para cargar diezmos
function cargarDiezmos() {
    fetch('diezmos_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=obtener_diezmos'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor: ' + response.status);
        }
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Error al parsear JSON:', text);
                throw new Error('Respuesta inválida del servidor: ' + text.substring(0, 100));
            }
        });
    })
    .then(data => {
        if (data.success) {
            datosDiezmos = data.diezmos || [];
            datosFiltrados = [...datosDiezmos];
            mostrarDiezmos();
        } else {
            console.error('Error del servidor:', data);
            Swal.fire('Error', data.message || 'Error al cargar los sobres', 'error');
        }
    })
    .catch(error => {
        console.error('Error al cargar diezmos:', error);
        Swal.fire('Error', 'Error al cargar los sobres: ' + error.message, 'error');
    });
}

// Función para mostrar diezmos
function mostrarDiezmos() {
    const tbody = document.getElementById('tbodyDiezmos');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    const inicio = (paginaActual - 1) * elementosPorPagina;
    const fin = inicio + elementosPorPagina;
    const datosPagina = datosFiltrados.slice(inicio, fin);
    
    if (datosPagina.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">No hay sobres registrados</td></tr>';
        document.getElementById('paginacionDiezmos').innerHTML = '';
        return;
    }
    
    datosPagina.forEach(diezmo => {
        const row = document.createElement('tr');
        const estadoTexto = parseInt(diezmo.estado_sobre) === 1 ? 'Activo' : 'Inactivo';
        const estadoBadge = parseInt(diezmo.estado_sobre) === 1 ? 'success' : 'danger';
        const personasAsociadas = diezmo.personas_asociadas || 'Sin personas asociadas';
        const fechaCreacion = diezmo.fecha_creacion || '-';
        
        row.innerHTML = `
            <td>${diezmo.id}</td>
            <td>${diezmo.sobre}</td>
            <td>${personasAsociadas}</td>
            <td><span class="badge bg-${estadoBadge}">${estadoTexto}</span></td>
            <td>${fechaCreacion}</td>
            <td>
                <button class="btn btn-sm btn-secondary me-1" onclick="verDetalleSobre(${diezmo.id})" title="Ver Detalles">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-sm btn-info me-1" onclick="registrarPagos(${diezmo.id})" title="Registrar Pagos" ${parseInt(diezmo.estado_sobre) === 0 ? 'disabled' : ''}>
                    <i class="fa-solid fa-envelope-open-text"></i>
                </button>
                ${esAdministradorDiezmos || esAdmin ? `
                <button class="btn btn-sm btn-primary me-1" onclick="editarSobre(${diezmo.id})" title="Editar" ${parseInt(diezmo.estado_sobre) === 0 ? 'disabled' : ''}>
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-${parseInt(diezmo.estado_sobre) === 1 ? 'danger' : 'success'}" onclick="cambiarEstadoSobre(${diezmo.id}, ${diezmo.estado_sobre})" title="${parseInt(diezmo.estado_sobre) === 1 ? 'Desactivar' : 'Activar'}">
                    <i class="fas fa-toggle-${parseInt(diezmo.estado_sobre) === 1 ? 'on' : 'off'}"></i>
                </button>
                ` : ''}
            </td>
        `;
        tbody.appendChild(row);
    });
    
    // Actualizar paginación
    actualizarPaginacion();
}

// Función para actualizar paginación
function actualizarPaginacion() {
    const totalPaginas = Math.ceil(datosFiltrados.length / elementosPorPagina);
    const paginacion = document.getElementById('paginacionDiezmos');
    
    if (totalPaginas <= 1) {
        paginacion.innerHTML = '';
        return;
    }
    
    let html = '';
    
    // Botón anterior
    html += `<li class="page-item ${paginaActual === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="cambiarPagina(${paginaActual - 1}); return false;">Anterior</a>
    </li>`;
    
    // Números de página
    for (let i = 1; i <= totalPaginas; i++) {
        if (i === 1 || i === totalPaginas || (i >= paginaActual - 2 && i <= paginaActual + 2)) {
            html += `<li class="page-item ${i === paginaActual ? 'active' : ''}">
                <a class="page-link" href="#" onclick="cambiarPagina(${i}); return false;">${i}</a>
            </li>`;
        } else if (i === paginaActual - 3 || i === paginaActual + 3) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    // Botón siguiente
    html += `<li class="page-item ${paginaActual === totalPaginas ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="cambiarPagina(${paginaActual + 1}); return false;">Siguiente</a>
    </li>`;
    
    paginacion.innerHTML = html;
}

// Función para cambiar página
function cambiarPagina(pagina) {
    if (pagina < 1) return;
    const totalPaginas = Math.ceil(datosFiltrados.length / elementosPorPagina);
    if (pagina > totalPaginas) return;
    
    paginaActual = pagina;
    mostrarDiezmos();
}

// Función para nuevo sobre
function nuevoSobre() {
    document.getElementById('modalSobreTitle').textContent = 'Nuevo Sobre de Diezmo';
    document.getElementById('sobreId').value = '';
    document.getElementById('sobreNombre').value = '';
    personasSeleccionadas = [];
    actualizarPersonasVinculadas();
    const resultadosBusqueda = document.getElementById('resultadosBusqueda');
    if (resultadosBusqueda) {
        resultadosBusqueda.style.display = 'none';
        resultadosBusqueda.innerHTML = '';
    }
    
    const modal = new bootstrap.Modal(document.getElementById('modalSobre'));
    modal.show();
}

// Función para editar sobre
function editarSobre(id) {
    fetch(`diezmos_actions.php?action=obtener_detalle&id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Error al parsear JSON:', text);
                    throw new Error('Respuesta inválida del servidor: ' + text.substring(0, 100));
                }
            });
        })
        .then(data => {
            if (data.success) {
                // Verificar si el sobre está inactivo
                if (parseInt(data.sobre.estado_sobre) === 0) {
                    Swal.fire('Error', 'No se puede editar un sobre inactivo. Debe activarlo primero.', 'error');
                    return;
                }
                
                document.getElementById('modalSobreTitle').textContent = 'Editar Sobre de Diezmo';
                document.getElementById('sobreId').value = data.sobre.id;
                document.getElementById('sobreNombre').value = data.sobre.sobre;
                personasSeleccionadas = data.personas.map(p => ({
                    id: p.ID,
                    nombre: `${p.NOMBRES} ${p.APELLIDO_PATERNO} ${p.APELLIDO_MATERNO || ''}`.trim(),
                    rut: p.RUT
                }));
                actualizarPersonasVinculadas();
                const resultadosBusqueda = document.getElementById('resultadosBusqueda');
                if (resultadosBusqueda) {
                    resultadosBusqueda.style.display = 'none';
                    resultadosBusqueda.innerHTML = '';
                }
                
                const modal = new bootstrap.Modal(document.getElementById('modalSobre'));
                modal.show();
            } else {
                Swal.fire('Error', data.message || 'Error al cargar el sobre', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Error al cargar el sobre: ' + error.message, 'error');
        });
}

// Función para buscar personas
function buscarPersonas() {
    const busqueda = document.getElementById('buscarPersona').value.trim();
    const resultados = document.getElementById('resultadosBusqueda');
    
    if (!resultados) return;
    
    if (busqueda.length < 2) {
        resultados.style.display = 'none';
        resultados.innerHTML = '';
        return;
    }
    
    clearTimeout(timeoutBusqueda);
    timeoutBusqueda = setTimeout(() => {
        fetch(`diezmos_actions.php?action=buscar_personas&q=${encodeURIComponent(busqueda)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.personas.length === 0) {
                        if (resultados) {
                            resultados.innerHTML = '<div class="list-group-item">No se encontraron personas</div>';
                            resultados.style.display = 'block';
                        }
                        return;
                    }
                    
                    let html = '';
                    data.personas.forEach(persona => {
                        const nombreCompleto = `${persona.NOMBRES} ${persona.APELLIDO_PATERNO} ${persona.APELLIDO_MATERNO || ''}`.trim();
                        const yaSeleccionada = personasSeleccionadas.some(p => p.id === persona.ID);
                        
                        html += `
                            <a href="#" class="list-group-item list-group-item-action ${yaSeleccionada ? 'disabled' : ''}" 
                               onclick="${yaSeleccionada ? 'return false;' : `agregarPersona(${persona.ID}, '${nombreCompleto.replace(/'/g, "\\'")}', '${persona.RUT || ''}'); return false;`}">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">${nombreCompleto}</h6>
                                    ${yaSeleccionada ? '<small class="text-muted">Ya agregada</small>' : ''}
                                </div>
                                <small class="text-muted">RUT: ${persona.RUT || 'N/A'} | Familia: ${persona.FAMILIA || 'N/A'}</small>
                            </a>
                        `;
                    });
                    if (resultados) {
                        resultados.innerHTML = html;
                        resultados.style.display = 'block';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }, 300);
}

// Función para agregar persona
function agregarPersona(id, nombre, rut) {
    if (personasSeleccionadas.some(p => p.id === id)) {
        return;
    }
    
    personasSeleccionadas.push({ id, nombre, rut });
    actualizarPersonasVinculadas();
    const buscarPersona = document.getElementById('buscarPersona');
    if (buscarPersona) buscarPersona.value = '';
    const resultadosBusqueda = document.getElementById('resultadosBusqueda');
    if (resultadosBusqueda) {
        resultadosBusqueda.style.display = 'none';
        resultadosBusqueda.innerHTML = '';
    }
}

// Función para eliminar persona
function eliminarPersona(id) {
    personasSeleccionadas = personasSeleccionadas.filter(p => p.id !== id);
    actualizarPersonasVinculadas();
}

// Función para actualizar personas vinculadas
function actualizarPersonasVinculadas() {
    const contenedor = document.getElementById('personasVinculadas');
    
    if (personasSeleccionadas.length === 0) {
        contenedor.innerHTML = '<p class="text-muted mb-0">No hay personas vinculadas</p>';
        return;
    }
    
    let html = '';
    personasSeleccionadas.forEach(persona => {
        html += `
            <span class="badge bg-primary me-2 mb-2" style="font-size: 0.9em;">
                ${persona.nombre}
                <button type="button" class="btn-close btn-close-white ms-1" style="font-size: 0.7em;" onclick="eliminarPersona(${persona.id})" aria-label="Eliminar"></button>
            </span>
        `;
    });
    contenedor.innerHTML = html;
}

// Función para guardar sobre
document.getElementById('formSobre').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const id = document.getElementById('sobreId').value;
    const sobre = document.getElementById('sobreNombre').value.trim();
    
    if (!sobre) {
        Swal.fire('Error', 'El nombre del sobre es obligatorio', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'guardar_sobre');
    formData.append('id', id);
    formData.append('sobre', sobre);
    personasSeleccionadas.forEach((persona, index) => {
        formData.append(`personas_ids[${index}]`, persona.id);
    });
    
    fetch('diezmos_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Éxito', data.message, 'success').then(() => {
                bootstrap.Modal.getInstance(document.getElementById('modalSobre')).hide();
                cargarDiezmos();
            });
        } else {
            Swal.fire('Error', data.message || 'Error al guardar el sobre', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'Error al guardar el sobre', 'error');
    });
});

// Función para registrar pagos
function registrarPagos(id) {
    const anhoActual = new Date().getFullYear();
    
    fetch(`diezmos_actions.php?action=obtener_detalle&id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Error al parsear JSON:', text);
                    throw new Error('Respuesta inválida del servidor: ' + text.substring(0, 100));
                }
            });
        })
        .then(data => {
            if (data.success) {
                document.getElementById('pagosSobreId').value = id;
                document.getElementById('pagosAnho').value = anhoActual;
                document.getElementById('pagosSobreNombre').textContent = data.sobre.sobre;
                document.getElementById('pagosAnhoDisplay').textContent = anhoActual;
                
                const estadoActivo = parseInt(data.sobre.estado_sobre) === 1;
                const alerta = document.getElementById('alertaEstadoSobre');
                const btnGuardar = document.getElementById('btnGuardarPagos');
                
                if (alerta) {
                    if (!estadoActivo) {
                        alerta.className = 'alert alert-warning';
                        alerta.textContent = 'Este sobre está inactivo. No se pueden registrar pagos.';
                        alerta.style.display = 'block';
                    } else {
                        alerta.style.display = 'none';
                    }
                }
                
                if (btnGuardar) {
                    btnGuardar.disabled = !estadoActivo;
                }
                
                // Generar tabla de pagos mensuales
                let html = '';
                meses.forEach(mes => {
                    // Asegurar que pagos sea un objeto
                    const pago = (data.pagos && typeof data.pagos === 'object') ? data.pagos : {};
                    const monto = pago[`monto_${mes.campo}`] || 0;
                    const fecha = pago[`fecha_pago_${mes.campo}`] || '';
                    
                    html += `
                        <tr>
                            <td><strong>${mes.nombre}</strong></td>
                            <td>
                                <input type="number" class="form-control form-control-sm monto-mes" 
                                       id="monto_${mes.campo}" name="monto_${mes.campo}" 
                                       min="0" value="${monto}" 
                                       onchange="calcularTotalAnual()" 
                                       ${!estadoActivo ? 'disabled' : ''}>
                            </td>
                            <td>
                                <input type="date" class="form-control form-control-sm" 
                                       id="fecha_pago_${mes.campo}" name="fecha_pago_${mes.campo}" 
                                       value="${fecha}" 
                                       ${!estadoActivo ? 'disabled' : ''}>
                            </td>
                        </tr>
                    `;
                });
                document.getElementById('tablaPagosMensuales').innerHTML = html;
                calcularTotalAnual();
                
                const modal = new bootstrap.Modal(document.getElementById('modalPagos'));
                modal.show();
            } else {
                Swal.fire('Error', data.message || 'Error al cargar el sobre', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Error al cargar el sobre: ' + error.message, 'error');
        });
}

// Función para calcular total anual
function calcularTotalAnual() {
    let total = 0;
    meses.forEach(mes => {
        const input = document.getElementById(`monto_${mes.campo}`);
        if (input) {
            total += parseInt(input.value) || 0;
        }
    });
    
    const totalFormateado = new Intl.NumberFormat('es-CL', {
        style: 'currency',
        currency: 'CLP',
        minimumFractionDigits: 0
    }).format(total);
    
    document.getElementById('totalAnual').textContent = totalFormateado;
}

// Función para guardar pagos
document.getElementById('formPagos').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'guardar_pagos');
    
    fetch('diezmos_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Éxito', data.message, 'success').then(() => {
                bootstrap.Modal.getInstance(document.getElementById('modalPagos')).hide();
            });
        } else {
            Swal.fire('Error', data.message || 'Error al guardar los pagos', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'Error al guardar los pagos', 'error');
    });
});

// Función para ver detalles del sobre
function verDetalleSobre(id) {
    const anhoActual = new Date().getFullYear();
    
    fetch(`diezmos_actions.php?action=obtener_detalle&id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Error al parsear JSON:', text);
                    throw new Error('Respuesta inválida del servidor: ' + text.substring(0, 100));
                }
            });
        })
        .then(data => {
            if (data.success) {
                const sobre = data.sobre;
                const pagos = data.pagos || {};
                const personas = data.personas || [];
                
                const estadoTexto = parseInt(sobre.estado_sobre) === 1 ? 'Activo' : 'Inactivo';
                const estadoBadge = parseInt(sobre.estado_sobre) === 1 ? 'success' : 'danger';
                
                // Calcular total anual
                let totalAnual = 0;
                meses.forEach(mes => {
                    const monto = parseInt(pagos[`monto_${mes.campo}`]) || 0;
                    totalAnual += monto;
                });
                
                const totalFormateado = new Intl.NumberFormat('es-CL', {
                    style: 'currency',
                    currency: 'CLP',
                    minimumFractionDigits: 0
                }).format(totalAnual);
                
                // Construir tabla de meses
                let tablaMeses = '';
                meses.forEach(mes => {
                    const monto = parseInt(pagos[`monto_${mes.campo}`]) || 0;
                    const fecha = pagos[`fecha_pago_${mes.campo}`] || '-';
                    const montoFormateado = monto > 0 ? new Intl.NumberFormat('es-CL', {
                        style: 'currency',
                        currency: 'CLP',
                        minimumFractionDigits: 0
                    }).format(monto) : '-';
                    
                    tablaMeses += `
                        <tr>
                            <td><strong>${mes.nombre}</strong></td>
                            <td class="text-end">${montoFormateado}</td>
                            <td>${fecha}</td>
                        </tr>
                    `;
                });
                
                // Construir lista de personas
                let listaPersonas = '';
                if (personas.length > 0) {
                    personas.forEach(persona => {
                        const nombreCompleto = `${persona.NOMBRES} ${persona.APELLIDO_PATERNO} ${persona.APELLIDO_MATERNO || ''}`.trim();
                        listaPersonas += `<li>${nombreCompleto} ${persona.RUT ? `(${persona.RUT})` : ''}</li>`;
                    });
                } else {
                    listaPersonas = '<li class="text-muted">No hay personas vinculadas</li>';
                }
                
                const html = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Nombre del Sobre:</strong> ${sobre.sobre}
                        </div>
                        <div class="col-md-6">
                            <strong>Estado:</strong> <span class="badge bg-${estadoBadge}">${estadoTexto}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Fecha de Creación:</strong> ${sobre.fecha_creacion || '-'}
                        </div>
                        <div class="col-md-6">
                            <strong>Última Actualización:</strong> ${sobre.fecha_actualizacion || '-'}
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <strong>Personas Vinculadas:</strong>
                        <ul class="mb-0">
                            ${listaPersonas}
                        </ul>
                    </div>
                    <hr>
                    <h6 class="mb-3">Pagos del Año ${anhoActual}</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Mes</th>
                                    <th class="text-end">Monto</th>
                                    <th>Fecha de Pago</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${tablaMeses}
                            </tbody>
                            <tfoot class="table-success">
                                <tr>
                                    <th class="text-end">TOTAL ANUAL:</th>
                                    <th class="text-end">${totalFormateado}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                `;
                
                document.getElementById('contenidoDetalleSobre').innerHTML = html;
                
                const modal = new bootstrap.Modal(document.getElementById('modalVerSobre'));
                modal.show();
            } else {
                Swal.fire('Error', data.message || 'Error al cargar el sobre', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Error al cargar el sobre: ' + error.message, 'error');
        });
}

// Función para cambiar estado del sobre
function cambiarEstadoSobre(id, estadoActual) {
    const nuevoEstado = parseInt(estadoActual) === 1 ? 0 : 1;
    const accion = nuevoEstado === 1 ? 'activar' : 'desactivar';
    
    Swal.fire({
        title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} el sobre?`,
        text: `¿Está seguro que desea ${accion} este sobre?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: nuevoEstado === 1 ? '#28a745' : '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: `Sí, ${accion}`,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'cambiar_estado');
            formData.append('id', id);
            formData.append('estado', nuevoEstado);
            
            fetch('diezmos_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Éxito', data.message, 'success').then(() => {
                        cargarDiezmos();
                    });
                } else {
                    Swal.fire('Error', data.message || 'Error al cambiar el estado', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Error al cambiar el estado', 'error');
            });
        }
    });
}

// Cargar diezmos al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarDiezmos();
});
</script>

<?php include '../includes/footer.php'; ?>

