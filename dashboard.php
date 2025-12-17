<?php 
include 'includes/header.php'; 

// Obtener módulos del usuario para determinar qué cuadros mostrar
$modulosUsuario = obtenerModulosUsuario($_SESSION['usuario_id']);
$tieneAccesoAsistencias = false;
$tieneAccesoOfrendas = false;

foreach ($modulosUsuario as $modulo) {
    if ($modulo['nombre_modulo'] === 'Asistencias') {
        $tieneAccesoAsistencias = true;
    }
    if ($modulo['nombre_modulo'] === 'Ofrendas') {
        $tieneAccesoOfrendas = true;
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

<?php include 'includes/footer.php'; ?>
