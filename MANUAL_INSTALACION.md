# 📚 Manual de Instalación - Sistema CDA

## 🎯 Descripción General

El **Sistema de Control de Asistencias (CDA)** es una aplicación web desarrollada en PHP que permite gestionar asistencias a cultos y eventos de una iglesia u organización religiosa.

## 📋 Requisitos del Sistema

### Servidor Web
- **Apache 2.4+** o **Nginx 1.18+**
- **mod_rewrite** habilitado (para Apache)
- **mod_headers** habilitado (para Apache)

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
- **Usuario con permisos** para crear bases de datos y tablas
- **Charset**: `utf8mb4`
- **Collation**: `utf8mb4_unicode_ci`

### Navegador
- **Chrome 80+**
- **Firefox 75+**
- **Safari 13+**
- **Edge 80+**

## 🚀 Instalación Automática (Recomendada)

### Opción 1: Instalación desde GitHub

#### Linux/macOS
```bash
# Descargar e instalar automáticamente
wget https://raw.githubusercontent.com/problesj/siscda/main/install_github.sh
chmod +x install_github.sh
./install_github.sh
```

#### Windows
```powershell
# Descargar e instalar automáticamente
Invoke-WebRequest -Uri "https://raw.githubusercontent.com/problesj/siscda/main/install_github.ps1" -OutFile "install_github.ps1"
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
.\install_github.ps1
```

### Opción 2: Instalación con Scripts Generales

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

### Opción 3: Instalador Web

1. **Abre tu navegador** y ve a: `http://tu-servidor/siscda/install.php`
2. **Sigue los pasos** del instalador automático:

#### Paso 1: Configuración de MySQL
- **Host**: `localhost` (o la IP de tu servidor MySQL)
- **Usuario**: Usuario con permisos de administrador
- **Contraseña**: Contraseña del usuario

#### Paso 2: Crear Base de Datos
- **Nombre**: `cda_base` (o el nombre que prefieras)

#### Paso 3: Crear Tablas
- El sistema creará automáticamente todas las tablas necesarias

#### Paso 4: Datos de Ejemplo
- Se insertarán datos de prueba para comenzar a usar el sistema

#### Paso 5: Archivo de Configuración
- Se generará automáticamente el archivo `config.php`

### Opción 4: Configuración Manual de Base de Datos

Si prefieres configurar la base de datos manualmente:

```bash
# Conectar a MySQL como root
mysql -u root -p

# En MySQL, ejecutar:
CREATE DATABASE cda_base CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'cda_user'@'localhost' IDENTIFIED BY 'tu_contraseña';
GRANT ALL PRIVILEGES ON cda_base.* TO 'cda_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## 🔧 Instalación Manual

### Paso 1: Clonar Repositorio

```bash
git clone https://github.com/problesj/siscda.git
cd siscda
```

### Paso 2: Configurar Base de Datos

1. **Abre** tu cliente MySQL (phpMyAdmin, MySQL Workbench, etc.)
2. **Selecciona** la base de datos `cda_base`
3. **Ejecuta** el archivo `install.sql`

### Paso 3: Configurar Archivos

1. **Copia** `config.example.php` a `config.php`
2. **Edita** `config.php` con tus credenciales de base de datos
3. **Copia** `.htaccess.example` a `.htaccess`

### Paso 4: Configurar Permisos

```bash
# En sistemas Linux/Unix
chmod 644 config.php
chmod 644 .htaccess
chmod 755 includes/
chmod 755 modules/
chmod 755 assets/
```

## 🚨 Después de la Instalación

### 1. Eliminar Archivos de Instalación
```bash
# Por seguridad, eliminar archivos de instalación
rm install.php
```

### 2. Cambiar Contraseña por Defecto
- **Usuario**: `admin`
- **Contraseña**: `admin123`
- Cambiar inmediatamente después del primer acceso

### 3. Configurar HTTPS (Recomendado)
- Configurar certificado SSL
- Redirigir HTTP a HTTPS

### 4. Hacer Backup Inicial
```bash
# Crear backup de la base de datos
php backup_restore.php backup
```

## 📊 Estructura del Sistema

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

## 🔒 Configuración de Seguridad

### 1. Archivo .htaccess
El archivo `.htaccess` incluye:
- Bloqueo de archivos sensibles
- Headers de seguridad
- Compresión y caché
- Redirección de errores

### 2. Configuración de Sesiones
- Cookies seguras (HttpOnly, Secure)
- Timeout de sesión configurable
- Regeneración de ID de sesión

### 3. Sanitización de Datos
- Función `limpiarDatos()` para prevenir XSS
- Preparación de consultas SQL
- Validación de entrada

## 🛠️ Mantenimiento

### Respaldos Regulares
```bash
# Crear backup diario
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

## 📞 Solución de Problemas

### Error de Conexión a Base de Datos
- Verificar credenciales en `config.php`
- Confirmar que MySQL esté ejecutándose
- Verificar permisos del usuario de la base de datos

### Error 500
- Revisar logs de Apache/Nginx
- Verificar permisos de archivos
- Habilitar visualización de errores PHP

### Problemas de Permisos
```bash
# En sistemas Linux/Unix
sudo chown -R www-data:www-data /var/www/html/siscda
sudo chmod -R 755 /var/www/html/siscda
sudo chmod -R 775 /var/www/html/siscda/assets/uploads
```

### Error de Función `limpiarDatos()`
- Verificar que `config.php` esté incluido correctamente
- El archivo `auth.php` incluye un fallback seguro

## 🔄 Actualizaciones

### Proceso de Actualización
```bash
# Hacer backup antes de actualizar
php backup_restore.php backup

# Actualizar código
git pull origin main

# Verificar cambios
git log --oneline -5
```

## 📚 Documentación Adicional

- **`README.md`** - Documentación principal del proyecto
- **`README_GITHUB.md`** - Instrucciones específicas para GitHub

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

**¡Tu Sistema CDA está listo para usar!** 🎉

**Recuerda cambiar la contraseña por defecto y hacer respaldos regulares.**
