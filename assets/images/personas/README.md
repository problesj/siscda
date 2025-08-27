# Directorio de Imágenes de Personas

Este directorio contiene las imágenes de las personas registradas en el sistema.

## Estructura

- `default_male.jpg` - Imagen por defecto para personas masculinas
- `default_female.jpg` - Imagen por defecto para personas femeninas
- `[ID_PERSONA].jpg` - Imágenes específicas de cada persona

## Notas

- Las imágenes se almacenan con el ID de la persona como nombre
- Se recomienda usar formato JPG o PNG
- Tamaño recomendado: 200x200 píxeles
- Peso máximo recomendado: 500KB por imagen

## Permisos

Asegúrate de que el directorio tenga permisos de escritura para el servidor web:

```bash
chmod 755 assets/images/personas/
chown www-data:www-data assets/images/personas/
```
