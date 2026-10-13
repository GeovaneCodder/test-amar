<?php

namespace Tests\Feature\Transactions;

use App\Infrastructure\Persistence\Eloquent\Models\Transaction;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_transaction_with_exact_amount(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/transactions', [
            'date' => '2026-10-13',
            'description' => 'Pagamento de cliente',
            'amount' => '125.75',
            'type' => 'Receita',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.amount', '125.75')
            ->assertJsonPath('data.type', 'income');

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount_minor' => 12575,
            'type' => 'income',
        ]);
    }

    public function test_expense_cannot_be_recorded_with_a_negative_amount(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')->postJson('/api/transactions', [
            'date' => '2026-10-13',
            'description' => 'Conta de energia',
            'amount' => '-20.00',
            'type' => 'Despesa',
        ])->assertUnprocessable()->assertJsonValidationErrors('amount');

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_transaction_list_is_paginated_and_scoped_to_authenticated_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        Transaction::create([
            'user_id' => $user->id,
            'transaction_date' => '2026-10-12',
            'description' => 'Minha transação',
            'amount_minor' => 5000,
            'type' => 'income',
        ]);
        Transaction::create([
            'user_id' => $otherUser->id,
            'transaction_date' => '2026-10-12',
            'description' => 'Transação privada',
            'amount_minor' => 9000,
            'type' => 'income',
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/transactions?per_page=1')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.description', 'Minha transação');
    }
}
