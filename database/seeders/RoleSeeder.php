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
        Role::create(['id' => 2, 'name' => 'Médico']);
    }
}
