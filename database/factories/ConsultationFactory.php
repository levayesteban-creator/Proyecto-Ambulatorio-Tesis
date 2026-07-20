<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Consultation;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConsultationFactory extends Factory
{
    protected $model = Consultation::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'user_id' => User::factory(),
            'consultation_type' => fake()->randomElement(['P', 'S']),
            'is_healthy' => fake()->boolean(20),
            'reason_for_consultation' => fake()->sentence(),
            'current_illness' => fake()->paragraph(),
        ];
    }
}
