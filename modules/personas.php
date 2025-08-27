<?php include '../includes/header_fixed.php'; ?>

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
    alert('Función de editar persona ' + id + ' - Implementar modal');
}

function nuevoPersona() {
    alert('Función de nueva persona - Implementar modal');
}

function eliminarPersona(id) {
    if (confirm('¿Está seguro de que desea eliminar esta persona?')) {
        alert('Eliminando persona ' + id);
    }
}

function verPersona(id) {
    alert('Viendo persona ' + id);
}

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

// Mostrar alertas de sesión
<?php if ($successMessage): ?>
alert('<?php echo addslashes($successMessage); ?>');
<?php endif; ?>

<?php if ($errorMessage): ?>
alert('<?php echo addslashes($errorMessage); ?>');
<?php endif; ?>
</script>

<?php include '../includes/footer.php'; ?>
