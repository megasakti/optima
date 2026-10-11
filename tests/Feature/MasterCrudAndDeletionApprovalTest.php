<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\DeletionRequest;
use App\Models\Lsp;
use App\Models\Trainer;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MasterCrudAndDeletionApprovalTest extends TestCase
{
    use DatabaseTransactions;

    protected Company $company;
    protected User $superAdmin;
    protected User $financeManager;
    protected User $operationsCoordinator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::where('code', 'OPTIMA-ID')->firstOrFail();
        $this->superAdmin = User::where('email', 'admin@optima.local')->firstOrFail();
        $this->financeManager = User::where('email', 'finance.manager@optima.local')->firstOrFail();
        $this->operationsCoordinator = User::where('email', 'operations@optima.local')->firstOrFail();
    }

    public function test_user_can_crud_training_program_with_deletion_approval_workflow(): void
    {
        // 1. Create Program
        $response = $this->actingAs($this->operationsCoordinator)
            ->post(route('programs.store'), [
                'code' => 'SKM-TEST-01',
                'name' => 'Skema Uji Kompetensi Test',
                'category' => 'certification',
                'duration_days' => 3,
                'default_price_per_participant' => 4500000,
                'default_lsp_cost_per_participant' => 1500000,
                'description' => 'Program pengujian otomatis',
            ]);

        $response->assertStatus(302);
        $program = TrainingProgram::where('code', 'SKM-TEST-01')->firstOrFail();
        $this->assertEquals('Skema Uji Kompetensi Test', $program->name);

        // 2. View Index & Show
        $this->actingAs($this->operationsCoordinator)
            ->get(route('programs.index'))
            ->assertStatus(200)
            ->assertSee('Skema Uji Kompetensi Test');

        $this->actingAs($this->operationsCoordinator)
            ->get(route('programs.show', $program->id))
            ->assertStatus(200)
            ->assertSee('SKM-TEST-01');

        // 3. Update Program
        $updateResponse = $this->actingAs($this->operationsCoordinator)
            ->put(route('programs.update', $program->id), [
                'code' => 'SKM-TEST-01',
                'name' => 'Skema Uji Kompetensi Test Updated',
                'category' => 'certification',
                'duration_days' => 4,
                'default_price_per_participant' => 5000000,
                'default_lsp_cost_per_participant' => 1600000,
                'is_active' => true,
            ]);

        $updateResponse->assertStatus(302);
        $this->assertDatabaseHas('training_programs', [
            'id' => $program->id,
            'name' => 'Skema Uji Kompetensi Test Updated',
            'duration_days' => 4,
        ]);

        // 4. Non-Super Admin Requests Deletion -> creates pending DeletionRequest
        $deleteReqResponse = $this->actingAs($this->operationsCoordinator)
            ->delete(route('programs.destroy', $program->id), [
                'reason' => 'Skema sudah tidak relevan dengan standar BNSP terbaru',
            ]);

        $deleteReqResponse->assertStatus(302);
        // The program must STILL exist in database because it requires Super Admin approval
        $this->assertDatabaseHas('training_programs', ['id' => $program->id]);

        $delRequest = DeletionRequest::where('model_type', TrainingProgram::class)
            ->where('model_id', $program->id)
            ->firstOrFail();

        $this->assertEquals('pending', $delRequest->status);
        $this->assertEquals($this->operationsCoordinator->id, $delRequest->requested_by);

        // 5. Super Admin Approves Deletion -> program is permanently deleted
        $approveResponse = $this->actingAs($this->superAdmin)
            ->post(route('admin.deletion_requests.approve', $delRequest->id));

        $approveResponse->assertStatus(302);
        $this->assertDatabaseMissing('training_programs', ['id' => $program->id]);
        $this->assertEquals('approved', $delRequest->fresh()->status);
    }

    public function test_user_can_crud_lsp_and_superadmin_can_reject_deletion(): void
    {
        // 1. Create LSP
        $response = $this->actingAs($this->operationsCoordinator)
            ->post(route('lsps.store'), [
                'code' => 'LSP-TEST-99',
                'name' => 'LSP Uji Kompetensi Nasional',
                'bnsp_license_number' => 'BNSP-TEST-999',
                'pic_name' => 'Bambang Sudiro',
                'pic_phone' => '081234567890',
                'pic_email' => 'bambang@lsp-test.org',
                'bank_name' => 'BCA',
                'bank_account_number' => '987654321',
                'bank_account_holder' => 'LSP Uji Kompetensi',
            ]);

        $response->assertStatus(302);
        $lsp = Lsp::where('code', 'LSP-TEST-99')->firstOrFail();

        // 2. Update LSP
        $this->actingAs($this->operationsCoordinator)
            ->put(route('lsps.update', $lsp->id), [
                'code' => 'LSP-TEST-99',
                'name' => 'LSP Uji Kompetensi Nasional Revisi',
                'is_active' => true,
            ])
            ->assertStatus(302);

        // 3. Request Deletion by non-superadmin
        $this->actingAs($this->operationsCoordinator)
            ->delete(route('lsps.destroy', $lsp->id), [
                'reason' => 'Perjanjian kerjasama telah berakhir',
            ]);

        $delRequest = DeletionRequest::where('model_type', Lsp::class)
            ->where('model_id', $lsp->id)
            ->firstOrFail();

        // 4. Super Admin Rejects Deletion
        $rejectResponse = $this->actingAs($this->superAdmin)
            ->post(route('admin.deletion_requests.reject', $delRequest->id), [
                'rejection_reason' => 'Kerjasama sedang diperpanjang, jangan dihapus.',
            ]);

        $rejectResponse->assertStatus(302);
        $this->assertDatabaseHas('lsps', ['id' => $lsp->id]);
        $this->assertEquals('rejected', $delRequest->fresh()->status);
        $this->assertEquals('Kerjasama sedang diperpanjang, jangan dihapus.', $delRequest->fresh()->rejection_reason);
    }

    public function test_superadmin_can_directly_delete_trainer(): void
    {
        // 1. Create Trainer
        $response = $this->actingAs($this->operationsCoordinator)
            ->post(route('trainers.store'), [
                'code' => 'TRN-TEST-01',
                'name' => 'Dr. Suwandi M.Sc',
                'specialization' => 'Manajemen Risiko BNSP',
                'phone' => '081211112222',
                'default_daily_rate' => 3500000,
            ]);

        $response->assertStatus(302);
        $trainer = Trainer::where('code', 'TRN-TEST-01')->firstOrFail();

        // 2. Super Admin Deletes Directly
        $deleteResponse = $this->actingAs($this->superAdmin)
            ->delete(route('trainers.destroy', $trainer->id), [
                'reason' => 'Pembersihan data dummy instruktur',
            ]);

        $deleteResponse->assertStatus(302);
        $this->assertDatabaseMissing('trainers', ['id' => $trainer->id]);
    }

    public function test_superadmin_can_crud_user_and_cannot_delete_self(): void
    {
        // 1. Create User
        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.users.store'), [
                'name' => 'Staf Baru Operasional',
                'email' => 'staf.baru@optima.local',
                'phone' => '081299998888',
                'role' => 'Operations Coordinator',
                'password' => 'PasswordBaru123!',
            ]);

        $response->assertStatus(302);
        $newUser = User::where('email', 'staf.baru@optima.local')->firstOrFail();
        $this->assertTrue($newUser->hasRole('Operations Coordinator'));

        // 2. Update User
        $this->actingAs($this->superAdmin)
            ->put(route('admin.users.update', $newUser->id), [
                'name' => 'Staf Operasional Senior',
                'email' => 'staf.baru@optima.local',
                'phone' => '081299998888',
                'role' => 'Operations Coordinator',
                'is_active' => true,
            ])
            ->assertStatus(302);

        $this->assertDatabaseHas('users', [
            'id' => $newUser->id,
            'name' => 'Staf Operasional Senior',
        ]);

        // 3. Super Admin cannot delete own account
        $selfDeleteResponse = $this->actingAs($this->superAdmin)
            ->delete(route('admin.users.destroy', $this->superAdmin->id));

        $selfDeleteResponse->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->superAdmin->id]);

        // 4. Super Admin can delete the new user
        $deleteOtherResponse = $this->actingAs($this->superAdmin)
            ->delete(route('admin.users.destroy', $newUser->id));

        $deleteOtherResponse->assertStatus(302);
        $this->assertDatabaseMissing('users', ['id' => $newUser->id]);
    }

    public function test_journal_and_account_have_no_edit_or_delete_routes(): void
    {
        $this->actingAs($this->superAdmin);

        // Jurnal Umum tidak boleh memiliki route edit atau delete
        $r1 = $this->get('/accounting/journals/1/edit');
        $this->assertContains($r1->getStatusCode(), [404, 405]);

        $r2 = $this->delete('/accounting/journals/1');
        $this->assertContains($r2->getStatusCode(), [404, 405]);

        // Bagan Akun tidak boleh memiliki route edit atau delete
        $r3 = $this->get('/accounting/accounts/1/edit');
        $this->assertContains($r3->getStatusCode(), [404, 405]);

        $r4 = $this->delete('/accounting/accounts/1');
        $this->assertContains($r4->getStatusCode(), [404, 405]);
    }
}
