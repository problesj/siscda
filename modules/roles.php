<?php include '../includes/header.php'; ?>

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
                        echo "<tr><td colspan='5'>Error al cargar roles: " . $e->getMessage() . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
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
                        <textarea class="form-control" name="descripcion" id="descripcion" rows="3"></textarea>
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

<!-- Scripts para SweetAlert2 -->
<script>
// Mostrar mensajes de éxito o error
<?php if ($successMessage): ?>
    Swal.fire({
        icon: 'success',
        title: 'Éxito',
        text: '<?php echo addslashes($successMessage); ?>',
        timer: 3000,
        showConfirmButton: false
    });
<?php endif; ?>

<?php if ($errorMessage): ?>
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: '<?php echo addslashes($errorMessage); ?>',
        timer: 5000,
        showConfirmButton: false
    });
<?php endif; ?>

// Función para editar rol
function editarRol(rolId) {
    // Aquí puedes implementar la lógica para editar
    Swal.fire({
        title: 'Editar Rol',
        text: 'Funcionalidad de edición en desarrollo',
        icon: 'info'
    });
}

// Función para eliminar rol
function eliminarRol(rolId) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Aquí puedes implementar la lógica para eliminar
            Swal.fire(
                'Eliminado',
                'El rol ha sido eliminado.',
                'success'
            );
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>
