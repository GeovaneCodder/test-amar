<?php

namespace Tests\Feature\Dashboard;

use App\Infrastructure\Persistence\Eloquent\Models\Transaction;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_sums_income_and_expenses_separately_and_subtracts_expenses_for_balance(): void
    {
        $user = User::factory()->create();
        Transaction::create([
            'user_id' => $user->id,
            'transaction_date' => '2026-10-01',
            'description' => 'Receita',
            'amount_minor' => 100005,
            'type' => 'income',
        ]);
        Transaction::create([
            'user_id' => $user->id,
            'transaction_date' => '2026-10-02',
            'description' => 'Despesa',
            'amount_minor' => 25025,
            'type' => 'expense',
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/dashboard')
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    'income' => '1000.05',
                    'expenses' => '250.25',
                    'balance' => '749.80',
                ],
            ]);
    }

    public function test_dashboard_cache_is_invalidated_when_a_transaction_is_added(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $this->getJson('/api/dashboard')->assertJsonPath('data.income', '0.00');
        $this->postJson('/api/transactions', [
            'date' => '2026-10-13',
            'description' => 'Nova receita',
            'amount' => '50.25',
            'type' => 'income',
        ])->assertCreated();

        $this->getJson('/api/dashboard')->assertJsonPath('data.income', '50.25');
    }
}
