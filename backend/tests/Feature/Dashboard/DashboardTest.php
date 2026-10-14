<?php

namespace Tests\Feature\Dashboard;

use App\Infrastructure\Persistence\Eloquent\Models\Import;
use App\Infrastructure\Persistence\Eloquent\Models\Transaction;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Jobs\ProcessImportChunkJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
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

    public function test_dashboard_totals_only_include_transactions_from_the_latest_completed_csv(): void
    {
        $user = User::factory()->create();
        $olderImport = Import::create([
            'user_id' => $user->id,
            'file_path' => 'imports/older-dashboard.csv',
            'fingerprint' => hash('sha256', 'older-dashboard'),
            'status' => 'completed',
        ]);
        $latestImport = Import::create([
            'user_id' => $user->id,
            'file_path' => 'imports/latest-dashboard.csv',
            'fingerprint' => hash('sha256', 'latest-dashboard'),
            'status' => 'completed_with_errors',
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'import_id' => $olderImport->id,
            'transaction_date' => '2026-10-01',
            'description' => 'Receita antiga',
            'amount_minor' => 100000,
            'type' => 'income',
        ]);
        Transaction::create([
            'user_id' => $user->id,
            'transaction_date' => '2026-10-02',
            'description' => 'Lançamento manual antigo',
            'amount_minor' => 50000,
            'type' => 'income',
        ]);
        Transaction::create([
            'user_id' => $user->id,
            'import_id' => $latestImport->id,
            'transaction_date' => '2026-10-03',
            'description' => 'Receita atual',
            'amount_minor' => 25000,
            'type' => 'income',
        ]);
        Transaction::create([
            'user_id' => $user->id,
            'import_id' => $latestImport->id,
            'transaction_date' => '2026-10-04',
            'description' => 'Despesa atual',
            'amount_minor' => 5000,
            'type' => 'expense',
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/dashboard')
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    'income' => '250.00',
                    'expenses' => '50.00',
                    'balance' => '200.00',
                ],
            ]);
    }

    public function test_empty_successful_import_replaces_the_cached_dashboard_dataset(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        Transaction::create([
            'user_id' => $user->id,
            'transaction_date' => '2026-10-01',
            'description' => 'Lançamento manual antigo',
            'amount_minor' => 99900,
            'type' => 'income',
        ]);
        $this->actingAs($user, 'sanctum');
        $this->getJson('/api/dashboard')->assertJsonPath('data.income', '999.00');

        $import = Import::create([
            'user_id' => $user->id,
            'file_path' => 'imports/empty.csv',
            'fingerprint' => hash('sha256', 'empty'),
            'status' => 'pending',
        ]);
        Storage::disk('local')->put($import->file_path, "date,description,amount,type\n");

        app()->call([new ProcessImportChunkJob($import->id), 'handle']);

        $import->refresh();
        $this->assertSame('completed', $import->status);
        $this->getJson('/api/dashboard')->assertExactJson([
            'data' => [
                'income' => '0.00',
                'expenses' => '0.00',
                'balance' => '0.00',
            ],
        ]);
        $this->assertDatabaseCount('transactions', 1);
    }
}
