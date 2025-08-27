# 🚀 Sistema CDA - Instalación desde GitHub

[![GitHub](https://img.shields.io/badge/GitHub-Repository-blue?style=for-the-badge&logo=github)](https://github.com/problesj/siscda)
[![PHP](https://img.shields.io/badge/PHP-7.4+-purple?style=for-the-badge&logo=php)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-blue?style=for-the-badge&logo=mysql)](https://mysql.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

## 📋 Descripción

El **Sistema de Control de Asistencias (CDA)** es una aplicación web desarrollada en PHP que permite gestionar asistencias a cultos y eventos de una iglesia u organización religiosa.

## 🌟 Características Principales

- 👥 **Gestión de Personas** - Registro y administración de miembros
- 📅 **Control de Cultos** - Programación y registro de eventos
- ✅ **Sistema de Asistencias** - Marcado y seguimiento de presencia
- 📊 **Reportes Avanzados** - Estadísticas y análisis detallados
- 👨‍👩‍👧‍👦 **Grupos Familiares** - Organización por familias
- 👤 **Gestión de Usuarios** - Sistema de autenticación y roles
- 🔒 **Seguridad** - Protección CSRF, sanitización de datos
- 📱 **Responsive** - Interfaz adaptada a todos los dispositivos

## 🚀 Instalación Rápida

### Opción 1: Instalación Automática (Recomendada)

#### Linux/macOS
```bash
# Descargar script de instalación
wget https://raw.githubusercontent.com/problesj/siscda/main/install_github.sh

# Hacer ejecutable
chmod +x install_github.sh

# Ejecutar instalador
./install_github.sh
```

#### Windows
```powershell
# Descargar script de instalación
Invoke-WebRequest -Uri "https://raw.githubusercontent.com/problesj/siscda/main/install_github.ps1" -OutFile "install_github.ps1"

# Ejecutar instalador (como administrador)
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
.\install_github.ps1
```

### Opción 2: Instalación Manual

```bash
# Clonar repositorio
git clone https://github.com/problesj/siscda.git
cd siscda

# Crear archivo de configuración
cp config.example.php config.php
# Editar config.php con tus credenciales

# Crear .htaccess
cp .htaccess.example .htaccess

# Ejecutar script SQL
mysql -u usuario -p cda_base < install.sql
```

### Opción 3: Instalación con Scripts Generales

#### Linux/macOS
```bash
# Descargar script de instalación
wget https://raw.githubusercontent.com/problesj/siscda/main/install.sh
chmod +x install.sh
sudo ./install.sh
```

#### Windows
```powershell
# Descargar script de instalación
Invoke-WebRequest -Uri "https://raw.githubusercontent.com/problesj/siscda/main/install.ps1" -OutFile "install.ps1"
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
.\install.ps1
```

## 📋 Requisitos del Sistema

### Servidor Web
- **Apache 2.4+** o **Nginx 1.18+**
- **mod_rewrite** habilitado (Apache)
- **mod_headers** habilitado (Apache)

### PHP
- **Versión mínima**: PHP 7.4
- **Versión recomendada**: PHP 8.0+
- **Extensiones requeridas**:
  - `pdo`
  - `pdo_mysql`
  - `session`
  - `mbstring`
  - `json`

### Base de Datos
- **MySQL 5.7+** o **MariaDB 10.2+**
- **Usuario con permisos** para crear bases de datos
- **Charset**: `utf8mb4`
- **Collation**: `utf8mb4_unicode_ci`

### Cliente
- **Git** para clonar el repositorio
- **Navegador web** moderno

## 🔧 Configuración

### 1. Archivo de Configuración

El archivo `config.php` contiene la configuración principal:

```php
<?php
// Configuración de base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'cda_base');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');
define('DB_CHARSET', 'utf8mb4');

// Configuración de la aplicación
define('APP_NAME', 'Sistema CDA');
define('APP_VERSION', '1.1.0');
define('TIMEZONE', 'America/Santiago');
?>
```

### 2. Configuración del Servidor Web

#### Apache (.htaccess)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]

# Seguridad
<Files "config.php">
    Order allow,deny
    Deny from all
</Files>
```

#### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
    fastcgi_index index.php;
    include fastcgi_params;
}
```

## 📊 Estructura del Proyecto

```
siscda/
├── assets/           # Archivos estáticos (CSS, JS, imágenes)
├── includes/         # Archivos de inclusión PHP
├── modules/          # Módulos de la aplicación
├── config.php       # Configuración principal
├── index.php        # Punto de entrada
├── auth.php         # Autenticación de usuarios
├── dashboard.php    # Panel principal
├── install.sql      # Estructura de la base de datos
├── setup_database.sh # Script para configurar BD en servidor remoto
├── install_github.sh # Instalador automático para Linux/macOS
├── install_github.ps1 # Instalador automático para Windows
├── install.sh       # Instalador general para Linux/macOS
├── install.ps1      # Instalador general para Windows
├── backup_restore.php # Script de backup y restauración
├── .htaccess.example # Configuración de Apache de ejemplo
└── config.example.php # Archivo de configuración de ejemplo
```

## 🎯 Funcionalidades Principales

- 👥 **Gestión de Personas** - Registro y administración de miembros
- 📅 **Registro de Cultos** - Programación y registro de eventos
- ✅ **Control de Asistencias** - Marcado y seguimiento de presencia
- 📊 **Reportes Avanzados** - Estadísticas y análisis detallados
- 👨‍👩‍👧‍👦 **Grupos Familiares** - Organización por familias
- 👤 **Sistema de Usuarios** - Gestión de roles y permisos

## 🔒 Seguridad

- Autenticación de usuarios robusta
- Protección CSRF implementada
- Sanitización de datos de entrada
- Headers de seguridad configurados
- Bloqueo de archivos sensibles
- Logs de actividad del sistema

## 🚨 Después de la Instalación

1. **Eliminar** archivos de instalación por seguridad
2. **Cambiar** contraseña de administrador por defecto
3. **Configurar** HTTPS (recomendado)
4. **Hacer backup** de la base de datos

## 📞 Solución de Problemas

### Error de Conexión a Base de Datos
- Verificar credenciales en `config.php`
- Confirmar que MySQL esté ejecutándose
- Verificar permisos del usuario de la base de datos

### Error 404 al descargar scripts
Si obtiene un error 404 al intentar descargar los scripts de instalación:

1. **Verificar que el repositorio esté actualizado:**
   ```bash
   git pull origin main
   ```

2. **Usar la instalación manual:**
   ```bash
   git clone https://github.com/problesj/siscda.git
   cd siscda
   # Seguir los pasos de instalación manual
   ```

3. **Verificar la URL del repositorio:**
   - Repositorio: https://github.com/problesj/siscda
   - Rama principal: main

### Error 500
- Revisar logs de Apache/Nginx
- Verificar permisos de archivos
- Habilitar visualización de errores PHP

### Problemas de Permisos
```bash
# En Linux/macOS
sudo chown -R www-data:www-data /var/www/html/siscda
sudo chmod -R 755 /var/www/html/siscda
sudo chmod -R 775 /var/www/html/siscda/assets/uploads
sudo chmod -R 775 /var/www/html/siscda/logs
```

### Problemas de Base de Datos
1. Verificar que MySQL/MariaDB esté ejecutándose
2. Verificar las credenciales en `config.php`
3. Verificar que la base de datos exista y tenga la estructura correcta

## 🛠️ Mantenimiento

### Respaldos
```bash
# Crear backup de la base de datos
php backup_restore.php backup

# Restaurar desde backup
php backup_restore.php restore backups/backup_cda_base_2025-08-26_10-30-00.sql.gz

# Listar backups disponibles
php backup_restore.php list

# Limpiar backups antiguos
php backup_restore.php clean
```

### Configuración en Servidor Remoto
```bash
# Usar el script de configuración automática
chmod +x setup_database.sh
sudo ./setup_database.sh
```

### Actualizaciones
```bash
# Hacer backup antes de actualizar
php backup_restore.php backup

# Actualizar código
git pull origin main

# Verificar cambios
git log --oneline -5
```

## 📚 Documentación Completa

Para más detalles, consulta:
- `README.md` - Documentación principal del proyecto
- `MANUAL_INSTALACION.md` - Guía completa de instalación y configuración

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Cree una rama para su feature (`git checkout -b feature/AmazingFeature`)
3. Commit sus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abra un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Consulte el archivo `LICENSE` para más detalles.

## 🔄 Changelog

### v1.1.0
- Script `setup_database.sh` para configuración automática de BD
- Mejoras en manejo de errores y autenticación
- Limpieza del repositorio y consolidación de documentación

### v1.0.0
- Sistema de control de asistencias completo
- Módulos de usuarios, personas, cultos y reportes
- Scripts de instalación automática
- Soporte para Linux, macOS y Windows

## 📞 Soporte

### Información del Proyecto
- **Repositorio**: https://github.com/problesj/siscda
- **Versión**: 1.1.0
- **Fecha**: Agosto 2025

### Recursos de Ayuda
- **Issues**: Reportar problemas en GitHub
- **Documentación**: Guías completas incluidas
- **Scripts**: Instalación automática disponible

---

**¡Listo! Tu Sistema CDA está funcionando.** 🎉

**Recuerda cambiar la contraseña por defecto después de la instalación.**
