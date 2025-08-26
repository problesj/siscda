<?php
require_once '../session_config.php';
session_start();
require_once '../config.php';

// Verificar autenticación
verificarAutenticacion();

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

include '../includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Gestión de Roles</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRol">
            <i class="fas fa-plus"></i> Nuevo Rol
        </button>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Listado de Roles</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre del Rol</th>
                                <th>Descripción</th>
                                <th>Personas Asignadas</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $pdo = conectarDB();
                                $stmt = $pdo->query("SELECT r.*, COUNT(p.ID) as personas_asignadas 
                                                   FROM roles r 
                                                   LEFT JOIN personas p ON r.id = p.ROL 
                                                   GROUP BY r.id 
                                                   ORDER BY r.id");
                                while ($row = $stmt->fetch()) {
                                    echo "<tr>";
                                    echo "<td>" . $row['id'] . "</td>";
                                    echo "<td>" . $row['nombre_rol'] . "</td>";
                                    echo "<td>" . ($row['descripcion'] ?: '<em class="text-muted">Sin descripción</em>') . "</td>";
                                    echo "<td>" . $row['personas_asignadas'] . "</td>";
                                    echo "<td>
                                            <button class='btn btn-sm btn-info' onclick='editarRol(" . $row['id'] . ")'>
                                                <i class='fas fa-edit'></i>
                                            </button>
                                            <button class='btn btn-sm btn-danger' onclick='eliminarRol(" . $row['id'] . ")'>
                                                <i class='fas fa-trash'></i>
                                            </button>
                                          </td>";
                                    echo "</tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='4'>Error al cargar roles: " . $e->getMessage() . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Rol -->
<div class="modal fade" id="modalRol" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nuevo Rol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="roles_actions.php" method="POST" id="formRol">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="crear">
                    <input type="hidden" name="rol_id" id="rol_id" value="">
                    <div class="mb-3">
                        <label for="nombre_rol" class="form-label">Nombre del Rol *</label>
                        <input type="text" class="form-control" name="nombre_rol" id="nombre_rol" required>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" name="descripcion" id="descripcion" rows="3" placeholder="Descripción opcional del rol..."></textarea>
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

<script>
function editarRol(id) {
    // Cambiar el modal a modo edición
    document.getElementById('modalTitle').textContent = 'Editar Rol';
    document.getElementById('formAction').value = 'editar';
    document.getElementById('rol_id').value = id;
    document.getElementById('btnSubmit').textContent = 'Actualizar';
    
    // Cargar datos del rol
    fetch('roles_actions.php?action=obtener&id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('nombre_rol').value = data.rol.nombre_rol;
                document.getElementById('descripcion').value = data.rol.descripcion || '';
            } else {
                SwalUtils.showError('Error al cargar datos del rol: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            SwalUtils.showError('Error al cargar datos del rol');
        });
    
    // Mostrar modal
    new bootstrap.Modal(document.getElementById('modalRol')).show();
}

function nuevoRol() {
    // Cambiar el modal a modo creación
    document.getElementById('modalTitle').textContent = 'Nuevo Rol';
    document.getElementById('formAction').value = 'crear';
    document.getElementById('rol_id').value = '';
    document.getElementById('btnSubmit').textContent = 'Guardar';
    
    // Limpiar formulario
    document.getElementById('formRol').reset();
    
    // Mostrar modal
    new bootstrap.Modal(document.getElementById('modalRol')).show();
}

function eliminarRol(id) {
    // Verificar si SwalUtils está disponible
    if (typeof SwalUtils !== 'undefined' && typeof SwalUtils.showDeleteConfirm === 'function') {
        SwalUtils.showDeleteConfirm('este rol').then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'roles_actions.php?action=eliminar&id=' + id;
            }
        });
    } else {
        // Fallback: usar SweetAlert2 directamente
        Swal.fire({
            icon: 'warning',
            title: '¿Está seguro?',
            text: '¿Realmente desea eliminar este rol? Esta acción no se puede deshacer.',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'roles_actions.php?action=eliminar&id=' + id;
            }
        });
    }
}

// Limpiar modal al cerrar
document.getElementById('modalRol').addEventListener('hidden.bs.modal', function () {
    document.getElementById('formRol').reset();
});

// Mostrar alertas de sesión con SweetAlert2
<?php if ($successMessage): ?>
SwalUtils.showSuccess('<?php echo addslashes($successMessage); ?>');
<?php endif; ?>

<?php if ($errorMessage): ?>
SwalUtils.showError('<?php echo addslashes($errorMessage); ?>');
<?php endif; ?>
</script>

<?php include '../includes/footer.php'; ?>
