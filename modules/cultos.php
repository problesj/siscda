<?php include '../includes/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Gestión de Cultos</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCulto">
        <i class="fas fa-plus"></i> Nuevo Culto
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
        <h6 class="m-0 font-weight-bold text-primary">Listado de Cultos</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Tipo de Culto</th>
                        <th>Observaciones</th>
                        <th>Fecha Creación</th>
                        <th>Asistentes</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        $pdo = conectarDB();
                        $stmt = $pdo->query("SELECT c.*, COUNT(a.PERSONA_ID) as asistentes 
                                           FROM cultos c 
                                           LEFT JOIN asistencias a ON c.ID = a.CULTO_ID 
                                           GROUP BY c.ID 
                                           ORDER BY c.FECHA DESC, c.FECHA_CREACION DESC");
                        while ($row = $stmt->fetch()) {
                            echo "<tr>";
                            echo "<td>" . $row['ID'] . "</td>";
                            echo "<td>" . date('d/m/Y', strtotime($row['FECHA'])) . "</td>";
                            echo "<td>" . $row['TIPO_CULTO'] . "</td>";
                            echo "<td>" . ($row['OBSERVACIONES'] ?? '-') . "</td>";
                            echo "<td>" . ($row['FECHA_CREACION'] ? date('d/m/Y H:i', strtotime($row['FECHA_CREACION'])) : '-') . "</td>";
                            echo "<td>" . $row['asistentes'] . "</td>";
                            echo "<td>";
                            echo "<button class='btn btn-sm btn-info' onclick='editarCulto(" . $row['ID'] . ")'>";
                            echo "<i class='fas fa-edit'></i>";
                            echo "</button> ";
                            echo "<button class='btn btn-sm btn-success' onclick='tomarAsistencia(" . $row['ID'] . ")'>";
                            echo "<i class='fas fa-clipboard-check'></i>";
                            echo "</button> ";
                            echo "<button class='btn btn-sm btn-danger' onclick='eliminarCulto(" . $row['ID'] . ")'>";
                            echo "<i class='fas fa-trash'></i>";
                            echo "</button>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } catch (PDOException $e) {
                        echo "<tr><td colspan='7'>Error al cargar cultos: " . $e->getMessage() . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para Culto -->
<div class="modal fade" id="modalCulto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Culto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="cultos_actions.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="crear">
                    <div class="mb-3">
                        <label for="fecha" class="form-label">Fecha *</label>
                        <input type="date" class="form-control" name="fecha" required>
                    </div>
                    <div class="mb-3">
                        <label for="tipo_culto" class="form-label">Tipo de Culto *</label>
                        <select class="form-select" name="tipo_culto" required>
                            <option value="">Seleccionar tipo</option>
                            <option value="Estudio Bíblico">Estudio Bíblico</option>
                            <option value="Oración">Oración</option>
                            <option value="Central">Central</option>
                            <option value="Acción de Gracias">Acción de Gracias</option>
                            <option value="Hageo">Hageo</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea class="form-control" name="observaciones" rows="3" placeholder="Observaciones adicionales del culto..."></textarea>
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
function editarCulto(id) {
    // Implementar edición
    alert('Editar culto ' + id);
}

function tomarAsistencia(id) {
    window.location.href = 'asistencias.php?culto_id=' + id;
}

function eliminarCulto(id) {
    // Verificar si SwalUtils está disponible
    if (typeof SwalUtils !== 'undefined' && typeof SwalUtils.showDeleteConfirm === 'function') {
        SwalUtils.showDeleteConfirm('este culto').then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'cultos_actions.php?action=eliminar&id=' + id;
            }
        });
    } else {
        // Fallback: usar SweetAlert2 directamente
        Swal.fire({
            icon: 'warning',
            title: '¿Está seguro?',
            text: '¿Realmente desea eliminar este culto? Esta acción no se puede deshacer.',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'cultos_actions.php?action=eliminar&id=' + id;
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
