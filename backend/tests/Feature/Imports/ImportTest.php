<?php

namespace Tests\Feature\Imports;

use App\Infrastructure\Persistence\Eloquent\Models\Import;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Jobs\ProcessImportChunkJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_dispatches_csv_import_to_the_queue(): void
    {
        Queue::fake();
        Storage::fake('local');
        $user = User::factory()->create();
        $csv = "date,description,amount,type\n2026-05-01,Mensalidade,115346,Receita\n";

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/imports', [
            'file' => UploadedFile::fake()->createWithContent('financial_transactions.csv', $csv),
        ]);

        $response->assertStatus(202)->assertJsonPath('data.status', 'pending');
        $importId = $response->json('data.id');

        $this->assertDatabaseHas('imports', [
            'id' => $importId,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
        Queue::assertPushed(ProcessImportChunkJob::class, fn (ProcessImportChunkJob $job): bool => $job->importId === $importId);
    }

    public function test_job_imports_revenue_and_expense_and_reports_invalid_rows(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $import = Import::create([
            'user_id' => $user->id,
            'file_path' => 'imports/test.csv',
            'fingerprint' => hash('sha256', 'imports/test.csv'),
            'status' => 'pending',
        ]);
        Storage::disk('local')->put(
            $import->file_path,
            "date,description,amount,type\n".
            "2026-05-01,Mensalidade Cliente B,115346,Receita\n".
            "2025-11-15,Impostos e Taxas,433209,Despesa\n".
            "not-a-date,Linha inválida,100,Receita\n"
        );

        app()->call([new ProcessImportChunkJob($import->id), 'handle']);

        $import->refresh();
        $this->assertSame('completed_with_errors', $import->status);
        $this->assertSame(3, $import->total_rows);
        $this->assertSame(2, $import->imported_rows);
        $this->assertSame(1, $import->failed_rows);
        $this->assertSame(11534600, $user->transactions()->where('type', 'income')->value('amount_minor'));
        $this->assertSame(43320900, $user->transactions()->where('type', 'expense')->value('amount_minor'));
        $this->assertSame(4, $import->errors[0]['row']);
        $this->assertSame('The date must use the YYYY-MM-DD format.', $import->errors[0]['message']);

        app()->call([new ProcessImportChunkJob($import->id), 'handle']);

        $this->assertSame(2, $user->transactions()->count());
    }

    public function test_import_status_cannot_be_read_by_another_user(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $import = Import::create([
            'user_id' => $owner->id,
            'file_path' => 'imports/private.csv',
            'fingerprint' => hash('sha256', 'imports/private.csv'),
            'status' => 'pending',
        ]);

        $this->actingAs($otherUser, 'sanctum')
            ->getJson("/api/imports/{$import->id}")
            ->assertNotFound();
    }

    public function test_csv_with_an_invalid_header_is_marked_failed_with_a_reason(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $import = Import::create([
            'user_id' => $user->id,
            'file_path' => 'imports/bad-header.csv',
            'fingerprint' => hash('sha256', 'imports/bad-header.csv'),
            'status' => 'pending',
        ]);
        Storage::disk('local')->put($import->file_path, "day,memo,value,kind\n");

        app()->call([new ProcessImportChunkJob($import->id), 'handle']);

        $import->refresh();
        $this->assertSame('failed', $import->status);
        $this->assertStringContainsString('header', $import->errors[0]['message']);
    }

    public function test_import_state_transitions_return_english_messages(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $import = Import::create([
            'user_id' => $user->id,
            'file_path' => 'imports/transitions.csv',
            'fingerprint' => hash('sha256', 'imports/transitions.csv'),
            'status' => 'processing',
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/imports/{$import->id}/pause")
            ->assertOk()
            ->assertJsonPath('message', 'Import paused.');

        $this->postJson("/api/imports/{$import->id}/resume")
            ->assertOk()
            ->assertJsonPath('message', 'Import resumed.');

        $this->postJson("/api/imports/{$import->id}/cancel")
            ->assertOk()
            ->assertJsonPath('message', 'Import cancelled.');
    }
}
