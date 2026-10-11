<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationAndRbacTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('OPTIMA FINANCE');
        $response->assertSee('Masuk ke Dashboard');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@optima.local',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');

        // Verify audit log created
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'login',
        ]);
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::where('email', 'admin@optima.local')->first();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::where('email', 'admin@optima.local')->first();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');

        // Verify audit log created
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'logout',
            'user_id' => $user->id,
        ]);
    }

    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_dashboard_can_be_rendered_for_authenticated_user(): void
    {
        $user = User::where('email', 'finance.manager@optima.local')->first();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Ringkasan Eksekutif Keuangan');
        $response->assertSee('Saldo Kas & Bank', false);
        $response->assertSee('PT Optima Mitra Sertifikasi');
    }

    public function test_super_admin_can_access_admin_route(): void
    {
        $admin = User::where('email', 'admin@optima.local')->first();

        $response = $this->actingAs($admin)->get('/admin/test-superadmin');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'authorized',
            'role' => 'Super Admin',
        ]);
    }

    public function test_non_admin_cannot_access_admin_route(): void
    {
        $operations = User::where('email', 'operations@optima.local')->first();

        $response = $this->actingAs($operations)->get('/admin/test-superadmin');

        $response->assertStatus(403);
    }

    public function test_finance_manager_can_access_accounting_route(): void
    {
        $finance = User::where('email', 'finance.manager@optima.local')->first();

        $response = $this->actingAs($finance)->get('/accounting/test-ledger');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'authorized',
            'module' => 'General Ledger',
        ]);
    }

    public function test_operations_coordinator_cannot_access_accounting_ledger(): void
    {
        $operations = User::where('email', 'operations@optima.local')->first();

        $response = $this->actingAs($operations)->get('/accounting/test-ledger');

        $response->assertStatus(403);
    }

    public function test_user_can_update_profile_and_creates_audit_log(): void
    {
        $user = User::where('email', 'accountant@optima.local')->first();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Siti Rahma Updated',
            'phone' => '081299998888',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Siti Rahma Updated',
            'phone' => '081299998888',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'update',
        ]);
    }
}
