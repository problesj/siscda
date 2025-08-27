#!/bin/bash

# Script para configurar la base de datos SISCDA en el servidor remoto

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

# Configuración de la base de datos
DB_NAME="siscda_db"
DB_USER="siscda_user"
DB_PASS="siscda_pwd_2025#"
DB_HOST="localhost"
DB_PORT="3306"

echo "=========================================="
echo "    CONFIGURADOR DE BASE DE DATOS SISCDA"
echo "=========================================="
echo

print_status "Configurando base de datos..."
echo "  Base de datos: $DB_NAME"
echo "  Usuario: $DB_USER"
echo "  Host: $DB_HOST:$DB_PORT"
echo

# Verificar si MySQL está ejecutándose
print_status "Verificando si MySQL está ejecutándose..."
if ! pgrep -x "mysqld" > /dev/null && ! pgrep -x "mysql" > /dev/null; then
    print_error "MySQL no está ejecutándose"
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

print_success "MySQL está ejecutándose"

# Crear archivo SQL temporal
print_status "Creando script SQL de configuración..."
cat > setup_db.sql << EOF
-- Script de configuración de base de datos SISCDA
-- Crear base de datos
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Crear usuario
CREATE USER IF NOT EXISTS '$DB_USER'@'$DB_HOST' IDENTIFIED BY '$DB_PASS';

-- Otorgar permisos
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'$DB_HOST';

-- Aplicar cambios
FLUSH PRIVILEGES;

-- Verificar permisos
SHOW GRANTS FOR '$DB_USER'@'$DB_HOST';
EOF

print_success "Script SQL creado: setup_db.sql"

# Ejecutar script SQL
print_status "Ejecutando script SQL..."
if mysql -u root -p < setup_db.sql; then
    print_success "Base de datos configurada exitosamente"
else
    print_error "Error al configurar la base de datos"
    print_status "Ejecute manualmente: mysql -u root -p < setup_db.sql"
    exit 1
fi

# Probar conexión
print_status "Probando conexión con el nuevo usuario..."
if mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT 1;" 2>/dev/null; then
    print_success "Conexión exitosa a la base de datos"
else
    print_error "No se puede conectar con el nuevo usuario"
    print_status "Verifique las credenciales y permisos"
    exit 1
fi

# Importar estructura de base de datos si existe
if [[ -f "install.sql" ]]; then
    print_status "Importando estructura de base de datos..."
    if mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < install.sql; then
        print_success "Estructura de base de datos importada"
    else
        print_warning "Error al importar estructura de base de datos"
    fi
else
    print_warning "Archivo install.sql no encontrado"
fi

# Limpiar archivo temporal
rm -f setup_db.sql

print_success "¡Configuración de base de datos completada!"
echo
echo "Resumen:"
echo "  Base de datos: $DB_NAME"
echo "  Usuario: $DB_USER"
echo "  Contraseña: $DB_PASS"
echo "  Host: $DB_HOST:$DB_PORT"
echo
echo "Ahora puede probar la aplicación en el navegador"

