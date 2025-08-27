<?php
require_once 'config.php';

// Simular sesión
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['nombre_completo'] = 'Administrador del Sistema';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Simple - Gestión de Personas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid mt-3">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Test Simple - Gestión de Personas</h1>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Listado de Personas (Test)</h6>
            </div>
            <div class="card-body">
                <!-- Campo de búsqueda -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control" id="searchInput" placeholder="Buscar personas...">
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered" id="tablaPersonas" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Imagen</th>
                                <th>RUT</th>
                                <th>Nombres</th>
                                <th>Apellido Paterno</th>
                                <th>Apellido Materno</th>
                                <th>Familia</th>
                                <th>Rol</th>
                                <th>Grupo Familiar</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $pdo = conectarDB();
                                echo "<!-- Debug: Conexión exitosa -->\n";
                                
                                $stmt = $pdo->query("SELECT p.*, gf.NOMBRE as grupo_familiar, r.nombre_rol as rol_nombre
                                                   FROM personas p 
                                                   LEFT JOIN grupos_familiares gf ON p.GRUPO_FAMILIAR_ID = gf.ID 
                                                   LEFT JOIN roles r ON p.ROL = r.id
                                                   ORDER BY p.ID
                                                   LIMIT 20");
                                
                                $count = 0;
                                while ($row = $stmt->fetch()) {
                                    $count++;
                                    echo "<!-- Debug: Procesando persona $count -->\n";
                                    
                                    // Determinar imagen por defecto según el sexo
                                    $imagenDefault = '';
                                    if ($row['URL_IMAGEN']) {
                                        $imagenDefault = $row['URL_IMAGEN'];
                                    } else {
                                        $imagenDefault = $row['SEXO'] === 'Femenino' ? 
                                            'assets/images/personas/default_female.svg' : 
                                            'assets/images/personas/default_male.svg';
                                    }
                                    
                                    echo "<tr>";
                                    echo "<td>" . $row['ID'] . "</td>";
                                    echo "<td><img src='" . htmlspecialchars($imagenDefault) . "' alt='Foto de " . htmlspecialchars($row['NOMBRES']) . "' class='img-thumbnail' style='width: 50px; height: 50px; object-fit: cover;' onerror=\"this.src='assets/images/personas/default_male.svg'\"></td>";
                                    echo "<td>" . ($row['RUT'] ?? '-') . "</td>";
                                    echo "<td>" . $row['NOMBRES'] . "</td>";
                                    echo "<td>" . $row['APELLIDO_PATERNO'] . "</td>";
                                    echo "<td>" . ($row['APELLIDO_MATERNO'] ?? '-') . "</td>";
                                    echo "<td>" . ($row['FAMILIA'] ?? '-') . "</td>";
                                    echo "<td>" . ($row['rol_nombre'] ?? '-') . "</td>";
                                    echo "<td>" . ($row['grupo_familiar'] ?? 'Sin grupo') . "</td>";
                                    echo "<td>
                                            <div class='btn-group' role='group'>
                                                <button class='btn btn-sm btn-primary' onclick='alert(\"Ver persona " . $row['ID'] . "\")' title='Ver datos'>
                                                    <i class='fas fa-eye'></i>
                                                </button>
                                                <button class='btn btn-sm btn-info' onclick='alert(\"Editar persona " . $row['ID'] . "\")' title='Editar'>
                                                    <i class='fas fa-edit'></i>
                                                </button>
                                                <button class='btn btn-sm btn-danger' onclick='alert(\"Eliminar persona " . $row['ID'] . "\")' title='Eliminar'>
                                                    <i class='fas fa-trash'></i>
                                                </button>
                                            </div>
                                          </td>";
                                    echo "</tr>";
                                }
                                
                                echo "<!-- Debug: Total de personas procesadas: $count -->\n";
                                
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='11'>Error al cargar personas: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                                echo "<!-- Debug: Error PDO: " . htmlspecialchars($e->getMessage()) . " -->\n";
                            } catch (Exception $e) {
                                echo "<tr><td colspan='11'>Error general: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                                echo "<!-- Debug: Error general: " . htmlspecialchars($e->getMessage()) . " -->\n";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="alert alert-info">
                    <strong>Debug Info:</strong> Esta es una versión simplificada para probar la carga de datos.
                    Revisa el código fuente para ver los comentarios de debug.
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        console.log('JavaScript cargado correctamente');
        
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM cargado');
            
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                console.log('Campo de búsqueda encontrado');
                searchInput.addEventListener('input', function() {
                    console.log('Búsqueda:', this.value);
                });
            } else {
                console.log('Campo de búsqueda NO encontrado');
            }
            
            const tabla = document.getElementById('tablaPersonas');
            if (tabla) {
                console.log('Tabla encontrada');
                const filas = tabla.querySelectorAll('tbody tr');
                console.log('Filas en la tabla:', filas.length);
            } else {
                console.log('Tabla NO encontrada');
            }
        });
    </script>
</body>
</html>
