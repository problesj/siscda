# SISCDA - Sistema de Control de Asistencias

## Instalación Rápida

### Opción 1: Instalación Automática (Recomendada)

#### Para Linux/macOS:
```bash
# Descargar e instalar
wget https://raw.githubusercontent.com/problesj/siscda/main/install.sh
chmod +x install.sh
sudo ./install.sh
```

#### Para Windows:
```powershell
# Descargar e instalar (PowerShell como Administrador)
Invoke-WebRequest -Uri "https://raw.githubusercontent.com/problesj/siscda/main/install.ps1" -OutFile "install.ps1"
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
.\install.ps1
```

### Opción 2: Instalación Manual

#### Requisitos Previos:
- PHP 7.4 o superior
- MySQL 5.7 o MariaDB 10.2 o superior
- Apache/Nginx
- Git

#### Pasos de Instalación:

1. **Clonar el repositorio:**
   ```bash
   git clone https://github.com/problesj/siscda.git
   cd siscda
   ```

2. **Configurar la base de datos:**
   ```bash
   mysql -u root -p < install.sql
   ```

3. **Configurar la aplicación:**
   ```bash
   cp config.example.php config.php
   # Editar config.php con las credenciales de la base de datos
   ```

4. **Configurar permisos:**
   ```bash
   chmod -R 755 .
   chmod -R 775 assets/uploads logs
   ```

5. **Configurar el servidor web:**
   ```bash
   cp .htaccess.example .htaccess
   ```

### Opción 3: Instalación desde GitHub (Scripts Específicos)

#### Para Linux/macOS:
```bash
wget https://raw.githubusercontent.com/problesj/siscda/main/install_github.sh
chmod +x install_github.sh
./install_github.sh
```

#### Para Windows:
```powershell
Invoke-WebRequest -Uri "https://raw.githubusercontent.com/problesj/siscda/main/install_github.ps1" -OutFile "install_github.ps1"
.\install_github.ps1
```

## Configuración

### Base de Datos
- **Host:** localhost
- **Puerto:** 3306 (por defecto)
- **Charset:** utf8mb4
- **Collation:** utf8mb4_unicode_ci

### Archivos de Configuración
- `config.php` - Configuración principal de la aplicación
- `.htaccess` - Configuración del servidor Apache
- `install.sql` - Estructura de la base de datos

## Estructura del Proyecto

```
siscda/
├── assets/           # Archivos estáticos (CSS, JS, imágenes)
├── includes/         # Archivos de inclusión PHP
├── modules/          # Módulos de la aplicación
├── logs/            # Archivos de registro
├── config.php       # Configuración principal
├── index.php        # Punto de entrada
└── install.sql      # Estructura de la base de datos
```

## Solución de Problemas

### Error 404 al descargar scripts
Si obtiene un error 404 al intentar descargar los scripts de instalación:

1. **Verificar que el repositorio esté actualizado:**
   ```bash
   git pull origin main
   ```

2. **Usar la instalación manual:**
   ```bash
   git clone https://github.com/problesj/siscda.git
   cd siscda
   # Seguir los pasos de instalación manual
   ```

3. **Verificar la URL del repositorio:**
   - Repositorio: https://github.com/problesj/siscda
   - Rama principal: main

### Problemas de Permisos
```bash
# En Linux/macOS
sudo chown -R www-data:www-data /var/www/html/siscda
sudo chmod -R 755 /var/www/html/siscda
sudo chmod -R 775 /var/www/html/siscda/assets/uploads
sudo chmod -R 775 /var/www/html/siscda/logs
```

### Problemas de Base de Datos
1. Verificar que MySQL/MariaDB esté ejecutándose
2. Verificar las credenciales en `config.php`
3. Verificar que la base de datos exista y tenga la estructura correcta

## Soporte

- **Documentación:** Consulte `MANUAL_INSTALACION.md` para instrucciones detalladas
- **Issues:** Reporte problemas en GitHub Issues
- **Wiki:** Consulte la wiki del proyecto para más información

## Licencia

Este proyecto está bajo la Licencia MIT. Consulte el archivo `LICENSE` para más detalles.

## Contribuciones

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Cree una rama para su feature (`git checkout -b feature/AmazingFeature`)
3. Commit sus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abra un Pull Request

## Changelog

### v1.0.0
- Sistema de control de asistencias completo
- Módulos de usuarios, personas, cultos y reportes
- Scripts de instalación automática
- Soporte para Linux, macOS y Windows
