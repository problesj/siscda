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
// Base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'cda_base');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');

// Aplicación
define('APP_NAME', 'Sistema de Control de Asistencias');
define('APP_VERSION', '1.0.0');
```

### 2. Configuración del Servidor

#### Apache
```apache
<VirtualHost *:80>
    ServerName siscda.tudominio.com
    DocumentRoot /var/www/html/siscda
    
    <Directory /var/www/html/siscda>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx
```nginx
server {
    listen 80;
    server_name siscda.tudominio.com;
    root /var/www/html/siscda;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}
```

## 📊 Estructura de la Base de Datos

### Tablas Principales

| Tabla | Descripción |
|-------|-------------|
| `usuarios` | Usuarios del sistema |
| `personas` | Personas registradas |
| `cultos` | Eventos/cultos |
| `asistencias` | Registro de asistencias |
| `grupos_familiares` | Grupos familiares |

### Relaciones

- **personas** → **asistencias** (1:N)
- **cultos** → **asistencias** (1:N)
- **personas** → **grupos_familiares** (N:1)

## 🔒 Seguridad

### Características Implementadas

- ✅ **Autenticación de usuarios** con sesiones seguras
- ✅ **Protección CSRF** con tokens únicos
- ✅ **Sanitización de datos** para prevenir XSS
- ✅ **Headers de seguridad** HTTP
- ✅ **Bloqueo de archivos sensibles** via .htaccess
- ✅ **Preparación de consultas** para prevenir SQL injection

### Después de la Instalación

1. **Eliminar** archivos de instalación
2. **Cambiar** contraseña por defecto
3. **Configurar** HTTPS
4. **Revisar** logs regularmente
5. **Hacer backups** periódicos

## 📚 Documentación

### Archivos de Documentación

- **`README.md`** - Documentación general del proyecto
- **`MANUAL_INSTALACION.md`** - Guía completa de instalación
- **`INSTALACION_RAPIDA.md`** - Instalación en 5 minutos
- **`README_GITHUB.md`** - Esta guía específica para GitHub

### Scripts de Utilidad

- **`install_github.sh`** - Instalador automático para Linux/macOS
- **`install_github.ps1`** - Instalador automático para Windows
- **`backup_restore.php`** - Script de backup y restauración
- **`install.php`** - Instalador web (después de clonar)

## 🚨 Solución de Problemas

### Problemas Comunes

#### Error de Conexión a Base de Datos
```bash
# Verificar que MySQL esté ejecutándose
sudo systemctl status mysql

# Verificar credenciales
mysql -u usuario -p -h localhost
```

#### Error 500 (Internal Server Error)
```bash
# Revisar logs de Apache
sudo tail -f /var/log/apache2/error.log

# Revisar logs de Nginx
sudo tail -f /var/log/nginx/error.log
```

#### Problemas de Permisos
```bash
# Establecer propietario correcto
sudo chown -R www-data:www-data /var/www/html/siscda

# Establecer permisos correctos
sudo find /var/www/html/siscda -type f -exec chmod 644 {} \;
sudo find /var/www/html/siscda -type d -exec chmod 755 {} \;
```

### Comandos de Verificación

```bash
# Verificar PHP
php -v
php -m | grep -E "(pdo|session|mbstring)"

# Verificar MySQL
mysql --version

# Verificar Git
git --version

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

### Antes de Actualizar

1. **Hacer backup** de la base de datos
2. **Hacer backup** de archivos personalizados
3. **Documentar** cambios personalizados
4. **Probar** en entorno de desarrollo

## 📞 Soporte

### Información del Proyecto

- **Repositorio**: https://github.com/problesj/siscda
- **Desarrollador**: Sistema CDA
- **Versión**: 1.0.0
- **Fecha**: Agosto 2025

### Recursos Adicionales

- **Issues**: Reportar problemas en GitHub
- **Wiki**: Documentación adicional (si está disponible)
- **Discussions**: Foro de discusión (si está habilitado)

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor:

1. **Fork** el repositorio
2. **Crea** una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. **Commit** tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. **Push** a la rama (`git push origin feature/AmazingFeature`)
5. **Abre** un Pull Request

## ⭐ Agradecimientos

- **Comunidad PHP** por el excelente lenguaje
- **Bootstrap** por el framework CSS
- **Font Awesome** por los iconos
- **Contribuidores** del proyecto

---

## 🎯 Próximos Pasos

1. **Clona** el repositorio
2. **Ejecuta** el instalador automático
3. **Configura** según tus necesidades
4. **Personaliza** el sistema
5. **¡Disfruta** de tu nuevo Sistema CDA!

**¿Necesitas ayuda?** Revisa la documentación o abre un issue en GitHub.

---

**¡Gracias por usar el Sistema CDA!** 🎉
