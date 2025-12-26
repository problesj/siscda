<?php 
require_once dirname(__DIR__) . '/session_config.php';
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth_functions.php';

// Verificar autenticación
verificarAutenticacion();

// Verificar acceso al módulo de Reportes
verificarAccesoModulo('Reportes');

include '../includes/header.php'; 

// Obtener módulos del usuario para determinar qué reportes mostrar
$modulosUsuario = obtenerModulosUsuario($_SESSION['usuario_id']);
$tieneAccesoAsistencias = false;
$tieneAccesoOfrendas = false;
$tieneAccesoDiezmos = false;

foreach ($modulosUsuario as $modulo) {
    if ($modulo['nombre_modulo'] === 'Asistencias') {
        $tieneAccesoAsistencias = true;
    }
    if ($modulo['nombre_modulo'] === 'Ofrendas') {
        $tieneAccesoOfrendas = true;
    }
    if ($modulo['nombre_modulo'] === 'Diezmos') {
        $tieneAccesoDiezmos = true;
    }
}

// Si no tiene acceso a ningún módulo relacionado, redirigir
if (!$tieneAccesoAsistencias && !$tieneAccesoOfrendas && !$tieneAccesoDiezmos) {
    $_SESSION['error'] = 'No tienes acceso a ningún módulo para generar reportes';
    $baseUrl = getBaseUrl();
    header('Location: ' . $baseUrl . '/dashboard.php');
    exit();
}

// Obtener parámetros de filtro
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$grupo_familiar = $_GET['grupo_familiar'] ?? '';

// Determinar la pestaña activa por defecto
$tab_activa = $_GET['tab'] ?? '';
if (empty($tab_activa)) {
    // Determinar la primera pestaña disponible
    if ($tieneAccesoAsistencias) {
        $tab_activa = 'asistencias';
    } elseif ($tieneAccesoOfrendas) {
        $tab_activa = 'ofrendas';
    } elseif ($tieneAccesoDiezmos) {
        $tab_activa = 'diezmos';
    }
}

// Calcular total de cultos para el período
try {
    $pdo = conectarDB();
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM cultos WHERE FECHA BETWEEN ? AND ?");
    $stmt->execute([$fecha_inicio, $fecha_fin]);
    $result = $stmt->fetch();
    $total_cultos = $result ? $result['total'] : 0;
} catch (PDOException $e) {
    $total_cultos = 0;
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <?php 
        $totalModulos = ($tieneAccesoAsistencias ? 1 : 0) + ($tieneAccesoOfrendas ? 1 : 0) + ($tieneAccesoDiezmos ? 1 : 0);
        if ($totalModulos > 1) {
            echo 'Reportes';
        } elseif ($tieneAccesoAsistencias) {
            echo 'Reporte de Asistencias';
        } elseif ($tieneAccesoOfrendas) {
            echo 'Reporte de Ofrendas';
        } elseif ($tieneAccesoDiezmos) {
            echo 'Reporte de Diezmos';
        }
        ?>
    </h1>
</div>

<!-- Pestañas de navegación -->
<?php 
$totalModulos = ($tieneAccesoAsistencias ? 1 : 0) + ($tieneAccesoOfrendas ? 1 : 0) + ($tieneAccesoDiezmos ? 1 : 0);
if ($totalModulos > 1): ?>
<ul class="nav nav-tabs" id="reportesTabs" role="tablist">
    <?php if ($tieneAccesoAsistencias): ?>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?php echo $tab_activa === 'asistencias' ? 'active' : ''; ?>" 
                id="asistencias-tab" data-bs-toggle="tab" data-bs-target="#asistencias" 
                type="button" role="tab" aria-controls="asistencias" aria-selected="<?php echo $tab_activa === 'asistencias' ? 'true' : 'false'; ?>">
            <i class="fas fa-clipboard-check"></i> Reporte de Asistencias
        </button>
    </li>
    <?php endif; ?>
    <?php if ($tieneAccesoOfrendas): ?>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?php echo $tab_activa === 'ofrendas' ? 'active' : ''; ?>" 
                id="ofrendas-tab" data-bs-toggle="tab" data-bs-target="#ofrendas" 
                type="button" role="tab" aria-controls="ofrendas" aria-selected="<?php echo $tab_activa === 'ofrendas' ? 'true' : 'false'; ?>">
            <i class="fas fa-hand-holding-usd"></i> Reporte de Ofrendas
        </button>
    </li>
    <?php endif; ?>
    <?php if ($tieneAccesoDiezmos): ?>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?php echo $tab_activa === 'diezmos' ? 'active' : ''; ?>" 
                id="diezmos-tab" data-bs-toggle="tab" data-bs-target="#diezmos" 
                type="button" role="tab" aria-controls="diezmos" aria-selected="<?php echo $tab_activa === 'diezmos' ? 'true' : 'false'; ?>">
            <i class="fa-solid fa-envelope-open-text"></i> Reporte de Diezmos
        </button>
    </li>
    <?php endif; ?>
</ul>
<?php endif; ?>

<div class="tab-content" id="reportesTabsContent">
    <!-- Pestaña de Asistencias -->
    <?php if ($tieneAccesoAsistencias): ?>
    <div class="tab-pane fade <?php echo ($tieneAccesoAsistencias && $tieneAccesoOfrendas) ? ($tab_activa === 'asistencias' ? 'show active' : '') : 'show active'; ?>" 
         id="asistencias" role="tabpanel" aria-labelledby="asistencias-tab">
        
        <div class="row mb-4 mt-3">
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Filtros de Reporte</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="">
                            <input type="hidden" name="tab" value="asistencias">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                                        <input type="date" class="form-control" name="fecha_inicio" 
                                               value="<?php echo htmlspecialchars($fecha_inicio); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="fecha_fin" class="form-label">Fecha Fin</label>
                                        <input type="date" class="form-control" name="fecha_fin" 
                                               value="<?php echo htmlspecialchars($fecha_fin); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="grupo_familiar" class="form-label">Grupo Familiar</label>
                                <select class="form-select" name="grupo_familiar">
                                    <option value="">Todos los grupos</option>
                                    <?php
                                    try {
                                        $stmt = $pdo->query("SELECT DISTINCT FAMILIA FROM personas WHERE FAMILIA IS NOT NULL AND FAMILIA != '' ORDER BY FAMILIA");
                                        $familias = $stmt->fetchAll();
                                        
                                        foreach ($familias as $familia) {
                                            if (isset($familia['FAMILIA']) && $familia['FAMILIA'] !== '') {
                                                $selected = $grupo_familiar == $familia['FAMILIA'] ? 'selected' : '';
                                                echo "<option value='" . htmlspecialchars($familia['FAMILIA']) . "' $selected>" . htmlspecialchars($familia['FAMILIA']) . "</option>";
                                            }
                                        }
                                    } catch (PDOException $e) {
                                        echo "<option value=''>Error de base de datos</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Generar Reporte
                            </button>
                            <a href="reportes.php?tab=asistencias" class="btn btn-secondary ms-2">
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
                        try {
                            // Total de asistencias en el período
                            $sql_asistencias = "SELECT COUNT(*) as total FROM asistencias a 
                                               JOIN cultos c ON a.CULTO_ID = c.ID 
                                               JOIN personas p ON a.PERSONA_ID = p.ID 
                                               WHERE c.FECHA BETWEEN ? AND ?";
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
                            $sql_personas = "SELECT COUNT(DISTINCT a.PERSONA_ID) as total FROM asistencias a 
                                            JOIN cultos c ON a.CULTO_ID = c.ID 
                                            JOIN personas p ON a.PERSONA_ID = p.ID 
                                            WHERE c.FECHA BETWEEN ? AND ?";
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
                
                <div class="row mb-3">
                    <div class="col-12">
                        <div id="contadorResultados" class="text-muted"></div>
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
                        <tbody id="tablaReporteBody"></tbody>
                    </table>
                </div>
                
                <div class="row mt-3">
                    <div class="col-12 col-md-4 mb-2 mb-md-0">
                        <nav aria-label="Navegación de páginas">
                            <ul class="pagination pagination-sm justify-content-start mb-0" id="paginacion"></ul>
                        </nav>
                    </div>
                    <div class="col-12 col-md-4 text-center mb-2 mb-md-0">
                        <div id="infoPaginacion" class="text-muted"></div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="d-flex align-items-center justify-content-end">
                            <label class="me-2">Mostrar:</label>
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
    </div>
    <?php endif; ?>

    <!-- Pestaña de Ofrendas -->
    <?php if ($tieneAccesoOfrendas): ?>
    <div class="tab-pane fade <?php echo ($tieneAccesoAsistencias && $tieneAccesoOfrendas) ? ($tab_activa === 'ofrendas' ? 'show active' : '') : 'show active'; ?>" 
         id="ofrendas" role="tabpanel" aria-labelledby="ofrendas-tab">
        
        <div class="row mb-4 mt-3">
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Filtros de Reporte</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="">
                            <input type="hidden" name="tab" value="ofrendas">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="fecha_inicio_ofrendas" class="form-label">Fecha Inicio</label>
                                        <input type="date" class="form-control" name="fecha_inicio" 
                                               value="<?php echo htmlspecialchars($fecha_inicio); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="fecha_fin_ofrendas" class="form-label">Fecha Fin</label>
                                        <input type="date" class="form-control" name="fecha_fin" 
                                               value="<?php echo htmlspecialchars($fecha_fin); ?>">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Generar Reporte
                            </button>
                            <a href="reportes.php?tab=ofrendas" class="btn btn-secondary ms-2">
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
                        try {
                            // Total de ofrendas en el período
                            $sql_ofrendas = "SELECT COUNT(*) as total, SUM(o.monto) as total_monto 
                                            FROM ofrendas o 
                                            JOIN cultos c ON o.id_culto = c.ID 
                                            WHERE c.FECHA BETWEEN ? AND ?";
                            $stmt = $pdo->prepare($sql_ofrendas);
                            $stmt->execute([$fecha_inicio, $fecha_fin]);
                            $result = $stmt->fetch();
                            $total_ofrendas = $result ? $result['total'] : 0;
                            $total_monto = $result ? ($result['total_monto'] ?? 0) : 0;
                            
                            echo "<div class='row'>";
                            echo "<div class='col-md-6 text-center'>";
                            echo "<h4 class='text-primary'>$total_cultos</h4>";
                            echo "<small class='text-muted'>Cultos</small>";
                            echo "</div>";
                            echo "<div class='col-md-6 text-center'>";
                            echo "<h4 class='text-success'>$" . number_format($total_monto, 0, ',', '.') . "</h4>";
                            echo "<small class='text-muted'>Total Ofrendas</small>";
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
                <h6 class="m-0 font-weight-bold text-primary">Reporte Detallado de Ofrendas</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0" id="tablaOfrendas">
                        <thead>
                            <tr>
                                <th>ID Culto</th>
                                <th>Fecha</th>
                                <th>Tipo de Culto</th>
                                <th>Monto</th>
                            </tr>
                        </thead>
                        <tbody id="tablaOfrendasBody">
                            <?php
                            try {
                                $sql = "SELECT 
                                            c.ID as culto_id,
                                            c.FECHA,
                                            DATE_FORMAT(c.FECHA, '%d/%m/%Y') as fecha_formateada,
                                            c.TIPO_CULTO,
                                            COALESCE(o.monto, 0) as monto
                                        FROM cultos c
                                        LEFT JOIN ofrendas o ON c.ID = o.id_culto
                                        WHERE c.FECHA BETWEEN ? AND ?
                                        ORDER BY c.FECHA DESC";
                                
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([$fecha_inicio, $fecha_fin]);
                                $ofrendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                $total_general = 0;
                                
                                if (empty($ofrendas)) {
                                    echo "<tr><td colspan='4' class='text-center'>No se encontraron ofrendas en el período seleccionado</td></tr>";
                                } else {
                                    foreach ($ofrendas as $ofrenda) {
                                        $monto = floatval($ofrenda['monto']);
                                        $total_general += $monto;
                                        $monto_formateado = number_format($monto, 0, ',', '.');
                                        
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($ofrenda['culto_id']) . "</td>";
                                        echo "<td>" . htmlspecialchars($ofrenda['fecha_formateada']) . "</td>";
                                        echo "<td>" . htmlspecialchars($ofrenda['TIPO_CULTO']) . "</td>";
                                        echo "<td class='text-end'><strong>$" . $monto_formateado . "</strong></td>";
                                        echo "</tr>";
                                    }
                                    
                                    // Fila de total
                                    echo "<tr class='table-info'>";
                                    echo "<td colspan='3' class='text-end'><strong>TOTAL:</strong></td>";
                                    echo "<td class='text-end'><strong>$" . number_format($total_general, 0, ',', '.') . "</strong></td>";
                                    echo "</tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='4' class='text-center text-danger'>Error al cargar ofrendas: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Pestaña de Diezmos -->
    <?php if ($tieneAccesoDiezmos): ?>
    <div class="tab-pane fade <?php echo ($totalModulos > 1) ? ($tab_activa === 'diezmos' ? 'show active' : '') : 'show active'; ?>" 
         id="diezmos" role="tabpanel" aria-labelledby="diezmos-tab">
        
        <div class="card shadow mb-4 mt-3">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Reporte de Sobres de Diezmos</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0" id="tablaDiezmos">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Sobre</th>
                                <?php
                                // Obtener todos los años únicos con pagos registrados
                                try {
                                    $stmt = $pdo->query("SELECT DISTINCT anho FROM pagos_diezmos WHERE anho IS NOT NULL ORDER BY anho DESC");
                                    $anhos = $stmt->fetchAll(PDO::FETCH_COLUMN);
                                    
                                    foreach ($anhos as $anho) {
                                        echo "<th class='text-end'>Año $anho</th>";
                                    }
                                } catch (PDOException $e) {
                                    $anhos = [];
                                }
                                ?>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                // Obtener todos los sobres
                                $stmt = $pdo->query("SELECT id, sobre, estado_sobre FROM diezmos ORDER BY id");
                                $sobres = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                if (empty($sobres)) {
                                    $colspan = 3 + count($anhos);
                                    echo "<tr><td colspan='$colspan' class='text-center'>No hay sobres registrados</td></tr>";
                                } else {
                                    foreach ($sobres as $sobre) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($sobre['id']) . "</td>";
                                        echo "<td>" . htmlspecialchars($sobre['sobre']) . "</td>";
                                        
                                        // Para cada año, obtener el monto total del sobre
                                        foreach ($anhos as $anho) {
                                            $stmtPago = $pdo->prepare("
                                                SELECT 
                                                    (COALESCE(monto_enero, 0) + 
                                                     COALESCE(monto_febrero, 0) + 
                                                     COALESCE(monto_marzo, 0) + 
                                                     COALESCE(monto_abril, 0) + 
                                                     COALESCE(monto_mayo, 0) + 
                                                     COALESCE(monto_junio, 0) + 
                                                     COALESCE(monto_julio, 0) + 
                                                     COALESCE(monto_agosto, 0) + 
                                                     COALESCE(monto_septiembre, 0) + 
                                                     COALESCE(monto_octubre, 0) + 
                                                     COALESCE(monto_noviembre, 0) + 
                                                     COALESCE(monto_diciembre, 0)) as total_anual
                                                FROM pagos_diezmos
                                                WHERE id_sobre_diezmo = ? AND anho = ?
                                            ");
                                            $stmtPago->execute([$sobre['id'], $anho]);
                                            $pago = $stmtPago->fetch(PDO::FETCH_ASSOC);
                                            
                                            $montoAnual = $pago ? floatval($pago['total_anual']) : 0;
                                            
                                            if ($montoAnual > 0) {
                                                $montoFormateado = number_format($montoAnual, 0, ',', '.');
                                                echo "<td class='text-end'><strong>$" . $montoFormateado . "</strong></td>";
                                            } else {
                                                echo "<td class='text-end text-muted'>-</td>";
                                            }
                                        }
                                        
                                        // Estado del sobre
                                        $estadoTexto = intval($sobre['estado_sobre']) === 1 ? 'Activo' : 'Inactivo';
                                        $estadoBadge = intval($sobre['estado_sobre']) === 1 ? 'success' : 'danger';
                                        echo "<td><span class='badge bg-$estadoBadge'>$estadoTexto</span></td>";
                                        
                                        echo "</tr>";
                                    }
                                    
                                    // Fila de totales por año
                                    echo "<tr class='table-info'>";
                                    echo "<td colspan='2' class='text-end'><strong>TOTAL:</strong></td>";
                                    
                                    foreach ($anhos as $anho) {
                                        $stmtTotal = $pdo->prepare("
                                            SELECT 
                                                SUM(COALESCE(monto_enero, 0) + 
                                                    COALESCE(monto_febrero, 0) + 
                                                    COALESCE(monto_marzo, 0) + 
                                                    COALESCE(monto_abril, 0) + 
                                                    COALESCE(monto_mayo, 0) + 
                                                    COALESCE(monto_junio, 0) + 
                                                    COALESCE(monto_julio, 0) + 
                                                    COALESCE(monto_agosto, 0) + 
                                                    COALESCE(monto_septiembre, 0) + 
                                                    COALESCE(monto_octubre, 0) + 
                                                    COALESCE(monto_noviembre, 0) + 
                                                    COALESCE(monto_diciembre, 0)) as total_anual
                                            FROM pagos_diezmos
                                            WHERE anho = ?
                                        ");
                                        $stmtTotal->execute([$anho]);
                                        $total = $stmtTotal->fetch(PDO::FETCH_ASSOC);
                                        
                                        $totalAnual = $total ? floatval($total['total_anual']) : 0;
                                        
                                        if ($totalAnual > 0) {
                                            $totalFormateado = number_format($totalAnual, 0, ',', '.');
                                            echo "<td class='text-end'><strong>$" . $totalFormateado . "</strong></td>";
                                        } else {
                                            echo "<td class='text-end'>-</td>";
                                        }
                                    }
                                    
                                    echo "<td></td>"; // Columna de estado vacía
                                    echo "</tr>";
                                }
                            } catch (PDOException $e) {
                                $colspan = 3 + count($anhos);
                                echo "<tr><td colspan='$colspan' class='text-center text-danger'>Error al cargar los datos: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal para mostrar asistencias detalladas por persona -->
<?php if ($tieneAccesoAsistencias): ?>
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
                            <tbody id="tablaAsistencias"></tbody>
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
<?php endif; ?>

<script>
// Variables globales para paginación y búsqueda (Asistencias)
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
    liAnterior.innerHTML = `<a class="page-link" href="#" onclick="cambiarPagina(${paginaActual - 1}); return false;">Anterior</a>`;
    paginacion.appendChild(liAnterior);
    
    // Números de página
    const inicio = Math.max(1, paginaActual - 2);
    const fin = Math.min(totalPaginas, paginaActual + 2);
    
    for (let i = inicio; i <= fin; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === paginaActual ? 'active' : ''}`;
        li.innerHTML = `<a class="page-link" href="#" onclick="cambiarPagina(${i}); return false;">${i}</a>`;
        paginacion.appendChild(li);
    }
    
    // Botón siguiente
    const liSiguiente = document.createElement('li');
    liSiguiente.className = `page-item ${paginaActual === totalPaginas ? 'disabled' : ''}`;
    liSiguiente.innerHTML = `<a class="page-link" href="#" onclick="cambiarPagina(${paginaActual + 1}); return false;">Siguiente</a>`;
    paginacion.appendChild(liSiguiente);
}

// Función para cambiar página
function cambiarPagina(pagina) {
    const totalPaginas = Math.ceil(datosFiltrados.length / itemsPorPagina);
    if (pagina >= 1 && pagina <= totalPaginas) {
        paginaActual = pagina;
        aplicarPaginacion();
    }
    return false;
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

// Función para ver detalle
function verDetalle(personaId) {
    const modal = new bootstrap.Modal(document.getElementById('modalDetalleAsistencias'));
    modal.show();
    
    document.getElementById('cargandoDetalle').style.display = 'block';
    document.getElementById('contenidoDetalle').style.display = 'none';
    document.getElementById('sinAsistencias').style.display = 'none';
    
    cargarDetalleAsistencias(personaId);
}

// Función para cargar detalle de asistencias
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
            document.getElementById('nombrePersona').textContent = data.persona.nombre_completo;
            document.getElementById('grupoFamiliar').textContent = data.persona.grupo_familiar;
            
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

// Inicializar cuando se carga la página (solo para pestaña de asistencias)
<?php if ($tieneAccesoAsistencias): ?>
document.addEventListener('DOMContentLoaded', function() {
    const tabActiva = document.querySelector('#asistencias-tab');
    if (tabActiva && tabActiva.classList.contains('active')) {
        cargarDatosIniciales();
    } else if (!tabActiva) {
        // Si no hay pestañas (solo tiene acceso a asistencias), cargar directamente
        cargarDatosIniciales();
    }
    
    // Cargar datos cuando se cambia a la pestaña de asistencias
    const asistenciasTab = document.getElementById('asistencias-tab');
    if (asistenciasTab) {
        asistenciasTab.addEventListener('shown.bs.tab', function() {
            if (datosPersonas.length === 0) {
                cargarDatosIniciales();
            }
        });
    }
});
<?php endif; ?>
</script>

<?php include '../includes/footer.php'; ?>
