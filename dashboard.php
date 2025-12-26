<?php 
include 'includes/header.php'; 

// Obtener módulos del usuario para determinar qué cuadros mostrar
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
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
</div>

<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Personas</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php
                            try {
                                $pdo = conectarDB();
                                $stmt = $pdo->query("SELECT COUNT(*) as total FROM personas");
                                $resultado = $stmt->fetch();
                                echo $resultado['total'];
                            } catch (PDOException $e) {
                                echo "0";
                            }
                            ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Cultos</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php
                            try {
                                $stmt = $pdo->query("SELECT COUNT(*) as total FROM cultos");
                                $resultado = $stmt->fetch();
                                echo $resultado['total'];
                            } catch (PDOException $e) {
                                echo "0";
                            }
                            ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-church fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Grupos Familiares</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php
                            try {
                                $stmt = $pdo->query("SELECT COUNT(*) as total FROM grupos_familiares");
                                $resultado = $stmt->fetch();
                                echo $resultado['total'];
                            } catch (PDOException $e) {
                                echo "0";
                            }
                            ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-home fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($tieneAccesoAsistencias): ?>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Total Asistencias</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php
                            try {
                                $stmt = $pdo->query("SELECT COUNT(*) as total FROM asistencias");
                                $resultado = $stmt->fetch();
                                echo $resultado['total'];
                            } catch (PDOException $e) {
                                echo "0";
                            }
                            ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($tieneAccesoOfrendas): ?>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Ofrendas</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php
                            try {
                                $stmt = $pdo->query("SELECT COALESCE(SUM(monto), 0) as total FROM ofrendas");
                                $resultado = $stmt->fetch();
                                $total = $resultado['total'] ?? 0;
                                echo "$" . number_format($total, 0, ',', '.');
                            } catch (PDOException $e) {
                                echo "$0";
                            }
                            ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Últimos Cultos</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Tipo</th>
                                <th>Asistentes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $stmt = $pdo->query("SELECT c.*, COUNT(a.PERSONA_ID) as asistentes 
                                                   FROM cultos c 
                                                   LEFT JOIN asistencias a ON c.ID = a.CULTO_ID 
                                                   GROUP BY c.ID 
                                                   ORDER BY c.FECHA DESC 
                                                   LIMIT 5");
                                while ($row = $stmt->fetch()) {
                                    echo "<tr>";
                                    echo "<td>" . date('d/m/Y', strtotime($row['FECHA'])) . "</td>";
                                    echo "<td>--</td>";
                                    echo "<td>" . $row['TIPO_CULTO'] . "</td>";
                                    echo "<td>" . $row['asistentes'] . "</td>";
                                    echo "</tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='4'>Error al cargar datos: " . $e->getMessage() . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Personas Recientes</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Grupo Familiar</th>
                                <th>Fecha Registro</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $stmt = $pdo->query("SELECT p.*, gf.NOMBRE as grupo_familiar 
                                                   FROM personas p 
                                                   LEFT JOIN grupos_familiares gf ON p.GRUPO_FAMILIAR_ID = gf.ID 
                                                   ORDER BY p.FECHA_CREACION DESC 
                                                   LIMIT 5");
                                while ($row = $stmt->fetch()) {
                                    echo "<tr>";
                                    echo "<td>" . $row['NOMBRES'] . " " . $row['APELLIDO_PATERNO'] . "</td>";
                                    echo "<td>" . ($row['grupo_familiar'] ?? 'Sin grupo') . "</td>";
                                    echo "<td>" . date('d/m/Y', strtotime($row['FECHA_CREACION'])) . "</td>";
                                    echo "</tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='3'>Error al cargar datos: " . $e->getMessage() . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos -->
<div class="row">
    <?php if ($tieneAccesoAsistencias): ?>
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Asistencias Domingos por Mes</h6>
            </div>
            <div class="card-body">
                <canvas id="graficoAsistenciasDomingos" height="100"></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($tieneAccesoOfrendas): ?>
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Ofrendas por Mes</h6>
            </div>
            <div class="card-body">
                <canvas id="graficoOfrendas" height="100"></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($tieneAccesoDiezmos): ?>
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Diezmos por Mes</h6>
            </div>
            <div class="card-body">
                <canvas id="graficoDiezmos" height="100"></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Configuración común para los gráficos
const chartOptions = {
    responsive: true,
    maintainAspectRatio: true,
    plugins: {
        legend: {
            display: true,
            position: 'top'
        },
        tooltip: {
            mode: 'index',
            intersect: false
        }
    },
    scales: {
        y: {
            beginAtZero: true
        }
    }
};

<?php if ($tieneAccesoAsistencias): ?>
// Gráfico de Asistencias Domingos
fetch('dashboard_actions.php?action=asistencias_domingos')
    .then(response => {
        if (!response.ok) {
            throw new Error('Error HTTP: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('Datos de asistencias:', data);
        if (data.success && data.data && data.data.length > 0) {
            const ctx = document.getElementById('graficoAsistenciasDomingos').getContext('2d');
            const labels = data.data.map(item => item.mes_nombre);
            const valores = data.data.map(item => parseInt(item.total_asistencias));
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Asistencias',
                        data: valores,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.1,
                        fill: true
                    }]
                },
                options: chartOptions
            });
        } else {
            console.log('No hay datos de asistencias o data está vacío');
            document.getElementById('graficoAsistenciasDomingos').parentElement.innerHTML = 
                '<p class="text-muted text-center">No hay datos disponibles</p>';
        }
    })
    .catch(error => {
        console.error('Error al cargar datos de asistencias:', error);
        document.getElementById('graficoAsistenciasDomingos').parentElement.innerHTML = 
            '<p class="text-danger text-center">Error al cargar los datos: ' + error.message + '</p>';
    });
<?php endif; ?>

<?php if ($tieneAccesoOfrendas): ?>
// Gráfico de Ofrendas
fetch('dashboard_actions.php?action=ofrendas_mes')
    .then(response => {
        if (!response.ok) {
            throw new Error('Error HTTP: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('Datos de ofrendas:', data);
        if (data.success && data.data && data.data.length > 0) {
            const ctx = document.getElementById('graficoOfrendas').getContext('2d');
            const labels = data.data.map(item => item.mes_nombre);
            const valores = data.data.map(item => parseFloat(item.total_ofrendas));
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Ofrendas (CLP)',
                        data: valores,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    ...chartOptions,
                    plugins: {
                        ...chartOptions.plugins,
                        tooltip: {
                            ...chartOptions.plugins.tooltip,
                            callbacks: {
                                label: function(context) {
                                    return 'Total: $' + new Intl.NumberFormat('es-CL').format(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + new Intl.NumberFormat('es-CL').format(value);
                                }
                            }
                        }
                    }
                }
            });
        } else {
            console.log('No hay datos de ofrendas o data está vacío');
            document.getElementById('graficoOfrendas').parentElement.innerHTML = 
                '<p class="text-muted text-center">No hay datos disponibles</p>';
        }
    })
    .catch(error => {
        console.error('Error al cargar datos de ofrendas:', error);
        document.getElementById('graficoOfrendas').parentElement.innerHTML = 
            '<p class="text-danger text-center">Error al cargar los datos: ' + error.message + '</p>';
    });
<?php endif; ?>

<?php if ($tieneAccesoDiezmos): ?>
// Gráfico de Diezmos
fetch('dashboard_actions.php?action=diezmos_mes')
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data.length > 0) {
            const ctx = document.getElementById('graficoDiezmos').getContext('2d');
            const labels = data.data.map(item => item.mes_nombre);
            const valores = data.data.map(item => parseFloat(item.total_diezmos));
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Diezmos (CLP)',
                        data: valores,
                        backgroundColor: 'rgba(255, 99, 132, 0.6)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    ...chartOptions,
                    plugins: {
                        ...chartOptions.plugins,
                        tooltip: {
                            ...chartOptions.plugins.tooltip,
                            callbacks: {
                                label: function(context) {
                                    return 'Total: $' + new Intl.NumberFormat('es-CL').format(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + new Intl.NumberFormat('es-CL').format(value);
                                }
                            }
                        }
                    }
                }
            });
        } else {
            document.getElementById('graficoDiezmos').parentElement.innerHTML = 
                '<p class="text-muted text-center">No hay datos disponibles</p>';
        }
    })
    .catch(error => {
        console.error('Error al cargar datos de diezmos:', error);
        document.getElementById('graficoDiezmos').parentElement.innerHTML = 
            '<p class="text-danger text-center">Error al cargar los datos</p>';
    });
<?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>
