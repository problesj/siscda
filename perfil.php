<?php include 'includes/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Mi Perfil</h1>
</div>

<?php
// Variables para mensajes
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

                // Obtener información del usuario actual
                try {
                    $pdo = conectarDB();
                    $stmt = $pdo->prepare("SELECT USUARIO_ID, USERNAME, NOMBRE_COMPLETO, EMAIL, FECHA_CREACION, FECHA_ACTUALIZACION, ULTIMO_ACCESO FROM usuarios WHERE USUARIO_ID = ?");
                    $stmt->execute([$_SESSION['usuario_id']]);
                    $usuario = $stmt->fetch();
                } catch (PDOException $e) {
                    $errorMessage = "Error al cargar información del usuario: " . $e->getMessage();
                }
?>

<!-- Mensajes de alerta -->
<?php if ($successMessage): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMessage); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($errorMessage): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($errorMessage); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Información del Usuario -->
    <div class="col-md-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-user"></i> Información del Usuario
                </h6>
            </div>
            <div class="card-body">
                <?php if ($usuario): ?>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>ID de Usuario:</strong>
                        </div>
                        <div class="col-sm-8">
                            <?php echo htmlspecialchars($usuario['USUARIO_ID']); ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Nombre de Usuario:</strong>
                        </div>
                        <div class="col-sm-8">
                            <?php echo htmlspecialchars($usuario['USERNAME']); ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Nombre Completo:</strong>
                        </div>
                        <div class="col-sm-8">
                            <?php echo htmlspecialchars($usuario['NOMBRE_COMPLETO']); ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Email:</strong>
                        </div>
                        <div class="col-sm-8">
                            <?php echo htmlspecialchars($usuario['EMAIL'] ?? 'No especificado'); ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Fecha de Creación:</strong>
                        </div>
                        <div class="col-sm-8">
                            <?php echo $usuario['FECHA_CREACION'] ? date('d/m/Y H:i', strtotime($usuario['FECHA_CREACION'])) : 'No especificada'; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Última Actualización:</strong>
                        </div>
                        <div class="col-sm-8">
                            <?php echo $usuario['FECHA_ACTUALIZACION'] ? date('d/m/Y H:i', strtotime($usuario['FECHA_ACTUALIZACION'])) : 'No registrada'; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Último Acceso:</strong>
                        </div>
                        <div class="col-sm-8">
                            <?php echo $usuario['ULTIMO_ACCESO'] ? date('d/m/Y H:i', strtotime($usuario['ULTIMO_ACCESO'])) : 'No registrado'; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> No se pudo cargar la información del usuario.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Cambiar Contraseña -->
    <div class="col-md-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-key"></i> Cambiar Contraseña
                </h6>
            </div>
            <div class="card-body">
                <form action="cambiar_password.php" method="POST" id="formCambiarPassword">
                    <div class="mb-3">
                        <label for="password_actual" class="form-label">
                            <i class="fas fa-lock"></i> Contraseña Actual
                        </label>
                        <input type="password" class="form-control" id="password_actual" name="password_actual" required>
                        <div class="form-text">Ingresa tu contraseña actual para verificar tu identidad.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password_nueva" class="form-label">
                            <i class="fas fa-key"></i> Nueva Contraseña
                        </label>
                        <input type="password" class="form-control" id="password_nueva" name="password_nueva" required minlength="6">
                        <div class="form-text">La nueva contraseña debe tener al menos 6 caracteres.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password_confirmar" class="form-label">
                            <i class="fas fa-check-circle"></i> Confirmar Nueva Contraseña
                        </label>
                        <input type="password" class="form-control" id="password_confirmar" name="password_confirmar" required minlength="6">
                        <div class="form-text">Repite la nueva contraseña para confirmar.</div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Cambiar Contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Información de Seguridad -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-shield-alt"></i> Información de Seguridad
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Consejos de Seguridad:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Usa una contraseña única y segura</li>
                                <li>No compartas tu contraseña con nadie</li>
                                <li>Cambia tu contraseña regularmente</li>
                                <li>No uses la misma contraseña en otros sitios</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-warning">
                            <i class="fas fa-clock"></i>
                            <strong>Última Actividad:</strong><br>
                            <small>Tu sesión se mantendrá activa mientras uses el sistema. Para mayor seguridad, cierra sesión cuando termines.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validación del formulario de cambio de contraseña
document.getElementById('formCambiarPassword').addEventListener('submit', function(e) {
    const passwordNueva = document.getElementById('password_nueva').value;
    const passwordConfirmar = document.getElementById('password_confirmar').value;
    
    if (passwordNueva !== passwordConfirmar) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Error de Validación',
            text: 'Las contraseñas nuevas no coinciden. Por favor, verifica que ambas sean iguales.',
            confirmButtonText: 'Entendido'
        });
        return false;
    }
    
    if (passwordNueva.length < 6) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Error de Validación',
            text: 'La nueva contraseña debe tener al menos 6 caracteres.',
            confirmButtonText: 'Entendido'
        });
        return false;
    }
    
    // Confirmar el cambio
    if (!confirm('¿Estás seguro de que quieres cambiar tu contraseña?')) {
        e.preventDefault();
        return false;
    }
});

// Mostrar/ocultar contraseñas
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.nextElementSibling.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

<?php include 'includes/footer.php'; ?>
