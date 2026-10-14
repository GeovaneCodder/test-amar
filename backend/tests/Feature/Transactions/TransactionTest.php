<?php

namespace Tests\Feature\Transactions;

use App\Infrastructure\Persistence\Eloquent\Models\Import;
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

    public function test_transaction_list_only_shows_transactions_from_the_latest_completed_csv_import(): void
    {
        $user = User::factory()->create();
        $olderImport = Import::create([
            'user_id' => $user->id,
            'file_path' => 'imports/older.csv',
            'fingerprint' => hash('sha256', 'older'),
            'status' => 'completed',
        ]);
        $latestImport = Import::create([
            'user_id' => $user->id,
            'file_path' => 'imports/latest.csv',
            'fingerprint' => hash('sha256', 'latest'),
            'status' => 'completed',
        ]);
        $pendingImport = Import::create([
            'user_id' => $user->id,
            'file_path' => 'imports/pending.csv',
            'fingerprint' => hash('sha256', 'pending'),
            'status' => 'processing',
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'import_id' => $olderImport->id,
            'transaction_date' => '2026-10-10',
            'description' => 'CSV anterior',
            'amount_minor' => 10000,
            'type' => 'income',
        ]);
        Transaction::create([
            'user_id' => $user->id,
            'import_id' => null,
            'transaction_date' => '2026-10-11',
            'description' => 'Lançamento manual antigo',
            'amount_minor' => 20000,
            'type' => 'income',
        ]);
        Transaction::create([
            'user_id' => $user->id,
            'import_id' => $latestImport->id,
            'transaction_date' => '2026-10-12',
            'description' => 'CSV atual',
            'amount_minor' => 30000,
            'type' => 'income',
        ]);
        Transaction::create([
            'user_id' => $user->id,
            'import_id' => $pendingImport->id,
            'transaction_date' => '2026-10-13',
            'description' => 'CSV ainda processando',
            'amount_minor' => 40000,
            'type' => 'income',
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/transactions')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.description', 'CSV atual');

        $this->assertDatabaseCount('transactions', 4);
    }
}
