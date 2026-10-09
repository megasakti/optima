<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create Permissions
        $permissions = [
            // General Ledger & COA
            'view-accounts', 'create-accounts', 'edit-accounts', 'delete-accounts',
            'view-journals', 'create-journals', 'post-journals', 'reverse-journals',
            'view-ledger', 'view-financial-reports', 'close-period',
            
            // Invoices & Receivables
            'view-invoices', 'create-invoices', 'edit-invoices', 'approve-invoices', 'delete-invoices',
            'record-customer-payments', 'view-customer-payments',
            
            // Bills & Payables (LSP & Vendors)
            'view-bills', 'create-bills', 'edit-bills', 'approve-bills', 'delete-bills',
            'record-vendor-payments', 'view-vendor-payments',
            
            // Treasury (Cash & Bank)
            'view-cash-bank', 'manage-cash-bank', 'reconcile-bank',
            
            // Training & Operations
            'view-batches', 'create-batches', 'edit-batches', 'close-batches',
            'view-programs', 'create-programs', 'edit-programs', 'delete-programs',
            'view-customers', 'create-customers', 'edit-customers',
            'view-lsps', 'create-lsps', 'edit-lsps', 'delete-lsps',
            'view-vendors', 'create-vendors', 'edit-vendors',
            'view-trainers', 'create-trainers', 'edit-trainers', 'delete-trainers',
            
            // Budgeting & RAB
            'view-budgets', 'create-budgets', 'edit-budgets', 'approve-budgets',
            
            // Admin & Audit
            'manage-users', 'view-users', 'create-users', 'edit-users', 'delete-users',
            'approve-deletion', 'manage-roles', 'view-audit-logs', 'manage-settings'
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // 2. Create Roles and Assign Permissions
        // Super Admin: Has all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // Finance Manager: Full financial approval, reports, and period closing
        $financeManager = Role::firstOrCreate(['name' => 'Finance Manager', 'guard_name' => 'web']);
        $financeManager->syncPermissions([
            'view-accounts', 'create-accounts', 'edit-accounts',
            'view-journals', 'create-journals', 'post-journals', 'reverse-journals',
            'view-ledger', 'view-financial-reports', 'close-period',
            'view-invoices', 'create-invoices', 'approve-invoices',
            'record-customer-payments', 'view-customer-payments',
            'view-bills', 'create-bills', 'approve-bills',
            'record-vendor-payments', 'view-vendor-payments',
            'view-cash-bank', 'manage-cash-bank', 'reconcile-bank',
            'view-batches', 'view-programs', 'view-customers', 'view-lsps', 'view-vendors', 'view-trainers',
            'view-budgets', 'approve-budgets',
            'view-audit-logs'
        ]);

        // Accountant / Kasir: Day-to-day transactions (Invoices, Bills, Payments)
        $accountant = Role::firstOrCreate(['name' => 'Accountant', 'guard_name' => 'web']);
        $accountant->syncPermissions([
            'view-accounts',
            'view-journals', 'create-journals',
            'view-ledger', 'view-financial-reports',
            'view-invoices', 'create-invoices',
            'record-customer-payments', 'view-customer-payments',
            'view-bills', 'create-bills',
            'record-vendor-payments', 'view-vendor-payments',
            'view-cash-bank',
            'view-batches', 'view-customers', 'view-lsps', 'view-vendors', 'view-trainers',
            'view-budgets'
        ]);

        // Operations / Batch Coordinator: Batches, RAB submission, viewing customers & trainers
        $operations = Role::firstOrCreate(['name' => 'Operations Coordinator', 'guard_name' => 'web']);
        $operations->syncPermissions([
            'view-batches', 'create-batches', 'edit-batches',
            'view-programs',
            'view-customers', 'create-customers', 'edit-customers',
            'view-lsps', 'view-trainers',
            'view-budgets', 'create-budgets', 'edit-budgets'
        ]);

        // Auditor / Management: Read-only access to all reports & audit logs
        $auditor = Role::firstOrCreate(['name' => 'Auditor', 'guard_name' => 'web']);
        $auditor->syncPermissions([
            'view-accounts', 'view-journals', 'view-ledger', 'view-financial-reports',
            'view-invoices', 'view-customer-payments',
            'view-bills', 'view-vendor-payments',
            'view-cash-bank', 'view-batches', 'view-budgets',
            'view-audit-logs'
        ]);
    }
}
