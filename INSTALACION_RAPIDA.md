# 🚀 Instalación Rápida - Sistema CDA

## ⚡ Instalación en 5 Minutos

### 1️⃣ Subir Archivos
```bash
# Descargar y subir todos los archivos a tu servidor
# Ejemplo: /var/www/html/siscda/
```

### 2️⃣ Ejecutar Instalador
```
http://tu-servidor/siscda/install.php
```

### 3️⃣ Seguir los 5 Pasos
- ✅ Conectar a MySQL
- ✅ Crear base de datos
- ✅ Crear tablas
- ✅ Insertar datos de ejemplo
- ✅ Generar configuración

### 4️⃣ Acceder al Sistema
```
URL: http://tu-servidor/siscda/
Usuario: admin
Contraseña: admin123
```

---

## 📋 Requisitos Mínimos

- **PHP**: 7.4+
- **MySQL**: 5.7+
- **Apache/Nginx** con mod_rewrite
- **Extensiones PHP**: pdo, pdo_mysql, session

---

## 🔧 Instalación Manual (Alternativa)

### Crear Base de Datos
```sql
CREATE DATABASE cda_base CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Ejecutar SQL
```bash
mysql -u usuario -p cda_base < install.sql
```

### Configurar
```bash
cp config.example.php config.php
# Editar config.php con tus credenciales
cp .htaccess.example .htaccess
```

---

## 🚨 Después de la Instalación

1. **Eliminar** `install.php`
2. **Cambiar** contraseña de admin
3. **Configurar** HTTPS (recomendado)
4. **Hacer backup** de la base de datos

---

## 📞 Problemas Comunes

### Error de Conexión
- Verificar credenciales MySQL
- Confirmar que MySQL esté ejecutándose

### Error 500
- Revisar logs de Apache/Nginx
- Verificar permisos de archivos

### Página en Blanco
- Habilitar visualización de errores PHP
- Verificar extensiones PHP requeridas

---

## 🎯 Funcionalidades Principales

- 👥 **Gestión de Personas**
- 📅 **Registro de Cultos**
- ✅ **Control de Asistencias**
- 📊 **Reportes y Estadísticas**
- 👨‍👩‍👧‍👦 **Grupos Familiares**
- 👤 **Sistema de Usuarios**

---

## 🔒 Seguridad

- Autenticación de usuarios
- Protección CSRF
- Sanitización de datos
- Headers de seguridad
- Bloqueo de archivos sensibles

---

## 📚 Documentación Completa

Para más detalles, consulta:
- `MANUAL_INSTALACION.md` - Guía completa
- `README.md` - Documentación general

---

**¡Listo! Tu Sistema CDA está funcionando.** 🎉

**Recuerda cambiar la contraseña por defecto.**
