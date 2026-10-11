<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Lsp;
use App\Models\Participant;
use App\Models\TrainingBatch;
use App\Models\TrainingProgram;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ParticipantSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (! $company) {
            return;
        }

        $customer1 = Customer::where('company_id', $company->id)->where('code', 'CUST-001')->first() ?? Customer::first();
        $customer2 = Customer::where('company_id', $company->id)->where('code', 'CUST-002')->first();
        $lspK3 = Lsp::where('company_id', $company->id)->where('code', 'LSP-K3-NAS')->first() ?? Lsp::first();
        $lspDigima = Lsp::where('company_id', $company->id)->where('code', 'LSP-DIGIMA')->first();
        $programK3 = TrainingProgram::where('company_id', $company->id)->first();
        $batch = TrainingBatch::where('company_id', $company->id)->first();

        $participantsData = [
            [
                'company_id' => $company->id,
                'customer_id' => $customer1?->id,
                'company_name' => $customer1?->name ?? 'PT Semen Nusantara Perkasa',
                'category' => 'iht',
                'name' => 'Ahmad Fauzi, S.T.',
                'phone' => '081288992211',
                'email' => 'ahmad.fauzi@semen-nusantara.co.id',
                'training_title' => 'Ahli K3 Umum Sertifikasi Kemnaker & BNSP',
                'training_program_id' => $programK3?->id,
                'training_batch_id' => $batch?->id,
                'training_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'location' => 'In-House Plant Cilegon (PT Semen Nusantara)',
                'price' => 5500000.00,
                'source' => 'bod',
                'lsp_id' => $lspK3?->id,
                'payment_status' => 'paid',
                'certificate_status' => 'issued',
                'notes' => 'Peserta perwakilan divisi HSE, lulus asesmen dengan predikat sangat memuaskan.',
            ],
            [
                'company_id' => $company->id,
                'customer_id' => $customer1?->id,
                'company_name' => $customer1?->name ?? 'PT Semen Nusantara Perkasa',
                'category' => 'iht',
                'name' => 'Rina Marlina, S.Psi',
                'phone' => '081377889900',
                'email' => 'rina.m@semen-nusantara.co.id',
                'training_title' => 'Human Resource Supervisor Sertifikasi BNSP',
                'training_program_id' => null,
                'training_batch_id' => null,
                'training_date' => Carbon::now()->subDays(2)->format('Y-m-d'),
                'location' => 'Head Office Semen Nusantara Jakarta',
                'price' => 4500000.00,
                'source' => 'bod',
                'lsp_id' => $lspDigima?->id ?? $lspK3?->id,
                'payment_status' => 'paid',
                'certificate_status' => 'passed',
                'notes' => 'Telah menyelesaikan asesmen portofolio.',
            ],
            [
                'company_id' => $company->id,
                'customer_id' => $customer2?->id,
                'company_name' => $customer2?->name ?? 'Dinas Ketenagakerjaan & Transmigrasi',
                'category' => 'public',
                'name' => 'Bayu Pratama',
                'phone' => '085611223344',
                'email' => 'bayu.pratama@disnakertrans.go.id',
                'training_title' => 'Petugas K3 Madya (Occupational Health & Safety)',
                'training_program_id' => $programK3?->id,
                'training_batch_id' => $batch?->id,
                'training_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
                'location' => 'Hotel Santika Premiere Slipi, Jakarta Barat',
                'price' => 4800000.00,
                'source' => 'web',
                'lsp_id' => $lspK3?->id,
                'payment_status' => 'partial',
                'certificate_status' => 'process',
                'notes' => 'DP 50% diterima, pelunasan saat registrasi ulang hari H.',
            ],
            [
                'company_id' => $company->id,
                'customer_id' => null,
                'company_name' => 'PT Karya Mandiri Solusindo',
                'category' => 'public',
                'name' => 'Dewi Sartika, S.E.',
                'phone' => '087799001122',
                'email' => 'dewi.sartika@karyamandiri.co.id',
                'training_title' => 'Digital Marketing & Growth Specialist BNSP',
                'training_program_id' => null,
                'training_batch_id' => null,
                'training_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
                'location' => 'Gedung Optima Training Center, Tebet',
                'price' => 3750000.00,
                'source' => 'ig',
                'lsp_id' => $lspDigima?->id ?? $lspK3?->id,
                'payment_status' => 'paid',
                'certificate_status' => 'process',
                'notes' => 'Mendaftar via campaign Instagram Ads Batch 24.',
            ],
            [
                'company_id' => $company->id,
                'customer_id' => null,
                'company_name' => 'Universitas Indonesia (FKM)',
                'category' => 'webinar',
                'name' => 'Gilang Ramadhan',
                'phone' => '081912345678',
                'email' => 'gilang.ramadhan@ui.ac.id',
                'training_title' => 'Webinar Nasional: Implementasi SMK3 di Era Industri 5.0',
                'training_program_id' => null,
                'training_batch_id' => null,
                'training_date' => Carbon::now()->addDays(12)->format('Y-m-d'),
                'location' => 'Online via Zoom Meeting Live Streaming',
                'price' => 150000.00,
                'source' => 'web',
                'lsp_id' => null,
                'payment_status' => 'paid',
                'certificate_status' => 'process',
                'notes' => 'E-Certificate kehadiran dan materi PDF akan dikirimkan otomatis H+1.',
            ],
            [
                'company_id' => $company->id,
                'customer_id' => $customer1?->id,
                'company_name' => $customer1?->name ?? 'PT Semen Nusantara Perkasa',
                'category' => 'webinar',
                'name' => 'Hendro Siswanto, S.T.',
                'phone' => '082133445566',
                'email' => 'hendro.s@semen-nusantara.co.id',
                'training_title' => 'Webinar Eksekutif: Manajemen Risiko & Audit Internal',
                'training_program_id' => null,
                'training_batch_id' => null,
                'training_date' => Carbon::now()->addDays(15)->format('Y-m-d'),
                'location' => 'Online Zoom Meeting',
                'price' => 250000.00,
                'source' => 'bod',
                'lsp_id' => null,
                'payment_status' => 'unpaid',
                'certificate_status' => 'process',
                'notes' => 'Invoice kolektif bersama 5 peserta lain dari bagian General Affair.',
            ],
        ];

        foreach ($participantsData as $data) {
            Participant::create($data);
        }
    }
}
