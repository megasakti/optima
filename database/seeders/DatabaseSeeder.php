<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            CompanyAndUserSeeder::class,
            AccountSeeder::class,
            CashBankSeeder::class,
            AccountingPeriodSeeder::class,
            OperationalMasterSeeder::class,
            FinancialDemoSeeder::class,
        ]);
    }
}
