<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Patient;
use App\Models\Role;
use App\Models\MaritalStatus;
use App\Models\Ethnicity;
use App\Models\InstructionLevel;
use App\Models\Occupation;
use App\Models\Religion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PatientControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $doctor;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create(['id' => 1, 'name' => 'Administrador']);
        Role::create(['id' => 2, 'name' => 'Médico Coordinador']);
        Role::create(['id' => 3, 'name' => 'Médico']);

        $this->admin = User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'role_id' => $adminRole->id,
        ]);

        $this->doctor = User::factory()->create([
            'name' => 'Doctor Test',
            'email' => 'doctor@test.com',
            'role_id' => 3,
        ]);
    }

    public function test_index_patients_returns_200(): void
    {
        Patient::factory()->count(3)->create();
        Auth::login($this->doctor);

        $response = $this->get('/patients');
        $response->assertStatus(200);
    }

    public function test_search_patients_by_name(): void
    {
        Patient::factory()->create(['full_name' => 'Juan Pérez', 'id_number' => '12345678']);
        Patient::factory()->create(['full_name' => 'María González', 'id_number' => '87654321']);

        Auth::login($this->doctor);

        $response = $this->get('/patients?search=Juan');
        $response->assertStatus(200);

        $response = $this->get('/patients?search=87654321');
        $response->assertStatus(200);
    }

    public function test_create_patient_successfully(): void
    {
        $this->seedCatalogs();
        Auth::login($this->doctor);

        $response = $this->post('/patients', $this->validPatientData());
        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('patients', [
            'full_name' => 'Carlos Rodríguez',
            'id_number' => '987654321',
        ]);
    }

    public function test_validation_full_name_required(): void
    {
        $this->seedCatalogs();
        Auth::login($this->doctor);

        $data = $this->validPatientData();
        $data['full_name'] = '';

        $response = $this->post('/patients', $data);
        $response->assertSessionHasErrors('full_name');
    }

    public function test_duplicate_id_number_rejected(): void
    {
        $this->seedCatalogs();
        Patient::factory()->create(['id_number' => '12345678']);

        Auth::login($this->doctor);
        $data = $this->validPatientData();
        $data['id_number'] = '12345678';

        $response = $this->post('/patients', $data);
        $response->assertSessionHasErrors('id_number');
    }

    public function test_future_birth_date_rejected(): void
    {
        $this->seedCatalogs();
        Auth::login($this->doctor);

        $data = $this->validPatientData();
        $data['birth_date'] = now()->addDay()->format('Y-m-d');

        $response = $this->post('/patients', $data);
        $response->assertSessionHasErrors('birth_date');
    }

    public function test_menarca_validation_between_9_and_16_years(): void
    {
        $this->seedCatalogs();
        Auth::login($this->doctor);

        $data = $this->validPatientData();
        $data['gender'] = 'F';
        $data['birth_date'] = '2010-05-15';
        $data['background']['obgyn_apply'] = true;
        $data['background']['obgyn_menarche'] = '2018-05-15';
        $data['background']['obgyn_gestas'] = '0';
        $data['background']['obgyn_partos'] = '0';
        $data['background']['obgyn_cesareas'] = '0';
        $data['background']['obgyn_abortos'] = '0';
        $data['background']['obgyn_cycle_periodicity'] = 'regular';
        $data['background']['obgyn_cycle_duration'] = '5';
        $data['background']['obgyn_cycle_pads_per_day'] = 3;
        $data['background']['obgyn_fur'] = '2025-06-01';

        $response = $this->post('/patients', $data);
        $response->assertSessionHasErrors('clinical_validation_error');
    }

    public function test_soft_delete_patient(): void
    {
        Auth::login($this->admin);
        $patient = Patient::factory()->create(['full_name' => 'Paciente a Eliminar']);

        $response = $this->delete("/patients/{$patient->id}");
        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertSoftDeleted($patient);
    }

    public function test_restore_patient(): void
    {
        Auth::login($this->admin);
        $patient = Patient::factory()->create(['full_name' => 'Paciente a Restaurar']);
        $patient->delete();

        $response = $this->post("/patients/{$patient->id}/restore");
        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertNotSoftDeleted($patient);
    }

    public function test_trashed_view_shows_deleted_patients(): void
    {
        Auth::login($this->admin);

        $deleted = Patient::factory()->create(['full_name' => 'Eliminado']);
        $deleted->delete();

        Patient::factory()->create(['full_name' => 'Activo']);

        $response = $this->get('/patients/trashed');
        $response->assertStatus(200);
    }

    public function test_show_patient(): void
    {
        Auth::login($this->doctor);
        $patient = Patient::factory()->create(['full_name' => 'Paciente Vista']);

        $response = $this->get("/patients/{$patient->id}");
        $response->assertStatus(200);
    }

    public function test_doctor_cannot_edit_patient(): void
    {
        Auth::login($this->doctor);
        $patient = Patient::factory()->create();

        $response = $this->get("/patients/{$patient->id}/edit");
        $response->assertStatus(200);
        $response->assertSessionMissing('success');
    }

    private function seedCatalogs(): void
    {
        MaritalStatus::create(['name' => 'Soltero']);
        Ethnicity::create(['code' => 'M', 'name' => 'Mestizo']);
        InstructionLevel::create(['code' => 1, 'name' => 'Primaria']);
        Occupation::create(['name' => 'Empleado']);
        Religion::create(['name' => 'Católico']);
    }

    private function validPatientData(): array
    {
        return [
            'full_name' => 'Carlos Rodríguez',
            'id_number' => '987654321',
            'nationality' => 'V',
            'gender' => 'M',
            'birth_date' => '1990-05-15',
            'birth_place' => 'Caracas',
            'marital_status_id' => 1,
            'ethnicity_id' => 1,
            'instruction_level_id' => 1,
            'occupation_id' => 1,
            'religion_id' => 1,
            'knows_blood_type' => true,
            'blood_type' => 'A',
            'rh_factor' => '+',
            'phone_number' => '0414-1234567',
            'addr_state' => 'Distrito Capital',
            'addr_municipality' => 'Libertador',
            'addr_parish' => 'El Recreo',
            'addr_sector' => 'La Urbina',
            'addr_locality' => 'La Urbina',
            'addr_street' => 'Calle Principal',
            'addr_house_number' => '123',
            'background' => [
                'allergies_deny' => true,
                'pathological_deny' => true,
                'infectious_deny' => true,
                'immune_deny_vaccination' => true,
                'immune_childhood_status' => 'completa',
                'transfusion_deny' => true,
                'surgical_deny' => true,
                'traumatic_deny' => true,
                'std_deny' => true,
                'epidemiological_deny' => true,
                'disability_deny' => true,
                'obgyn_apply' => false,
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
                'sleep' => ['type' => 'nocturno', 'hours' => 8, 'interrupted' => false],
                'sexual_habits' => ['active' => false],
                'nutrition' => ['type' => 'balanceada'],
                'housing' => [
                    'floor_material' => 'Cemento',
                    'roof_material' => 'Zinc',
                    'walls_material' => 'Bloque',
                    'rooms_count' => 3,
                    'habitants_count' => 4,
                    'services' => [
                        'water' => true,
                        'electricity' => true,
                        'gas' => false,
                        'waste_collection' => true,
                    ],
                ],
            ],
        ];
    }
}
