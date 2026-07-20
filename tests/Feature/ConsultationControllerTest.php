<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Patient;
use App\Models\Consultation;
use App\Models\SisDiagnosis;
use App\Models\MedicalConduct;
use App\Models\MaritalStatus;
use App\Models\Ethnicity;
use App\Models\InstructionLevel;
use App\Models\Occupation;
use App\Models\Religion;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ConsultationControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $doctor;
    private User $admin;
    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['id' => 1, 'name' => 'Administrador']);
        Role::create(['id' => 2, 'name' => 'Médico Coordinador']);
        Role::create(['id' => 3, 'name' => 'Médico']);

        $this->admin = User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'role_id' => 1,
        ]);

        $this->doctor = User::factory()->create([
            'name' => 'Dr. Test',
            'email' => 'doctor@test.com',
            'role_id' => 3,
        ]);

        $this->seedCatalogs();

        $this->patient = Patient::factory()->create([
            'full_name' => 'Paciente Test',
            'id_number' => '12345678',
            'birth_date' => '1990-01-15',
            'marital_status_id' => MaritalStatus::first()->id,
            'ethnicity_id' => Ethnicity::first()->id,
            'instruction_level_id' => InstructionLevel::first()->id,
            'occupation_id' => Occupation::first()->id,
            'religion_id' => Religion::first()->id,
        ]);

        $this->seedDiagnoses();
    }

    private function seedCatalogs(): void
    {
        MaritalStatus::create(['name' => 'Soltero']);
        Ethnicity::create(['code' => 'M', 'name' => 'Mestizo']);
        InstructionLevel::create(['code' => 1, 'name' => 'Primaria']);
        Occupation::create(['name' => 'Empleado']);
        Religion::create(['name' => 'Católico']);
    }

    private function seedDiagnoses(): void
    {
        SisDiagnosis::create(['code' => 'I10', 'name' => 'Hipertensión Arterial Esencial']);
        SisDiagnosis::create(['code' => 'J00', 'name' => 'Resfriado Común']);
        MedicalConduct::create(['code' => '01', 'name' => 'Orientación sobre horarios de comidas']);
    }

    private function validConsultationData(): array
    {
        return [
            'patient_id' => $this->patient->id,
            'consultation_type' => 'P',
            'is_healthy' => false,
            'reason_for_consultation' => 'Paciente refiere cefalea persistente',
            'current_illness' => 'Cefalea frontal de 3 días de evolución',
            'treatment_plan' => 'Paracetamol 500mg cada 8 horas por 3 días',
            'blood_pressure' => '120/80',
            'temperature' => 37.5,
            'temperature_route' => 'Oral',
            'heart_rate' => 72,
            'respiratory_rate' => 16,
            'oxygen_saturation' => 98.0,
            'weight' => 70.0,
            'height' => 1.75,
            'edit_justification' => 'Corrección de error de tipeo en diagnóstico',
            'functional_exam' => [
                'general_deny' => false,
                'general_description' => 'Astenia leve',
                'skin_deny' => true,
                'skin_description' => null,
                'head_face_deny' => false,
                'head_face_description' => 'Cefalea frontal',
                'neck_throat_deny' => true,
                'neck_throat_description' => null,
                'eyes_deny' => true,
                'eyes_description' => null,
                'mouth_deny' => true,
                'mouth_description' => null,
                'breasts_deny' => true,
                'breasts_description' => null,
                'ears_deny' => true,
                'ears_description' => null,
                'nose_deny' => true,
                'nose_description' => null,
                'respiratory_deny' => true,
                'respiratory_description' => null,
                'cardiovascular_deny' => true,
                'cardiovascular_description' => null,
                'gastrointestinal_deny' => true,
                'gastrointestinal_description' => null,
                'genitourinary_deny' => true,
                'genitourinary_description' => null,
                'menstrual_cycle_deny' => true,
                'menstrual_cycle_description' => null,
                'nervous_mental_deny' => false,
                'nervous_mental_description' => 'Ansiedad leve referida',
                'osteomuscular_deny' => true,
                'osteomuscular_description' => null,
            ],
            'physical_exam' => [
                'general_data' => ['skin_color' => 'normal', 'hydration' => 'adecuada'],
                'cardiovascular' => ['rhythm' => 'regular', 'murmurs' => 'no'],
                'abdomen' => ['soft' => true, 'painful' => false],
            ],
            'diagnoses' => [
                [
                    'sis_diagnosis_id' => 1,
                    'diagnosis_type' => 'Probable',
                    'sort_order' => 1,
                    'medical_conduct_id' => 1,
                ],
            ],
            'referrals' => [
                [
                    'specialty_code' => 5,
                    'type' => 'referral',
                ],
            ],
        ];
    }

    public function test_create_form_renders_successfully(): void
    {
        Auth::login($this->doctor);

        $response = $this->get("/patients/{$this->patient->id}/consultations/create");

        $response->assertStatus(200);
    }

    public function test_store_consultation_creates_record(): void
    {
        Auth::login($this->doctor);

        $response = $this->post(
            "/patients/{$this->patient->id}/consultations",
            $this->validConsultationData()
        );

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('consultations', [
            'patient_id' => $this->patient->id,
            'user_id' => $this->doctor->id,
            'consultation_type' => 'P',
            'blood_pressure' => '120/80',
        ]);

        $consultation = Consultation::where('patient_id', $this->patient->id)->first();
        $this->assertNotNull($consultation);

        $this->assertDatabaseHas('consultation_functional_exams', [
            'consultation_id' => $consultation->id,
            'general_deny' => false,
            'cardiovascular_deny' => true,
        ]);

        $this->assertDatabaseHas('consultation_sis_diagnosis', [
            'consultation_id' => $consultation->id,
            'sis_diagnosis_id' => 1,
            'diagnosis_type' => 'Probable',
        ]);

        $this->assertDatabaseHas('consultation_referrals', [
            'consultation_id' => $consultation->id,
            'specialty_code' => 5,
        ]);
    }

    public function test_store_consultation_for_healthy_patient(): void
    {
        Auth::login($this->doctor);

        $data = $this->validConsultationData();
        $data['is_healthy'] = true;
        $data['reason_for_consultation'] = null;
        $data['current_illness'] = null;

        $response = $this->post(
            "/patients/{$this->patient->id}/consultations",
            $data
        );

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('consultations', [
            'patient_id' => $this->patient->id,
            'is_healthy' => true,
        ]);
    }

    public function test_store_consultation_requires_at_least_one_diagnosis(): void
    {
        Auth::login($this->doctor);

        $data = $this->validConsultationData();
        $data['diagnoses'] = [];

        $response = $this->post(
            "/patients/{$this->patient->id}/consultations",
            $data
        );

        $response->assertSessionHasErrors('diagnoses');
    }

    public function test_store_consultation_requires_functional_exam(): void
    {
        Auth::login($this->doctor);

        $data = $this->validConsultationData();
        unset($data['functional_exam']);

        $response = $this->post(
            "/patients/{$this->patient->id}/consultations",
            $data
        );

        $response->assertSessionHasErrors('functional_exam');
    }

    public function test_show_consultation_detail(): void
    {
        Auth::login($this->doctor);

        $consultation = $this->createConsultation();

        $response = $this->get("/consultations/detail/{$consultation->id}");

        $response->assertStatus(200);
    }

    public function test_show_consultation_history(): void
    {
        Auth::login($this->doctor);

        $this->createConsultation();
        $this->createConsultation();

        $response = $this->get("/patients/{$this->patient->id}/consultations/history");

        $response->assertStatus(200);
    }

    public function test_edit_consultation_form_loads(): void
    {
        Auth::login($this->doctor);

        $consultation = $this->createConsultation();

        $response = $this->get(
            "/patients/{$this->patient->id}/consultations/{$consultation->id}/edit"
        );

        $response->assertStatus(200);
    }

    public function test_update_consultation(): void
    {
        Auth::login($this->doctor);

        $consultation = $this->createConsultation();

        $updateData = $this->validConsultationData();
        $updateData['reason_for_consultation'] = 'Motivo actualizado';
        $updateData['heart_rate'] = 80;

        $response = $this->put(
            "/patients/{$this->patient->id}/consultations/{$consultation->id}",
            $updateData
        );

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('consultations', [
            'id' => $consultation->id,
            'heart_rate' => 80,
        ]);
    }

    public function test_destroy_consultation_soft_deletes(): void
    {
        $consultation = $this->createConsultation();

        Auth::login($this->admin);

        $response = $this->delete(
            "/patients/{$this->patient->id}/consultations/{$consultation->id}"
        );

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertSoftDeleted($consultation);
    }

    public function test_store_validates_vital_signs_ranges(): void
    {
        Auth::login($this->doctor);

        $data = $this->validConsultationData();
        $data['heart_rate'] = 500;
        $data['temperature'] = 50;

        $response = $this->post(
            "/patients/{$this->patient->id}/consultations",
            $data
        );

        $response->assertSessionHasErrors(['heart_rate', 'temperature']);
    }

    public function test_store_validates_blood_pressure_format(): void
    {
        Auth::login($this->doctor);

        $data = $this->validConsultationData();
        $data['blood_pressure'] = 'invalid';

        $response = $this->post(
            "/patients/{$this->patient->id}/consultations",
            $data
        );

        $response->assertSessionHasErrors('blood_pressure');
    }

    private function createConsultation(): Consultation
    {
        Auth::login($this->doctor);

        $this->post(
            "/patients/{$this->patient->id}/consultations",
            $this->validConsultationData()
        );

        return Consultation::where('patient_id', $this->patient->id)->first();
    }
}
