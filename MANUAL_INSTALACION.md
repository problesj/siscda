# 📚 Manual de Instalación - Sistema CDA

## 🎯 Descripción General

El **Sistema de Control de Asistencias (CDA)** es una aplicación web desarrollada en PHP que permite gestionar asistencias a cultos y eventos de una iglesia o organización religiosa.

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

### Paso 1: Descargar y Subir Archivos

1. **Descarga** todos los archivos del sistema
2. **Sube** los archivos a tu servidor web en el directorio deseado (ej: `/var/www/html/siscda/`)
3. **Asegúrate** de que el directorio sea accesible desde el navegador

### Paso 2: Ejecutar el Instalador

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

### Paso 3: Acceder al Sistema

- **URL**: `http://tu-servidor/siscda/`
- **Usuario**: `admin`
- **Contraseña**: `admin123`

## 🔧 Instalación Manual

### Paso 1: Crear Base de Datos

```sql
CREATE DATABASE cda_base CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Paso 2: Ejecutar Script SQL

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

## ⚙️ Configuración del Servidor

### Apache

#### Habilitar Módulos
```bash
sudo a2enmod rewrite
sudo a2enmod headers
sudo systemctl restart apache2
```

#### Configuración Virtual Host (Opcional)
```apache
<VirtualHost *:80>
    ServerName siscda.tudominio.com
    DocumentRoot /var/www/html/siscda
    
    <Directory /var/www/html/siscda>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/siscda_error.log
    CustomLog ${APACHE_LOG_DIR}/siscda_access.log combined
</VirtualHost>
```

### Nginx

#### Configuración del Sitio
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
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

## 🔒 Configuración de Seguridad

### Después de la Instalación

1. **Elimina** el archivo `install.php`
2. **Cambia** la contraseña del usuario administrador
3. **Configura** HTTPS si es posible
4. **Revisa** los logs del servidor regularmente

### Archivos de Configuración

- **config.php**: Contiene credenciales de base de datos
- **.htaccess**: Configuración de seguridad del servidor
- **session_config.php**: Configuración de sesiones

## 📊 Estructura de la Base de Datos

### Tablas Principales

| Tabla | Descripción |
|-------|-------------|
| `usuarios` | Usuarios del sistema |
| `personas` | Personas registradas |
| `cultos` | Eventos/cultos |
| `asistencias` | Registro de asistencias |
| `grupos_familiares` | Grupos familiares (opcional) |

### Relaciones

- **personas** → **asistencias** (1:N)
- **cultos** → **asistencias** (1:N)
- **personas** → **grupos_familiares** (N:1)

## 🚨 Solución de Problemas

### Error de Conexión a Base de Datos

1. **Verifica** que MySQL esté ejecutándose
2. **Confirma** las credenciales en `config.php`
3. **Asegúrate** de que el usuario tenga permisos

### Error 500 (Internal Server Error)

1. **Revisa** los logs de error de Apache/Nginx
2. **Verifica** que PHP esté configurado correctamente
3. **Confirma** que las extensiones requeridas estén habilitadas

### Página en Blanco

1. **Habilita** la visualización de errores en PHP
2. **Verifica** los permisos de archivos
3. **Revisa** la configuración de PHP

### Problemas de Permisos

```bash
# Establecer propietario correcto
sudo chown -R www-data:www-data /var/www/html/siscda

# Establecer permisos correctos
sudo find /var/www/html/siscda -type f -exec chmod 644 {} \;
sudo find /var/www/html/siscda -type d -exec chmod 755 {} \;
```

## 🔄 Actualizaciones

### Antes de Actualizar

1. **Haz backup** de la base de datos
2. **Haz backup** de todos los archivos
3. **Documenta** cualquier personalización

### Proceso de Actualización

1. **Descarga** la nueva versión
2. **Reemplaza** los archivos (excepto `config.php`)
3. **Ejecuta** cualquier script de migración necesario
4. **Verifica** que todo funcione correctamente

## 📞 Soporte

### Información de Contacto

- **Desarrollador**: Sistema CDA
- **Versión**: 1.0.0
- **Fecha**: Agosto 2025

### Recursos Adicionales

- **Documentación**: README.md
- **Logs del Sistema**: Directorio `logs/`
- **Backup de Base de Datos**: Recomendado antes de cambios importantes

## ✅ Verificación de Instalación

### Checklist de Verificación

- [ ] El instalador se ejecuta sin errores
- [ ] Se puede acceder al sistema con admin/admin123
- [ ] Se pueden crear nuevas personas
- [ ] Se pueden registrar cultos
- [ ] Se pueden marcar asistencias
- [ ] Los reportes funcionan correctamente
- [ ] No hay errores en los logs del servidor

### Comandos de Verificación

```bash
# Verificar que PHP esté funcionando
php -v

# Verificar extensiones PHP
php -m | grep -E "(pdo|session|mbstring)"

# Verificar permisos de archivos
ls -la /var/www/html/siscda/

# Verificar logs de error
tail -f /var/log/apache2/error.log
```

---

**¡Felicitaciones! Tu Sistema CDA está listo para usar.** 🎉

Para comenzar, accede a `http://tu-servidor/siscda/` y usa las credenciales:
- **Usuario**: `admin`
- **Contraseña**: `admin123`

**Recuerda cambiar la contraseña por defecto después del primer acceso.**
