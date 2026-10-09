<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\CashBankAccount;
use App\Models\Company;
use Illuminate\Database\Seeder;

class CashBankSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (!$company) {
            return;
        }

        $kasAccount = Account::where('company_id', $company->id)->where('code', '101')->first();
        $bcaAccount = Account::where('company_id', $company->id)->where('code', '102')->first();

        if ($kasAccount) {
            CashBankAccount::firstOrCreate(
                [
                    'company_id' => $company->id,
                    'account_id' => $kasAccount->id,
                ],
                [
                    'name' => 'KAS TUNAI OPERASIONAL (PETTY CASH)',
                    'account_type' => 'petty_cash',
                    'bank_name' => 'KAS INTERNAL',
                    'account_number' => 'KAS-01',
                    'account_holder' => 'Kasir Perusahaan',
                    'currency' => 'IDR',
                    'initial_balance' => 4500000.00,
                    'current_balance' => 4500000.00,
                    'is_active' => true,
                ]
            );
        }

        if ($bcaAccount) {
            CashBankAccount::firstOrCreate(
                [
                    'company_id' => $company->id,
                    'account_id' => $bcaAccount->id,
                ],
                [
                    'name' => 'REKENING GIRO BCA OPERASIONAL',
                    'account_type' => 'bank',
                    'bank_name' => 'BANK CENTRAL ASIA (BCA)',
                    'account_number' => '8801239988',
                    'account_holder' => 'PT Optima Mitra Sertifikasi',
                    'currency' => 'IDR',
                    'initial_balance' => 327894498.00,
                    'current_balance' => 74484680.00,
                    'is_active' => true,
                ]
            );
        }
    }
}
