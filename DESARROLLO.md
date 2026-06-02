# Cómo ver los cambios en pantalla (Laragon)

Si solo ves el dashboard viejo de Laravel Breeze **sin** el menú oscuro ni las consultas, el navegador está usando un **build antiguo**.

## Opción A — Producción (recomendada en Laragon)

Cada vez que cambies archivos `.vue` o el layout:

```powershell
cd C:\laragon\www\gestion-salud
npm run build
```

Luego en el navegador: **Ctrl + F5** (recarga forzada).

## Opción B — Desarrollo con recarga automática

Terminal 1 (dejar abierta):

```powershell
cd C:\laragon\www\gestion-salud
npm run dev
```

Terminal 2: Laragon con Apache/MySQL encendidos.

Abrir: `http://gestion-salud.test`

## Rutas del sistema

| Pantalla | URL |
|----------|-----|
| Inicio | `/dashboard` |
| Pacientes | `/patients` |
| Parte 1 — Nueva historia | `/patients/create` |
| Ficha del paciente | `/patients/{id}` |
| **Parte 2 — Nueva consulta** | `/patients/{id}/consultations/create` |

La Parte 2 **no** aparece en el menú lateral: se entra desde **Pacientes → Iniciar consulta** o **+ Nueva Consulta** en la ficha.

## Menú lateral

- Botón **☰** en la barra superior: muestra u oculta el menú.
- Por defecto el menú inicia **oculto** para dar más espacio al formulario.
