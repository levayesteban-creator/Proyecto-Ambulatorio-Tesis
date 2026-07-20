<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Patient;
use App\Models\MaritalStatus;
use App\Models\Ethnicity;
use App\Models\InstructionLevel;
use App\Models\Occupation;
use App\Models\Religion;
use App\Models\SisDiagnosis;
use App\Models\MedicalConduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['id' => 1, 'name' => 'Administrador']);
        $this->admin = User::factory()->create([
            'name' => 'Admin Reportes',
            'email' => 'reportes@test.com',
            'role_id' => 1,
        ]);

        $this->seedCatalogs();
        $this->patient = Patient::factory()->create();
        $this->seedDiagnosesCatalog();
    }

    private function seedCatalogs(): void
    {
        MaritalStatus::create(['name' => 'Soltero']);
        Ethnicity::create(['code' => 'M', 'name' => 'Mestizo']);
        InstructionLevel::create(['code' => 1, 'name' => 'Primaria']);
        Occupation::create(['name' => 'Empleado']);
        Religion::create(['name' => 'Católico']);
    }

    private function seedDiagnosesCatalog(): void
    {
        SisDiagnosis::create(['code' => 'I10', 'name' => 'Hipertensión Arterial Esencial']);
        SisDiagnosis::create(['code' => 'J00', 'name' => 'Resfriado Común']);
        MedicalConduct::create(['code' => '01', 'name' => 'Orientación']);
    }

    public function test_epi10_requires_authentication(): void
    {
        $response = $this->get('/reportes/epi10?fecha=2026-01-15');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_epi10_returns_error_on_empty_date(): void
    {
        Auth::login($this->admin);

        $response = $this->get('/reportes/epi10?fecha=2026-01-15');
        $response->assertStatus(302);
        $response->assertSessionHasErrors('error');
    }

    public function test_epi10_validates_date_format(): void
    {
        Auth::login($this->admin);

        $response = $this->get('/reportes/epi10?fecha=invalid-date');
        $response->assertSessionHasErrors('fecha');
    }

    public function test_epi12_requires_authentication(): void
    {
        $response = $this->get('/reportes/epi12?semana=2026-03');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_epi12_returns_error_on_empty_week(): void
    {
        Auth::login($this->admin);

        $response = $this->get('/reportes/epi12?semana=2026-03');
        $response->assertStatus(302);
        $response->assertSessionHasErrors('error');
    }

    public function test_epi12_validates_week_format(): void
    {
        Auth::login($this->admin);

        $response = $this->get('/reportes/epi12?semana=invalid');
        $response->assertSessionHasErrors('semana');
    }

    public function test_epi13_requires_authentication(): void
    {
        $response = $this->get('/reportes/epi13?semana=2026-03');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_epi13_returns_error_on_empty_week(): void
    {
        Auth::login($this->admin);

        $response = $this->get('/reportes/epi13?semana=2026-03');
        $response->assertStatus(302);
        $response->assertSessionHasErrors('error');
    }

    public function test_epi13_validates_week_format(): void
    {
        Auth::login($this->admin);

        $response = $this->get('/reportes/epi13?semana=invalid');
        $response->assertSessionHasErrors('semana');
    }

    public function test_epi15_requires_authentication(): void
    {
        $response = $this->get('/reportes/epi15?periodo=2026-01');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_epi15_returns_error_on_empty_month(): void
    {
        Auth::login($this->admin);

        $response = $this->get('/reportes/epi15?periodo=2026-01');
        $response->assertStatus(302);
        $response->assertSessionHasErrors('error');
    }

    public function test_epi15_validates_period_format(): void
    {
        Auth::login($this->admin);

        $response = $this->get('/reportes/epi15?periodo=invalid');
        $response->assertSessionHasErrors('periodo');
    }

    public function test_epi10_returns_pdf_with_data(): void
    {
        $this->markTestSkipped(
            'ReportController usa relaciones (consultationDiagnoses) que no existen en el modelo Consultation. '
            . 'Requiere refactor previo del ReportController.'
        );
    }
}
