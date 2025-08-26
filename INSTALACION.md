# Guía de Instalación del Sistema CDA

## Paso 1: Preparación del Servidor

### Requisitos Mínimos:
- Apache 2.4+
- PHP 7.4+
- MySQL 5.7+ o MariaDB 10.2+
- Extensiones PHP: PDO, PDO_MySQL, session

### Verificar Extensiones PHP:
```bash
php -m | grep -E "(pdo|session)"
```

## Paso 2: Configuración de la Base de Datos

### 1. Conectar a MySQL:
```bash
mysql -u root -p
```

### 2. Crear base de datos y usuario:
```sql
CREATE DATABASE cda_base CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'cda_base'@'localhost' IDENTIFIED BY 'cda2025$';
GRANT ALL PRIVILEGES ON cda_base.* TO 'cda_base'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Importar estructura de la base de datos:
```bash
mysql -u cda_base -p cda_base < database_setup.sql
```

## Paso 3: Configuración del Sistema

### 1. Verificar permisos:
```bash
chown -R www-data:www-data /var/www/html/siscda
chmod -R 755 /var/www/html/siscda
chmod 644 /var/www/html/siscda/.htaccess
```

### 2. Verificar configuración en config.php:
- Host de la base de datos
- Nombre de la base de datos
- Usuario y contraseña
- Configuración de sesiones

### 3. Configurar Apache:
Asegurarse de que el directorio esté accesible desde el navegador web.

## Paso 4: Acceso al Sistema

### 1. Abrir navegador:
```
http://tu-servidor/siscda/
```

### 2. Credenciales por defecto:
- **Usuario**: admin
- **Contraseña**: password

### 3. Cambiar contraseña:
Una vez dentro del sistema, cambiar la contraseña del usuario administrador.

## Paso 5: Verificación del Sistema

### 1. Verificar módulos:
- Dashboard: Estadísticas generales
- Usuarios: Gestión de usuarios
- Personas: Registro de asistentes
- Grupos Familiares: Organización familiar
- Cultos: Programación de cultos
- Asistencias: Control de asistencia
- Reportes: Generación de reportes

### 2. Crear datos de prueba:
- Agregar algunos grupos familiares
- Registrar personas
- Crear cultos
- Tomar asistencias

## Paso 6: Personalización

### 1. Modificar estilos:
- Editar `assets/css/style.css`
- Cambiar colores, fuentes, etc.

### 2. Modificar funcionalidades:
- Editar `assets/js/app.js`
- Agregar nuevas características

### 3. Modificar encabezado y pie:
- Editar `includes/header.php`
- Editar `includes/footer.php`

## Solución de Problemas

### Error de conexión a la base de datos:
- Verificar credenciales en `config.php`
- Verificar que MySQL esté ejecutándose
- Verificar permisos del usuario de la base de datos

### Error de permisos:
- Verificar propietario de archivos
- Verificar permisos de directorios
- Verificar configuración de Apache

### Error de sesiones:
- Verificar configuración de PHP
- Verificar permisos de directorio temporal

## Mantenimiento

### 1. Respaldos:
- Respaldar base de datos regularmente
- Respaldar archivos del sistema

### 2. Actualizaciones:
- Mantener PHP y MySQL actualizados
- Revisar logs de errores

### 3. Seguridad:
- Cambiar contraseñas regularmente
- Revisar logs de acceso
- Mantener sistema actualizado

## Soporte

Para soporte técnico o consultas:
- Revisar logs de errores
- Verificar configuración del servidor
- Contactar al administrador del sistema
