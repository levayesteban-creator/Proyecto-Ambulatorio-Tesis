# 📋 GUÍA DE REGISTRO DE USUARIOS SEGÚN NIVEL

## 🗝️ CREDENCIALES DEL ADMINISTRADOR

**Email:** `levayesteban@gmail.com`  
**Contraseña:** `Estebanmiguel*`  
**Nivel:** Administrador (Nivel máximo)

---

## 🏥 NIVELES DE USUARIOS Y PERMISOS

### 1. ADMINISTRADOR (Nivel Máximo)
- **Puede crear y editar pacientes**
- **Puede cerrar historias clínicas**
- **Puede reabrir historias clínicas** (único nivel con este permiso)
- **Puede registrar usuarios de cualquier nivel**

### MÉDICO COORDINADOR (Nivel Máximo)
- **Puede crear y editar pacientes**
- **Puede cerrar historias clínicas**
- **Puede reabrir historias clínicas**
- **Puede registrar usuarios de cualquier nivel**
- **Tiene todos los permisos del administrador**

### 3. MÉDICO
- **Puede ver pacientes**
- **Puede crear pacientes**
- **Puede cerrar historias clínicas** (al registrar paciente)
- **NO puede editar pacientes**
- **NO puede reabrir historias clínicas**
- **Puede crear consultas médicas**

---

## 📝 CÓMO REGISTRAR USUARIOS SEGÚN SU NIVEL

### OPCIÓN 1: DIRECTAMENTE EN LA BASE DE DATOS

**Para registrar un Médico Coordinador:**
```sql
INSERT INTO users (name, email, password, role_id, email_verified_at, created_at, updated_at)
VALUES (
  'Nombre del Coordinador',
  'coordinador@ejemplo.com',
  '$2y$10$[contraseña_encriptada]',
  2, -- ID 2 = Médico Coordinador
  NOW(),
  NOW(),
  NOW()
);
```

**Para registrar un Médico:**
```sql
INSERT INTO users (name, email, password, role_id, email_verified_at, created_at, updated_at)
VALUES (
  'Nombre del Médico',
  'medico@ejemplo.com',
  '$2y$10$[contraseña_encriptada]',
  3, -- ID 3 = Médico
  NOW(),
  NOW(),
  NOW()
);
```

### OPCIÓN 2: USANDO TINKER (CONSOLA DE LARAVEL)

**Médico Coordinador:**
```bash
php artisan tinker
```
```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
    'name' => 'Dr. Juan Pérez',
    'email' => 'juan.perez@ejemplo.com',
    'password' => Hash::make('contraseña123'),
    'role_id' => 2, // Médico Coordinador
    'email_verified_at' => now(),
]);
```

**Médico:**
```bash
php artisan tinker
```
```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
    'name' => 'Dra. María González',
    'email' => 'maria.gonzalez@ejemplo.com',
    'password' => Hash::make('contraseña123'),
    'role_id' => 3, // Médico
    'email_verified_at' => now(),
]);
```

### OPCIÓN 3: ACTUALIZANDO EL ROLESEEDER (RECOMENDADO)

Edita el archivo `database/seeders/RoleSeeder.php` y agrega los usuarios:

```php
// Usuarios predeterminados
$users = [
    [
        'name' => 'Dr. Coordinador Principal',
        'email' => 'coordinador@ejemplo.com',
        'password' => bcrypt('contraseña_coordinador'),
        'role_id' => 2, // Médico Coordinador
    ],
    [
        'name' => 'Dra. María López',
        'email' => 'maria.lopez@ejemplo.com',
        'password' => bcrypt('contraseña_medico'),
        'role_id' => 3, // Médico
    ],
];

foreach ($users as $user) {
    $existing = \App\Models\User::where('email', $user['email'])->first();
    if (!$existing) {
        \App\Models\User::create([
            'name' => $user['name'],
            'email' => $user['email'],
            'password' => $user['password'],
            'role_id' => $user['role_id'],
            'email_verified_at' => now(),
        ]);
    }
}
```

Luego ejecuta el seeder:
```bash
php artisan db:seed --class=RoleSeeder
```

---

## 🎯 FLUJO DE TRABAJO POR ROL

### ADMINISTRADOR
1. **Login** con credenciales de administrador
2. **Ir a** `http://gestion-salud.test/patients`
3. Puede **crear, editar, cerrar y reabrir** historias clínicas
4. Puede **registrar nuevos usuarios** (médicos y coordinadores)

### MÉDICO COORDINADOR
1. **Login** con credenciales de médico coordinador
2. **Ir a** `http://gestion-salud.test/patients`
3. Puede **crear, editar y cerrar** historias clínicas
4. **NO puede reabrir** historias clínicas cerradas
5. **NO puede registrar usuarios**

### MÉDICO
1. **Login** con credenciales de médico
2. **Ir a** `http://gestion-salud.test/patients`
3. Puede **crear pacientes** y **cerrar** historias al registrar
4. **NO puede editar** pacientes
5. **NO puede reabrir** historias clínicas
6. Puede **crear consultas** para los pacientes

---

## 🔐 PERMISOS RESUMIDOS

| Acción | Administrador | Médico Coordinador | Médico |
|---------|---------------|--------------------|---------|
| Ver pacientes | ✅ | ✅ | ✅ |
| Crear pacientes | ✅ | ✅ | ✅ |
| Editar pacientes | ✅ | ✅ | ❌ |
| Cerrar historias | ✅ | ✅ | ✅ |
| Reabrir historias | ✅ | ✅ | ❌ |
| Crear consultas | ✅ | ✅ | ✅ |
| Registrar usuarios | ✅ | ✅ | ❌ |

---

## 📌 IMPORTANTE

- **Administrador y Médico Coordinador** tienen el nivel máximo de permisos
- Ambos pueden reabrir historias clínicas cerradas
- **Médicos** pueden cerrar historias al registrar pacientes (marcando el checkbox)
- **Médicos NO pueden reabrir** historias clínicas cerradas
- Las credenciales del administrador son: `levayesteban@gmail.com` / `Estebanmiguel*`

---

## 🚨 ADVERTENCIAS DE SEGURIDAD

- **Nunca compartas** las credenciales del administrador
- **Asigna contraseñas fuertes** a los médicos
- **Elimina** usuarios cuando ya no trabajen en la institución
- **Revisa periódicamente** los usuarios activos
