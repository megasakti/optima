-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: optima_finance_test
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `accounting_periods`
--

DROP TABLE IF EXISTS `accounting_periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `accounting_periods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nama periode, misal "Maret 2026" atau "Tahun 2026"',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_closed` tinyint(1) NOT NULL DEFAULT '0',
  `closed_at` datetime DEFAULT NULL,
  `closed_by` bigint unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounting_periods_company_id_start_date_end_date_unique` (`company_id`,`start_date`,`end_date`),
  KEY `accounting_periods_closed_by_foreign` (`closed_by`),
  KEY `accounting_periods_company_id_is_closed_index` (`company_id`,`is_closed`),
  CONSTRAINT `accounting_periods_closed_by_foreign` FOREIGN KEY (`closed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `accounting_periods_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounting_periods`
--

LOCK TABLES `accounting_periods` WRITE;
/*!40000 ALTER TABLE `accounting_periods` DISABLE KEYS */;
INSERT INTO `accounting_periods` VALUES (1,1,'Tahun Buku 2026','2026-01-01','2026-12-31',0,NULL,NULL,'Periode akuntansi tahun berjalan 2026','2026-10-08 22:39:32','2026-10-08 22:39:32'),(2,1,'Januari 2026','2026-01-01','2026-01-31',1,NULL,NULL,'Periode tutup buku Januari 2026','2026-10-08 22:39:32','2026-10-08 22:39:32'),(3,1,'Februari 2026','2026-02-01','2026-02-28',1,NULL,NULL,'Periode tutup buku Februari 2026','2026-10-08 22:39:32','2026-10-08 22:39:32'),(4,1,'Maret 2026','2026-03-01','2026-03-31',0,NULL,NULL,'Periode aktif berjalan Maret 2026','2026-10-08 22:39:32','2026-10-08 22:39:32');
/*!40000 ALTER TABLE `accounting_periods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accounts`
--

DROP TABLE IF EXISTS `accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Kode Akun, misal 101, 102, 504',
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('asset','liability','equity','revenue','expense') COLLATE utf8mb4_unicode_ci NOT NULL,
  `normal_balance` enum('debit','credit') COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` enum('current_asset','non_current_asset','current_liability','long_term_liability','equity','operating_revenue','direct_cost','operating_expense','other_expense_income') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'operating_expense',
  `is_cash_bank` tinyint(1) NOT NULL DEFAULT '0',
  `is_reconcilable` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounts_company_id_code_unique` (`company_id`,`code`),
  KEY `accounts_parent_id_foreign` (`parent_id`),
  KEY `accounts_company_id_type_index` (`company_id`,`type`),
  KEY `accounts_company_id_category_index` (`company_id`,`category`),
  KEY `accounts_code_index` (`code`),
  CONSTRAINT `accounts_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `accounts_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounts`
--

LOCK TABLES `accounts` WRITE;
/*!40000 ALTER TABLE `accounts` DISABLE KEYS */;
INSERT INTO `accounts` VALUES (1,1,NULL,'101','KAS','asset','debit','current_asset',1,1,1,'Kas Tunai / Kas Kecil (Petty Cash)','2026-10-08 22:39:32','2026-10-08 22:39:32'),(2,1,NULL,'102','KAS BANK BCA','asset','debit','current_asset',1,1,1,'Rekening Giro/Tabungan Utama BCA Operasional','2026-10-08 22:39:32','2026-10-08 22:39:32'),(3,1,NULL,'103','PIUTANG USAHA','asset','debit','current_asset',0,0,1,'Piutang tagihan invoice pelatihan & sertifikasi ke pelanggan','2026-10-08 22:39:32','2026-10-08 22:39:32'),(4,1,NULL,'104','ASET KANTOR','asset','debit','non_current_asset',0,0,1,'Peralatan kantor, proyektor, komputer, meja','2026-10-08 22:39:32','2026-10-08 22:39:32'),(5,1,NULL,'105','UANG MUKA BIAYA (PANJAR)','asset','debit','current_asset',0,0,1,'Panjar kas operasional kegiatan pelatihan di lapangan','2026-10-08 22:39:32','2026-10-08 22:39:32'),(6,1,NULL,'106','PAJAK DIBAYAR DI MUKA (PPH 23)','asset','debit','current_asset',0,0,1,'Pajak penghasilan pasal 23 yang dipotong oleh pelanggan corporate','2026-10-08 22:39:32','2026-10-08 22:39:32'),(7,1,NULL,'201','UTANG USAHA','liability','credit','current_liability',0,0,1,'Kewajiban kepada vendor hotel, katering, dan ATK','2026-10-08 22:39:32','2026-10-08 22:39:32'),(8,1,NULL,'202','UTANG SERTIFIKASI','liability','credit','current_liability',0,0,1,'Kewajiban tagihan uji kompetensi & blanko sertifikat kepada LSP mitra BNSP','2026-10-08 22:39:32','2026-10-08 22:39:32'),(9,1,NULL,'203','UTANG GAJI','liability','credit','current_liability',0,0,1,'Akrual gaji staf dan honor instruktur yang belum dibayarkan','2026-10-08 22:39:32','2026-10-08 22:39:32'),(10,1,NULL,'204','UTANG PAJAK','liability','credit','current_liability',0,0,1,'Utang PPN, PPh 21 tenaga ahli instruktur, dan PPh 23 vendor','2026-10-08 22:39:32','2026-10-08 22:39:32'),(11,1,NULL,'205','PENDAPATAN DITERIMA DI MUKA','liability','credit','current_liability',0,0,1,'Uang muka pendaftaran batch pelatihan sebelum kegiatan diselenggarakan','2026-10-08 22:39:32','2026-10-08 22:39:32'),(12,1,NULL,'206','UTANG SEWA','liability','credit','current_liability',0,0,1,'Akrual utang sewa kantor','2026-10-08 22:39:32','2026-10-08 22:39:32'),(13,1,NULL,'301','MODAL','equity','credit','equity',0,0,1,'Modal disetor pemilik','2026-10-08 22:39:32','2026-10-08 22:39:32'),(14,1,NULL,'302','SALDO LABA (RETAINED EARNINGS)','equity','credit','equity',0,0,1,'Akumulasi laba atau rugi tahun-tahun sebelumnya','2026-10-08 22:39:32','2026-10-08 22:39:32'),(15,1,NULL,'303','IKHTISAR LABA RUGI','equity','credit','equity',0,0,1,'Akun kliring tutup buku tahunan','2026-10-08 22:39:32','2026-10-08 22:39:32'),(16,1,NULL,'401','PENDAPATAN JASA PELATIHAN','revenue','credit','operating_revenue',0,0,1,'Pendapatan dari paket pelatihan dan sertifikasi kompetensi BNSP','2026-10-08 22:39:32','2026-10-08 22:39:32'),(17,1,NULL,'402','PENDAPATAN JASA KONSULTASI','revenue','credit','operating_revenue',0,0,1,'Pendapatan jasa penyusunan modul, kurikulum, dan konsultasi SKKNI','2026-10-08 22:39:32','2026-10-08 22:39:32'),(18,1,NULL,'403','PENDAPATAN LAIN-LAIN','revenue','credit','other_expense_income',0,0,1,'Bunga bank, jasa giro, dan pendapatan non-operasional lainnya','2026-10-08 22:39:32','2026-10-08 22:39:32'),(19,1,NULL,'503','BIAYA PELATIHAN','expense','debit','direct_cost',0,0,1,'Modul materi, ATK peserta, sertifikat internal, nametag, seragam/rompi','2026-10-08 22:39:32','2026-10-08 22:39:32'),(20,1,NULL,'504','BIAYA SERTIFIKASI','expense','debit','direct_cost',0,0,1,'Biaya pendaftaran asesi dan asesmen uji kompetensi oleh LSP mitra BNSP','2026-10-08 22:39:32','2026-10-08 22:39:32'),(21,1,NULL,'505','BIAYA INSTRUKTUR PELATIHAN','expense','debit','direct_cost',0,0,1,'Honorium / fee narasumber dan instruktur pengajar batch','2026-10-08 22:39:32','2026-10-08 22:39:32'),(22,1,NULL,'514','BIAYA MAKAN & MINUM','expense','debit','direct_cost',0,0,1,'Konsumsi makan siang dan coffee break peserta pelatihan','2026-10-08 22:39:32','2026-10-08 22:39:32'),(23,1,NULL,'515','BIAYA HOTEL','expense','debit','direct_cost',0,0,1,'Sewa ruang meeting hotel (fullboard/fullday) dan kamar penginapan','2026-10-08 22:39:32','2026-10-08 22:39:32'),(24,1,NULL,'501','BIAYA GAJI KARYAWAN','expense','debit','operating_expense',0,0,1,'Gaji tetap staf kantor, marketing, dan manajemen','2026-10-08 22:39:32','2026-10-08 22:39:32'),(25,1,NULL,'502','BIAYA SEWA KANTOR','expense','debit','operating_expense',0,0,1,'Beban sewa gedung kantor operasional','2026-10-08 22:39:32','2026-10-08 22:39:32'),(26,1,NULL,'506','BIAYA PAJAK','expense','debit','operating_expense',0,0,1,'Pajak daerah, retribusi, dan denda pajak jika ada','2026-10-08 22:39:32','2026-10-08 22:39:32'),(27,1,NULL,'507','BIAYA PEMASARAN','expense','debit','operating_expense',0,0,1,'Iklan digital (Google/Meta Ads), brosur, pameran, landing page','2026-10-08 22:39:32','2026-10-08 22:39:32'),(28,1,NULL,'508','BONUS KARYAWAN','expense','debit','operating_expense',0,0,1,'Bonus pencapaian target target batch dan insentif penjualan','2026-10-08 22:39:32','2026-10-08 22:39:32'),(29,1,NULL,'509','BIAYA LISTRIK & INTERNET','expense','debit','operating_expense',0,0,1,'Tagihan PLN, air, dan koneksi internet WiFi kantor','2026-10-08 22:39:32','2026-10-08 22:39:32'),(30,1,NULL,'510','BIAYA ADM BANK','expense','debit','operating_expense',0,0,1,'Biaya administrasi bulanan rekening dan transfer antar bank','2026-10-08 22:39:32','2026-10-08 22:39:32'),(31,1,NULL,'511','BIAYA PERALATAN KANTOR','expense','debit','operating_expense',0,0,1,'Pembelian ATK kantor rutin dan tinta printer','2026-10-08 22:39:32','2026-10-08 22:39:32'),(32,1,NULL,'512','BIAYA DANA SOSIAL','expense','debit','operating_expense',0,0,1,'Kegiatan kepedulian sosial, sumbangan, santunan','2026-10-08 22:39:32','2026-10-08 22:39:32'),(33,1,NULL,'513','BIAYA LAIN-LAIN','expense','debit','operating_expense',0,0,1,'Pengeluaran rupa-rupa operasional umum yang belum terdaftar','2026-10-08 22:39:32','2026-10-08 22:39:32');
/*!40000 ALTER TABLE `accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attachments`
--

DROP TABLE IF EXISTS `attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attachments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned DEFAULT NULL,
  `attachable_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attachable_id` bigint unsigned DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` bigint unsigned NOT NULL DEFAULT '0' COMMENT 'Ukuran dalam byte',
  `caption` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attachments_company_id_foreign` (`company_id`),
  KEY `attachments_attachable_type_attachable_id_index` (`attachable_type`,`attachable_id`),
  KEY `attachments_uploaded_by_index` (`uploaded_by`),
  CONSTRAINT `attachments_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attachments_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attachments`
--

LOCK TABLES `attachments` WRITE;
/*!40000 ALTER TABLE `attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `auditable_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auditable_id` bigint unsigned DEFAULT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'create, update, delete, post, reverse, approve, cancel',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  KEY `audit_logs_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`),
  KEY `audit_logs_company_id_action_index` (`company_id`,`action`),
  KEY `audit_logs_created_at_index` (`created_at`),
  CONSTRAINT `audit_logs_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bank_transactions`
--

DROP TABLE IF EXISTS `bank_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bank_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cash_bank_account_id` bigint unsigned NOT NULL,
  `journal_entry_id` bigint unsigned DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `transaction_type` enum('deposit','withdrawal','transfer_in','transfer_out') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `reference_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_id` bigint unsigned DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_reconciled` tinyint(1) NOT NULL DEFAULT '0',
  `reconciled_at` datetime DEFAULT NULL,
  `reconciled_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bank_transactions_journal_entry_id_foreign` (`journal_entry_id`),
  KEY `bank_transactions_source_type_source_id_index` (`source_type`,`source_id`),
  KEY `bank_transactions_reconciled_by_foreign` (`reconciled_by`),
  KEY `bank_transactions_cash_bank_account_id_transaction_date_index` (`cash_bank_account_id`,`transaction_date`),
  KEY `bank_transactions_cash_bank_account_id_is_reconciled_index` (`cash_bank_account_id`,`is_reconciled`),
  KEY `bank_transactions_transaction_date_index` (`transaction_date`),
  CONSTRAINT `bank_transactions_cash_bank_account_id_foreign` FOREIGN KEY (`cash_bank_account_id`) REFERENCES `cash_bank_accounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bank_transactions_journal_entry_id_foreign` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bank_transactions_reconciled_by_foreign` FOREIGN KEY (`reconciled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bank_transactions`
--

LOCK TABLES `bank_transactions` WRITE;
/*!40000 ALTER TABLE `bank_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `bank_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budget_approvals`
--

DROP TABLE IF EXISTS `budget_approvals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `budget_approvals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `budget_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `action` enum('submitted','approved','rejected','revision_requested') COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `budget_approvals_user_id_foreign` (`user_id`),
  KEY `budget_approvals_budget_id_index` (`budget_id`),
  CONSTRAINT `budget_approvals_budget_id_foreign` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `budget_approvals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_approvals`
--

LOCK TABLES `budget_approvals` WRITE;
/*!40000 ALTER TABLE `budget_approvals` DISABLE KEYS */;
/*!40000 ALTER TABLE `budget_approvals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budget_items`
--

DROP TABLE IF EXISTS `budget_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `budget_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `budget_id` bigint unsigned NOT NULL,
  `account_id` bigint unsigned DEFAULT NULL,
  `category` enum('lsp_certification','trainer_fee','hotel_venue','catering','training_kit_materials','transport_travel','marketing','contingency_other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'training_kit_materials',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT '1.00',
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pax' COMMENT 'pax, hari, paket, org',
  `unit_price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_budget` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `budget_items_account_id_foreign` (`account_id`),
  KEY `budget_items_budget_id_index` (`budget_id`),
  CONSTRAINT `budget_items_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `budget_items_budget_id_foreign` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_items`
--

LOCK TABLES `budget_items` WRITE;
/*!40000 ALTER TABLE `budget_items` DISABLE KEYS */;
INSERT INTO `budget_items` VALUES (1,1,20,'lsp_certification','Biaya Sertifikasi Uji Kompetensi LSP BNSP',12.00,'asesi',1800000.00,21600000.00,'2026-10-08 22:39:33','2026-10-08 22:39:33'),(2,1,21,'trainer_fee','Honor Instruktur K3 (4 Hari)',4.00,'hari',3500000.00,14000000.00,'2026-10-08 22:39:33','2026-10-08 22:39:33'),(3,1,23,'hotel_venue','Paket Meeting Fullboard Hotel Santika',1.00,'paket',7500000.00,7500000.00,'2026-10-08 22:39:33','2026-10-08 22:39:33'),(4,1,19,'training_kit_materials','Bahan Uji, Hardcopy Modul & Perlengkapan K3',12.00,'paket',200000.00,2400000.00,'2026-10-08 22:39:33','2026-10-08 22:39:33');
/*!40000 ALTER TABLE `budget_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budgets`
--

DROP TABLE IF EXISTS `budgets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `budgets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `batch_id` bigint unsigned NOT NULL,
  `budget_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_participants` decimal(8,2) NOT NULL DEFAULT '0.00',
  `target_revenue_per_pax` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_revenue_budget` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_cost_budget` decimal(15,2) NOT NULL DEFAULT '0.00',
  `projected_gross_margin` decimal(15,2) NOT NULL DEFAULT '0.00',
  `projected_margin_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `status` enum('draft','submitted','approved','rejected','revised') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `budgets_company_id_batch_id_unique` (`company_id`,`batch_id`),
  UNIQUE KEY `budgets_company_id_budget_number_unique` (`company_id`,`budget_number`),
  KEY `budgets_batch_id_foreign` (`batch_id`),
  KEY `budgets_created_by_foreign` (`created_by`),
  KEY `budgets_approved_by_foreign` (`approved_by`),
  KEY `budgets_company_id_status_index` (`company_id`,`status`),
  KEY `budgets_budget_number_index` (`budget_number`),
  CONSTRAINT `budgets_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `budgets_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `training_batches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `budgets_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `budgets_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budgets`
--

LOCK TABLES `budgets` WRITE;
/*!40000 ALTER TABLE `budgets` DISABLE KEYS */;
INSERT INTO `budgets` VALUES (1,1,1,'RAB-2026-03-001',12.00,6500000.00,78000000.00,45500000.00,32500000.00,41.67,'approved','RAB Batch 01 disetujui Finance Manager',1,1,'2026-10-09 05:39:33','2026-10-08 22:39:33','2026-10-08 22:39:33');
/*!40000 ALTER TABLE `budgets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cash_bank_accounts`
--

DROP TABLE IF EXISTS `cash_bank_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cash_bank_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `account_id` bigint unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nama identifikasi, misal: Kas Operasional, BCA Giro',
  `account_type` enum('cash','bank','petty_cash') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bank',
  `bank_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_holder` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'IDR',
  `initial_balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `current_balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cash_bank_accounts_company_id_account_id_unique` (`company_id`,`account_id`),
  KEY `cash_bank_accounts_account_id_foreign` (`account_id`),
  CONSTRAINT `cash_bank_accounts_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `cash_bank_accounts_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cash_bank_accounts`
--

LOCK TABLES `cash_bank_accounts` WRITE;
/*!40000 ALTER TABLE `cash_bank_accounts` DISABLE KEYS */;
INSERT INTO `cash_bank_accounts` VALUES (1,1,1,'KAS TUNAI OPERASIONAL (PETTY CASH)','petty_cash','KAS INTERNAL','KAS-01','Kasir Perusahaan','IDR',4500000.00,4500000.00,1,'2026-10-08 22:39:32','2026-10-08 22:39:32'),(2,1,2,'REKENING GIRO BCA OPERASIONAL','bank','BANK CENTRAL ASIA (BCA)','8801239988','PT Optima Mitra Sertifikasi','IDR',327894498.00,74484680.00,1,'2026-10-08 22:39:32','2026-10-08 22:39:32');
/*!40000 ALTER TABLE `cash_bank_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `companies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tax_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'NPWP Badan',
  `address` text COLLATE utf8mb4_unicode_ci,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'IDR',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `companies_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `companies`
--

LOCK TABLES `companies` WRITE;
/*!40000 ALTER TABLE `companies` DISABLE KEYS */;
INSERT INTO `companies` VALUES (1,'PT Optima Mitra Sertifikasi','OPTIMA-ID','01.234.567.8-012.000','Gedung Wisma Pelatihan Lt. 4, Jl. TB Simatupang, Jakarta Selatan','021-78901234','finance@optima-training.co.id','https://optima-training.co.id',NULL,'IDR',1,'2026-10-08 22:39:32','2026-10-08 22:39:32');
/*!40000 ALTER TABLE `companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_payments`
--

DROP TABLE IF EXISTS `customer_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `cash_bank_account_id` bigint unsigned NOT NULL,
  `journal_entry_id` bigint unsigned DEFAULT NULL,
  `payment_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nomor Kwitansi/Kuitansi, misal REC-2026-03-0001',
  `payment_date` date NOT NULL,
  `payment_method` enum('bank_transfer','cash','giro','credit_card') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bank_transfer',
  `amount` decimal(15,2) NOT NULL COMMENT 'Jumlah uang neto yang disetor ke kas/bank',
  `pph23_deducted` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Potongan PPh 23 dibuktikan bukti potong',
  `total_credited` decimal(15,2) NOT NULL COMMENT 'Total pengurang piutang (amount + pph23_deducted)',
  `reference_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'No referensi bank / no bukti transfer',
  `status` enum('draft','posted','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_payments_company_id_payment_number_unique` (`company_id`,`payment_number`),
  KEY `customer_payments_customer_id_foreign` (`customer_id`),
  KEY `customer_payments_cash_bank_account_id_foreign` (`cash_bank_account_id`),
  KEY `customer_payments_journal_entry_id_foreign` (`journal_entry_id`),
  KEY `customer_payments_created_by_foreign` (`created_by`),
  KEY `customer_payments_company_id_customer_id_index` (`company_id`,`customer_id`),
  KEY `customer_payments_company_id_status_index` (`company_id`,`status`),
  KEY `customer_payments_payment_number_index` (`payment_number`),
  KEY `customer_payments_payment_date_index` (`payment_date`),
  CONSTRAINT `customer_payments_cash_bank_account_id_foreign` FOREIGN KEY (`cash_bank_account_id`) REFERENCES `cash_bank_accounts` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `customer_payments_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `customer_payments_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `customer_payments_journal_entry_id_foreign` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_payments`
--

LOCK TABLES `customer_payments` WRITE;
/*!40000 ALTER TABLE `customer_payments` DISABLE KEYS */;
INSERT INTO `customer_payments` VALUES (1,1,1,2,2,'REC-2026-03-0001','2026-03-12','bank_transfer',26000000.00,0.00,26000000.00,'TRF-BCA-9921102','posted','Pembayaran DP 50% Pelatihan K3 Umum Batch 01',1,'2026-10-08 22:39:33','2026-10-08 22:39:33');
/*!40000 ALTER TABLE `customer_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_type` enum('corporate','government','individual','institution') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'corporate',
  `pic_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pic_phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pic_email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'NPWP Klien',
  `address` text COLLATE utf8mb4_unicode_ci,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_term_days` int NOT NULL DEFAULT '30',
  `credit_limit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_company_id_code_unique` (`company_id`,`code`),
  KEY `customers_code_index` (`code`),
  CONSTRAINT `customers_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (1,1,'CUST-001','PT Semen Nusantara Perkasa','corporate','Bambang Trihatmodjo (HR Manager)','081234567890','hrd@semen-nusantara.co.id','01.555.444.3-091.000','Kawasan Industri Cilegon Kav. 45, Banten','Cilegon',30,200000000.00,NULL,1,'2026-10-08 22:39:32','2026-10-08 22:39:32'),(2,1,'CUST-002','Dinas Ketenagakerjaan & Transmigrasi','government','Drs. Agus Subekti, M.Si','081199887766','pelatihan@disnakertrans.go.id','00.123.456.7-001.000','Komplek Balaikota Blok C, Bandung','Bandung',45,350000000.00,NULL,1,'2026-10-08 22:39:32','2026-10-08 22:39:32');
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deletion_requests`
--

DROP TABLE IF EXISTS `deletion_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `deletion_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `requested_by` bigint unsigned NOT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `model_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  `model_label` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nama atau identitas data',
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Alasan penghapusan',
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `actioned_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deletion_requests_company_id_foreign` (`company_id`),
  KEY `deletion_requests_requested_by_foreign` (`requested_by`),
  KEY `deletion_requests_approved_by_foreign` (`approved_by`),
  KEY `deletion_requests_model_type_index` (`model_type`),
  KEY `deletion_requests_model_id_index` (`model_id`),
  KEY `deletion_requests_status_index` (`status`),
  CONSTRAINT `deletion_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `deletion_requests_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `deletion_requests_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deletion_requests`
--

LOCK TABLES `deletion_requests` WRITE;
/*!40000 ALTER TABLE `deletion_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `deletion_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_items`
--

DROP TABLE IF EXISTS `invoice_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint unsigned NOT NULL,
  `account_id` bigint unsigned DEFAULT NULL,
  `item_type` enum('training','certification','assessment','consulting','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'training',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `unit_price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_items_account_id_foreign` (`account_id`),
  KEY `invoice_items_invoice_id_index` (`invoice_id`),
  CONSTRAINT `invoice_items_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoice_items_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_items`
--

LOCK TABLES `invoice_items` WRITE;
/*!40000 ALTER TABLE `invoice_items` DISABLE KEYS */;
INSERT INTO `invoice_items` VALUES (1,1,16,'training','Pelatihan & Sertifikasi Ahli K3 Umum BNSP (8 Peserta)',8,6500000.00,0.00,52000000.00,'2026-10-08 22:39:33','2026-10-08 22:39:33');
/*!40000 ALTER TABLE `invoice_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `program_id` bigint unsigned DEFAULT NULL,
  `batch_id` bigint unsigned DEFAULT NULL,
  `journal_entry_id` bigint unsigned DEFAULT NULL,
  `invoice_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date NOT NULL,
  `po_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'No Purchase Order / Kontrak / SPK',
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `tax_ppn_rate` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT 'Tarif PPN (misal 11%)',
  `tax_ppn_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `tax_pph23_rate` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT 'Potongan PPh 23 jika ada (misal 2%)',
  `tax_pph23_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `down_payment_applied` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Pengurangan DP dari unearned revenue',
  `total_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `paid_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `balance_due` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` enum('draft','pending_approval','issued','partial','paid','overdue','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `terms_conditions` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoices_company_id_invoice_number_unique` (`company_id`,`invoice_number`),
  KEY `invoices_customer_id_foreign` (`customer_id`),
  KEY `invoices_program_id_foreign` (`program_id`),
  KEY `invoices_batch_id_foreign` (`batch_id`),
  KEY `invoices_journal_entry_id_foreign` (`journal_entry_id`),
  KEY `invoices_created_by_foreign` (`created_by`),
  KEY `invoices_approved_by_foreign` (`approved_by`),
  KEY `invoices_company_id_status_index` (`company_id`,`status`),
  KEY `invoices_company_id_customer_id_index` (`company_id`,`customer_id`),
  KEY `invoices_company_id_batch_id_index` (`company_id`,`batch_id`),
  KEY `invoices_due_date_status_index` (`due_date`,`status`),
  KEY `invoices_invoice_number_index` (`invoice_number`),
  KEY `invoices_invoice_date_index` (`invoice_date`),
  KEY `invoices_due_date_index` (`due_date`),
  CONSTRAINT `invoices_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoices_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `training_batches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoices_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoices_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `invoices_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `invoices_journal_entry_id_foreign` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoices_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `training_programs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
INSERT INTO `invoices` VALUES (1,1,1,1,1,1,'INV-2026-03-0001','2026-03-10','2026-04-10','PO/SNP/K3/2026/089',52000000.00,0.00,0.00,0.00,0.00,0.00,0.00,52000000.00,26000000.00,26000000.00,'partial','Tagihan termin 1 (DP 50% telah diterima, sisa H+30 setelah asesmen)',NULL,1,1,'2026-10-09 05:39:33','2026-10-08 22:39:33','2026-10-08 22:39:33');
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `journal_entries`
--

DROP TABLE IF EXISTS `journal_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `journal_entries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `period_id` bigint unsigned NOT NULL,
  `batch_id` bigint unsigned DEFAULT NULL,
  `entry_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nomor unik jurnal, misal JV-2026-03-0001',
  `entry_date` date NOT NULL,
  `reference_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` bigint unsigned DEFAULT NULL,
  `source_module` enum('general','invoice','customer_payment','vendor_bill','vendor_payment','bank_transaction','reversal','closing') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_debit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_credit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` enum('draft','posted','reversed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `posted_at` datetime DEFAULT NULL,
  `posted_by` bigint unsigned DEFAULT NULL,
  `reversed_by_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `journal_entries_company_id_entry_number_unique` (`company_id`,`entry_number`),
  KEY `journal_entries_period_id_foreign` (`period_id`),
  KEY `journal_entries_batch_id_foreign` (`batch_id`),
  KEY `journal_entries_reference_type_reference_id_index` (`reference_type`,`reference_id`),
  KEY `journal_entries_posted_by_foreign` (`posted_by`),
  KEY `journal_entries_reversed_by_id_foreign` (`reversed_by_id`),
  KEY `journal_entries_company_id_entry_date_index` (`company_id`,`entry_date`),
  KEY `journal_entries_company_id_status_index` (`company_id`,`status`),
  KEY `journal_entries_company_id_batch_id_index` (`company_id`,`batch_id`),
  KEY `journal_entries_entry_number_index` (`entry_number`),
  KEY `journal_entries_entry_date_index` (`entry_date`),
  CONSTRAINT `journal_entries_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `training_batches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `journal_entries_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `journal_entries_period_id_foreign` FOREIGN KEY (`period_id`) REFERENCES `accounting_periods` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `journal_entries_posted_by_foreign` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `journal_entries_reversed_by_id_foreign` FOREIGN KEY (`reversed_by_id`) REFERENCES `journal_entries` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journal_entries`
--

LOCK TABLES `journal_entries` WRITE;
/*!40000 ALTER TABLE `journal_entries` DISABLE KEYS */;
INSERT INTO `journal_entries` VALUES (1,1,1,1,'JV-INV-2026-03-0001','2026-03-10','App\\Models\\Invoice',1,'invoice','Pengakuan Piutang Invoice INV-2026-03-0001 - PT Semen Nusantara Perkasa',52000000.00,52000000.00,'posted','2026-10-09 05:39:33',1,NULL,'2026-10-08 22:39:33','2026-10-08 22:39:33'),(2,1,1,1,'JV-REC-2026-03-0001','2026-03-12','App\\Models\\CustomerPayment',1,'customer_payment','Penerimaan Kas/Bank Pembayaran REC-2026-03-0001',26000000.00,26000000.00,'posted','2026-10-09 05:39:33',1,NULL,'2026-10-08 22:39:33','2026-10-08 22:39:33'),(3,1,1,1,'JV-BILL-2026-03-0001','2026-03-15','App\\Models\\VendorBill',1,'vendor_bill','Tagihan Asesmen Sertifikasi LSP BILL-2026-03-0001',21600000.00,21600000.00,'posted','2026-10-09 05:39:33',1,NULL,'2026-10-08 22:39:33','2026-10-08 22:39:33');
/*!40000 ALTER TABLE `journal_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `journal_lines`
--

DROP TABLE IF EXISTS `journal_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `journal_lines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `journal_entry_id` bigint unsigned NOT NULL,
  `account_id` bigint unsigned NOT NULL,
  `debit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `credit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `memo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `journal_lines_journal_entry_id_account_id_index` (`journal_entry_id`,`account_id`),
  KEY `journal_lines_account_id_index` (`account_id`),
  CONSTRAINT `journal_lines_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `journal_lines_journal_entry_id_foreign` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journal_lines`
--

LOCK TABLES `journal_lines` WRITE;
/*!40000 ALTER TABLE `journal_lines` DISABLE KEYS */;
INSERT INTO `journal_lines` VALUES (1,1,3,52000000.00,0.00,'Piutang Invoice INV-2026-03-0001','2026-10-08 22:39:33','2026-10-08 22:39:33'),(2,1,16,0.00,52000000.00,'Pendapatan Jasa Pelatihan Batch 01 K3','2026-10-08 22:39:33','2026-10-08 22:39:33'),(3,2,2,26000000.00,0.00,'Penerimaan Transfer Bank BCA PT Semen Nusantara Perkasa','2026-10-08 22:39:33','2026-10-08 22:39:33'),(4,2,3,0.00,26000000.00,'Pelunasan Sebagian Piutang Invoice INV-2026-03-0001','2026-10-08 22:39:33','2026-10-08 22:39:33'),(5,3,20,21600000.00,0.00,'Biaya Sertifikasi LSP Batch 01 K3','2026-10-08 22:39:33','2026-10-08 22:39:33'),(6,3,8,0.00,21600000.00,'Utang Sertifikasi ke LSP Keselamatan Kerja Nasional (K3)','2026-10-08 22:39:33','2026-10-08 22:39:33');
/*!40000 ALTER TABLE `journal_lines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lsps`
--

DROP TABLE IF EXISTS `lsps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lsps` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bnsp_license_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'No Lisensi BNSP',
  `pic_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pic_phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pic_email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `bank_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account_holder` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lsps_company_id_code_unique` (`company_id`,`code`),
  KEY `lsps_code_index` (`code`),
  CONSTRAINT `lsps_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lsps`
--

LOCK TABLES `lsps` WRITE;
/*!40000 ALTER TABLE `lsps` DISABLE KEYS */;
INSERT INTO `lsps` VALUES (1,1,'LSP-K3-NAS','LSP Keselamatan Kerja Nasional (K3)','BNSP-LSP-045-ID/2022','Ir. Hendra Gunawan','081298765432','admin@lsp-k3nasional.org','Jl. Salemba Raya No. 12, Jakarta Pusat','Bank Mandiri','122-00-9876543-1','LSP Keselamatan Kerja Nasional',1,'2026-10-08 22:39:32','2026-10-08 22:39:32'),(2,1,'LSP-DIGIMA','LSP Digital Marketing Indonesia','BNSP-LSP-112-ID/2023','Anita Wulandari, S.Kom','081388990011','sertifikasi@lsp-digima.id','Gedung Cyber 2 Lt. 8, Kuningan, Jakarta Selatan','Bank BCA','542-019-8811','LSP Digital Marketing Indonesia',1,'2026-10-08 22:39:32','2026-10-08 22:39:32');
/*!40000 ALTER TABLE `lsps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_10_09_023324_create_permission_tables',1),(5,'2026_10_09_030001_create_companies_table',1),(6,'2026_10_09_030002_add_company_and_profile_to_users_table',1),(7,'2026_10_09_030003_create_customers_lsps_vendors_trainers_tables',1),(8,'2026_10_09_030004_create_training_programs_and_batches_tables',1),(9,'2026_10_09_030005_create_accounting_core_tables',1),(10,'2026_10_09_030006_create_cash_and_bank_tables',1),(11,'2026_10_09_030007_create_invoices_and_receivables_tables',1),(12,'2026_10_09_030008_create_vendor_bills_and_payables_tables',1),(13,'2026_10_09_030009_create_budgets_and_rab_tables',1),(14,'2026_10_09_030010_create_audit_logs_and_attachments_tables',1),(15,'2026_10_09_042716_create_deletion_requests_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1),(2,'App\\Models\\User',2),(3,'App\\Models\\User',3),(4,'App\\Models\\User',4),(5,'App\\Models\\User',5);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_allocations`
--

DROP TABLE IF EXISTS `payment_allocations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_allocations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_payment_id` bigint unsigned NOT NULL,
  `invoice_id` bigint unsigned NOT NULL,
  `amount_applied` decimal(15,2) NOT NULL COMMENT 'Nominal pelunasan untuk invoice ini',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_allocations_invoice_id_foreign` (`invoice_id`),
  KEY `payment_allocations_customer_payment_id_invoice_id_index` (`customer_payment_id`,`invoice_id`),
  CONSTRAINT `payment_allocations_customer_payment_id_foreign` FOREIGN KEY (`customer_payment_id`) REFERENCES `customer_payments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payment_allocations_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_allocations`
--

LOCK TABLES `payment_allocations` WRITE;
/*!40000 ALTER TABLE `payment_allocations` DISABLE KEYS */;
INSERT INTO `payment_allocations` VALUES (1,1,1,26000000.00,'2026-10-08 22:39:33','2026-10-08 22:39:33');
/*!40000 ALTER TABLE `payment_allocations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'view-accounts','web','2026-10-08 22:39:30','2026-10-08 22:39:30'),(2,'create-accounts','web','2026-10-08 22:39:30','2026-10-08 22:39:30'),(3,'edit-accounts','web','2026-10-08 22:39:30','2026-10-08 22:39:30'),(4,'delete-accounts','web','2026-10-08 22:39:30','2026-10-08 22:39:30'),(5,'view-journals','web','2026-10-08 22:39:30','2026-10-08 22:39:30'),(6,'create-journals','web','2026-10-08 22:39:30','2026-10-08 22:39:30'),(7,'post-journals','web','2026-10-08 22:39:30','2026-10-08 22:39:30'),(8,'reverse-journals','web','2026-10-08 22:39:30','2026-10-08 22:39:30'),(9,'view-ledger','web','2026-10-08 22:39:30','2026-10-08 22:39:30'),(10,'view-financial-reports','web','2026-10-08 22:39:30','2026-10-08 22:39:30'),(11,'close-period','web','2026-10-08 22:39:30','2026-10-08 22:39:30'),(12,'view-invoices','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(13,'create-invoices','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(14,'edit-invoices','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(15,'approve-invoices','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(16,'delete-invoices','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(17,'record-customer-payments','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(18,'view-customer-payments','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(19,'view-bills','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(20,'create-bills','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(21,'edit-bills','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(22,'approve-bills','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(23,'delete-bills','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(24,'record-vendor-payments','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(25,'view-vendor-payments','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(26,'view-cash-bank','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(27,'manage-cash-bank','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(28,'reconcile-bank','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(29,'view-batches','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(30,'create-batches','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(31,'edit-batches','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(32,'close-batches','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(33,'view-programs','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(34,'create-programs','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(35,'edit-programs','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(36,'delete-programs','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(37,'view-customers','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(38,'create-customers','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(39,'edit-customers','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(40,'view-lsps','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(41,'create-lsps','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(42,'edit-lsps','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(43,'delete-lsps','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(44,'view-vendors','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(45,'create-vendors','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(46,'edit-vendors','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(47,'view-trainers','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(48,'create-trainers','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(49,'edit-trainers','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(50,'delete-trainers','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(51,'view-budgets','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(52,'create-budgets','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(53,'edit-budgets','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(54,'approve-budgets','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(55,'manage-users','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(56,'view-users','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(57,'create-users','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(58,'edit-users','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(59,'delete-users','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(60,'approve-deletion','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(61,'manage-roles','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(62,'view-audit-logs','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(63,'manage-settings','web','2026-10-08 22:39:31','2026-10-08 22:39:31');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(15,1),(16,1),(17,1),(18,1),(19,1),(20,1),(21,1),(22,1),(23,1),(24,1),(25,1),(26,1),(27,1),(28,1),(29,1),(30,1),(31,1),(32,1),(33,1),(34,1),(35,1),(36,1),(37,1),(38,1),(39,1),(40,1),(41,1),(42,1),(43,1),(44,1),(45,1),(46,1),(47,1),(48,1),(49,1),(50,1),(51,1),(52,1),(53,1),(54,1),(55,1),(56,1),(57,1),(58,1),(59,1),(60,1),(61,1),(62,1),(63,1),(1,2),(2,2),(3,2),(5,2),(6,2),(7,2),(8,2),(9,2),(10,2),(11,2),(12,2),(13,2),(15,2),(17,2),(18,2),(19,2),(20,2),(22,2),(24,2),(25,2),(26,2),(27,2),(28,2),(29,2),(33,2),(37,2),(40,2),(44,2),(47,2),(51,2),(54,2),(62,2),(1,3),(5,3),(6,3),(9,3),(10,3),(12,3),(13,3),(17,3),(18,3),(19,3),(20,3),(24,3),(25,3),(26,3),(29,3),(37,3),(40,3),(44,3),(47,3),(51,3),(29,4),(30,4),(31,4),(33,4),(37,4),(38,4),(39,4),(40,4),(47,4),(51,4),(52,4),(53,4),(1,5),(5,5),(9,5),(10,5),(12,5),(18,5),(19,5),(25,5),(26,5),(29,5),(51,5),(62,5);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Super Admin','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(2,'Finance Manager','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(3,'Accountant','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(4,'Operations Coordinator','web','2026-10-08 22:39:31','2026-10-08 22:39:31'),(5,'Auditor','web','2026-10-08 22:39:31','2026-10-08 22:39:31');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trainers`
--

DROP TABLE IF EXISTS `trainers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trainers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `specialization` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'NPWP Instruktur',
  `id_card_number` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'NIK KTP',
  `default_daily_rate` decimal(15,2) NOT NULL DEFAULT '0.00',
  `bank_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account_holder` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `trainers_company_id_code_unique` (`company_id`,`code`),
  KEY `trainers_code_index` (`code`),
  CONSTRAINT `trainers_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trainers`
--

LOCK TABLES `trainers` WRITE;
/*!40000 ALTER TABLE `trainers` DISABLE KEYS */;
INSERT INTO `trainers` VALUES (1,1,'TRN-001','Dr. Ir. Rudi Hartono, M.KKK','Ahli K3 Umum & Higiene Industri BNSP','081122334455','rudi.k3trainer@gmail.com','14.555.666.7-011.000','3171012304750001',3500000.00,'Bank BCA','542-881-2299','Rudi Hartono',1,'2026-10-08 22:39:32','2026-10-08 22:39:32');
/*!40000 ALTER TABLE `trainers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `training_batches`
--

DROP TABLE IF EXISTS `training_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `training_batches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `program_id` bigint unsigned NOT NULL,
  `lsp_id` bigint unsigned DEFAULT NULL,
  `trainer_id` bigint unsigned DEFAULT NULL,
  `pic_user_id` bigint unsigned DEFAULT NULL,
  `batch_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Kode unik angkatan, misal BTH-2026-03-001',
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `assessment_date` date DEFAULT NULL COMMENT 'Tanggal Asesmen BNSP',
  `delivery_type` enum('offline_hotel','offline_inhouse','online','hybrid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'offline_hotel',
  `venue_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `venue_city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_participants` int NOT NULL DEFAULT '10',
  `actual_participants` int NOT NULL DEFAULT '0',
  `price_per_participant` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` enum('draft','confirmed','ongoing','completed','evaluated','closed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `training_batches_company_id_batch_code_unique` (`company_id`,`batch_code`),
  KEY `training_batches_program_id_foreign` (`program_id`),
  KEY `training_batches_lsp_id_foreign` (`lsp_id`),
  KEY `training_batches_trainer_id_foreign` (`trainer_id`),
  KEY `training_batches_pic_user_id_foreign` (`pic_user_id`),
  KEY `training_batches_company_id_status_index` (`company_id`,`status`),
  KEY `training_batches_start_date_end_date_index` (`start_date`,`end_date`),
  KEY `training_batches_batch_code_index` (`batch_code`),
  CONSTRAINT `training_batches_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `training_batches_lsp_id_foreign` FOREIGN KEY (`lsp_id`) REFERENCES `lsps` (`id`) ON DELETE SET NULL,
  CONSTRAINT `training_batches_pic_user_id_foreign` FOREIGN KEY (`pic_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `training_batches_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `training_programs` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `training_batches_trainer_id_foreign` FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `training_batches`
--

LOCK TABLES `training_batches` WRITE;
/*!40000 ALTER TABLE `training_batches` DISABLE KEYS */;
INSERT INTO `training_batches` VALUES (1,1,1,1,1,4,'BTH-2026-03-001','Batch 01 - Sertifikasi Ahli K3 Umum BNSP Maret 2026','2026-03-16','2026-03-19','2026-03-20','offline_hotel','Hotel Santika Premiere Slipi, Jakarta','Jakarta Barat',15,12,6500000.00,'ongoing','Batch berjalan. Klien utama PT Semen Nusantara Perkasa (8 peserta).','2026-10-08 22:39:32','2026-10-08 22:39:32');
/*!40000 ALTER TABLE `training_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `training_programs`
--

DROP TABLE IF EXISTS `training_programs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `training_programs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `lsp_id` bigint unsigned DEFAULT NULL,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `skkni_scheme_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nama Skema BNSP',
  `category` enum('certification','non_certification','consulting','inhouse') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'certification',
  `duration_days` int NOT NULL DEFAULT '2',
  `default_price_per_participant` decimal(15,2) NOT NULL DEFAULT '0.00',
  `default_lsp_cost_per_participant` decimal(15,2) NOT NULL DEFAULT '0.00',
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `training_programs_company_id_code_unique` (`company_id`,`code`),
  KEY `training_programs_lsp_id_foreign` (`lsp_id`),
  KEY `training_programs_code_index` (`code`),
  CONSTRAINT `training_programs_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `training_programs_lsp_id_foreign` FOREIGN KEY (`lsp_id`) REFERENCES `lsps` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `training_programs`
--

LOCK TABLES `training_programs` WRITE;
/*!40000 ALTER TABLE `training_programs` DISABLE KEYS */;
INSERT INTO `training_programs` VALUES (1,1,1,'PRG-K3U','Pelatihan & Sertifikasi Ahli K3 Umum BNSP','Skema Okupasi Ahli K3 Umum Kemenaker / BNSP','certification',4,6500000.00,1800000.00,'Paket pelatihan intensif K3, praktikum asesmen, dan uji kompetensi lisensi BNSP',1,'2026-10-08 22:39:32','2026-10-08 22:39:32');
/*!40000 ALTER TABLE `training_programs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_company_id_foreign` (`company_id`),
  CONSTRAINT `users_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,'Super Administrator','admin@optima.local','081100000001',NULL,1,NULL,'$2y$04$peYAWcmBFCJk/ulvfsPicOiAr9W4ew6Cp99niZu/lvRNwQm6iYHXi',NULL,'2026-10-08 22:39:32','2026-10-08 22:39:32'),(2,1,'Budi Santoso (Finance Manager)','finance.manager@optima.local','081100000002',NULL,1,NULL,'$2y$04$n2LNxfFo0xIJDX4bLtJhf.mlzTGCcROtNvyaQ.62rfFUAdgwMdX4q',NULL,'2026-10-08 22:39:32','2026-10-08 22:39:32'),(3,1,'Siti Rahma (Staff Kasir & Akuntansi)','accountant@optima.local','081100000003',NULL,1,NULL,'$2y$04$ItL/.ttanCOEYC3.F2zu.O/xgED07vgrLOkH9ou7VVmcwRc9VSWEu',NULL,'2026-10-08 22:39:32','2026-10-08 22:39:32'),(4,1,'Dimas Prasetyo (Koordinator Batch)','operations@optima.local','081100000004',NULL,1,NULL,'$2y$04$gUhXayhwe/92I1amc/KCCelSRb8Rq6k8SqD.oc71lypj05fndAHNS',NULL,'2026-10-08 22:39:32','2026-10-08 22:39:32'),(5,1,'Dewi Lestari (Internal Auditor)','auditor@optima.local','081100000005',NULL,1,NULL,'$2y$04$Gg.8OoWeJe1Op83kQFQUk.XDe1rVXNEULf9fu5sd7M2ES3xgKjwQK',NULL,'2026-10-08 22:39:32','2026-10-08 22:39:32');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendor_bill_items`
--

DROP TABLE IF EXISTS `vendor_bill_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendor_bill_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_bill_id` bigint unsigned NOT NULL,
  `account_id` bigint unsigned NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `unit_price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vendor_bill_items_account_id_foreign` (`account_id`),
  KEY `vendor_bill_items_vendor_bill_id_account_id_index` (`vendor_bill_id`,`account_id`),
  CONSTRAINT `vendor_bill_items_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `vendor_bill_items_vendor_bill_id_foreign` FOREIGN KEY (`vendor_bill_id`) REFERENCES `vendor_bills` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendor_bill_items`
--

LOCK TABLES `vendor_bill_items` WRITE;
/*!40000 ALTER TABLE `vendor_bill_items` DISABLE KEYS */;
INSERT INTO `vendor_bill_items` VALUES (1,1,20,'Uji Kompetensi & Sertifikat BNSP Ahli K3 Umum (12 asesi)',12,1800000.00,21600000.00,'2026-10-08 22:39:33','2026-10-08 22:39:33');
/*!40000 ALTER TABLE `vendor_bill_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendor_bills`
--

DROP TABLE IF EXISTS `vendor_bills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendor_bills` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `lsp_id` bigint unsigned DEFAULT NULL,
  `vendor_id` bigint unsigned DEFAULT NULL,
  `trainer_id` bigint unsigned DEFAULT NULL,
  `batch_id` bigint unsigned DEFAULT NULL,
  `journal_entry_id` bigint unsigned DEFAULT NULL,
  `bill_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'No internal tagihan sistem, misal BILL-2026-03-0001',
  `vendor_invoice_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'No faktur/kuitansi asli dari vendor/LSP',
  `bill_date` date NOT NULL,
  `due_date` date NOT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `paid_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `balance_due` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` enum('draft','pending_approval','approved','partial','paid','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vendor_bills_company_id_bill_number_unique` (`company_id`,`bill_number`),
  KEY `vendor_bills_lsp_id_foreign` (`lsp_id`),
  KEY `vendor_bills_vendor_id_foreign` (`vendor_id`),
  KEY `vendor_bills_trainer_id_foreign` (`trainer_id`),
  KEY `vendor_bills_batch_id_foreign` (`batch_id`),
  KEY `vendor_bills_journal_entry_id_foreign` (`journal_entry_id`),
  KEY `vendor_bills_created_by_foreign` (`created_by`),
  KEY `vendor_bills_approved_by_foreign` (`approved_by`),
  KEY `vendor_bills_company_id_status_index` (`company_id`,`status`),
  KEY `vendor_bills_company_id_lsp_id_index` (`company_id`,`lsp_id`),
  KEY `vendor_bills_company_id_vendor_id_index` (`company_id`,`vendor_id`),
  KEY `vendor_bills_company_id_batch_id_index` (`company_id`,`batch_id`),
  KEY `vendor_bills_due_date_status_index` (`due_date`,`status`),
  KEY `vendor_bills_bill_number_index` (`bill_number`),
  KEY `vendor_bills_bill_date_index` (`bill_date`),
  KEY `vendor_bills_due_date_index` (`due_date`),
  CONSTRAINT `vendor_bills_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vendor_bills_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `training_batches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vendor_bills_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `vendor_bills_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `vendor_bills_journal_entry_id_foreign` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vendor_bills_lsp_id_foreign` FOREIGN KEY (`lsp_id`) REFERENCES `lsps` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vendor_bills_trainer_id_foreign` FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vendor_bills_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendor_bills`
--

LOCK TABLES `vendor_bills` WRITE;
/*!40000 ALTER TABLE `vendor_bills` DISABLE KEYS */;
INSERT INTO `vendor_bills` VALUES (1,1,1,NULL,NULL,1,3,'BILL-2026-03-0001','INV/LSP-K3/2026/041','2026-03-15','2026-03-29',21600000.00,0.00,21600000.00,0.00,21600000.00,'approved','Tagihan asesmen uji kompetensi K3 12 asesi',1,1,'2026-10-09 05:39:33','2026-10-08 22:39:33','2026-10-08 22:39:33');
/*!40000 ALTER TABLE `vendor_bills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendor_payment_allocations`
--

DROP TABLE IF EXISTS `vendor_payment_allocations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendor_payment_allocations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_payment_id` bigint unsigned NOT NULL,
  `vendor_bill_id` bigint unsigned NOT NULL,
  `amount_applied` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vendor_payment_allocations_vendor_bill_id_foreign` (`vendor_bill_id`),
  KEY `vpay_alloc_idx` (`vendor_payment_id`,`vendor_bill_id`),
  CONSTRAINT `vendor_payment_allocations_vendor_bill_id_foreign` FOREIGN KEY (`vendor_bill_id`) REFERENCES `vendor_bills` (`id`) ON DELETE CASCADE,
  CONSTRAINT `vendor_payment_allocations_vendor_payment_id_foreign` FOREIGN KEY (`vendor_payment_id`) REFERENCES `vendor_payments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendor_payment_allocations`
--

LOCK TABLES `vendor_payment_allocations` WRITE;
/*!40000 ALTER TABLE `vendor_payment_allocations` DISABLE KEYS */;
/*!40000 ALTER TABLE `vendor_payment_allocations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendor_payments`
--

DROP TABLE IF EXISTS `vendor_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendor_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `lsp_id` bigint unsigned DEFAULT NULL,
  `vendor_id` bigint unsigned DEFAULT NULL,
  `trainer_id` bigint unsigned DEFAULT NULL,
  `cash_bank_account_id` bigint unsigned NOT NULL,
  `journal_entry_id` bigint unsigned DEFAULT NULL,
  `payment_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'No voucher pengeluaran, misal VPY-2026-03-0001',
  `payment_date` date NOT NULL,
  `payment_method` enum('bank_transfer','cash','giro') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bank_transfer',
  `amount` decimal(15,2) NOT NULL COMMENT 'Total dana yang dibayarkan',
  `reference_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'No referensi transfer bank',
  `status` enum('draft','posted','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vendor_payments_company_id_payment_number_unique` (`company_id`,`payment_number`),
  KEY `vendor_payments_lsp_id_foreign` (`lsp_id`),
  KEY `vendor_payments_vendor_id_foreign` (`vendor_id`),
  KEY `vendor_payments_trainer_id_foreign` (`trainer_id`),
  KEY `vendor_payments_cash_bank_account_id_foreign` (`cash_bank_account_id`),
  KEY `vendor_payments_journal_entry_id_foreign` (`journal_entry_id`),
  KEY `vendor_payments_created_by_foreign` (`created_by`),
  KEY `vendor_payments_company_id_status_index` (`company_id`,`status`),
  KEY `vendor_payments_payment_number_index` (`payment_number`),
  KEY `vendor_payments_payment_date_index` (`payment_date`),
  CONSTRAINT `vendor_payments_cash_bank_account_id_foreign` FOREIGN KEY (`cash_bank_account_id`) REFERENCES `cash_bank_accounts` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `vendor_payments_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `vendor_payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `vendor_payments_journal_entry_id_foreign` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vendor_payments_lsp_id_foreign` FOREIGN KEY (`lsp_id`) REFERENCES `lsps` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vendor_payments_trainer_id_foreign` FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vendor_payments_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendor_payments`
--

LOCK TABLES `vendor_payments` WRITE;
/*!40000 ALTER TABLE `vendor_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `vendor_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendors`
--

DROP TABLE IF EXISTS `vendors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` enum('hotel_venue','catering','stationery_printing','transport','it_software','general') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `pic_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pic_phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pic_email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'NPWP Vendor',
  `address` text COLLATE utf8mb4_unicode_ci,
  `bank_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account_holder` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_term_days` int NOT NULL DEFAULT '14',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vendors_company_id_code_unique` (`company_id`,`code`),
  KEY `vendors_code_index` (`code`),
  CONSTRAINT `vendors_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendors`
--

LOCK TABLES `vendors` WRITE;
/*!40000 ALTER TABLE `vendors` DISABLE KEYS */;
INSERT INTO `vendors` VALUES (1,1,'VEND-HTL-01','Hotel Santika Premiere Slipi','hotel_venue','Fiona Sales Banquet','021-5330350','banquet@slipi.santika.com','01.999.888.7-014.000','Jl. Aipda KS Tubun No. 7, Jakarta Barat','Bank BCA','001-229-8765','PT Santika Graha Slipi',14,1,'2026-10-08 22:39:32','2026-10-08 22:39:32'),(2,1,'VEND-ATK-01','CV Grafika Mandiri Printing','stationery_printing','Rahmat Hidayat','081277665544','order@grafikamandiri.com','02.333.444.5-015.000','Jl. Percetakan Negara No. 88, Jakarta Pusat','Bank Mandiri','119-00-554433-2','CV Grafika Mandiri',14,1,'2026-10-08 22:39:32','2026-10-08 22:39:32');
/*!40000 ALTER TABLE `vendors` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-09 13:07:25
