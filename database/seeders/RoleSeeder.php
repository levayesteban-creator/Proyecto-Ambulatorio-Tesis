<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Desactivamos las llaves foráneas un segundo para limpiar y resetear IDs
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Role::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Creamos los roles con IDs fijos
        Role::create(['id' => 1, 'name' => 'Administrador']);
        Role::create(['id' => 2, 'name' => 'Médico Coordinador']);
        Role::create(['id' => 3, 'name' => 'Médico']);
        Role::create(['id' => 4, 'name' => 'Enfermero']);

        // Creamos el usuario administrador predeterminado desde .env
        $adminEmail = env('ADMIN_EMAIL', 'admin@ejemplo.com');
        $adminPassword = env('ADMIN_PASSWORD', 'cambiar123');
        $adminName = env('ADMIN_NAME', 'Administrador');

        $admin = \App\Models\User::where('email', $adminEmail)->first();
        if (!$admin) {
            \App\Models\User::create([
                'name' => $adminName,
                'email' => $adminEmail,
                'password' => bcrypt($adminPassword),
                'role_id' => 1, // Administrador
                'email_verified_at' => now(),
            ]);
        }

        // Asignar cédula al admin si no tiene
        if ($admin && !$admin->id_number) {
            $admin->update(['id_number' => '12345678', 'phone' => '0412-0000000']);
        }
    }
}
