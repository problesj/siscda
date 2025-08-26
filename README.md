# Sistema de Control de Asistencias (CDA)

Sistema web para el registro y control de asistencias a cultos de una iglesia, desarrollado con PHP, MySQL y Bootstrap.

## Características

- **Gestión de Usuarios**: Administración de usuarios del sistema
- **Gestión de Personas**: Registro de asistentes a los cultos
- **Grupos Familiares**: Organización de personas por grupos familiares
- **Gestión de Cultos**: Programación y administración de cultos
- **Control de Asistencias**: Registro de asistencia por culto
- **Reportes**: Generación de reportes de asistencia por fechas
- **Dashboard**: Vista general con estadísticas del sistema

## Requisitos del Sistema

- Apache 2.4+
- PHP 7.4+
- MySQL 5.7+ o MariaDB 10.2+
- Navegador web moderno con JavaScript habilitado

## Instalación

### 1. Configuración de la Base de Datos

La aplicación utiliza la base de datos `cda_base` con las siguientes tablas:

- `usuarios`: Usuarios del sistema
- `personas`: Datos de asistentes
- `grupos_familiares`: Grupos familiares
- `cultos`: Programación de cultos
- `asistencias`: Registro de asistencias

### 2. Configuración del Sistema

1. Copiar todos los archivos al directorio web del servidor
2. Verificar que el directorio tenga permisos de escritura para el usuario web
3. Configurar la conexión a la base de datos en `config.php`
4. Crear un usuario administrador en la tabla `usuarios`

### 3. Crear Usuario Administrador

```sql
INSERT INTO usuarios (username, password, nombre_completo, email, activo) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'admin@iglesia.com', 1);
```

**Contraseña por defecto**: `password`

### 4. Acceso al Sistema

- URL: `http://tu-servidor/siscda/`
- Usuario: `admin`
- Contraseña: `password`

## Estructura del Proyecto

```
siscda/
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── app.js
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── sidebar.php
├── modules/
│   ├── usuarios.php
│   ├── personas.php
│   ├── grupos_familiares.php
│   ├── cultos.php
│   ├── asistencias.php
│   └── reportes.php
├── config.php
├── index.php
├── auth.php
├── dashboard.php
├── logout.php
└── README.md
```

## Módulos del Sistema

### Dashboard
- Estadísticas generales del sistema
- Resumen de cultos recientes
- Lista de personas registradas recientemente

### Usuarios
- Crear, editar y eliminar usuarios del sistema
- Gestión de permisos y accesos

### Personas
- Registro de asistentes a los cultos
- Asociación con grupos familiares
- Información de contacto

### Grupos Familiares
- Organización de personas por afinidad familiar
- Gestión de responsables de grupo

### Cultos
- Programación de cultos con fecha, hora y tipo
- Descripción y detalles del culto

### Asistencias
- Registro de asistencia por culto
- Interfaz intuitiva con checkboxes
- Historial de asistencias

### Reportes
- Reportes de asistencia por fechas
- Filtros por grupo familiar
- Estadísticas de asistencia por persona

## Seguridad

- Autenticación de usuarios
- Validación de datos de entrada
- Protección contra inyección SQL
- Sesiones seguras

## Personalización

El sistema puede ser personalizado modificando:

- `assets/css/style.css`: Estilos visuales
- `assets/js/app.js`: Funcionalidades JavaScript
- `includes/header.php`: Encabezado común
- `includes/sidebar.php`: Menú de navegación

## Soporte

Para soporte técnico o consultas sobre el sistema, contactar al administrador del sistema.

## Licencia

Este software es de uso interno para la iglesia y no está destinado para distribución comercial.
# siscda
