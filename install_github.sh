#!/bin/bash

# =====================================================
# Script de Instalación del Sistema CDA desde GitHub
# Repositorio: https://github.com/problesj/siscda.git
# =====================================================

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Función para mostrar mensajes
print_message() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_header() {
    echo -e "${BLUE}================================${NC}"
    echo -e "${BLUE}  Sistema CDA - Instalador     ${NC}"
    echo -e "${BLUE}================================${NC}"
}

# Función para verificar dependencias
check_dependencies() {
    print_message "Verificando dependencias del sistema..."
    
    # Verificar Git
    if ! command -v git &> /dev/null; then
        print_error "Git no está instalado. Por favor instálalo primero:"
        echo "  Ubuntu/Debian: sudo apt-get install git"
        echo "  CentOS/RHEL: sudo yum install git"
        echo "  macOS: brew install git"
        exit 1
    fi
    
    # Verificar PHP
    if ! command -v php &> /dev/null; then
        print_error "PHP no está instalado. Por favor instálalo primero:"
        echo "  Ubuntu/Debian: sudo apt-get install php php-mysql php-mbstring"
        echo "  CentOS/RHEL: sudo yum install php php-mysql php-mbstring"
        echo "  macOS: brew install php"
        exit 1
    fi
    
    # Verificar versión de PHP
    PHP_VERSION=$(php -r "echo PHP_VERSION;" 2>/dev/null)
    REQUIRED_VERSION="7.4.0"
    
    if [ "$(printf '%s\n' "$REQUIRED_VERSION" "$PHP_VERSION" | sort -V | head -n1)" != "$REQUIRED_VERSION" ]; then
        print_error "Se requiere PHP 7.4 o superior. Versión actual: $PHP_VERSION"
        exit 1
    fi
    
    # Verificar extensiones PHP
    REQUIRED_EXTENSIONS=("pdo" "pdo_mysql" "session" "mbstring" "json")
    for ext in "${REQUIRED_EXTENSIONS[@]}"; do
        if ! php -m | grep -q "^$ext$"; then
            print_warning "Extensión PHP '$ext' no está habilitada"
        fi
    done
    
    # Verificar MySQL/MariaDB
    if ! command -v mysql &> /dev/null; then
        print_warning "Cliente MySQL no encontrado. Asegúrate de tener acceso a la base de datos."
    fi
    
    print_message "Dependencias verificadas correctamente"
}

# Función para obtener configuración del usuario
get_configuration() {
    print_message "Configuración del sistema..."
    
    # Directorio de instalación
    read -p "Directorio de instalación (por defecto: /var/www/html/siscda): " INSTALL_DIR
    INSTALL_DIR=${INSTALL_DIR:-/var/www/html/siscda}
    
    # Verificar si el directorio existe
    if [ -d "$INSTALL_DIR" ]; then
        read -p "El directorio $INSTALL_DIR ya existe. ¿Deseas sobrescribirlo? (y/N): " OVERWRITE
        if [[ $OVERWRITE =~ ^[Yy]$ ]]; then
            rm -rf "$INSTALL_DIR"
        else
            print_error "Instalación cancelada"
            exit 1
        fi
    fi
    
    # Configuración de base de datos
    read -p "Host de MySQL (por defecto: localhost): " DB_HOST
    DB_HOST=${DB_HOST:-localhost}
    
    read -p "Usuario de MySQL: " DB_USER
    
    read -s -p "Contraseña de MySQL: " DB_PASS
    echo
    
    read -p "Nombre de la base de datos (por defecto: cda_base): " DB_NAME
    DB_NAME=${DB_NAME:-cda_base}
    
    # Configuración del servidor web
    read -p "Usuario del servidor web (por defecto: www-data): " WEB_USER
    WEB_USER=${WEB_USER:-www-data}
    
    read -p "Grupo del servidor web (por defecto: www-data): " WEB_GROUP
    WEB_GROUP=${WEB_GROUP:-www-data}
}

# Función para clonar el repositorio
clone_repository() {
    print_message "Clonando repositorio desde GitHub..."
    
    GIT_REPO="https://github.com/problesj/siscda.git"
    
    if git clone "$GIT_REPO" "$INSTALL_DIR"; then
        print_message "Repositorio clonado exitosamente en $INSTALL_DIR"
    else
        print_error "Error al clonar el repositorio"
        exit 1
    fi
}

# Función para configurar permisos
setup_permissions() {
    print_message "Configurando permisos de archivos..."
    
    cd "$INSTALL_DIR"
    
    # Cambiar propietario
    if command -v sudo &> /dev/null; then
        sudo chown -R "$WEB_USER:$WEB_GROUP" .
    else
        chown -R "$WEB_USER:$WEB_GROUP" .
    fi
    
    # Configurar permisos
    find . -type f -exec chmod 644 {} \;
    find . -type d -exec chmod 755 {} \;
    
    # Permisos especiales
    chmod 755 .
    chmod 755 includes/
    chmod 755 modules/
    chmod 755 assets/
    
    # Hacer ejecutable el script de backup
    chmod +x backup_restore.php
    
    print_message "Permisos configurados correctamente"
}

# Función para crear archivo de configuración
create_config_file() {
    print_message "Creando archivo de configuración..."
    
    cd "$INSTALL_DIR"
    
    # Crear config.php desde el ejemplo
    if [ -f "config.example.php" ]; then
        cp config.example.php config.php
        
        # Reemplazar valores en config.php
        sed -i "s/localhost/$DB_HOST/g" config.php
        sed -i "s/cda_base/$DB_NAME/g" config.php
        sed -i "s/admincda/$DB_USER/g" config.php
        sed -i "s/cda2025\$/$DB_PASS/g" config.php
        
        print_message "Archivo config.php creado y configurado"
    else
        print_warning "Archivo config.example.php no encontrado"
    fi
    
    # Crear .htaccess desde el ejemplo
    if [ -f ".htaccess.example" ]; then
        cp .htaccess.example .htaccess
        print_message "Archivo .htaccess creado"
    else
        print_warning "Archivo .htaccess.example no encontrado"
    fi
}

# Función para crear base de datos
setup_database() {
    print_message "Configurando base de datos..."
    
    # Crear base de datos
    if command -v mysql &> /dev/null; then
        print_message "Creando base de datos '$DB_NAME'..."
        
        if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null; then
            print_message "Base de datos creada exitosamente"
        else
            print_warning "No se pudo crear la base de datos. Verifica las credenciales."
        fi
        
        # Ejecutar script SQL si existe
        if [ -f "install.sql" ]; then
            print_message "Ejecutando script de instalación SQL..."
            if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < install.sql 2>/dev/null; then
                print_message "Script SQL ejecutado correctamente"
            else
                print_warning "Error al ejecutar script SQL. Verifica la conexión."
            fi
        fi
    else
        print_warning "Cliente MySQL no encontrado. Deberás crear la base de datos manualmente."
        echo "Ejecuta: CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    fi
}

# Función para crear directorio de backups
setup_backup_directory() {
    print_message "Configurando directorio de backups..."
    
    cd "$INSTALL_DIR"
    
    if [ ! -d "backups" ]; then
        mkdir -p backups
        chmod 755 backups
        chown "$WEB_USER:$WEB_GROUP" backups
        print_message "Directorio de backups creado"
    fi
}

# Función para mostrar información final
show_final_info() {
    print_message "Instalación completada exitosamente!"
    echo
    echo -e "${GREEN}================================${NC}"
    echo -e "${GREEN}  Sistema CDA Instalado        ${NC}"
    echo -e "${GREEN}================================${NC}"
    echo
    echo "Directorio de instalación: $INSTALL_DIR"
    echo "URL del sistema: http://$(hostname -I | awk '{print $1}')/siscda/"
    echo
    echo "Credenciales de acceso:"
    echo "  Usuario: admin"
    echo "  Contraseña: admin123"
    echo
    echo "Próximos pasos:"
    echo "1. Accede al sistema y cambia la contraseña por defecto"
    echo "2. Configura HTTPS si es posible"
    echo "3. Elimina el archivo install.php por seguridad"
    echo "4. Haz un backup inicial de la base de datos"
    echo
    echo "Para hacer backup de la base de datos:"
    echo "  cd $INSTALL_DIR"
    echo "  php backup_restore.php backup"
    echo
    echo "Documentación disponible en:"
    echo "  - MANUAL_INSTALACION.md"
    echo "  - INSTALACION_RAPIDA.md"
    echo "  - README.md"
}

# Función principal
main() {
    print_header
    
    # Verificar si se ejecuta como root
    if [[ $EUID -eq 0 ]]; then
        print_warning "Este script no debe ejecutarse como root"
        print_warning "Ejecuta como usuario normal y usa sudo cuando sea necesario"
        exit 1
    fi
    
    # Verificar dependencias
    check_dependencies
    
    # Obtener configuración
    get_configuration
    
    # Clonar repositorio
    clone_repository
    
    # Configurar permisos
    setup_permissions
    
    # Crear archivo de configuración
    create_config_file
    
    # Configurar base de datos
    setup_database
    
    # Configurar directorio de backups
    setup_backup_directory
    
    # Mostrar información final
    show_final_info
}

# Ejecutar función principal
main "$@"
