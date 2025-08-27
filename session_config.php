<?php
// Configuración de sesión - debe ejecutarse ANTES de session_start()

// Configuración de cookies de sesión
ini_set('session.cookie_httponly', 1);           // Prevenir acceso desde JavaScript
ini_set('session.use_only_cookies', 1);          // Solo usar cookies para sesiones
ini_set('session.cookie_secure', 0);             // Cambiar a 1 si usas HTTPS
ini_set('session.cookie_samesite', 'Lax');      // Protección CSRF
ini_set('session.cookie_lifetime', 0);           // Sesión de navegador (se cierra al cerrar)

// Configuración de tiempo de sesión
ini_set('session.gc_maxlifetime', 7200);        // 2 horas en segundos
ini_set('session.gc_probability', 1);            // Probabilidad de limpieza
ini_set('session.gc_divisor', 100);             // Divisor para la probabilidad

// Configuración de regeneración de ID de sesión
ini_set('session.use_strict_mode', 1);          // Modo estricto de sesión
ini_set('session.use_trans_sid', 0);            // No usar trans_sid

// Configuración de hash de sesión
ini_set('session.hash_function', 'sha256');      // Función hash más segura
ini_set('session.hash_bits_per_character', 5);  // Bits por carácter

// Configuración de cache
ini_set('session.cache_limiter', 'nocache');     // No cachear sesiones
ini_set('session.cache_expire', 0);             // Expiración inmediata del cache
?>
