<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Crea usuarios de ejemplo para desarrollo.
     * La cuenta admin principal se crea en RoleSeeder.
     */
    public function run(): void
    {
        // Solo crear si no existe (idempotente)
        $users = [
            [
                'name' => 'María García',
                'email' => 'maria@ejemplo.com',
                'id_number' => '20123456',
                'phone' => '0414-1234567',
                'role_id' => 3, // Médico
                'must_change_password' => true,
            ],
            [
                'name' => 'Ana López',
                'email' => 'ana@ejemplo.com',
                'id_number' => '20987654',
                'phone' => '0424-7654321',
                'role_id' => 4, // Enfermero
                'must_change_password' => true,
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ])
            );
        }
    }
}
