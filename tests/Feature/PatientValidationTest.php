<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Patient;

class PatientValidationTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_store_returns_spanish_validation_messages(): void
    {
        $response = $this->actingAs($this->user)->post(route('patients.store'), [
            'full_name' => '',
            'id_number' => '',
            'nationality' => '',
            'gender' => '',
            'birth_date' => '',
            'birth_place' => '',
            'marital_status_id' => '',
            'ethnicity_id' => '',
            'instruction_level_id' => '',
            'occupation_id' => '',
            'religion_id' => '',
            'knows_blood_type' => '',
            'addr_state' => '',
            'addr_municipality' => '',
            'addr_parish' => '',
            'addr_sector' => '',
            'background' => '',
            'family_background' => '',
            'habits' => '',
        ]);

        $response->assertSessionHasErrors([
            'full_name',
            'id_number',
            'nationality',
            'gender',
            'birth_date',
            'birth_place',
            'marital_status_id',
            'ethnicity_id',
            'instruction_level_id',
            'occupation_id',
            'religion_id',
            'knows_blood_type',
            'addr_state',
            'addr_municipality',
            'addr_parish',
            'addr_sector',
        ]);

        $errors = session('errors')->getBag('default')->toArray();

        $this->assertEquals('El nombre completo es obligatorio.', $errors['full_name'][0]);
        $this->assertEquals('El número de cédula es obligatorio.', $errors['id_number'][0]);
        $this->assertEquals('La nacionalidad es obligatoria.', $errors['nationality'][0]);
        $this->assertEquals('El género es obligatorio.', $errors['gender'][0]);
        $this->assertEquals('La fecha de nacimiento es obligatoria.', $errors['birth_date'][0]);
        $this->assertEquals('El lugar de nacimiento es obligatorio.', $errors['birth_place'][0]);
    }

    public function test_store_background_obgyn_required_if_messages(): void
    {
        $patient = Patient::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('patients.store'), [
            'full_name' => 'Test Patient',
            'id_number' => 'V12345678',
            'nationality' => 'V',
            'gender' => 'F',
            'birth_date' => '1990-01-15',
            'birth_place' => 'Caracas',
            'marital_status_id' => 1,
            'ethnicity_id' => 1,
            'instruction_level_id' => 1,
            'occupation_id' => 1,
            'religion_id' => 1,
            'knows_blood_type' => false,
            'addr_state' => 'Miranda',
            'addr_municipality' => 'Sucre',
            'addr_parish' => 'Petare',
            'addr_sector' => 'Centro',
            'background' => [
                'allergies_deny' => true,
                'pathological_deny' => true,
                'infectious_deny' => true,
                'immune_deny_vaccination' => true,
                'immune_childhood_status' => 'completa',
                'transfusion_deny' => true,
                'obgyn_apply' => true,
                'surgical_deny' => true,
                'traumatic_deny' => true,
                'std_deny' => true,
                'epidemiological_deny' => true,
                'disability_deny' => true,
            ],
            'family_background' => [
                'mother' => ['unknown' => true],
                'father' => ['unknown' => true],
                'grandmother_maternal' => ['unknown' => true],
                'grandfather_maternal' => ['unknown' => true],
                'grandmother_paternal' => ['unknown' => true],
                'grandfather_paternal' => ['unknown' => true],
                'siblings' => ['unknown' => true],
                'children' => ['unknown' => true],
            ],
            'habits' => [
                'alcohol' => ['deny' => true],
                'tobacco' => ['deny' => true],
                'coffee' => ['deny' => true],
                'drugs' => ['deny' => true],
                'nutrition' => ['type' => 'balanceada'],
                'sleep' => ['type' => 'nocturno', 'hours' => 8],
                'sexual_habits' => ['active' => false],
                'housing' => [
                    'floor_material' => 'cemento',
                    'roof_material' => 'acero',
                    'walls_material' => 'bloque',
                    'rooms_count' => 3,
                    'habitants_count' => 4,
                    'services' => ['water' => true, 'electricity' => true, 'gas' => true, 'waste_collection' => true],
                ],
            ],
        ]);

        $errors = session('errors')?->getBag('default')?->toArray() ?? [];

        $expectedObgynErrors = [
            'background.obgyn_gestas',
            'background.obgyn_partos',
            'background.obgyn_cesareas',
            'background.obgyn_abortos',
            'background.obgyn_menarche',
            'background.obgyn_cycle_periodicity',
            'background.obgyn_cycle_duration',
            'background.obgyn_cycle_pads_per_day',
            'background.obgyn_fur',
        ];

        foreach ($expectedObgynErrors as $field) {
            $this->assertArrayHasKey($field, $errors, "Missing error for: $field");
            $this->assertStringNotContainsString('background.obgyn', $errors[$field][0], "Message for $field contains raw field name");
        }
    }
}
