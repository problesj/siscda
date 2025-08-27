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
                    <input type="text" class="form-control" id="searchInput" placeholder="Buscar personas por nombre, apellido, RUT o familia..." value="<?php echo htmlspecialchars($buscar); ?>">
                    <?php if (!empty($buscar)): ?>
                    <button class="btn btn-outline-secondary" type="button" onclick="limpiarBusqueda()" title="Limpiar búsqueda">
                        <i class="fas fa-times"></i>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
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
                    <?php
                    try {
                        $pdo = conectarDB();
                        
                        // Configuración de paginación y búsqueda
                        $registros_por_pagina = isset($_GET['items']) ? (int)$_GET['items'] : 25;
                        $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
                        $buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
                        
                        // Construir consulta base
                        $sql_base = "FROM personas p 
                                    LEFT JOIN grupos_familiares gf ON p.GRUPO_FAMILIAR_ID = gf.ID 
                                    LEFT JOIN roles r ON p.ROL = r.id";
                        
                        $where_conditions = [];
                        $params = [];
                        
                        // Agregar condiciones de búsqueda si existe término
                        if (!empty($buscar)) {
                            $where_conditions[] = "(p.NOMBRES LIKE ? OR p.APELLIDO_PATERNO LIKE ? OR p.APELLIDO_MATERNO LIKE ? OR p.RUT LIKE ? OR p.FAMILIA LIKE ?)";
                            $search_term = '%' . $buscar . '%';
                            $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term, $search_term]);
                        }
                        
                        $sql_where = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
                        
                        // Obtener total de registros
                        $sql_total = "SELECT COUNT(*) as total " . $sql_base . " " . $sql_where;
                        $stmt_total = $pdo->prepare($sql_total);
                        $stmt_total->execute($params);
                        $total_registros = $stmt_total->fetch()['total'];
                        $total_paginas = ceil($total_registros / $registros_por_pagina);
                        
                        // Ajustar página actual si es mayor al total
                        if ($pagina_actual > $total_paginas && $total_paginas > 0) {
                            $pagina_actual = $total_paginas;
                        }
                        
                        $offset = ($pagina_actual - 1) * $registros_por_pagina;
                        
                        // Consulta principal con paginación y búsqueda
                        $sql = "SELECT p.*, gf.NOMBRE as grupo_familiar, r.nombre_rol as rol_nombre " . $sql_base . " " . $sql_where . " ORDER BY p.ID LIMIT ? OFFSET ?";
                        $stmt = $pdo->prepare($sql);
                        $params[] = $registros_por_pagina;
                        $params[] = $offset;
                        $stmt->execute($params);
                        
                        while ($row = $stmt->fetch()) {
                            // Determinar imagen por defecto según el sexo
                            $imagenDefault = '';
                            if ($row['URL_IMAGEN']) {
                                $imagenDefault = $row['URL_IMAGEN'];
                            } else {
                                $imagenDefault = $row['SEXO'] === 'Femenino' ? 
                                    '../assets/images/personas/default_female.svg' : 
                                    '../assets/images/personas/default_male.svg';
                            }
                            
                            echo "<tr>";
                            echo "<td>" . $row['ID'] . "</td>";
                            echo "<td><img src='" . htmlspecialchars($imagenDefault) . "' alt='Foto de " . htmlspecialchars($row['NOMBRES']) . "' class='img-thumbnail' style='width: 50px; height: 50px; object-fit: cover;' onerror=\"this.src='../assets/images/personas/default_male.svg'\"></td>";
                            echo "<td>" . ($row['RUT'] ?? '-') . "</td>";
                            echo "<td>" . $row['NOMBRES'] . "</td>";
                            echo "<td>" . $row['APELLIDO_PATERNO'] . "</td>";
                            echo "<td>" . ($row['APELLIDO_MATERNO'] ?? '-') . "</td>";
                            echo "<td>" . ($row['FAMILIA'] ?? '-') . "</td>";
                            echo "<td>" . ($row['rol_nombre'] ?? '-') . "</td>";
                            echo "<td>" . ($row['grupo_familiar'] ?? 'Sin grupo') . "</td>";
                            echo "<td>
                                    <div class='btn-group' role='group'>
                                        <button class='btn btn-sm btn-primary' onclick='verPersona(" . $row['ID'] . ")' title='Ver datos'>
                                            <i class='fas fa-eye'></i>
                                        </button>
                                        <button class='btn btn-sm btn-info' onclick='editarPersona(" . $row['ID'] . ")' title='Editar'>
                                            <i class='fas fa-edit'></i>
                                        </button>
                                        <button class='btn btn-sm btn-danger' onclick='eliminarPersona(" . $row['ID'] . ")' title='Eliminar'>
                                            <i class='fas fa-trash'></i>
                                        </button>
                                    </div>
                                  </td>";
                            echo "</tr>";
                        }
                    } catch (PDOException $e) {
                        echo "<tr><td colspan='11'>Error al cargar personas: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            
            <!-- Controles de paginación y búsqueda -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <label class="me-2">Mostrar:</label>
                        <select class="form-select form-select-sm me-2" id="itemsPorPagina" style="width: auto;">
                            <option value="10" <?php echo $registros_por_pagina == 10 ? 'selected' : ''; ?>>10</option>
                            <option value="25" <?php echo $registros_por_pagina == 25 ? 'selected' : ''; ?>>25</option>
                            <option value="50" <?php echo $registros_por_pagina == 50 ? 'selected' : ''; ?>>50</option>
                            <option value="100" <?php echo $registros_por_pagina == 100 ? 'selected' : ''; ?>>100</option>
                        </select>
                        <span class="text-muted">registros por página</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <small class="text-muted" id="infoRegistros">
                        Mostrando <?php echo $offset + 1; ?>-<?php echo min($offset + $registros_por_pagina, $total_registros); ?> de <?php echo $total_registros; ?> registros
                    </small>
                </div>
            </div>
            
            <!-- Paginación -->
            <?php if ($total_paginas > 1): ?>
            <div class="row mt-2">
                <div class="col-12">
                    <nav aria-label="Navegación de páginas">
                        <ul class="pagination pagination-sm justify-content-center mb-0">
                            <!-- Botón Anterior -->
                            <?php if ($pagina_actual > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?pagina=<?php echo $pagina_actual - 1; ?>&items=<?php echo $registros_por_pagina; ?><?php echo !empty($buscar) ? '&buscar=' . urlencode($buscar) : ''; ?>" aria-label="Anterior">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <!-- Números de página -->
                            <?php
                            $inicio = max(1, $pagina_actual - 2);
                            $fin = min($total_paginas, $pagina_actual + 2);
                            
                            if ($inicio > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?pagina=1&items=<?php echo $registros_por_pagina; ?><?php echo !empty($buscar) ? '&buscar=' . urlencode($buscar) : ''; ?>">1</a>
                            </li>
                            <?php if ($inicio > 2): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                            <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                            <li class="page-item <?php echo $i == $pagina_actual ? 'active' : ''; ?>">
                                <a class="page-link" href="?pagina=<?php echo $i; ?>&items=<?php echo $registros_por_pagina; ?><?php echo !empty($buscar) ? '&buscar=' . urlencode($buscar) : ''; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($fin < $total_paginas): ?>
                            <?php if ($fin < $total_paginas - 1): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?pagina=<?php echo $total_paginas; ?>&items=<?php echo $registros_por_pagina; ?><?php echo !empty($buscar) ? '&buscar=' . urlencode($buscar) : ''; ?>"><?php echo $total_paginas; ?></a>
                            </li>
                            <?php endif; ?>
                            
                            <!-- Botón Siguiente -->
                            <?php if ($pagina_actual < $total_paginas): ?>
                            <li class="page-item">
                                <a class="page-link" href="?pagina=<?php echo $pagina_actual + 1; ?>&items=<?php echo $registros_por_pagina; ?><?php echo !empty($buscar) ? '&buscar=' . urlencode($buscar) : ''; ?>" aria-label="Siguiente">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
            <?php endif; ?>
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
                                <label for="rol" class="form-label">Rol</label>
                                <select class="form-select" id="rol" name="rol">
                                    <option value="">Seleccionar rol</option>
                                    <?php
                                    try {
                                        $stmt = $pdo->query("SELECT * FROM roles ORDER BY nombre_rol");
                                        while ($rol = $stmt->fetch()) {
                                            echo "<option value='" . $rol['id'] . "'>" . $rol['nombre_rol'] . "</option>";
                                        }
                                    } catch (PDOException $e) {
                                        echo "<option value=''>Error al cargar roles</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="grupo_familiar_id" class="form-label">Grupo Familiar</label>
                                <select class="form-select" id="grupo_familiar_id" name="grupo_familiar_id">
                                    <option value="">Seleccionar grupo familiar</option>
                                    <?php
                                    try {
                                        $stmt = $pdo->query("SELECT * FROM grupos_familiares ORDER BY NOMBRE");
                                        while ($grupo = $stmt->fetch()) {
                                            echo "<option value='" . $grupo['ID'] . "'>" . $grupo['NOMBRE'] . "</option>";
                                        }
                                    } catch (PDOException $e) {
                                        echo "<option value=''>Error al cargar grupos</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="imagen" class="form-label">Imagen de la Persona</label>
                                <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*">
                                <div class="form-text">Formatos permitidos: JPG, PNG. Tamaño máximo: 500KB</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Vista Previa</label>
                                <div class="text-center">
                                    <img id="previewImagen" src="../assets/images/personas/default_male.svg" 
                                         alt="Vista previa" class="img-thumbnail" 
                                         style="width: 100px; height: 100px; object-fit: cover;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="3" placeholder="Información adicional..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmit">Guardar</button>
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
// Función para mostrar vista previa de imagen
function mostrarVistaPrevia(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImagen').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Evento para mostrar vista previa de imagen
document.addEventListener('DOMContentLoaded', function() {
    const imagenInput = document.getElementById('imagen');
    if (imagenInput) {
        imagenInput.addEventListener('change', function() {
            mostrarVistaPrevia(this);
            validarImagen(this);
        });
    }
    
    // Agregar validación del formulario
    const formPersona = document.getElementById('formPersona');
    if (formPersona) {
        formPersona.addEventListener('submit', function(e) {
            if (!validarFormulario()) {
                e.preventDefault();
            }
        });
    }
    
    // Agregar evento para cambiar cantidad de registros por página
    const itemsPorPagina = document.getElementById('itemsPorPagina');
    if (itemsPorPagina) {
        itemsPorPagina.addEventListener('change', function() {
            cambiarItemsPorPagina(this.value);
        });
    }
});

// Función para validar imagen antes de enviar
function validarImagen(input) {
    const archivo = input.files[0];
    if (!archivo) return true;
    
    // Validar tipo de archivo
    const tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!tiposPermitidos.includes(archivo.type)) {
        Swal.fire({
            icon: 'error',
            title: 'Tipo de archivo no permitido',
            text: 'Solo se permiten archivos JPG y PNG. El archivo seleccionado es: ' + archivo.type,
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#dc3545'
        });
        input.value = '';
        document.getElementById('previewImagen').src = '../assets/images/personas/default_male.svg';
        return false;
    }
    
    // Validar extensión
    const extension = archivo.name.split('.').pop().toLowerCase();
    const extensionesPermitidas = ['jpg', 'jpeg', 'png'];
    if (!extensionesPermitidas.includes(extension)) {
        Swal.fire({
            icon: 'error',
            title: 'Extensión no permitida',
            text: 'Solo se permiten archivos con extensión: ' + extensionesPermitidas.join(', '),
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#dc3545'
        });
        input.value = '';
        document.getElementById('previewImagen').src = '../assets/images/personas/default_male.svg';
        return false;
    }
    
    // Validar tamaño (500KB máximo)
    const tamanioMaximo = 500 * 1024; // 500KB en bytes
    if (archivo.size > tamanioMaximo) {
        const tamanioMB = (archivo.size / (1024 * 1024)).toFixed(2);
        Swal.fire({
            icon: 'error',
            title: 'Archivo demasiado grande',
            text: 'El archivo excede el tamaño máximo de 500KB. Tamaño actual: ' + tamanioMB + 'MB',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#dc3545'
        });
        input.value = '';
        document.getElementById('previewImagen').src = '../assets/images/personas/default_male.svg';
        return false;
    }
    
    // Validar que no esté vacío
    if (archivo.size === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Archivo vacío',
            text: 'El archivo seleccionado está vacío. Selecciona una imagen válida.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#dc3545'
        });
        input.value = '';
        document.getElementById('previewImagen').src = '../assets/images/personas/default_male.svg';
        return false;
    }
    
    return true;
}

// Función para validar el formulario completo
function validarFormulario() {
    const nombres = document.getElementById('nombres').value.trim();
    const apellidoPaterno = document.getElementById('apellido_paterno').value.trim();
    const sexo = document.getElementById('sexo').value;
    
    if (!nombres) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'El campo "Nombres" es obligatorio.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#ffc107'
        });
        document.getElementById('nombres').focus();
        return false;
    }
    
    if (!apellidoPaterno) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'El campo "Apellido Paterno" es obligatorio.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#ffc107'
        });
        document.getElementById('apellido_paterno').focus();
        return false;
    }
    
    if (!sexo) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'El campo "Sexo" es obligatorio.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#ffc107'
        });
        document.getElementById('sexo').focus();
        return false;
    }
    
    // Validar imagen si se seleccionó una
    const imagenInput = document.getElementById('imagen');
    if (imagenInput.files.length > 0) {
        if (!validarImagen(imagenInput)) {
            return false;
        }
    }
    
    return true;
}

function editarPersona(id) {
    // Cambiar el modal a modo edición
    document.getElementById('modalTitle').textContent = 'Editar Persona';
    document.getElementById('formAction').value = 'editar';
    document.getElementById('persona_id').value = id;
    document.getElementById('btnSubmit').textContent = 'Actualizar';
    
    // Cargar datos de la persona
    fetch('personas_actions.php?action=obtener&id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
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
                
                // Mostrar imagen actual si existe
                if (data.persona.URL_IMAGEN) {
                    document.getElementById('previewImagen').src = data.persona.URL_IMAGEN;
                } else {
                    // Mostrar imagen por defecto según sexo
                    const imagenDefault = data.persona.SEXO === 'Femenino' ? 
                        '../assets/images/personas/default_female.svg' : 
                        '../assets/images/personas/default_male.svg';
                    document.getElementById('previewImagen').src = imagenDefault;
                }
            } else {
                SwalUtils.showError('Error al cargar datos de la persona: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            SwalUtils.showError('Error al cargar datos de la persona');
        });
    
    // Mostrar modal
    new bootstrap.Modal(document.getElementById('modalPersona')).show();
}

function nuevoPersona() {
    // Cambiar el modal a modo creación
    document.getElementById('modalTitle').textContent = 'Nueva Persona';
    document.getElementById('formAction').value = 'crear';
    document.getElementById('persona_id').value = '';
    document.getElementById('btnSubmit').textContent = 'Guardar';
    
    // Limpiar formulario
    document.getElementById('formPersona').reset();
    
    // Resetear imagen de vista previa
    document.getElementById('previewImagen').src = '../assets/images/personas/default_male.svg';
    
    // Mostrar modal
    new bootstrap.Modal(document.getElementById('modalPersona')).show();
}

function eliminarPersona(id) {
    // Verificar si SwalUtils está disponible
    if (typeof SwalUtils !== 'undefined' && typeof SwalUtils.showDeleteConfirm === 'function') {
        SwalUtils.showDeleteConfirm('esta persona').then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'personas_actions.php?action=eliminar&id=' + id;
            }
        });
    } else {
        // Fallback: usar SweetAlert2 directamente
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
}

function verPersona(personaId) {
    // Mostrar loading
    document.getElementById('datosPersona').innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2">Cargando información de la persona...</p>
        </div>
    `;
    
    // Obtener datos completos de la persona desde la base de datos
    fetch('personas_actions.php?action=obtener&id=' + personaId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const persona = data.persona;
                mostrarDatosPersona(persona);
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
}

function mostrarDatosPersona(persona) {
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
                                <strong>Grupo Familiar:</strong> ${persona.grupo_familiar || 'No asignado'}
                            </div>
                            <div class="mb-2">
                                <strong>Rol:</strong> ${persona.rol_nombre || 'No asignado'}
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
        
        // Mostrar el modal
        const modal = new bootstrap.Modal(document.getElementById('modalVerPersona'));
        modal.show();
    } else {
        // Si no se encuentra la persona, mostrar error
        SwalUtils.showError('No se pudo encontrar la información de la persona');
    }
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

// Limpiar modal al cerrar
document.addEventListener('DOMContentLoaded', function() {
    const modalPersona = document.getElementById('modalPersona');
    if (modalPersona) {
        modalPersona.addEventListener('hidden.bs.modal', function () {
            document.getElementById('formPersona').reset();
            document.getElementById('previewImagen').src = '../assets/images/personas/default_male.svg';
        });
    }
});

// Inicializar cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    console.log('Módulo de personas cargado correctamente');
    
    // Agregar evento de búsqueda en tiempo real
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.trim();
            if (searchTerm.length > 0) {
                // Si hay término de búsqueda, redirigir a la página de búsqueda
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('buscar', searchTerm);
                urlParams.set('pagina', '1'); // Volver a la primera página
                window.location.href = window.location.pathname + '?' + urlParams.toString();
            } else {
                // Si no hay término de búsqueda, limpiar la búsqueda
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.delete('buscar');
                urlParams.set('pagina', '1');
                window.location.href = window.location.pathname + '?' + urlParams.toString();
            }
        });
    }
    
    // Actualizar información de registros
    actualizarInfoRegistros();
});

// Función para cambiar cantidad de registros por página
function cambiarItemsPorPagina(items) {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('items', items);
    urlParams.set('pagina', '1'); // Volver a la primera página
    window.location.href = window.location.pathname + '?' + urlParams.toString();
}

// Función para limpiar búsqueda
function limpiarBusqueda() {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.delete('buscar');
    urlParams.set('pagina', '1');
    window.location.href = window.location.pathname + '?' + urlParams.toString();
}

// Función para filtrar la tabla
function filtrarTabla(searchTerm) {
    const filas = document.querySelectorAll('#tablaPersonas tbody tr');
    let registrosVisibles = 0;
    
    filas.forEach(fila => {
        const celdas = fila.querySelectorAll('td');
        let textoFila = '';
        
        // Concatenar texto de todas las celdas relevantes (excluyendo imagen y acciones)
        for (let i = 0; i < celdas.length - 2; i++) { // -2 para excluir imagen y acciones
            if (i !== 1) { // Excluir columna de imagen
                textoFila += celdas[i].textContent.toLowerCase() + ' ';
            }
        }
        
        if (textoFila.includes(searchTerm)) {
            fila.style.display = '';
            registrosVisibles++;
        } else {
            fila.style.display = 'none';
        }
    });
    
    // Actualizar información de registros filtrados
    document.getElementById('infoRegistros').textContent = `Mostrando ${registrosVisibles} de ${filas.length} registros (filtrados)`;
}

// Función para actualizar información de registros
function actualizarInfoRegistros() {
    const filas = document.querySelectorAll('#tablaPersonas tbody tr');
    const total = filas.length;
    document.getElementById('infoRegistros').textContent = `Mostrando 1-${total} de ${total} registros`;
}

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
