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
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
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
                <tbody>
                    <?php
                    try {
                        // Consulta corregida para usar los nombres reales de las columnas
                        $sql = "SELECT 
                                    p.ID, p.NOMBRES, p.APELLIDO_PATERNO, p.APELLIDO_MATERNO, 
                                    COALESCE(p.FAMILIA, 'Sin familia') as grupo_familiar
                                 FROM personas p";
                        
                        $params = [];
                        
                        if ($grupo_familiar) {
                            $sql .= " WHERE p.FAMILIA = ?";
                            $params[] = $grupo_familiar;
                        }
                        
                        $sql .= " ORDER BY p.APELLIDO_PATERNO, p.NOMBRES";
                        
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($params);
                        $personas = $stmt->fetchAll();
                        
                        if (empty($personas)) {
                            echo "<tr><td colspan='6' class='text-center'>No se encontraron personas</td></tr>";
                        } else {
                            foreach ($personas as $persona) {
                                // Verificar que las claves existan antes de usarlas
                                if (!isset($persona['ID']) || !isset($persona['NOMBRES']) || !isset($persona['APELLIDO_PATERNO'])) {
                                    continue; // Saltar esta persona si faltan datos
                                }
                                
                                // Construir nombre completo
                                $nombre_completo = $persona['NOMBRES'];
                                if (isset($persona['APELLIDO_PATERNO']) && $persona['APELLIDO_PATERNO']) {
                                    $nombre_completo .= " " . $persona['APELLIDO_PATERNO'];
                                }
                                if (isset($persona['APELLIDO_MATERNO']) && $persona['APELLIDO_MATERNO']) {
                                    $nombre_completo .= " " . $persona['APELLIDO_MATERNO'];
                                }
                                
                                // Obtener estadísticas de asistencia para esta persona
                                $sql_asistencias = "SELECT COUNT(*) as total_asistencias, MAX(c.fecha) as ultima_asistencia
                                                   FROM asistencias a 
                                                   JOIN cultos c ON a.culto_id = c.id 
                                                   WHERE a.persona_id = ? AND c.fecha BETWEEN ? AND ?";
                                
                                $stmt_asistencias = $pdo->prepare($sql_asistencias);
                                $stmt_asistencias->execute([$persona['ID'], $fecha_inicio, $fecha_fin]);
                                $stats = $stmt_asistencias->fetch();
                                
                                $total_asistencias = $stats ? $stats['total_asistencias'] : 0;
                                $ultima_asistencia = $stats ? $stats['ultima_asistencia'] : null;
                                $porcentaje = $total_cultos > 0 ? round(($total_asistencias / $total_cultos) * 100, 1) : 0;
                                
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($nombre_completo) . "</td>";
                                echo "<td>" . htmlspecialchars($persona['grupo_familiar']) . "</td>";
                                echo "<td class='text-center'>" . $total_asistencias . "</td>";
                                echo "<td class='text-center'>" . $porcentaje . "%</td>";
                                echo "<td class='text-center'>" . ($ultima_asistencia ? date('d/m/Y', strtotime($ultima_asistencia)) : 'Nunca') . "</td>";
                                echo "<td class='text-center'>
                                        <button class='btn btn-sm btn-info' onclick='verDetalle(" . $persona['ID'] . ")'>
                                            <i class='fas fa-eye'></i> Ver
                                        </button>
                                      </td>";
                                echo "</tr>";
                            }
                        }
                    } catch (PDOException $e) {
                        echo "<tr><td colspan='6'>Error al cargar reporte: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function verDetalle(personaId) {
    // Implementar vista detallada de asistencias por persona
    alert('Ver detalle de persona ' + personaId);
}
</script>

<?php include '../includes/footer.php'; ?>
