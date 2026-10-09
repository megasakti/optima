<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Lsp;
use App\Models\Trainer;
use App\Models\TrainingBatch;
use App\Models\TrainingProgram;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class OperationalMasterSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (!$company) {
            return;
        }

        $coordinator = User::where('email', 'operations@optima.local')->first() ?? User::first();

        // 1. LSP (Lembaga Sertifikasi Profesi)
        $lsp1 = Lsp::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'LSP-K3-NAS'],
            [
                'name' => 'LSP Keselamatan Kerja Nasional (K3)',
                'bnsp_license_number' => 'BNSP-LSP-045-ID/2022',
                'pic_name' => 'Ir. Hendra Gunawan',
                'pic_phone' => '081298765432',
                'pic_email' => 'admin@lsp-k3nasional.org',
                'address' => 'Jl. Salemba Raya No. 12, Jakarta Pusat',
                'bank_name' => 'Bank Mandiri',
                'bank_account_number' => '122-00-9876543-1',
                'bank_account_holder' => 'LSP Keselamatan Kerja Nasional',
                'is_active' => true,
            ]
        );

        $lsp2 = Lsp::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'LSP-DIGIMA'],
            [
                'name' => 'LSP Digital Marketing Indonesia',
                'bnsp_license_number' => 'BNSP-LSP-112-ID/2023',
                'pic_name' => 'Anita Wulandari, S.Kom',
                'pic_phone' => '081388990011',
                'pic_email' => 'sertifikasi@lsp-digima.id',
                'address' => 'Gedung Cyber 2 Lt. 8, Kuningan, Jakarta Selatan',
                'bank_name' => 'Bank BCA',
                'bank_account_number' => '542-019-8811',
                'bank_account_holder' => 'LSP Digital Marketing Indonesia',
                'is_active' => true,
            ]
        );

        // 2. Customers
        $customer1 = Customer::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'CUST-001'],
            [
                'name' => 'PT Semen Nusantara Perkasa',
                'customer_type' => 'corporate',
                'pic_name' => 'Bambang Trihatmodjo (HR Manager)',
                'pic_phone' => '081234567890',
                'pic_email' => 'hrd@semen-nusantara.co.id',
                'tax_id' => '01.555.444.3-091.000',
                'address' => 'Kawasan Industri Cilegon Kav. 45, Banten',
                'city' => 'Cilegon',
                'payment_term_days' => 30,
                'credit_limit' => 200000000.00,
                'is_active' => true,
            ]
        );

        $customer2 = Customer::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'CUST-002'],
            [
                'name' => 'Dinas Ketenagakerjaan & Transmigrasi',
                'customer_type' => 'government',
                'pic_name' => 'Drs. Agus Subekti, M.Si',
                'pic_phone' => '081199887766',
                'pic_email' => 'pelatihan@disnakertrans.go.id',
                'tax_id' => '00.123.456.7-001.000',
                'address' => 'Komplek Balaikota Blok C, Bandung',
                'city' => 'Bandung',
                'payment_term_days' => 45,
                'credit_limit' => 350000000.00,
                'is_active' => true,
            ]
        );

        // 3. Vendors
        $vendorHotel = Vendor::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'VEND-HTL-01'],
            [
                'name' => 'Hotel Santika Premiere Slipi',
                'category' => 'hotel_venue',
                'pic_name' => 'Fiona Sales Banquet',
                'pic_phone' => '021-5330350',
                'pic_email' => 'banquet@slipi.santika.com',
                'tax_id' => '01.999.888.7-014.000',
                'address' => 'Jl. Aipda KS Tubun No. 7, Jakarta Barat',
                'bank_name' => 'Bank BCA',
                'bank_account_number' => '001-229-8765',
                'bank_account_holder' => 'PT Santika Graha Slipi',
                'payment_term_days' => 14,
                'is_active' => true,
            ]
        );

        $vendorAtk = Vendor::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'VEND-ATK-01'],
            [
                'name' => 'CV Grafika Mandiri Printing',
                'category' => 'stationery_printing',
                'pic_name' => 'Rahmat Hidayat',
                'pic_phone' => '081277665544',
                'pic_email' => 'order@grafikamandiri.com',
                'tax_id' => '02.333.444.5-015.000',
                'address' => 'Jl. Percetakan Negara No. 88, Jakarta Pusat',
                'bank_name' => 'Bank Mandiri',
                'bank_account_number' => '119-00-554433-2',
                'bank_account_holder' => 'CV Grafika Mandiri',
                'payment_term_days' => 14,
                'is_active' => true,
            ]
        );

        // 4. Trainers
        $trainer1 = Trainer::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'TRN-001'],
            [
                'name' => 'Dr. Ir. Rudi Hartono, M.KKK',
                'specialization' => 'Ahli K3 Umum & Higiene Industri BNSP',
                'phone' => '081122334455',
                'email' => 'rudi.k3trainer@gmail.com',
                'tax_id' => '14.555.666.7-011.000',
                'id_card_number' => '3171012304750001',
                'default_daily_rate' => 3500000.00,
                'bank_name' => 'Bank BCA',
                'bank_account_number' => '542-881-2299',
                'bank_account_holder' => 'Rudi Hartono',
                'is_active' => true,
            ]
        );

        // 5. Training Programs
        $prog1 = TrainingProgram::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'PRG-K3U'],
            [
                'lsp_id' => $lsp1->id,
                'name' => 'Pelatihan & Sertifikasi Ahli K3 Umum BNSP',
                'skkni_scheme_name' => 'Skema Okupasi Ahli K3 Umum Kemenaker / BNSP',
                'category' => 'certification',
                'duration_days' => 4,
                'default_price_per_participant' => 6500000.00,
                'default_lsp_cost_per_participant' => 1800000.00,
                'description' => 'Paket pelatihan intensif K3, praktikum asesmen, dan uji kompetensi lisensi BNSP',
                'is_active' => true,
            ]
        );

        // 6. Training Batches
        TrainingBatch::firstOrCreate(
            ['company_id' => $company->id, 'batch_code' => 'BTH-2026-03-001'],
            [
                'program_id' => $prog1->id,
                'lsp_id' => $lsp1->id,
                'trainer_id' => $trainer1->id,
                'pic_user_id' => $coordinator->id,
                'name' => 'Batch 01 - Sertifikasi Ahli K3 Umum BNSP Maret 2026',
                'start_date' => '2026-03-16',
                'end_date' => '2026-03-19',
                'assessment_date' => '2026-03-20',
                'delivery_type' => 'offline_hotel',
                'venue_name' => 'Hotel Santika Premiere Slipi, Jakarta',
                'venue_city' => 'Jakarta Barat',
                'target_participants' => 15,
                'actual_participants' => 12,
                'price_per_participant' => 6500000.00,
                'status' => 'ongoing',
                'notes' => 'Batch berjalan. Klien utama PT Semen Nusantara Perkasa (8 peserta).',
            ]
        );
    }
}
