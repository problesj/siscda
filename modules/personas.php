<?php include '../includes/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Gestión de Personas</h1>
    <button class="btn btn-primary" onclick="nuevoPersona()">
        <i class="fas fa-plus"></i> Nueva Persona
    </button>
</div>

<?php
// Variables para SweetAlert2
$successMessage = '';
$errorMessage = '';

if (isset($_SESSION['success'])) {
    $successMessage = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $errorMessage = $_SESSION['error'];
    unset($_SESSION['error']);
}

try {
    $pdo = conectarDB();
    
    // Obtener todas las personas para el filtrado en tiempo real
    $sql = "SELECT p.*, gf.NOMBRE as GRUPO_FAMILIAR_NOMBRE, r.nombre_rol as ROL_NOMBRE 
            FROM personas p 
            LEFT JOIN grupos_familiares gf ON p.GRUPO_FAMILIAR_ID = gf.ID 
            LEFT JOIN roles r ON p.ROL = r.id 
            ORDER BY p.FAMILIA, p.APELLIDO_PATERNO, p.NOMBRES";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $personas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convertir a JSON para JavaScript
    $personas_json = json_encode($personas);
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error al cargar las personas: ' . htmlspecialchars($e->getMessage()) . '</div>';
    $personas = [];
    $personas_json = '[]';
}
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Listado de Personas</h6>
    </div>
    <div class="card-body">
        <!-- Campo de búsqueda -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" class="form-control" id="searchInput" placeholder="Buscar personas..." oninput="filtrarPersonas()">
                </div>
            </div>
            <div class="col-md-6 text-end">
                <div class="d-flex align-items-center justify-content-end">
                    <label for="itemsPorPagina" class="me-2">Items:</label>
                    <select class="form-select form-select-sm me-2" id="itemsPorPagina" onchange="cambiarItemsPorPagina()" style="width: auto;">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span id="infoRegistros" class="text-muted"></span>
                </div>
            </div>
        </div>
        
        <!-- Botones de ordenamiento -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-md-end">
                    <div class="btn-group-vertical btn-group-sm d-md-none mb-2" role="group">
                        <button type="button" class="btn btn-outline-primary" onclick="cambiarOrden('FAMILIA')">
                            <i class="fas fa-sort"></i> Familia
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="cambiarOrden('GRUPO_FAMILIAR')">
                            <i class="fas fa-sort"></i> Grupo Familiar
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="cambiarOrden('APELLIDO_PATERNO')">
                            <i class="fas fa-sort"></i> Apellido
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="cambiarOrden('NOMBRES')">
                            <i class="fas fa-sort"></i> Nombres
                        </button>
                    </div>
                    <div class="btn-group d-none d-md-flex" role="group">
                        <button type="button" class="btn btn-outline-primary" onclick="cambiarOrden('FAMILIA')">
                            <i class="fas fa-sort"></i> Familia
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="cambiarOrden('GRUPO_FAMILIAR')">
                            <i class="fas fa-sort"></i> Grupo Familiar
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="cambiarOrden('APELLIDO_PATERNO')">
                            <i class="fas fa-sort"></i> Apellido
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="cambiarOrden('NOMBRES')">
                            <i class="fas fa-sort"></i> Nombres
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Indicador de estado de búsqueda -->
        <div id="estadoBusqueda" class="alert alert-info" style="display: none;">
            <i class="fas fa-search"></i> Búsqueda en tiempo real activa
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered" id="tablaPersonas" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Imagen</th>
                        <th>RUT</th>
                        <th>Nombres</th>
                        <th>Apellido Paterno</th>
                        <th>Apellido Materno</th>
                        <th>Familia</th>
                        <th>Rol</th>
                        <th>Grupo Familiar</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Se llenará dinámicamente con JavaScript -->
                </tbody>
            </table>
        </div>
        
        <!-- Paginación del lado del cliente -->
        <div class="row mt-3">
            <div class="col-12">
                <nav aria-label="Navegación de páginas">
                    <ul class="pagination justify-content-center mb-0" id="paginacion">
                        <!-- Se generará dinámicamente con JavaScript -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Persona -->
<div class="modal fade" id="modalPersona" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nueva Persona</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="personas_actions.php" method="POST" id="formPersona" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="crear">
                    <input type="hidden" name="persona_id" id="persona_id" value="">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="rut" class="form-label">RUT</label>
                                <input type="text" class="form-control" id="rut" name="rut" placeholder="12345678-9">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nombres" class="form-label">Nombres *</label>
                                <input type="text" class="form-control" id="nombres" name="nombres" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="apellido_paterno" class="form-label">Apellido Paterno *</label>
                                <input type="text" class="form-control" id="apellido_paterno" name="apellido_paterno" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="apellido_materno" class="form-label">Apellido Materno</label>
                                <input type="text" class="form-control" id="apellido_materno" name="apellido_materno">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="sexo" class="form-label">Sexo</label>
                                <select class="form-select" id="sexo" name="sexo">
                                    <option value="">Seleccionar</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="familia" class="form-label">Familia</label>
                                <input type="text" class="form-control" id="familia" name="familia" placeholder="Nombre de la familia">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="rol" class="form-label">Rol</label>
                                <select class="form-select" id="rol" name="rol">
                                    <option value="">Seleccionar rol</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="grupo_familiar_id" class="form-label">Grupo Familiar</label>
                                <select class="form-select" id="grupo_familiar_id" name="grupo_familiar_id">
                                    <option value="">Seleccionar grupo familiar</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="imagen" class="form-label">Imagen</label>
                                <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*" onchange="mostrarVistaPrevia(this)">
                                <div class="form-text">Formatos: JPG, PNG. Máximo: 500KB</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                            </div>
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

<!-- Modal para Ver Datos de Persona -->
<div class="modal fade" id="modalVerPersona" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user me-2"></i>Datos de la Persona
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="datosPersona">
                    <!-- Los datos se cargarán dinámicamente -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cerrar
                </button>
                <button type="button" class="btn btn-primary" id="btnEditarPersona" onclick="editarPersonaDesdeVer()">
                    <i class="fas fa-edit"></i> Editar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Variables globales para el sistema de búsqueda y paginación
let datosPersonas = [];
let datosFiltrados = [];
let paginaActual = 1;
let itemsPorPagina = 25;
let ordenActual = 'ORIGINAL';
let direccionOrden = 'asc';

// Función para mostrar vista previa de imagen
function mostrarVistaPrevia(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Aquí puedes mostrar la vista previa si tienes un elemento para ello
            console.log('Vista previa de imagen cargada');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Función para filtrar personas en tiempo real
function filtrarPersonas() {
    const busqueda = document.getElementById('searchInput').value.toLowerCase().trim();
    const estadoBusqueda = document.getElementById('estadoBusqueda');
    
    // Mostrar indicador de búsqueda (con verificación de seguridad)
    if (estadoBusqueda) {
        if (busqueda !== '') {
            estadoBusqueda.style.display = 'block';
        } else {
            estadoBusqueda.style.display = 'none';
        }
    }
    
    if (busqueda === '') {
        // Si no hay búsqueda, mostrar todas las personas
        datosFiltrados = [...datosPersonas];
    } else {
        // Filtrar por nombre, apellido, RUT, familia o grupo familiar
        datosFiltrados = datosPersonas.filter(persona => {
            const nombres = (persona.NOMBRES || '').toLowerCase();
            const apellidoPaterno = (persona.APELLIDO_PATERNO || '').toLowerCase();
            const apellidoMaterno = (persona.APELLIDO_MATERNO || '').toLowerCase();
            const rut = (persona.RUT || '').toLowerCase();
            const familia = (persona.FAMILIA || '').toLowerCase();
            const grupoFamiliar = (persona.GRUPO_FAMILIAR_NOMBRE || '').toLowerCase();
            
            return nombres.includes(busqueda) ||
                   apellidoPaterno.includes(busqueda) ||
                   apellidoMaterno.includes(busqueda) ||
                   rut.includes(busqueda) ||
                   familia.includes(busqueda) ||
                   grupoFamiliar.includes(busqueda);
        });
    }
    
    // Reiniciar a la primera página
    paginaActual = 1;
    
    // Aplicar ordenamiento y mostrar resultados
    aplicarOrdenamientoYFiltrado();
    
    // Mostrar información de resultados
    const totalResultados = datosFiltrados.length;
    const info = document.getElementById('infoRegistros');
    if (info) {
        if (busqueda === '') {
            info.textContent = `Mostrando todas las personas (${totalResultados} total)`;
        } else {
            info.textContent = `Búsqueda: "${busqueda}" - ${totalResultados} resultado(s) encontrado(s)`;
        }
    }
}

// Función para cambiar el número de items por página
function cambiarItemsPorPagina() {
    itemsPorPagina = parseInt(document.getElementById('itemsPorPagina').value);
    paginaActual = 1;
    aplicarOrdenamientoYFiltrado();
}

// Función para aplicar ordenamiento y filtrado
function aplicarOrdenamientoYFiltrado() {
    if (!datosPersonas || datosPersonas.length === 0) {
        console.log('No hay datos de personas para ordenar/filtrar');
        return;
    }
    
    let datosOrdenados;
    
    // Si no hay ordenamiento específico, mantener el orden original
    if (ordenActual === 'ORIGINAL') {
        datosOrdenados = [...datosFiltrados];
    } else {
        // Aplicar ordenamiento personalizado
        datosOrdenados = [...datosFiltrados].sort((a, b) => {
            let valorA, valorB;
            
            switch (ordenActual) {
                case 'FAMILIA':
                    valorA = a.FAMILIA || '';
                    valorB = b.FAMILIA || '';
                    break;
                case 'GRUPO_FAMILIAR':
                    valorA = a.GRUPO_FAMILIAR_NOMBRE || '';
                    valorB = b.GRUPO_FAMILIAR_NOMBRE || '';
                    break;
                case 'APELLIDO_PATERNO':
                    valorA = a.APELLIDO_PATERNO || '';
                    valorB = b.APELLIDO_PATERNO || '';
                    break;
                case 'NOMBRES':
                    valorA = a.NOMBRES || '';
                    valorB = b.NOMBRES || '';
                    break;
                default:
                    valorA = a.FAMILIA || '';
                    valorB = b.FAMILIA || '';
            }
            
            if (direccionOrden === 'asc') {
                return valorA.localeCompare(valorB);
            } else {
                return valorB.localeCompare(valorA);
            }
        });
    }
    
    // Mostrar la página actual
    mostrarPagina(datosOrdenados, paginaActual);
    
    // Generar paginación
    generarPaginacion(datosOrdenados.length, paginaActual);
}

// Función para mostrar una página específica
function mostrarPagina(datos, pagina) {
    const tbody = document.querySelector('#tablaPersonas tbody');
    const inicio = (pagina - 1) * itemsPorPagina;
    const fin = inicio + itemsPorPagina;
    const datosPagina = datos.slice(inicio, fin);
    
    let html = '';
    
    if (datosPagina.length === 0) {
        html = '<tr><td colspan="10" class="text-center text-muted">No se encontraron personas</td></tr>';
    } else {
        datosPagina.forEach(persona => {
            // Determinar imagen por defecto
            let imagenSrc = '../assets/images/personas/default_male.svg';
            if (persona.URL_IMAGEN) {
                imagenSrc = '../' + persona.URL_IMAGEN;
            } else if (persona.SEXO === 'F') {
                imagenSrc = '../assets/images/personas/default_female.svg';
            }
            
            html += `
                <tr>
                    <td>${persona.ID}</td>
                    <td><img src="${imagenSrc}" alt="Foto de ${persona.NOMBRES}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;" onerror="this.src='../assets/images/personas/default_male.svg'"></td>
                    <td>${persona.RUT || '-'}</td>
                    <td>${persona.NOMBRES}</td>
                    <td>${persona.APELLIDO_PATERNO}</td>
                    <td>${persona.APELLIDO_MATERNO || '-'}</td>
                    <td>${persona.FAMILIA || '-'}</td>
                    <td>${persona.ROL_NOMBRE || '-'}</td>
                    <td>${persona.GRUPO_FAMILIAR_NOMBRE || 'Sin grupo'}</td>
                    <td>
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-primary" onclick="verPersona(${persona.ID})" title="Ver datos">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-info" onclick="editarPersona(${persona.ID})" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="eliminarPersona(${persona.ID})" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
    }
    
    tbody.innerHTML = html;
    
    // Actualizar información de registros
    const total = datos.length;
    const inicioMostrado = total > 0 ? inicio + 1 : 0;
    const finMostrado = Math.min(fin, total);
    const info = document.getElementById('infoRegistros');
    if (info) {
        info.textContent = `Mostrando ${inicioMostrado}-${finMostrado} de ${total} registros`;
    }
}

// Función para generar la paginación
function generarPaginacion(totalItems, paginaActual) {
    const totalPaginas = Math.ceil(totalItems / itemsPorPagina);
    const paginacion = document.getElementById('paginacion');
    
    if (totalPaginas <= 1) {
        paginacion.innerHTML = '';
        return;
    }
    
    let html = '';
    
    // Botón anterior
    if (paginaActual > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="mostrarPagina(datosFiltrados, ${paginaActual - 1})">‹</a></li>`;
    } else {
        html += `<li class="page-item disabled"><span class="page-link">‹</span></li>`;
    }
    
    // Números de página
    const esMobile = window.innerWidth <= 767.98;
    let paginasAMostrar = [];
    
    if (esMobile) {
        // En móviles, mostrar más páginas para ocupar toda la fila
        if (totalPaginas <= 5) {
            for (let i = 1; i <= totalPaginas; i++) {
                paginasAMostrar.push(i);
            }
        } else {
            const inicio = Math.max(1, paginaActual - 2);
            const fin = Math.min(totalPaginas, paginaActual + 2);
            
            if (inicio > 1) {
                paginasAMostrar.push(1);
                if (inicio > 2) paginasAMostrar.push('...');
            }
            
            for (let i = inicio; i <= fin; i++) {
                paginasAMostrar.push(i);
            }
            
            if (fin < totalPaginas) {
                if (fin < totalPaginas - 1) paginasAMostrar.push('...');
                paginasAMostrar.push(totalPaginas);
            }
        }
    } else {
        // En desktop, mostrar más páginas
        for (let i = 1; i <= totalPaginas; i++) {
            if (i === 1 || i === totalPaginas || (i >= paginaActual - 2 && i <= paginaActual + 2)) {
                paginasAMostrar.push(i);
            } else if (i === paginaActual - 3 || i === paginaActual + 3) {
                paginasAMostrar.push('...');
            }
        }
    }
    
    // Generar HTML para las páginas
    paginasAMostrar.forEach(item => {
        if (item === '...') {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        } else if (item === paginaActual) {
            html += `<li class="page-item active"><span class="page-link">${item}</span></li>`;
        } else {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="mostrarPagina(datosFiltrados, ${item})">${item}</a></li>`;
        }
    });
    
    // Botón siguiente
    if (paginaActual < totalPaginas) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="mostrarPagina(datosFiltrados, ${paginaActual + 1})">›</a></li>`;
    } else {
        html += `<li class="page-item disabled"><span class="page-link">›</span></li>`;
    }
    
    paginacion.innerHTML = html;
}

// Función auxiliar para verificar si existe un archivo (simulada)
function fileExists(path) {
    // En un entorno real, esto se haría con una petición AJAX
    // Por ahora, asumimos que existe si tiene extensión
    return path.includes('.') && !path.includes('default_');
}

// Función para nueva persona
function nuevoPersona() {
    document.getElementById('modalTitle').textContent = 'Nueva Persona';
    document.getElementById('formAction').value = 'crear';
    document.getElementById('persona_id').value = '';
    document.getElementById('formPersona').reset();
    
    // Cargar roles y grupos familiares para el formulario
    fetch('personas_actions.php?action=obtener&id=0')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                llenarSelectores(data.roles, data.gruposFamiliares);
            }
        })
        .catch(error => {
            console.error('Error al cargar roles y grupos familiares:', error);
        });
    
    const modal = new bootstrap.Modal(document.getElementById('modalPersona'));
    modal.show();
}

// Función para editar persona
function editarPersona(id) {
    // Cambiar el modal a modo edición
    document.getElementById('modalTitle').textContent = 'Editar Persona';
    document.getElementById('formAction').value = 'editar';
    document.getElementById('persona_id').value = id;
    
    // Cargar datos de la persona
    fetch('personas_actions.php?action=obtener&id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Llenar el formulario con los datos
                document.getElementById('rut').value = data.persona.RUT || '';
                document.getElementById('nombres').value = data.persona.NOMBRES || '';
                document.getElementById('apellido_paterno').value = data.persona.APELLIDO_PATERNO || '';
                document.getElementById('apellido_materno').value = data.persona.APELLIDO_MATERNO || '';
                document.getElementById('sexo').value = data.persona.SEXO || '';
                document.getElementById('fecha_nacimiento').value = data.persona.FECHA_NACIMIENTO || '';
                document.getElementById('familia').value = data.persona.FAMILIA || '';
                document.getElementById('rol').value = data.persona.ROL || '';
                document.getElementById('email').value = data.persona.EMAIL || '';
                document.getElementById('telefono').value = data.persona.TELEFONO || '';
                document.getElementById('grupo_familiar_id').value = data.persona.GRUPO_FAMILIAR_ID || '';
                document.getElementById('observaciones').value = data.persona.OBSERVACIONES || '';
                
                // Llenar los selectores de rol y grupo familiar
                llenarSelectores(data.roles, data.gruposFamiliares);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al cargar datos de la persona: ' + (data.error || 'Error desconocido'),
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#dc3545'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al cargar datos de la persona',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#dc3545'
            });
        });
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalPersona'));
    modal.show();
}

// Función para ver persona
function verPersona(id) {
    // Mostrar loading
    document.getElementById('datosPersona').innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2">Cargando información de la persona...</p>
        </div>
    `;
    
    // Obtener datos completos de la persona
    fetch('personas_actions.php?action=obtener&id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarDatosPersona(data.persona, id);
            } else {
                document.getElementById('datosPersona').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error: ${data.error || 'No se pudo cargar la información de la persona'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('datosPersona').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error de conexión. Intenta nuevamente.
                </div>
            `;
        });
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalVerPersona'));
    modal.show();
}

// Función para eliminar persona
function eliminarPersona(id) {
    Swal.fire({
        icon: 'warning',
        title: '¿Está seguro?',
        text: '¿Realmente desea eliminar esta persona? Esta acción no se puede deshacer.',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'personas_actions.php?action=eliminar&id=' + id;
        }
    });
}

// Función para mostrar datos de la persona en el modal de vista
function mostrarDatosPersona(persona, personaId) {
    // Generar el HTML con los datos completos de la persona
    const html = `
        <div class="row">
            <div class="col-md-4">
                <div class="text-center mb-3">
                    <img src="${persona.URL_IMAGEN ? '../' + persona.URL_IMAGEN : '../assets/images/personas/default_male.svg'}" 
                         alt="Foto de ${persona.NOMBRES}" 
                         class="img-thumbnail" 
                         style="width: 150px; height: 150px; object-fit: cover;"
                         onerror="this.src='../assets/images/personas/default_male.svg'">
                    <h5 class="mt-2">${persona.NOMBRES} ${persona.APELLIDO_PATERNO}</h5>
                    <small class="text-muted">ID: ${persona.ID}</small>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h6 class="card-title text-primary">
                            <i class="fas fa-id-card me-2"></i>Información Personal
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <strong>RUT:</strong> ${persona.RUT || 'No especificado'}
                                </div>
                                <div class="mb-2">
                                    <strong>Nombres:</strong> ${persona.NOMBRES}
                                </div>
                                <div class="mb-2">
                                    <strong>Apellido Paterno:</strong> ${persona.APELLIDO_PATERNO}
                                </div>
                                <div class="mb-2">
                                    <strong>Apellido Materno:</strong> ${persona.APELLIDO_MATERNO || 'No especificado'}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <strong>Sexo:</strong> ${persona.SEXO || 'No especificado'}
                                </div>
                                <div class="mb-2">
                                    <strong>Fecha de Nacimiento:</strong> ${persona.FECHA_NACIMIENTO ? new Date(persona.FECHA_NACIMIENTO).toLocaleDateString('es-ES') : 'No especificada'}
                                </div>
                                <div class="mb-2">
                                    <strong>Email:</strong> ${persona.EMAIL || 'No especificado'}
                                </div>
                                <div class="mb-2">
                                    <strong>Teléfono:</strong> ${persona.TELEFONO || 'No especificado'}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h6 class="card-title text-primary">
                            <i class="fas fa-users me-2"></i>Información Familiar
                        </h6>
                        <div class="mb-2">
                            <strong>Familia:</strong> ${persona.FAMILIA || 'No especificada'}
                        </div>
                        <div class="mb-2">
                            <strong>Grupo Familiar:</strong> ${persona.GRUPO_FAMILIAR_NOMBRE || 'No asignado'}
                        </div>
                        <div class="mb-2">
                            <strong>Rol:</strong> ${persona.ROL_NOMBRE || 'No asignado'}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h6 class="card-title text-primary">
                            <i class="fas fa-calendar me-2"></i>Información del Sistema
                        </h6>
                        <div class="mb-2">
                            <strong>Fecha de Creación:</strong> ${persona.FECHA_CREACION ? new Date(persona.FECHA_CREACION).toLocaleDateString('es-ES') + ' ' + new Date(persona.FECHA_CREACION).toLocaleTimeString('es-ES') : 'No disponible'}
                        </div>
                        <div class="mb-2">
                            <strong>Última Actualización:</strong> ${persona.FECHA_ACTUALIZACION ? new Date(persona.FECHA_ACTUALIZACION).toLocaleDateString('es-ES') + ' ' + new Date(persona.FECHA_ACTUALIZACION).toLocaleTimeString('es-ES') : 'No disponible'}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        ${persona.OBSERVACIONES ? `
        <div class="row mt-3">
            <div class="col-12">
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h6 class="card-title text-primary">
                            <i class="fas fa-sticky-note me-2"></i>Observaciones
                        </h6>
                        <p class="mb-0">${persona.OBSERVACIONES}</p>
                    </div>
                </div>
            </div>
        </div>
        ` : ''}
    `;
    
    // Mostrar los datos en el modal
    document.getElementById('datosPersona').innerHTML = html;
    
    // Guardar el ID de la persona para poder editarla
    document.getElementById('btnEditarPersona').setAttribute('data-persona-id', personaId);
}

// Función para editar persona desde el modal de ver
function editarPersonaDesdeVer() {
    const personaId = document.getElementById('btnEditarPersona').getAttribute('data-persona-id');
    
    // Cerrar el modal de ver
    const modalVer = bootstrap.Modal.getInstance(document.getElementById('modalVerPersona'));
    modalVer.hide();
    
    // Abrir el modal de edición
    if (personaId) {
        editarPersona(personaId);
    }
}

// Función para llenar los selectores de rol y grupo familiar
function llenarSelectores(roles, gruposFamiliares) {
    // Llenar selector de roles
    const rolSelect = document.getElementById('rol');
    rolSelect.innerHTML = '<option value="">Seleccionar rol</option>';
    if (roles) {
        roles.forEach(rol => {
            const option = document.createElement('option');
            option.value = rol.id;
            option.textContent = rol.nombre_rol;
            rolSelect.appendChild(option);
        });
    }
    
    // Llenar selector de grupos familiares
    const grupoSelect = document.getElementById('grupo_familiar_id');
    grupoSelect.innerHTML = '<option value="">Seleccionar grupo familiar</option>';
    if (gruposFamiliares) {
        gruposFamiliares.forEach(grupo => {
            const option = document.createElement('option');
            option.value = grupo.ID;
            option.textContent = grupo.NOMBRE;
            grupoSelect.appendChild(option);
        });
    }
}

// Función para cargar datos iniciales
function cargarDatosIniciales() {
    try {
        // Obtener datos del PHP
        const personasData = <?php echo $personas_json; ?>;
        datosPersonas = personasData;
        datosFiltrados = [...datosPersonas];
        
        // Mostrar primera página
        mostrarPagina(datosFiltrados, 1);
        
        // Generar paginación
        generarPaginacion(datosFiltrados.length, 1);
        
        console.log('✅ Datos de personas cargados:', datosPersonas.length);
    } catch (error) {
        console.error('❌ Error al cargar datos iniciales:', error);
    }
}

// Evento principal cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Módulo de personas inicializado');
    
    // Cargar datos iniciales
    cargarDatosIniciales();
    
    // Agregar evento de redimensionamiento para regenerar paginación
    window.addEventListener('resize', function() {
        if (datosFiltrados && datosFiltrados.length > 0) {
            generarPaginacion(datosFiltrados.length, paginaActual);
        }
    });
    
    // Limpiar modal al cerrar
    const modalPersona = document.getElementById('modalPersona');
    if (modalPersona) {
        modalPersona.addEventListener('hidden.bs.modal', function () {
            document.getElementById('formPersona').reset();
        });
    }
});

// Mostrar alertas de sesión con SweetAlert2
<?php if ($successMessage): ?>
Swal.fire({
    icon: 'success',
    title: '¡Éxito!',
    text: '<?php echo addslashes($successMessage); ?>',
    confirmButtonText: 'Entendido',
    confirmButtonColor: '#28a745'
});
<?php endif; ?>

<?php if ($errorMessage): ?>
Swal.fire({
    icon: 'error',
    title: 'Error',
    text: '<?php echo addslashes($errorMessage); ?>',
    confirmButtonText: 'Entendido',
    confirmButtonColor: '#dc3545'
});
<?php endif; ?>
</script>

<?php include '../includes/footer.php'; ?>
