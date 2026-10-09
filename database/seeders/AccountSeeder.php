<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Company;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (!$company) {
            return;
        }

        // Definisi Chart of Accounts (COA) Standar Lembaga Pelatihan & Sertifikasi BNSP
        // Mapped 100% dari Laporan Keuangan 2026 Google Sheets + Akun Kontrol SAK
        $accounts = [
            // ==================== 1. ASET ====================
            [
                'code' => '101',
                'name' => 'KAS',
                'type' => 'asset',
                'normal_balance' => 'debit',
                'category' => 'current_asset',
                'is_cash_bank' => true,
                'is_reconcilable' => true,
                'description' => 'Kas Tunai / Kas Kecil (Petty Cash)'
            ],
            [
                'code' => '102',
                'name' => 'KAS BANK BCA',
                'type' => 'asset',
                'normal_balance' => 'debit',
                'category' => 'current_asset',
                'is_cash_bank' => true,
                'is_reconcilable' => true,
                'description' => 'Rekening Giro/Tabungan Utama BCA Operasional'
            ],
            [
                'code' => '103',
                'name' => 'PIUTANG USAHA',
                'type' => 'asset',
                'normal_balance' => 'debit',
                'category' => 'current_asset',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Piutang tagihan invoice pelatihan & sertifikasi ke pelanggan'
            ],
            [
                'code' => '104',
                'name' => 'ASET KANTOR',
                'type' => 'asset',
                'normal_balance' => 'debit',
                'category' => 'non_current_asset',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Peralatan kantor, proyektor, komputer, meja'
            ],
            [
                'code' => '105',
                'name' => 'UANG MUKA BIAYA (PANJAR)',
                'type' => 'asset',
                'normal_balance' => 'debit',
                'category' => 'current_asset',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Panjar kas operasional kegiatan pelatihan di lapangan'
            ],
            [
                'code' => '106',
                'name' => 'PAJAK DIBAYAR DI MUKA (PPH 23)',
                'type' => 'asset',
                'normal_balance' => 'debit',
                'category' => 'current_asset',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Pajak penghasilan pasal 23 yang dipotong oleh pelanggan corporate'
            ],

            // ==================== 2. KEWAJIBAN ====================
            [
                'code' => '201',
                'name' => 'UTANG USAHA',
                'type' => 'liability',
                'normal_balance' => 'credit',
                'category' => 'current_liability',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Kewajiban kepada vendor hotel, katering, dan ATK'
            ],
            [
                'code' => '202',
                'name' => 'UTANG SERTIFIKASI',
                'type' => 'liability',
                'normal_balance' => 'credit',
                'category' => 'current_liability',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Kewajiban tagihan uji kompetensi & blanko sertifikat kepada LSP mitra BNSP'
            ],
            [
                'code' => '203',
                'name' => 'UTANG GAJI',
                'type' => 'liability',
                'normal_balance' => 'credit',
                'category' => 'current_liability',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Akrual gaji staf dan honor instruktur yang belum dibayarkan'
            ],
            [
                'code' => '204',
                'name' => 'UTANG PAJAK',
                'type' => 'liability',
                'normal_balance' => 'credit',
                'category' => 'current_liability',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Utang PPN, PPh 21 tenaga ahli instruktur, dan PPh 23 vendor'
            ],
            [
                'code' => '205',
                'name' => 'PENDAPATAN DITERIMA DI MUKA',
                'type' => 'liability',
                'normal_balance' => 'credit',
                'category' => 'current_liability',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Uang muka pendaftaran batch pelatihan sebelum kegiatan diselenggarakan'
            ],
            [
                'code' => '206',
                'name' => 'UTANG SEWA',
                'type' => 'liability',
                'normal_balance' => 'credit',
                'category' => 'current_liability',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Akrual utang sewa kantor'
            ],

            // ==================== 3. EKUITAS ====================
            [
                'code' => '301',
                'name' => 'MODAL',
                'type' => 'equity',
                'normal_balance' => 'credit',
                'category' => 'equity',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Modal disetor pemilik'
            ],
            [
                'code' => '302',
                'name' => 'SALDO LABA (RETAINED EARNINGS)',
                'type' => 'equity',
                'normal_balance' => 'credit',
                'category' => 'equity',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Akumulasi laba atau rugi tahun-tahun sebelumnya'
            ],
            [
                'code' => '303',
                'name' => 'IKHTISAR LABA RUGI',
                'type' => 'equity',
                'normal_balance' => 'credit',
                'category' => 'equity',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Akun kliring tutup buku tahunan'
            ],

            // ==================== 4. PENDAPATAN ====================
            [
                'code' => '401',
                'name' => 'PENDAPATAN JASA PELATIHAN',
                'type' => 'revenue',
                'normal_balance' => 'credit',
                'category' => 'operating_revenue',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Pendapatan dari paket pelatihan dan sertifikasi kompetensi BNSP'
            ],
            [
                'code' => '402',
                'name' => 'PENDAPATAN JASA KONSULTASI',
                'type' => 'revenue',
                'normal_balance' => 'credit',
                'category' => 'operating_revenue',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Pendapatan jasa penyusunan modul, kurikulum, dan konsultasi SKKNI'
            ],
            [
                'code' => '403',
                'name' => 'PENDAPATAN LAIN-LAIN',
                'type' => 'revenue',
                'normal_balance' => 'credit',
                'category' => 'other_expense_income',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Bunga bank, jasa giro, dan pendapatan non-operasional lainnya'
            ],

            // ==================== 5. BIAYA LANGSUNG PELATIHAN (HPP BATCH) ====================
            [
                'code' => '503',
                'name' => 'BIAYA PELATIHAN',
                'type' => 'expense',
                'normal_balance' => 'debit',
                'category' => 'direct_cost',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Modul materi, ATK peserta, sertifikat internal, nametag, seragam/rompi'
            ],
            [
                'code' => '504',
                'name' => 'BIAYA SERTIFIKASI',
                'type' => 'expense',
                'normal_balance' => 'debit',
                'category' => 'direct_cost',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Biaya pendaftaran asesi dan asesmen uji kompetensi oleh LSP mitra BNSP'
            ],
            [
                'code' => '505',
                'name' => 'BIAYA INSTRUKTUR PELATIHAN',
                'type' => 'expense',
                'normal_balance' => 'debit',
                'category' => 'direct_cost',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Honorium / fee narasumber dan instruktur pengajar batch'
            ],
            [
                'code' => '514',
                'name' => 'BIAYA MAKAN & MINUM',
                'type' => 'expense',
                'normal_balance' => 'debit',
                'category' => 'direct_cost',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Konsumsi makan siang dan coffee break peserta pelatihan'
            ],
            [
                'code' => '515',
                'name' => 'BIAYA HOTEL',
                'type' => 'expense',
                'normal_balance' => 'debit',
                'category' => 'direct_cost',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Sewa ruang meeting hotel (fullboard/fullday) dan kamar penginapan'
            ],

            // ==================== 6. BIAYA OPERASIONAL & UMUM ====================
            [
                'code' => '501',
                'name' => 'BIAYA GAJI KARYAWAN',
                'type' => 'expense',
                'normal_balance' => 'debit',
                'category' => 'operating_expense',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Gaji tetap staf kantor, marketing, dan manajemen'
            ],
            [
                'code' => '502',
                'name' => 'BIAYA SEWA KANTOR',
                'type' => 'expense',
                'normal_balance' => 'debit',
                'category' => 'operating_expense',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Beban sewa gedung kantor operasional'
            ],
            [
                'code' => '506',
                'name' => 'BIAYA PAJAK',
                'type' => 'expense',
                'normal_balance' => 'debit',
                'category' => 'operating_expense',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Pajak daerah, retribusi, dan denda pajak jika ada'
            ],
            [
                'code' => '507',
                'name' => 'BIAYA PEMASARAN',
                'type' => 'expense',
                'normal_balance' => 'debit',
                'category' => 'operating_expense',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Iklan digital (Google/Meta Ads), brosur, pameran, landing page'
            ],
            [
                'code' => '508',
                'name' => 'BONUS KARYAWAN',
                'type' => 'expense',
                'normal_balance' => 'debit',
                'category' => 'operating_expense',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Bonus pencapaian target target batch dan insentif penjualan'
            ],
            [
                'code' => '509',
                'name' => 'BIAYA LISTRIK & INTERNET',
                'type' => 'expense',
                'normal_balance' => 'debit',
                'category' => 'operating_expense',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Tagihan PLN, air, dan koneksi internet WiFi kantor'
            ],
            [
                'code' => '510',
                'name' => 'BIAYA ADM BANK',
                'type' => 'expense',
                'normal_balance' => 'debit',
                'category' => 'operating_expense',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Biaya administrasi bulanan rekening dan transfer antar bank'
            ],
            [
                'code' => '511',
                'name' => 'BIAYA PERALATAN KANTOR',
                'type' => 'expense',
                'normal_balance' => 'debit',
                'category' => 'operating_expense',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Pembelian ATK kantor rutin dan tinta printer'
            ],
            [
                'code' => '512',
                'name' => 'BIAYA DANA SOSIAL',
                'type' => 'expense',
                'normal_balance' => 'debit',
                'category' => 'operating_expense',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Kegiatan kepedulian sosial, sumbangan, santunan'
            ],
            [
                'code' => '513',
                'name' => 'BIAYA LAIN-LAIN',
                'type' => 'expense',
                'normal_balance' => 'debit',
                'category' => 'operating_expense',
                'is_cash_bank' => false,
                'is_reconcilable' => false,
                'description' => 'Pengeluaran rupa-rupa operasional umum yang belum terdaftar'
            ],
        ];

        foreach ($accounts as $data) {
            $data['company_id'] = $company->id;
            $data['is_active'] = true;

            Account::firstOrCreate(
                [
                    'company_id' => $company->id,
                    'code' => $data['code']
                ],
                $data
            );
        }
    }
}
