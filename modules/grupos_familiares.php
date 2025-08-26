<?php include '../includes/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Gestión de Grupos Familiares</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalGrupoFamiliar">
        <i class="fas fa-plus"></i> Nuevo Grupo Familiar
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
        <h6 class="m-0 font-weight-bold text-primary">Listado de Grupos Familiares</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Fecha Creación</th>
                        <th>Fecha Actualización</th>
                        <th>Miembros</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        $pdo = conectarDB();
                        $stmt = $pdo->query("SELECT gf.*, COUNT(p.ID) as miembros 
                                           FROM grupos_familiares gf 
                                           LEFT JOIN personas p ON gf.ID = p.GRUPO_FAMILIAR_ID 
                                           GROUP BY gf.ID 
                                           ORDER BY gf.ID");
                        while ($row = $stmt->fetch()) {
                            echo "<tr>";
                            echo "<td>" . $row['ID'] . "</td>";
                            echo "<td>" . $row['NOMBRE'] . "</td>";
                            echo "<td>" . ($row['DESCRIPCION'] ?? '-') . "</td>";
                            echo "<td>" . ($row['FECHA_CREACION'] ? date('d/m/Y H:i', strtotime($row['FECHA_CREACION'])) : '-') . "</td>";
                            echo "<td>" . ($row['FECHA_ACTUALIZACION'] ? date('d/m/Y H:i', strtotime($row['FECHA_ACTUALIZACION'])) : '-') . "</td>";
                            echo "<td>" . $row['miembros'] . "</td>";
                            echo "<td>
                                    <button class='btn btn-sm btn-info' onclick='editarGrupoFamiliar(" . $row['ID'] . ")'>
                                        <i class='fas fa-edit'></i>
                                    </button>
                                    <button class='btn btn-sm btn-danger' onclick='eliminarGrupoFamiliar(" . $row['ID'] . ")'>
                                        <i class='fas fa-trash'></i>
                                    </button>
                                  </td>";
                            echo "</tr>";
                        }
                    } catch (PDOException $e) {
                        echo "<tr><td colspan='7'>Error al cargar grupos familiares: " . $e->getMessage() . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para Grupo Familiar -->
<div class="modal fade" id="modalGrupoFamiliar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Grupo Familiar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="grupos_familiares_actions.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="crear">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del Grupo *</label>
                        <input type="text" class="form-control" name="nombre" required placeholder="Ej: Familia González">
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" name="descripcion" rows="3" placeholder="Descripción del grupo familiar..."></textarea>
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

<script>
function editarGrupoFamiliar(id) {
    // Implementar edición
    alert('Editar grupo familiar ' + id);
}

function eliminarGrupoFamiliar(id) {
    // Verificar si SwalUtils está disponible
    if (typeof SwalUtils !== 'undefined' && typeof SwalUtils.showDeleteConfirm === 'function') {
        SwalUtils.showDeleteConfirm('este grupo familiar').then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'grupos_familiares_actions.php?action=eliminar&id=' + id;
            }
        });
    } else {
        // Fallback: usar SweetAlert2 directamente
        Swal.fire({
            icon: 'warning',
            title: '¿Está seguro?',
            text: '¿Realmente desea eliminar este grupo familiar? Esta acción no se puede deshacer.',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'grupos_familiares_actions.php?action=eliminar&id=' + id;
            }
        });
    }
}

// Mostrar alertas de sesión con SweetAlert2
<?php if ($successMessage): ?>
SwalUtils.showSuccess('<?php echo addslashes($successMessage); ?>');
<?php endif; ?>

<?php if ($errorMessage): ?>
SwalUtils.showError('<?php echo addslashes($errorMessage); ?>');
<?php endif; ?>
</script>

<?php include '../includes/footer.php'; ?>
