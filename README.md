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

### Desde GitHub (Recomendado)

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

### Instalación Manual
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

## 📋 Requisitos del Sistema

- **PHP**: 7.4 o superior
- **MySQL**: 5.7 o superior
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
├── assets/           # CSS, JS e imágenes
├── includes/         # Archivos de inclusión
├── modules/          # Módulos del sistema
├── backups/          # Directorio de backups
├── config.php        # Configuración principal
├── index.php         # Página de inicio
├── dashboard.php     # Panel principal
└── README.md         # Esta documentación
```

## 🔒 Seguridad

### Características Implementadas
- ✅ Autenticación de usuarios con sesiones seguras
- ✅ Protección CSRF con tokens únicos
- ✅ Sanitización de datos para prevenir XSS
- ✅ Headers de seguridad HTTP
- ✅ Bloqueo de archivos sensibles
- ✅ Preparación de consultas para prevenir SQL injection

### Después de la Instalación
1. **Eliminar** archivos de instalación
2. **Cambiar** contraseña por defecto (admin/admin123)
3. **Configurar** HTTPS si es posible
4. **Revisar** logs regularmente
5. **Hacer backups** periódicos

## 📚 Documentación

### Guías de Instalación
- **`README_GITHUB.md`** - Instalación desde GitHub
- **`INSTALACION_RAPIDA.md`** - Instalación en 5 minutos
- **`MANUAL_INSTALACION.md`** - Guía completa

### Scripts de Utilidad
- **`install_github.sh`** - Instalador automático Linux/macOS
- **`install_github.ps1`** - Instalador automático Windows
- **`backup_restore.php`** - Backup y restauración de BD
- **`install.php`** - Instalador web

## 🚨 Solución de Problemas

### Problemas Comunes
- **Error de conexión**: Verificar credenciales MySQL
- **Error 500**: Revisar logs del servidor
- **Página en blanco**: Verificar extensiones PHP
- **Problemas de permisos**: Ejecutar script de instalación

### Comandos de Verificación
```bash
# Verificar PHP
php -v
php -m | grep -E "(pdo|session|mbstring)"

# Verificar MySQL
mysql --version

# Verificar permisos
ls -la /var/www/html/siscda/
```

## 🔄 Actualizaciones

### Proceso de Actualización
```bash
# Hacer backup
cd /var/www/html/siscda
php backup_restore.php backup

# Actualizar código
git pull origin main

# Verificar cambios
git log --oneline -5
```

## 📞 Soporte

### Información del Proyecto
- **Repositorio**: https://github.com/problesj/siscda
- **Desarrollador**: Sistema CDA
- **Versión**: 1.0.0
- **Fecha**: Agosto 2025

### Recursos de Ayuda
- **Issues**: Reportar problemas en GitHub
- **Documentación**: Guías completas incluidas
- **Scripts**: Instalación automática disponible

## 📄 Licencia

Este proyecto está bajo la **Licencia MIT**. Ver el archivo `LICENSE` para más detalles.

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor:

1. **Fork** el repositorio
2. **Crea** una rama para tu feature
3. **Commit** tus cambios
4. **Push** a la rama
5. **Abre** un Pull Request

## ⭐ Agradecimientos

- **Comunidad PHP** por el excelente lenguaje
- **Bootstrap** por el framework CSS
- **Font Awesome** por los iconos
- **Contribuidores** del proyecto

---

## 🎯 Próximos Pasos

1. **Clona** el repositorio desde GitHub
2. **Ejecuta** el instalador automático
3. **Configura** según tus necesidades
4. **Personaliza** el sistema
5. **¡Disfruta** de tu nuevo Sistema CDA!

**¿Necesitas ayuda?** Revisa la documentación o abre un issue en GitHub.

---

**¡Gracias por usar el Sistema CDA!** 🎉

*Un sistema completo para el control de asistencias de tu organización religiosa.*
