<?php 
require_once dirname(__DIR__) . '/session_config.php';
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth_functions.php';

// Verificar autenticación
verificarAutenticacion();

// Verificar acceso al módulo de Asistencias
verificarAccesoModulo('Asistencias');

// Forzar recarga de caché
$version = time();
?>
<?php include '../includes/header.php'; ?>
<link rel="stylesheet" href="../assets/css/asistencias.css?v=<?php echo $version; ?>">

<!-- Librería para generar archivos Excel -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
// Forzar recarga de caché para este archivo
console.log('Cargando módulo de asistencias - versión: <?php echo $version; ?>');
// Verificar que las funciones estén disponibles
window.addEventListener('load', function() {
    console.log('Verificando funciones...');
    if (typeof verAsistentesCulto === 'function') {
        console.log('✅ Función verAsistentesCulto está disponible');
    } else {
        console.log('❌ Función verAsistentesCulto NO está disponible');
    }
    
    if (document.getElementById('modalAsistentesCulto')) {
        console.log('✅ Modal modalAsistentesCulto está disponible');
    } else {
        console.log('❌ Modal modalAsistentesCulto NO está disponible');
    }
});
</script>
<style>
/* Estilos para elementos fijos */
.sticky-top {
    position: sticky;
    top: 0;
    z-index: 1000;
}

/* Controles de búsqueda fijos */
.controles-fijos {
    position: sticky;
    top: 0;
    background: white;
    z-index: 1001;
    border-bottom: 2px solid #e9ecef;
    padding: 1rem 0;
    margin-bottom: 0;
}

/* Encabezados de tabla fijos */
.thead-fijo {
    position: sticky;
    top: 0;
    z-index: 999;
    background: #1e3a8a !important;
    color: white !important;
}

/* Tabla con scroll y altura máxima */
.tabla-con-scroll {
    max-height: 70vh;
    overflow-y: auto;
    border: 1px solid #dee2e6;
}

/* Mejorar la apariencia del botón de limpiar */
.btn-limpiar {
    border-left: 1px solid #dee2e6;
}

/* Sobrescribir variables CSS de Bootstrap para la tabla de asistencias */
.table-asistencias {
    --bs-table-bg: transparent !important;
    --bs-table-striped-bg: transparent !important;
    --bs-table-hover-bg: transparent !important;
    --bs-table-active-bg: transparent !important;
}

/* Estilos CSS con máxima prioridad para sobrescribir estilos generales */
html body .table-asistencias tbody tr[style*="background-color"] {
    background-color: inherit !important;
}

/* Estilos específicos para filas con colores */
html body .table-asistencias tbody tr[data-debug*="Familia:"] {
    background-color: inherit !important;
}

/* Forzar colores específicos */
html body .table-asistencias tbody tr[data-debug*="Color: #d1ecf1"] {
    background-color: #d1ecf1 !important;
}

html body .table-asistencias tbody tr[data-debug*="Color: #fff3cd"] {
    background-color: #fff3cd !important;
}

html body .table-asistencias tbody tr[data-debug*="Color: #f8f9fa"] {
    background-color: #f8f9fa !important;
}

html body .table-asistencias tbody tr[data-debug*="Color: #ffffff"] {
    background-color: #ffffff !important;
}

/* Estilos adicionales para asegurar que se apliquen */
html body .table-responsive .table-asistencias tbody tr[data-debug*="Color: #d1ecf1"] {
    background-color: #d1ecf1 !important;
}

html body .table-responsive .table-asistencias tbody tr[data-debug*="Color: #fff3cd"] {
    background-color: #fff3cd !important;
}

html body .table-responsive .table-asistencias tbody tr[data-debug*="Color: #f8f9fa"] {
    background-color: #f8f9fa !important;
}

html body .table-responsive .table-asistencias tbody tr[data-debug*="Color: #ffffff"] {
    background-color: #ffffff !important;
}

/* Sobrescribir estilos de Bootstrap específicamente */
.table-asistencias tbody tr {
    background-color: transparent !important;
}

.table-asistencias tbody tr:nth-child(odd) {
    background-color: transparent !important;
}

.table-asistencias tbody tr:nth-child(even) {
    background-color: transparent !important;
}

.table-asistencias tbody tr:hover {
    background-color: transparent !important;
}

/* Forzar colores específicos con máxima prioridad */
.table-asistencias tbody tr[data-debug*="Color: #d1ecf1"] {
    background-color: #d1ecf1 !important;
    --bs-table-bg: #d1ecf1 !important;
}

.table-asistencias tbody tr[data-debug*="Color: #fff3cd"] {
    background-color: #fff3cd !important;
    --bs-table-bg: #fff3cd !important;
}

.table-asistencias tbody tr[data-debug*="Color: #f8f9fa"] {
    background-color: #f8f9fa !important;
    --bs-table-bg: #f8f9fa !important;
}

.table-asistencias tbody tr[data-debug*="Color: #ffffff"] {
    background-color: #ffffff !important;
    --bs-table-bg: #ffffff !important;
}

/* Asegurar que los colores se mantengan en todos los estados */
.table-asistencias tbody tr[data-debug*="Color: #d1ecf1"]:hover,
.table-asistencias tbody tr[data-debug*="Color: #d1ecf1"]:active {
    background-color: #d1ecf1 !important;
    --bs-table-bg: #d1ecf1 !important;
}

/* Estilos de paginación con máxima prioridad */
.pagination .page-link {
    min-height: 64px !important;
    font-size: 1.25rem !important;
    padding: 1rem 0.75rem !important;
    border: 3px solid #dee2e6 !important;
    border-radius: 0.75rem !important;
    background-color: #ffffff !important;
    color: #495057 !important;
    font-weight: 600 !important;
    transition: all 0.2s ease-in-out !important;
}

.pagination .page-link:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
    border-color: #007bff !important;
    color: #007bff !important;
}

.pagination .page-item.active .page-link {
    background-color: #007bff !important;
    border-color: #007bff !important;
    color: white !important;
    transform: scale(1.05) !important;
}

.pagination .page-item.disabled .page-link {
    background-color: #f8f9fa !important;
    border-color: #dee2e6 !important;
    color: #6c757d !important;
    opacity: 0.6 !important;
}

/* Estilos específicos para móviles */
@media (max-width: 767.98px) {
    .pagination {
        justify-content: space-between !important;
        flex-wrap: nowrap !important;
        gap: 0.75rem !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .pagination .page-item {
        flex: 1 !important;
        min-width: 0 !important;
        margin: 0 !important;
    }
    
    .pagination .page-link {
        min-height: 64px !important;
        font-size: 1.25rem !important;
        padding: 1rem 0.75rem !important;
        border: 3px solid #dee2e6 !important;
        border-radius: 0.75rem !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
    }
    
    .pagination .page-item:first-child .page-link,
    .pagination .page-item:last-child .page-link {
        font-size: 1.5rem !important;
        font-weight: 700 !important;
        min-height: 64px !important;
        padding: 1rem 0.5rem !important;
        background-color: #f8f9fa !important;
        border-color: #dee2e6 !important;
        color: #495057 !important;
    }
}

@media (max-width: 480px) {
    .pagination .page-link {
        min-height: 72px !important;
        font-size: 1.4rem !important;
        padding: 1.25rem 0.5rem !important;
    }
    
    .pagination .page-item:first-child .page-link,
    .pagination .page-item:last-child .page-link {
        min-height: 72px !important;
        font-size: 1.8rem !important;
    }
    
    .pagination {
        gap: 0.5rem !important;
    }
}

.table-asistencias tbody tr[data-debug*="Color: #fff3cd"]:hover,
.table-asistencias tbody tr[data-debug*="Color: #fff3cd"]:active {
    background-color: #fff3cd !important;
    --bs-table-bg: #fff3cd !important;
}

.table-asistencias tbody tr[data-debug*="Color: #f8f9fa"]:hover,
.table-asistencias tbody tr[data-debug*="Color: #f8f9fa"]:active {
    background-color: #f8f9fa !important;
    --bs-table-bg: #f8f9fa !important;
}

.table-asistencias tbody tr[data-debug*="Color: #ffffff"]:hover,
.table-asistencias tbody tr[data-debug*="Color: #ffffff"]:active {
    background-color: #ffffff !important;
    --bs-table-bg: #ffffff !important;
}

/* Estilos para el encabezado de la tabla */
.table-asistencias thead th {
    background-color: #1e3a8a !important;
    color: #ffffff !important;
    border-color: #1e3a8a !important;
    font-weight: 600 !important;
}

.table-asistencias thead th:hover {
    background-color: #1e40af !important;
}

/* Asegurar que el encabezado mantenga el estilo en todas las resoluciones */
.table-responsive .table-asistencias thead th {
    background-color: #1e3a8a !important;
    color: #ffffff !important;
    border-color: #1e3a8a !important;
}

/* Estilos para las sugerencias de autocompletado */
.sugerencias-container {
    position: relative;
}

.sugerencias-list {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1050;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    max-height: 200px;
    overflow-y: auto;
    width: 100%;
    margin-top: 2px;
}

.sugerencias-item {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f8f9fa;
    cursor: pointer;
    transition: background-color 0.15s ease-in-out;
    font-size: 0.9rem;
}

.sugerencias-item:hover {
    background-color: #e9ecef;
}

.sugerencias-item:last-child {
    border-bottom: none;
}

.sugerencias-item .texto-principal {
    font-weight: 600;
    color: #212529;
    margin-bottom: 2px;
}

.sugerencias-item .texto-secundario {
    font-size: 0.8rem;
    color: #6c757d;
    font-style: italic;
}

/* Asegurar que las sugerencias aparezcan sobre otros elementos */
.modal-body {
    position: relative;
}

.sugerencias-container {
    position: relative;
    z-index: 1060;
}

/* Estilos para indicador de guardado */
.checkbox-asistencia.guardando {
    opacity: 0.6;
    pointer-events: none;
}

.checkbox-asistencia.guardando::after {
    content: "⏳";
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 0.8rem;
    color: #6c757d;
}

/* Indicador de éxito sutil */
.checkbox-asistencia.guardado {
    animation: guardadoExitoso 0.5s ease-in-out;
}

@keyframes guardadoExitoso {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}
</style>

<?php
$culto_id = isset($_GET['culto_id']) ? $_GET['culto_id'] : null;
$culto = null;

if ($culto_id) {
    try {
        $pdo = conectarDB();
        $stmt = $pdo->prepare("SELECT * FROM cultos WHERE ID = ?");
        $stmt->execute([$culto_id]);
        $culto = $stmt->fetch();
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error al cargar el culto: ' . $e->getMessage();
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <?php if ($culto): ?>
            Asistencia: <?php echo $culto['TIPO_CULTO'] . ' - ' . date('d/m/Y', strtotime($culto['FECHA'])); ?>
        <?php else: ?>
            Gestión de Asistencias
        <?php endif; ?>
    </h1>
    <?php if (!$culto_id): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSeleccionarCulto">
            <i class="fas fa-plus"></i> Tomar Asistencia
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

<?php if ($culto): ?>
    <!-- Formulario de Asistencia -->
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                Marcar Asistencias - <?php echo $culto['TIPO_CULTO'] . ' del ' . date('d/m/Y', strtotime($culto['FECHA'])); ?>
            </h6>

        </div>
        <div class="card-body">
            <form action="asistencias_actions.php" method="POST">
                <input type="hidden" name="culto_id" value="<?php echo $culto_id; ?>">
                <input type="hidden" name="action" value="guardar_asistencias">
                
                <!-- Controles de búsqueda y ordenamiento - FIJOS -->
                <div class="row mb-3 controles-fijos">
                    <!-- Búsqueda - Ocupa todo el ancho en móviles -->
                    <div class="col-12 col-md-6 mb-3 mb-md-0">
                        <div class="input-group">
                            <input type="text" class="form-control" id="searchInput" placeholder="Buscar personas..." oninput="filtrarPersonas()" onkeydown="prevenirEnter(event)">
                            <button class="btn btn-outline-secondary" type="button" onclick="filtrarPersonas()">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-limpiar" type="button" onclick="limpiarBusqueda()" title="Limpiar búsqueda">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <small class="text-muted d-none d-md-block">La búsqueda se actualiza automáticamente mientras escribes (Enter deshabilitado)</small>
                        <div id="estadoBusqueda" class="mt-1" style="display: none;">
                            <span class="badge bg-info">Buscando...</span>
                        </div>
                    </div>
                    
                    <!-- Botones de ordenamiento - Apilados en móviles -->
                    <div class="col-12 col-md-6">
                        <div class="d-flex flex-column flex-md-row justify-content-md-end">
                            <div class="btn-group-vertical btn-group-sm d-md-none mb-2" role="group">
                                <button type="button" class="btn btn-outline-primary" onclick="cambiarOrden('FAMILIA')">
                                    <i class="fas fa-sort"></i> Familia
                                </button>
                                <button type="button" class="btn btn-outline-primary" onclick="cambiarOrden('GRUPO_FAMILIAR')">
                                    <i class="fas fa-sort"></i> Grupo Familiar
                                </button>
                                <button type="button" class="btn btn-outline-primary" onclick="cambiarOrden('APELLIDO_PATERNO')">
                                    <i class="fas fa-sort"></i> Apellido
                                </button>
                            </div>
                            <div class="btn-group d-none d-md-flex" role="group">
                                <button type="button" class="btn btn-outline-primary" onclick="cambiarOrden('FAMILIA')">
                                    <i class="fas fa-sort"></i> Familia
                                </button>
                                <button type="button" class="btn btn-outline-primary" onclick="cambiarOrden('GRUPO_FAMILIAR')">
                                    <i class="fas fa-sort"></i> Grupo Familiar
                                </button>
                                <button type="button" class="btn btn-outline-primary" onclick="cambiarOrden('APELLIDO_PATERNO')">
                                    <i class="fas fa-sort"></i> Apellido
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tabla con encabezados fijos -->
                <div class="table-responsive tabla-con-scroll">
                    <table class="table table-bordered table-sm table-asistencias">
                        <thead class="thead-fijo">
                            <tr>
                                <th class="text-center" style="min-width: 80px;">✓</th>
                                <th class="d-none d-md-table-cell">Nombres</th>
                                <th class="d-none d-md-table-cell">Apellido Paterno</th>
                                <th class="d-none d-md-table-cell">Familia</th>
                                <th class="d-none d-md-table-cell">Grupo Familiar</th>
                                <th class="text-center" style="min-width: 60px;">Ver</th>
                                <!-- Columna móvil que combina toda la información -->
                                <th class="d-table-cell d-md-none">Persona</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                // Consulta con ordenamiento que prioriza filas con datos sobre las vacías
                                $stmt = $pdo->query("SELECT p.*, gf.NOMBRE as grupo_familiar 
                                                   FROM personas p 
                                                   LEFT JOIN grupos_familiares gf ON p.GRUPO_FAMILIAR_ID = gf.ID 
                                                   ORDER BY 
                                                       CASE WHEN gf.NOMBRE IS NOT NULL AND gf.NOMBRE != '' THEN 1 ELSE 2 END,
                                                       gf.NOMBRE ASC,
                                                       CASE WHEN p.FAMILIA IS NOT NULL AND p.FAMILIA != '' THEN 1 ELSE 2 END,
                                                       p.FAMILIA ASC,
                                                       CASE WHEN p.APELLIDO_PATERNO IS NOT NULL AND p.APELLIDO_PATERNO != '' THEN 1 ELSE 2 END,
                                                       p.APELLIDO_PATERNO ASC");
                                
                                $familiaActual = '';
                                $colorAlternado = true;
                                
                                while ($persona = $stmt->fetch()) {
                                    $asistio = false;
                                    // Verificar si ya asistió
                                    $stmt_asistencia = $pdo->prepare("SELECT PERSONA_ID FROM asistencias WHERE PERSONA_ID = ? AND CULTO_ID = ?");
                                    $stmt_asistencia->execute([$persona['ID'], $culto_id]);
                                    $asistio = $stmt_asistencia->fetch() ? true : false;
                                    
                                    // Determinar el color de la fila según la familia
                                    $familiaPersona = $persona['FAMILIA'] ?? '';
                                    if ($familiaPersona !== $familiaActual) {
                                        $familiaActual = $familiaPersona;
                                        $colorAlternado = !$colorAlternado;
                                    }
                                    
                                    // Determinar el color hexadecimal directamente
                                    $colorHex = '';
                                    if ($familiaPersona === '') {
                                        // Sin familia: alternar gris claro y blanco
                                        $colorHex = $colorAlternado ? '#f8f9fa' : '#ffffff';
                                    } else {
                                        // Con familia: alternar verde claro y amarillo claro
                                        $colorHex = $colorAlternado ? '#d1ecf1' : '#fff3cd';
                                    }
                                    
                                    echo "<tr style='background-color: $colorHex !important;' data-debug='Familia: $familiaPersona, Color: $colorHex, Alternado: " . ($colorAlternado ? 'true' : 'false') . "'>";
                                    echo "<td class='text-center'>
                                            <input type='checkbox' name='asistencias[]' value='" . $persona['ID'] . "' " . ($asistio ? 'checked' : '') . " class='form-check-input checkbox-asistencia'>
                                          </td>";
                                    
                                    // Columnas para pantallas grandes (desktop)
                                    echo "<td class='d-none d-md-table-cell'>" . $persona['NOMBRES'] . "</td>";
                                    echo "<td class='d-none d-md-table-cell'>" . $persona['APELLIDO_PATERNO'] . "</td>";
                                    echo "<td class='d-none d-md-table-cell'>" . ($persona['FAMILIA'] ?? '-') . "</td>";
                                    echo "<td class='d-none d-md-table-cell'>" . ($persona['grupo_familiar'] ?? '') . "</td>";
                                    
                                    // Columna Ver para todas las pantallas
                                    $tieneImagen = !empty($persona['URL_IMAGEN']);
                                    $icono = $tieneImagen ? 'fa-image' : 'fa-user';
                                    $claseBoton = $tieneImagen ? 'btn-info' : 'btn-secondary';
                                    $textoBoton = $tieneImagen ? 'Ver Foto' : 'Ver Datos';
                                    
                                    echo "<td class='text-center'>
                                            <button type='button' class='btn btn-sm $claseBoton' 
                                                    onclick='verPersonaAsistencia(" . $persona['ID'] . ")'
                                                    title='$textoBoton'>
                                                <i class='fas $icono'></i>
                                            </button>
                                          </td>";
                                    
                                    // Columna móvil que combina toda la información
                                    echo "<td class='d-table-cell d-md-none'>
                                            <div class='fw-bold'>" . $persona['NOMBRES'] . " " . $persona['APELLIDO_PATERNO'] . "</div>
                                            <div class='small text-muted'>
                                                <i class='fas fa-home me-1'></i>" . ($persona['grupo_familiar'] ?? '') . "
                                            </div>
                                            <div class='small text-muted'>
                                                <i class='fas fa-users me-1'></i>" . ($persona['FAMILIA'] ?? '') . "
                                            </div>
                                          </td>";
                                    
                                    echo "</tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='6'>Error al cargar personas: " . $e->getMessage() . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <script>
                // Inicializar funcionalidades después de cargar la página
                document.addEventListener('DOMContentLoaded', function() {
                    // Definir cultoId globalmente
                    window.cultoId = <?php echo $culto_id ?: 'null'; ?>;
                    console.log('🎯 CultoId definido:', window.cultoId);
                    
                    setTimeout(function() {
                        sobrescribirVariablesBootstrap();
                        aplicarEstilosEncabezado();
                        inicializarAutocompletado();
                    }, 50);
                });
                
                // Función para sobrescribir variables CSS de Bootstrap
                function sobrescribirVariablesBootstrap() {
                    const filas = document.querySelectorAll('.table-asistencias tbody tr');
                    
                    filas.forEach(fila => {
                        const debugInfo = fila.getAttribute('data-debug');
                        if (debugInfo) {
                            if (debugInfo.includes('Color: #d1ecf1')) {
                                fila.style.setProperty('--bs-table-bg', '#d1ecf1', 'important');
                                fila.style.setProperty('--bs-table-striped-bg', '#d1ecf1', 'important');
                                fila.style.setProperty('--bs-table-hover-bg', '#d1ecf1', 'important');
                                fila.style.setProperty('--bs-table-active-bg', '#d1ecf1', 'important');
                            } else if (debugInfo.includes('Color: #fff3cd')) {
                                fila.style.setProperty('--bs-table-bg', '#fff3cd', 'important');
                                fila.style.setProperty('--bs-table-striped-bg', '#fff3cd', 'important');
                                fila.style.setProperty('--bs-table-hover-bg', '#fff3cd', 'important');
                                fila.style.setProperty('--bs-table-active-bg', '#fff3cd', 'important');
                            } else if (debugInfo.includes('Color: #f8f9fa')) {
                                fila.style.setProperty('--bs-table-bg', '#f8f9fa', 'important');
                                fila.style.setProperty('--bs-table-striped-bg', '#f8f9fa', 'important');
                                fila.style.setProperty('--bs-table-hover-bg', '#f8f9fa', 'important');
                                fila.style.setProperty('--bs-table-active-bg', '#f8f9fa', 'important');
                            } else if (debugInfo.includes('Color: #ffffff')) {
                                fila.style.setProperty('--bs-table-bg', '#ffffff', 'important');
                                fila.style.setProperty('--bs-table-striped-bg', '#ffffff', 'important');
                                fila.style.setProperty('--bs-table-hover-bg', '#ffffff', 'important');
                                fila.style.setProperty('--bs-table-active-bg', '#ffffff', 'important');
                            }
                        }
                    });
                    
                    console.log('Variables CSS de Bootstrap sobrescritas');
                }
                
                // Inicializar autocompletado
                function inicializarAutocompletado() {
                    const nombresInput = document.getElementById('nombres');
                    const apellidosInput = document.getElementById('apellidos');
                    
                    console.log('Inicializando autocompletado...');
                    console.log('Datos disponibles:', datosPersonas.length);
                    
                    if (nombresInput) {
                        nombresInput.addEventListener('input', function() {
                            console.log('Buscando nombres que empiecen con:', this.value);
                            buscarSugerencias(this.value, 'nombres');
                        });
                    }
                    
                    if (apellidosInput) {
                        apellidosInput.addEventListener('input', function() {
                            console.log('Buscando apellidos que empiecen con:', this.value);
                            buscarSugerencias(this.value, 'apellidos');
                        });
                    }
                }
                
                // Función para buscar sugerencias
                function buscarSugerencias(texto, tipo) {
                    if (texto.length < 2) {
                        ocultarSugerencias(tipo);
                        return;
                    }
                    
                    // Buscar en los datos existentes
                    const sugerencias = [];
                    const textoNormalizado = normalizarTexto(texto);
                    
                    datosPersonas.forEach(persona => {
                        if (tipo === 'nombres' && persona.nombres && normalizarTexto(persona.nombres).startsWith(textoNormalizado)) {
                            sugerencias.push({
                                texto: persona.nombres,
                                subtitulo: persona.apellidoPaterno
                            });
                        } else if (tipo === 'apellidos' && persona.apellidoPaterno && normalizarTexto(persona.apellidoPaterno).startsWith(textoNormalizado)) {
                            sugerencias.push({
                                texto: persona.apellidoPaterno,
                                subtitulo: persona.nombres
                            });
                        }
                    });
                    
                    // Eliminar duplicados
                    const sugerenciasUnicas = sugerencias.filter((item, index, self) => 
                        index === self.findIndex(t => t.texto === item.texto)
                    );
                    
                    mostrarSugerencias(sugerenciasUnicas, tipo);
                }
                
                // Función para mostrar sugerencias
                function mostrarSugerencias(sugerencias, tipo) {
                    const container = document.getElementById(`sugerencias${tipo.charAt(0).toUpperCase() + tipo.slice(1)}`);
                    if (!container) return;
                    
                    if (sugerencias.length === 0) {
                        container.style.display = 'none';
                        return;
                    }
                    
                    let html = '';
                    sugerencias.slice(0, 10).forEach(sugerencia => {
                        const textoEscapado = sugerencia.texto.replace(/'/g, "\\'");
                        const subtituloEscapado = sugerencia.subtitulo ? sugerencia.subtitulo.replace(/'/g, "\\'") : '';
                        html += `
                            <div class="sugerencias-item" onclick="seleccionarSugerencia('${textoEscapado}', '${tipo}')">
                                <div class="texto-principal">${sugerencia.texto}</div>
                                <div class="texto-secundario">${sugerencia.subtitulo || ''}</div>
                            </div>
                        `;
                    });
                    
                    container.innerHTML = html;
                    container.style.display = 'block';
                    
                    // Debug para verificar que se muestren
                    console.log(`Mostrando ${sugerencias.length} sugerencias para ${tipo}:`, sugerencias);
                }
                
                // Función para ocultar sugerencias
                function ocultarSugerencias(tipo) {
                    const container = document.getElementById(`sugerencias${tipo.charAt(0).toUpperCase() + tipo.slice(1)}`);
                    if (container) {
                        container.style.display = 'none';
                        container.innerHTML = '';
                    }
                }
                
                // Función para seleccionar una sugerencia
                function seleccionarSugerencia(texto, tipo) {
                    if (tipo === 'nombres') {
                        document.getElementById('nombres').value = texto;
                    } else if (tipo === 'apellidos') {
                        document.getElementById('apellidos').value = texto;
                    }
                    ocultarSugerencias(tipo);
                }
                
                // Función para guardar nueva persona
                function guardarNuevaPersona() {
                    const form = document.getElementById('formAgregarPersona');
                    const formData = new FormData(form);
                    
                    // Validar campos requeridos
                    const nombres = formData.get('nombres').trim();
                    const apellidos = formData.get('apellidos').trim();
                    
                    if (!nombres || !apellidos) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Los campos Nombres y Apellidos son obligatorios'
                        });
                        return;
                    }
                    
                    // Preparar datos como FormData para compatibilidad
                    const datosForm = new FormData();
                    datosForm.append('action', 'agregar_persona');
                    datosForm.append('nombres', nombres);
                    datosForm.append('apellidos', apellidos);
                    datosForm.append('primeraVez', formData.get('primeraVez'));
                    datosForm.append('familia', formData.get('familia').trim());
                    datosForm.append('observaciones', formData.get('observaciones').trim());
                    datosForm.append('culto_id', <?php echo $culto_id ?: 'null'; ?>);
                    
                    // Mostrar indicador de carga
                    Swal.fire({
                        title: 'Guardando...',
                        text: 'Por favor espere',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Debug: mostrar datos que se van a enviar
                    console.log('Datos a enviar:');
                    for (let [key, value] of datosForm.entries()) {
                        console.log(key + ': ' + value);
                    }
                    
                    // Enviar datos al servidor usando FormData
                    fetch('asistencias_actions.php', {
                        method: 'POST',
                        body: datosForm
                    })
                    .then(response => {
                        console.log('Respuesta del servidor:', response);
                        console.log('Status:', response.status);
                        console.log('Headers:', response.headers);
                        return response.text();
                    })
                    .then(data => {
                        console.log('Datos recibidos del servidor:', data);
                        
                        let jsonData;
                        try {
                            jsonData = JSON.parse(data);
                        } catch (e) {
                            console.error('Error al parsear JSON:', e);
                            console.error('Respuesta del servidor:', data);
                            throw new Error('Respuesta del servidor no válida: ' + data);
                        }
                        
                        // Verificar si la sesión ha caducado
                        if (jsonData.error === 'session_expired') {
                            console.error('Sesión caducada:', jsonData.message);
                            
                            // Mostrar alerta de sesión caducada
                            Swal.fire({
                                icon: 'warning',
                                title: 'Sesión Caducada',
                                text: jsonData.message,
                                confirmButtonText: 'Ir al Login',
                                allowOutsideClick: false,
                                allowEscapeKey: false
                            }).then(() => {
                                // Redirigir al login
                                window.location.href = jsonData.redirect;
                            });
                            return;
                        }
                        
                        if (jsonData.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: 'Persona agregada correctamente'
                            }).then(() => {
                                // Cerrar modal y recargar página
                                const modal = bootstrap.Modal.getInstance(document.getElementById('modalAgregarPersona'));
                                modal.hide();
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: jsonData.message || 'Error al agregar persona'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error completo:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error de conexión: ' + error.message
                        });
                    });
                }
                
                // Función para guardar nueva visita
                function guardarNuevaVisita() {
                    const form = document.getElementById('formAgregarVisita');
                    const formData = new FormData(form);
                    
                    // Validar campos requeridos
                    const nombres = formData.get('nombres').trim();
                    const apellidos = formData.get('apellidos').trim();
                    const cultoId = formData.get('cultoId');
                    
                    if (!nombres || !apellidos || !cultoId) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Los campos Nombres, Apellidos y Culto son obligatorios'
                        });
                        return;
                    }
                    
                    // Preparar datos
                    const datosVisita = {
                        action: 'agregar_visita',
                        nombres: nombres,
                        apellidos: apellidos,
                        observaciones: formData.get('observaciones').trim(),
                        cultoId: cultoId,
                        primeraVez: formData.get('primeraVez')
                    };
                    
                    // Mostrar indicador de carga
                    Swal.fire({
                        title: 'Guardando...',
                        text: 'Por favor espere',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Enviar datos al servidor
                    fetch('visitas_actions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(datosVisita)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: 'Visita agregada y asistencia registrada correctamente'
                            }).then(() => {
                                // Cerrar modal
                                const modal = bootstrap.Modal.getInstance(document.getElementById('modalAgregarVisita'));
                                modal.hide();
                                
                                // Actualizar contador de asistencias inmediatamente
                                setTimeout(() => {
                                    actualizarContadorAsistencias();
                                }, 500);
                                
                                // Recargar página después de un breve delay para mostrar el contador actualizado
                                setTimeout(() => {
                                    location.reload();
                                }, 1000);
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'Error al guardar la visita'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error de conexión al guardar la visita'
                        });
                    });
                }
                
                // Ocultar sugerencias al hacer clic fuera
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('.sugerencias-container') && !e.target.closest('input')) {
                        ocultarSugerencias('nombres');
                        ocultarSugerencias('apellidos');
                    }
                });
                
                // Limpiar sugerencias cuando se cierre el modal (solo si existe)
                const modalAgregarPersona = document.getElementById('modalAgregarPersona');
                if (modalAgregarPersona) {
                    modalAgregarPersona.addEventListener('hidden.bs.modal', function() {
                    ocultarSugerencias('nombres');
                    ocultarSugerencias('apellidos');
                        const form = document.getElementById('formAgregarPersona');
                        if (form) {
                            form.reset();
                        }
                });
                }
                

                
                // Función para obtener el estado actual de las asistencias desde la base de datos
                function obtenerEstadoAsistenciasActual(personas) {
                    return new Promise((resolve, reject) => {
                        const cultoId = <?php echo $culto_id ?: 'null'; ?>;
                        if (!cultoId) {
                            console.error('No hay culto_id para consultar asistencias');
                            resolve({});
                            return;
                        }
                        
                        // Verificar que personas sea un array válido
                        if (!Array.isArray(personas) || personas.length === 0) {
                            console.warn('No hay personas para consultar asistencias');
                            resolve({});
                            return;
                        }
                        
                        // Extraer IDs de personas
                        const idsPersonas = personas.map(p => p.id).filter(id => id != null);
                        if (idsPersonas.length === 0) {
                            console.warn('No hay IDs válidos de personas');
                            resolve({});
                            return;
                        }
                        
                        console.log(`Consultando estado de asistencias para ${idsPersonas.length} personas en culto ${cultoId}`);
                        
                        // Crear FormData para la consulta
                        const formData = new FormData();
                        formData.append('action', 'consultar_asistencias');
                        formData.append('culto_id', cultoId);
                        formData.append('personas_ids', JSON.stringify(idsPersonas));
                        
                        fetch('asistencias_actions.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => {
                            console.log('Respuesta del servidor:', response.status, response.statusText);
                            
                            if (!response.ok) {
                                if (response.status === 500) {
                                    throw new Error('Error interno del servidor (500). Verifique los logs del servidor.');
                                } else {
                                    throw new Error(`HTTP error! status: ${response.status}`);
                                }
                            }
                            return response.text();
                        })
                        .then(data => {
                            // Verificar que la respuesta no esté vacía
                            if (!data || data.trim() === '') {
                                console.error('Respuesta vacía del servidor');
                                resolve({});
                                return;
                            }
                            
                            try {
                                const resultado = JSON.parse(data);
                                if (resultado.success) {
                                    console.log('Estados de asistencia obtenidos:', resultado.asistencias);
                                    resolve(resultado.asistencias);
                                } else {
                                    console.warn('Error al consultar asistencias:', resultado.message);
                                    resolve({});
                                }
                            } catch (e) {
                                console.error('Error al parsear respuesta de asistencias:', e);
                                console.error('Respuesta del servidor:', data);
                                resolve({});
                            }
                        })
                        .catch(error => {
                            console.error('Error al consultar asistencias:', error);
                            resolve({});
                        });
                    });
                }
                

                

                

                

                

                
                // Función para guardar asistencia automáticamente
                function guardarAsistenciaAutomatica(personaId, asistio, cultoId) {
                    console.log(`Iniciando guardado automático: Persona ${personaId}, Asistio: ${asistio}, Culto: ${cultoId}`);
                    
                    const checkbox = document.querySelector(`input[value="${personaId}"]`);
                    if (!checkbox) {
                        console.error(`No se encontró checkbox para persona ${personaId}`);
                        return;
                    }
                    
                    // Agregar clase de guardando
                    checkbox.classList.add('guardando');
                    console.log(`Checkbox marcado como guardando:`, checkbox);
                    
                    const datos = new FormData();
                    datos.append('action', 'guardar_asistencia_individual');
                    datos.append('persona_id', personaId);
                    datos.append('culto_id', cultoId);
                    datos.append('asistio', asistio ? '1' : '0');
                    
                    // Debug: mostrar datos que se envían
                    console.log('Datos a enviar:');
                    for (let [key, value] of datos.entries()) {
                        console.log(`${key}: ${value}`);
                    }
                    
                    console.log('Enviando petición a asistencias_actions.php...');
                    
                    fetch('asistencias_actions.php', {
                        method: 'POST',
                        body: datos
                    })
                    .then(response => {
                        console.log('Respuesta recibida:', response);
                        console.log('Status:', response.status, response.statusText);
                        console.log('Headers:', response.headers);
                        
                        if (!response.ok) {
                            if (response.status === 500) {
                                throw new Error('Error interno del servidor (500). Verifique los logs del servidor.');
                            } else {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                        }
                        
                        return response.text();
                    })
                    .then(data => {
                        console.log('Datos de respuesta:', data);
                        
                        // Verificar que la respuesta no esté vacía
                        if (!data || data.trim() === '') {
                            console.error('Respuesta vacía del servidor al guardar asistencia');
                            checkbox.checked = !asistio;
                            checkbox.classList.remove('guardando');
                            return;
                        }
                        
                        try {
                            const resultado = JSON.parse(data);
                            console.log('Resultado parseado:', resultado);
                            
                            // Verificar si la sesión ha caducado
                            if (resultado.error === 'session_expired') {
                                console.error('Sesión caducada:', resultado.message);
                                checkbox.classList.remove('guardando');
                                
                                // Mostrar alerta de sesión caducada
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Sesión Caducada',
                                    text: resultado.message,
                                    confirmButtonText: 'Ir al Login',
                                    allowOutsideClick: false,
                                    allowEscapeKey: false
                                }).then(() => {
                                    // Redirigir al login
                                    window.location.href = resultado.redirect;
                                });
                                return;
                            }
                            
                            if (resultado.success) {
                                console.log('Asistencia guardada exitosamente');
                                
                                // Actualizar el estado de asistencia en los datos locales
                                if (window.datosPersonas) {
                                    const personaIndex = window.datosPersonas.findIndex(p => p.id == personaId);
                                    if (personaIndex !== -1) {
                                        window.datosPersonas[personaIndex].asistio = asistio;
                                    }
                                }
                                
                                // Remover clase de guardando y agregar indicador de éxito
                                checkbox.classList.remove('guardando');
                                checkbox.classList.add('guardado');
                                
                                // Actualizar contador de asistencias (con manejo de errores)
                                try {
                                    // Actualizar inmediatamente para mostrar el cambio
                                    actualizarContadorAsistencias();
                                } catch (e) {
                                    console.warn('Error al actualizar contador:', e);
                                }
                                
                                // Remover indicador de éxito después de la animación
                                setTimeout(() => {
                                    checkbox.classList.remove('guardado');
                                }, 500);
                            } else {
                                console.error('Error al guardar asistencia:', resultado.message);
                                // Revertir el checkbox si hay error
                                checkbox.checked = !asistio;
                                checkbox.classList.remove('guardando');
                            }
                        } catch (e) {
                            console.error('Error al parsear JSON:', e);
                            console.error('Respuesta del servidor:', data);
                            checkbox.checked = !asistio;
                            checkbox.classList.remove('guardando');
                        }
                    })
                    .catch(error => {
                        console.error('Error de conexión:', error);
                        // Revertir el checkbox si hay error
                        checkbox.checked = !asistio;
                        checkbox.classList.remove('guardando');
                    });
                }
                
                // Función para inicializar el guardado automático de asistencias
                function inicializarGuardadoAutomatico() {
                    const checkboxes = document.querySelectorAll('input[name="asistencias[]"]');
                    const cultoId = <?php echo $culto_id ?: 'null'; ?>;
                    
                    console.log('Inicializando guardado automático...');
                    console.log('Checkboxes encontrados:', checkboxes.length);
                    console.log('Culto ID:', cultoId);
                    
                    if (!cultoId) {
                        console.error('No hay culto_id, no se puede inicializar guardado automático');
                        return;
                    }
                    
                    if (checkboxes.length === 0) {
                        console.warn('No se encontraron checkboxes, esperando a que se carguen...');
                        // Reintentar en 200ms si no hay checkboxes
                        setTimeout(() => {
                            inicializarGuardadoAutomatico();
                        }, 200);
                        return;
                    }
                    
                    // Limpiar event listeners previos de todos los checkboxes
                    checkboxes.forEach(checkbox => {
                        if (checkbox.asistenciaHandler) {
                            checkbox.removeEventListener('change', checkbox.asistenciaHandler);
                            delete checkbox.asistenciaHandler;
                        }
                    });
                    
                    checkboxes.forEach((checkbox, index) => {
                        if (!checkbox || !checkbox.value) {
                            console.warn(`Checkbox ${index + 1} no válido:`, checkbox);
                            return;
                        }
                        
                        console.log(`Configurando checkbox ${index + 1}:`, checkbox.value, checkbox.checked);
                        
                        // Crear nuevo handler
                        checkbox.asistenciaHandler = function() {
                            const personaId = this.value;
                            const asistio = this.checked;
                            
                            console.log(`Checkbox cambiado: Persona ${personaId}, Asistio: ${asistio}`);
                            
                            // Guardar inmediatamente
                            guardarAsistenciaAutomatica(personaId, asistio, cultoId);
                        };
                        
                        // Agregar evento
                        checkbox.addEventListener('change', checkbox.asistenciaHandler);
                    });
                    
                    // Marcar como inicializado
                    window.guardadoAutomaticoInicializado = true;
                    console.log('Guardado automático inicializado correctamente');
                    console.log(`✅ ${checkboxes.length} checkboxes configurados con event listeners`);
                }
                

                

                
                // Función para actualizar contador de asistencias
                function actualizarContadorAsistencias() {
                    console.log('🔄 Actualizando contador de asistencias...');
                    console.log('📊 Estado de window.datosPersonas:', window.datosPersonas ? window.datosPersonas.length : 'undefined');
                    
                    // Usar los datos reales de la base de datos si están disponibles
                    let totalPersonas = 0;
                    let asistenciasMarcadas = 0;
                    
                    if (window.datosPersonas && window.datosPersonas.length > 0) {
                        // El total de personas es el número real en la base de datos (todas las páginas)
                        totalPersonas = window.datosPersonas.length;
                        
                        // Contar TODAS las asistencias marcadas de TODAS las páginas
                        asistenciasMarcadas = window.datosPersonas.filter(persona => persona.asistio).length;
                        
                        console.log(`✅ Contador actualizado con datos de BD: ${asistenciasMarcadas}/${totalPersonas} personas (${Math.round((asistenciasMarcadas / totalPersonas) * 100)}%)`);
                        console.log(`📋 Muestra de datos:`, window.datosPersonas.slice(0, 3).map(p => ({ id: p.id, nombres: p.nombres, asistio: p.asistio })));
                    } else {
                        // Fallback: contar checkboxes del DOM (solo para casos de emergencia)
                    const checkboxes = document.querySelectorAll('input[name="asistencias[]"]:checked');
                        const totalCheckboxes = document.querySelectorAll('input[name="asistencias[]"]').length;
                        totalPersonas = totalCheckboxes;
                        asistenciasMarcadas = checkboxes.length;
                        console.warn('⚠️ Usando fallback del DOM para el contador');
                        console.warn('⚠️ window.datosPersonas no está disponible o está vacío');
                    }
                    
                    // Obtener el conteo de visitas del culto actual
                    obtenerConteoVisitasCulto(totalPersonas, asistenciasMarcadas);
                }
                
                // Función para obtener el conteo de visitas del culto
                function obtenerConteoVisitasCulto(totalPersonas, asistenciasPersonas) {
                    const cultoId = window.cultoId;
                    console.log('🔍 Debug obtenerConteoVisitasCulto:');
                    console.log('  - cultoId:', cultoId);
                    console.log('  - totalPersonas:', totalPersonas);
                    console.log('  - asistenciasPersonas:', asistenciasPersonas);
                    
                    if (!cultoId) {
                        console.warn('⚠️ No se encontró cultoId para obtener visitas');
                        mostrarContador(totalPersonas, asistenciasPersonas, 0);
                        return;
                    }
                    
                    const formData = new FormData();
                    formData.append('action', 'obtener_conteo_visitas_culto');
                    formData.append('culto_id', cultoId);
                    
                    console.log('📡 Enviando petición para obtener visitas del culto:', cultoId);
                    
                    fetch('asistencias_actions.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        console.log('📡 Respuesta recibida:', response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log('📡 Datos recibidos:', data);
                        if (data.success) {
                            const totalVisitas = data.total_visitas || 0;
                            console.log(`📊 Visitas del culto ${cultoId}: ${totalVisitas}`);
                            mostrarContador(totalPersonas, asistenciasPersonas, totalVisitas);
                        } else {
                            console.error('❌ Error al obtener conteo de visitas:', data.message);
                            mostrarContador(totalPersonas, asistenciasPersonas, 0);
                        }
                    })
                    .catch(error => {
                        console.error('❌ Error en la petición de visitas:', error);
                        mostrarContador(totalPersonas, asistenciasPersonas, 0);
                    });
                }
                
                // Función para mostrar el contador con personas y visitas
                function mostrarContador(totalPersonas, asistenciasPersonas, totalVisitas) {
                    console.log('🎯 Debug mostrarContador:');
                    console.log('  - totalPersonas:', totalPersonas);
                    console.log('  - asistenciasPersonas:', asistenciasPersonas);
                    console.log('  - totalVisitas:', totalVisitas);
                    
                    const totalAsistentes = asistenciasPersonas + totalVisitas;
                    const totalPersonasYVisitas = totalPersonas + totalVisitas;
                    
                    console.log('  - totalAsistentes:', totalAsistentes);
                    console.log('  - totalPersonasYVisitas:', totalPersonasYVisitas);
                    
                    // Buscar o crear el contador
                    let contador = document.getElementById('contadorAsistencias');
                    if (!contador) {
                        contador = document.createElement('div');
                        contador.id = 'contadorAsistencias';
                        contador.className = 'ms-3';
                        
                        // Insertar al lado derecho del título de asistencias
                        const cardHeader = document.querySelector('.card-header.d-flex.justify-content-between.align-items-center');
                        if (cardHeader) {
                            // Buscar si ya existe un contenedor para el contador
                            let contadorContainer = document.getElementById('contadorContainer');
                            if (!contadorContainer) {
                                contadorContainer = document.createElement('div');
                                contadorContainer.id = 'contadorContainer';
                                contadorContainer.className = 'd-flex align-items-center';
                                cardHeader.appendChild(contadorContainer);
                            }
                            contadorContainer.appendChild(contador);
                        }
                    }
                    
                    // Mostrar el contador con información de personas y visitas
                    if (totalVisitas > 0) {
                        contador.innerHTML = `
                            <small class="text-success">
                                <i class="fas fa-check-circle"></i> ${totalAsistentes} de ${totalPersonasYVisitas} personas han marcado asistencia
                                <br><small class="text-info">(${asistenciasPersonas} personas + ${totalVisitas} visitas)</small>
                            </small>
                        `;
                        console.log('✅ Contador mostrado CON visitas:', `${totalAsistentes}/${totalPersonasYVisitas} (${asistenciasPersonas} personas + ${totalVisitas} visitas)`);
                    } else {
                        contador.innerHTML = `
                            <small class="text-success">
                                <i class="fas fa-check-circle"></i> ${asistenciasPersonas} de ${totalPersonas} personas han marcado asistencia
                            </small>
                        `;
                        console.log('✅ Contador mostrado SIN visitas:', `${asistenciasPersonas}/${totalPersonas} personas`);
                    }
                    
                    console.log('🎯 Contador final:', `${totalAsistentes}/${totalPersonasYVisitas} (${asistenciasPersonas} personas + ${totalVisitas} visitas)`);
                }
                

                
                // Inicializar guardado automático cuando se cargue la página
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(function() {
                        inicializarGuardadoAutomatico();
                        actualizarContadorAsistencias();
                    }, 100);
                });
                </script>
                
                <!-- Funciones globales para abrir modales -->
                <script>
                // Función global para abrir modal de agregar persona
                function abrirModalPersona() {
                    console.log('Abriendo modal de agregar persona...');
                    const modalElement = document.getElementById('modalAgregarPersona');
                    if (modalElement) {
                        const modal = new bootstrap.Modal(modalElement);
                        modal.show();
                        console.log('Modal de agregar persona abierto');
                    } else {
                        console.error('No se encontró el modal modalAgregarPersona');
                    }
                }
                
                // Función global para abrir modal de agregar visita
                function abrirModalVisita() {
                    console.log('Abriendo modal de agregar visita...');
                    const modalElement = document.getElementById('modalAgregarVisita');
                    if (modalElement) {
                        // Cargar cultos antes de abrir el modal
                        cargarCultosParaVisita();
                        
                        const modal = new bootstrap.Modal(modalElement);
                        modal.show();
                        console.log('Modal de agregar visita abierto');
                    } else {
                        console.error('No se encontró el modal modalAgregarVisita');
                    }
                }
                
                // Función global para abrir modal de múltiples visitas
                function abrirModalMultiplesVisitas() {
                    console.log('Abriendo modal de múltiples visitas...');
                    console.log('Buscando modal con ID: modalMultiplesVisitas');
                    
                    const modalElement = document.getElementById('modalMultiplesVisitas');
                    console.log('Modal encontrado:', modalElement);
                    
                    if (modalElement) {
                        console.log('Modal existe, verificando elementos internos:');
                        console.log('totalVisitas:', document.getElementById('totalVisitas'));
                        console.log('cantidadHombres:', document.getElementById('cantidadHombres'));
                        console.log('cantidadMujeres:', document.getElementById('cantidadMujeres'));
                        console.log('cantidadNinos:', document.getElementById('cantidadNinos'));
                        console.log('alertaDistribucion:', document.getElementById('alertaDistribucion'));
                        console.log('btnGuardarMultiples:', document.getElementById('btnGuardarMultiples'));
                        
                        // Cargar cultos antes de abrir el modal
                        cargarCultosParaMultiplesVisitas();
                        
                        const modal = new bootstrap.Modal(modalElement);
                        
                        // Agregar event listener para cuando el modal se muestre completamente
                        modalElement.addEventListener('shown.bs.modal', function() {
                            console.log('Modal completamente mostrado, verificando elementos:');
                            console.log('totalVisitas:', document.getElementById('totalVisitas') ? '✅' : '❌');
                            console.log('cantidadHombres:', document.getElementById('cantidadHombres') ? '✅' : '❌');
                            console.log('cantidadMujeres:', document.getElementById('cantidadMujeres') ? '✅' : '❌');
                            console.log('cantidadNinos:', document.getElementById('cantidadNinos') ? '✅' : '❌');
                            console.log('alertaDistribucion:', document.getElementById('alertaDistribucion') ? '✅' : '❌');
                            console.log('btnGuardarMultiples:', document.getElementById('btnGuardarMultiples') ? '✅' : '❌');
                        });
                        
                        modal.show();
                        console.log('Modal de múltiples visitas abierto');
                    } else {
                        console.error('No se encontró el modal modalMultiplesVisitas');
                        console.error('Elementos con "modal" en el ID:', document.querySelectorAll('[id*="modal"]'));
                    }
                }
                
                // Función para cargar cultos en el modal de múltiples visitas
                function cargarCultosParaMultiplesVisitas() {
                    const selectCulto = document.getElementById('cultoIdMultiples');
                    if (!selectCulto) return;
                    
                    // Mostrar estado de carga
                    selectCulto.innerHTML = '<option value="">Cargando cultos...</option>';
                    
                    // Obtener culto actual desde la URL o parámetros
                    const cultoActual = <?php echo $culto_id ?: 'null'; ?>;
                    
                    // Cargar cultos via AJAX
                    fetch('asistencias_actions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=obtener_cultos_activos'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            selectCulto.innerHTML = '<option value="">Seleccione un culto</option>';
                            
                            data.cultos.forEach(culto => {
                                const option = document.createElement('option');
                                option.value = culto.ID;
                                option.textContent = `${culto.TIPO_CULTO} - ${culto.FECHA_FORMATEADA}`;
                                
                                // Seleccionar el culto actual por defecto
                                if (cultoActual && culto.ID == cultoActual) {
                                    option.selected = true;
                                }
                                
                                selectCulto.appendChild(option);
                            });
                            
                            console.log('Cultos cargados correctamente para múltiples visitas:', data.cultos.length);
                        } else {
                            selectCulto.innerHTML = '<option value="">Error al cargar cultos</option>';
                            console.error('Error al cargar cultos:', data.message);
                        }
                    })
                    .catch(error => {
                        selectCulto.innerHTML = '<option value="">Error de conexión</option>';
                        console.error('Error de conexión:', error);
                    });
                }
                
                // Función para actualizar las opciones de distribución cuando cambia el total
                function actualizarDistribucion() {
                    console.log('actualizarDistribucion() ejecutándose...');
                    
                    const totalVisitasElement = document.getElementById('totalVisitas');
                    const selectHombres = document.getElementById('cantidadHombres');
                    const selectMujeres = document.getElementById('cantidadMujeres');
                    const selectNinos = document.getElementById('cantidadNinos');
                    
                    console.log('Elementos en actualizarDistribucion:');
                    console.log('totalVisitasElement:', totalVisitasElement);
                    console.log('selectHombres:', selectHombres);
                    console.log('selectMujeres:', selectMujeres);
                    console.log('selectNinos:', selectNinos);
                    
                    // Verificar que todos los elementos existan
                    if (!totalVisitasElement || !selectHombres || !selectMujeres || !selectNinos) {
                        console.error('Elementos del formulario no encontrados en actualizarDistribucion');
                        console.error('totalVisitasElement:', totalVisitasElement ? 'OK' : 'MISSING');
                        console.error('selectHombres:', selectHombres ? 'OK' : 'MISSING');
                        console.error('selectMujeres:', selectMujeres ? 'OK' : 'MISSING');
                        console.error('selectNinos:', selectNinos ? 'OK' : 'MISSING');
                        return;
                    }
                    
                    const totalVisitas = parseInt(totalVisitasElement.value) || 0;
                    
                    // Limpiar opciones existentes
                    selectHombres.innerHTML = '';
                    selectMujeres.innerHTML = '';
                    selectNinos.innerHTML = '';
                    
                    // Crear opciones de 0 a totalVisitas para cada select
                    for (let i = 0; i <= totalVisitas; i++) {
                        const optionHombres = document.createElement('option');
                        optionHombres.value = i;
                        optionHombres.textContent = i;
                        selectHombres.appendChild(optionHombres);
                        
                        const optionMujeres = document.createElement('option');
                        optionMujeres.value = i;
                        optionMujeres.textContent = i;
                        selectMujeres.appendChild(optionMujeres);
                        
                        const optionNinos = document.createElement('option');
                        optionNinos.value = i;
                        optionNinos.textContent = i;
                        selectNinos.appendChild(optionNinos);
                    }
                    
                    // Resetear valores
                    selectHombres.value = '0';
                    selectMujeres.value = '0';
                    selectNinos.value = '0';
                    
                    // Validar distribución
                    validarDistribucion();
                }
                
                // Función para validar que la suma de distribución sea igual al total
                function validarDistribucion() {
                    console.log('validarDistribucion() ejecutándose...');
                    
                    const totalVisitasElement = document.getElementById('totalVisitas');
                    const hombresElement = document.getElementById('cantidadHombres');
                    const mujeresElement = document.getElementById('cantidadMujeres');
                    const ninosElement = document.getElementById('cantidadNinos');
                    const alerta = document.getElementById('alertaDistribucion');
                    const btnGuardar = document.getElementById('btnGuardarMultiples');
                    
                    console.log('Elementos encontrados:');
                    console.log('totalVisitasElement:', totalVisitasElement);
                    console.log('hombresElement:', hombresElement);
                    console.log('mujeresElement:', mujeresElement);
                    console.log('ninosElement:', ninosElement);
                    console.log('alerta:', alerta);
                    console.log('btnGuardar:', btnGuardar);
                    
                    // Verificar que los elementos principales existan (alerta es opcional)
                    if (!totalVisitasElement || !hombresElement || !mujeresElement || !ninosElement || !btnGuardar) {
                        console.error('Elementos principales del formulario no encontrados');
                        console.error('totalVisitasElement:', totalVisitasElement ? 'OK' : 'MISSING');
                        console.error('hombresElement:', hombresElement ? 'OK' : 'MISSING');
                        console.error('mujeresElement:', mujeresElement ? 'OK' : 'MISSING');
                        console.error('ninosElement:', ninosElement ? 'OK' : 'MISSING');
                        console.error('btnGuardar:', btnGuardar ? 'OK' : 'MISSING');
                        return;
                    }
                    
                    // El elemento alerta es opcional, solo mostrar advertencia si no existe
                    if (!alerta) {
                        console.warn('Elemento alertaDistribucion no encontrado, continuando sin alerta');
                    }
                    
                    const totalVisitas = parseInt(totalVisitasElement.value) || 0;
                    const hombres = parseInt(hombresElement.value) || 0;
                    const mujeres = parseInt(mujeresElement.value) || 0;
                    const ninos = parseInt(ninosElement.value) || 0;
                    const suma = hombres + mujeres + ninos;
                    
                    if (totalVisitas > 0) {
                        if (suma === totalVisitas) {
                            if (alerta) alerta.style.display = 'none';
                            btnGuardar.disabled = false;
                        } else {
                            if (alerta) alerta.style.display = 'block';
                            btnGuardar.disabled = true;
                        }
                    } else {
                        if (alerta) alerta.style.display = 'none';
                        btnGuardar.disabled = true;
                    }
                }
                
                // Función para guardar múltiples visitas
                function guardarMultiplesVisitas() {
                    const totalVisitasElement = document.getElementById('totalVisitas');
                    const hombresElement = document.getElementById('cantidadHombres');
                    const mujeresElement = document.getElementById('cantidadMujeres');
                    const ninosElement = document.getElementById('cantidadNinos');
                    const cultoIdElement = document.getElementById('cultoIdMultiples');
                    const observacionesElement = document.getElementById('observacionesMultiples');
                    
                    // Verificar que todos los elementos existan
                    if (!totalVisitasElement || !hombresElement || !mujeresElement || !ninosElement || !cultoIdElement || !observacionesElement) {
                        console.error('Elementos del formulario no encontrados en guardarMultiplesVisitas');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al acceder a los elementos del formulario'
                        });
                        return;
                    }
                    
                    const totalVisitas = parseInt(totalVisitasElement.value) || 0;
                    const hombres = parseInt(hombresElement.value) || 0;
                    const mujeres = parseInt(mujeresElement.value) || 0;
                    const ninos = parseInt(ninosElement.value) || 0;
                    const cultoId = cultoIdElement.value;
                    const observaciones = observacionesElement.value.trim();
                    
                    // Validar campos requeridos
                    if (!totalVisitas || !cultoId) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Por favor complete todos los campos requeridos'
                        });
                        return;
                    }
                    
                    // Validar distribución
                    if (hombres + mujeres + ninos !== totalVisitas) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'La suma de hombres, mujeres y niños debe ser igual al total de visitas'
                        });
                        return;
                    }
                    
                    // Mostrar confirmación
                    Swal.fire({
                        title: '¿Confirmar creación de visitas?',
                        text: `Se crearán ${totalVisitas} visitas: ${hombres} hombres, ${mujeres} mujeres, ${ninos} niños`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, crear',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Crear datos para enviar
                            const datosVisitas = {
                                action: 'agregar_multiples_visitas',
                                culto_id: cultoId,
                                total_visitas: totalVisitas,
                                hombres: hombres,
                                mujeres: mujeres,
                                ninos: ninos,
                                observaciones: observaciones
                            };
                            
                            // Enviar datos
                            fetch('asistencias_actions.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: new URLSearchParams(datosVisitas)
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Éxito',
                                        text: `Se crearon ${data.visitas_creadas} visitas correctamente`
                                    }).then(() => {
                                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalMultiplesVisitas'));
                                        modal.hide();
                                        
                                        // Actualizar contador de asistencias inmediatamente
                                        setTimeout(() => {
                                            actualizarContadorAsistencias();
                                        }, 500);
                                        
                                        // Recargar página después de un breve delay para mostrar el contador actualizado
                                        setTimeout(() => {
                                            location.reload();
                                        }, 1000);
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: data.message || 'Error al crear las visitas'
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Error de conexión al crear las visitas'
                                });
                            });
                        }
                    });
                }
                
                // Función para cargar cultos en el modal de visitas
                function cargarCultosParaVisita() {
                    const selectCulto = document.getElementById('cultoIdVisita');
                    if (!selectCulto) return;
                    
                    // Mostrar estado de carga
                    selectCulto.innerHTML = '<option value="">Cargando cultos...</option>';
                    
                    // Obtener culto actual desde la URL o parámetros
                    const cultoActual = <?php echo $culto_id ?: 'null'; ?>;
                    
                    // Cargar cultos via AJAX
                    fetch('asistencias_actions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=obtener_cultos_activos'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            selectCulto.innerHTML = '<option value="">Seleccione un culto</option>';
                            
                            data.cultos.forEach(culto => {
                                const option = document.createElement('option');
                                option.value = culto.ID;
                                option.textContent = `${culto.TIPO_CULTO} - ${culto.FECHA_FORMATEADA}`;
                                
                                // Seleccionar el culto actual por defecto
                                if (cultoActual && culto.ID == cultoActual) {
                                    option.selected = true;
                                }
                                
                                selectCulto.appendChild(option);
                            });
                            
                            console.log('Cultos cargados correctamente:', data.cultos.length);
                        } else {
                            selectCulto.innerHTML = '<option value="">Error al cargar cultos</option>';
                            console.error('Error al cargar cultos:', data.message);
                        }
                    })
                    .catch(error => {
                        selectCulto.innerHTML = '<option value="">Error de conexión</option>';
                        console.error('Error de conexión:', error);
                    });
                }
                </script>
                
                <!-- Script de debug para verificar funciones -->
                <script>
                // Verificar que las funciones estén disponibles
                document.addEventListener('DOMContentLoaded', function() {
                    console.log('🔍 Verificando funciones de modales...');
                    console.log('abrirModalPersona:', typeof abrirModalPersona);
                    console.log('abrirModalVisita:', typeof abrirModalVisita);
                    
                    if (typeof abrirModalPersona === 'function') {
                        console.log('✅ abrirModalPersona está disponible');
                    } else {
                        console.error('❌ abrirModalPersona NO está disponible');
                    }
                    
                    if (typeof abrirModalVisita === 'function') {
                        console.log('✅ abrirModalVisita está disponible');
                    } else {
                        console.error('❌ abrirModalVisita NO está disponible');
                    }
                    
                    // Verificar que los modales existan
                    const modalPersona = document.getElementById('modalAgregarPersona');
                    const modalVisita = document.getElementById('modalAgregarVisita');
                    
                    console.log('Modal Persona:', modalPersona ? '✅ Existe' : '❌ No existe');
                    console.log('Modal Visita:', modalVisita ? '✅ Existe' : '❌ No existe');
                });
                </script>
                
                <!-- Paginación -->
                <div class="row mt-3">
                    <!-- Navegación de páginas - Movida a la izquierda -->
                    <div class="col-12 col-md-4 mb-2 mb-md-0">
                        <nav aria-label="Navegación de páginas">
                            <ul class="pagination pagination-sm justify-content-start mb-0" id="paginacion">
                                <!-- La paginación se generará dinámicamente -->
                            </ul>
                        </nav>
                    </div>
                    
                    <!-- Información de registros -->
                    <div class="col-12 col-md-4 text-center">
                        <small class="text-muted" id="infoRegistros">
                            <!-- La información se generará dinámicamente -->
                        </small>
                    </div>
                    
                    <!-- Selector de items por página - Movido a la derecha -->
                    <div class="col-12 col-md-4">
                        <div class="d-flex align-items-center justify-content-end">
                            <label class="me-2 d-none d-sm-inline">Mostrar:</label>
                            <label class="me-2 d-inline d-sm-none">Items:</label>
                            <select class="form-select form-select-sm me-2" id="itemsPorPagina" onchange="cambiarItemsPorPagina()" style="width: auto;">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100" selected>100</option>
                            </select>
                            <span class="text-muted d-none d-md-inline">registros por página</span>
                        </div>
                    </div>
                </div>
                
                <!-- Botones flotantes para agregar -->
                <div class="botones-flotantes" id="botonesFlotantes">
                    <button type="button" class="btn btn-primary btn-lg" onclick="abrirModalPersona()">
                        <i class="fas fa-user"></i> Agregar Persona
                    </button>
                    <button type="button" class="btn btn-info btn-lg" onclick="abrirModalVisita()">
                        <i class="fas fa-user-plus"></i> Agregar Visita
                    </button>
                    <button type="button" class="btn btn-success btn-lg" onclick="abrirModalMultiplesVisitas()">
                        <i class="fas fa-users"></i> Múltiples Visitas
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm btn-toggle" onclick="toggleBotonesFlotantes()" title="Colapsar/Expandir">
                        <i class="fas fa-chevron-up" id="iconToggle"></i>
                    </button>
                </div>
                
                <div class="text-center mt-3">
                    <div class="d-grid gap-2 d-md-block">
                        <a href="asistencias.php" class="btn btn-secondary btn-lg w-100 w-md-auto">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Las asistencias se guardan automáticamente al marcar/desmarcar los checkboxes
                        </small>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <!-- Lista de Cultos para Seleccionar -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Listado de Cultos para Asistencia</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="d-none d-md-table-cell">Fecha</th>
                            <th class="d-none d-md-table-cell">Hora</th>
                            <th class="d-none d-md-table-cell">Tipo</th>
                            <th class="d-none d-md-table-cell">Descripción</th>
                            <th class="d-none d-md-table-cell">Asistentes</th>
                            <th class="culto-info-mobile">Información del Culto</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $pdo = conectarDB();
                            $stmt = $pdo->query("SELECT c.*, 
                                               (COUNT(DISTINCT a.PERSONA_ID) + COUNT(DISTINCT av.VISITA_ID)) as asistentes 
                                               FROM cultos c 
                                               LEFT JOIN asistencias a ON c.ID = a.CULTO_ID 
                                               LEFT JOIN asistencias_visitas av ON c.ID = av.CULTO_ID 
                                               GROUP BY c.ID 
                                               ORDER BY c.FECHA DESC, c.FECHA_CREACION DESC");
                            while ($row = $stmt->fetch()) {
                                echo "<tr>";
                                
                                // Columnas para pantallas grandes (desktop)
                                echo "<td class='d-none d-md-table-cell'>" . date('d/m/Y', strtotime($row['FECHA'])) . "</td>";
                                echo "<td class='d-none d-md-table-cell'>" . ($row['FECHA_CREACION'] ? date('H:i', strtotime($row['FECHA_CREACION'])) : '-') . "</td>";
                                echo "<td class='d-none d-md-table-cell'>" . $row['TIPO_CULTO'] . "</td>";
                                echo "<td class='d-none d-md-table-cell'>" . ($row['OBSERVACIONES'] ?? '-') . "</td>";
                                echo "<td class='d-none d-md-table-cell'>" . $row['asistentes'] . "</td>";
                                
                                // Columna móvil que combina la información del culto
                                echo "<td class='culto-info-mobile'>
                                        <div class='culto-tipo'>" . $row['TIPO_CULTO'] . "</div>
                                        <div class='culto-fecha'>
                                            <i class='fas fa-calendar me-1'></i>" . date('d/m/Y', strtotime($row['FECHA'])) . "
                                        </div>
                                        <div class='culto-hora'>
                                            <i class='fas fa-clock me-1'></i>" . ($row['FECHA_CREACION'] ? date('H:i', strtotime($row['FECHA_CREACION'])) : '--') . "
                                        </div>
                                        <div class='culto-asistentes'>
                                            <i class='fas fa-users me-1'></i>" . $row['asistentes'] . " asistentes
                                        </div>
                                      </td>";
                                
                                // Botones de acción
                                echo "<td>
                                        <div class='d-grid gap-1 d-md-block'>
                                            <a href='?culto_id=" . $row['ID'] . "' class='btn btn-sm btn-success w-100 w-md-auto' title='Tomar Asistencia'>
                                                <i class='fas fa-clipboard-check'></i> <span class='d-none d-sm-inline'>Tomar Asistencia</span>
                                            </a>
                                            <button onclick='verAsistentesCulto(" . $row['ID'] . ", \"" . addslashes($row['TIPO_CULTO']) . "\", \"" . date('d/m/Y', strtotime($row['FECHA'])) . "\")' class='btn btn-sm btn-info w-100 w-md-auto mt-1 mt-md-0' title='Ver Asistentes'>
                                                <i class='fas fa-eye'></i> <span class='d-none d-sm-inline'>Ver</span>
                                            </button>
                                        </div>
                                      </td>";
                                
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
<?php endif; ?>

<!-- Modal para Seleccionar Culto -->
<div class="modal fade" id="modalSeleccionarCulto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seleccionar Culto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Seleccione un culto de la lista para tomar asistencia.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Agregar Persona -->
<div class="modal fade" id="modalAgregarPersona" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Nueva Persona</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formAgregarPersona">
                    <div class="row">
                        <div class="col-md-6 mb-3 sugerencias-container">
                            <label for="nombres" class="form-label">Nombres *</label>
                            <input type="text" class="form-control" id="nombres" name="nombres" required 
                                   placeholder="Ingrese nombres" autocomplete="off">
                            <div id="sugerenciasNombres" class="sugerencias-list" style="display: none;"></div>
                        </div>
                        <div class="col-md-6 mb-3 sugerencias-container">
                            <label for="apellidos" class="form-label">Apellidos *</label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos" required 
                                   placeholder="Ingrese apellidos" autocomplete="off">
                            <div id="sugerenciasApellidos" class="sugerencias-list" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="primeraVez" class="form-label">¿Es primera vez?</label>
                            <select class="form-select" id="primeraVez" name="primeraVez">
                                <option value="0">No</option>
                                <option value="1">Sí</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="familia" class="form-label">Familia</label>
                            <input type="text" class="form-control" id="familia" name="familia" 
                                   placeholder="Nombre de la familia (opcional)">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="3" 
                                  placeholder="Observaciones adicionales (opcional)"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarNuevaPersona()">
                    <i class="fas fa-save"></i> Guardar Persona
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Agregar Visita -->
<div class="modal fade" id="modalAgregarVisita" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Nueva Visita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formAgregarVisita">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombresVisita" class="form-label">Nombres *</label>
                            <input type="text" class="form-control" id="nombresVisita" name="nombres" required 
                                   placeholder="Ingrese nombres de la visita">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="apellidosVisita" class="form-label">Apellidos *</label>
                            <input type="text" class="form-control" id="apellidosVisita" name="apellidos" required 
                                   placeholder="Ingrese apellidos de la visita">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="primeraVezVisita" class="form-label">¿Es primera vez?</label>
                            <select class="form-select" id="primeraVezVisita" name="primeraVez">
                                <option value="1">Sí</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cultoIdVisita" class="form-label">Culto *</label>
                            <select class="form-select" id="cultoIdVisita" name="cultoId" required>
                                <option value="">Cargando cultos...</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="observacionesVisita" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observacionesVisita" name="observaciones" rows="3" 
                                  placeholder="Observaciones adicionales (opcional)"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarNuevaVisita()">
                    <i class="fas fa-save"></i> Guardar Visita
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Agregar Múltiples Visitas -->
<div class="modal fade" id="modalMultiplesVisitas" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Múltiples Visitas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formMultiplesVisitas">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="totalVisitas" class="form-label">Total de Visitas</label>
                            <select class="form-select" id="totalVisitas" onchange="actualizarDistribucion()" required>
                                <option value="">Seleccione cantidad</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cultoIdMultiples" class="form-label">Culto</label>
                            <select class="form-select" id="cultoIdMultiples" required>
                                <option value="">Cargando cultos...</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="cantidadHombres" class="form-label">Hombres</label>
                            <select class="form-select" id="cantidadHombres" onchange="validarDistribucion()">
                                <option value="0">0</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="cantidadMujeres" class="form-label">Mujeres</label>
                            <select class="form-select" id="cantidadMujeres" onchange="validarDistribucion()">
                                <option value="0">0</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="cantidadNinos" class="form-label">Niños</label>
                            <select class="form-select" id="cantidadNinos" onchange="validarDistribucion()">
                                <option value="0">0</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="observacionesMultiples" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observacionesMultiples" rows="3" placeholder="Observaciones para todas las visitas"></textarea>
                    </div>
                    
                    <div class="alert alert-info" id="alertaDistribucion" style="display: none;">
                        <i class="fas fa-info-circle"></i> La suma de hombres, mujeres y niños debe ser igual al total de visitas seleccionado.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="guardarMultiplesVisitas()" id="btnGuardarMultiples" disabled>
                    <i class="fas fa-save"></i> Guardar Visitas
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos para botones flotantes */
.botones-flotantes {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1050;
    display: flex;
    flex-direction: column;
    gap: 10px;
    opacity: 0.9;
    transition: opacity 0.3s ease;
}

.botones-flotantes:hover {
    opacity: 1;
}

.botones-flotantes .btn {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border-radius: 50px;
    padding: 12px 20px;
    font-weight: 600;
    min-width: 180px;
    transition: all 0.3s ease;
}

.botones-flotantes .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

.botones-flotantes .btn-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
    border: none;
}

.botones-flotantes .btn-info {
    background: linear-gradient(135deg, #17a2b8, #138496);
    border: none;
}

.botones-flotantes .btn-success {
    background: linear-gradient(135deg, #28a745, #1e7e34);
    border: none;
}

.botones-flotantes .btn-toggle {
    background: linear-gradient(135deg, #6c757d, #495057);
    border: none;
    min-width: 50px;
    padding: 8px 12px;
    border-radius: 50px;
    margin-top: 5px;
}

.botones-flotantes .btn-toggle:hover {
    background: linear-gradient(135deg, #495057, #343a40);
    transform: translateY(-1px);
}

.botones-flotantes.colapsado .btn:not(.btn-toggle) {
    display: none;
}

.botones-flotantes.colapsado .btn-toggle i {
    transform: rotate(180deg);
}

/* Responsive para botones flotantes */
@media (max-width: 768px) {
    .botones-flotantes {
        bottom: 20px;
        right: 15px;
        left: 15px;
        flex-direction: row;
        justify-content: center;
        gap: 8px;
    }
    
    .botones-flotantes .btn {
        min-width: auto;
        flex: 1;
        padding: 10px 15px;
        font-size: 0.9rem;
    }
}

@media (max-width: 480px) {
    .botones-flotantes {
        bottom: 15px;
        right: 10px;
        left: 10px;
        gap: 5px;
    }
    
    .botones-flotantes .btn {
        padding: 8px 12px;
        font-size: 0.8rem;
    }
    
    .botones-flotantes .btn i {
        margin-right: 4px;
    }
}

/* Estilos para el contador de asistencias al lado del título */
#contadorContainer {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 6px;
    padding: 6px 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border: 1px solid #dee2e6;
}

#contadorAsistencias {
    font-weight: 600;
    font-size: 0.85rem;
    white-space: nowrap;
}

#contadorAsistencias .text-success {
    color: #28a745 !important;
}

#contadorAsistencias .text-info {
    color: #17a2b8 !important;
}

/* Responsive para el contador */
@media (max-width: 768px) {
    #contadorContainer {
        padding: 4px 8px;
        margin-top: 8px;
    }
    
    #contadorAsistencias {
        font-size: 0.8rem;
    }
    
    .card-header.d-flex {
        flex-direction: column;
        align-items: flex-start !important;
    }
}

/* Estilos personalizados para dispositivos móviles */
@media (max-width: 767.98px) {
    /* Optimización de tablas para móviles */
    .table-responsive {
        border: none;
    }
    
    /* Espacio normal para paginación */
    .pagination {
        margin-bottom: 1rem !important;
    }
    
    .table-sm td, .table-sm th {
        padding: 0.5rem 0.25rem;
        font-size: 0.875rem;
    }
    
    /* Mejora de botones en móviles */
    .btn-group-vertical .btn {
        border-radius: 0.375rem !important;
        margin-bottom: 0.25rem;
    }
    
    /* Optimización de formularios */
    .form-control, .form-select {
        font-size: 16px; /* Evita zoom en iOS */
    }
    
    /* Mejora de paginación */
    .pagination-sm .page-link {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    /* Optimización de checkboxes */
    .form-check-input {
        width: 1.2rem;
        height: 1.2rem;
    }
    
    /* Mejora de espaciado en móviles */
    .card-body {
        padding: 1rem 0.75rem;
    }
    
    /* Optimización de botones de acción */
    .btn-lg {
        padding: 0.75rem 1rem;
        font-size: 1rem;
    }
}

/* Estilos para pantallas muy pequeñas */
@media (max-width: 575.98px) {
    .table-sm td, .table-sm th {
        padding: 0.25rem 0.125rem;
        font-size: 0.8rem;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
    }
    
    .pagination-sm .page-link {
        padding: 0.125rem 0.25rem;
        font-size: 0.8rem;
    }
}

/* Mejoras generales de usabilidad */
.table th {
    white-space: nowrap;
    background-color: #f8f9fc;
}

.btn-group-vertical .btn:not(:last-child) {
    border-bottom: 1px solid #dee2e6;
}

/* Indicadores visuales para móviles */
@media (max-width: 767.98px) {
    .d-table-cell {
        display: table-cell !important;
    }
    
    .d-md-none {
        display: none !important;
    }
    
    .d-none.d-md-table-cell {
        display: none !important;
    }
}
</style>

<script>
// Variables para paginación y ordenamiento
let paginaActual = 1;
let itemsPorPagina = 100;
let ordenActual = 'ORIGINAL';
let direccionOrden = 'asc';
let datosPersonas = [];
let datosFiltrados = [];

// Función para cambiar el orden de las columnas
function cambiarOrden(columna) {
    if (ordenActual === columna) {
        direccionOrden = direccionOrden === 'asc' ? 'desc' : 'asc';
    } else {
        ordenActual = columna;
        direccionOrden = 'asc';
    }
    actualizarBotonesOrdenamiento();
    
    // Mantener la página actual si es posible, sino ir a la página 1
    const totalPaginas = Math.ceil(datosFiltrados.length / itemsPorPagina);
    if (paginaActual > totalPaginas) {
        paginaActual = 1;
    }
    
    aplicarOrdenamientoYFiltrado();
}

// Función para actualizar el estado visual de los botones de ordenamiento
function actualizarBotonesOrdenamiento() {
    const botones = document.querySelectorAll('.btn-outline-primary');
    botones.forEach(boton => {
        boton.classList.remove('btn-primary');
        boton.classList.add('btn-outline-primary');
    });
    
    const botonActivo = document.querySelector(`[onclick*="${ordenActual}"]`);
    if (botonActivo) {
        botonActivo.classList.remove('btn-outline-primary');
        botonActivo.classList.add('btn-primary');
    }
}

// Función para normalizar texto (remover acentos, ñ y caracteres especiales)
function normalizarTexto(texto) {
    if (!texto) return '';
    
    return texto
        .toLowerCase()
        .normalize('NFD') // Normalizar a forma de descomposición
        .replace(/[\u0300-\u036f]/g, '') // Remover diacríticos (acentos)
        .replace(/ñ/g, 'n') // Reemplazar ñ por n
        .replace(/Ñ/g, 'N') // Reemplazar Ñ por N
        .replace(/[^a-z0-9\s]/g, ''); // Remover caracteres especiales excepto espacios
}

// Función para filtrar personas
// Función para prevenir el comportamiento del Enter en el campo de búsqueda
function prevenirEnter(event) {
    if (event.key === 'Enter' || event.keyCode === 13) {
        event.preventDefault();
        return false;
    }
}

// Función para eliminar duplicados de un array de personas
function eliminarDuplicados(arrayPersonas) {
    const personasUnicas = [];
    const idsVistos = new Set();
    
    arrayPersonas.forEach(persona => {
        // Usar ID como identificador único, si no existe, usar combinación de nombre + apellido
        const identificador = persona.id || `${persona.nombres || ''}_${persona.apellidoPaterno || ''}`;
        
        if (!idsVistos.has(identificador)) {
            idsVistos.add(identificador);
            personasUnicas.push(persona);
        }
    });
    
    return personasUnicas;
}

function filtrarPersonas() {
    const busqueda = document.getElementById('searchInput').value.toLowerCase().trim();
    const estadoBusqueda = document.getElementById('estadoBusqueda');
    
    // Mostrar indicador de búsqueda
    if (busqueda !== '') {
        estadoBusqueda.style.display = 'block';
    } else {
        estadoBusqueda.style.display = 'none';
    }
    
    if (busqueda === '') {
        // Si no hay búsqueda, mostrar todas las personas (eliminando duplicados)
        datosFiltrados = eliminarDuplicados([...datosPersonas]);
    } else {
        // Normalizar el texto de búsqueda
        const busquedaNormalizada = normalizarTexto(busqueda);
        
        // Filtrar por nombre, apellido, familia o grupo familiar
        const resultadosFiltrados = datosPersonas.filter(persona => {
            const nombres = normalizarTexto(persona.nombres || '');
            const apellidoPaterno = normalizarTexto(persona.apellidoPaterno || '');
            const familia = normalizarTexto(persona.familia || '');
            const grupoFamiliar = normalizarTexto(persona.grupoFamiliar || '');
            
            return nombres.includes(busquedaNormalizada) ||
                   apellidoPaterno.includes(busquedaNormalizada) ||
                   familia.includes(busquedaNormalizada) ||
                   grupoFamiliar.includes(busquedaNormalizada);
        });
        
        // Eliminar duplicados de los resultados filtrados
        datosFiltrados = eliminarDuplicados(resultadosFiltrados);
    }
    
    // Reiniciar a la primera página
    paginaActual = 1;
    
    // Aplicar ordenamiento y mostrar resultados
    aplicarOrdenamientoYFiltrado();
    
    // Mostrar información de resultados
    const totalResultados = datosFiltrados.length;
    const info = document.getElementById('infoRegistros');
    if (info) {
        if (busqueda === '') {
            info.textContent = `Mostrando todas las personas (${totalResultados} total)`;
        } else {
            info.textContent = `Búsqueda: "${busqueda}" - ${totalResultados} resultado(s) encontrado(s)`;
        }
    }
}

// Función para limpiar la búsqueda
function limpiarBusqueda() {
    const searchInput = document.getElementById('searchInput');
    const estadoBusqueda = document.getElementById('estadoBusqueda');
    
    // Limpiar el campo de búsqueda
    searchInput.value = '';
    
    // Ocultar indicador de búsqueda
    estadoBusqueda.style.display = 'none';
    
    // Restaurar todos los datos
    datosFiltrados = [...datosPersonas];
    
    // Reiniciar a la primera página
    paginaActual = 1;
    
    // Aplicar ordenamiento y mostrar resultados
    aplicarOrdenamientoYFiltrado();
    
    // Actualizar información de registros
    const info = document.getElementById('infoRegistros');
    if (info) {
        info.textContent = `Mostrando todas las personas (${datosPersonas.length} total)`;
    }
    
    // Enfocar el campo de búsqueda
    searchInput.focus();
    
    console.log('🔍 Búsqueda limpiada, mostrando todas las personas');
}

// Función para cambiar el número de items por página
function cambiarItemsPorPagina() {
    itemsPorPagina = parseInt(document.getElementById('itemsPorPagina').value);
    paginaActual = 1;
    aplicarOrdenamientoYFiltrado();
}

// Función para aplicar ordenamiento y filtrado
function aplicarOrdenamientoYFiltrado() {
    // Solo ejecutar si estamos en la vista de tomar asistencia
    if (!datosPersonas || datosPersonas.length === 0) {
        console.log('No hay datos de personas para ordenar/filtrar');
        return;
    }
    
    let datosOrdenados;
    
            // Si no hay ordenamiento específico, aplicar el orden por defecto: grupo familiar, familia, apellido paterno
        // Priorizando filas con datos sobre las vacías
    if (ordenActual === 'ORIGINAL') {
            // Eliminar duplicados antes de ordenar
            const datosSinDuplicados = eliminarDuplicados([...datosFiltrados]);
            datosOrdenados = datosSinDuplicados.sort((a, b) => {
                // Primero por grupo familiar (priorizar los que tienen datos)
                const grupoA = a.grupoFamiliar || '';
                const grupoB = b.grupoFamiliar || '';
                const tieneGrupoA = grupoA !== '';
                const tieneGrupoB = grupoB !== '';
                
                if (tieneGrupoA !== tieneGrupoB) {
                    return tieneGrupoA ? -1 : 1; // Los que tienen grupo familiar van primero
                }
                if (grupoA !== grupoB) {
                    return grupoA.localeCompare(grupoB);
                }
                
                // Luego por familia (priorizar los que tienen datos)
                const familiaA = a.familia || '';
                const familiaB = b.familia || '';
                const tieneFamiliaA = familiaA !== '';
                const tieneFamiliaB = familiaB !== '';
                
                if (tieneFamiliaA !== tieneFamiliaB) {
                    return tieneFamiliaA ? -1 : 1; // Los que tienen familia van primero
                }
                if (familiaA !== familiaB) {
                    return familiaA.localeCompare(familiaB);
                }
                
                // Finalmente por apellido paterno (priorizar los que tienen datos)
                const apellidoA = a.apellidoPaterno || '';
                const apellidoB = b.apellidoPaterno || '';
                const tieneApellidoA = apellidoA !== '';
                const tieneApellidoB = apellidoB !== '';
                
                if (tieneApellidoA !== tieneApellidoB) {
                    return tieneApellidoA ? -1 : 1; // Los que tienen apellido van primero
                }
                return apellidoA.localeCompare(apellidoB);
            });
    } else {
        // Aplicar ordenamiento personalizado (eliminando duplicados primero)
        const datosSinDuplicados = eliminarDuplicados([...datosFiltrados]);
        datosOrdenados = datosSinDuplicados.sort((a, b) => {
            let valorA, valorB;
            
            switch (ordenActual) {
                case 'FAMILIA':
                    valorA = a.familia || '';
                    valorB = b.familia || '';
                    break;
                case 'GRUPO_FAMILIAR':
                    valorA = a.grupoFamiliar || '';
                    valorB = b.grupoFamiliar || '';
                    break;
                case 'APELLIDO_PATERNO':
                    valorA = a.apellidoPaterno || '';
                    valorB = b.apellidoPaterno || '';
                    break;
                default:
                    valorA = a.familia || '';
                    valorB = b.familia || '';
            }
            
            if (direccionOrden === 'asc') {
                return valorA.localeCompare(valorB);
            } else {
                return valorB.localeCompare(valorA);
            }
        });
    }
    
    mostrarPagina(datosOrdenados, paginaActual);
}

                // Función para mostrar una página específica
                function mostrarPagina(datos, pagina) {
                    console.log(`=== CAMBIANDO A PÁGINA ${pagina} ===`);
                    
                    // Actualizar la variable global de página actual
                    paginaActual = pagina;
                    
                    const inicio = (pagina - 1) * itemsPorPagina;
                    const fin = inicio + itemsPorPagina;
                    const datosPagina = datos.slice(inicio, fin);
                    
                    console.log(`Mostrando registros del ${inicio + 1} al ${fin} de ${datos.length} total`);
                    
                    actualizarTabla(datosPagina);
                    generarPaginacion(datos.length, pagina);
                    actualizarInfoRegistros(datos.length, inicio + 1, Math.min(fin, datos.length));
                    
                    console.log(`Página ${pagina} cargada correctamente`);
                }

                // Función para actualizar la tabla
                function actualizarTabla(datos) {
                    console.log(`Actualizando tabla con ${datos.length} registros`);
                    
                    const tbody = document.querySelector('tbody');
                    tbody.innerHTML = '';
                    
                    let familiaActual = '';
                    let colorAlternado = true;
                    
                    // Ordenar por orden por defecto si es necesario (priorizando filas con datos)
                    const datosOrdenados = ordenActual === 'ORIGINAL' ? 
                        [...datos].sort((a, b) => {
                            // Primero por grupo familiar (priorizar los que tienen datos)
                            const grupoA = a.grupoFamiliar || '';
                            const grupoB = b.grupoFamiliar || '';
                            const tieneGrupoA = grupoA !== '';
                            const tieneGrupoB = grupoB !== '';
                            
                            if (tieneGrupoA !== tieneGrupoB) {
                                return tieneGrupoA ? -1 : 1; // Los que tienen grupo familiar van primero
                            }
                            if (grupoA !== grupoB) {
                                return grupoA.localeCompare(grupoB);
                            }
                            
                            // Luego por familia (priorizar los que tienen datos)
                            const familiaA = a.familia || '';
                            const familiaB = b.familia || '';
                            const tieneFamiliaA = familiaA !== '';
                            const tieneFamiliaB = familiaB !== '';
                            
                            if (tieneFamiliaA !== tieneFamiliaB) {
                                return tieneFamiliaA ? -1 : 1; // Los que tienen familia van primero
                            }
                            if (familiaA !== familiaB) {
                                return familiaA.localeCompare(familiaB);
                            }
                            
                            // Finalmente por apellido paterno (priorizar los que tienen datos)
                            const apellidoA = a.apellidoPaterno || '';
                            const apellidoB = b.apellidoPaterno || '';
                            const tieneApellidoA = apellidoA !== '';
                            const tieneApellidoB = apellidoB !== '';
                            
                            if (tieneApellidoA !== tieneApellidoB) {
                                return tieneApellidoA ? -1 : 1; // Los que tienen apellido van primero
                            }
                            return apellidoA.localeCompare(apellidoB);
                        }) : 
                        datos;
                    
                    // Obtener el estado actual de las asistencias desde la base de datos
                    obtenerEstadoAsistenciasActual(datosOrdenados).then(estadosAsistencias => {
                        datosOrdenados.forEach((persona, index) => {
                            // Determinar el color de la fila según la familia
                            const familiaPersona = persona.familia || '';
                            if (familiaPersona !== familiaActual) {
                                familiaActual = familiaPersona;
                                colorAlternado = !colorAlternado;
                            }
                            
                            let claseColor = '';
                            if (familiaPersona === '') {
                                // Sin familia: alternar gris claro y blanco
                                claseColor = colorAlternado ? 'fila-gris-claro' : 'fila-blanca';
                            } else {
                                // Con familia: alternar verde claro y azul claro
                                claseColor = colorAlternado ? 'fila-verde-claro' : 'fila-azul-claro';
                            }
                            
                            // Determinar el color hexadecimal directamente
                            let colorHex = '';
                            if (familiaPersona === '') {
                                // Sin familia: alternar gris claro y blanco
                                colorHex = colorAlternado ? '#f8f9fa' : '#ffffff';
                            } else {
                                // Con familia: alternar verde claro y amarillo claro
                                colorHex = colorAlternado ? '#d1ecf1' : '#fff3cd';
                            }
                            
                            // Obtener el estado actual de asistencia para esta persona
                            const asistio = estadosAsistencias[persona.id] || false;
                            
                            // Actualizar el estado de asistencia en los datos locales
                            if (window.datosPersonas) {
                                const personaIndex = window.datosPersonas.findIndex(p => p.id == persona.id);
                                if (personaIndex !== -1) {
                                    window.datosPersonas[personaIndex].asistio = asistio;
                                }
                            }
                            
                            const row = document.createElement('tr');
                            row.setAttribute('data-debug', `Familia: ${familiaPersona}, Color: ${colorHex}, Alternado: ${colorAlternado}`);
                            row.style.setProperty('background-color', colorHex, 'important');
                            // Determinar si la persona tiene imagen
                            const tieneImagen = persona.tieneImagen || false;
                            const icono = tieneImagen ? 'fa-image' : 'fa-user';
                            const claseBoton = tieneImagen ? 'btn-info' : 'btn-secondary';
                            const textoBoton = tieneImagen ? 'Ver Foto' : 'Ver Datos';
                            
                            row.innerHTML = `
                                <td class='text-center'>
                                    <input type='checkbox' name='asistencias[]' value='${persona.id}' ${asistio ? 'checked' : ''} class='form-check-input checkbox-asistencia'>
                                </td>
                                <td class='d-none d-md-table-cell'>${persona.nombres}</td>
                                <td class='d-none d-md-table-cell'>${persona.apellidoPaterno}</td>
                                <td class='d-none d-md-table-cell'>${persona.familia || '-'}</td>
                                <td class='d-none d-md-table-cell'>${persona.grupoFamiliar || ''}</td>
                                <td class='text-center'>
                                    <button type='button' class='btn btn-sm ${claseBoton}' 
                                            onclick='verPersonaAsistencia(${persona.id})'
                                            title='${textoBoton}'>
                                        <i class='fas ${icono}'></i>
                                    </button>
                                </td>
                                <td class='d-table-cell d-md-none'>
                                    <div class='fw-bold'>${persona.nombres} ${persona.apellidoPaterno}</div>
                                    <div class='small text-muted'>
                                        <i class='fas fa-home me-1'></i>${persona.grupoFamiliar || ''}
                                    </div>
                                    <div class='small text-muted'>
                                        <i class='fas fa-users me-1'></i>${persona.familia || ''}
                                    </div>
                                </td>
                            `;
                            tbody.appendChild(row);
                        });
                        
                        console.log(`Tabla actualizada con ${datos.length} filas y estados de asistencia sincronizados`);
                        
                        // Aplicar estilos del encabezado después de actualizar la tabla
                        aplicarEstilosEncabezado();
                        
                        // IMPORTANTE: Inicializar el guardado automático DESPUÉS de que se haya renderizado la tabla
                        setTimeout(() => {
                            console.log('Inicializando guardado automático para nueva página...');
                            // Resetear el flag para permitir nueva inicialización en cada cambio de página
                            window.guardadoAutomaticoInicializado = false;
                            inicializarGuardadoAutomatico();
                            
                            // Actualizar el contador de asistencias con los datos reales
                            actualizarContadorAsistencias();
                        }, 100);
                    });
                }

// Función para aplicar estilos del encabezado
function aplicarEstilosEncabezado() {
    const thead = document.querySelector('.table-asistencias thead');
    if (thead) {
        const ths = thead.querySelectorAll('th');
        ths.forEach(th => {
            th.style.setProperty('background-color', '#1e3a8a', 'important');
            th.style.setProperty('color', '#ffffff', 'important');
            th.style.setProperty('border-color', '#1e3a8a', 'important');
            th.style.setProperty('font-weight', '600', 'important');
        });
    }
}

// Función para generar la paginación
function generarPaginacion(totalItems, paginaActual) {
    console.log('🔧 Generando paginación con estilos mejorados...');
    console.log('📱 Ancho de ventana:', window.innerWidth, 'px');
    console.log('📊 Total de items:', totalItems, 'Items por página:', itemsPorPagina);
    
    const totalPaginas = Math.ceil(totalItems / itemsPorPagina);
    const paginacion = document.getElementById('paginacion');
    
    if (totalPaginas <= 1) {
        paginacion.innerHTML = '';
        return;
    }
    
    let html = '';
    
    // Botón anterior
    if (paginaActual > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="mostrarPagina(datosFiltrados, ${paginaActual - 1})">‹</a></li>`;
    } else {
        html += `<li class="page-item disabled"><span class="page-link">‹</span></li>`;
    }
    
    // Números de página - versión más compacta para móviles
    const esMobile = window.innerWidth <= 767.98;
    let paginasAMostrar = [];
    
    if (esMobile) {
        // En móviles, mostrar más páginas para ocupar toda la fila (como en la imagen)
        if (totalPaginas <= 5) {
            // Si hay 5 o menos páginas, mostrar todas
    for (let i = 1; i <= totalPaginas; i++) {
                paginasAMostrar.push(i);
            }
        } else {
            // Mostrar página actual + 2 antes y 2 después, más primera y última
            const inicio = Math.max(1, paginaActual - 2);
            const fin = Math.min(totalPaginas, paginaActual + 2);
            
            // Siempre incluir primera página
            if (inicio > 1) {
                paginasAMostrar.push(1);
                if (inicio > 2) paginasAMostrar.push('...');
            }
            
            for (let i = inicio; i <= fin; i++) {
                paginasAMostrar.push(i);
            }
            
            // Incluir última página si no está ya incluida
            if (fin < totalPaginas) {
                if (fin < totalPaginas - 1) paginasAMostrar.push('...');
                paginasAMostrar.push(totalPaginas);
            }
        }
    } else {
        // En desktop, mostrar más páginas
        for (let i = 1; i <= totalPaginas; i++) {
            if (i === 1 || i === totalPaginas || (i >= paginaActual - 2 && i <= paginaActual + 2)) {
                paginasAMostrar.push(i);
        } else if (i === paginaActual - 3 || i === paginaActual + 3) {
                paginasAMostrar.push('...');
            }
        }
    }
    
    // Generar HTML para las páginas
    paginasAMostrar.forEach(item => {
        if (item === '...') {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        } else if (item === paginaActual) {
            html += `<li class="page-item active"><span class="page-link">${item}</span></li>`;
        } else {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="mostrarPagina(datosFiltrados, ${item})">${item}</a></li>`;
        }
    });
    
    // Botón siguiente
    if (paginaActual < totalPaginas) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="mostrarPagina(datosFiltrados, ${paginaActual + 1})">›</a></li>`;
    } else {
        html += `<li class="page-item disabled"><span class="page-link">›</span></li>`;
    }
    
    paginacion.innerHTML = html;
    
    // Verificar que los estilos se hayan aplicado
    console.log('✅ Paginación generada con HTML:', html);
    console.log('🎨 Verificando estilos aplicados...');
    
    // Verificar estilos de los botones
    const botones = paginacion.querySelectorAll('.page-link');
    botones.forEach((boton, index) => {
        const estilos = window.getComputedStyle(boton);
        console.log(`🔘 Botón ${index + 1}:`, {
            'min-height': estilos.minHeight,
            'font-size': estilos.fontSize,
            'padding': estilos.padding,
            'border': estilos.border,
            'border-radius': estilos.borderRadius
        });
    });
}

// Función para actualizar la información de registros
function actualizarInfoRegistros(total, inicio, fin) {
    const info = document.getElementById('infoRegistros');
    if (info) {
        info.textContent = `Mostrando ${inicio} a ${fin} de ${total} registros`;
    }
}

// Función para colapsar/expandir botones flotantes
function toggleBotonesFlotantes() {
    const botonesFlotantes = document.getElementById('botonesFlotantes');
    const iconToggle = document.getElementById('iconToggle');
    
    if (botonesFlotantes.classList.contains('colapsado')) {
        botonesFlotantes.classList.remove('colapsado');
        iconToggle.className = 'fas fa-chevron-up';
    } else {
        botonesFlotantes.classList.add('colapsado');
        iconToggle.className = 'fas fa-chevron-down';
    }
}

// Función eliminada - ya no se necesita manejo automático de visibilidad

// Función para cargar datos iniciales
function cargarDatosIniciales() {
    // Verificar si estamos en la vista de tomar asistencia o en la lista de cultos
    const tablaAsistencias = document.querySelector('.table-asistencias');
    const tablaCultos = document.querySelector('table:not(.table-asistencias)');
    
    // Solo ejecutar si estamos en la vista de tomar asistencia (con checkboxes)
    if (tablaAsistencias) {
        // Cargar TODAS las personas de la base de datos para el contador
        cargarTodasLasPersonas().then(() => {
            console.log('✅ Todas las personas cargadas para el contador:', datosPersonas.length);
            
            // También cargar las filas visibles para la tabla actual
            const filas = tablaAsistencias.querySelectorAll('tbody tr');
            const datosVisibles = [];
    
    // Mantener el orden original de las filas tal como aparecen en el HTML
    filas.forEach((fila, index) => {
        const celdas = fila.querySelectorAll('td');
        if (celdas.length >= 5) {
            const checkbox = celdas[0].querySelector('input[type="checkbox"]');
                    if (checkbox) { // Verificar que el checkbox existe
            const nombres = celdas[1].textContent.trim();
            const apellidoPaterno = celdas[2].textContent.trim();
            const familia = celdas[3].textContent.trim();
            const grupoFamiliar = celdas[4].textContent.trim();
            
            // Solo agregar si hay datos válidos
            if (nombres && apellidoPaterno) {
                            // Buscar información de imagen en la fila original
                            const botonVer = celdas[5]?.querySelector('button');
                            const tieneImagen = botonVer && botonVer.classList.contains('btn-info');
                            
                            // Obtener la URL real de la imagen si existe
                            let imagenUrl = null;
                            if (tieneImagen) {
                                // Buscar la URL real de la imagen en la fila original
                                // La URL está en el atributo data-url del botón o en algún campo oculto
                                // Por ahora, intentaremos obtenerla de la base de datos más adelante
                                imagenUrl = null; // Se cargará dinámicamente cuando se abra el modal
                            }
                            
                            datosVisibles.push({
                    id: checkbox.value,
                    nombres: nombres,
                    apellidoPaterno: apellidoPaterno,
                    familia: familia === '-' ? '' : familia,
                    grupoFamiliar: grupoFamiliar === '' ? '' : grupoFamiliar,
                    asistio: checkbox.checked,
                                ordenOriginal: index, // Mantener el orden original
                                tieneImagen: tieneImagen, // Información sobre si tiene imagen
                                imagenUrl: imagenUrl // URL de la imagen si existe
                });
                        }
            }
        }
    });
    
            console.log('Datos visibles cargados:', datosVisibles.length, 'personas');
            datosFiltrados = [...datosVisibles];
    aplicarOrdenamientoYFiltrado();
            
            // Actualizar el contador con todas las personas
            actualizarContadorAsistencias();
        });
    } else if (tablaCultos) {
        console.log('Vista de lista de cultos - no se cargan datos de personas');
        // En la vista de cultos, no necesitamos cargar datos de personas
        datosPersonas = [];
        datosFiltrados = [];
    } else {
        console.log('No se encontró tabla de asistencias ni de cultos');
    }
}

// Función para cargar TODAS las personas de la base de datos
function cargarTodasLasPersonas() {
    return new Promise((resolve, reject) => {
        const cultoId = <?php echo $culto_id ?: 'null'; ?>;
        if (!cultoId) {
            console.error('No hay culto_id para cargar personas');
            datosPersonas = [];
            resolve();
            return;
        }
        
        console.log('🔄 Cargando TODAS las personas de la base de datos para culto:', cultoId);
        
        // Crear FormData para la consulta
        const formData = new FormData();
        formData.append('action', 'obtener_todas_personas');
        formData.append('culto_id', cultoId);
        
        fetch('asistencias_actions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(data => {
            console.log('📥 Respuesta recibida del servidor:', data);
            
            if (!data || data.trim() === '') {
                console.warn('Respuesta vacía al cargar todas las personas');
                datosPersonas = [];
                resolve();
                return;
            }
            
            try {
                const resultado = JSON.parse(data);
                console.log('🔍 Resultado parseado:', resultado);
                
                if (resultado.success) {
                    console.log('✅ Todas las personas cargadas:', resultado.personas.length);
                    console.log('📋 Primeras 3 personas:', resultado.personas.slice(0, 3));
                    
                    // Asignar a la variable global (eliminando duplicados)
                    const personasSinDuplicados = eliminarDuplicados(resultado.personas);
                    const duplicadosEliminados = resultado.personas.length - personasSinDuplicados.length;
                    
                    if (duplicadosEliminados > 0) {
                        console.log(`🧹 Se eliminaron ${duplicadosEliminados} duplicados de ${resultado.personas.length} personas`);
                    }
                    
                    window.datosPersonas = personasSinDuplicados;
                    datosPersonas = personasSinDuplicados;
                    
                    console.log('🔒 Variable global datosPersonas asignada:', window.datosPersonas.length, 'personas');
                    resolve();
                } else {
                    console.warn('❌ Error al cargar todas las personas:', resultado.message);
                    datosPersonas = [];
                    window.datosPersonas = [];
                    resolve();
                }
            } catch (e) {
                console.error('❌ Error al parsear respuesta de todas las personas:', e);
                console.error('📄 Respuesta problemática:', data);
                datosPersonas = [];
                window.datosPersonas = [];
                resolve();
            }
        })
        .catch(error => {
            console.error('❌ Error al cargar todas las personas:', error);
            datosPersonas = [];
            window.datosPersonas = [];
            resolve();
        });
    });
}

// Inicializar cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    cargarDatosIniciales();
    
    // Solo actualizar botones de ordenamiento si estamos en la vista de tomar asistencia
    const tablaAsistencias = document.querySelector('.table-asistencias');
    if (tablaAsistencias) {
        actualizarBotonesOrdenamiento();
    }
    
    // Los botones flotantes mantienen su funcionalidad normal
    
    // Listener para cambios de tamaño de ventana (para paginación responsive)
    window.addEventListener('resize', function() {
        if (datosFiltrados && datosFiltrados.length > 0) {
            // Regenerar paginación cuando cambie el tamaño de la ventana
            generarPaginacion(datosFiltrados.length, paginaActual);
        }
    });
});



// Función para ver información de persona
function verPersonaAsistencia(personaId) {
    console.log('Ver persona:', personaId);
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalVerPersonaAsistencia'));
    modal.show();
    
    // Cargar datos de la persona
    mostrarDatosPersonaAsistencia(personaId);
}

// Función para mostrar datos de persona en el modal
function mostrarDatosPersonaAsistencia(personaId) {
    // Buscar la persona en los datos cargados
    const persona = datosPersonas.find(p => p.id == personaId);
    
    if (persona) {
        // Actualizar imagen
        const imagenPersona = document.getElementById('imagenPersona');
        const imagenPersonaContainer = document.getElementById('imagenPersonaContainer');
        const imagenDefaultContainer = document.getElementById('imagenDefaultContainer');
        
        // Construir la URL de la imagen basada en si la persona tiene imagen
        if (persona.tieneImagen) {
            // Hacer una llamada AJAX para obtener la URL real de la imagen
            fetch(`../modules/personas_actions.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=obtener_imagen&persona_id=${persona.id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.imagen_url) {
                    // Usar la URL real de la imagen
                    imagenPersona.src = `../${data.imagen_url}`;
                    imagenPersonaContainer.style.display = 'block';
                    imagenDefaultContainer.style.display = 'none';
                    
                    // Manejar errores de carga de imagen
                    imagenPersona.onerror = function() {
                        imagenPersonaContainer.style.display = 'none';
                        imagenDefaultContainer.style.display = 'block';
                    };
                } else {
                    // No hay imagen o error, mostrar icono por defecto
                    imagenPersonaContainer.style.display = 'none';
                    imagenDefaultContainer.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error al obtener imagen:', error);
                imagenPersonaContainer.style.display = 'none';
                imagenDefaultContainer.style.display = 'block';
            });
        } else {
            imagenPersonaContainer.style.display = 'none';
            imagenDefaultContainer.style.display = 'block';
        }
        
        // Actualizar datos
        const datosPersona = document.getElementById('datosPersona');
        datosPersona.innerHTML = `
            <h6 class="mb-3">Información Personal</h6>
            <div class="row mb-2">
                <div class="col-4"><strong>Nombres:</strong></div>
                <div class="col-8">${persona.nombres}</div>
            </div>
            <div class="row mb-2">
                <div class="col-4"><strong>Apellido Paterno:</strong></div>
                <div class="col-8">${persona.apellidoPaterno}</div>
            </div>
            <div class="row mb-2">
                <div class="col-4"><strong>Familia:</strong></div>
                <div class="col-8">${persona.familia || 'No especificada'}</div>
            </div>
            <div class="row mb-2">
                <div class="col-4"><strong>Grupo Familiar:</strong></div>
                <div class="col-8">${persona.grupoFamiliar || 'No especificado'}</div>
            </div>
            <div class="row mb-2">
                <div class="col-4"><strong>Estado de Asistencia:</strong></div>
                <div class="col-8">
                    <span class="badge ${persona.asistio ? 'bg-success' : 'bg-secondary'}">
                        ${persona.asistio ? 'Asistió' : 'No asistió'}
                    </span>
                </div>
            </div>
        `;
    } else {
        console.error('Persona no encontrada:', personaId);
        // Mostrar mensaje de error
        const datosPersona = document.getElementById('datosPersona');
        datosPersona.innerHTML = '<div class="alert alert-danger">No se pudo cargar la información de la persona.</div>';
    }
}



// Variables globales para exportación
let datosAsistentesExportar = [];
let cultoActual = {};

// Funciones para ver asistentes del culto
function verAsistentesCulto(cultoId, tipoCulto, fecha) {
    // Mostrar el modal
    const modal = new bootstrap.Modal(document.getElementById('modalAsistentesCulto'));
    modal.show();
    
    // Almacenar datos del culto para exportación
    cultoActual = {
        id: cultoId,
        tipo: tipoCulto,
        fecha: fecha
    };
    
    // Actualizar información del culto
    document.getElementById('tipoCultoAsistentes').textContent = tipoCulto;
    document.getElementById('fechaCultoAsistentes').textContent = fecha;
    
    // Mostrar indicador de carga
    document.getElementById('cargandoAsistentesCulto').style.display = 'block';
    document.getElementById('tablaAsistentesCulto').style.display = 'none';
    document.getElementById('sinAsistentesCulto').style.display = 'none';
    document.getElementById('btnExportarExcel').style.display = 'none';
    
    // Cargar datos de asistentes
    cargarAsistentesCulto(cultoId);
}

function cargarAsistentesCulto(cultoId) {
    const formData = new FormData();
    formData.append('action', 'obtener_asistentes_culto');
    formData.append('culto_id', cultoId);
    
    fetch('asistencias_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Ocultar indicador de carga
        document.getElementById('cargandoAsistentesCulto').style.display = 'none';
        
        if (data.success) {
            // Actualizar contador de asistentes
            document.getElementById('totalAsistentes').textContent = data.total || 0;
            
            if (data.asistentes && data.asistentes.length > 0) {
                mostrarTablaAsistentesCulto(data.asistentes);
            } else {
                document.getElementById('sinAsistentesCulto').style.display = 'block';
            }
        } else {
            console.error('Error al cargar asistentes:', data.error);
            SwalUtils.showError('Error al cargar la lista de asistentes: ' + (data.error || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error en la petición:', error);
        document.getElementById('cargandoAsistentesCulto').style.display = 'none';
        SwalUtils.showError('Error al cargar la lista de asistentes');
    });
}

function mostrarTablaAsistentesCulto(asistentes) {
    const tbody = document.getElementById('cuerpoTablaAsistentesCulto');
    tbody.innerHTML = '';
    
    // Almacenar datos para exportación
    datosAsistentesExportar = asistentes;
    
    asistentes.forEach(asistente => {
        const row = document.createElement('tr');
        
        // Determinar el tipo y estilo
        const esVisita = asistente.tipo === 'visita';
        const tipoTexto = esVisita ? 'Visita' : 'Persona';
        const tipoIcono = esVisita ? 'fas fa-user-plus' : 'fas fa-user';
        const tipoColor = esVisita ? 'text-info' : 'text-primary';
        
        // Agregar información de primera vez para visitas
        const infoPrimeraVez = esVisita && asistente.primera_vez == 1 ? 
            '<br><small class="text-success"><i class="fas fa-star"></i> Primera vez</small>' : '';
        
        row.innerHTML = `
            <td>
                <i class="${tipoIcono} ${tipoColor}"></i> 
                ${asistente.nombres || '-'}
                ${infoPrimeraVez}
            </td>
            <td>${asistente.apellidos || '-'}</td>
            <td>${asistente.familia || '-'}</td>
            <td>${asistente.grupo_familiar || '-'}</td>
            <td>${asistente.observaciones || '-'}</td>
            <td>
                <span class="badge ${esVisita ? 'bg-info' : 'bg-primary'}">
                    ${tipoTexto}
                </span>
            </td>
        `;
        tbody.appendChild(row);
    });
    
    document.getElementById('tablaAsistentesCulto').style.display = 'table';
    document.getElementById('btnExportarExcel').style.display = 'inline-block';
}

// Función para exportar a Excel
function exportarAsistentesExcel() {
    if (datosAsistentesExportar.length === 0) {
        SwalUtils.showError('No hay datos para exportar');
        return;
    }
    
    try {
        // Verificar que la librería XLSX esté disponible
        if (typeof XLSX === 'undefined') {
            SwalUtils.showError('Error: La librería Excel no está disponible');
            return;
        }
        
        // Crear el workbook
        const wb = XLSX.utils.book_new();
        
        // Preparar los datos para la hoja de cálculo
        const datos = [];
        
        // Agregar encabezado con información del culto
        datos.push(['LISTA DE ASISTENTES']);
        datos.push(['Tipo de Culto:', cultoActual.tipo]);
        datos.push(['Fecha:', cultoActual.fecha]);
        datos.push(['Total Asistentes:', datosAsistentesExportar.length]);
        datos.push([]); // Línea en blanco
        
        // Agregar encabezados de la tabla
        datos.push(['Número', 'Nombre', 'Apellidos']);
        
        // Agregar datos de los asistentes
        datosAsistentesExportar.forEach((asistente, index) => {
            datos.push([
                index + 1,
                asistente.nombres || '',
                asistente.apellidos || ''
            ]);
        });
        
        // Crear la hoja de cálculo
        const ws = XLSX.utils.aoa_to_sheet(datos);
        
        // Configurar el ancho de las columnas
        ws['!cols'] = [
            { wch: 8 },  // Número
            { wch: 25 }, // Nombre
            { wch: 30 }  // Apellidos
        ];
        
        // Agregar la hoja al workbook
        XLSX.utils.book_append_sheet(wb, ws, 'Asistentes');
        
        // Generar el archivo Excel
        const nombreArchivo = 'Asistentes_' + cultoActual.tipo.replace(/[^a-zA-Z0-9]/g, '_') + '_' + cultoActual.fecha.replace(/\//g, '-') + '.xlsx';
        XLSX.writeFile(wb, nombreArchivo);
        
        SwalUtils.showSuccess('Archivo Excel generado exitosamente');
        
    } catch (error) {
        console.error('Error al generar archivo Excel:', error);
        SwalUtils.showError('Error al generar el archivo Excel: ' + error.message);
    }
}

// Limpiar modal cuando se cierre
document.addEventListener('DOMContentLoaded', function() {
    const modalAsistentesCulto = document.getElementById('modalAsistentesCulto');
    if (modalAsistentesCulto) {
        modalAsistentesCulto.addEventListener('hidden.bs.modal', function () {
            document.getElementById('cuerpoTablaAsistentesCulto').innerHTML = '';
            document.getElementById('totalAsistentes').textContent = '0';
            document.getElementById('tablaAsistentesCulto').style.display = 'none';
            document.getElementById('sinAsistentesCulto').style.display = 'none';
            document.getElementById('cargandoAsistentesCulto').style.display = 'none';
            document.getElementById('btnExportarExcel').style.display = 'none';
            
            // Limpiar datos de exportación
            datosAsistentesExportar = [];
            cultoActual = {};
        });
    }
});

// Mostrar alertas de sesión con SweetAlert2
<?php if ($successMessage): ?>
SwalUtils.showSuccess('<?php echo addslashes($successMessage); ?>');
<?php endif; ?>

<?php if ($errorMessage): ?>
SwalUtils.showError('<?php echo addslashes($errorMessage); ?>');
<?php endif; ?>
</script>

<!-- Modal para ver información de persona -->
<div class="modal fade" id="modalVerPersonaAsistencia" tabindex="-1" aria-labelledby="modalVerPersonaAsistenciaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalVerPersonaAsistenciaLabel">Información de la Persona</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div id="imagenPersonaContainer">
                            <img id="imagenPersona" src="" alt="Foto de la persona" class="img-fluid rounded" style="max-width: 200px; max-height: 200px; object-fit: cover;">
                        </div>
                        <div id="imagenDefaultContainer" style="display: none;">
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 200px; height: 200px; margin: 0 auto;">
                                <i class="fas fa-user fa-4x text-muted"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div id="datosPersona">
                            <!-- Los datos se cargarán dinámicamente -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Ver Asistentes del Culto -->
<div class="modal fade" id="modalAsistentesCulto" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lista de Asistentes del Culto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="info-culto">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Tipo de Culto:</strong> <span id="tipoCultoAsistentes"></span>
                        </div>
                        <div class="col-md-4">
                            <strong>Fecha:</strong> <span id="fechaCultoAsistentes"></span>
                        </div>
                        <div class="col-md-4">
                            <strong>Total Asistentes:</strong> <span id="totalAsistentes" class="badge bg-primary">0</span>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="tablaAsistentesCulto">
                        <thead class="table-dark">
                            <tr>
                                <th>Nombres</th>
                                <th>Apellidos</th>
                                <th>Familia</th>
                                <th>Grupo Familiar</th>
                                <th>Observaciones</th>
                                <th>Tipo</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoTablaAsistentesCulto">
                            <!-- Los datos se cargarán dinámicamente -->
                        </tbody>
                    </table>
                </div>
                
                <div class="text-center mt-3" id="sinAsistentesCulto" style="display: none;">
                    <p class="text-muted">No hay asistentes registrados para este culto.</p>
                </div>
                
                <div class="text-center mt-3" id="cargandoAsistentesCulto">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando lista de asistentes...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="btnExportarExcel" onclick="exportarAsistentesExcel()" style="display: none;">
                    <i class="fas fa-file-excel"></i> Exportar a Excel
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos para el modal de asistentes del culto */
#modalAsistentesCulto .modal-dialog {
    max-width: 900px;
}

#modalAsistentesCulto .table th {
    background-color: #343a40;
    color: white;
    font-weight: 600;
    border: none;
}

#modalAsistentesCulto .table td {
    vertical-align: middle;
    border-color: #dee2e6;
}

#modalAsistentesCulto .table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.1);
}

#cargandoAsistentesCulto {
    padding: 40px 20px;
}

#sinAsistentesCulto {
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

<?php include '../includes/footer.php'; ?>
