<?php

namespace Database\Seeders;

use App\Models\AccountingPeriod;
use App\Models\Company;
use Illuminate\Database\Seeder;

class AccountingPeriodSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (!$company) {
            return;
        }

        // Buat Periode Akuntansi 2026
        $periods = [
            [
                'name' => 'Tahun Buku 2026',
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'is_closed' => false,
                'notes' => 'Periode akuntansi tahun berjalan 2026',
            ],
            [
                'name' => 'Januari 2026',
                'start_date' => '2026-01-01',
                'end_date' => '2026-01-31',
                'is_closed' => true,
                'notes' => 'Periode tutup buku Januari 2026',
            ],
            [
                'name' => 'Februari 2026',
                'start_date' => '2026-02-01',
                'end_date' => '2026-02-28',
                'is_closed' => true,
                'notes' => 'Periode tutup buku Februari 2026',
            ],
            [
                'name' => 'Maret 2026',
                'start_date' => '2026-03-01',
                'end_date' => '2026-03-31',
                'is_closed' => false,
                'notes' => 'Periode aktif berjalan Maret 2026',
            ],
        ];

        foreach ($periods as $periodData) {
            $periodData['company_id'] = $company->id;
            AccountingPeriod::firstOrCreate(
                [
                    'company_id' => $company->id,
                    'start_date' => $periodData['start_date'],
                    'end_date' => $periodData['end_date'],
                ],
                $periodData
            );
        }
    }
}
