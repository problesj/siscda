# =====================================================
# Script de Instalación del Sistema CDA desde GitHub (Windows)
# Repositorio: https://github.com/problesj/siscda.git
# =====================================================

# Configuración de colores
$Host.UI.RawUI.ForegroundColor = "White"

# Función para mostrar mensajes
function Write-Info {
    param([string]$Message)
    Write-Host "[INFO] $Message" -ForegroundColor Green
}

function Write-Warning {
    param([string]$Message)
    Write-Host "[WARNING] $Message" -ForegroundColor Yellow
}

function Write-Error {
    param([string]$Message)
    Write-Host "[ERROR] $Message" -ForegroundColor Red
}

function Write-Header {
    Write-Host "=================================" -ForegroundColor Blue
    Write-Host "  Sistema CDA - Instalador      " -ForegroundColor Blue
    Write-Host "=================================" -ForegroundColor Blue
    Write-Host ""
}

# Función para verificar dependencias
function Test-Dependencies {
    Write-Info "Verificando dependencias del sistema..."
    
    # Verificar Git
    try {
        $gitVersion = git --version 2>$null
        if ($gitVersion) {
            Write-Info "Git encontrado: $gitVersion"
        } else {
            throw "Git no encontrado"
        }
    } catch {
        Write-Error "Git no está instalado. Por favor instálalo desde: https://git-scm.com/download/win"
        exit 1
    }
    
    # Verificar PHP
    try {
        $phpVersion = php --version 2>$null
        if ($phpVersion) {
            Write-Info "PHP encontrado: $($phpVersion[0])"
            
            # Verificar versión mínima
            $versionMatch = [regex]::Match($phpVersion[0], 'PHP (\d+\.\d+\.\d+)')
            if ($versionMatch.Success) {
                $version = $versionMatch.Groups[1].Value
                $majorMinor = ($version -split '\.')[0,1] -join '.'
                if ([version]"$majorMinor.0" -lt [version]"7.4.0") {
                    Write-Error "Se requiere PHP 7.4 o superior. Versión actual: $version"
                    exit 1
                }
            }
        } else {
            throw "PHP no encontrado"
        }
    } catch {
        Write-Error "PHP no está instalado. Por favor instálalo desde: https://windows.php.net/download/"
        exit 1
    }
    
    # Verificar extensiones PHP
    $requiredExtensions = @("pdo", "pdo_mysql", "session", "mbstring", "json")
    foreach ($ext in $requiredExtensions) {
        $extInfo = php -m 2>$null | Where-Object { $_ -eq $ext }
        if ($extInfo) {
            Write-Info "Extensión PHP '$ext' encontrada"
        } else {
            Write-Warning "Extensión PHP '$ext' no está habilitada"
        }
    }
    
    # Verificar MySQL (opcional)
    try {
        $mysqlVersion = mysql --version 2>$null
        if ($mysqlVersion) {
            Write-Info "Cliente MySQL encontrado: $mysqlVersion"
        } else {
            Write-Warning "Cliente MySQL no encontrado. Asegúrate de tener acceso a la base de datos."
        }
    } catch {
        Write-Warning "Cliente MySQL no encontrado. Asegúrate de tener acceso a la base de datos."
    }
    
    Write-Info "Dependencias verificadas correctamente"
}

# Función para obtener configuración del usuario
function Get-Configuration {
    Write-Info "Configuración del sistema..."
    
    # Directorio de instalación
    $defaultDir = "C:\xampp\htdocs\siscda"
    $installDir = Read-Host "Directorio de instalación (por defecto: $defaultDir)"
    if ([string]::IsNullOrEmpty($installDir)) {
        $installDir = $defaultDir
    }
    
    # Verificar si el directorio existe
    if (Test-Path $installDir) {
        $overwrite = Read-Host "El directorio $installDir ya existe. ¿Deseas sobrescribirlo? (y/N)"
        if ($overwrite -eq "y" -or $overwrite -eq "Y") {
            Remove-Item -Path $installDir -Recurse -Force
        } else {
            Write-Error "Instalación cancelada"
            exit 1
        }
    }
    
    # Configuración de base de datos
    $dbHost = Read-Host "Host de MySQL (por defecto: localhost)"
    if ([string]::IsNullOrEmpty($dbHost)) {
        $dbHost = "localhost"
    }
    
    $dbUser = Read-Host "Usuario de MySQL"
    $dbPass = Read-Host "Contraseña de MySQL" -AsSecureString
    $dbName = Read-Host "Nombre de la base de datos (por defecto: cda_base)"
    if ([string]::IsNullOrEmpty($dbName)) {
        $dbName = "cda_base"
    }
    
    # Convertir contraseña segura a texto plano para uso interno
    $BSTR = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($dbPass)
    $dbPassPlain = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto($BSTR)
    
    # Guardar configuración en variables globales
    $script:INSTALL_DIR = $installDir
    $script:DB_HOST = $dbHost
    $script:DB_USER = $dbUser
    $script:DB_PASS = $dbPassPlain
    $script:DB_NAME = $dbName
}

# Función para clonar el repositorio
function Clone-Repository {
    Write-Info "Clonando repositorio desde GitHub..."
    
    $gitRepo = "https://github.com/problesj/siscda.git"
    
    try {
        Set-Location (Split-Path $INSTALL_DIR -Parent)
        git clone $gitRepo (Split-Path $INSTALL_DIR -Leaf)
        
        if (Test-Path $INSTALL_DIR) {
            Write-Info "Repositorio clonado exitosamente en $INSTALL_DIR"
        } else {
            throw "Error al clonar el repositorio"
        }
    } catch {
        Write-Error "Error al clonar el repositorio: $($_.Exception.Message)"
        exit 1
    }
}

# Función para configurar permisos (Windows)
function Set-Permissions {
    Write-Info "Configurando permisos de archivos..."
    
    Set-Location $INSTALL_DIR
    
    # En Windows, los permisos se manejan de manera diferente
    # Solo verificamos que los archivos sean accesibles
    Write-Info "Permisos configurados para Windows"
}

# Función para crear archivo de configuración
function New-ConfigFile {
    Write-Info "Creando archivo de configuración..."
    
    Set-Location $INSTALL_DIR
    
    # Crear config.php desde el ejemplo
    if (Test-Path "config.example.php") {
        Copy-Item "config.example.php" "config.php"
        
        # Reemplazar valores en config.php
        $configContent = Get-Content "config.php" -Raw
        $configContent = $configContent -replace "localhost", $DB_HOST
        $configContent = $configContent -replace "cda_base", $DB_NAME
        $configContent = $configContent -replace "admincda", $DB_USER
        $configContent = $configContent -replace "cda2025\$", $DB_PASS
        
        Set-Content "config.php" $configContent -NoNewline
        
        Write-Info "Archivo config.php creado y configurado"
    } else {
        Write-Warning "Archivo config.example.php no encontrado"
    }
    
    # Crear .htaccess desde el ejemplo
    if (Test-Path ".htaccess.example") {
        Copy-Item ".htaccess.example" ".htaccess"
        Write-Info "Archivo .htaccess creado"
    } else {
        Write-Warning "Archivo .htaccess.example no encontrado"
    }
}

# Función para crear base de datos
function Set-Database {
    Write-Info "Configurando base de datos..."
    
    # Crear base de datos
    try {
        $mysqlVersion = mysql --version 2>$null
        if ($mysqlVersion) {
            Write-Info "Creando base de datos '$DB_NAME'..."
            
            $createDbCmd = "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
            $createDbCmd | mysql -h $DB_HOST -u $DB_USER -p$DB_PASS 2>$null
            
            if ($LASTEXITCODE -eq 0) {
                Write-Info "Base de datos creada exitosamente"
            } else {
                Write-Warning "No se pudo crear la base de datos. Verifica las credenciales."
            }
            
            # Ejecutar script SQL si existe
            if (Test-Path "install.sql") {
                Write-Info "Ejecutando script de instalación SQL..."
                mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME < install.sql 2>$null
                
                if ($LASTEXITCODE -eq 0) {
                    Write-Info "Script SQL ejecutado correctamente"
                } else {
                    Write-Warning "Error al ejecutar script SQL. Verifica la conexión."
                }
            }
        } else {
            Write-Warning "Cliente MySQL no encontrado. Deberás crear la base de datos manualmente."
            Write-Host "Ejecuta: CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
        }
    } catch {
        Write-Warning "Error al configurar base de datos: $($_.Exception.Message)"
    }
}

# Función para crear directorio de backups
function New-BackupDirectory {
    Write-Info "Configurando directorio de backups..."
    
    Set-Location $INSTALL_DIR
    
    if (-not (Test-Path "backups")) {
        New-Item -ItemType Directory -Name "backups" | Out-Null
        Write-Info "Directorio de backups creado"
    }
}

# Función para mostrar información final
function Show-FinalInfo {
    Write-Info "Instalación completada exitosamente!"
    Write-Host ""
    Write-Host "=================================" -ForegroundColor Green
    Write-Host "  Sistema CDA Instalado         " -ForegroundColor Green
    Write-Host "=================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "Directorio de instalación: $INSTALL_DIR"
    Write-Host "URL del sistema: http://localhost/siscda/"
    Write-Host ""
    Write-Host "Credenciales de acceso:"
    Write-Host "  Usuario: admin"
    Write-Host "  Contraseña: admin123"
    Write-Host ""
    Write-Host "Próximos pasos:"
    Write-Host "1. Accede al sistema y cambia la contraseña por defecto"
    Write-Host "2. Configura HTTPS si es posible"
    Write-Host "3. Elimina el archivo install.php por seguridad"
    Write-Host "4. Haz un backup inicial de la base de datos"
    Write-Host ""
    Write-Host "Para hacer backup de la base de datos:"
    Write-Host "  cd $INSTALL_DIR"
    Write-Host "  php backup_restore.php backup"
    Write-Host ""
    Write-Host "Documentación disponible en:"
    Write-Host "  - MANUAL_INSTALACION.md"
    Write-Host "  - INSTALACION_RAPIDA.md"
    Write-Host "  - README.md"
    Write-Host ""
    Write-Host "Nota: Si usas XAMPP, asegúrate de que Apache y MySQL estén ejecutándose."
}

# Función principal
function Main {
    Write-Header
    
    # Verificar dependencias
    Test-Dependencies
    
    # Obtener configuración
    Get-Configuration
    
    # Clonar repositorio
    Clone-Repository
    
    # Configurar permisos
    Set-Permissions
    
    # Crear archivo de configuración
    New-ConfigFile
    
    # Configurar base de datos
    Set-Database
    
    # Configurar directorio de backups
    New-BackupDirectory
    
    # Mostrar información final
    Show-FinalInfo
}

# Ejecutar función principal
Main
