<?php
// Configuración de la base de datos para el servidor remoto
// Este archivo está configurado para el servidor donde tienes siscda_db

define('DB_HOST', 'localhost');
define('DB_NAME', 'siscda_db');
define('DB_USER', 'siscda_user');
define('DB_PASS', 'siscda_pwd_2025#');

// Configuración de la aplicación
define('APP_NAME', 'Sistema de Control de Asistencias');
define('APP_VERSION', '1.0.0');

// Configuración de sesión se maneja en session_config.php
// Las funciones se manejan en includes/auth_functions.php

// INSTRUCCIONES PARA USAR EN EL SERVIDOR REMOTO:
// 1. Copia este archivo como 'config.php' en la raíz del proyecto
// 2. O ejecuta: cp config_remote_server.php config.php
// 3. Verifica que la conexión funcione
// 4. Elimina este archivo después de configurar
?>
