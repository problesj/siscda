<?php include '../includes/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Reportes de Asistencias</h1>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Filtros de Reporte</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control" name="fecha_inicio" 
                                       value="<?php echo $_GET['fecha_inicio'] ?? date('Y-m-01'); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fecha_fin" class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control" name="fecha_fin" 
                                       value="<?php echo $_GET['fecha_fin'] ?? date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="grupo_familiar" class="form-label">Grupo Familiar</label>
                        <select class="form-select" name="grupo_familiar">
                            <option value="">Todos los grupos</option>
                            <?php
                            try {
                                $pdo = conectarDB();
                                
                                // Obtener familias únicas de la tabla personas
                                $stmt = $pdo->query("SELECT DISTINCT FAMILIA FROM personas WHERE FAMILIA IS NOT NULL AND FAMILIA != '' ORDER BY FAMILIA");
                                $familias = $stmt->fetchAll();
                                
                                if (empty($familias)) {
                                    echo "<option value=''>No hay familias disponibles</option>";
                                } else {
                                    foreach ($familias as $familia) {
                                        if (isset($familia['FAMILIA']) && $familia['FAMILIA'] !== '') {
                                            $selected = ($_GET['grupo_familiar'] ?? '') == $familia['FAMILIA'] ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($familia['FAMILIA']) . "' $selected>" . htmlspecialchars($familia['FAMILIA']) . "</option>";
                                        }
                                    }
                                }
                            } catch (PDOException $e) {
                                echo "<option value=''>Error de base de datos: " . htmlspecialchars($e->getMessage()) . "</option>";
                            } catch (Exception $e) {
                                echo "<option value=''>Error general: " . htmlspecialchars($e->getMessage()) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Generar Reporte
                    </button>
                    <a href="reportes.php" class="btn btn-secondary ms-2">
                        <i class="fas fa-refresh"></i> Limpiar
                    </a>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Resumen</h6>
            </div>
            <div class="card-body">
                <?php
                $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
                $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
                $grupo_familiar = $_GET['grupo_familiar'] ?? '';
                
                try {
                    // Total de cultos en el período
                    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM cultos c WHERE c.fecha BETWEEN ? AND ?");
                    $stmt->execute([$fecha_inicio, $fecha_fin]);
                    $result = $stmt->fetch();
                    $total_cultos = $result ? $result['total'] : 0;
                    
                    // Total de asistencias en el período
                    $sql_asistencias = "SELECT COUNT(*) as total FROM asistencias a 
                                       JOIN cultos c ON a.culto_id = c.id 
                                       JOIN personas p ON a.persona_id = p.id 
                                       WHERE c.fecha BETWEEN ? AND ?";
                    $params_asistencias = [$fecha_inicio, $fecha_fin];
                    
                    if ($grupo_familiar) {
                        $sql_asistencias .= " AND p.FAMILIA = ?";
                        $params_asistencias[] = $grupo_familiar;
                    }
                    
                    $stmt = $pdo->prepare($sql_asistencias);
                    $stmt->execute($params_asistencias);
                    $result = $stmt->fetch();
                    $total_asistencias = $result ? $result['total'] : 0;
                    
                    // Total de personas únicas
                    $sql_personas = "SELECT COUNT(DISTINCT a.persona_id) as total FROM asistencias a 
                                    JOIN cultos c ON a.culto_id = c.id 
                                    JOIN personas p ON a.persona_id = p.id 
                                    WHERE c.fecha BETWEEN ? AND ?";
                    $params_personas = [$fecha_inicio, $fecha_fin];
                    
                    if ($grupo_familiar) {
                        $sql_personas .= " AND p.FAMILIA = ?";
                        $params_personas[] = $grupo_familiar;
                    }
                    
                    $stmt = $pdo->prepare($sql_personas);
                    $stmt->execute($params_personas);
                    $result = $stmt->fetch();
                    $personas_unicas = $result ? $result['total'] : 0;
                    
                    echo "<div class='row'>";
                    echo "<div class='col-md-4 text-center'>";
                    echo "<h4 class='text-primary'>$total_cultos</h4>";
                    echo "<small class='text-muted'>Cultos</small>";
                    echo "</div>";
                    echo "<div class='col-md-4 text-center'>";
                    echo "<h4 class='text-success'>$total_asistencias</h4>";
                    echo "<small class='text-muted'>Asistencias</small>";
                    echo "</div>";
                    echo "<div class='col-md-4 text-center'>";
                    echo "<h4 class='text-info'>$personas_unicas</h4>";
                    echo "<small class='text-muted'>Personas Únicas</small>";
                    echo "</div>";
                    echo "</div>";
                    
                } catch (PDOException $e) {
                    echo "<p class='text-danger'>Error al generar resumen: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
                ?>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">Reporte Detallado de Asistencias</h6>
    </div>
    <div class="card-body">
        <!-- Buscador y controles -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" id="searchInput" placeholder="Buscar personas..." onkeyup="filtrarPersonas()">
                    <button class="btn btn-outline-secondary" type="button" onclick="limpiarBusqueda()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <small class="text-muted">La búsqueda se actualiza automáticamente mientras escribes (Enter deshabilitado)</small>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-end align-items-center">
                    <label class="me-2">Mostrar:</label>
                    <select class="form-select form-select-sm" id="itemsPorPagina" onchange="cambiarItemsPorPagina()" style="width: auto;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100" selected>100</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Contador de resultados -->
        <div class="row mb-3">
            <div class="col-12">
                <div id="contadorResultados" class="text-muted">
                    <!-- Se actualizará dinámicamente -->
                </div>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0" id="tablaReporte">
                <thead>
                    <tr>
                        <th>Persona</th>
                        <th>Grupo Familiar</th>
                        <th>Total Asistencias</th>
                        <th>Porcentaje Asistencia</th>
                        <th>Última Asistencia</th>
                        <th>Detalle por Culto</th>
                    </tr>
                </thead>
                <tbody id="tablaReporteBody">
                    <!-- Los datos se cargarán dinámicamente con JavaScript -->
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <div class="row mt-3">
            <div class="col-12 col-md-4 mb-2 mb-md-0">
                <nav aria-label="Navegación de páginas">
                    <ul class="pagination pagination-sm justify-content-start mb-0" id="paginacion">
                        <!-- La paginación se generará dinámicamente -->
                    </ul>
                </nav>
            </div>
            <div class="col-12 col-md-4 text-center mb-2 mb-md-0">
                <div id="infoPaginacion" class="text-muted">
                    <!-- Se actualizará dinámicamente -->
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="d-flex align-items-center justify-content-end">
                    <label class="me-2 d-none d-sm-inline">Mostrar:</label>
                    <label class="me-2 d-inline d-sm-none">Items:</label>
                    <select class="form-select form-select-sm me-2" id="itemsPorPaginaFooter" onchange="cambiarItemsPorPagina()" style="width: auto;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100" selected>100</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para mostrar asistencias detalladas por persona -->
<div class="modal fade" id="modalDetalleAsistencias" tabindex="-1" aria-labelledby="modalDetalleAsistenciasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetalleAsistenciasLabel">Detalle de Asistencias</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="cargandoDetalle" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando asistencias...</p>
                </div>
                
                <div id="contenidoDetalle" style="display: none;">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Persona:</strong> <span id="nombrePersona"></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Grupo Familiar:</strong> <span id="grupoFamiliar"></span>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Día</th>
                                    <th>Tipo de Culto</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="tablaAsistencias">
                                <!-- Las asistencias se cargarán aquí dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div id="sinAsistencias" class="text-center text-muted" style="display: none;">
                        <i class="fas fa-calendar-times fa-3x mb-3"></i>
                        <p>No se encontraron asistencias para esta persona en el período seleccionado.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Variables globales para paginación y búsqueda
let datosPersonas = [];
let datosFiltrados = [];
let paginaActual = 1;
let itemsPorPagina = 100;
let totalCultos = <?php echo $total_cultos; ?>;
let fechaInicio = '<?php echo $fecha_inicio; ?>';
let fechaFin = '<?php echo $fecha_fin; ?>';

// Función para cargar datos iniciales
function cargarDatosIniciales() {
    const formData = new FormData();
    formData.append('action', 'obtener_datos_reporte');
    formData.append('fecha_inicio', fechaInicio);
    formData.append('fecha_fin', fechaFin);
    formData.append('grupo_familiar', '<?php echo $grupo_familiar; ?>');
    
    fetch('reportes_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            datosPersonas = data.personas;
            datosFiltrados = [...datosPersonas];
            aplicarPaginacion();
            actualizarContador();
        } else {
            console.error('Error al cargar datos:', data.message);
            mostrarError('Error al cargar los datos del reporte');
        }
    })
    .catch(error => {
        console.error('Error en la petición:', error);
        mostrarError('Error al cargar los datos del reporte');
    });
}

// Función para filtrar personas
function filtrarPersonas() {
    const busqueda = document.getElementById('searchInput').value.toLowerCase().trim();
    
    if (busqueda === '') {
        datosFiltrados = [...datosPersonas];
    } else {
        datosFiltrados = datosPersonas.filter(persona => 
            persona.nombre_completo.toLowerCase().includes(busqueda) ||
            persona.grupo_familiar.toLowerCase().includes(busqueda)
        );
    }
    
    paginaActual = 1;
    aplicarPaginacion();
    actualizarContador();
}

// Función para limpiar búsqueda
function limpiarBusqueda() {
    document.getElementById('searchInput').value = '';
    filtrarPersonas();
}

// Función para cambiar items por página
function cambiarItemsPorPagina() {
    const select = document.getElementById('itemsPorPagina');
    const selectFooter = document.getElementById('itemsPorPaginaFooter');
    
    itemsPorPagina = parseInt(select.value);
    selectFooter.value = itemsPorPagina;
    
    paginaActual = 1;
    aplicarPaginacion();
    actualizarContador();
}

// Función para aplicar paginación
function aplicarPaginacion() {
    const inicio = (paginaActual - 1) * itemsPorPagina;
    const fin = inicio + itemsPorPagina;
    const datosPagina = datosFiltrados.slice(inicio, fin);
    
    mostrarDatos(datosPagina);
    generarPaginacion();
    actualizarInfoPaginacion();
}

// Función para mostrar datos en la tabla
function mostrarDatos(datos) {
    const tbody = document.getElementById('tablaReporteBody');
    tbody.innerHTML = '';
    
    if (datos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">No se encontraron personas</td></tr>';
        return;
    }
    
    datos.forEach(persona => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${persona.nombre_completo}</td>
            <td>${persona.grupo_familiar}</td>
            <td class="text-center">${persona.total_asistencias}</td>
            <td class="text-center">${persona.porcentaje}%</td>
            <td class="text-center">${persona.ultima_asistencia}</td>
            <td class="text-center">
                <button class="btn btn-sm btn-info" onclick="verDetalle(${persona.id})">
                    <i class="fas fa-eye"></i> Ver
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Función para generar paginación
function generarPaginacion() {
    const totalPaginas = Math.ceil(datosFiltrados.length / itemsPorPagina);
    const paginacion = document.getElementById('paginacion');
    paginacion.innerHTML = '';
    
    if (totalPaginas <= 1) return;
    
    // Botón anterior
    const liAnterior = document.createElement('li');
    liAnterior.className = `page-item ${paginaActual === 1 ? 'disabled' : ''}`;
    liAnterior.innerHTML = `<a class="page-link" href="#" onclick="cambiarPagina(${paginaActual - 1})">Anterior</a>`;
    paginacion.appendChild(liAnterior);
    
    // Números de página
    const inicio = Math.max(1, paginaActual - 2);
    const fin = Math.min(totalPaginas, paginaActual + 2);
    
    for (let i = inicio; i <= fin; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === paginaActual ? 'active' : ''}`;
        li.innerHTML = `<a class="page-link" href="#" onclick="cambiarPagina(${i})">${i}</a>`;
        paginacion.appendChild(li);
    }
    
    // Botón siguiente
    const liSiguiente = document.createElement('li');
    liSiguiente.className = `page-item ${paginaActual === totalPaginas ? 'disabled' : ''}`;
    liSiguiente.innerHTML = `<a class="page-link" href="#" onclick="cambiarPagina(${paginaActual + 1})">Siguiente</a>`;
    paginacion.appendChild(liSiguiente);
}

// Función para cambiar página
function cambiarPagina(pagina) {
    const totalPaginas = Math.ceil(datosFiltrados.length / itemsPorPagina);
    if (pagina >= 1 && pagina <= totalPaginas) {
        paginaActual = pagina;
        aplicarPaginacion();
    }
}

// Función para actualizar información de paginación
function actualizarInfoPaginacion() {
    const inicio = (paginaActual - 1) * itemsPorPagina + 1;
    const fin = Math.min(paginaActual * itemsPorPagina, datosFiltrados.length);
    const total = datosFiltrados.length;
    
    document.getElementById('infoPaginacion').textContent = 
        `Mostrando ${inicio} a ${fin} de ${total} personas`;
}

// Función para actualizar contador
function actualizarContador() {
    const total = datosFiltrados.length;
    const busqueda = document.getElementById('searchInput').value.trim();
    
    let mensaje = `Total: ${total} personas`;
    if (busqueda) {
        mensaje += ` (filtrado por: "${busqueda}")`;
    }
    
    document.getElementById('contadorResultados').textContent = mensaje;
}

// Función para mostrar error
function mostrarError(mensaje) {
    const tbody = document.getElementById('tablaReporteBody');
    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">${mensaje}</td></tr>`;
}

// Función para ver detalle (existente)
function verDetalle(personaId) {
    // Mostrar el modal
    const modal = new bootstrap.Modal(document.getElementById('modalDetalleAsistencias'));
    modal.show();
    
    // Mostrar indicador de carga
    document.getElementById('cargandoDetalle').style.display = 'block';
    document.getElementById('contenidoDetalle').style.display = 'none';
    document.getElementById('sinAsistencias').style.display = 'none';
    
    // Cargar datos de la persona y sus asistencias
    cargarDetalleAsistencias(personaId);
}

// Función para cargar detalle de asistencias (existente)
function cargarDetalleAsistencias(personaId) {
    const formData = new FormData();
    formData.append('action', 'obtener_detalle_asistencias_persona');
    formData.append('persona_id', personaId);
    formData.append('fecha_inicio', fechaInicio);
    formData.append('fecha_fin', fechaFin);
    
    fetch('reportes_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('cargandoDetalle').style.display = 'none';
        
        if (data.success) {
            // Mostrar información de la persona
            document.getElementById('nombrePersona').textContent = data.persona.nombre_completo;
            document.getElementById('grupoFamiliar').textContent = data.persona.grupo_familiar;
            
            // Mostrar asistencias
            const tablaAsistencias = document.getElementById('tablaAsistencias');
            tablaAsistencias.innerHTML = '';
            
            if (data.asistencias && data.asistencias.length > 0) {
                data.asistencias.forEach(asistencia => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${asistencia.fecha}</td>
                        <td>${asistencia.dia_semana}</td>
                        <td>${asistencia.tipo_culto}</td>
                        <td>
                            <span class="badge bg-success">
                                <i class="fas fa-check"></i> Asistió
                            </span>
                        </td>
                    `;
                    tablaAsistencias.appendChild(row);
                });
                
                document.getElementById('contenidoDetalle').style.display = 'block';
            } else {
                document.getElementById('sinAsistencias').style.display = 'block';
            }
        } else {
            console.error('Error al cargar detalle:', data.message);
            alert('Error al cargar el detalle de asistencias: ' + data.message);
        }
    })
    .catch(error => {
        document.getElementById('cargandoDetalle').style.display = 'none';
        console.error('Error en la petición:', error);
        alert('Error al cargar el detalle de asistencias');
    });
}

// Inicializar cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    cargarDatosIniciales();
});
</script>

<?php include '../includes/footer.php'; ?>
