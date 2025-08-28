<?php include '../includes/header.php'; ?>
<link rel="stylesheet" href="../assets/css/asistencias.css?v=<?php echo time(); ?>">
<style>
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
                
                <!-- Controles de búsqueda y ordenamiento -->
                <div class="row mb-3">
                    <!-- Búsqueda - Ocupa todo el ancho en móviles -->
                    <div class="col-12 col-md-6 mb-3 mb-md-0">
                        <div class="input-group">
                            <input type="text" class="form-control" id="searchInput" placeholder="Buscar personas..." oninput="filtrarPersonas()">
                            <button class="btn btn-outline-secondary" type="button" onclick="filtrarPersonas()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <small class="text-muted d-none d-md-block">La búsqueda se actualiza automáticamente mientras escribes</small>
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
                
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-asistencias">
                        <thead>
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
                                // Consulta con el mismo ordenamiento que el listado de personas
                                $stmt = $pdo->query("SELECT p.*, gf.NOMBRE as grupo_familiar 
                                                   FROM personas p 
                                                   LEFT JOIN grupos_familiares gf ON p.GRUPO_FAMILIAR_ID = gf.ID 
                                                   ORDER BY p.ID");
                                
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
                    const textoLower = texto.toLowerCase();
                    
                    datosPersonas.forEach(persona => {
                        if (tipo === 'nombres' && persona.nombres && persona.nombres.toLowerCase().startsWith(textoLower)) {
                            sugerencias.push({
                                texto: persona.nombres,
                                subtitulo: persona.apellidoPaterno
                            });
                        } else if (tipo === 'apellidos' && persona.apellidoPaterno && persona.apellidoPaterno.toLowerCase().startsWith(textoLower)) {
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
                                // Remover clase de guardando y agregar indicador de éxito
                                checkbox.classList.remove('guardando');
                                checkbox.classList.add('guardado');
                                
                                // Actualizar contador de asistencias (con manejo de errores)
                                try {
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
                    const checkboxes = document.querySelectorAll('input[name="asistencias[]"]:checked');
                    const totalPersonas = document.querySelectorAll('input[name="asistencias[]"]').length;
                    const asistenciasMarcadas = checkboxes.length;
                    
                    // Buscar o crear el contador
                    let contador = document.getElementById('contadorAsistencias');
                    if (!contador) {
                        contador = document.createElement('div');
                        contador.id = 'contadorAsistencias';
                        contador.className = 'mt-2';
                        contador.innerHTML = `
                            <small class="text-success">
                                <i class="fas fa-check-circle"></i> ${asistenciasMarcadas} de ${totalPersonas} personas han marcado asistencia
                            </small>
                        `;
                        
                        // Insertar después del mensaje de info
                        const infoDiv = document.querySelector('.text-muted');
                        if (infoDiv && infoDiv.parentNode) {
                            infoDiv.parentNode.insertBefore(contador, infoDiv.nextSibling);
                        }
                    } else {
                        contador.innerHTML = `
                            <small class="text-success">
                                <i class="fas fa-check-circle"></i> ${asistenciasMarcadas} de ${totalPersonas} personas han marcado asistencia
                            </small>
                        `;
                    }
                }
                

                
                // Inicializar guardado automático cuando se cargue la página
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(function() {
                        inicializarGuardadoAutomatico();
                        actualizarContadorAsistencias();
                    }, 100);
                });
                </script>
                
                <!-- Paginación -->
                <div class="row mt-3">
                    <!-- Selector de items por página - Oculto en móviles muy pequeños -->
                    <div class="col-6 col-md-4 mb-2 mb-md-0">
                        <div class="d-flex align-items-center">
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
                    
                    <!-- Información de registros -->
                    <div class="col-6 col-md-4 text-center">
                        <small class="text-muted" id="infoRegistros">
                            <!-- La información se generará dinámicamente -->
                        </small>
                    </div>
                    
                    <!-- Navegación de páginas -->
                    <div class="col-12 col-md-4">
                        <nav aria-label="Navegación de páginas">
                            <ul class="pagination pagination-sm justify-content-center justify-content-md-end mb-0" id="paginacion">
                                <!-- La paginación se generará dinámicamente -->
                            </ul>
                        </nav>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <div class="d-grid gap-2 d-md-block">


                        <button type="button" class="btn btn-primary btn-lg w-100 w-md-auto ms-md-2 mt-2 mt-md-0" data-bs-toggle="modal" data-bs-target="#modalAgregarPersona">
                            <i class="fas fa-plus"></i> Agregar Persona
                        </button>
                        <a href="asistencias.php" class="btn btn-secondary btn-lg w-100 w-md-auto ms-md-2 mt-2 mt-md-0">
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
                            $stmt = $pdo->query("SELECT c.*, COUNT(a.PERSONA_ID) as asistentes 
                                               FROM cultos c 
                                               LEFT JOIN asistencias a ON c.ID = a.CULTO_ID 
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
                                            <a href='asistencias_ver.php?culto_id=" . $row['ID'] . "' class='btn btn-sm btn-info w-100 w-md-auto mt-1 mt-md-0' title='Ver Asistencias'>
                                                <i class='fas fa-eye'></i> <span class='d-none d-sm-inline'>Ver</span>
                                            </a>
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

<style>
/* Estilos personalizados para dispositivos móviles */
@media (max-width: 767.98px) {
    /* Optimización de tablas para móviles */
    .table-responsive {
        border: none;
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

// Función para filtrar personas
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
        // Si no hay búsqueda, mostrar todas las personas
        datosFiltrados = [...datosPersonas];
    } else {
        // Filtrar por nombre, apellido, familia o grupo familiar
        datosFiltrados = datosPersonas.filter(persona => {
            const nombres = (persona.nombres || '').toLowerCase();
            const apellidoPaterno = (persona.apellidoPaterno || '').toLowerCase();
            const familia = (persona.familia || '').toLowerCase();
            const grupoFamiliar = (persona.grupoFamiliar || '').toLowerCase();
            
            return nombres.includes(busqueda) ||
                   apellidoPaterno.includes(busqueda) ||
                   familia.includes(busqueda) ||
                   grupoFamiliar.includes(busqueda);
        });
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
    
    // Si no hay ordenamiento específico, mantener el orden original de la base de datos
    if (ordenActual === 'ORIGINAL') {
        datosOrdenados = [...datosFiltrados].sort((a, b) => a.ordenOriginal - b.ordenOriginal);
    } else {
        // Aplicar ordenamiento personalizado
        datosOrdenados = [...datosFiltrados].sort((a, b) => {
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
    
    mostrarPagina(datosOrdenados, 1);
}

                // Función para mostrar una página específica
                function mostrarPagina(datos, pagina) {
                    console.log(`=== CAMBIANDO A PÁGINA ${pagina} ===`);
                    
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
                    
                    // Ordenar por orden original si es necesario
                    const datosOrdenados = ordenActual === 'ORIGINAL' ? 
                        [...datos].sort((a, b) => a.ordenOriginal - b.ordenOriginal) : 
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
                            
                            const row = document.createElement('tr');
                            row.setAttribute('data-debug', `Familia: ${familiaPersona}, Color: ${colorHex}, Alternado: ${colorAlternado}`);
                            row.style.setProperty('background-color', colorHex, 'important');
                            row.innerHTML = `
                                <td class='text-center'>
                                    <input type='checkbox' name='asistencias[]' value='${persona.id}' ${asistio ? 'checked' : ''} class='form-check-input checkbox-asistencia'>
                                </td>
                                <td>${persona.nombres}</td>
                                <td>${persona.apellidoPaterno}</td>
                                <td>${persona.familia || ''}</td>
                                <td>${persona.grupoFamiliar || ''}</td>
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

// Función para cargar datos iniciales
function cargarDatosIniciales() {
    // Verificar si estamos en la vista de tomar asistencia o en la lista de cultos
    const tablaAsistencias = document.querySelector('.table-asistencias');
    const tablaCultos = document.querySelector('table:not(.table-asistencias)');
    
    // Solo ejecutar si estamos en la vista de tomar asistencia (con checkboxes)
    if (tablaAsistencias) {
        const filas = tablaAsistencias.querySelectorAll('tbody tr');
        datosPersonas = [];
        
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
                        datosPersonas.push({
                            id: checkbox.value,
                            nombres: nombres,
                            apellidoPaterno: apellidoPaterno,
                            familia: familia === '-' ? '' : familia,
                            grupoFamiliar: grupoFamiliar === '' ? '' : grupoFamiliar,
                            asistio: checkbox.checked,
                            ordenOriginal: index // Mantener el orden original
                        });
                    }
                }
            }
        });
        
        console.log('Datos cargados:', datosPersonas.length, 'personas');
        datosFiltrados = [...datosPersonas];
        aplicarOrdenamientoYFiltrado();
    } else if (tablaCultos) {
        console.log('Vista de lista de cultos - no se cargan datos de personas');
        // En la vista de cultos, no necesitamos cargar datos de personas
        datosPersonas = [];
        datosFiltrados = [];
    } else {
        console.log('No se encontró tabla de asistencias ni de cultos');
    }
}

// Inicializar cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    cargarDatosIniciales();
    
    // Solo actualizar botones de ordenamiento si estamos en la vista de tomar asistencia
    const tablaAsistencias = document.querySelector('.table-asistencias');
    if (tablaAsistencias) {
        actualizarBotonesOrdenamiento();
    }
    
    // Listener para cambios de tamaño de ventana (para paginación responsive)
    window.addEventListener('resize', function() {
        if (datosFiltrados && datosFiltrados.length > 0) {
            // Regenerar paginación cuando cambie el tamaño de la ventana
            generarPaginacion(datosFiltrados.length, paginaActual);
        }
    });
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
