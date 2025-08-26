# Script de instalación para SISCDA en Windows
# Descargar desde: https://raw.githubusercontent.com/problesj/siscda/main/install.ps1

param(
    [string]$InstallDir = "C:\xampp\htdocs\siscda",
    [string]$DBName = "siscda_db",
    [string]$DBUser = "siscda_user",
    [string]$DBPass = "siscda_password_$(Get-Date -Format 'yyyyMMddHHmmss')"
)

# Función para imprimir mensajes
function Write-Status {
    param([string]$Message)
    Write-Host "[INFO] $Message" -ForegroundColor Blue
}

function Write-Success {
    param([string]$Message)
    Write-Host "[SUCCESS] $Message" -ForegroundColor Green
}

function Write-Warning {
    param([string]$Message)
    Write-Host "[WARNING] $Message" -ForegroundColor Yellow
}

function Write-Error {
    param([string]$Message)
    Write-Host "[ERROR] $Message" -ForegroundColor Red
}

# Función para verificar si Git está instalado
function Test-GitInstalled {
    try {
        git --version | Out-Null
        return $true
    } catch {
        return $false
    }
}

# Función para verificar si XAMPP está instalado
function Test-XAMPPInstalled {
    return Test-Path "C:\xampp"
}

# Función principal
function Main {
    Write-Host "==========================================" -ForegroundColor Cyan
    Write-Host "    INSTALADOR DE SISCDA PARA WINDOWS" -ForegroundColor Cyan
    Write-Host "==========================================" -ForegroundColor Cyan
    Write-Host ""
    
    # Verificar Git
    if (-not (Test-GitInstalled)) {
        Write-Error "Git no está instalado. Instálelo desde https://git-scm.com/"
        exit 1
    }
    
    # Verificar XAMPP
    if (-not (Test-XAMPPInstalled)) {
        Write-Warning "XAMPP no está instalado en C:\xampp"
        Write-Status "Instalando XAMPP..."
        # Aquí se podría agregar la descarga automática de XAMPP
        Write-Error "Instale XAMPP manualmente desde https://www.apachefriends.org/"
        exit 1
    }
    
    Write-Status "Configurando variables de instalación..."
    Write-Host "  Directorio: $InstallDir"
    Write-Host "  Base de datos: $DBName"
    Write-Host "  Usuario DB: $DBUser"
    Write-Host "  Contraseña DB: $DBPass"
    Write-Host ""
    
    # Crear directorio de instalación
    Write-Status "Creando directorio de instalación..."
    if (Test-Path $InstallDir) {
        Write-Warning "El directorio $InstallDir ya existe"
        $response = Read-Host "¿Desea continuar? (y/N)"
        if ($response -ne "y" -and $response -ne "Y") {
            Write-Status "Instalación cancelada"
            exit 0
        }
    } else {
        New-Item -ItemType Directory -Path $InstallDir -Force | Out-Null
        Write-Success "Directorio creado: $InstallDir"
    }
    
    # Cambiar al directorio de instalación
    Set-Location $InstallDir
    
    # Clonar repositorio
    Write-Status "Clonando repositorio SISCDA..."
    if (Test-Path ".git") {
        Write-Warning "El directorio ya es un repositorio Git"
        Write-Status "Actualizando desde el repositorio remoto..."
        git pull origin main
    } else {
        git clone https://github.com/problesj/siscda.git .
        Write-Success "Repositorio clonado exitosamente"
    }
    
    # Crear archivo de configuración
    Write-Status "Creando archivo de configuración..."
    $configContent = @"
<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', '$DBName');
define('DB_USER', '$DBUser');
define('DB_PASS', '$DBPass');
define('DB_CHARSET', 'utf8mb4');

// Configuración de la aplicación
define('APP_NAME', 'SISCDA');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/siscda');
define('TIMEZONE', 'America/Santiago');

// Función de conexión a la base de datos
function conectarDB() {
    try {
        `$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        `$pdo = new PDO(`$dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return `$pdo;
    } catch (PDOException `$e) {
        die("Error de conexión: " . `$e->getMessage());
    }
}

// Configurar zona horaria
date_default_timezone_set(TIMEZONE);
"@
    
    $configContent | Out-File -FilePath "config.php" -Encoding UTF8
    Write-Success "Archivo de configuración creado"
    
    # Crear archivo .htaccess
    Write-Status "Creando archivo .htaccess..."
    $htaccessContent = @"
# Configuración de seguridad
<Files "config.php">
    Order allow,deny
    Deny from all
</Files>

<Files "*.sql">
    Order allow,deny
    Deny from all
</Files>

# Deshabilitar listado de directorios
Options -Indexes
"@
    
    $htaccessContent | Out-File -FilePath ".htaccess" -Encoding UTF8
    Write-Success "Archivo .htaccess creado"
    
    # Crear directorios necesarios
    Write-Status "Creando directorios necesarios..."
    New-Item -ItemType Directory -Path "assets\uploads" -Force | Out-Null
    New-Item -ItemType Directory -Path "assets\images" -Force | Out-Null
    New-Item -ItemType Directory -Path "logs" -Force | Out-Null
    New-Item -ItemType Directory -Path "temp" -Force | Out-Null
    Write-Success "Directorios creados"
    
    # Mostrar información final
    Write-Success "¡Instalación completada exitosamente!"
    Write-Host ""
    Write-Host "Información de la instalación:"
    Write-Host "  URL de la aplicación: http://localhost/siscda"
    Write-Host "  Directorio de instalación: $InstallDir"
    Write-Host "  Base de datos: $DBName"
    Write-Host "  Usuario de base de datos: $DBUser"
    Write-Host "  Contraseña de base de datos: $DBPass"
    Write-Host ""
    Write-Host "Próximos pasos:"
    Write-Host "  1. Iniciar XAMPP (Apache y MySQL)"
    Write-Host "  2. Crear la base de datos en phpMyAdmin"
    Write-Host "  3. Acceder a la aplicación en el navegador"
    Write-Host ""
    Write-Warning "IMPORTANTE: Guarde la contraseña de la base de datos en un lugar seguro"
}

# Ejecutar función principal
Main
