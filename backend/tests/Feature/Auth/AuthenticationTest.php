<?php

namespace Tests\Feature\Auth;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receives_an_api_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Maria Silva',
            'email' => 'maria@example.com',
            'password' => 'password-123',
            'password_confirmation' => 'password-123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.user.email', 'maria@example.com')
            ->assertJsonStructure(['data' => ['user' => ['id', 'name', 'email'], 'token']]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => User::where('email', 'maria@example.com')->value('id'),
        ]);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create(['password' => 'password-123']);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password-123',
        ])
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonStructure(['data' => ['token']]);
    }

    public function test_protected_api_endpoints_reject_guest_requests(): void
    {
        $this->getJson('/api/dashboard')->assertUnauthorized();
        $this->getJson('/api/transactions')->assertUnauthorized();
        $this->postJson('/api/imports')->assertUnauthorized();
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'incorrect-password',
        ])->assertUnauthorized()->assertJsonPath('message', 'As credenciais informadas são inválidas.');
    }
}
