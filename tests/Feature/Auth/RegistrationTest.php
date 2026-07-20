<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['id' => 1, 'name' => 'Administrador']);
        Role::create(['id' => 2, 'name' => 'Médico Coordinador']);
        Role::create(['id' => 3, 'name' => 'Médico']);
    }

    public function test_registration_screen_redirects_to_login(): void
    {
        $response = $this->get('/register');

        $response->assertRedirect(route('login'));
    }

    public function test_new_users_cannot_register_publicly(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(405);
    }
}
