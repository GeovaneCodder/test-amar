<?php

namespace Tests\Feature\Auth;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Testing\TestResponse;
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

    public function test_api_returns_json_for_unauthenticated_requests_without_an_accept_header(): void
    {
        $this->get('/api/session/user')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_logout_returns_an_english_confirmation_message(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Session ended.');
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'incorrect-password',
        ])->assertUnauthorized()->assertJsonPath('message', 'The provided credentials are invalid.');
    }

    public function test_session_registration_uses_an_http_only_cookie_without_issuing_an_api_token(): void
    {
        $response = $this->withHeaders(['Origin' => 'http://localhost'])
            ->postJson('/api/session/register', [
                'name' => 'Ana Costa',
                'email' => 'ana@example.com',
                'password' => 'password-123',
                'password_confirmation' => 'password-123',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.user.email', 'ana@example.com')
            ->assertJsonMissingPath('data.token');
        $this->assertAuthenticatedAs(User::where('email', 'ana@example.com')->firstOrFail(), 'web');
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertSessionCookieIsHttpOnly($response);

        $this->withHeaders(['Origin' => 'http://localhost'])
            ->getJson('/api/session/user')
            ->assertOk()
            ->assertJsonPath('data.user.email', 'ana@example.com');
    }

    public function test_session_login_does_not_issue_or_return_a_persisted_api_token(): void
    {
        $user = User::factory()->create(['password' => 'password-123']);

        $response = $this->withHeaders(['Origin' => 'http://localhost'])
            ->postJson('/api/session/login', [
                'email' => $user->email,
                'password' => 'password-123',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonMissingPath('data.token');
        $this->assertAuthenticatedAs($user, 'web');
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertSessionCookieIsHttpOnly($response);
    }

    public function test_session_logout_invalidates_the_server_session(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web');

        $this->withHeaders(['Origin' => 'http://localhost'])
            ->postJson('/api/session/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Session ended.');

        $this->assertGuest('web');
    }

    private function assertSessionCookieIsHttpOnly(TestResponse $response): void
    {
        $cookie = collect($response->headers->getCookies())
            ->first(fn ($cookie) => $cookie->getName() === config('session.cookie'));

        $this->assertNotNull($cookie);
        $this->assertTrue($cookie->isHttpOnly());
    }
}
