# Solución para Error de Exportación Excel

## Problema Identificado

El error en el servidor de producción:
```
PHP Fatal error: Uncaught Error: Failed opening required '/var/www/html/siscda/modules/../vendor/autoload.php'
```

Indica que **PhpSpreadsheet no está instalado** en el servidor de producción.

## Solución Implementada

### 1. **Detección Automática de PhpSpreadsheet**
- El archivo `personas_export.php` ahora detecta automáticamente si PhpSpreadsheet está disponible
- Si está disponible: genera archivo Excel (.xlsx) con formato completo
- Si no está disponible: genera archivo CSV (.csv) como alternativa

### 2. **Funcionalidad Dual**
```php
if ($phpspreadsheet_available) {
    // Usar PhpSpreadsheet para generar Excel
    generarExcelConPhpSpreadsheet($personas);
} else {
    // Usar CSV como alternativa
    generarCSV($personas);
}
```

### 3. **Archivo CSV Optimizado**
- **Separador**: Punto y coma (`;`) para compatibilidad con Excel
- **Codificación**: UTF-8 con BOM para caracteres especiales
- **Headers**: Mismos campos que el Excel
- **Formato**: Compatible con Excel y LibreOffice

## Instalación en Producción

### Opción 1: Instalación Automática
```bash
# En el servidor de producción
cd /var/www/html/siscda
bash install_composer.sh
```

### Opción 2: Instalación Manual
```bash
# Instalar Composer (si no está instalado)
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Instalar dependencias
composer install --no-dev --optimize-autoloader
```

### Opción 3: Solo CSV (Sin PhpSpreadsheet)
- No requiere instalación adicional
- El sistema funcionará automáticamente con CSV
- Los archivos CSV se pueden abrir en Excel

## Archivos Modificados

### `modules/personas_export.php`
- ✅ **Detección automática** de PhpSpreadsheet
- ✅ **Función `generarExcelConPhpSpreadsheet()`** para Excel
- ✅ **Función `generarCSV()`** para CSV
- ✅ **Manejo de errores** mejorado
- ✅ **Compatibilidad** con ambos formatos

### `install_composer.sh` (Nuevo)
- ✅ **Script de instalación** automática
- ✅ **Verificación** de dependencias
- ✅ **Instalación optimizada** para producción

## Ventajas de la Solución

### 🎯 **Robustez**
- Funciona **con o sin** PhpSpreadsheet
- **Detección automática** de capacidades del servidor
- **Fallback** a CSV si Excel no está disponible

### 🚀 **Rendimiento**
- **CSV más rápido** para grandes volúmenes de datos
- **Excel con formato** cuando está disponible
- **Optimización** de memoria

### 🔧 **Mantenimiento**
- **Una sola función** de exportación
- **Código limpio** y mantenible
- **Logging** de errores para debugging

## Uso

### Para el Usuario Final
1. **Hacer clic** en "Exportar a Excel"
2. **El sistema decide** automáticamente el formato:
   - Si PhpSpreadsheet está disponible → **Excel (.xlsx)**
   - Si no está disponible → **CSV (.csv)**
3. **Descargar** el archivo generado

### Para el Administrador
- **Verificar logs** si hay problemas
- **Instalar Composer** si se prefiere Excel
- **Mantener** ambas opciones disponibles

## Verificación

### Comprobar Instalación
```bash
# Verificar si PhpSpreadsheet está disponible
php -r "require 'vendor/autoload.php'; echo class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet') ? 'OK' : 'NO';"
```

### Probar Exportación
1. Ir a **Personas** → **Exportar a Excel**
2. Verificar que se descarga un archivo
3. Abrir el archivo en Excel/LibreOffice

## Notas Técnicas

- **CSV**: Separador `;`, codificación UTF-8 con BOM
- **Excel**: Formato completo con colores y estilos
- **Memoria**: Limpieza automática después de generar
- **Headers**: Configuración correcta para descarga
- **Errores**: Manejo robusto con mensajes informativos
