<?php
// Archivo de acciones para el módulo de personas
require_once dirname(__DIR__) . '/session_config.php';
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth_functions.php';

// Verificar autenticación
verificarAutenticacion();

// Verificar acceso al módulo de Personas
verificarAccesoModulo('Personas');

// Verificar si el usuario es Administrador del módulo
$esAdministrador = esAdministradorModulo($_SESSION['usuario_id'], 'Personas');

// Obtener la acción solicitada
$action = $_REQUEST['action'] ?? '';
    
    try {
        $pdo = conectarDB();
        
    switch ($action) {
        case 'obtener':
            // Obtener datos de una persona específica o solo roles/grupos si ID es 0
            $id = $_GET['id'] ?? 0;
            
            // Obtener roles disponibles para el formulario
            $stmtRoles = $pdo->query("SELECT id, nombre_rol FROM roles ORDER BY nombre_rol");
            $roles = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);
            
            // Obtener grupos familiares disponibles
            $stmtGrupos = $pdo->query("SELECT ID, NOMBRE FROM grupos_familiares ORDER BY NOMBRE");
            $gruposFamiliares = $stmtGrupos->fetchAll(PDO::FETCH_ASSOC);
            
            if ($id == 0) {
                // Solo retornar roles y grupos familiares para formularios nuevos
                echo json_encode([
                    'success' => true,
                    'persona' => null,
                    'roles' => $roles,
                    'gruposFamiliares' => $gruposFamiliares
                ]);
                break;
            }
            
            $sql = "SELECT p.*, gf.NOMBRE as GRUPO_FAMILIAR_NOMBRE, r.nombre_rol as ROL_NOMBRE 
                    FROM personas p 
                    LEFT JOIN grupos_familiares gf ON p.GRUPO_FAMILIAR_ID = gf.ID 
                    LEFT JOIN roles r ON p.ROL = r.id 
                    WHERE p.ID = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $persona = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$persona) {
                throw new Exception('Persona no encontrada');
            }
            
            echo json_encode([
                'success' => true,
                'persona' => $persona,
                'roles' => $roles,
                'gruposFamiliares' => $gruposFamiliares
            ]);
            break;
            
        case 'crear':
            // Solo administradores pueden crear
            if (!$esAdministrador) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'No tienes permisos para crear personas']);
                exit();
            }
            // Crear nueva persona
            $datos = [
                'RUT' => $_POST['rut'] ?? null,
                'NOMBRES' => $_POST['nombres'] ?? '',
                'APELLIDO_PATERNO' => $_POST['apellido_paterno'] ?? '',
                'APELLIDO_MATERNO' => $_POST['apellido_materno'] ?? null,
                'SEXO' => $_POST['sexo'] ?? null,
                'FECHA_NACIMIENTO' => $_POST['fecha_nacimiento'] ?: null,
                'TELEFONO' => $_POST['telefono'] ?? null,
                'FAMILIA' => $_POST['familia'] ?? null,
                'EMAIL' => $_POST['email'] ?? null,
                'ROL' => $_POST['rol'] ?? null,
                'GRUPO_FAMILIAR_ID' => $_POST['grupo_familiar_id'] ?? null,
                'OBSERVACIONES' => $_POST['observaciones'] ?? null,
                'FECHA_CREACION' => date('Y-m-d H:i:s')
            ];
            
            // Validar campos obligatorios
            if (empty($datos['NOMBRES']) || empty($datos['APELLIDO_PATERNO'])) {
                throw new Exception('Los campos Nombres y Apellido Paterno son obligatorios');
            }
            
            // Procesar imagen si se subió
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $imagen = procesarImagen($_FILES['imagen']);
                if ($imagen) {
                    $datos['URL_IMAGEN'] = $imagen;
                }
            }
            
            // Filtrar solo los campos que no son null para la inserción
            $datosInsert = array_filter($datos, function($valor) {
                return $valor !== null && $valor !== '';
            });
            
            // Insertar en la base de datos
            $campos = implode(', ', array_keys($datosInsert));
            $placeholders = ':' . implode(', :', array_keys($datosInsert));
            
            $sql = "INSERT INTO personas ($campos) VALUES ($placeholders)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($datosInsert);
            
            $nuevoId = $pdo->lastInsertId();
            
            $_SESSION['success'] = "Persona creada exitosamente con ID: $nuevoId";
            header('Location: personas.php');
            exit();
            break;
            
        case 'editar':
            // Solo administradores pueden editar
            if (!$esAdministrador) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'No tienes permisos para editar personas']);
                exit();
            }
            // Editar persona existente
            $id = $_POST['persona_id'] ?? 0;
            if (!$id) {
                throw new Exception('ID de persona no proporcionado');
            }
            
            $datos = [
                'RUT' => $_POST['rut'] ?? null,
                'NOMBRES' => $_POST['nombres'] ?? '',
                'APELLIDO_PATERNO' => $_POST['apellido_paterno'] ?? '',
                'APELLIDO_MATERNO' => $_POST['apellido_materno'] ?? null,
                'SEXO' => $_POST['sexo'] ?? null,
                'FECHA_NACIMIENTO' => $_POST['fecha_nacimiento'] ?: null,
                'TELEFONO' => $_POST['telefono'] ?? null,
                'FAMILIA' => $_POST['familia'] ?? null,
                'EMAIL' => $_POST['email'] ?? null,
                'ROL' => $_POST['rol'] ?? null,
                'GRUPO_FAMILIAR_ID' => $_POST['grupo_familiar_id'] ?? null,
                'OBSERVACIONES' => $_POST['observaciones'] ?? null,
                'FECHA_ACTUALIZACION' => date('Y-m-d H:i:s')
            ];
            
            // Validar campos obligatorios
            if (empty($datos['NOMBRES']) || empty($datos['APELLIDO_PATERNO'])) {
                throw new Exception('Los campos Nombres y Apellido Paterno son obligatorios');
            }
            
            // Procesar imagen si se subió
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $imagen = procesarImagen($_FILES['imagen']);
                if ($imagen) {
                    $datos['URL_IMAGEN'] = $imagen;
                }
            }
            
            // Filtrar solo los campos que no son null para la actualización
            $datosUpdate = array_filter($datos, function($valor) {
                return $valor !== null && $valor !== '';
            });
            
            // Actualizar en la base de datos
            $campos = [];
            foreach ($datosUpdate as $campo => $valor) {
                $campos[] = "$campo = :$campo";
            }
            
            $sql = "UPDATE personas SET " . implode(', ', $campos) . " WHERE ID = :id";
            $datosUpdate['id'] = $id;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($datosUpdate);
            
            $_SESSION['success'] = "Persona actualizada exitosamente";
            header('Location: personas.php');
            exit();
            break;
            
        case 'obtener_imagen':
            // Obtener la URL de la imagen de una persona
            $persona_id = $_POST['persona_id'] ?? 0;
            if (!$persona_id) {
                throw new Exception('ID de persona no proporcionado');
            }
            
            // Obtener la URL de la imagen
            $stmt = $pdo->prepare("SELECT URL_IMAGEN FROM personas WHERE ID = ?");
            $stmt->execute([$persona_id]);
            $imagen_url = $stmt->fetchColumn();
            
            if ($imagen_url) {
                echo json_encode([
                    'success' => true,
                    'imagen_url' => $imagen_url
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se encontró imagen para esta persona'
                ]);
            }
            exit();
            break;
            
        case 'eliminar':
            // Solo administradores pueden eliminar
            if (!$esAdministrador) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'No tienes permisos para eliminar personas']);
                exit();
            }
            // Eliminar persona
            $id = $_GET['id'] ?? 0;
            if (!$id) {
                throw new Exception('ID de persona no proporcionado');
            }
            
            // Verificar si la persona existe
            $stmt = $pdo->prepare("SELECT ID, NOMBRES, APELLIDO_PATERNO FROM personas WHERE ID = ?");
            $stmt->execute([$id]);
            $persona = $stmt->fetch();
            
            if (!$persona) {
                throw new Exception('Persona no encontrada');
            }
            
            // Eliminar imagen si existe
            $stmt = $pdo->prepare("SELECT URL_IMAGEN FROM personas WHERE ID = ?");
            $stmt->execute([$id]);
            $imagen = $stmt->fetchColumn();
            
            if ($imagen && file_exists(dirname(__DIR__) . '/' . $imagen)) {
                unlink(dirname(__DIR__) . '/' . $imagen);
            }
            
            // Eliminar de la base de datos
            $stmt = $pdo->prepare("DELETE FROM personas WHERE ID = ?");
            $stmt->execute([$id]);
            
            $_SESSION['success'] = "Persona {$persona['NOMBRES']} {$persona['APELLIDO_PATERNO']} eliminada exitosamente";
            header('Location: personas.php');
        exit();
            break;
            
        case 'obtener_grupos':
            $stmt = $pdo->query("
                SELECT gf.*, COUNT(p.ID) as miembros 
                FROM grupos_familiares gf 
                LEFT JOIN personas p ON gf.ID = p.GRUPO_FAMILIAR_ID 
                GROUP BY gf.ID 
                ORDER BY gf.NOMBRE
            ");
            $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'grupos' => $grupos
            ]);
            break;
            
        case 'crear_grupo':
            $nombre = $_POST['nombre'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            
            if (empty($nombre)) {
                throw new Exception('El nombre del grupo es requerido');
            }
            
            $stmt = $pdo->prepare("INSERT INTO grupos_familiares (NOMBRE, DESCRIPCION) VALUES (?, ?)");
            $stmt->execute([$nombre, $descripcion]);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Grupo familiar creado exitosamente'
            ]);
            break;
            
        case 'editar_grupo':
            $id = $_POST['id'] ?? 0;
            $nombre = $_POST['nombre'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            
            if (empty($nombre)) {
                throw new Exception('El nombre del grupo es requerido');
            }
            
            $stmt = $pdo->prepare("UPDATE grupos_familiares SET NOMBRE = ?, DESCRIPCION = ? WHERE ID = ?");
            $stmt->execute([$nombre, $descripcion, $id]);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Grupo familiar actualizado exitosamente'
            ]);
            break;
            
        case 'eliminar_grupo':
            $id = $_POST['id'] ?? 0;
            
            // Verificar si hay personas asignadas a este grupo
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM personas WHERE GRUPO_FAMILIAR_ID = ?");
            $stmt->execute([$id]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                throw new Exception('No se puede eliminar el grupo porque tiene personas asignadas');
            }
            
            $stmt = $pdo->prepare("DELETE FROM grupos_familiares WHERE ID = ?");
            $stmt->execute([$id]);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Grupo familiar eliminado exitosamente'
            ]);
            break;
            
        case 'obtener_roles':
            $stmt = $pdo->query("SELECT * FROM roles ORDER BY nombre_rol");
            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'roles' => $roles
            ]);
            break;
            
        case 'crear_rol':
            $nombre_rol = $_POST['nombre_rol'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            
            if (empty($nombre_rol)) {
                throw new Exception('El nombre del rol es requerido');
            }
            
            $stmt = $pdo->prepare("INSERT INTO roles (nombre_rol, descripcion) VALUES (?, ?)");
            $stmt->execute([$nombre_rol, $descripcion]);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Rol creado exitosamente'
            ]);
            break;
            
        case 'editar_rol':
            $id = $_POST['id'] ?? 0;
            $nombre_rol = $_POST['nombre_rol'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            
            if (empty($nombre_rol)) {
                throw new Exception('El nombre del rol es requerido');
            }
            
            $stmt = $pdo->prepare("UPDATE roles SET nombre_rol = ?, descripcion = ? WHERE id = ?");
            $stmt->execute([$nombre_rol, $descripcion, $id]);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Rol actualizado exitosamente'
            ]);
            break;
            
        case 'eliminar_rol':
            $id = $_POST['id'] ?? 0;
            
            // Verificar si hay personas asignadas a este rol
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM personas WHERE ROL = ?");
            $stmt->execute([$id]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                throw new Exception('No se puede eliminar el rol porque tiene personas asignadas');
            }
            
            $stmt = $pdo->prepare("DELETE FROM roles WHERE id = ?");
            $stmt->execute([$id]);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Rol eliminado exitosamente'
            ]);
            break;
            
        // Acciones para Visitas
        case 'obtener_visitas':
            // Obtener todas las visitas con información del culto
            $sql = "SELECT v.id, v.NOMBRES, v.APELLIDOS, v.OBSERVACIONES,
                           c.FECHA as fecha_culto, c.TIPO_CULTO as tipo_culto,
                           av.PRIMERA_VEZ, av.USUARIO_ID
                    FROM visitas v
                    LEFT JOIN asistencias_visitas av ON v.id = av.VISITA_ID
                    LEFT JOIN cultos c ON av.CULTO_ID = c.id
                    ORDER BY c.FECHA DESC, v.id DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $visitas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Formatear fechas
            foreach ($visitas as &$visita) {
                if ($visita['fecha_culto']) {
                    $visita['fecha_culto'] = date('d/m/Y', strtotime($visita['fecha_culto']));
                } else {
                    $visita['fecha_culto'] = 'Sin culto asignado';
                }
                if (!$visita['tipo_culto']) {
                    $visita['tipo_culto'] = 'N/A';
                }
                $visita['fecha_registro'] = 'N/A'; // No hay fecha de registro en la tabla
            }
            
            echo json_encode([
                'success' => true,
                'visitas' => $visitas
            ]);
            break;

        case 'obtener_visita':
            $visita_id = intval($_POST['visita_id'] ?? 0);
            
            if ($visita_id <= 0) {
                throw new Exception('ID de visita no válido');
            }
            
            $sql = "SELECT v.id, v.NOMBRES, v.APELLIDOS, v.OBSERVACIONES,
                           c.FECHA as fecha_culto, c.TIPO_CULTO as tipo_culto,
                           av.PRIMERA_VEZ, av.USUARIO_ID
                    FROM visitas v
                    LEFT JOIN asistencias_visitas av ON v.id = av.VISITA_ID
                    LEFT JOIN cultos c ON av.CULTO_ID = c.id
                    WHERE v.id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$visita_id]);
            $visita = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$visita) {
                throw new Exception('Visita no encontrada');
            }
            
            // Formatear fechas y agregar campos adicionales
            if ($visita['fecha_culto']) {
                $visita['fecha_culto'] = date('d/m/Y', strtotime($visita['fecha_culto']));
            } else {
                $visita['fecha_culto'] = 'Sin culto asignado';
            }
            if (!$visita['tipo_culto']) {
                $visita['tipo_culto'] = 'N/A';
            }
            $visita['fecha_registro'] = 'N/A';
            $visita['RUT'] = 'N/A';
            $visita['TELEFONO'] = 'N/A';
            $visita['EMAIL'] = 'N/A';
            
            echo json_encode([
                'success' => true,
                'visita' => $visita
            ]);
            break;

        case 'fusionar_visita_persona':
            error_log("=== INICIO FUSION VISITA PERSONA ===");
            error_log("POST data: " . json_encode($_POST));
            
            $visita_id = intval($_POST['visita_id'] ?? 0);
            $persona_id = intval($_POST['persona_id'] ?? 0);
            
            error_log("Visita ID: $visita_id, Persona ID: $persona_id");
            
            if ($visita_id <= 0 || $persona_id <= 0) {
                error_log("ERROR: IDs no válidos");
                throw new Exception('IDs no válidos');
            }
            
            // Obtener datos de la visita
            $stmt = $pdo->prepare("SELECT NOMBRES, APELLIDOS, OBSERVACIONES FROM visitas WHERE id = ?");
            $stmt->execute([$visita_id]);
            $visita = $stmt->fetch(PDO::FETCH_ASSOC);
            
            error_log("Datos de visita: " . json_encode($visita));
            
            if (!$visita) {
                error_log("ERROR: Visita no encontrada");
                throw new Exception('Visita no encontrada');
            }
            
            // Obtener datos de la persona
            $stmt = $pdo->prepare("SELECT ID FROM personas WHERE ID = ?");
            $stmt->execute([$persona_id]);
            $persona = $stmt->fetch(PDO::FETCH_ASSOC);
            
            error_log("Datos de persona: " . json_encode($persona));
            
            if (!$persona) {
                error_log("ERROR: Persona no encontrada");
                throw new Exception('Persona no encontrada');
            }
            
            // Actualizar datos de la persona con información de la visita
            $sql = "UPDATE personas SET 
                    NOMBRES = COALESCE(NULLIF(NOMBRES, ''), ?),
                    APELLIDO_PATERNO = COALESCE(NULLIF(APELLIDO_PATERNO, ''), ?)
                    WHERE ID = ?";
            
            error_log("SQL UPDATE: $sql");
            error_log("Parámetros: " . json_encode([$visita['NOMBRES'], $visita['APELLIDOS'], $persona_id]));
            
            $stmt = $pdo->prepare($sql);
            $resultado_update = $stmt->execute([
                $visita['NOMBRES'],
                $visita['APELLIDOS'],
                $persona_id
            ]);
            
            error_log("Resultado UPDATE: " . ($resultado_update ? 'SUCCESS' : 'FAILED'));
            
            // Eliminar primero los registros de asistencias_visitas
            $stmt = $pdo->prepare("DELETE FROM asistencias_visitas WHERE VISITA_ID = ?");
            $resultado_delete_av = $stmt->execute([$visita_id]);
            
            error_log("Resultado DELETE asistencias_visitas: " . ($resultado_delete_av ? 'SUCCESS' : 'FAILED'));
            
            // Eliminar la visita después de fusionar
            $stmt = $pdo->prepare("DELETE FROM visitas WHERE id = ?");
            $resultado_delete = $stmt->execute([$visita_id]);
            
            error_log("Resultado DELETE visitas: " . ($resultado_delete ? 'SUCCESS' : 'FAILED'));
            
            error_log("=== FUSION COMPLETADA EXITOSAMENTE ===");
            
            echo json_encode([
                'success' => true,
                'message' => 'Visita fusionada exitosamente con la persona'
            ]);
            break;

        case 'buscar_personas':
            $busqueda = $_POST['busqueda'] ?? '';
            $personas = buscarPersonas($busqueda);
            
            echo json_encode([
                'success' => true,
                'personas' => $personas
            ]);
            break;

        case 'obtener_culto_visita':
            $visita_id = intval($_POST['visita_id'] ?? 0);
            
            if ($visita_id <= 0) {
                throw new Exception('ID de visita no válido');
            }
            
            // Obtener el culto_id de la visita
            $sql = "SELECT av.CULTO_ID as culto_id
                    FROM asistencias_visitas av
                    WHERE av.VISITA_ID = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$visita_id]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$resultado) {
                throw new Exception('No se encontró culto asociado a esta visita');
            }
            
            echo json_encode([
                'success' => true,
                'culto_id' => $resultado['culto_id']
            ]);
            break;

        case 'crear_persona_desde_visita':
            error_log("=== INICIO CREAR PERSONA DESDE VISITA ===");
            error_log("POST data: " . json_encode($_POST));
            
            $visita_id = intval($_POST['visita_id'] ?? 0);
            $culto_id = intval($_POST['culto_id'] ?? 0);
            
            error_log("Visita ID: $visita_id, Culto ID: $culto_id");
            
            // Validar datos requeridos
            $nombres = trim($_POST['nombres'] ?? '');
            $apellido_paterno = trim($_POST['apellido_paterno'] ?? '');
            $rut = trim($_POST['rut'] ?? '');
            
            error_log("Datos validados - Nombres: '$nombres', Apellido: '$apellido_paterno', RUT: '$rut'");
            
            if (empty($nombres) || empty($apellido_paterno)) {
                error_log("ERROR: Campos obligatorios faltantes");
                throw new Exception('Los campos Nombres y Apellido Paterno son obligatorios');
            }
            
            if ($visita_id <= 0 || $culto_id <= 0) {
                throw new Exception('IDs de visita o culto no válidos');
            }
            
            // Verificar que el RUT no exista (solo si se proporciona)
            if (!empty($rut)) {
                $stmt = $pdo->prepare("SELECT ID FROM personas WHERE RUT = ?");
                $stmt->execute([$rut]);
                if ($stmt->fetch()) {
                    throw new Exception('Ya existe una persona con este RUT');
                }
            }
            
            // Crear la nueva persona
            $sql = "INSERT INTO personas (
                        RUT, NOMBRES, APELLIDO_PATERNO, APELLIDO_MATERNO, 
                        SEXO, FECHA_NACIMIENTO, TELEFONO, EMAIL, 
                        FAMILIA, ROL, GRUPO_FAMILIAR_ID, OBSERVACIONES
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            // Procesar fecha de nacimiento
            $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null;
            if (empty($fecha_nacimiento)) {
                $fecha_nacimiento = null;
            }
            
            // Procesar grupo familiar
            $grupo_familiar = $_POST['grupo_familiar'] ?? null;
            if (empty($grupo_familiar)) {
                $grupo_familiar = null;
            } else {
                $grupo_familiar = intval($grupo_familiar);
            }
            
            // Procesar rol
            $rol = $_POST['rol'] ?? null;
            if (empty($rol)) {
                $rol = null;
            } else {
                $rol = intval($rol);
            }
            
            $parametros = [
                $rut,
                $nombres,
                $apellido_paterno,
                trim($_POST['apellido_materno'] ?? ''),
                $_POST['sexo'] ?? null,
                $fecha_nacimiento,
                trim($_POST['telefono'] ?? ''),
                trim($_POST['email'] ?? ''),
                trim($_POST['familia'] ?? ''),
                $rol,
                $grupo_familiar,
                trim($_POST['observaciones'] ?? '')
            ];
            
            error_log("SQL INSERT: $sql");
            error_log("Parámetros: " . json_encode($parametros));
            
            try {
                $stmt = $pdo->prepare($sql);
                error_log("Preparando statement...");
                
                $resultado = $stmt->execute($parametros);
                error_log("Ejecutando statement...");
                
                error_log("Resultado INSERT persona: " . ($resultado ? 'SUCCESS' : 'FAILED'));
                
                if (!$resultado) {
                    $errorInfo = $stmt->errorInfo();
                    error_log("Error SQL: " . json_encode($errorInfo));
                    throw new Exception('Error al crear la persona: ' . $errorInfo[2]);
                }
            } catch (Exception $e) {
                error_log("EXCEPTION en INSERT persona: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                throw $e;
            }
            
            $persona_id = $pdo->lastInsertId();
            error_log("Persona creada con ID: $persona_id");
            
            // Crear la asistencia al culto
            error_log("Creando asistencia al culto...");
            $sql_asistencia = "INSERT INTO asistencias (PERSONA_ID, CULTO_ID, PRIMERA_VEZ, USUARIO_ID) VALUES (?, ?, ?, ?)";
            $parametros_asistencia = [
                $persona_id,
                $culto_id,
                intval($_POST['primera_vez'] ?? 1),
                1 // Usuario ID por defecto
            ];
            
            error_log("SQL Asistencia: $sql_asistencia");
            error_log("Parámetros Asistencia: " . json_encode($parametros_asistencia));
            
            try {
                $stmt_asistencia = $pdo->prepare($sql_asistencia);
                $resultado_asistencia = $stmt_asistencia->execute($parametros_asistencia);
                
                error_log("Resultado INSERT asistencia: " . ($resultado_asistencia ? 'SUCCESS' : 'FAILED'));
                
                if (!$resultado_asistencia) {
                    $errorInfo = $stmt_asistencia->errorInfo();
                    error_log("Error SQL Asistencia: " . json_encode($errorInfo));
                    // Si falla la asistencia, eliminar la persona creada
                    $pdo->prepare("DELETE FROM personas WHERE ID = ?")->execute([$persona_id]);
                    throw new Exception('Error al crear la asistencia al culto: ' . $errorInfo[2]);
                }
            } catch (Exception $e) {
                error_log("EXCEPTION en INSERT asistencia: " . $e->getMessage());
                // Si falla la asistencia, eliminar la persona creada
                $pdo->prepare("DELETE FROM personas WHERE ID = ?")->execute([$persona_id]);
                throw $e;
            }
            
            // Eliminar los registros de asistencias_visitas
            $stmt = $pdo->prepare("DELETE FROM asistencias_visitas WHERE VISITA_ID = ?");
            $stmt->execute([$visita_id]);
            
            // Eliminar la visita
            $stmt = $pdo->prepare("DELETE FROM visitas WHERE id = ?");
            $resultado_delete_visita = $stmt->execute([$visita_id]);
            
            error_log("Resultado DELETE visita: " . ($resultado_delete_visita ? 'SUCCESS' : 'FAILED'));
            
            error_log("=== PERSONA CREADA EXITOSAMENTE ===");
            
            echo json_encode([
                'success' => true,
                'message' => 'Persona creada exitosamente y asociada al culto'
            ]);
            break;

        case 'validar_asistencia_persona':
            $visita_id = intval($_POST['visita_id'] ?? 0);
            $persona_id = intval($_POST['persona_id'] ?? 0);
            
            if ($visita_id <= 0 || $persona_id <= 0) {
                throw new Exception('IDs no válidos');
            }
            
            // Obtener información del culto al que asistió la visita
            $sql_culto = "SELECT c.ID as culto_id, c.FECHA, c.TIPO_CULTO
                         FROM visitas v
                         LEFT JOIN asistencias_visitas av ON v.id = av.VISITA_ID
                         LEFT JOIN cultos c ON av.CULTO_ID = c.ID
                         WHERE v.id = ?";
            
            $stmt_culto = $pdo->prepare($sql_culto);
            $stmt_culto->execute([$visita_id]);
            $culto_visita = $stmt_culto->fetch(PDO::FETCH_ASSOC);
            
            if (!$culto_visita || !$culto_visita['culto_id']) {
                throw new Exception('No se encontró información del culto para esta visita');
            }
            
            // Verificar si la persona ya está registrada como asistente a este culto
            $sql_asistencia = "SELECT COUNT(*) as total_asistencias
                              FROM asistencias a
                              WHERE a.PERSONA_ID = ? AND a.CULTO_ID = ?";
            
            $stmt_asistencia = $pdo->prepare($sql_asistencia);
            $stmt_asistencia->execute([$persona_id, $culto_visita['culto_id']]);
            $asistencia_existente = $stmt_asistencia->fetch(PDO::FETCH_ASSOC);
            
            $conflicto_asistencia = $asistencia_existente['total_asistencias'] > 0;
            $fecha_culto = $culto_visita['FECHA'] ? date('d/m/Y', strtotime($culto_visita['FECHA'])) : 'Fecha no disponible';
            
            echo json_encode([
                'success' => true,
                'conflicto_asistencia' => $conflicto_asistencia,
                'fecha_culto' => $fecha_culto,
                'tipo_culto' => $culto_visita['TIPO_CULTO'] ?? 'N/A',
                'culto_id' => $culto_visita['culto_id']
            ]);
            break;

        case 'agregar_visita_personas':
            $visita_id = intval($_POST['visita_id'] ?? 0);
            $personas_ids = $_POST['personas_ids'] ?? [];
            
            if ($visita_id <= 0 || empty($personas_ids)) {
                throw new Exception('Datos no válidos');
            }
            
            // Obtener datos de la visita
            $stmt = $pdo->prepare("SELECT NOMBRES, APELLIDOS, OBSERVACIONES FROM visitas WHERE id = ?");
            $stmt->execute([$visita_id]);
            $visita = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$visita) {
                throw new Exception('Visita no encontrada');
            }
            
            // Actualizar datos de las personas seleccionadas
            $personas_actualizadas = 0;
            foreach ($personas_ids as $persona_id) {
                $persona_id = intval($persona_id);
                if ($persona_id <= 0) continue;
                
                // Verificar si la persona existe
                $stmt = $pdo->prepare("SELECT ID FROM personas WHERE ID = ?");
                $stmt->execute([$persona_id]);
                if ($stmt->fetch()) {
                    // Actualizar datos de la persona existente
                    $sql = "UPDATE personas SET 
                            NOMBRES = COALESCE(NULLIF(NOMBRES, ''), ?),
                            APELLIDO_PATERNO = COALESCE(NULLIF(APELLIDO_PATERNO, ''), ?)
                            WHERE ID = ?";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $visita['NOMBRES'],
                        $visita['APELLIDOS'],
                        $persona_id
                    ]);
                    $personas_actualizadas++;
                }
            }
            
            // Eliminar la visita después de agregar a personas
            $stmt = $pdo->prepare("DELETE FROM visitas WHERE id = ?");
            $stmt->execute([$visita_id]);
            
            echo json_encode([
                'success' => true,
                'message' => "Visita agregada exitosamente a {$personas_actualizadas} persona(s)"
            ]);
            break;
            
        case 'buscar_duplicados':
            // Buscar personas duplicadas de una persona específica
            header('Content-Type: application/json');
            
            try {
                $personaId = $_POST['persona_id'] ?? $_GET['persona_id'] ?? null;
                
                if (!$personaId) {
                    throw new Exception('ID de persona no proporcionado');
                }
                
                // Obtener datos de la persona seleccionada
                $stmt = $pdo->prepare("SELECT * FROM personas WHERE ID = ?");
                $stmt->execute([$personaId]);
                $personaSeleccionada = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$personaSeleccionada) {
                    throw new Exception('Persona no encontrada');
                }
                
                // Buscar duplicados de esta persona específica SOLO por nombre y apellido paterno
                if (empty($personaSeleccionada['NOMBRES']) || empty($personaSeleccionada['APELLIDO_PATERNO'])) {
                    echo json_encode([
                        'success' => true,
                        'duplicados' => [],
                        'message' => 'No se puede buscar duplicados: la persona no tiene nombre y apellido paterno'
                    ]);
                    break;
                }
                
                // Obtener solo la primera palabra del apellido paterno
                $apellidoPaternoPrimeraPalabra = trim(explode(' ', trim($personaSeleccionada['APELLIDO_PATERNO']))[0]);
                
                // Construir la consulta - solo por nombre y primera palabra del apellido paterno
                $sql = "SELECT 
                            p.ID,
                            p.NOMBRES,
                            p.APELLIDO_PATERNO,
                            p.APELLIDO_MATERNO,
                            p.RUT,
                            p.FAMILIA,
                            gf.NOMBRE as GRUPO_FAMILIAR_NOMBRE,
                            (SELECT COUNT(*) FROM asistencias a WHERE a.PERSONA_ID = p.ID) as TOTAL_ASISTENCIAS
                        FROM personas p
                        LEFT JOIN grupos_familiares gf ON p.GRUPO_FAMILIAR_ID = gf.ID
                        WHERE p.ID != ?
                        AND LOWER(TRIM(p.NOMBRES)) = LOWER(TRIM(?))
                        AND LOWER(TRIM(SUBSTRING_INDEX(p.APELLIDO_PATERNO, ' ', 1))) = LOWER(TRIM(?))
                        AND p.NOMBRES IS NOT NULL 
                        AND p.NOMBRES != ''
                        AND p.APELLIDO_PATERNO IS NOT NULL 
                        AND p.APELLIDO_PATERNO != ''
                        ORDER BY p.NOMBRES, p.APELLIDO_PATERNO, p.ID";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $personaId,
                    trim($personaSeleccionada['NOMBRES']),
                    $apellidoPaternoPrimeraPalabra
                ]);
                $duplicados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Agregar la persona seleccionada al inicio del grupo
                $stmt = $pdo->prepare("SELECT 
                            p.ID,
                            p.NOMBRES,
                            p.APELLIDO_PATERNO,
                            p.APELLIDO_MATERNO,
                            p.RUT,
                            p.FAMILIA,
                            gf.NOMBRE as GRUPO_FAMILIAR_NOMBRE,
                            (SELECT COUNT(*) FROM asistencias a WHERE a.PERSONA_ID = p.ID) as TOTAL_ASISTENCIAS
                        FROM personas p
                        LEFT JOIN grupos_familiares gf ON p.GRUPO_FAMILIAR_ID = gf.ID
                        WHERE p.ID = ?");
                $stmt->execute([$personaId]);
                $personaPrincipal = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Crear el grupo con la persona principal primero
                $grupo = [$personaPrincipal];
                foreach ($duplicados as $duplicado) {
                    $grupo[] = $duplicado;
                }
                
                echo json_encode([
                    'success' => true,
                    'duplicados' => [$grupo], // Siempre un solo grupo ahora
                    'total_grupos' => 1
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al buscar duplicados: ' . $e->getMessage()
                ]);
            }
            break;
            
        case 'unificar_personas':
            // Unificar personas duplicadas y transferir sus asistencias
            header('Content-Type: application/json');
            
            try {
                $unificaciones = json_decode($_POST['unificaciones'] ?? '[]', true);
                
                if (empty($unificaciones)) {
                    throw new Exception('No se proporcionaron unificaciones para procesar');
                }
                
                $pdo->beginTransaction();
                
                $totalUnificadas = 0;
                $totalAsistencias = 0;
                
                foreach ($unificaciones as $unificacion) {
                    $personaPrincipalId = intval($unificacion['persona_principal_id']);
                    $personasDuplicadas = array_map('intval', $unificacion['personas_duplicadas']);
                    
                    if (empty($personasDuplicadas)) {
                        continue;
                    }
                    
                    // Verificar que la persona principal existe
                    $stmt = $pdo->prepare("SELECT ID FROM personas WHERE ID = ?");
                    $stmt->execute([$personaPrincipalId]);
                    if (!$stmt->fetch()) {
                        throw new Exception("La persona principal con ID $personaPrincipalId no existe");
                    }
                    
                    // Obtener datos de la persona principal para combinarlos con los duplicados
                    $stmt = $pdo->prepare("SELECT * FROM personas WHERE ID = ?");
                    $stmt->execute([$personaPrincipalId]);
                    $personaPrincipal = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Para cada persona duplicada
                    foreach ($personasDuplicadas as $personaDuplicadaId) {
                        // Verificar que existe
                        $stmt = $pdo->prepare("SELECT * FROM personas WHERE ID = ?");
                        $stmt->execute([$personaDuplicadaId]);
                        $personaDuplicada = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if (!$personaDuplicada) {
                            continue;
                        }
                        
                        // Transferir TODAS las asistencias de la persona duplicada a la principal
                        // Primero, obtener todas las asistencias de la persona duplicada
                        $stmt = $pdo->prepare("SELECT CULTO_ID, PRIMERA_VEZ, USUARIO_ID FROM asistencias WHERE PERSONA_ID = ?");
                        $stmt->execute([$personaDuplicadaId]);
                        $asistencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        error_log("=== INICIO TRANSFERENCIA ===");
                        error_log("Persona duplicada ID: $personaDuplicadaId");
                        error_log("Persona principal ID: $personaPrincipalId");
                        error_log("Total asistencias a transferir: " . count($asistencias));
                        
                        if (count($asistencias) > 0) {
                            foreach ($asistencias as $asistencia) {
                                $cultoId = $asistencia['CULTO_ID'];
                                $primeraVez = $asistencia['PRIMERA_VEZ'];
                                $usuarioId = $asistencia['USUARIO_ID'];
                                
                                error_log("Procesando asistencia - Culto ID: $cultoId, Primera Vez: $primeraVez, Usuario ID: $usuarioId");
                                
                                // Verificar si la persona principal ya tiene asistencia en ese culto
                                $stmt = $pdo->prepare("SELECT COUNT(*) as existe FROM asistencias WHERE PERSONA_ID = ? AND CULTO_ID = ?");
                                $stmt->execute([$personaPrincipalId, $cultoId]);
                                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                                $existe = intval($resultado['existe']);
                                
                                error_log("Verificación: existe = $existe");
                                
                                if ($existe == 0) {
                                    // Si no existe, crear la asistencia
                                    try {
                                        $stmt = $pdo->prepare("INSERT INTO asistencias (PERSONA_ID, CULTO_ID, PRIMERA_VEZ, USUARIO_ID) VALUES (?, ?, ?, ?)");
                                        $resultado_insert = $stmt->execute([
                                            $personaPrincipalId,
                                            $cultoId,
                                            $primeraVez,
                                            $usuarioId
                                        ]);
                                        
                                        // Verificar que realmente se insertó
                                        $stmt_verificar = $pdo->prepare("SELECT COUNT(*) as insertado FROM asistencias WHERE PERSONA_ID = ? AND CULTO_ID = ?");
                                        $stmt_verificar->execute([$personaPrincipalId, $cultoId]);
                                        $verificacion = $stmt_verificar->fetch(PDO::FETCH_ASSOC);
                                        
                                        if ($resultado_insert && intval($verificacion['insertado']) > 0) {
                                            $totalAsistencias++;
                                            error_log("✓ Asistencia transferida exitosamente: Culto $cultoId de persona $personaDuplicadaId a persona $personaPrincipalId");
                                        } else {
                                            $errorInfo = $stmt->errorInfo();
                                            error_log("✗ Error al transferir asistencia: Culto $cultoId - " . ($errorInfo[2] ?? 'Error desconocido'));
                                            error_log("  Verificación post-insert: " . $verificacion['insertado']);
                                            throw new Exception("Error al transferir asistencia del culto $cultoId: " . ($errorInfo[2] ?? 'Error desconocido'));
                                        }
                                    } catch (PDOException $e) {
                                        error_log("✗ Excepción PDO al transferir asistencia: " . $e->getMessage());
                                        error_log("  Código de error: " . $e->getCode());
                                        // Si es un error de duplicado (clave única), la asistencia ya existe, continuar
                                        if ($e->getCode() == '23000' || strpos($e->getCode(), '23000') !== false) {
                                            error_log("  (Error de duplicado ignorado, la asistencia ya existe)");
                                            // Verificar si realmente existe ahora
                                            $stmt_verificar = $pdo->prepare("SELECT COUNT(*) as existe_ahora FROM asistencias WHERE PERSONA_ID = ? AND CULTO_ID = ?");
                                            $stmt_verificar->execute([$personaPrincipalId, $cultoId]);
                                            $verificacion_ahora = $stmt_verificar->fetch(PDO::FETCH_ASSOC);
                                            if (intval($verificacion_ahora['existe_ahora']) > 0) {
                                                $totalAsistencias++;
                                                error_log("  (Asistencia ya existe, contada como transferida)");
                                            }
                                        } else {
                                            throw $e;
                                        }
                                    }
                                } else {
                                    error_log("→ La persona principal ya tiene asistencia en el culto $cultoId, no se duplica");
                                }
                            }
                        } else {
                            error_log("No hay asistencias para transferir de la persona $personaDuplicadaId");
                        }
                        
                        error_log("=== FIN TRANSFERENCIA ===");
                        error_log("Total asistencias transferidas hasta ahora: $totalAsistencias");
                        
                        // Combinar datos: actualizar la persona principal con datos de la duplicada si están vacíos
                        $actualizaciones = [];
                        $valores = [];
                        
                        $campos = ['RUT', 'APELLIDO_MATERNO', 'SEXO', 'FECHA_NACIMIENTO', 'TELEFONO', 'EMAIL', 'FAMILIA', 'ROL', 'GRUPO_FAMILIAR_ID', 'OBSERVACIONES', 'URL_IMAGEN'];
                        
                        foreach ($campos as $campo) {
                            if (empty($personaPrincipal[$campo]) && !empty($personaDuplicada[$campo])) {
                                $actualizaciones[] = "$campo = ?";
                                $valores[] = $personaDuplicada[$campo];
                            }
                        }
                        
                        if (!empty($actualizaciones)) {
                            $valores[] = $personaPrincipalId;
                            $sql = "UPDATE personas SET " . implode(', ', $actualizaciones) . ", FECHA_ACTUALIZACION = NOW() WHERE ID = ?";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute($valores);
                            
                            // Refrescar los datos de la persona principal después de la actualización
                            $stmt = $pdo->prepare("SELECT * FROM personas WHERE ID = ?");
                            $stmt->execute([$personaPrincipalId]);
                            $personaPrincipal = $stmt->fetch(PDO::FETCH_ASSOC);
                        }
                        
                        // Verificar que las asistencias se transfirieron correctamente antes de eliminar
                        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM asistencias WHERE PERSONA_ID = ?");
                        $stmt->execute([$personaPrincipalId]);
                        $resultado_final = $stmt->fetch(PDO::FETCH_ASSOC);
                        $totalAsistenciasPrincipal = intval($resultado_final['total']);
                        
                        error_log("Verificación final: La persona principal $personaPrincipalId ahora tiene $totalAsistenciasPrincipal asistencias");
                        
                        // Eliminar las asistencias de la persona duplicada (ya fueron transferidas)
                        $stmt = $pdo->prepare("DELETE FROM asistencias WHERE PERSONA_ID = ?");
                        $resultado_delete = $stmt->execute([$personaDuplicadaId]);
                        $asistenciasEliminadas = $stmt->rowCount();
                        
                        error_log("Asistencias eliminadas de persona duplicada $personaDuplicadaId: $asistenciasEliminadas (resultado: " . ($resultado_delete ? 'OK' : 'ERROR') . ")");
                        
                        // Eliminar la persona duplicada
                        $stmt = $pdo->prepare("DELETE FROM personas WHERE ID = ?");
                        $resultado_delete_persona = $stmt->execute([$personaDuplicadaId]);
                        
                        error_log("Persona duplicada $personaDuplicadaId eliminada: " . ($resultado_delete_persona ? 'OK' : 'ERROR'));
                        
                        $totalUnificadas++;
                    }
                }
                
                // Verificación final antes de commit
                error_log("=== RESUMEN FINAL ===");
                error_log("Total personas unificadas: $totalUnificadas");
                error_log("Total asistencias transferidas: $totalAsistencias");
                
                $pdo->commit();
                error_log("Transacción confirmada (COMMIT)");
                
                $mensaje = "Se unificaron exitosamente $totalUnificadas persona(s) duplicada(s)";
                if ($totalAsistencias > 0) {
                    $mensaje .= " y se transfirieron $totalAsistencias asistencia(s) a la persona principal";
                } else {
                    $mensaje .= ". No se encontraron asistencias nuevas para transferir (la persona principal ya tenía todas las asistencias)";
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => $mensaje,
                    'total_unificadas' => $totalUnificadas,
                    'total_asistencias' => $totalAsistencias
                ]);
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al unificar personas: ' . $e->getMessage()
                ]);
            }
            break;
            
        default:
            throw new Exception('Acción no válida');
}

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
header('Location: personas.php');
exit();
}

/**
 * Función para procesar y guardar imágenes subidas
 */
function procesarImagen($archivo) {
    $directorioDestino = dirname(__DIR__) . '/uploads/personas/';
    
    // Crear directorio si no existe
    if (!is_dir($directorioDestino)) {
        mkdir($directorioDestino, 0755, true);
    }
    
    // Validar tipo de archivo
    $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!in_array($archivo['type'], $tiposPermitidos)) {
        throw new Exception('Tipo de archivo no permitido. Solo se permiten JPG y PNG.');
    }
    
    // Validar tamaño (500KB máximo)
    $tamanioMaximo = 500 * 1024;
    if ($archivo['size'] > $tamanioMaximo) {
        throw new Exception('El archivo es demasiado grande. Tamaño máximo: 500KB');
    }
    
    // Generar nombre único
    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    $nombreArchivo = uniqid() . '_' . time() . '.' . $extension;
    $rutaCompleta = $directorioDestino . $nombreArchivo;
    
    // Mover archivo
    if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
        throw new Exception('Error al guardar la imagen');
    }
    
    // Retornar ruta relativa
    return 'uploads/personas/' . $nombreArchivo;
}

// Función para buscar personas
function buscarPersonas($busqueda) {
    global $pdo;
    
    $busqueda = trim($busqueda);
    if (strlen($busqueda) < 2) {
        return [];
    }
    
    $sql = "SELECT ID, NOMBRES, APELLIDO_PATERNO, APELLIDO_MATERNO, RUT, FAMILIA 
            FROM personas 
            WHERE NOMBRES LIKE ? 
               OR APELLIDO_PATERNO LIKE ? 
               OR APELLIDO_MATERNO LIKE ? 
               OR RUT LIKE ?
            ORDER BY APELLIDO_PATERNO, NOMBRES 
            LIMIT 20";
    
    $parametro = "%{$busqueda}%";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$parametro, $parametro, $parametro, $parametro]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>
