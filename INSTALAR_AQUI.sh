#!/bin/bash

# Script de instalación para SISCDA
# Este script se ejecuta DESPUÉS de clonar el repositorio

set -e

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Función para imprimir mensajes
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Función para verificar si un comando existe
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Función para verificar si estamos en un sistema compatible
check_system() {
    print_status "Verificando sistema operativo..."
    
    if [[ "$OSTYPE" == "linux-gnu"* ]]; then
        print_success "Sistema Linux detectado"
        OS="linux"
    elif [[ "$OSTYPE" == "darwin"* ]]; then
        print_success "Sistema macOS detectado"
        OS="macos"
    else
        print_error "Sistema operativo no soportado: $OSTYPE"
        exit 1
    fi
}

# Función para verificar dependencias
check_dependencies() {
    print_status "Verificando dependencias..."
    
    local missing_deps=()
    
    if ! command_exists php; then
        missing_deps+=("php")
    fi
    
    if ! command_exists mysql; then
        missing_deps+=("mysql")
    fi
    
    if [[ ${#missing_deps[@]} -gt 0 ]]; then
        print_error "Dependencias faltantes: ${missing_deps[*]}"
        print_status "Instalando dependencias..."
        
        if [[ "$OS" == "linux" ]]; then
            if command_exists apt-get; then
                sudo apt-get update
                sudo apt-get install -y "${missing_deps[@]}"
            elif command_exists yum; then
                sudo yum install -y "${missing_deps[@]}"
            elif command_exists dnf; then
                sudo dnf install -y "${missing_deps[@]}"
            else
                print_error "No se pudo instalar las dependencias. Instálelas manualmente."
                exit 1
            fi
        elif [[ "$OS" == "macos" ]]; then
            if command_exists brew; then
                brew install "${missing_deps[@]}"
            else
                print_error "Homebrew no está instalado. Instálelo primero."
                exit 1
            fi
        fi
    else
        print_success "Todas las dependencias están instaladas"
    fi
}

# Función para configurar variables
setup_variables() {
    print_status "Configurando variables de instalación..."
    
    # Obtener el directorio actual
    INSTALL_DIR=$(pwd)
    
    # Base de datos
    DB_NAME="siscda_db"
    DB_USER="siscda_user"
    DB_PASS="siscda_password_$(date +%s)"
    
    # Usuario del sistema
    SYSTEM_USER="www-data"
    
    print_success "Variables configuradas:"
    echo "  Directorio: $INSTALL_DIR"
    echo "  Base de datos: $DB_NAME"
    echo "  Usuario DB: $DB_USER"
    echo "  Contraseña DB: $DB_PASS"
}

# Función para crear archivo de configuración
create_config_file() {
    print_status "Creando archivo de configuración..."
    
    cat > config.php << EOF
<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', '$DB_NAME');
define('DB_USER', '$DB_USER');
define('DB_PASS', '$DB_PASS');
define('DB_CHARSET', 'utf8mb4');

// Configuración de la aplicación
define('APP_NAME', 'SISCDA');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/siscda');
define('TIMEZONE', 'America/Santiago');

// Configuración de seguridad
define('SECRET_KEY', '$(openssl rand -hex 32)');
define('SESSION_TIMEOUT', 3600);

// Función de conexión a la base de datos
function conectarDB() {
    try {
        \$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        \$pdo = new PDO(\$dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return \$pdo;
    } catch (PDOException \$e) {
        die("Error de conexión: " . \$e->getMessage());
    }
}

// Configurar zona horaria
date_default_timezone_set(TIMEZONE);

// Configurar manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// Crear directorio de logs si no existe
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}
EOF

    print_success "Archivo de configuración creado"
}

# Función para crear archivo .htaccess
create_htaccess() {
    print_status "Creando archivo .htaccess..."
    
    cat > .htaccess << EOF
# Configuración de seguridad
<Files "config.php">
    Order allow,deny
    Deny from all
</Files>

<Files "*.sql">
    Order allow,deny
    Deny from all
</Files>

<Files "*.log">
    Order allow,deny
    Deny from all
</Files>

# Protección contra XSS
<IfModule mod_headers.c>
    Header set X-XSS-Protection "1; mode=block"
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
</IfModule>

# Configuración de caché
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/ico "access plus 1 month"
    ExpiresByType image/icon "access plus 1 month"
    ExpiresByType text/plain "access plus 1 month"
</IfModule>

# Compresión GZIP
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Deshabilitar listado de directorios
Options -Indexes

# Manejo de errores
ErrorDocument 404 /siscda/404.php
ErrorDocument 500 /siscda/500.php
EOF

    print_success "Archivo .htaccess creado"
}

# Función para crear directorios necesarios
create_directories() {
    print_status "Creando directorios necesarios..."
    
    mkdir -p assets/uploads
    mkdir -p assets/images
    mkdir -p logs
    mkdir -p temp
    
    print_success "Directorios creados"
}

# Función para crear base de datos
create_database() {
    print_status "Creando base de datos..."
    
    # Crear base de datos
    mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    
    # Crear usuario
    mysql -u root -p -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
    
    # Otorgar permisos
    mysql -u root -p -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';"
    mysql -u root -p -e "FLUSH PRIVILEGES;"
    
    print_success "Base de datos creada exitosamente"
}

# Función para configurar permisos
setup_permissions() {
    print_status "Configurando permisos..."
    
    # Cambiar propietario
    sudo chown -R "$SYSTEM_USER:$SYSTEM_USER" "$INSTALL_DIR"
    
    # Configurar permisos
    sudo chmod -R 755 "$INSTALL_DIR"
    sudo chmod -R 775 "$INSTALL_DIR/assets/uploads"
    sudo chmod -R 775 "$INSTALL_DIR/logs"
    
    print_success "Permisos configurados correctamente"
}

# Función para importar base de datos
import_database() {
    print_status "Importando estructura de base de datos..."
    
    if [[ -f "install.sql" ]]; then
        mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < install.sql
        print_success "Estructura de base de datos importada"
    else
        print_warning "Archivo install.sql no encontrado"
    fi
}

# Función para verificar instalación
verify_installation() {
    print_status "Verificando instalación..."
    
    # Verificar archivos principales
    local required_files=("index.php" "config.php" ".htaccess")
    for file in "${required_files[@]}"; do
        if [[ ! -f "$file" ]]; then
            print_error "Archivo requerido no encontrado: $file"
            return 1
        fi
    done
    
    # Verificar conexión a base de datos
    if php -r "
        require_once 'config.php';
        try {
            \$pdo = conectarDB();
            echo 'Conexión exitosa a la base de datos\n';
        } catch (Exception \$e) {
            echo 'Error de conexión: ' . \$e->getMessage() . '\n';
            exit(1);
        }
    "; then
        print_success "Conexión a base de datos verificada"
    else
        print_error "Error al verificar conexión a base de datos"
        return 1
    fi
    
    print_success "Instalación verificada correctamente"
    return 0
}

# Función para mostrar información final
show_final_info() {
    print_success "¡Instalación completada exitosamente!"
    echo
    echo "Información de la instalación:"
    echo "  URL de la aplicación: http://localhost/siscda"
    echo "  Directorio de instalación: $INSTALL_DIR"
    echo "  Base de datos: $DB_NAME"
    echo "  Usuario de base de datos: $DB_USER"
    echo "  Contraseña de base de datos: $DB_PASS"
    echo
    echo "Archivos de configuración creados:"
    echo "  - config.php (configuración de la aplicación)"
    echo "  - .htaccess (configuración del servidor web)"
    echo
    echo "Próximos pasos:"
    echo "  1. Configurar el servidor web (Apache/Nginx)"
    echo "  2. Acceder a la aplicación en el navegador"
    echo "  3. Cambiar la contraseña de administrador por defecto"
    echo
    echo "Para obtener ayuda, consulte:"
    echo "  - MANUAL_INSTALACION.md"
    echo "  - README.md"
    echo
    print_warning "IMPORTANTE: Guarde la contraseña de la base de datos en un lugar seguro"
}

# Función principal
main() {
    echo "=========================================="
    echo "    INSTALADOR DE SISCDA (LOCAL)"
    echo "=========================================="
    echo
    
    # Verificar si se ejecuta como root
    if [[ $EUID -eq 0 ]]; then
        print_error "No ejecute este script como root"
        exit 1
    fi
    
    # Verificar sistema
    check_system
    
    # Verificar dependencias
    check_dependencies
    
    # Configurar variables
    setup_variables
    
    # Crear directorios necesarios
    create_directories
    
    # Crear archivo de configuración
    create_config_file
    
    # Crear archivo .htaccess
    create_htaccess
    
    # Crear base de datos
    create_database
    
    # Importar base de datos
    import_database
    
    # Configurar permisos
    setup_permissions
    
    # Verificar instalación
    if verify_installation; then
        show_final_info
    else
        print_error "La instalación no se completó correctamente"
        exit 1
    fi
}

# Ejecutar función principal
main "$@"
