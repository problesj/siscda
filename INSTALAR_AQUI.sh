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
    
    # Permitir al usuario personalizar la configuración
    echo
    print_status "Configuración de base de datos:"
    read -p "Nombre de la base de datos [$DB_NAME]: " input_db_name
    if [[ -n "$input_db_name" ]]; then
        DB_NAME="$input_db_name"
    fi
    
    read -p "Usuario de la base de datos [$DB_USER]: " input_db_user
    if [[ -n "$input_db_user" ]]; then
        DB_USER="$input_db_user"
    fi
    
    read -p "Contraseña de la base de datos [$DB_PASS]: " input_db_pass
    if [[ -n "$input_db_pass" ]]; then
        DB_PASS="$input_db_pass"
    fi
    
    # Verificar si el usuario quiere usar credenciales existentes
    echo
    read -p "¿Desea usar una base de datos existente? (y/N): " use_existing_db
    if [[ "$use_existing_db" =~ ^[Yy]$ ]]; then
        read -p "Host de la base de datos [localhost]: " input_db_host
        DB_HOST="${input_db_host:-localhost}"
        
        read -p "Puerto de la base de datos [3306]: " input_db_port
        DB_PORT="${input_db_port:-3306}"
        
        print_status "Verificando conexión a la base de datos existente..."
        if mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT 1;" 2>/dev/null; then
            print_success "Conexión exitosa a la base de datos existente"
            USE_EXISTING_DB=true
        else
            print_error "No se puede conectar a la base de datos existente"
            print_status "Continuando con creación de nueva base de datos..."
            USE_EXISTING_DB=false
        fi
    else
        DB_HOST="localhost"
        DB_PORT="3306"
        USE_EXISTING_DB=false
    fi
    
    print_success "Variables configuradas:"
    echo "  Directorio: $INSTALL_DIR"
    echo "  Host DB: $DB_HOST:$DB_PORT"
    echo "  Base de datos: $DB_NAME"
    echo "  Usuario DB: $DB_USER"
    echo "  Contraseña DB: $DB_PASS"
    echo "  Usar DB existente: $USE_EXISTING_DB"
}

# Función para crear archivo de configuración
create_config_file() {
    print_status "Creando archivo de configuración..."
    
    cat > config.php << EOF
<?php
// Configuración de la base de datos
define('DB_HOST', '$DB_HOST');
define('DB_PORT', '$DB_PORT');
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
        \$dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
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
    print_status "Configurando base de datos..."
    
    # Si ya existe una base de datos, no crear una nueva
    if [[ "$USE_EXISTING_DB" == true ]]; then
        print_success "Usando base de datos existente"
        return 0
    fi
    
    # Verificar si MySQL está ejecutándose
    if ! pgrep -x "mysqld" > /dev/null && ! pgrep -x "mysql" > /dev/null; then
        print_error "MySQL/MariaDB no está ejecutándose"
        print_status "Iniciando MySQL..."
        if command_exists systemctl; then
            sudo systemctl start mysql
        elif command_exists service; then
            sudo service mysql start
        else
            print_error "No se pudo iniciar MySQL. Iniciélo manualmente."
            exit 1
        fi
    fi
    
    # Intentar diferentes métodos para crear la base de datos
    local db_created=false
    
    # Método 1: Intentar con usuario root (si está disponible)
    if command_exists mysql; then
        print_status "Intentando crear base de datos con usuario root..."
        if mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null; then
            print_success "Base de datos creada con usuario root"
            db_created=true
            
            # Crear usuario y otorgar permisos
            if mysql -u root -p -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';" 2>/dev/null; then
                mysql -u root -p -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';" 2>/dev/null
                mysql -u root -p -e "FLUSH PRIVILEGES;" 2>/dev/null
                print_success "Usuario de base de datos creado y configurado"
            else
                print_warning "No se pudo crear usuario con root, pero la base de datos existe"
            fi
        else
            print_warning "No se pudo crear base de datos con usuario root"
        fi
    fi
    
    # Método 2: Si no se pudo crear, verificar si ya existe
    if [[ "$db_created" == false ]]; then
        print_status "Verificando si la base de datos ya existe..."
        
        # Intentar conectar a la base de datos existente
        if mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT 1;" 2>/dev/null; then
            print_success "Base de datos ya existe y es accesible"
            db_created=true
        else
            print_warning "Base de datos no existe o no es accesible"
        fi
    fi
    
    # Método 3: Instrucciones manuales si nada funcionó
    if [[ "$db_created" == false ]]; then
        print_warning "No se pudo crear la base de datos automáticamente"
        echo
        echo "Por favor, cree la base de datos manualmente:"
        echo "1. Acceda a MySQL como administrador:"
        echo "   mysql -u root -p"
        echo
        echo "2. Ejecute estos comandos:"
        echo "   CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
        echo "   CREATE USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
        echo "   GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';"
        echo "   FLUSH PRIVILEGES;"
        echo "   EXIT;"
        echo
        echo "3. Presione Enter cuando haya terminado..."
        read -r
        
        # Verificar si ahora se puede conectar
        if mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT 1;" 2>/dev/null; then
            print_success "Base de datos configurada correctamente"
            db_created=true
        else
            print_error "Aún no se puede conectar a la base de datos"
            print_status "Verifique las credenciales y permisos"
            exit 1
        fi
    fi
    
    if [[ "$db_created" == true ]]; then
        print_success "Base de datos configurada exitosamente"
    fi
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
    print_status "Verificando conexión a base de datos..."
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
        print_status "Verificando configuración..."
        
        # Mostrar información de depuración
        echo "Configuración actual:"
        echo "  Host: $DB_HOST"
        echo "  Puerto: $DB_PORT"
        echo "  Base de datos: $DB_NAME"
        echo "  Usuario: $DB_USER"
        echo
        
        # Intentar conexión directa con mysql
        if command_exists mysql; then
            print_status "Probando conexión directa con MySQL..."
            if mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT 1;" 2>/dev/null; then
                print_success "Conexión MySQL directa exitosa"
                print_warning "El problema puede estar en la configuración PHP"
            else
                print_error "Conexión MySQL directa falló"
                return 1
            fi
        fi
        
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
    echo "  Host de base de datos: $DB_HOST:$DB_PORT"
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
    
    # Mostrar información adicional si se usó base de datos existente
    if [[ "$USE_EXISTING_DB" == true ]]; then
        echo
        print_status "Nota: Se utilizó una base de datos existente"
        echo "  Asegúrese de que la base de datos tenga la estructura correcta"
        echo "  Si es necesario, ejecute: mysql -u $DB_USER -p$DB_PASS $DB_NAME < install.sql"
    fi
}

# Función para mostrar ayuda
show_help() {
    echo "=========================================="
    echo "    AYUDA DEL INSTALADOR SISCDA"
    echo "=========================================="
    echo
    echo "Uso: $0 [opciones]"
    echo
    echo "Opciones:"
    echo "  -h, --help     Mostrar esta ayuda"
    echo "  --skip-db       Saltar creación de base de datos"
    echo "  --skip-perms    Saltar configuración de permisos"
    echo
    echo "Problemas comunes:"
    echo "  1. Error de permisos MySQL:"
    echo "     - Asegúrese de tener acceso root a MySQL"
    echo "     - O cree la base de datos manualmente antes de ejecutar"
    echo
    echo "  2. Error de conexión:"
    echo "     - Verifique que MySQL esté ejecutándose"
    echo "     - Verifique las credenciales de la base de datos"
    echo
    echo "  3. Error de permisos del sistema:"
    echo "     - Ejecute con sudo si es necesario"
    echo "     - Verifique que el usuario tenga permisos de escritura"
    echo
    echo "Para más ayuda, consulte MANUAL_INSTALACION.md"
    echo
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
    
    # Crear base de datos (si no se saltó)
    if [[ "$SKIP_DB" == false ]]; then
        create_database
        
        # Importar base de datos
        import_database
    else
        print_status "Saltando creación de base de datos (--skip-db)"
    fi
    
    # Configurar permisos (si no se saltó)
    if [[ "$SKIP_PERMS" == false ]]; then
        setup_permissions
    else
        print_status "Saltando configuración de permisos (--skip-perms)"
    fi
    
    # Verificar instalación
    if verify_installation; then
        show_final_info
    else
        print_error "La instalación no se completó correctamente"
        echo
        print_status "Sugerencias para resolver problemas:"
        echo "  1. Verifique que MySQL esté ejecutándose"
        echo "  2. Verifique las credenciales de la base de datos"
        echo "  3. Asegúrese de tener permisos de administrador en MySQL"
        echo "  4. Consulte MANUAL_INSTALACION.md para más detalles"
        echo
        exit 1
    fi
}

# Manejo de argumentos de línea de comandos
SKIP_DB=false
SKIP_PERMS=false

while [[ $# -gt 0 ]]; do
    case $1 in
        -h|--help)
            show_help
            exit 0
            ;;
        --skip-db)
            SKIP_DB=true
            shift
            ;;
        --skip-perms)
            SKIP_PERMS=true
            shift
            ;;
        *)
            print_error "Opción desconocida: $1"
            show_help
            exit 1
            ;;
    esac
done

# Ejecutar función principal
main "$@"
