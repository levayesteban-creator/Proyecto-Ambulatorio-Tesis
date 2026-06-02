<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Esteban Rojas',
            'email' => 'levayesteban@gmail.com',
            'password' => Hash::make('admin123'), // Hash::make es la forma recomendada en versiones recientes
            'role_id' => 1, // 1 para Admin/Médico
        ]);
    }
}
