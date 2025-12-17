<?php 
require_once dirname(__DIR__) . '/session_config.php';
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth_functions.php';

// Verificar autenticación
verificarAutenticacion();

// Verificar acceso al módulo de Ofrendas
verificarAccesoModulo('Ofrendas');

// Verificar si el usuario es Administrador del módulo
$esAdministrador = esAdministradorModulo($_SESSION['usuario_id'], 'Ofrendas');

include '../includes/header.php'; 
?>

<script>
// Variable global para verificar si el usuario es administrador
const esAdministradorOfrendas = <?php echo $esAdministrador ? 'true' : 'false'; ?>;
</script>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Gestión de Ofrendas</h1>
</div>

<!-- Controles de búsqueda y filtros -->
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="date" class="form-control" id="filtroFecha" placeholder="Filtrar por fecha..." onchange="aplicarFiltros()">
                    <button class="btn btn-outline-danger btn-limpiar" type="button" onclick="limpiarFiltros()" title="Limpiar filtros">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-8">
                <div id="estadoFiltros" class="alert alert-info d-none" role="alert">
                    <i class="fas fa-filter"></i> <span id="textoFiltros"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de ofrendas -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Listado de Ofrendas</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="tablaOfrendas" width="100%" cellspacing="0">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Culto</th>
                        <th>Tipo de Culto</th>
                        <th>Monto</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyOfrendas">
                    <!-- Se llenará dinámicamente con JavaScript -->
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <nav aria-label="Paginación de ofrendas">
            <ul class="pagination justify-content-center" id="paginacionOfrendas">
                <!-- Se generará dinámicamente -->
            </ul>
        </nav>
    </div>
</div>

<!-- Modal para Editar Ofrenda -->
<div class="modal fade" id="modalOfrenda" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalOfrendaTitle">Editar Ofrenda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formOfrenda">
                <div class="modal-body">
                    <input type="hidden" id="ofrendaId" name="id">
                    <input type="hidden" id="ofrendaFecha" name="fecha">
                    <input type="hidden" id="ofrendaCultoId" name="id_culto">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Fecha del Culto</label>
                            <input type="text" class="form-control" id="displayFecha" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Culto</label>
                            <input type="text" class="form-control" id="displayTipoCulto" readonly>
                        </div>
                    </div>
                    
                    <hr>
                    <h6 class="mb-3">Desglose de Billetes y Monedas</h6>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40%;">Denominación</th>
                                    <th style="width: 30%;">Cantidad</th>
                                    <th style="width: 30%;" class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><label for="cantidad_20000" class="mb-0">Billetes de $20.000</label></td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm cantidad-billete" id="cantidad_20000" name="cantidad_20000" min="0" value="0" onfocus="this.select()" onchange="calcularTotal()">
                                    </td>
                                    <td class="text-end"><strong>$<span id="total_20000">0</span></strong></td>
                                </tr>
                                <tr>
                                    <td><label for="cantidad_10000" class="mb-0">Billetes de $10.000</label></td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm cantidad-billete" id="cantidad_10000" name="cantidad_10000" min="0" value="0" onfocus="this.select()" onchange="calcularTotal()">
                                    </td>
                                    <td class="text-end"><strong>$<span id="total_10000">0</span></strong></td>
                                </tr>
                                <tr>
                                    <td><label for="cantidad_5000" class="mb-0">Billetes de $5.000</label></td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm cantidad-billete" id="cantidad_5000" name="cantidad_5000" min="0" value="0" onfocus="this.select()" onchange="calcularTotal()">
                                    </td>
                                    <td class="text-end"><strong>$<span id="total_5000">0</span></strong></td>
                                </tr>
                                <tr>
                                    <td><label for="cantidad_2000" class="mb-0">Billetes de $2.000</label></td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm cantidad-billete" id="cantidad_2000" name="cantidad_2000" min="0" value="0" onfocus="this.select()" onchange="calcularTotal()">
                                    </td>
                                    <td class="text-end"><strong>$<span id="total_2000">0</span></strong></td>
                                </tr>
                                <tr>
                                    <td><label for="cantidad_1000" class="mb-0">Billetes de $1.000</label></td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm cantidad-billete" id="cantidad_1000" name="cantidad_1000" min="0" value="0" onfocus="this.select()" onchange="calcularTotal()">
                                    </td>
                                    <td class="text-end"><strong>$<span id="total_1000">0</span></strong></td>
                                </tr>
                                <tr>
                                    <td><label for="cantidad_500" class="mb-0">Monedas de $500</label></td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm cantidad-billete" id="cantidad_500" name="cantidad_500" min="0" value="0" onfocus="this.select()" onchange="calcularTotal()">
                                    </td>
                                    <td class="text-end"><strong>$<span id="total_500">0</span></strong></td>
                                </tr>
                                <tr>
                                    <td><label for="cantidad_100" class="mb-0">Monedas de $100</label></td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm cantidad-billete" id="cantidad_100" name="cantidad_100" min="0" value="0" onfocus="this.select()" onchange="calcularTotal()">
                                    </td>
                                    <td class="text-end"><strong>$<span id="total_100">0</span></strong></td>
                                </tr>
                                <tr>
                                    <td><label for="cantidad_50" class="mb-0">Monedas de $50</label></td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm cantidad-billete" id="cantidad_50" name="cantidad_50" min="0" value="0" onfocus="this.select()" onchange="calcularTotal()">
                                    </td>
                                    <td class="text-end"><strong>$<span id="total_50">0</span></strong></td>
                                </tr>
                                <tr>
                                    <td><label for="cantidad_10" class="mb-0">Monedas de $10</label></td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm cantidad-billete" id="cantidad_10" name="cantidad_10" min="0" value="0" onfocus="this.select()" onchange="calcularTotal()">
                                    </td>
                                    <td class="text-end"><strong>$<span id="total_10">0</span></strong></td>
                                </tr>
                            </tbody>
                            <tfoot class="table-success">
                                <tr>
                                    <th colspan="2" class="text-end">TOTAL:</th>
                                    <th class="text-end">$<span id="montoTotal">0</span></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning" onclick="limpiarOfrenda()">Limpiar (Poner en $0)</button>
                    <button type="button" class="btn btn-primary" onclick="guardarOfrenda()">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Variables globales
let datosOfrendas = [];
let datosFiltrados = [];
let paginaActual = 1;
const elementosPorPagina = 10;

// Montos de billetes y monedas
const montos = {
    20000: { id: 'cantidad_20000', total: 'total_20000' },
    10000: { id: 'cantidad_10000', total: 'total_10000' },
    5000: { id: 'cantidad_5000', total: 'total_5000' },
    2000: { id: 'cantidad_2000', total: 'total_2000' },
    1000: { id: 'cantidad_1000', total: 'total_1000' },
    500: { id: 'cantidad_500', total: 'total_500' },
    100: { id: 'cantidad_100', total: 'total_100' },
    50: { id: 'cantidad_50', total: 'total_50' },
    10: { id: 'cantidad_10', total: 'total_10' }
};

// Función para cargar ofrendas
function cargarOfrendas() {
    fetch('ofrendas_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=obtener_ofrendas'
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
            datosOfrendas = data.ofrendas || [];
            // Usar setTimeout para asegurar que el DOM esté listo
            setTimeout(() => {
                aplicarFiltros();
            }, 100);
        } else {
            console.error('Error del servidor:', data);
            Swal.fire('Error', data.message || 'Error al cargar las ofrendas', 'error');
        }
    })
    .catch(error => {
        console.error('Error al cargar ofrendas:', error);
        Swal.fire('Error', 'Error al cargar las ofrendas: ' + error.message, 'error');
    });
}

// Función para aplicar filtros
function aplicarFiltros() {
    try {
        // Filtrar datos primero (esto no requiere DOM)
        const filtroFechaInput = document.getElementById('filtroFecha');
        const filtroFecha = filtroFechaInput ? filtroFechaInput.value : '';
        
        datosFiltrados = datosOfrendas.filter(ofrenda => {
            if (filtroFecha && ofrenda.fecha_ofrenda !== filtroFecha) {
                return false;
            }
            return true;
        });
        
        // Actualizar estado de filtros solo si los elementos existen
        // Usar una función auxiliar para evitar errores
        const actualizarEstadoFiltros = () => {
            const estadoFiltros = document.getElementById('estadoFiltros');
            const textoFiltros = document.getElementById('textoFiltros');
            
            if (!estadoFiltros || !textoFiltros) {
                return; // Salir silenciosamente si los elementos no existen
            }
            
            if (!estadoFiltros.classList) {
                return; // Salir si classList no está disponible
            }
            
            try {
                if (filtroFecha) {
                    estadoFiltros.classList.remove('d-none');
                    if (textoFiltros.textContent !== undefined) {
                        textoFiltros.textContent = `Filtrado por fecha: ${filtroFecha} (${datosFiltrados.length} resultado${datosFiltrados.length !== 1 ? 's' : ''})`;
                    }
                } else {
                    estadoFiltros.classList.add('d-none');
                }
            } catch (e) {
                // Ignorar errores al actualizar el estado de filtros
                console.warn('No se pudo actualizar el estado de filtros:', e);
            }
        };
        
        // Intentar actualizar el estado de filtros
        actualizarEstadoFiltros();
        
        paginaActual = 1;
        mostrarOfrendas();
    } catch (error) {
        console.error('Error en aplicarFiltros:', error);
        // Continuar con la visualización aunque haya error en los filtros
        paginaActual = 1;
        mostrarOfrendas();
    }
}

// Función para limpiar filtros
function limpiarFiltros() {
    document.getElementById('filtroFecha').value = '';
    aplicarFiltros();
}

// Función para mostrar ofrendas
function mostrarOfrendas() {
    const tbody = document.getElementById('tbodyOfrendas');
    if (!tbody) {
        return; // El elemento aún no está cargado
    }
    tbody.innerHTML = '';
    
    const inicio = (paginaActual - 1) * elementosPorPagina;
    const fin = inicio + elementosPorPagina;
    const ofrendasPagina = datosFiltrados.slice(inicio, fin);
    
    if (ofrendasPagina.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">No hay ofrendas registradas</td></tr>';
        generarPaginacion();
        return;
    }
    
    ofrendasPagina.forEach(ofrenda => {
        const row = document.createElement('tr');
        const montoFormateado = new Intl.NumberFormat('es-CL', {
            style: 'currency',
            currency: 'CLP',
            minimumFractionDigits: 0
        }).format(ofrenda.monto);
        
        row.innerHTML = `
            <td>${ofrenda.id}</td>
            <td>${ofrenda.fecha_ofrenda}</td>
            <td>${ofrenda.culto_id || '-'}</td>
            <td>${ofrenda.tipo_culto || '-'}</td>
            <td class="text-end"><strong>${montoFormateado}</strong></td>
            <td>
                ${esAdministradorOfrendas ? `
                <button class="btn btn-sm btn-primary" onclick="editarOfrenda(${ofrenda.id})" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                ` : '<span class="text-muted">Solo lectura</span>'}
            </td>
        `;
        tbody.appendChild(row);
    });
    
    generarPaginacion();
}

// Función para generar paginación
function generarPaginacion() {
    const totalPaginas = Math.ceil(datosFiltrados.length / elementosPorPagina);
    const paginacion = document.getElementById('paginacionOfrendas');
    if (!paginacion) {
        return; // El elemento aún no está cargado
    }
    paginacion.innerHTML = '';
    
    if (totalPaginas <= 1) return;
    
    // Botón Anterior
    const liAnterior = document.createElement('li');
    liAnterior.className = `page-item ${paginaActual === 1 ? 'disabled' : ''}`;
    liAnterior.innerHTML = `<a class="page-link" href="#" onclick="irAPagina(${paginaActual - 1}); return false;">Anterior</a>`;
    paginacion.appendChild(liAnterior);
    
    // Números de página
    for (let i = 1; i <= totalPaginas; i++) {
        if (i === 1 || i === totalPaginas || (i >= paginaActual - 2 && i <= paginaActual + 2)) {
            const li = document.createElement('li');
            li.className = `page-item ${i === paginaActual ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="#" onclick="irAPagina(${i}); return false;">${i}</a>`;
            paginacion.appendChild(li);
        } else if (i === paginaActual - 3 || i === paginaActual + 3) {
            const li = document.createElement('li');
            li.className = 'page-item disabled';
            li.innerHTML = '<a class="page-link" href="#">...</a>';
            paginacion.appendChild(li);
        }
    }
    
    // Botón Siguiente
    const liSiguiente = document.createElement('li');
    liSiguiente.className = `page-item ${paginaActual === totalPaginas ? 'disabled' : ''}`;
    liSiguiente.innerHTML = `<a class="page-link" href="#" onclick="irAPagina(${paginaActual + 1}); return false;">Siguiente</a>`;
    paginacion.appendChild(liSiguiente);
}

// Función para ir a una página
function irAPagina(pagina) {
    const totalPaginas = Math.ceil(datosFiltrados.length / elementosPorPagina);
    if (pagina < 1 || pagina > totalPaginas) return;
    paginaActual = pagina;
    mostrarOfrendas();
}

// Función para editar ofrenda
function editarOfrenda(id) {
    const ofrenda = datosOfrendas.find(o => o.id === id);
    if (!ofrenda) {
        Swal.fire('Error', 'Ofrenda no encontrada', 'error');
        return;
    }
    
    // Cargar detalles del culto
    fetch('ofrendas_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=obtener_detalle&id=${id}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const detalle = data.ofrenda;
            
            document.getElementById('ofrendaId').value = detalle.id;
            document.getElementById('ofrendaFecha').value = detalle.fecha_ofrenda;
            document.getElementById('ofrendaCultoId').value = detalle.id_culto;
            document.getElementById('displayFecha').value = detalle.fecha_ofrenda;
            document.getElementById('displayTipoCulto').value = detalle.tipo_culto || '-';
            
            // Cargar las cantidades guardadas por denominación
            document.getElementById('cantidad_20000').value = detalle.cantidad_20000 || 0;
            document.getElementById('cantidad_10000').value = detalle.cantidad_10000 || 0;
            document.getElementById('cantidad_5000').value = detalle.cantidad_5000 || 0;
            document.getElementById('cantidad_2000').value = detalle.cantidad_2000 || 0;
            document.getElementById('cantidad_1000').value = detalle.cantidad_1000 || 0;
            document.getElementById('cantidad_500').value = detalle.cantidad_500 || 0;
            document.getElementById('cantidad_100').value = detalle.cantidad_100 || 0;
            document.getElementById('cantidad_50').value = detalle.cantidad_50 || 0;
            document.getElementById('cantidad_10').value = detalle.cantidad_10 || 0;
            
            // Calcular y mostrar el total
            calcularTotal();
            
            const modal = new bootstrap.Modal(document.getElementById('modalOfrenda'));
            modal.show();
        } else {
            Swal.fire('Error', data.message || 'Error al cargar los detalles', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'Error al cargar los detalles', 'error');
    });
}

// Función para calcular el total
function calcularTotal() {
    let total = 0;
    
    Object.keys(montos).forEach(monto => {
        const cantidad = parseInt(document.getElementById(montos[monto].id).value) || 0;
        const subtotal = cantidad * parseInt(monto);
        // Formatear con separadores de miles sin decimales
        document.getElementById(montos[monto].total).textContent = subtotal.toLocaleString('es-CL', {minimumFractionDigits: 0, maximumFractionDigits: 0});
        total += subtotal;
    });
    
    // Formatear el total general sin decimales
    document.getElementById('montoTotal').textContent = total.toLocaleString('es-CL', {minimumFractionDigits: 0, maximumFractionDigits: 0});
}

// Función para limpiar ofrenda (poner en $0)
function limpiarOfrenda() {
    Swal.fire({
        title: '¿Limpiar ofrenda?',
        text: 'Esto pondrá todos los valores en $0',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, limpiar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Object.keys(montos).forEach(monto => {
                document.getElementById(montos[monto].id).value = 0;
            });
            calcularTotal();
        }
    });
}

// Función para guardar ofrenda
function guardarOfrenda() {
    const formData = new FormData(document.getElementById('formOfrenda'));
    const data = {
        action: 'guardar_ofrenda',
        id: formData.get('id'),
        id_culto: formData.get('id_culto'),
        fecha_ofrenda: formData.get('fecha'),
        cantidad_20000: parseInt(document.getElementById('cantidad_20000').value) || 0,
        cantidad_10000: parseInt(document.getElementById('cantidad_10000').value) || 0,
        cantidad_5000: parseInt(document.getElementById('cantidad_5000').value) || 0,
        cantidad_2000: parseInt(document.getElementById('cantidad_2000').value) || 0,
        cantidad_1000: parseInt(document.getElementById('cantidad_1000').value) || 0,
        cantidad_500: parseInt(document.getElementById('cantidad_500').value) || 0,
        cantidad_100: parseInt(document.getElementById('cantidad_100').value) || 0,
        cantidad_50: parseInt(document.getElementById('cantidad_50').value) || 0,
        cantidad_10: parseInt(document.getElementById('cantidad_10').value) || 0
    };
    
    fetch('ofrendas_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor: ' + response.status);
        }
        return response.json();
    })
    .then(result => {
        if (result.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalOfrenda'));
            if (modal) {
                modal.hide();
            }
            
            // Esperar a que el modal se cierre completamente antes de recargar
            setTimeout(() => {
                cargarOfrendas();
            }, 300);
            
            Swal.fire('Éxito', result.message || 'Ofrenda guardada exitosamente', 'success');
        } else {
            console.error('Error del servidor:', result);
            Swal.fire('Error', result.message || 'Error al guardar la ofrenda', 'error');
        }
    })
    .catch(error => {
        console.error('Error al guardar ofrenda:', error);
        Swal.fire('Error', 'Error al guardar la ofrenda: ' + error.message, 'error');
    });
}

// Cargar ofrendas al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarOfrendas();
});
</script>

<?php include '../includes/footer.php'; ?>

