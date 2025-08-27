<?php
require_once 'config.php';

// Simular sesión
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['nombre_completo'] = 'Administrador del Sistema';

echo "<!DOCTYPE html>\n";
echo "<html lang='es'>\n";
echo "<head>\n";
echo "    <meta charset='UTF-8'>\n";
echo "    <title>Test Render Personas</title>\n";
echo "    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>\n";
echo "</head>\n";
echo "<body>\n";
echo "<div class='container mt-4'>\n";

echo "<h1>Test de Renderizado de Personas</h1>\n";

try {
    $pdo = conectarDB();
    
    echo "<div class='alert alert-success'>✅ Conexión a BD exitosa</div>\n";
    
    // Ejecutar la consulta exacta
    $stmt = $pdo->query("SELECT p.*, gf.NOMBRE as grupo_familiar, r.nombre_rol as rol_nombre
                       FROM personas p 
                       LEFT JOIN grupos_familiares gf ON p.GRUPO_FAMILIAR_ID = gf.ID 
                       LEFT JOIN roles r ON p.ROL = r.id
                       ORDER BY p.ID
                       LIMIT 10");
    
    $personas = $stmt->fetchAll();
    
    echo "<div class='alert alert-info'>📊 Consulta ejecutada: " . count($personas) . " personas encontradas</div>\n";
    
    if (count($personas) > 0) {
        echo "<table class='table table-bordered'>\n";
        echo "<thead><tr>\n";
        echo "<th>ID</th>\n";
        echo "<th>Imagen</th>\n";
        echo "<th>RUT</th>\n";
        echo "<th>Nombres</th>\n";
        echo "<th>Apellido Paterno</th>\n";
        echo "<th>Apellido Materno</th>\n";
        echo "<th>Familia</th>\n";
        echo "<th>Rol</th>\n";
        echo "<th>Grupo Familiar</th>\n";
        echo "<th>Acciones</th>\n";
        echo "</tr></thead>\n";
        echo "<tbody>\n";
        
        foreach ($personas as $row) {
            // Determinar imagen por defecto según el sexo
            $imagenDefault = '';
            if ($row['URL_IMAGEN']) {
                $imagenDefault = $row['URL_IMAGEN'];
            } else {
                $imagenDefault = $row['SEXO'] === 'Femenino' ? 
                    '../assets/images/personas/default_female.svg' : 
                    '../assets/images/personas/default_male.svg';
            }
            
            echo "<tr>\n";
            echo "<td>" . $row['ID'] . "</td>\n";
            echo "<td><img src='" . htmlspecialchars($imagenDefault) . "' alt='Foto de " . htmlspecialchars($row['NOMBRES']) . "' class='img-thumbnail' style='width: 50px; height: 50px; object-fit: cover;' onerror=\"this.src='../assets/images/personas/default_male.svg'\"></td>\n";
            echo "<td>" . ($row['RUT'] ?? '-') . "</td>\n";
            echo "<td>" . $row['NOMBRES'] . "</td>\n";
            echo "<td>" . $row['APELLIDO_PATERNO'] . "</td>\n";
            echo "<td>" . ($row['APELLIDO_MATERNO'] ?? '-') . "</td>\n";
            echo "<td>" . ($row['FAMILIA'] ?? '-') . "</td>\n";
            echo "<td>" . ($row['rol_nombre'] ?? '-') . "</td>\n";
            echo "<td>" . ($row['grupo_familiar'] ?? 'Sin grupo') . "</td>\n";
            echo "<td>\n";
            echo "    <div class='btn-group' role='group'>\n";
            echo "        <button class='btn btn-sm btn-primary' onclick='verPersona(" . $row['ID'] . ")' title='Ver datos'>\n";
            echo "            <i class='fas fa-eye'></i>\n";
            echo "        </button>\n";
            echo "        <button class='btn btn-sm btn-info' onclick='editarPersona(" . $row['ID'] . ")' title='Editar'>\n";
            echo "            <i class='fas fa-edit'></i>\n";
            echo "        </button>\n";
            echo "        <button class='btn btn-sm btn-danger' onclick='eliminarPersona(" . $row['ID'] . ")' title='Eliminar'>\n";
            echo "            <i class='fas fa-trash'></i>\n";
            echo "        </button>\n";
            echo "    </div>\n";
            echo "</td>\n";
            echo "</tr>\n";
        }
        
        echo "</tbody>\n";
        echo "</table>\n";
        
        echo "<div class='alert alert-success'>✅ Tabla renderizada correctamente</div>\n";
        
    } else {
        echo "<div class='alert alert-warning'>⚠️ No se encontraron personas</div>\n";
    }
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>\n";
}

echo "</div>\n";
echo "</body>\n";
echo "</html>\n";
?>
