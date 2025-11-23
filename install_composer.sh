#!/bin/bash

# Script para instalar Composer y PhpSpreadsheet en el servidor de producción
# Ejecutar como: bash install_composer.sh

echo "=== INSTALACIÓN DE COMPOSER Y PHPSPREADSHEET ==="

# Verificar si composer.json existe
if [ ! -f "composer.json" ]; then
    echo "ERROR: composer.json no encontrado"
    exit 1
fi

# Verificar si composer está instalado
if ! command -v composer &> /dev/null; then
    echo "Composer no está instalado. Instalando..."
    
    # Descargar e instalar Composer
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    sudo chmod +x /usr/local/bin/composer
    
    echo "Composer instalado exitosamente"
else
    echo "Composer ya está instalado"
fi

# Instalar dependencias
echo "Instalando dependencias de Composer..."
composer install --no-dev --optimize-autoloader

# Verificar instalación
if [ -f "vendor/autoload.php" ]; then
    echo "✅ Dependencias instaladas correctamente"
    echo "✅ PhpSpreadsheet disponible"
else
    echo "❌ Error en la instalación de dependencias"
    exit 1
fi

echo "=== INSTALACIÓN COMPLETADA ==="
