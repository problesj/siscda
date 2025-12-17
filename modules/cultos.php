<?php 
require_once dirname(__DIR__) . '/session_config.php';
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth_functions.php';

// Verificar autenticación
verificarAutenticacion();

// Verificar acceso al módulo de Cultos
verificarAccesoModulo('Cultos');

// Verificar si el usuario es Administrador del módulo
$esAdministrador = esAdministradorModulo($_SESSION['usuario_id'], 'Cultos');

include '../includes/header.php'; 
?>

<style>
/* Estilos para el modal de asistentes */
#modalAsistentes .modal-dialog {
    max-width: 900px;
}

#modalAsistentes .table th {
    background-color: #343a40;
    color: white;
    font-weight: 600;
    border: none;
}

#modalAsistentes .table td {
    vertical-align: middle;
    border-color: #dee2e6;
}

#modalAsistentes .table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.1);
}

#cargandoAsistentes {
    padding: 40px 20px;
}

#sinAsistentes {
    padding: 40px 20px;
    color: #6c757d;
}

.info-culto {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 15px;
    margin-bottom: 20px;
}

.info-culto strong {
    color: #495057;
}
</style>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Gestión de Cultos</h1>
    <?php if ($esAdministrador): ?>
    <button class="btn btn-primary" onclick="nuevoCulto()">
        <i class="fas fa-plus"></i> Nuevo Culto
    </button>
    <?php endif; ?>
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
                            if ($esAdministrador) {
                                echo "<button class='btn btn-sm btn-info' onclick='editarCulto(" . $row['ID'] . ")'>";
                                echo "<i class='fas fa-edit'></i>";
                                echo "</button> ";
                            }
                            echo "<button class='btn btn-sm btn-success' onclick='tomarAsistencia(" . $row['ID'] . ")'>";
                            echo "<i class='fas fa-clipboard-check'></i>";
                            echo "</button> ";
                            if ($esAdministrador) {
                                echo "<button class='btn btn-sm btn-danger' onclick='eliminarCulto(" . $row['ID'] . ", event); return false;'>";
                                echo "<i class='fas fa-trash'></i>";
                                echo "</button>";
                            }
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
                <h5 class="modal-title" id="modalCultoTitle">Nuevo Culto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="cultos_actions.php" method="POST" id="formCulto">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="crear">
                    <input type="hidden" name="id" id="cultoId" value="">
                    <div class="mb-3">
                        <label for="fecha" class="form-label">Fecha *</label>
                        <input type="date" class="form-control" name="fecha" id="fecha" required>
                    </div>
                    <div class="mb-3">
                        <label for="tipo_culto" class="form-label">Tipo de Culto *</label>
                        <select class="form-select" name="tipo_culto" id="tipo_culto" required>
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
                        <textarea class="form-control" name="observaciones" id="observaciones" rows="3" placeholder="Observaciones adicionales del culto..."></textarea>
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
// Función para nuevo culto
function nuevoCulto() {
    // Resetear formulario
    document.getElementById('formCulto').reset();
    document.getElementById('modalCultoTitle').textContent = 'Nuevo Culto';
    document.getElementById('formAction').value = 'crear';
    document.getElementById('cultoId').value = '';
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalCulto'));
    modal.show();
}

// Limpiar modal cuando se cierre
document.getElementById('modalAsistentes').addEventListener('hidden.bs.modal', function () {
    document.getElementById('cuerpoTablaAsistentes').innerHTML = '';
    document.getElementById('totalAsistentes').textContent = '0';
    document.getElementById('tablaAsistentes').style.display = 'none';
    document.getElementById('sinAsistentes').style.display = 'none';
    document.getElementById('cargandoAsistentes').style.display = 'none';
});

// Funciones del módulo de cultos
function editarCulto(id) {
    // Cargar datos del culto
    fetch('cultos_actions.php?action=obtener&id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const culto = data.culto;
                
                // Cambiar título del modal
                document.getElementById('modalCultoTitle').textContent = 'Editar Culto';
                
                // Cambiar acción del formulario
                document.getElementById('formAction').value = 'editar';
                document.getElementById('cultoId').value = culto.ID;
                
                // Llenar campos
                document.getElementById('fecha').value = culto.FECHA;
                document.getElementById('tipo_culto').value = culto.TIPO_CULTO;
                document.getElementById('observaciones').value = culto.OBSERVACIONES || '';
                
                // Mostrar modal
                const modal = new bootstrap.Modal(document.getElementById('modalCulto'));
                modal.show();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'No se pudo cargar la información del culto'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al cargar la información del culto'
            });
        });
}

// Limpiar modal cuando se cierre para crear nuevo culto
document.getElementById('modalCulto').addEventListener('hidden.bs.modal', function () {
    // Resetear formulario
    document.getElementById('formCulto').reset();
    document.getElementById('modalCultoTitle').textContent = 'Nuevo Culto';
    document.getElementById('formAction').value = 'crear';
    document.getElementById('cultoId').value = '';
});

function tomarAsistencia(id) {
    window.location.href = 'asistencias.php?culto_id=' + id;
}



function eliminarCulto(id, event) {
    // Prevenir cualquier comportamiento por defecto y propagación del evento
    if (event) {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
    }
    
    // Verificar que Swal esté disponible
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 no está disponible');
        return false;
    }
    
    // Usar SweetAlert2 directamente - solo una alerta
    Swal.fire({
        icon: 'warning',
        title: '¿Está seguro?',
        text: '¿Realmente desea eliminar este culto? Esta acción no se puede deshacer.',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        reverseButtons: true,
        allowOutsideClick: false,
        allowEscapeKey: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'cultos_actions.php?action=eliminar&id=' + id;
        }
    });
    
    return false;
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
