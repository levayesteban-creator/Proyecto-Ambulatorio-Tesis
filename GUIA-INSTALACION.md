# Guía de Instalación - Sistema de Gestión de Salud

**Para alguien que nunca ha instalado un programa de desarrollo.**
Sigue cada paso en orden. No saltes ninguno.

---

## PASO 1: Descargar e instalar Laragon

Laragon incluye PHP, MySQL y Apache (lo que necesita el programa para funcionar).

1. Abrir el navegador (Chrome)
2. Ir a: **https://laragon.org/download**
3. Hacer clic en **"Download"** (la versión "Lite" es suficiente)
4. Abrir el archivo descargado (`laragon-*.exe`)
5. Si Windows pregunta "¿Quieres permitir que esta app haga cambios?", dar clic en **Sí**
6. En el instalador:
   - Idioma: seleccionar **Español**
   - Clic en **Siguiente**
   - Carpeta de instalación: dejar como está (C:\laragon)
   - Clic en **Instalar**
   - Esperar a que termine
   - Clic en **Finalizar**
7. Laragon se abre solo. Verás una ventana pequeña en la esquina inferior derecha

---

## PASO 2: Descargar e instalar Git

Git es para descargar el programa desde internet.

1. Ir a: **https://git-scm.com/downloads/win**
2. Hacer clic en **"Download"** (se descarga solo)
3. Abrir el archivo descargado
4. Si Windows pregunta, dar clic en **Sí**
5. En el instalador:
   - Clic en **Next**
   - **Next** (licken las opciones por defecto)
   - **Next**
   - En "Adjusting your PATH": seleccionar **"Git from the command line and also from 3rd-party software"** (la opción que ya viene marcada)
   - **Next**
   - **Next**
   - **Next**
   - **Next**
   - **Next**
   - **Install**
   - Esperar a que termine
   - **Finish**

---

## PASO 3: Descargar e instalar Node.js

Node.js es para compilar el frontend del programa.

1. Ir a: **https://nodejs.org**
2. Hacer clic en el botón verde **"LTS"** (versión estable)
3. Abrir el archivo descargado
4. Si Windows pregunta, dar clic en **Sí**
5. En el instalador:
   - **Next**
   - Aceptar los términos: marcar "I accept..." → **Next**
   - **Next** (carpeta por defecto)
   - **Next** (opciones por defecto)
   - **Install**
   - Si pregunta instalar componentes adicionales, dar clic en **Sí**
   - Esperar a que termine
   - **Finish**

---

## PASO 4: Descargar e instalar Composer

Composer es para instalar las dependencias PHP del programa.

1. Ir a: **https://getcomposer.org/download/**
2. Hacer clic en **"Composer-Setup.exe"** (Windows Installer)
3. Si el navegador pregunta si quieres guardar, dar clic en **Sí**
4. Abrir el archivo descargado
5. Si Windows pregunta, dar clic en **Sí**
6. En el instalador:
   - Seleccionar **"Install for all users"** → **Next**
   - Marcar **"Add to PATH"** (debe estar marcado) → **Next**
   - En "PHP executable": buscar y seleccionar:
     ```
     C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\php.exe
     ```
     (Si no ves esa carpeta, busca dentro de `C:\laragon\bin\php\` la carpeta que empiece con `php-`)
   - **Next**
   - **Next**
   - **Install**
   - Esperar a que termine
   - **Finish**

---

## PASO 5: Descargar e instalar Visual Studio Code

Es el programa donde se escribe el código. Lo necesitamos para abrir el proyecto.

1. Ir a: **https://code.visualstudio.com/download**
2. Hacer clic en **"Windows"** (el botón grande)
3. Abrir el archivo descargado
4. Si Windows pregunta, dar clic en **Sí**
5. En el instalador:
   - Aceptar términos → **Next**
   - **Next** (carpeta por defecto)
   - Marcar:
     - ✅ "Add Open with Code action to Windows Explorer file context menu"
     - ✅ "Add Open with Code action to Windows Explorer directory context menu"
   - **Next**
   - **Install**
   - Esperar a que termine
   - Marcar **"Launch Visual Studio Code"**
   - **Finish**

---

## PASO 6: REINICIAR LA COMPUTADORA

**Esto es obligatorio.** Sin reiniciar, los programas no funcionan correctamente.

1. Guardar todo lo que tengas abierto
2. Clic en **Inicio** → **Apagar o cerrar sesión** → **Reiniciar**
3. Esperar a que la computadora encienda de nuevo
4. Abrir Laragon (debe estar en la bandeja del sistema, esquina inferior derecha)
5. Verificar que Apache y MySQL estén en verde (activos)

---

## PASO 7: Abrir terminal y clonar el programa

1. Hacer clic derecho en cualquier parte de la pantalla de inicio
2. Seleccionar **"Abrir en Terminal"** o **"Open in Terminal"**
3. Si se abre PowerShell, escribir:
   ```
   cd Desktop
   ```
4. Escribir este comando y dar Enter:
   ```
   git clone https://github.com/levayesteban-creator/Proyecto-Ambulatorio-Tesis.git
   ```
5. Esperar a que descargue todos los archivos (puede tardar 1-2 minutos)

---

## PASO 8: Ejecutar el instalador

1. En la misma terminal, escribir:
   ```
   cd Proyecto-Ambulatorio-Tesis
   ```
2. Escribir:
   ```
   .\instalar.bat
   ```
3. El instalador te preguntará:
   - **Email del admin:** escribe tu correo (ejemplo: tu@gmail.com)
   - **Contraseña del admin:** escribe una contraseña segura
   - **Nombre del admin:** escribe tu nombre completo
4. Esperar a que termine (puede tardar 3-5 minutos)
5. Cuando diga "INSTALACION COMPLETADA", presionar cualquier tecla

---

## PASO 9: Configurar el sitio web en Laragon

1. Abrir **Laragon** (clic en el ícono en la bandeja del sistema)
2. Clic en **Menu** → **Apache** → **vhosts**
3. Se abrirá un archivo de texto. Borrar todo lo que haya y escribir:
   ```
   <VirtualHost *:80>
       ServerName gestion-salud.test
       DocumentRoot "C:/laragon/www/Proyecto-Ambulatorio-Tesis/public"
       <Directory "C:/laragon/www/Proyecto-Ambulatorio-Tesis/public">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```
4. Guardar el archivo (Ctrl+S) y cerrar
5. En Laragon, clic en **"Reload"** (o reiniciar Apache)

---

## PASO 10: Acceder al programa

1. Abrir el navegador (Chrome)
2. Escribir en la barra de direcciones:
   ```
   http://gestion-salud.test
   ```
3. Verás la pantalla de inicio de sesión
4. Escribir el email y contraseña que configuraste en el Paso 8
5. Clic en **Iniciar Sesión**
6. **Listo.** El programa está funcionando.

---

## Resumen rápido (si ya tienes todo instalado)

```
git clone https://github.com/levayesteban-creator/Proyecto-Ambulatorio-Tesis.git
cd Proyecto-Ambulatorio-Tesis
.\instalar.bat
```

---

## Solución de problemas

### "No se reconoce 'git' como comando"
→ Reiniciar la computadora después de instalar Git

### "No se reconoce 'node' como comando"
→ Reiniciar la computadora después de instalar Node.js

### "No se reconoce 'composer' como comando"
→ Reiniciar la computadora y verificar que Composer se instaló con "Add to PATH"

### No carga la página http://gestion-salud.test
→ Verificar que Laragon esté abierto y que Apache y MySQL estén en verde

### Error de base de datos
→ Verificar que MySQL esté activo en Laragon (verde)

### El frontend se ve sin estilos
→ Ejecutar en la terminal: `npm run build`
