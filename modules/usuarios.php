<?php 
require_once dirname(__DIR__) . '/session_config.php';
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth_functions.php';

// Verificar autenticación
verificarAutenticacion();

// Verificar acceso al módulo de Usuarios
verificarAccesoModulo('Usuarios');

// Verificar si el usuario es Administrador del módulo
$esAdministrador = esAdministradorModulo($_SESSION['usuario_id'], 'Usuarios');

include '../includes/header.php'; 
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Gestión de Usuarios</h1>
    <button class="btn btn-primary" onclick="nuevoUsuario()">
        <i class="fas fa-plus"></i> Nuevo Usuario
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
        <h6 class="m-0 font-weight-bold text-primary">Listado de Usuarios</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Nombre Completo</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        $pdo = conectarDB();
                        $stmt = $pdo->query("SELECT * FROM usuarios ORDER BY USUARIO_ID");
                        while ($row = $stmt->fetch()) {
                            echo "<tr>";
                            echo "<td>" . $row['USUARIO_ID'] . "</td>";
                            echo "<td>" . $row['USERNAME'] . "</td>";
                            echo "<td>" . $row['NOMBRE_COMPLETO'] . "</td>";
                            echo "<td>" . $row['EMAIL'] . "</td>";
                            echo "<td>" . ($row['ACTIVO'] ? 'Activo' : 'Inactivo') . "</td>";
                            echo "<td>";
                            if ($esAdministrador) {
                                echo "<button class='btn btn-sm btn-info' onclick='editarUsuario(" . $row['USUARIO_ID'] . ")'>";
                                echo "<i class='fas fa-edit'></i>";
                                echo "</button> ";
                                echo "<button class='btn btn-sm btn-danger' onclick='eliminarUsuario(" . $row['USUARIO_ID'] . ")'>";
                                echo "<i class='fas fa-trash'></i>";
                                echo "</button>";
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                    } catch (PDOException $e) {
                        echo "<tr><td colspan='6'>Error al cargar usuarios: " . $e->getMessage() . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para Usuario -->
<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="usuarios_actions.php" method="POST" id="formUsuario">
                <div class="modal-body">
                    <input type="hidden" name="action" value="crear" id="formAction">
                    <input type="hidden" name="usuario_id" id="usuario_id">
                    <div class="mb-3">
                        <label for="username" class="form-label">Usuario *</label>
                        <input type="text" class="form-control" name="username" id="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña <span id="passwordNote">*</span></label>
                        <input type="password" class="form-control" name="password" id="password">
                        <small class="form-text text-muted" id="passwordHelp">Deja en blanco para mantener la contraseña actual</small>
                    </div>
                    <div class="mb-3">
                        <label for="nombre_completo" class="form-label">Nombre Completo *</label>
                        <input type="text" class="form-control" name="nombre_completo" id="nombre_completo" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" id="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="activo" class="form-label">Estado</label>
                        <select class="form-select" name="activo" id="activo">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
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
function editarUsuario(id) {
    // Cambiar el modal a modo edición
    document.getElementById('modalTitle').textContent = 'Editar Usuario';
    document.getElementById('formAction').value = 'editar';
    document.getElementById('usuario_id').value = id;
    document.getElementById('password').required = false;
    document.getElementById('passwordNote').textContent = '';
    document.getElementById('passwordHelp').style.display = 'block';
    document.getElementById('btnSubmit').textContent = 'Actualizar';
    
    // Cargar datos del usuario
    fetch('usuarios_actions.php?action=obtener&id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('username').value = data.usuario.USERNAME;
                document.getElementById('nombre_completo').value = data.usuario.NOMBRE_COMPLETO;
                document.getElementById('email').value = data.usuario.EMAIL;
                document.getElementById('activo').value = data.usuario.ACTIVO;
                document.getElementById('password').value = ''; // Limpiar contraseña
            } else {
                SwalUtils.showError('Error al cargar datos del usuario: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            SwalUtils.showError('Error al cargar datos del usuario');
        });
    
    // Mostrar modal
    new bootstrap.Modal(document.getElementById('modalUsuario')).show();
}

function nuevoUsuario() {
    // Cambiar el modal a modo creación
    document.getElementById('modalTitle').textContent = 'Nuevo Usuario';
    document.getElementById('formAction').value = 'crear';
    document.getElementById('usuario_id').value = '';
    document.getElementById('password').required = true;
    document.getElementById('passwordNote').textContent = '*';
    document.getElementById('passwordHelp').style.display = 'none';
    document.getElementById('btnSubmit').textContent = 'Guardar';
    
    // Limpiar formulario
    document.getElementById('formUsuario').reset();
    
    // Mostrar modal
    new bootstrap.Modal(document.getElementById('modalUsuario')).show();
}

function eliminarUsuario(id) {
    // Verificar si SwalUtils está disponible
    if (typeof SwalUtils !== 'undefined' && typeof SwalUtils.showDeleteConfirm === 'function') {
        SwalUtils.showDeleteConfirm('este usuario').then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'usuarios_actions.php?action=eliminar&id=' + id;
            }
        });
    } else {
        // Fallback: usar SweetAlert2 directamente
        Swal.fire({
            icon: 'warning',
            title: '¿Está seguro?',
            text: '¿Realmente desea eliminar este usuario? Esta acción no se puede deshacer.',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'usuarios_actions.php?action=eliminar&id=' + id;
            }
        });
    }
}

// Limpiar modal al cerrar
document.getElementById('modalUsuario').addEventListener('hidden.bs.modal', function () {
    document.getElementById('formUsuario').reset();
    document.getElementById('passwordHelp').style.display = 'none';
    document.getElementById('password').required = true;
    document.getElementById('passwordNote').textContent = '*';
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
