<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Patient;
use App\Models\MaritalStatus;
use App\Models\Ethnicity;
use App\Models\InstructionLevel;
use App\Models\Occupation;
use App\Models\Religion;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'id_number' => fake()->unique()->numerify('########'),
            'nationality' => fake()->randomElement(['V', 'E']),
            'gender' => fake()->randomElement(['M', 'F']),
            'birth_date' => fake()->date('Y-m-d', '2005-01-01'),
            'birth_place' => fake()->city(),
            'phone_number' => fake()->numerify('04##-#######'),
            'marital_status_id' => MaritalStatus::firstOrCreate(['name' => 'Soltero'])->id,
            'ethnicity_id' => Ethnicity::firstOrCreate(['name' => 'Mestizo', 'code' => 'M'])->id,
            'instruction_level_id' => InstructionLevel::firstOrCreate(['name' => 'Primaria', 'code' => '1'])->id,
            'occupation_id' => Occupation::firstOrCreate(['name' => 'Empleado'])->id,
            'religion_id' => Religion::firstOrCreate(['name' => 'Católico'])->id,
            'blood_type' => fake()->randomElement(['A', 'B', 'AB', 'O']),
            'rh_factor' => fake()->randomElement(['+', '-']),
            'addr_state' => fake()->state(),
            'addr_municipality' => fake()->city(),
            'addr_parish' => fake()->streetName(),
            'addr_sector' => fake()->streetName(),
            'addr_locality' => fake()->streetName(),
            'addr_street' => fake()->streetAddress(),
            'addr_house_number' => fake()->buildingNumber(),
        ];
    }
}
