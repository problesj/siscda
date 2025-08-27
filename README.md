# 🏛️ Sistema CDA - Control de Asistencias

[![GitHub](https://img.shields.io/badge/GitHub-Repository-blue?style=for-the-badge&logo=github)](https://github.com/problesj/siscda)
[![PHP](https://img.shields.io/badge/PHP-7.4+-purple?style=for-the-badge&logo=php)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-blue?style=for-the-badge&logo=mysql)](https://mysql.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

## 📋 Descripción

El **Sistema de Control de Asistencias (CDA)** es una aplicación web desarrollada en PHP que permite gestionar asistencias a cultos y eventos de una iglesia u organización religiosa. El sistema proporciona una interfaz intuitiva para el registro de personas, programación de cultos, control de asistencias y generación de reportes detallados.

## 🌟 Características Principales

### 👥 Gestión de Personas
- Registro completo de miembros con información personal
- Organización por grupos familiares
- Historial de asistencia individual
- Gestión de contactos y observaciones

### 📅 Control de Cultos
- Programación de eventos y cultos
- Diferentes tipos de servicios
- Control de fechas y horarios
- Descripción detallada de cada evento

### ✅ Sistema de Asistencias
- Marcado rápido de asistencia
- Registro automático de fecha y hora
- Control de asistencias por culto
- Historial completo de presencia

### 📊 Reportes y Estadísticas
- Reportes de asistencia por período
- Estadísticas por persona y familia
- Análisis de tendencias
- Exportación de datos

### 🔒 Seguridad y Usuarios
- Sistema de autenticación robusto
- Gestión de roles y permisos
- Protección CSRF y sanitización de datos
- Logs de actividad del sistema

## 🚀 Instalación Rápida

### Opción 1: Instalación Automática desde GitHub (Recomendada)

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

### Opción 2: Instalación Manual
```bash
# Clonar repositorio
git clone https://github.com/problesj/siscda.git
cd siscda

# Configurar base de datos
cp config.example.php config.php
# Editar config.php con tus credenciales

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

- **PHP**: 7.4 o superior
- **MySQL**: 5.7 o superior (MariaDB 10.2+)
- **Servidor Web**: Apache 2.4+ o Nginx 1.18+
- **Extensiones PHP**: pdo, pdo_mysql, session, mbstring, json
- **Git**: Para clonar el repositorio

## 🔧 Configuración

### 1. Base de Datos
El sistema crea automáticamente todas las tablas necesarias con la estructura correcta para tu base de datos existente.

### 2. Archivos de Configuración
- `config.php` - Configuración de base de datos y aplicación
- `.htaccess` - Configuración de seguridad del servidor

### 3. Permisos
Los scripts de instalación configuran automáticamente los permisos correctos para tu servidor.

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

## 🛠️ Mantenimiento

### Respaldos
```bash
# Crear backup de la base de datos
php backup_restore.php backup

# Restaurar desde backup
php backup_restore.php restore backups/backup_cda_base_2025-08-26_10-30-00.sql.gz

# Listar backups disponibles
php backup_restore.php list
```

### Configuración de Base de Datos en Servidor Remoto
```bash
# Usar el script de configuración automática
chmod +x setup_database.sh
sudo ./setup_database.sh
```

## 📚 Documentación Completa

Para más detalles, consulta:
- `MANUAL_INSTALACION.md` - Guía completa de instalación y configuración
- `README_GITHUB.md` - Instrucciones específicas para instalación desde GitHub

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

---

**¡Listo! Tu Sistema CDA está funcionando.** 🎉

**Recuerda cambiar la contraseña por defecto después de la instalación.**
