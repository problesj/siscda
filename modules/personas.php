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
                    <input type="text" class="form-control" id="searchInput" placeholder="Buscar personas por nombre, apellido, RUT o familia...">
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
                        $stmt = $pdo->query("SELECT p.*, gf.NOMBRE as grupo_familiar, r.nombre_rol as rol_nombre
                                           FROM personas p 
                                           LEFT JOIN grupos_familiares gf ON p.GRUPO_FAMILIAR_ID = gf.ID 
                                           LEFT JOIN roles r ON p.ROL = r.id
                                           ORDER BY p.ID");
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
            
            <!-- Paginación -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <label class="me-2">Mostrar:</label>
                        <select class="form-select form-select-sm me-2" id="itemsPorPagina" onchange="cambiarItemsPorPagina()" style="width: auto;">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-muted">registros por página</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <nav aria-label="Navegación de páginas">
                        <ul class="pagination justify-content-end mb-0" id="paginacion">
                            <!-- La paginación se generará dinámicamente -->
                        </ul>
                    </nav>
                </div>
            </div>
            
            <!-- Información de registros -->
            <div class="row mt-2">
                <div class="col-md-6">
                    <small class="text-muted" id="infoRegistros">
                        Mostrando 1-25 de 0 registros
                    </small>
                </div>
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
        });
    }
});

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
    // Buscar la persona en los datos cargados
    const filas = document.querySelectorAll('#tablaPersonas tbody tr');
    let persona = null;
    
    filas.forEach(fila => {
        const celdas = fila.querySelectorAll('td');
        if (celdas.length >= 11 && celdas[0].textContent == personaId) {
            persona = {
                ID: celdas[0].textContent,
                URL_IMAGEN: celdas[1].querySelector('img')?.src || '',
                RUT: celdas[2].textContent,
                NOMBRES: celdas[3].textContent,
                APELLIDO_PATERNO: celdas[4].textContent,
                APELLIDO_MATERNO: celdas[5].textContent,
                FAMILIA: celdas[6].textContent,
                rol_nombre: celdas[7].textContent,
                grupo_familiar: celdas[8].textContent
            };
        }
    });
    
    if (persona) {
        // Generar el HTML con los datos de la persona
        const html = `
            <div class="row">
                <div class="col-md-4">
                    <div class="text-center mb-3">
                        <img src="${persona.URL_IMAGEN || '../assets/images/personas/default_male.svg'}" 
                             alt="Foto de ${persona.NOMBRES}" 
                             class="img-thumbnail" 
                             style="width: 150px; height: 150px; object-fit: cover;"
                             onerror="this.src='../assets/images/personas/default_male.svg'">
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6 class="card-title text-primary">
                                <i class="fas fa-id-card me-2"></i>Información Personal
                            </h6>
                            <div class="mb-2">
                                <strong>ID:</strong> ${persona.ID}
                            </div>
                            <div class="mb-2">
                                <strong>RUT:</strong> ${persona.RUT !== '-' ? persona.RUT : 'No especificado'}
                            </div>
                            <div class="mb-2">
                                <strong>Nombres:</strong> ${persona.NOMBRES}
                            </div>
                            <div class="mb-2">
                                <strong>Apellido Paterno:</strong> ${persona.APELLIDO_PATERNO}
                            </div>
                            <div class="mb-2">
                                <strong>Apellido Materno:</strong> ${persona.APELLIDO_MATERNO !== '-' ? persona.APELLIDO_MATERNO : 'No especificado'}
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
                                <strong>Familia:</strong> ${persona.FAMILIA !== '-' ? persona.FAMILIA : 'No especificada'}
                            </div>
                            <div class="mb-2">
                                <strong>Grupo Familiar:</strong> ${persona.grupo_familiar !== 'Sin grupo' ? persona.grupo_familiar : 'No asignado'}
                            </div>
                            <div class="mb-2">
                                <strong>Rol:</strong> ${persona.rol_nombre !== '-' ? persona.rol_nombre : 'No asignado'}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6 class="card-title text-primary">
                                <i class="fas fa-info-circle me-2"></i>Información Adicional
                            </h6>
                            <div class="alert alert-info">
                                <i class="fas fa-lightbulb me-2"></i>
                                <strong>Nota:</strong> Para ver información más detallada como fecha de nacimiento, 
                                email, teléfono y observaciones, edita la persona desde este modal.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
            console.log('Búsqueda:', this.value);
            // Implementar filtrado aquí
        });
    }
    
    // Actualizar información de registros
    const filas = document.querySelectorAll('#tablaPersonas tbody tr');
    const total = filas.length;
    document.getElementById('infoRegistros').textContent = `Mostrando 1-${total} de ${total} registros`;
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
