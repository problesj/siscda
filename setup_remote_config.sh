#!/bin/bash

# Script para configurar la base de datos en el servidor remoto
# Uso: ./setup_remote_config.sh

echo "🔧 Configurando base de datos para servidor remoto..."

# Hacer backup del config.php actual si existe
if [ -f "config.php" ]; then
    backup_name="config_backup_$(date +%Y-%m-%d_%H-%M-%S).php"
    cp config.php "$backup_name"
    echo "✅ Backup creado: $backup_name"
fi

# Crear config.php con configuración remota
cat > config.php << 'EOF'
<?php
// Configuración de la base de datos para el servidor remoto
define('DB_HOST', 'localhost');
define('DB_NAME', 'siscda_db');
define('DB_USER', 'siscda_user');
define('DB_PASS', 'siscda_pwd_2025#');

// Configuración de la aplicación
define('APP_NAME', 'Sistema de Control de Asistencias');
define('APP_VERSION', '1.0.0');

// Configuración de sesión se maneja en session_config.php
// Las funciones se manejan en includes/auth_functions.php
?>
EOF

echo "✅ Archivo config.php creado con configuración remota"
echo ""
echo "📋 Configuración aplicada:"
echo "   Host: localhost"
echo "   Base de datos: siscda_db"
echo "   Usuario: siscda_user"
echo "   Contraseña: siscda_pwd_2025#"
echo ""
echo "🧪 Para probar la conexión, ejecuta:"
echo "   php -r \"include 'config.php'; echo 'Configuración cargada correctamente\n';\""
echo ""
echo "⚠️  IMPORTANTE: Después de verificar que funciona, elimina este script:"
echo "   rm setup_remote_config.sh"
echo ""
echo "🎯 La aplicación debería funcionar ahora con la base de datos remota"
