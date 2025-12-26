# Instrucciones para Agregar Tablas en Producción

Este script agrega las siguientes tablas nuevas al servidor en producción:
- `roles_sistema`
- `modulos`
- `privilegios`
- `ofrendas`

## Requisitos Previos

1. Acceso a la base de datos de producción
2. Credenciales de usuario con permisos de CREATE TABLE, INSERT, etc.
3. Backup de la base de datos antes de ejecutar el script

## Pasos para Ejecutar

### Opción 1: Desde la línea de comandos (MySQL/MariaDB)

```bash
mysql -u usuario -p nombre_base_datos < scripts/agregar_tablas_produccion.sql
```

### Opción 2: Desde MySQL Workbench o phpMyAdmin

1. Abrir el archivo `scripts/agregar_tablas_produccion.sql`
2. Ejecutar todo el script completo
3. Verificar que no haya errores

### Opción 3: Ejecutar sección por sección

Si prefieres ejecutar paso a paso, puedes ejecutar cada sección del script por separado.

## Verificación Post-Ejecución

Después de ejecutar el script, verifica que:

1. Las 4 tablas se hayan creado correctamente:
```sql
SHOW TABLES LIKE 'roles_sistema';
SHOW TABLES LIKE 'modulos';
SHOW TABLES LIKE 'privilegios';
SHOW TABLES LIKE 'ofrendas';
```

2. Los roles iniciales se hayan insertado:
```sql
SELECT * FROM roles_sistema;
```
Debe mostrar: Administrador y Usuario

3. Los módulos se hayan insertado:
```sql
SELECT * FROM modulos;
```
Debe mostrar: Usuarios, Personas, Cultos, Asistencias, Reportes, Ofrendas, Diezmos

4. El usuario admin tenga privilegios asignados:
```sql
SELECT 
    u.USERNAME,
    m.nombre_modulo,
    rs.nombre_rol
FROM privilegios p
INNER JOIN usuarios u ON p.id_usuario = u.USUARIO_ID
INNER JOIN modulos m ON p.id_modulo = m.id
INNER JOIN roles_sistema rs ON p.id_rol_sistema = rs.id
WHERE LOWER(u.USERNAME) = 'admin';
```

## Notas Importantes

- El script usa `CREATE TABLE IF NOT EXISTS`, por lo que es seguro ejecutarlo múltiples veces
- El script usa `INSERT IGNORE` para los datos iniciales, evitando duplicados
- Los privilegios del usuario admin se asignan automáticamente si el usuario existe
- Si el usuario admin no existe, los privilegios no se asignarán (pero las tablas se crearán correctamente)

## Solución de Problemas

### Error: "Table already exists"
- Esto es normal si las tablas ya existen. El script no las sobrescribirá.

### Error: "Foreign key constraint fails"
- Verifica que la tabla `usuarios` y `cultos` existan antes de ejecutar el script
- Verifica que las columnas referenciadas existan en las tablas padre

### El usuario admin no tiene privilegios
- Verifica que el usuario 'admin' exista en la tabla `usuarios`
- El USERNAME puede ser case-sensitive, verifica que coincida exactamente
- Puedes ejecutar manualmente la sección de asignación de privilegios si es necesario

## Rollback (Si es necesario)

Si necesitas revertir los cambios:

```sql
-- Eliminar tablas (¡CUIDADO! Esto eliminará todos los datos)
DROP TABLE IF EXISTS privilegios;
DROP TABLE IF EXISTS ofrendas;
DROP TABLE IF EXISTS modulos;
DROP TABLE IF EXISTS roles_sistema;
```

**ADVERTENCIA**: Solo ejecuta el rollback si estás seguro de que quieres eliminar todas las tablas y sus datos.





