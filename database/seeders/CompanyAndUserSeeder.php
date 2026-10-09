<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CompanyAndUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Perusahaan Utama
        $company = Company::firstOrCreate(
            ['code' => 'OPTIMA-ID'],
            [
                'name' => 'PT Optima Mitra Sertifikasi',
                'tax_id' => '01.234.567.8-012.000',
                'address' => 'Gedung Wisma Pelatihan Lt. 4, Jl. TB Simatupang, Jakarta Selatan',
                'phone' => '021-78901234',
                'email' => 'finance@optima-training.co.id',
                'website' => 'https://optima-training.co.id',
                'currency' => 'IDR',
                'is_active' => true,
            ]
        );

        // 2. Pengguna Utama
        $users = [
            [
                'name' => 'Super Administrator',
                'email' => 'admin@optima.local',
                'password' => Hash::make('password123'),
                'phone' => '081100000001',
                'role' => 'Super Admin',
            ],
            [
                'name' => 'Budi Santoso (Finance Manager)',
                'email' => 'finance.manager@optima.local',
                'password' => Hash::make('password123'),
                'phone' => '081100000002',
                'role' => 'Finance Manager',
            ],
            [
                'name' => 'Siti Rahma (Staff Kasir & Akuntansi)',
                'email' => 'accountant@optima.local',
                'password' => Hash::make('password123'),
                'phone' => '081100000003',
                'role' => 'Accountant',
            ],
            [
                'name' => 'Dimas Prasetyo (Koordinator Batch)',
                'email' => 'operations@optima.local',
                'password' => Hash::make('password123'),
                'phone' => '081100000004',
                'role' => 'Operations Coordinator',
            ],
            [
                'name' => 'Dewi Lestari (Internal Auditor)',
                'email' => 'auditor@optima.local',
                'password' => Hash::make('password123'),
                'phone' => '081100000005',
                'role' => 'Auditor',
            ],
        ];

        foreach ($users as $userData) {
            $roleName = $userData['role'];
            unset($userData['role']);
            $userData['company_id'] = $company->id;
            $userData['is_active'] = true;

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            $user->syncRoles([$roleName]);
        }
    }
}
