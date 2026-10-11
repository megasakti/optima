<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Lsp;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ParticipantTest extends TestCase
{
    use DatabaseTransactions;

    protected Company $company;

    protected User $superAdmin;

    protected User $operationsCoordinator;

    protected Customer $customer;

    protected Lsp $lsp;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::firstOrFail();
        $this->superAdmin = User::where('email', 'admin@optima.local')->firstOrFail();
        $this->operationsCoordinator = User::where('email', 'operations@optima.local')->firstOrFail();
        $this->customer = Customer::where('company_id', $this->company->id)->firstOrFail();
        $this->lsp = Lsp::where('company_id', $this->company->id)->firstOrFail();
    }

    public function test_authorized_user_can_view_participants_index(): void
    {
        $response = $this->actingAs($this->operationsCoordinator)
            ->get(route('participants.index'));

        $response->assertStatus(200);
        $response->assertSee('Data Peserta Pelatihan');
        $response->assertSee('Daftar Seluruh Peserta');
    }

    public function test_user_can_create_participant_with_customer_relation(): void
    {
        $payload = [
            'category' => 'iht',
            'name' => 'Dr. Hendra Wijaya',
            'customer_id' => $this->customer->id,
            'company_name' => $this->customer->name,
            'phone' => '081233445566',
            'email' => 'hendra.w@example.com',
            'training_title' => 'Pelatihan Implementasi SMK3 ISO 45001',
            'training_date' => now()->addDays(5)->format('Y-m-d'),
            'location' => 'Kantor PT Semen Nusantara',
            'price' => 6000000,
            'source' => 'bod',
            'lsp_id' => $this->lsp->id,
            'payment_status' => 'paid',
            'certificate_status' => 'process',
            'notes' => 'Pendaftaran IHT Batch Spesial',
        ];

        $response = $this->actingAs($this->operationsCoordinator)
            ->post(route('participants.store'), $payload);

        $response->assertStatus(302);

        $participant = Participant::where('name', 'Dr. Hendra Wijaya')->first();
        $this->assertNotNull($participant);
        $this->assertEquals($this->customer->id, $participant->customer_id);
        $this->assertEquals('iht', $participant->category);
        $this->assertEquals(6000000, (float) $participant->price);
        $this->assertEquals('paid', $participant->payment_status);
        $this->assertEquals('bod', $participant->source);
        $this->assertEquals($this->lsp->id, $participant->lsp_id);
    }

    public function test_user_can_view_participant_detail(): void
    {
        $participant = Participant::create([
            'company_id' => $this->company->id,
            'customer_id' => $this->customer->id,
            'company_name' => $this->customer->name,
            'category' => 'public',
            'name' => 'Siti Nurhaliza, S.E.',
            'phone' => '081399887766',
            'email' => 'siti.nur@example.com',
            'training_title' => 'Digital Marketing Specialist BNSP',
            'training_date' => now()->format('Y-m-d'),
            'location' => 'Optima Training Hall',
            'price' => 3500000,
            'source' => 'ig',
            'lsp_id' => $this->lsp->id,
            'payment_status' => 'paid',
            'certificate_status' => 'passed',
            'notes' => 'Catatan test',
        ]);

        $response = $this->actingAs($this->operationsCoordinator)
            ->get(route('participants.show', $participant->id));

        $response->assertStatus(200);
        $response->assertSee('Siti Nurhaliza, S.E.');
        $response->assertSee($this->customer->name);
        $response->assertSee('Rp 3.500.000');
    }

    public function test_user_can_update_participant(): void
    {
        $participant = Participant::create([
            'company_id' => $this->company->id,
            'customer_id' => null,
            'company_name' => 'PT Mandiri Jaya',
            'category' => 'webinar',
            'name' => 'Rudi Hartono',
            'phone' => '087711223344',
            'email' => 'rudi@example.com',
            'training_title' => 'Webinar Manajemen Risiko',
            'training_date' => now()->format('Y-m-d'),
            'location' => 'Zoom Online',
            'price' => 200000,
            'source' => 'web',
            'lsp_id' => null,
            'payment_status' => 'unpaid',
            'certificate_status' => 'process',
        ]);

        $response = $this->actingAs($this->operationsCoordinator)
            ->put(route('participants.update', $participant->id), [
                'category' => 'webinar',
                'name' => 'Rudi Hartono, M.M.',
                'customer_id' => $this->customer->id,
                'company_name' => $this->customer->name,
                'phone' => '087711223344',
                'email' => 'rudi.hartono@example.com',
                'training_title' => 'Webinar Manajemen Risiko & K3',
                'training_date' => now()->format('Y-m-d'),
                'location' => 'Zoom Online Premium',
                'price' => 250000,
                'source' => 'web',
                'lsp_id' => null,
                'payment_status' => 'paid',
                'certificate_status' => 'issued',
            ]);

        $response->assertStatus(302);

        $participant->refresh();
        $this->assertEquals('Rudi Hartono, M.M.', $participant->name);
        $this->assertEquals('paid', $participant->payment_status);
        $this->assertEquals('issued', $participant->certificate_status);
        $this->assertEquals($this->customer->id, $participant->customer_id);
    }

    public function test_super_admin_can_delete_participant(): void
    {
        $participant = Participant::create([
            'company_id' => $this->company->id,
            'customer_id' => null,
            'company_name' => 'PT Temp Test',
            'category' => 'public',
            'name' => 'Peserta Hapus Test',
            'phone' => '081200000000',
            'training_title' => 'Test Training Delete',
            'training_date' => now()->format('Y-m-d'),
            'location' => 'Jakarta',
            'price' => 1000000,
            'source' => 'other',
            'payment_status' => 'unpaid',
            'certificate_status' => 'process',
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->delete(route('participants.destroy', $participant->id), [
                'reason' => 'Pengujian unit test',
            ]);

        $response->assertStatus(302);
        $this->assertDatabaseMissing('participants', ['id' => $participant->id]);
    }
}
