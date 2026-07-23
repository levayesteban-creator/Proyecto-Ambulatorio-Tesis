# Sistema de Gestión de Salud - Consultorio "El Chaparro"

Sistema de gestión de historias clínicas y reportes epidemiológicos (EPI) para el Consultorio Popular Tipo III "El Chaparro de Guanta".

## Requisitos del Sistema

| Componente | Versión requerida | Versión probada |
|------------|-------------------|-----------------|
| PHP | ≥ 8.1 | 8.3.26 |
| MySQL | ≥ 5.7 | 8.4.3 |
| Node.js | ≥ 18 | 24.13.0 |
| npm | ≥ 9 | 10.x |
| Composer | ≥ 2.0 | 2.x |
| Git | Cualquier versión reciente | - |

### Extensiones PHP requeridas
- php-curl
- php-dom
- php-mbstring
- php-gd
- php-pdo_mysql
- php-openssl
- php-zip

## Instalación Rápida (Windows)

### Opción 1: Instalador automático
1. Clonar el repositorio:
   ```
   git clone https://github.com/USUARIO/gestion-salud.git
   ```
2. Ejecutar `instalar.bat` como administrador
3. Abrir http://gestion-salud.test

### Opción 2: Instalación manual
1. Clonar el repositorio
2. Ejecutar `verificar-entorno.bat` para confirmar que tienes todo instalado
3. Abrir terminal en la carpeta del proyecto y ejecutar:
   ```
   composer install
   npm install
   npm run build
   cp .env.example .env
   php artisan key:generate
   php artisan migrate --force
   php artisan db:seed --force
   ```
4. Configurar Laragon con el host virtual `gestion-salud.test`
5. Abrir http://gestion-salud.test

## Cuenta de Administrador

Las credenciales se configuran en el archivo `.env` (no se sube a GitHub):

| Variable | Descripción |
|----------|-------------|
| `ADMIN_EMAIL` | Email del administrador |
| `ADMIN_PASSWORD` | Contraseña del administrador |
| `ADMIN_NAME` | Nombre completo |

Al ejecutar `instalar.bat`, se te pedirá configurar estas credenciales.

> **Nota de seguridad:** Nunca subir el archivo `.env` a GitHub. Usar `.env.example` como plantilla.

## Estructura del Proyecto

```
├── app/
│   ├── Http/Controllers/     # Controladores
│   ├── Models/               # Modelos Eloquent
│   ├── Policies/             # Políticas de autorización
│   └── Notifications/        # Notificaciones por email
├── database/
│   ├── migrations/           # Migraciones de la BD
│   └── seeders/              # Datos iniciales (roles, catálogos)
├── resources/
│   ├── js/Pages/             # Componentes Vue (Inertia)
│   ├── js/Components/        # Componentes reutilizables
│   └── views/reports/        # Plantillas PDF (DomPDF)
├── routes/web.php            # Rutas
├── instalar.bat              # Instalador automático
├── verificar-entorno.bat     # Verificador de entorno
└── .env.example              # Plantilla de configuración
```

## Roles del Sistema

| Rol | Permisos |
|-----|----------|
| **Administrador** | Acceso total: usuarios, eliminar/restaurar registros, auditoría, reportes |
| **Médico Coordinador** | Gestionar usuarios, eliminar/restaurar, cerrar historias, auditoría, reportes |
| **Médico** | Registrar pacientes, crear/editar consultas, cerrar historias, reportes |
| **Enfermero** | Registrar pacientes, ver consultas, exportar reportes |

## Reportes Epidemiológicos

- **EPI-10**: Registro Diario de Atención Integral (SIS-02)
- **EPI-12**: Consolidado Semanal de 52 Enfermedades × 13 Grupos de Edad (SIS-04)
- **EPI-13**: Registro de Enfermedades de Notificación Obligatoria (SIS-03)
- **EPI-15**: Consolidado Mensual de Morbilidad por Aparatos y Sistemas

## Solución de Problemas

### Error "No se pudo enviar el correo"
El sistema funciona sin email. El administrador puede restablecer contraseñas directamente desde **Usuarios → Restablecer contraseña**.

### Error de migraciones
```
php artisan migrate:fresh --force
php artisan db:seed --force
```

### Frontend no se actualiza
```
npm run build
```

### Limpiar caché
```
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```
