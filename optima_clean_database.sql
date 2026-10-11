-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: optima_finance
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
INSERT INTO `accounting_periods` VALUES (1,1,'Tahun Buku 2026','2026-01-01','2026-12-31',0,NULL,NULL,'Periode akuntansi tahun berjalan 2026','2026-10-08 22:37:50','2026-10-08 22:37:50'),(2,1,'Januari 2026','2026-01-01','2026-01-31',1,NULL,NULL,'Periode tutup buku Januari 2026','2026-10-08 22:37:50','2026-10-08 22:37:50'),(3,1,'Februari 2026','2026-02-01','2026-02-28',1,NULL,NULL,'Periode tutup buku Februari 2026','2026-10-08 22:37:50','2026-10-08 22:37:50'),(4,1,'Maret 2026','2026-03-01','2026-03-31',0,NULL,NULL,'Periode aktif berjalan Maret 2026','2026-10-08 22:37:51','2026-10-08 22:37:51');
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
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounts`
--

LOCK TABLES `accounts` WRITE;
/*!40000 ALTER TABLE `accounts` DISABLE KEYS */;
INSERT INTO `accounts` VALUES (1,1,NULL,'101','KAS','asset','debit','current_asset',1,1,1,'Kas Tunai / Kas Kecil (Petty Cash)','2026-10-08 22:37:50','2026-10-08 22:37:50'),(2,1,NULL,'102','KAS BANK BCA','asset','debit','current_asset',1,1,1,'Rekening Giro/Tabungan Utama BCA Operasional','2026-10-08 22:37:50','2026-10-08 22:37:50'),(3,1,NULL,'103','PIUTANG USAHA','asset','debit','current_asset',0,0,1,'Piutang tagihan invoice pelatihan & sertifikasi ke pelanggan','2026-10-08 22:37:50','2026-10-08 22:37:50'),(4,1,NULL,'104','ASET KANTOR','asset','debit','non_current_asset',0,0,1,'Peralatan kantor, proyektor, komputer, meja','2026-10-08 22:37:50','2026-10-08 22:37:50'),(5,1,NULL,'105','UANG MUKA BIAYA (PANJAR)','asset','debit','current_asset',0,0,1,'Panjar kas operasional kegiatan pelatihan di lapangan','2026-10-08 22:37:50','2026-10-08 22:37:50'),(6,1,NULL,'106','PAJAK DIBAYAR DI MUKA (PPH 23)','asset','debit','current_asset',0,0,1,'Pajak penghasilan pasal 23 yang dipotong oleh pelanggan corporate','2026-10-08 22:37:50','2026-10-08 22:37:50'),(7,1,NULL,'201','UTANG USAHA','liability','credit','current_liability',0,0,1,'Kewajiban kepada vendor hotel, katering, dan ATK','2026-10-08 22:37:50','2026-10-08 22:37:50'),(8,1,NULL,'202','UTANG SERTIFIKASI','liability','credit','current_liability',0,0,1,'Kewajiban tagihan uji kompetensi & blanko sertifikat kepada LSP mitra BNSP','2026-10-08 22:37:50','2026-10-08 22:37:50'),(9,1,NULL,'203','UTANG GAJI','liability','credit','current_liability',0,0,1,'Akrual gaji staf dan honor instruktur yang belum dibayarkan','2026-10-08 22:37:50','2026-10-08 22:37:50'),(10,1,NULL,'204','UTANG PAJAK','liability','credit','current_liability',0,0,1,'Utang PPN, PPh 21 tenaga ahli instruktur, dan PPh 23 vendor','2026-10-08 22:37:50','2026-10-08 22:37:50'),(11,1,NULL,'205','PENDAPATAN DITERIMA DI MUKA','liability','credit','current_liability',0,0,1,'Uang muka pendaftaran batch pelatihan sebelum kegiatan diselenggarakan','2026-10-08 22:37:50','2026-10-08 22:37:50'),(12,1,NULL,'206','UTANG SEWA','liability','credit','current_liability',0,0,1,'Akrual utang sewa kantor','2026-10-08 22:37:50','2026-10-08 22:37:50'),(13,1,NULL,'301','MODAL','equity','credit','equity',0,0,1,'Modal disetor pemilik','2026-10-08 22:37:50','2026-10-08 22:37:50'),(14,1,NULL,'302','SALDO LABA (RETAINED EARNINGS)','equity','credit','equity',0,0,1,'Akumulasi laba atau rugi tahun-tahun sebelumnya','2026-10-08 22:37:50','2026-10-08 22:37:50'),(15,1,NULL,'303','IKHTISAR LABA RUGI','equity','credit','equity',0,0,1,'Akun kliring tutup buku tahunan','2026-10-08 22:37:50','2026-10-08 22:37:50'),(16,1,NULL,'401','PENDAPATAN JASA PELATIHAN','revenue','credit','operating_revenue',0,0,1,'Pendapatan dari paket pelatihan dan sertifikasi kompetensi BNSP','2026-10-08 22:37:50','2026-10-08 22:37:50'),(17,1,NULL,'402','PENDAPATAN JASA KONSULTASI','revenue','credit','operating_revenue',0,0,1,'Pendapatan jasa penyusunan modul, kurikulum, dan konsultasi SKKNI','2026-10-08 22:37:50','2026-10-08 22:37:50'),(18,1,NULL,'403','PENDAPATAN LAIN-LAIN','revenue','credit','other_expense_income',0,0,1,'Bunga bank, jasa giro, dan pendapatan non-operasional lainnya','2026-10-08 22:37:50','2026-10-08 22:37:50'),(19,1,NULL,'503','BIAYA PELATIHAN','expense','debit','direct_cost',0,0,1,'Modul materi, ATK peserta, sertifikat internal, nametag, seragam/rompi','2026-10-08 22:37:50','2026-10-08 22:37:50'),(20,1,NULL,'504','BIAYA SERTIFIKASI','expense','debit','direct_cost',0,0,1,'Biaya pendaftaran asesi dan asesmen uji kompetensi oleh LSP mitra BNSP','2026-10-08 22:37:50','2026-10-08 22:37:50'),(21,1,NULL,'505','BIAYA INSTRUKTUR PELATIHAN','expense','debit','direct_cost',0,0,1,'Honorium / fee narasumber dan instruktur pengajar batch','2026-10-08 22:37:50','2026-10-08 22:37:50'),(22,1,NULL,'514','BIAYA MAKAN & MINUM','expense','debit','direct_cost',0,0,1,'Konsumsi makan siang dan coffee break peserta pelatihan','2026-10-08 22:37:50','2026-10-08 22:37:50'),(23,1,NULL,'515','BIAYA HOTEL','expense','debit','direct_cost',0,0,1,'Sewa ruang meeting hotel (fullboard/fullday) dan kamar penginapan','2026-10-08 22:37:50','2026-10-08 22:37:50'),(24,1,NULL,'501','BIAYA GAJI KARYAWAN','expense','debit','operating_expense',0,0,1,'Gaji tetap staf kantor, marketing, dan manajemen','2026-10-08 22:37:50','2026-10-08 22:37:50'),(25,1,NULL,'502','BIAYA SEWA KANTOR','expense','debit','operating_expense',0,0,1,'Beban sewa gedung kantor operasional','2026-10-08 22:37:50','2026-10-08 22:37:50'),(26,1,NULL,'506','BIAYA PAJAK','expense','debit','operating_expense',0,0,1,'Pajak daerah, retribusi, dan denda pajak jika ada','2026-10-08 22:37:50','2026-10-08 22:37:50'),(27,1,NULL,'507','BIAYA PEMASARAN','expense','debit','operating_expense',0,0,1,'Iklan digital (Google/Meta Ads), brosur, pameran, landing page','2026-10-08 22:37:50','2026-10-08 22:37:50'),(28,1,NULL,'508','BONUS KARYAWAN','expense','debit','operating_expense',0,0,1,'Bonus pencapaian target target batch dan insentif penjualan','2026-10-08 22:37:50','2026-10-08 22:37:50'),(29,1,NULL,'509','BIAYA LISTRIK & INTERNET','expense','debit','operating_expense',0,0,1,'Tagihan PLN, air, dan koneksi internet WiFi kantor','2026-10-08 22:37:50','2026-10-08 22:37:50'),(30,1,NULL,'510','BIAYA ADM BANK','expense','debit','operating_expense',0,0,1,'Biaya administrasi bulanan rekening dan transfer antar bank','2026-10-08 22:37:50','2026-10-08 22:37:50'),(31,1,NULL,'511','BIAYA PERALATAN KANTOR','expense','debit','operating_expense',0,0,1,'Pembelian ATK kantor rutin dan tinta printer','2026-10-08 22:37:50','2026-10-08 22:37:50'),(32,1,NULL,'512','BIAYA DANA SOSIAL','expense','debit','operating_expense',0,0,1,'Kegiatan kepedulian sosial, sumbangan, santunan','2026-10-08 22:37:50','2026-10-08 22:37:50'),(33,1,NULL,'513','BIAYA LAIN-LAIN','expense','debit','operating_expense',0,0,1,'Pengeluaran rupa-rupa operasional umum yang belum terdaftar','2026-10-08 22:37:50','2026-10-08 22:37:50');
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
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,1,1,'App\\Models\\User',1,'login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,NULL,'User logged in successfully','2026-10-08 22:38:39'),(2,1,1,'App\\Models\\JournalEntry',4,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"929527.00\", \"entry_number\": \"JV-2026-01-0001\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0001: Cicilan Laptop kantor','2026-10-08 22:43:02'),(3,1,1,'App\\Models\\JournalEntry',5,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2500.00\", \"entry_number\": \"JV-2026-01-0002\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0002: adm bank','2026-10-08 22:43:02'),(4,1,1,'App\\Models\\JournalEntry',6,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"1000000.00\", \"entry_number\": \"JV-2026-01-0003\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0003: Petycash januari','2026-10-08 22:43:02'),(5,1,1,'App\\Models\\JournalEntry',7,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"332000.00\", \"entry_number\": \"JV-2026-01-0004\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0004: PAM','2026-10-08 22:43:02'),(6,1,1,'App\\Models\\JournalEntry',8,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"150000000.00\", \"entry_number\": \"JV-2026-01-0005\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0005: Cicil Hutang HAKE','2026-10-08 22:43:02'),(7,1,1,'App\\Models\\JournalEntry',9,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2500.00\", \"entry_number\": \"JV-2026-01-0006\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0006: adm bank','2026-10-08 22:43:02'),(8,1,1,'App\\Models\\JournalEntry',10,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"10000000.00\", \"entry_number\": \"JV-2026-01-0007\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0007: BOD','2026-10-08 22:43:02'),(9,1,1,'App\\Models\\JournalEntry',11,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2500.00\", \"entry_number\": \"JV-2026-01-0008\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0008: adm bank','2026-10-08 22:43:02'),(10,1,1,'App\\Models\\JournalEntry',12,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"9000000.00\", \"entry_number\": \"JV-2026-01-0009\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0009: Fee Narsum','2026-10-08 22:43:02'),(11,1,1,'App\\Models\\JournalEntry',13,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2500.00\", \"entry_number\": \"JV-2026-01-0010\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0010: adm bank','2026-10-08 22:43:02'),(12,1,1,'App\\Models\\JournalEntry',14,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"506400.00\", \"entry_number\": \"JV-2026-01-0011\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0011: Mesin Absen Fingerprint','2026-10-08 22:43:02'),(13,1,1,'App\\Models\\JournalEntry',15,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"14210000.00\", \"entry_number\": \"JV-2026-01-0012\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0012: Pembayaran Pelatihan','2026-10-08 22:43:02'),(14,1,1,'App\\Models\\JournalEntry',16,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"933225.00\", \"entry_number\": \"JV-2026-01-0013\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0013: Token & Internet','2026-10-08 22:43:02'),(15,1,1,'App\\Models\\JournalEntry',17,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2000000.00\", \"entry_number\": \"JV-2026-01-0014\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0014: Cashback SUJ','2026-10-08 22:43:02'),(16,1,1,'App\\Models\\JournalEntry',18,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"1000000.00\", \"entry_number\": \"JV-2026-01-0015\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0015: Cashback MTI','2026-10-08 22:43:02'),(17,1,1,'App\\Models\\JournalEntry',19,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"500000.00\", \"entry_number\": \"JV-2026-01-0016\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0016: Cashback','2026-10-08 22:43:02'),(18,1,1,'App\\Models\\JournalEntry',20,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2500.00\", \"entry_number\": \"JV-2026-01-0017\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0017: adm bank','2026-10-08 22:43:02'),(19,1,1,'App\\Models\\JournalEntry',21,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"500000.00\", \"entry_number\": \"JV-2026-01-0018\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0018: Tambahan Petycash','2026-10-08 22:43:02'),(20,1,1,'App\\Models\\JournalEntry',22,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"7913261.00\", \"entry_number\": \"JV-2026-01-0019\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0019: Tiket pesawat','2026-10-08 22:43:03'),(21,1,1,'App\\Models\\JournalEntry',23,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"1889469.00\", \"entry_number\": \"JV-2026-01-0020\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0020: Tiket pesawat Adm','2026-10-08 22:43:03'),(22,1,1,'App\\Models\\JournalEntry',24,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"1901801.00\", \"entry_number\": \"JV-2026-01-0021\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0021: Tiket pesawat Adm','2026-10-08 22:43:03'),(23,1,1,'App\\Models\\JournalEntry',25,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"12398500.00\", \"entry_number\": \"JV-2026-01-0022\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0022: Gaji Januari','2026-10-08 22:43:03'),(24,1,1,'App\\Models\\JournalEntry',26,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"5000.00\", \"entry_number\": \"JV-2026-01-0023\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0023: Biaya admi','2026-10-08 22:43:03'),(25,1,1,'App\\Models\\JournalEntry',27,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"121000.00\", \"entry_number\": \"JV-2026-01-0024\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0024: Biaya Iklan','2026-10-08 22:43:03'),(26,1,1,'App\\Models\\JournalEntry',28,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2520000.00\", \"entry_number\": \"JV-2026-01-0025\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0025: ATK & Training Kit','2026-10-08 22:43:03'),(27,1,1,'App\\Models\\JournalEntry',29,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"13500000.00\", \"entry_number\": \"JV-2026-01-0026\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0026: Pendapatan Pelatihan','2026-10-08 22:43:03'),(28,1,1,'App\\Models\\JournalEntry',30,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"45000.00\", \"entry_number\": \"JV-2026-01-0027\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0027: Kuota Hp Kantor','2026-10-08 22:43:03'),(29,1,1,'App\\Models\\JournalEntry',31,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"39687500.00\", \"entry_number\": \"JV-2026-01-0028\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0028: Pendaptan pelatihan','2026-10-08 22:43:03'),(30,1,1,'App\\Models\\JournalEntry',32,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"1275000.00\", \"entry_number\": \"JV-2026-01-0029\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0029: Hotel','2026-10-08 22:43:03'),(31,1,1,'App\\Models\\JournalEntry',33,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"6000000.00\", \"entry_number\": \"JV-2026-01-0030\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0030: Hotel','2026-10-08 22:43:03'),(32,1,1,'App\\Models\\JournalEntry',34,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2500.00\", \"entry_number\": \"JV-2026-01-0031\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0031: Biaya Adm','2026-10-08 22:43:03'),(33,1,1,'App\\Models\\JournalEntry',35,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"1420000.00\", \"entry_number\": \"JV-2026-01-0032\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0032: ATK Fee Cofas 50%','2026-10-08 22:43:03'),(34,1,1,'App\\Models\\JournalEntry',36,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"500000.00\", \"entry_number\": \"JV-2026-01-0033\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0033: Casback Rfki','2026-10-08 22:43:03'),(35,1,1,'App\\Models\\JournalEntry',37,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2500.00\", \"entry_number\": \"JV-2026-01-0034\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0034: Biaya Adm','2026-10-08 22:43:03'),(36,1,1,'App\\Models\\JournalEntry',38,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"13717100.00\", \"entry_number\": \"JV-2026-01-0035\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0035: Pendaptan pelatihan','2026-10-08 22:43:03'),(37,1,1,'App\\Models\\JournalEntry',39,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"3375000.00\", \"entry_number\": \"JV-2026-01-0036\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0036: Biaya Hotel','2026-10-08 22:43:03'),(38,1,1,'App\\Models\\JournalEntry',40,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2500.00\", \"entry_number\": \"JV-2026-01-0037\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0037: Biaya Adm','2026-10-08 22:43:03'),(39,1,1,'App\\Models\\JournalEntry',41,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"1929527.00\", \"entry_number\": \"JV-2026-01-0038\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0038: Subsidi Hotel & Cicilan Laptop 2','2026-10-08 22:43:03'),(40,1,1,'App\\Models\\JournalEntry',42,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2500.00\", \"entry_number\": \"JV-2026-01-0039\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0039: Biaya Adm','2026-10-08 22:43:03'),(41,1,1,'App\\Models\\JournalEntry',43,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"30000.00\", \"entry_number\": \"JV-2026-01-0040\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-01-0040: Biaya Adm','2026-10-08 22:43:03'),(42,1,1,'App\\Models\\JournalEntry',44,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"15000000.00\", \"entry_number\": \"JV-2026-02-0001\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0001: Pembayaran Sertifikasi','2026-10-08 22:43:03'),(43,1,1,'App\\Models\\JournalEntry',45,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2500.00\", \"entry_number\": \"JV-2026-02-0002\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0002: Biaya Adm','2026-10-08 22:43:03'),(44,1,1,'App\\Models\\JournalEntry',46,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2736000.00\", \"entry_number\": \"JV-2026-02-0003\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0003: Hotel Ayani Aceh','2026-10-08 22:43:03'),(45,1,1,'App\\Models\\JournalEntry',47,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2500.00\", \"entry_number\": \"JV-2026-02-0004\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0004: Biaya Adm','2026-10-08 22:43:03'),(46,1,1,'App\\Models\\JournalEntry',48,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"242000.00\", \"entry_number\": \"JV-2026-02-0005\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0005: Reimbush Canva & Member Parkir','2026-10-08 22:43:03'),(47,1,1,'App\\Models\\JournalEntry',49,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2500.00\", \"entry_number\": \"JV-2026-02-0006\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0006: Biaya Adm','2026-10-08 22:43:03'),(48,1,1,'App\\Models\\JournalEntry',50,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"9000000.00\", \"entry_number\": \"JV-2026-02-0007\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0007: Fee instruktur','2026-10-08 22:43:03'),(49,1,1,'App\\Models\\JournalEntry',51,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2043530.00\", \"entry_number\": \"JV-2026-02-0008\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0008: Tiket Pulang Instruktur','2026-10-08 22:43:03'),(50,1,1,'App\\Models\\JournalEntry',52,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2500.00\", \"entry_number\": \"JV-2026-02-0009\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0009: Biaya Adm','2026-10-08 22:43:03'),(51,1,1,'App\\Models\\JournalEntry',53,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2465500.00\", \"entry_number\": \"JV-2026-02-0010\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0010: Operasional Indodepp','2026-10-08 22:43:03'),(52,1,1,'App\\Models\\JournalEntry',54,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"1500000.00\", \"entry_number\": \"JV-2026-02-0011\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0011: Pengganti Hotel IFah','2026-10-08 22:43:03'),(53,1,1,'App\\Models\\JournalEntry',55,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"215100.00\", \"entry_number\": \"JV-2026-02-0012\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0012: Reimbush Transport','2026-10-08 22:43:03'),(54,1,1,'App\\Models\\JournalEntry',56,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2500.00\", \"entry_number\": \"JV-2026-02-0013\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0013: Biaya Adm','2026-10-08 22:43:03'),(55,1,1,'App\\Models\\JournalEntry',57,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"49800000.00\", \"entry_number\": \"JV-2026-02-0014\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0014: DP Indodepp','2026-10-08 22:43:03'),(56,1,1,'App\\Models\\JournalEntry',58,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"4740000.00\", \"entry_number\": \"JV-2026-02-0015\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0015: Biaya Makan & Minum','2026-10-08 22:43:03'),(57,1,1,'App\\Models\\JournalEntry',59,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2500.00\", \"entry_number\": \"JV-2026-02-0016\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0016: Biaya Adm','2026-10-08 22:43:03'),(58,1,1,'App\\Models\\JournalEntry',60,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"1000000.00\", \"entry_number\": \"JV-2026-02-0017\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0017: Petycash Februari','2026-10-08 22:43:03'),(59,1,1,'App\\Models\\JournalEntry',61,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"568475.00\", \"entry_number\": \"JV-2026-02-0018\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0018: Pam & Internet','2026-10-08 22:43:03'),(60,1,1,'App\\Models\\JournalEntry',62,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"500000.00\", \"entry_number\": \"JV-2026-02-0019\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0019: Kasbon Tria','2026-10-08 22:43:03'),(61,1,1,'App\\Models\\JournalEntry',63,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"1653001.00\", \"entry_number\": \"JV-2026-02-0020\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0020: Tiket Pesawat','2026-10-08 22:43:03'),(62,1,1,'App\\Models\\JournalEntry',64,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"7500000.00\", \"entry_number\": \"JV-2026-02-0021\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0021: Fee Narsum','2026-10-08 22:43:03'),(63,1,1,'App\\Models\\JournalEntry',65,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"3620000.00\", \"entry_number\": \"JV-2026-02-0022\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0022: Gaji Februari','2026-10-08 22:43:03'),(64,1,1,'App\\Models\\JournalEntry',66,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2500.00\", \"entry_number\": \"JV-2026-02-0023\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0023: Biaya Adm','2026-10-08 22:43:03'),(65,1,1,'App\\Models\\JournalEntry',67,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"1000000.00\", \"entry_number\": \"JV-2026-02-0024\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0024: Casback','2026-10-08 22:43:03'),(66,1,1,'App\\Models\\JournalEntry',68,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2500.00\", \"entry_number\": \"JV-2026-02-0025\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0025: Biaya Adm','2026-10-08 22:43:03'),(67,1,1,'App\\Models\\JournalEntry',69,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2000000.00\", \"entry_number\": \"JV-2026-02-0026\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0026: Fee Narsum Indodepp','2026-10-08 22:43:03'),(68,1,1,'App\\Models\\JournalEntry',70,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2000000.00\", \"entry_number\": \"JV-2026-02-0027\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0027: Fee Narsum Indodepp','2026-10-08 22:43:03'),(69,1,1,'App\\Models\\JournalEntry',71,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2500.00\", \"entry_number\": \"JV-2026-02-0028\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0028: Biaya Adm','2026-10-08 22:43:04'),(70,1,1,'App\\Models\\JournalEntry',72,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"729000.00\", \"entry_number\": \"JV-2026-02-0029\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0029: Iklan Google','2026-10-08 22:43:04'),(71,1,1,'App\\Models\\JournalEntry',73,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"15000000.00\", \"entry_number\": \"JV-2026-02-0030\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0030: BOD','2026-10-08 22:43:04'),(72,1,1,'App\\Models\\JournalEntry',74,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2500.00\", \"entry_number\": \"JV-2026-02-0031\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0031: Biaya Adm','2026-10-08 22:43:04'),(73,1,1,'App\\Models\\JournalEntry',75,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"30000.00\", \"entry_number\": \"JV-2026-02-0032\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-02-0032: Biaya Adm','2026-10-08 22:43:04'),(74,1,1,'App\\Models\\JournalEntry',76,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"1650000.00\", \"entry_number\": \"JV-2026-03-0001\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-03-0001: Gaji Ifah','2026-10-08 22:43:04'),(75,1,1,'App\\Models\\JournalEntry',77,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"141000.00\", \"entry_number\": \"JV-2026-03-0002\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-03-0002: PAM','2026-10-08 22:43:04'),(76,1,1,'App\\Models\\JournalEntry',78,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"8660000.00\", \"entry_number\": \"JV-2026-03-0003\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-03-0003: Hotel & meet room','2026-10-08 22:43:04'),(77,1,1,'App\\Models\\JournalEntry',79,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2500.00\", \"entry_number\": \"JV-2026-03-0004\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-03-0004: Biaya Adm','2026-10-08 22:43:04'),(78,1,1,'App\\Models\\JournalEntry',80,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"1780000.00\", \"entry_number\": \"JV-2026-03-0005\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-03-0005: Mandays ae & Persekot ae','2026-10-08 22:43:04'),(79,1,1,'App\\Models\\JournalEntry',81,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"330000.00\", \"entry_number\": \"JV-2026-03-0006\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-03-0006: Persekot proper & Mandays proper','2026-10-08 22:43:04'),(80,1,1,'App\\Models\\JournalEntry',82,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"3000000.00\", \"entry_number\": \"JV-2026-03-0007\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-03-0007: Fee Instruktur','2026-10-08 22:43:04'),(81,1,1,'App\\Models\\JournalEntry',83,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2500.00\", \"entry_number\": \"JV-2026-03-0008\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-03-0008: Biaya Adm','2026-10-08 22:43:04'),(82,1,1,'App\\Models\\JournalEntry',84,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"12000000.00\", \"entry_number\": \"JV-2026-03-0009\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-03-0009: THR BOD','2026-10-08 22:43:04'),(83,1,1,'App\\Models\\JournalEntry',85,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"5000.00\", \"entry_number\": \"JV-2026-03-0010\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-03-0010: Biaya Adm','2026-10-08 22:43:04'),(84,1,1,'App\\Models\\JournalEntry',86,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"30000000.00\", \"entry_number\": \"JV-2026-03-0011\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-03-0011: Bayar Sertifikasi','2026-10-08 22:43:04'),(85,1,1,'App\\Models\\JournalEntry',87,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2500.00\", \"entry_number\": \"JV-2026-03-0012\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-03-0012: Biaya Adm','2026-10-08 22:43:04'),(86,1,1,'App\\Models\\JournalEntry',88,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"16500000.00\", \"entry_number\": \"JV-2026-03-0013\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-03-0013: Bayar Sertifikasi RTI','2026-10-08 22:43:04'),(87,1,1,'App\\Models\\JournalEntry',89,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"929527.00\", \"entry_number\": \"JV-2026-03-0014\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-03-0014: Cicilan Laptop','2026-10-08 22:43:04'),(88,1,1,'App\\Models\\JournalEntry',90,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2500.00\", \"entry_number\": \"JV-2026-03-0015\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-03-0015: Biaya Adm','2026-10-08 22:43:04'),(89,1,1,'App\\Models\\JournalEntry',91,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"4000000.00\", \"entry_number\": \"JV-2026-03-0016\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-03-0016: Dp Perpanjang ISO','2026-10-08 22:43:04'),(90,1,1,'App\\Models\\JournalEntry',92,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2500.00\", \"entry_number\": \"JV-2026-03-0017\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-03-0017: Biaya Adm','2026-10-08 22:43:04'),(91,1,1,'App\\Models\\JournalEntry',93,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"412975.00\", \"entry_number\": \"JV-2026-03-0018\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-03-0018: Internet Kantor','2026-10-08 22:43:04'),(92,1,1,'App\\Models\\JournalEntry',94,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"1600000.00\", \"entry_number\": \"JV-2026-03-0019\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-03-0019: THR Tria','2026-10-08 22:43:04'),(93,1,1,'App\\Models\\JournalEntry',95,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"1000000.00\", \"entry_number\": \"JV-2026-03-0020\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-03-0020: Petycash Maret','2026-10-08 22:43:04'),(94,1,1,'App\\Models\\JournalEntry',96,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"13500000.00\", \"entry_number\": \"JV-2026-03-0021\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-03-0021: Pemasukan Pelatihan','2026-10-08 22:43:04'),(95,1,1,'App\\Models\\JournalEntry',97,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"9000000.00\", \"entry_number\": \"JV-2026-03-0022\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-03-0022: Fee Instruktur','2026-10-08 22:43:04'),(96,1,1,'App\\Models\\JournalEntry',98,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2500.00\", \"entry_number\": \"JV-2026-03-0023\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-03-0023: Biaya Adm','2026-10-08 22:43:04'),(97,1,1,'App\\Models\\JournalEntry',99,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"3300000.00\", \"entry_number\": \"JV-2026-03-0024\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-03-0024: Gaji Maret','2026-10-08 22:43:04'),(98,1,1,'App\\Models\\JournalEntry',100,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"2500.00\", \"entry_number\": \"JV-2026-03-0025\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-03-0025: Biaya Adm','2026-10-08 22:43:04'),(99,1,1,'App\\Models\\JournalEntry',101,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"352000.00\", \"entry_number\": \"JV-2026-03-0026\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-03-0026: Iklan Google','2026-10-08 22:43:04'),(100,1,1,'App\\Models\\JournalEntry',102,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"1000000.00\", \"entry_number\": \"JV-2026-03-0027\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-03-0027: Petycash April','2026-10-08 22:43:04'),(101,1,1,'App\\Models\\JournalEntry',103,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"501600.00\", \"entry_number\": \"JV-2026-03-0028\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-03-0028: Token Listrik','2026-10-08 22:43:04'),(102,1,1,'App\\Models\\JournalEntry',104,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"10000000.00\", \"entry_number\": \"JV-2026-03-0029\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-03-0029: Pembayaran Sertifikasi','2026-10-08 22:43:04'),(103,1,1,'App\\Models\\JournalEntry',105,'post_journal','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total\": \"30000.00\", \"entry_number\": \"JV-2026-03-0030\", \"source_module\": \"general\"}','Posting Jurnal JV-2026-03-0030: Biaya Adm','2026-10-08 22:43:04'),(104,1,1,NULL,NULL,'bulk_import','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,'{\"total_amount\": 542239018, \"journals_count\": 102}','Bulk import 102 jurnal umum berhasil dilakukan oleh Super Administrator. Total mutasi: Rp 542.239.018','2026-10-08 22:43:04'),(105,1,1,'App\\Models\\User',1,'login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,NULL,'User logged in successfully','2026-10-10 21:38:33'),(106,1,4,'App\\Models\\User',4,'login','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',NULL,NULL,'User logged in successfully','2026-10-10 22:08:41');
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_items`
--

LOCK TABLES `budget_items` WRITE;
/*!40000 ALTER TABLE `budget_items` DISABLE KEYS */;
INSERT INTO `budget_items` VALUES (1,1,20,'lsp_certification','Biaya Sertifikasi Uji Kompetensi LSP BNSP',12.00,'asesi',1800000.00,21600000.00,'2026-10-08 22:37:51','2026-10-08 22:37:51'),(2,1,21,'trainer_fee','Honor Instruktur K3 (4 Hari)',4.00,'hari',3500000.00,14000000.00,'2026-10-08 22:37:51','2026-10-08 22:37:51'),(3,1,23,'hotel_venue','Paket Meeting Fullboard Hotel Santika',1.00,'paket',7500000.00,7500000.00,'2026-10-08 22:37:51','2026-10-08 22:37:51'),(4,1,19,'training_kit_materials','Bahan Uji, Hardcopy Modul & Perlengkapan K3',12.00,'paket',200000.00,2400000.00,'2026-10-08 22:37:51','2026-10-08 22:37:51');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budgets`
--

LOCK TABLES `budgets` WRITE;
/*!40000 ALTER TABLE `budgets` DISABLE KEYS */;
INSERT INTO `budgets` VALUES (1,1,1,'RAB-2026-03-001',12.00,6500000.00,78000000.00,45500000.00,32500000.00,41.67,'approved','RAB Batch 01 disetujui Finance Manager',1,1,'2026-10-09 05:37:51','2026-10-08 22:37:51','2026-10-08 22:37:51');
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
INSERT INTO `cache` VALUES ('optima-finance-cache-roster:project:v3:bc2da184c4b0a53175f843ec4491768d','O:26:\"Laravel\\Roster\\ProjectScan\":8:{s:8:\"basePath\";s:22:\"D:\\laragon\\www\\optima\\\";s:3:\"php\";O:35:\"Laravel\\Roster\\Ecosystems\\Ecosystem\":2:{s:9:\"\0*\0byName\";a:130:{s:23:\"barryvdh/laravel-dompdf\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"barryvdh/laravel-dompdf\";s:10:\"\0*\0version\";s:5:\"3.1.2\";s:9:\"\0*\0source\";E:43:\"Laravel\\Roster\\Enums\\PackageSource:Composer\";s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^3.1\";s:7:\"\0*\0path\";s:52:\"D:\\laragon\\www\\optima\\vendor\\barryvdh\\laravel-dompdf\";}s:10:\"brick/math\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"brick/math\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:39:\"D:\\laragon\\www\\optima\\vendor\\brick\\math\";}s:31:\"carbonphp/carbon-doctrine-types\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:31:\"carbonphp/carbon-doctrine-types\";s:10:\"\0*\0version\";s:5:\"3.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"D:\\laragon\\www\\optima\\vendor\\carbonphp\\carbon-doctrine-types\";}s:13:\"composer/pcre\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"composer/pcre\";s:10:\"\0*\0version\";s:5:\"3.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:42:\"D:\\laragon\\www\\optima\\vendor\\composer\\pcre\";}s:15:\"composer/semver\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"composer/semver\";s:10:\"\0*\0version\";s:5:\"3.5.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:44:\"D:\\laragon\\www\\optima\\vendor\\composer\\semver\";}s:23:\"dflydev/dot-access-data\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"dflydev/dot-access-data\";s:10:\"\0*\0version\";s:5:\"3.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"D:\\laragon\\www\\optima\\vendor\\dflydev\\dot-access-data\";}s:18:\"doctrine/inflector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"doctrine/inflector\";s:10:\"\0*\0version\";s:5:\"2.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:47:\"D:\\laragon\\www\\optima\\vendor\\doctrine\\inflector\";}s:14:\"doctrine/lexer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"doctrine/lexer\";s:10:\"\0*\0version\";s:5:\"3.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:43:\"D:\\laragon\\www\\optima\\vendor\\doctrine\\lexer\";}s:13:\"dompdf/dompdf\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"dompdf/dompdf\";s:10:\"\0*\0version\";s:5:\"3.1.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:42:\"D:\\laragon\\www\\optima\\vendor\\dompdf\\dompdf\";}s:19:\"dompdf/php-font-lib\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"dompdf/php-font-lib\";s:10:\"\0*\0version\";s:5:\"1.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:48:\"D:\\laragon\\www\\optima\\vendor\\dompdf\\php-font-lib\";}s:18:\"dompdf/php-svg-lib\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"dompdf/php-svg-lib\";s:10:\"\0*\0version\";s:5:\"1.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:47:\"D:\\laragon\\www\\optima\\vendor\\dompdf\\php-svg-lib\";}s:29:\"dragonmantank/cron-expression\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"dragonmantank/cron-expression\";s:10:\"\0*\0version\";s:5:\"3.6.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"D:\\laragon\\www\\optima\\vendor\\dragonmantank\\cron-expression\";}s:23:\"egulias/email-validator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"egulias/email-validator\";s:10:\"\0*\0version\";s:5:\"4.0.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"D:\\laragon\\www\\optima\\vendor\\egulias\\email-validator\";}s:18:\"fruitcake/php-cors\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"fruitcake/php-cors\";s:10:\"\0*\0version\";s:5:\"1.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:47:\"D:\\laragon\\www\\optima\\vendor\\fruitcake\\php-cors\";}s:27:\"graham-campbell/result-type\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"graham-campbell/result-type\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"D:\\laragon\\www\\optima\\vendor\\graham-campbell\\result-type\";}s:17:\"guzzlehttp/guzzle\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"guzzlehttp/guzzle\";s:10:\"\0*\0version\";s:5:\"8.2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:46:\"D:\\laragon\\www\\optima\\vendor\\guzzlehttp\\guzzle\";}s:19:\"guzzlehttp/promises\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"guzzlehttp/promises\";s:10:\"\0*\0version\";s:5:\"3.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:48:\"D:\\laragon\\www\\optima\\vendor\\guzzlehttp\\promises\";}s:15:\"guzzlehttp/psr7\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"guzzlehttp/psr7\";s:10:\"\0*\0version\";s:5:\"3.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:44:\"D:\\laragon\\www\\optima\\vendor\\guzzlehttp\\psr7\";}s:23:\"guzzlehttp/uri-template\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"guzzlehttp/uri-template\";s:10:\"\0*\0version\";s:5:\"2.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"D:\\laragon\\www\\optima\\vendor\\guzzlehttp\\uri-template\";}s:17:\"laravel/framework\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"laravel/framework\";s:10:\"\0*\0version\";s:7:\"13.35.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^13.17\";s:7:\"\0*\0path\";s:46:\"D:\\laragon\\www\\optima\\vendor\\laravel\\framework\";}s:15:\"laravel/prompts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"laravel/prompts\";s:10:\"\0*\0version\";s:6:\"0.3.25\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:44:\"D:\\laragon\\www\\optima\\vendor\\laravel\\prompts\";}s:28:\"laravel/serializable-closure\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"laravel/serializable-closure\";s:10:\"\0*\0version\";s:5:\"2.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"D:\\laragon\\www\\optima\\vendor\\laravel\\serializable-closure\";}s:14:\"laravel/tinker\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"laravel/tinker\";s:10:\"\0*\0version\";s:5:\"3.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^3.0\";s:7:\"\0*\0path\";s:43:\"D:\\laragon\\www\\optima\\vendor\\laravel\\tinker\";}s:17:\"league/commonmark\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"league/commonmark\";s:10:\"\0*\0version\";s:6:\"2.10.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:46:\"D:\\laragon\\www\\optima\\vendor\\league\\commonmark\";}s:13:\"league/config\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"league/config\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:42:\"D:\\laragon\\www\\optima\\vendor\\league\\config\";}s:16:\"league/flysystem\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"league/flysystem\";s:10:\"\0*\0version\";s:6:\"3.36.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:45:\"D:\\laragon\\www\\optima\\vendor\\league\\flysystem\";}s:22:\"league/flysystem-local\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"league/flysystem-local\";s:10:\"\0*\0version\";s:6:\"3.35.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"D:\\laragon\\www\\optima\\vendor\\league\\flysystem-local\";}s:26:\"league/mime-type-detection\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"league/mime-type-detection\";s:10:\"\0*\0version\";s:6:\"1.17.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"D:\\laragon\\www\\optima\\vendor\\league\\mime-type-detection\";}s:10:\"league/uri\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"league/uri\";s:10:\"\0*\0version\";s:5:\"7.8.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:39:\"D:\\laragon\\www\\optima\\vendor\\league\\uri\";}s:21:\"league/uri-interfaces\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"league/uri-interfaces\";s:10:\"\0*\0version\";s:5:\"7.8.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"D:\\laragon\\www\\optima\\vendor\\league\\uri-interfaces\";}s:17:\"livewire/livewire\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"livewire/livewire\";s:10:\"\0*\0version\";s:5:\"4.4.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^4.4\";s:7:\"\0*\0path\";s:46:\"D:\\laragon\\www\\optima\\vendor\\livewire\\livewire\";}s:17:\"maatwebsite/excel\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"maatwebsite/excel\";s:10:\"\0*\0version\";s:5:\"4.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^4.0\";s:7:\"\0*\0path\";s:46:\"D:\\laragon\\www\\optima\\vendor\\maatwebsite\\excel\";}s:23:\"maennchen/zipstream-php\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"maennchen/zipstream-php\";s:10:\"\0*\0version\";s:5:\"3.2.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"D:\\laragon\\www\\optima\\vendor\\maennchen\\zipstream-php\";}s:17:\"markbaker/complex\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"markbaker/complex\";s:10:\"\0*\0version\";s:5:\"3.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:46:\"D:\\laragon\\www\\optima\\vendor\\markbaker\\complex\";}s:16:\"markbaker/matrix\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"markbaker/matrix\";s:10:\"\0*\0version\";s:5:\"3.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:45:\"D:\\laragon\\www\\optima\\vendor\\markbaker\\matrix\";}s:17:\"masterminds/html5\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"masterminds/html5\";s:10:\"\0*\0version\";s:6:\"2.11.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:46:\"D:\\laragon\\www\\optima\\vendor\\masterminds\\html5\";}s:15:\"monolog/monolog\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"monolog/monolog\";s:10:\"\0*\0version\";s:6:\"3.12.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:44:\"D:\\laragon\\www\\optima\\vendor\\monolog\\monolog\";}s:13:\"nesbot/carbon\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"nesbot/carbon\";s:10:\"\0*\0version\";s:6:\"3.14.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:42:\"D:\\laragon\\www\\optima\\vendor\\nesbot\\carbon\";}s:12:\"nette/schema\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"nette/schema\";s:10:\"\0*\0version\";s:5:\"1.3.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:41:\"D:\\laragon\\www\\optima\\vendor\\nette\\schema\";}s:11:\"nette/utils\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"nette/utils\";s:10:\"\0*\0version\";s:5:\"4.1.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:40:\"D:\\laragon\\www\\optima\\vendor\\nette\\utils\";}s:16:\"nikic/php-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"nikic/php-parser\";s:10:\"\0*\0version\";s:5:\"5.9.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:45:\"D:\\laragon\\www\\optima\\vendor\\nikic\\php-parser\";}s:19:\"nunomaduro/termwind\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"nunomaduro/termwind\";s:10:\"\0*\0version\";s:5:\"2.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:48:\"D:\\laragon\\www\\optima\\vendor\\nunomaduro\\termwind\";}s:24:\"phpoffice/phpspreadsheet\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:24:\"phpoffice/phpspreadsheet\";s:10:\"\0*\0version\";s:6:\"5.10.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"D:\\laragon\\www\\optima\\vendor\\phpoffice\\phpspreadsheet\";}s:19:\"phpoption/phpoption\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"phpoption/phpoption\";s:10:\"\0*\0version\";s:6:\"1.10.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:48:\"D:\\laragon\\www\\optima\\vendor\\phpoption\\phpoption\";}s:9:\"psr/clock\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"psr/clock\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:38:\"D:\\laragon\\www\\optima\\vendor\\psr\\clock\";}s:13:\"psr/container\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"psr/container\";s:10:\"\0*\0version\";s:5:\"2.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:42:\"D:\\laragon\\www\\optima\\vendor\\psr\\container\";}s:20:\"psr/event-dispatcher\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"psr/event-dispatcher\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"D:\\laragon\\www\\optima\\vendor\\psr\\event-dispatcher\";}s:15:\"psr/http-client\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"psr/http-client\";s:10:\"\0*\0version\";s:5:\"1.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:44:\"D:\\laragon\\www\\optima\\vendor\\psr\\http-client\";}s:16:\"psr/http-factory\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"psr/http-factory\";s:10:\"\0*\0version\";s:5:\"1.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:45:\"D:\\laragon\\www\\optima\\vendor\\psr\\http-factory\";}s:16:\"psr/http-message\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"psr/http-message\";s:10:\"\0*\0version\";s:3:\"2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:45:\"D:\\laragon\\www\\optima\\vendor\\psr\\http-message\";}s:7:\"psr/log\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"psr/log\";s:10:\"\0*\0version\";s:5:\"3.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:36:\"D:\\laragon\\www\\optima\\vendor\\psr\\log\";}s:16:\"psr/simple-cache\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"psr/simple-cache\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:45:\"D:\\laragon\\www\\optima\\vendor\\psr\\simple-cache\";}s:9:\"psy/psysh\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"psy/psysh\";s:10:\"\0*\0version\";s:7:\"0.12.24\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:38:\"D:\\laragon\\www\\optima\\vendor\\psy\\psysh\";}s:17:\"ramsey/collection\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"ramsey/collection\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:46:\"D:\\laragon\\www\\optima\\vendor\\ramsey\\collection\";}s:11:\"ramsey/uuid\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"ramsey/uuid\";s:10:\"\0*\0version\";s:5:\"4.9.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:40:\"D:\\laragon\\www\\optima\\vendor\\ramsey\\uuid\";}s:25:\"sabberworm/php-css-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"sabberworm/php-css-parser\";s:10:\"\0*\0version\";s:5:\"9.5.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"D:\\laragon\\www\\optima\\vendor\\sabberworm\\php-css-parser\";}s:28:\"spatie/laravel-package-tools\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"spatie/laravel-package-tools\";s:10:\"\0*\0version\";s:6:\"1.93.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"D:\\laragon\\www\\optima\\vendor\\spatie\\laravel-package-tools\";}s:25:\"spatie/laravel-permission\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"spatie/laravel-permission\";s:10:\"\0*\0version\";s:5:\"8.3.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^8.3\";s:7:\"\0*\0path\";s:54:\"D:\\laragon\\www\\optima\\vendor\\spatie\\laravel-permission\";}s:13:\"symfony/clock\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"symfony/clock\";s:10:\"\0*\0version\";s:5:\"7.4.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:42:\"D:\\laragon\\www\\optima\\vendor\\symfony\\clock\";}s:15:\"symfony/console\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"symfony/console\";s:10:\"\0*\0version\";s:6:\"7.4.20\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:44:\"D:\\laragon\\www\\optima\\vendor\\symfony\\console\";}s:20:\"symfony/css-selector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"symfony/css-selector\";s:10:\"\0*\0version\";s:6:\"7.4.18\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"D:\\laragon\\www\\optima\\vendor\\symfony\\css-selector\";}s:29:\"symfony/deprecation-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"symfony/deprecation-contracts\";s:10:\"\0*\0version\";s:5:\"3.7.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"D:\\laragon\\www\\optima\\vendor\\symfony\\deprecation-contracts\";}s:21:\"symfony/error-handler\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"symfony/error-handler\";s:10:\"\0*\0version\";s:6:\"7.4.20\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"D:\\laragon\\www\\optima\\vendor\\symfony\\error-handler\";}s:24:\"symfony/event-dispatcher\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:24:\"symfony/event-dispatcher\";s:10:\"\0*\0version\";s:6:\"7.4.17\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"D:\\laragon\\www\\optima\\vendor\\symfony\\event-dispatcher\";}s:34:\"symfony/event-dispatcher-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:34:\"symfony/event-dispatcher-contracts\";s:10:\"\0*\0version\";s:5:\"3.7.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"D:\\laragon\\www\\optima\\vendor\\symfony\\event-dispatcher-contracts\";}s:14:\"symfony/finder\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"symfony/finder\";s:10:\"\0*\0version\";s:6:\"7.4.20\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:43:\"D:\\laragon\\www\\optima\\vendor\\symfony\\finder\";}s:23:\"symfony/http-foundation\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"symfony/http-foundation\";s:10:\"\0*\0version\";s:6:\"7.4.20\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"D:\\laragon\\www\\optima\\vendor\\symfony\\http-foundation\";}s:19:\"symfony/http-kernel\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"symfony/http-kernel\";s:10:\"\0*\0version\";s:6:\"7.4.20\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:48:\"D:\\laragon\\www\\optima\\vendor\\symfony\\http-kernel\";}s:14:\"symfony/mailer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"symfony/mailer\";s:10:\"\0*\0version\";s:6:\"7.4.19\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:43:\"D:\\laragon\\www\\optima\\vendor\\symfony\\mailer\";}s:12:\"symfony/mime\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"symfony/mime\";s:10:\"\0*\0version\";s:6:\"7.4.19\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:41:\"D:\\laragon\\www\\optima\\vendor\\symfony\\mime\";}s:22:\"symfony/polyfill-ctype\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-ctype\";s:10:\"\0*\0version\";s:6:\"1.37.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"D:\\laragon\\www\\optima\\vendor\\symfony\\polyfill-ctype\";}s:30:\"symfony/polyfill-intl-grapheme\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:30:\"symfony/polyfill-intl-grapheme\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"D:\\laragon\\www\\optima\\vendor\\symfony\\polyfill-intl-grapheme\";}s:25:\"symfony/polyfill-intl-idn\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"symfony/polyfill-intl-idn\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"D:\\laragon\\www\\optima\\vendor\\symfony\\polyfill-intl-idn\";}s:32:\"symfony/polyfill-intl-normalizer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:32:\"symfony/polyfill-intl-normalizer\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"D:\\laragon\\www\\optima\\vendor\\symfony\\polyfill-intl-normalizer\";}s:25:\"symfony/polyfill-mbstring\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"symfony/polyfill-mbstring\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"D:\\laragon\\www\\optima\\vendor\\symfony\\polyfill-mbstring\";}s:22:\"symfony/polyfill-php80\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php80\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"D:\\laragon\\www\\optima\\vendor\\symfony\\polyfill-php80\";}s:22:\"symfony/polyfill-php82\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php82\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"D:\\laragon\\www\\optima\\vendor\\symfony\\polyfill-php82\";}s:22:\"symfony/polyfill-php83\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php83\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"D:\\laragon\\www\\optima\\vendor\\symfony\\polyfill-php83\";}s:22:\"symfony/polyfill-php84\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php84\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"D:\\laragon\\www\\optima\\vendor\\symfony\\polyfill-php84\";}s:22:\"symfony/polyfill-php85\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php85\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"D:\\laragon\\www\\optima\\vendor\\symfony\\polyfill-php85\";}s:22:\"symfony/polyfill-php86\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php86\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"D:\\laragon\\www\\optima\\vendor\\symfony\\polyfill-php86\";}s:21:\"symfony/polyfill-uuid\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"symfony/polyfill-uuid\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"D:\\laragon\\www\\optima\\vendor\\symfony\\polyfill-uuid\";}s:15:\"symfony/process\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"symfony/process\";s:10:\"\0*\0version\";s:6:\"7.4.19\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:44:\"D:\\laragon\\www\\optima\\vendor\\symfony\\process\";}s:15:\"symfony/routing\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"symfony/routing\";s:10:\"\0*\0version\";s:6:\"7.4.20\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:44:\"D:\\laragon\\www\\optima\\vendor\\symfony\\routing\";}s:25:\"symfony/service-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"symfony/service-contracts\";s:10:\"\0*\0version\";s:5:\"3.7.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"D:\\laragon\\www\\optima\\vendor\\symfony\\service-contracts\";}s:14:\"symfony/string\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"symfony/string\";s:10:\"\0*\0version\";s:6:\"7.4.19\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:43:\"D:\\laragon\\www\\optima\\vendor\\symfony\\string\";}s:19:\"symfony/translation\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"symfony/translation\";s:10:\"\0*\0version\";s:6:\"7.4.17\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:48:\"D:\\laragon\\www\\optima\\vendor\\symfony\\translation\";}s:29:\"symfony/translation-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"symfony/translation-contracts\";s:10:\"\0*\0version\";s:5:\"3.7.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"D:\\laragon\\www\\optima\\vendor\\symfony\\translation-contracts\";}s:11:\"symfony/uid\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"symfony/uid\";s:10:\"\0*\0version\";s:6:\"7.4.20\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:40:\"D:\\laragon\\www\\optima\\vendor\\symfony\\uid\";}s:18:\"symfony/var-dumper\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"symfony/var-dumper\";s:10:\"\0*\0version\";s:6:\"7.4.18\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:47:\"D:\\laragon\\www\\optima\\vendor\\symfony\\var-dumper\";}s:33:\"tijsverkoyen/css-to-inline-styles\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:33:\"tijsverkoyen/css-to-inline-styles\";s:10:\"\0*\0version\";s:5:\"2.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"D:\\laragon\\www\\optima\\vendor\\tijsverkoyen\\css-to-inline-styles\";}s:16:\"vlucas/phpdotenv\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"vlucas/phpdotenv\";s:10:\"\0*\0version\";s:5:\"5.7.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:45:\"D:\\laragon\\www\\optima\\vendor\\vlucas\\phpdotenv\";}s:19:\"voku/portable-ascii\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"voku/portable-ascii\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:48:\"D:\\laragon\\www\\optima\\vendor\\voku\\portable-ascii\";}s:14:\"fakerphp/faker\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"fakerphp/faker\";s:10:\"\0*\0version\";s:6:\"1.24.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^1.23\";s:7:\"\0*\0path\";s:43:\"D:\\laragon\\www\\optima\\vendor\\fakerphp\\faker\";}s:11:\"filp/whoops\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"filp/whoops\";s:10:\"\0*\0version\";s:6:\"2.18.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:40:\"D:\\laragon\\www\\optima\\vendor\\filp\\whoops\";}s:21:\"hamcrest/hamcrest-php\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"hamcrest/hamcrest-php\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"D:\\laragon\\www\\optima\\vendor\\hamcrest\\hamcrest-php\";}s:22:\"laravel/agent-detector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"laravel/agent-detector\";s:10:\"\0*\0version\";s:5:\"2.0.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"D:\\laragon\\www\\optima\\vendor\\laravel\\agent-detector\";}s:13:\"laravel/boost\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"laravel/boost\";s:10:\"\0*\0version\";s:6:\"2.10.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^2.10\";s:7:\"\0*\0path\";s:42:\"D:\\laragon\\www\\optima\\vendor\\laravel\\boost\";}s:11:\"laravel/mcp\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"laravel/mcp\";s:10:\"\0*\0version\";s:5:\"1.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:40:\"D:\\laragon\\www\\optima\\vendor\\laravel\\mcp\";}s:12:\"laravel/pail\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"laravel/pail\";s:10:\"\0*\0version\";s:5:\"1.2.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^1.2.5\";s:7:\"\0*\0path\";s:41:\"D:\\laragon\\www\\optima\\vendor\\laravel\\pail\";}s:11:\"laravel/pao\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"laravel/pao\";s:10:\"\0*\0version\";s:5:\"1.1.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^1.0.6\";s:7:\"\0*\0path\";s:40:\"D:\\laragon\\www\\optima\\vendor\\laravel\\pao\";}s:12:\"laravel/pint\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"laravel/pint\";s:10:\"\0*\0version\";s:6:\"1.32.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^1.27\";s:7:\"\0*\0path\";s:41:\"D:\\laragon\\www\\optima\\vendor\\laravel\\pint\";}s:14:\"laravel/roster\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"laravel/roster\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:43:\"D:\\laragon\\www\\optima\\vendor\\laravel\\roster\";}s:15:\"mockery/mockery\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"mockery/mockery\";s:10:\"\0*\0version\";s:6:\"1.6.15\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^1.6\";s:7:\"\0*\0path\";s:44:\"D:\\laragon\\www\\optima\\vendor\\mockery\\mockery\";}s:17:\"myclabs/deep-copy\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"myclabs/deep-copy\";s:10:\"\0*\0version\";s:6:\"1.14.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:46:\"D:\\laragon\\www\\optima\\vendor\\myclabs\\deep-copy\";}s:20:\"nunomaduro/collision\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"nunomaduro/collision\";s:10:\"\0*\0version\";s:5:\"8.9.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^8.6\";s:7:\"\0*\0path\";s:49:\"D:\\laragon\\www\\optima\\vendor\\nunomaduro\\collision\";}s:16:\"phar-io/manifest\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"phar-io/manifest\";s:10:\"\0*\0version\";s:5:\"2.0.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:45:\"D:\\laragon\\www\\optima\\vendor\\phar-io\\manifest\";}s:15:\"phar-io/version\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"phar-io/version\";s:10:\"\0*\0version\";s:5:\"3.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:44:\"D:\\laragon\\www\\optima\\vendor\\phar-io\\version\";}s:25:\"phpunit/php-code-coverage\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"phpunit/php-code-coverage\";s:10:\"\0*\0version\";s:6:\"12.5.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"D:\\laragon\\www\\optima\\vendor\\phpunit\\php-code-coverage\";}s:25:\"phpunit/php-file-iterator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"phpunit/php-file-iterator\";s:10:\"\0*\0version\";s:5:\"6.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"D:\\laragon\\www\\optima\\vendor\\phpunit\\php-file-iterator\";}s:19:\"phpunit/php-invoker\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"phpunit/php-invoker\";s:10:\"\0*\0version\";s:5:\"6.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:48:\"D:\\laragon\\www\\optima\\vendor\\phpunit\\php-invoker\";}s:25:\"phpunit/php-text-template\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"phpunit/php-text-template\";s:10:\"\0*\0version\";s:5:\"5.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"D:\\laragon\\www\\optima\\vendor\\phpunit\\php-text-template\";}s:17:\"phpunit/php-timer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"phpunit/php-timer\";s:10:\"\0*\0version\";s:5:\"8.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:46:\"D:\\laragon\\www\\optima\\vendor\\phpunit\\php-timer\";}s:15:\"phpunit/phpunit\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"phpunit/phpunit\";s:10:\"\0*\0version\";s:7:\"12.5.38\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:8:\"^12.5.12\";s:7:\"\0*\0path\";s:44:\"D:\\laragon\\www\\optima\\vendor\\phpunit\\phpunit\";}s:20:\"sebastian/cli-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"sebastian/cli-parser\";s:10:\"\0*\0version\";s:5:\"4.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"D:\\laragon\\www\\optima\\vendor\\sebastian\\cli-parser\";}s:20:\"sebastian/comparator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"sebastian/comparator\";s:10:\"\0*\0version\";s:5:\"7.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"D:\\laragon\\www\\optima\\vendor\\sebastian\\comparator\";}s:20:\"sebastian/complexity\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"sebastian/complexity\";s:10:\"\0*\0version\";s:5:\"5.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"D:\\laragon\\www\\optima\\vendor\\sebastian\\complexity\";}s:14:\"sebastian/diff\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"sebastian/diff\";s:10:\"\0*\0version\";s:5:\"7.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:43:\"D:\\laragon\\www\\optima\\vendor\\sebastian\\diff\";}s:21:\"sebastian/environment\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"sebastian/environment\";s:10:\"\0*\0version\";s:5:\"8.1.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"D:\\laragon\\www\\optima\\vendor\\sebastian\\environment\";}s:18:\"sebastian/exporter\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"sebastian/exporter\";s:10:\"\0*\0version\";s:5:\"7.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:47:\"D:\\laragon\\www\\optima\\vendor\\sebastian\\exporter\";}s:22:\"sebastian/global-state\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"sebastian/global-state\";s:10:\"\0*\0version\";s:5:\"8.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"D:\\laragon\\www\\optima\\vendor\\sebastian\\global-state\";}s:23:\"sebastian/lines-of-code\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"sebastian/lines-of-code\";s:10:\"\0*\0version\";s:5:\"4.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"D:\\laragon\\www\\optima\\vendor\\sebastian\\lines-of-code\";}s:27:\"sebastian/object-enumerator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"sebastian/object-enumerator\";s:10:\"\0*\0version\";s:5:\"7.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"D:\\laragon\\www\\optima\\vendor\\sebastian\\object-enumerator\";}s:26:\"sebastian/object-reflector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"sebastian/object-reflector\";s:10:\"\0*\0version\";s:5:\"5.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"D:\\laragon\\www\\optima\\vendor\\sebastian\\object-reflector\";}s:27:\"sebastian/recursion-context\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"sebastian/recursion-context\";s:10:\"\0*\0version\";s:5:\"7.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"D:\\laragon\\www\\optima\\vendor\\sebastian\\recursion-context\";}s:14:\"sebastian/type\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"sebastian/type\";s:10:\"\0*\0version\";s:5:\"6.0.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:43:\"D:\\laragon\\www\\optima\\vendor\\sebastian\\type\";}s:17:\"sebastian/version\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"sebastian/version\";s:10:\"\0*\0version\";s:5:\"6.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:46:\"D:\\laragon\\www\\optima\\vendor\\sebastian\\version\";}s:28:\"staabm/side-effects-detector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"staabm/side-effects-detector\";s:10:\"\0*\0version\";s:5:\"1.0.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"D:\\laragon\\www\\optima\\vendor\\staabm\\side-effects-detector\";}s:12:\"symfony/yaml\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"symfony/yaml\";s:10:\"\0*\0version\";s:6:\"7.4.20\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:41:\"D:\\laragon\\www\\optima\\vendor\\symfony\\yaml\";}s:17:\"theseer/tokenizer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"theseer/tokenizer\";s:10:\"\0*\0version\";s:5:\"2.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:46:\"D:\\laragon\\www\\optima\\vendor\\theseer\\tokenizer\";}}s:11:\"\0*\0packages\";O:32:\"Laravel\\Roster\\PackageCollection\":2:{s:8:\"\0*\0items\";a:130:{i:0;r:5;i:1;r:13;i:2;r:21;i:3;r:29;i:4;r:37;i:5;r:45;i:6;r:53;i:7;r:61;i:8;r:69;i:9;r:77;i:10;r:85;i:11;r:93;i:12;r:101;i:13;r:109;i:14;r:117;i:15;r:125;i:16;r:133;i:17;r:141;i:18;r:149;i:19;r:157;i:20;r:165;i:21;r:173;i:22;r:181;i:23;r:189;i:24;r:197;i:25;r:205;i:26;r:213;i:27;r:221;i:28;r:229;i:29;r:237;i:30;r:245;i:31;r:253;i:32;r:261;i:33;r:269;i:34;r:277;i:35;r:285;i:36;r:293;i:37;r:301;i:38;r:309;i:39;r:317;i:40;r:325;i:41;r:333;i:42;r:341;i:43;r:349;i:44;r:357;i:45;r:365;i:46;r:373;i:47;r:381;i:48;r:389;i:49;r:397;i:50;r:405;i:51;r:413;i:52;r:421;i:53;r:429;i:54;r:437;i:55;r:445;i:56;r:453;i:57;r:461;i:58;r:469;i:59;r:477;i:60;r:485;i:61;r:493;i:62;r:501;i:63;r:509;i:64;r:517;i:65;r:525;i:66;r:533;i:67;r:541;i:68;r:549;i:69;r:557;i:70;r:565;i:71;r:573;i:72;r:581;i:73;r:589;i:74;r:597;i:75;r:605;i:76;r:613;i:77;r:621;i:78;r:629;i:79;r:637;i:80;r:645;i:81;r:653;i:82;r:661;i:83;r:669;i:84;r:677;i:85;r:685;i:86;r:693;i:87;r:701;i:88;r:709;i:89;r:717;i:90;r:725;i:91;r:733;i:92;r:741;i:93;r:749;i:94;r:757;i:95;r:765;i:96;r:773;i:97;r:781;i:98;r:789;i:99;r:797;i:100;r:805;i:101;r:813;i:102;r:821;i:103;r:829;i:104;r:837;i:105;r:845;i:106;r:853;i:107;r:861;i:108;r:869;i:109;r:877;i:110;r:885;i:111;r:893;i:112;r:901;i:113;r:909;i:114;r:917;i:115;r:925;i:116;r:933;i:117;r:941;i:118;r:949;i:119;r:957;i:120;r:965;i:121;r:973;i:122;r:981;i:123;r:989;i:124;r:997;i:125;r:1005;i:126;r:1013;i:127;r:1021;i:128;r:1029;i:129;r:1037;}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:2:\"js\";O:37:\"Laravel\\Roster\\Ecosystems\\JsEcosystem\":3:{s:9:\"\0*\0byName\";a:6:{s:17:\"@tailwindcss/vite\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"@tailwindcss/vite\";s:10:\"\0*\0version\";s:5:\"4.0.0\";s:9:\"\0*\0source\";E:38:\"Laravel\\Roster\\Enums\\PackageSource:Npm\";s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^4.0.0\";s:7:\"\0*\0path\";s:52:\"D:\\laragon\\www\\optima\\node_modules\\@tailwindcss\\vite\";}s:12:\"concurrently\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"concurrently\";s:10:\"\0*\0version\";s:6:\"10.0.3\";s:9:\"\0*\0source\";r:1183;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:7:\"^10.0.3\";s:7:\"\0*\0path\";s:47:\"D:\\laragon\\www\\optima\\node_modules\\concurrently\";}s:19:\"laravel-vite-plugin\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"laravel-vite-plugin\";s:10:\"\0*\0version\";s:3:\"3.1\";s:9:\"\0*\0source\";r:1183;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^3.1\";s:7:\"\0*\0path\";s:54:\"D:\\laragon\\www\\optima\\node_modules\\laravel-vite-plugin\";}s:11:\"tailwindcss\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"tailwindcss\";s:10:\"\0*\0version\";s:5:\"4.0.0\";s:9:\"\0*\0source\";r:1183;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^4.0.0\";s:7:\"\0*\0path\";s:46:\"D:\\laragon\\www\\optima\\node_modules\\tailwindcss\";}s:4:\"vite\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"vite\";s:10:\"\0*\0version\";s:5:\"8.0.0\";s:9:\"\0*\0source\";r:1183;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^8.0.0\";s:7:\"\0*\0path\";s:39:\"D:\\laragon\\www\\optima\\node_modules\\vite\";}s:18:\"@laravel/multiplex\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"@laravel/multiplex\";s:10:\"\0*\0version\";s:5:\"0.4.1\";s:9:\"\0*\0source\";r:1183;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^0.4.1\";s:7:\"\0*\0path\";s:53:\"D:\\laragon\\www\\optima\\node_modules\\@laravel\\multiplex\";}}s:11:\"\0*\0packages\";O:32:\"Laravel\\Roster\\PackageCollection\":2:{s:8:\"\0*\0items\";a:6:{i:0;r:1180;i:1;r:1188;i:2;r:1196;i:3;r:1204;i:4;r:1212;i:5;r:1220;}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:17:\"\0*\0packageManager\";N;}s:6:\"stacks\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:1:{i:0;E:35:\"Laravel\\Roster\\Enums\\Stack:Livewire\";}}s:21:\"browserTestFrameworks\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:0:{}}s:9:\"frontends\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:0:{}}s:6:\"agents\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:2:{i:0;E:37:\"Laravel\\Roster\\Enums\\Agent:ClaudeCode\";i:1;E:32:\"Laravel\\Roster\\Enums\\Agent:Codex\";}}s:7:\"editors\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:0:{}}}',1791698089),('optima-finance-cache-spatie.permission.cache','a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:67:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:13:\"view-accounts\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:5;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:15:\"create-accounts\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:13:\"edit-accounts\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:15:\"delete-accounts\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:13:\"view-journals\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:5;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:15:\"create-journals\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:13:\"post-journals\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:16:\"reverse-journals\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:11:\"view-ledger\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:5;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:22:\"view-financial-reports\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:5;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:12:\"close-period\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:13:\"view-invoices\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:5;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:15:\"create-invoices\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:13:\"edit-invoices\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:16:\"approve-invoices\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:15:\"delete-invoices\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:24:\"record-customer-payments\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:22:\"view-customer-payments\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:5;}}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:10:\"view-bills\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:5;}}i:19;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:12:\"create-bills\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:20;a:4:{s:1:\"a\";i:21;s:1:\"b\";s:10:\"edit-bills\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:21;a:4:{s:1:\"a\";i:22;s:1:\"b\";s:13:\"approve-bills\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:22;a:4:{s:1:\"a\";i:23;s:1:\"b\";s:12:\"delete-bills\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:23;a:4:{s:1:\"a\";i:24;s:1:\"b\";s:22:\"record-vendor-payments\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:24;a:4:{s:1:\"a\";i:25;s:1:\"b\";s:20:\"view-vendor-payments\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:5;}}i:25;a:4:{s:1:\"a\";i:26;s:1:\"b\";s:14:\"view-cash-bank\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:5;}}i:26;a:4:{s:1:\"a\";i:27;s:1:\"b\";s:16:\"manage-cash-bank\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:27;a:4:{s:1:\"a\";i:28;s:1:\"b\";s:14:\"reconcile-bank\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:28;a:4:{s:1:\"a\";i:29;s:1:\"b\";s:12:\"view-batches\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:29;a:4:{s:1:\"a\";i:30;s:1:\"b\";s:14:\"create-batches\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:30;a:4:{s:1:\"a\";i:31;s:1:\"b\";s:12:\"edit-batches\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:31;a:4:{s:1:\"a\";i:32;s:1:\"b\";s:13:\"close-batches\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:32;a:4:{s:1:\"a\";i:33;s:1:\"b\";s:13:\"view-programs\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:33;a:4:{s:1:\"a\";i:34;s:1:\"b\";s:15:\"create-programs\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:34;a:4:{s:1:\"a\";i:35;s:1:\"b\";s:13:\"edit-programs\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:35;a:4:{s:1:\"a\";i:36;s:1:\"b\";s:15:\"delete-programs\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:36;a:4:{s:1:\"a\";i:37;s:1:\"b\";s:14:\"view-customers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:37;a:4:{s:1:\"a\";i:38;s:1:\"b\";s:16:\"create-customers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:38;a:4:{s:1:\"a\";i:39;s:1:\"b\";s:14:\"edit-customers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:39;a:4:{s:1:\"a\";i:40;s:1:\"b\";s:9:\"view-lsps\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:40;a:4:{s:1:\"a\";i:41;s:1:\"b\";s:11:\"create-lsps\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:41;a:4:{s:1:\"a\";i:42;s:1:\"b\";s:9:\"edit-lsps\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:42;a:4:{s:1:\"a\";i:43;s:1:\"b\";s:11:\"delete-lsps\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:43;a:4:{s:1:\"a\";i:44;s:1:\"b\";s:12:\"view-vendors\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:44;a:4:{s:1:\"a\";i:45;s:1:\"b\";s:14:\"create-vendors\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:45;a:4:{s:1:\"a\";i:46;s:1:\"b\";s:12:\"edit-vendors\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:46;a:4:{s:1:\"a\";i:47;s:1:\"b\";s:13:\"view-trainers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:47;a:4:{s:1:\"a\";i:48;s:1:\"b\";s:15:\"create-trainers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:48;a:4:{s:1:\"a\";i:49;s:1:\"b\";s:13:\"edit-trainers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:49;a:4:{s:1:\"a\";i:50;s:1:\"b\";s:15:\"delete-trainers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:50;a:4:{s:1:\"a\";i:51;s:1:\"b\";s:12:\"view-budgets\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:51;a:4:{s:1:\"a\";i:52;s:1:\"b\";s:14:\"create-budgets\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:52;a:4:{s:1:\"a\";i:53;s:1:\"b\";s:12:\"edit-budgets\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:53;a:4:{s:1:\"a\";i:54;s:1:\"b\";s:15:\"approve-budgets\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:54;a:4:{s:1:\"a\";i:55;s:1:\"b\";s:12:\"manage-users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:55;a:4:{s:1:\"a\";i:56;s:1:\"b\";s:10:\"view-users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:56;a:4:{s:1:\"a\";i:57;s:1:\"b\";s:12:\"create-users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:57;a:4:{s:1:\"a\";i:58;s:1:\"b\";s:10:\"edit-users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:58;a:4:{s:1:\"a\";i:59;s:1:\"b\";s:12:\"delete-users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:59;a:4:{s:1:\"a\";i:60;s:1:\"b\";s:16:\"approve-deletion\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:60;a:4:{s:1:\"a\";i:61;s:1:\"b\";s:12:\"manage-roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:61;a:4:{s:1:\"a\";i:62;s:1:\"b\";s:15:\"view-audit-logs\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:5;}}i:62;a:4:{s:1:\"a\";i:63;s:1:\"b\";s:15:\"manage-settings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:63;a:4:{s:1:\"a\";i:64;s:1:\"b\";s:17:\"view-participants\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:64;a:4:{s:1:\"a\";i:65;s:1:\"b\";s:19:\"create-participants\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:65;a:4:{s:1:\"a\";i:66;s:1:\"b\";s:17:\"edit-participants\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:66;a:4:{s:1:\"a\";i:67;s:1:\"b\";s:19:\"delete-participants\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}}s:5:\"roles\";a:5:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:11:\"Super Admin\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:15:\"Finance Manager\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:10:\"Accountant\";s:1:\"c\";s:3:\"web\";}i:3;a:3:{s:1:\"a\";i:5;s:1:\"b\";s:7:\"Auditor\";s:1:\"c\";s:3:\"web\";}i:4;a:3:{s:1:\"a\";i:4;s:1:\"b\";s:22:\"Operations Coordinator\";s:1:\"c\";s:3:\"web\";}}}',1791781723);
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
INSERT INTO `cash_bank_accounts` VALUES (1,1,1,'KAS TUNAI OPERASIONAL (PETTY CASH)','petty_cash','KAS INTERNAL','KAS-01','Kasir Perusahaan','IDR',4500000.00,4500000.00,1,'2026-10-08 22:37:50','2026-10-08 22:37:50'),(2,1,2,'REKENING GIRO BCA OPERASIONAL','bank','BANK CENTRAL ASIA (BCA)','8801239988','PT Optima Mitra Sertifikasi','IDR',327894498.00,74484680.00,1,'2026-10-08 22:37:50','2026-10-08 22:37:50');
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
INSERT INTO `companies` VALUES (1,'PT Optima Mitra Sertifikasi','OPTIMA-ID','01.234.567.8-012.000','Gedung Wisma Pelatihan Lt. 4, Jl. TB Simatupang, Jakarta Selatan','021-78901234','finance@optima-training.co.id','https://optima-training.co.id',NULL,'IDR',1,'2026-10-08 22:37:49','2026-10-08 22:37:49');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_payments`
--

LOCK TABLES `customer_payments` WRITE;
/*!40000 ALTER TABLE `customer_payments` DISABLE KEYS */;
INSERT INTO `customer_payments` VALUES (1,1,1,2,2,'REC-2026-03-0001','2026-03-12','bank_transfer',26000000.00,0.00,26000000.00,'TRF-BCA-9921102','posted','Pembayaran DP 50% Pelatihan K3 Umum Batch 01',1,'2026-10-08 22:37:51','2026-10-08 22:37:51');
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
INSERT INTO `customers` VALUES (1,1,'CUST-001','PT Semen Nusantara Perkasa','corporate','Bambang Trihatmodjo (HR Manager)','081234567890','hrd@semen-nusantara.co.id','01.555.444.3-091.000','Kawasan Industri Cilegon Kav. 45, Banten','Cilegon',30,200000000.00,NULL,1,'2026-10-08 22:37:51','2026-10-08 22:37:51'),(2,1,'CUST-002','Dinas Ketenagakerjaan & Transmigrasi','government','Drs. Agus Subekti, M.Si','081199887766','pelatihan@disnakertrans.go.id','00.123.456.7-001.000','Komplek Balaikota Blok C, Bandung','Bandung',45,350000000.00,NULL,1,'2026-10-08 22:37:51','2026-10-08 22:37:51');
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_items`
--

LOCK TABLES `invoice_items` WRITE;
/*!40000 ALTER TABLE `invoice_items` DISABLE KEYS */;
INSERT INTO `invoice_items` VALUES (1,1,16,'training','Pelatihan & Sertifikasi Ahli K3 Umum BNSP (8 Peserta)',8,6500000.00,0.00,52000000.00,'2026-10-08 22:37:51','2026-10-08 22:37:51');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
INSERT INTO `invoices` VALUES (1,1,1,1,1,1,'INV-2026-03-0001','2026-03-10','2026-04-10','PO/SNP/K3/2026/089',52000000.00,0.00,0.00,0.00,0.00,0.00,0.00,52000000.00,26000000.00,26000000.00,'partial','Tagihan termin 1 (DP 50% telah diterima, sisa H+30 setelah asesmen)',NULL,1,1,'2026-10-09 05:37:51','2026-10-08 22:37:51','2026-10-08 22:37:51');
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
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journal_entries`
--

LOCK TABLES `journal_entries` WRITE;
/*!40000 ALTER TABLE `journal_entries` DISABLE KEYS */;
INSERT INTO `journal_entries` VALUES (1,1,1,1,'JV-INV-2026-03-0001','2026-03-10','App\\Models\\Invoice',1,'invoice','Pengakuan Piutang Invoice INV-2026-03-0001 - PT Semen Nusantara Perkasa',52000000.00,52000000.00,'posted','2026-10-09 05:37:51',1,NULL,'2026-10-08 22:37:51','2026-10-08 22:37:51'),(2,1,1,1,'JV-REC-2026-03-0001','2026-03-12','App\\Models\\CustomerPayment',1,'customer_payment','Penerimaan Kas/Bank Pembayaran REC-2026-03-0001',26000000.00,26000000.00,'posted','2026-10-09 05:37:51',1,NULL,'2026-10-08 22:37:51','2026-10-08 22:37:51'),(3,1,1,1,'JV-BILL-2026-03-0001','2026-03-15','App\\Models\\VendorBill',1,'vendor_bill','Tagihan Asesmen Sertifikasi LSP BILL-2026-03-0001',21600000.00,21600000.00,'posted','2026-10-09 05:37:51',1,NULL,'2026-10-08 22:37:51','2026-10-08 22:37:51'),(4,1,1,NULL,'JV-2026-01-0001','2026-01-06',NULL,NULL,'general','Cicilan Laptop kantor',929527.00,929527.00,'posted','2026-10-09 05:43:02',1,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(5,1,1,NULL,'JV-2026-01-0002','2026-01-06',NULL,NULL,'general','adm bank',2500.00,2500.00,'posted','2026-10-09 05:43:02',1,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(6,1,1,NULL,'JV-2026-01-0003','2026-01-06',NULL,NULL,'general','Petycash januari',1000000.00,1000000.00,'posted','2026-10-09 05:43:02',1,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(7,1,1,NULL,'JV-2026-01-0004','2026-01-06',NULL,NULL,'general','PAM',332000.00,332000.00,'posted','2026-10-09 05:43:02',1,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(8,1,1,NULL,'JV-2026-01-0005','2026-01-07',NULL,NULL,'general','Cicil Hutang HAKE',150000000.00,150000000.00,'posted','2026-10-09 05:43:02',1,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(9,1,1,NULL,'JV-2026-01-0006','2026-01-07',NULL,NULL,'general','adm bank',2500.00,2500.00,'posted','2026-10-09 05:43:02',1,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(10,1,1,NULL,'JV-2026-01-0007','2026-01-07',NULL,NULL,'general','BOD',10000000.00,10000000.00,'posted','2026-10-09 05:43:02',1,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(11,1,1,NULL,'JV-2026-01-0008','2026-01-07',NULL,NULL,'general','adm bank',2500.00,2500.00,'posted','2026-10-09 05:43:02',1,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(12,1,1,NULL,'JV-2026-01-0009','2026-01-08',NULL,NULL,'general','Fee Narsum',9000000.00,9000000.00,'posted','2026-10-09 05:43:02',1,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(13,1,1,NULL,'JV-2026-01-0010','2026-01-08',NULL,NULL,'general','adm bank',2500.00,2500.00,'posted','2026-10-09 05:43:02',1,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(14,1,1,NULL,'JV-2026-01-0011','2026-01-08',NULL,NULL,'general','Mesin Absen Fingerprint',506400.00,506400.00,'posted','2026-10-09 05:43:02',1,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(15,1,1,NULL,'JV-2026-01-0012','2026-01-15',NULL,NULL,'general','Pembayaran Pelatihan',14210000.00,14210000.00,'posted','2026-10-09 05:43:02',1,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(16,1,1,NULL,'JV-2026-01-0013','2026-01-19',NULL,NULL,'general','Token & Internet',933225.00,933225.00,'posted','2026-10-09 05:43:02',1,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(17,1,1,NULL,'JV-2026-01-0014','2026-01-19',NULL,NULL,'general','Cashback SUJ',2000000.00,2000000.00,'posted','2026-10-09 05:43:02',1,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(18,1,1,NULL,'JV-2026-01-0015','2026-01-19',NULL,NULL,'general','Cashback MTI',1000000.00,1000000.00,'posted','2026-10-09 05:43:02',1,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(19,1,1,NULL,'JV-2026-01-0016','2026-01-19',NULL,NULL,'general','Cashback',500000.00,500000.00,'posted','2026-10-09 05:43:02',1,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(20,1,1,NULL,'JV-2026-01-0017','2026-01-19',NULL,NULL,'general','adm bank',2500.00,2500.00,'posted','2026-10-09 05:43:02',1,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(21,1,1,NULL,'JV-2026-01-0018','2026-01-19',NULL,NULL,'general','Tambahan Petycash',500000.00,500000.00,'posted','2026-10-09 05:43:02',1,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(22,1,1,NULL,'JV-2026-01-0019','2026-01-21',NULL,NULL,'general','Tiket pesawat',7913261.00,7913261.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(23,1,1,NULL,'JV-2026-01-0020','2026-01-21',NULL,NULL,'general','Tiket pesawat Adm',1889469.00,1889469.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(24,1,1,NULL,'JV-2026-01-0021','2026-01-21',NULL,NULL,'general','Tiket pesawat Adm',1901801.00,1901801.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(25,1,1,NULL,'JV-2026-01-0022','2026-01-26',NULL,NULL,'general','Gaji Januari',12398500.00,12398500.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(26,1,1,NULL,'JV-2026-01-0023','2026-01-26',NULL,NULL,'general','Biaya admi',5000.00,5000.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(27,1,1,NULL,'JV-2026-01-0024','2026-01-26',NULL,NULL,'general','Biaya Iklan',121000.00,121000.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(28,1,1,NULL,'JV-2026-01-0025','2026-01-26',NULL,NULL,'general','ATK & Training Kit',2520000.00,2520000.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(29,1,1,NULL,'JV-2026-01-0026','2026-01-26',NULL,NULL,'general','Pendapatan Pelatihan',13500000.00,13500000.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(30,1,1,NULL,'JV-2026-01-0027','2026-01-27',NULL,NULL,'general','Kuota Hp Kantor',45000.00,45000.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(31,1,1,NULL,'JV-2026-01-0028','2026-01-28',NULL,NULL,'general','Pendaptan pelatihan',39687500.00,39687500.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(32,1,1,NULL,'JV-2026-01-0029','2026-01-29',NULL,NULL,'general','Hotel',1275000.00,1275000.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(33,1,1,NULL,'JV-2026-01-0030','2026-01-29',NULL,NULL,'general','Hotel',6000000.00,6000000.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(34,1,1,NULL,'JV-2026-01-0031','2026-01-29',NULL,NULL,'general','Biaya Adm',2500.00,2500.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(35,1,1,NULL,'JV-2026-01-0032','2026-01-29',NULL,NULL,'general','ATK Fee Cofas 50%',1420000.00,1420000.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(36,1,1,NULL,'JV-2026-01-0033','2026-01-29',NULL,NULL,'general','Casback Rfki',500000.00,500000.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(37,1,1,NULL,'JV-2026-01-0034','2026-01-29',NULL,NULL,'general','Biaya Adm',2500.00,2500.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(38,1,1,NULL,'JV-2026-01-0035','2026-01-30',NULL,NULL,'general','Pendaptan pelatihan',13717100.00,13717100.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(39,1,1,NULL,'JV-2026-01-0036','2026-01-31',NULL,NULL,'general','Biaya Hotel',3375000.00,3375000.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(40,1,1,NULL,'JV-2026-01-0037','2026-01-31',NULL,NULL,'general','Biaya Adm',2500.00,2500.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(41,1,1,NULL,'JV-2026-01-0038','2026-01-31',NULL,NULL,'general','Subsidi Hotel & Cicilan Laptop 2',1929527.00,1929527.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(42,1,1,NULL,'JV-2026-01-0039','2026-01-31',NULL,NULL,'general','Biaya Adm',2500.00,2500.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(43,1,1,NULL,'JV-2026-01-0040','2026-01-31',NULL,NULL,'general','Biaya Adm',30000.00,30000.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(44,1,1,NULL,'JV-2026-02-0001','2026-02-03',NULL,NULL,'general','Pembayaran Sertifikasi',15000000.00,15000000.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(45,1,1,NULL,'JV-2026-02-0002','2026-02-03',NULL,NULL,'general','Biaya Adm',2500.00,2500.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(46,1,1,NULL,'JV-2026-02-0003','2026-02-03',NULL,NULL,'general','Hotel Ayani Aceh',2736000.00,2736000.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(47,1,1,NULL,'JV-2026-02-0004','2026-02-03',NULL,NULL,'general','Biaya Adm',2500.00,2500.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(48,1,1,NULL,'JV-2026-02-0005','2026-02-03',NULL,NULL,'general','Reimbush Canva & Member Parkir',242000.00,242000.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(49,1,1,NULL,'JV-2026-02-0006','2026-02-03',NULL,NULL,'general','Biaya Adm',2500.00,2500.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(50,1,1,NULL,'JV-2026-02-0007','2026-02-04',NULL,NULL,'general','Fee instruktur',9000000.00,9000000.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(51,1,1,NULL,'JV-2026-02-0008','2026-02-04',NULL,NULL,'general','Tiket Pulang Instruktur',2043530.00,2043530.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(52,1,1,NULL,'JV-2026-02-0009','2026-02-04',NULL,NULL,'general','Biaya Adm',2500.00,2500.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(53,1,1,NULL,'JV-2026-02-0010','2026-02-04',NULL,NULL,'general','Operasional Indodepp',2465500.00,2465500.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(54,1,1,NULL,'JV-2026-02-0011','2026-02-04',NULL,NULL,'general','Pengganti Hotel IFah',1500000.00,1500000.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(55,1,1,NULL,'JV-2026-02-0012','2026-02-04',NULL,NULL,'general','Reimbush Transport',215100.00,215100.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(56,1,1,NULL,'JV-2026-02-0013','2026-02-04',NULL,NULL,'general','Biaya Adm',2500.00,2500.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(57,1,1,NULL,'JV-2026-02-0014','2026-02-05',NULL,NULL,'general','DP Indodepp',49800000.00,49800000.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(58,1,1,NULL,'JV-2026-02-0015','2026-02-10',NULL,NULL,'general','Biaya Makan & Minum',4740000.00,4740000.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(59,1,1,NULL,'JV-2026-02-0016','2026-02-10',NULL,NULL,'general','Biaya Adm',2500.00,2500.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(60,1,1,NULL,'JV-2026-02-0017','2026-02-10',NULL,NULL,'general','Petycash Februari',1000000.00,1000000.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(61,1,1,NULL,'JV-2026-02-0018','2026-02-10',NULL,NULL,'general','Pam & Internet',568475.00,568475.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(62,1,1,NULL,'JV-2026-02-0019','2026-02-17',NULL,NULL,'general','Kasbon Tria',500000.00,500000.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(63,1,1,NULL,'JV-2026-02-0020','2026-02-17',NULL,NULL,'general','Tiket Pesawat',1653001.00,1653001.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(64,1,1,NULL,'JV-2026-02-0021','2026-02-17',NULL,NULL,'general','Fee Narsum',7500000.00,7500000.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(65,1,1,NULL,'JV-2026-02-0022','2026-02-26',NULL,NULL,'general','Gaji Februari',3620000.00,3620000.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(66,1,1,NULL,'JV-2026-02-0023','2026-02-26',NULL,NULL,'general','Biaya Adm',2500.00,2500.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(67,1,1,NULL,'JV-2026-02-0024','2026-02-26',NULL,NULL,'general','Casback',1000000.00,1000000.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(68,1,1,NULL,'JV-2026-02-0025','2026-02-26',NULL,NULL,'general','Biaya Adm',2500.00,2500.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(69,1,1,NULL,'JV-2026-02-0026','2026-02-26',NULL,NULL,'general','Fee Narsum Indodepp',2000000.00,2000000.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(70,1,1,NULL,'JV-2026-02-0027','2026-02-26',NULL,NULL,'general','Fee Narsum Indodepp',2000000.00,2000000.00,'posted','2026-10-09 05:43:03',1,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(71,1,1,NULL,'JV-2026-02-0028','2026-02-26',NULL,NULL,'general','Biaya Adm',2500.00,2500.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(72,1,1,NULL,'JV-2026-02-0029','2026-02-26',NULL,NULL,'general','Iklan Google',729000.00,729000.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(73,1,1,NULL,'JV-2026-02-0030','2026-02-26',NULL,NULL,'general','BOD',15000000.00,15000000.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(74,1,1,NULL,'JV-2026-02-0031','2026-02-26',NULL,NULL,'general','Biaya Adm',2500.00,2500.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(75,1,1,NULL,'JV-2026-02-0032','2026-02-28',NULL,NULL,'general','Biaya Adm',30000.00,30000.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(76,1,1,NULL,'JV-2026-03-0001','2026-03-02',NULL,NULL,'general','Gaji Ifah',1650000.00,1650000.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(77,1,1,NULL,'JV-2026-03-0002','2026-03-02',NULL,NULL,'general','PAM',141000.00,141000.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(78,1,1,NULL,'JV-2026-03-0003','2026-03-07',NULL,NULL,'general','Hotel & meet room',8660000.00,8660000.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(79,1,1,NULL,'JV-2026-03-0004','2026-03-07',NULL,NULL,'general','Biaya Adm',2500.00,2500.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(80,1,1,NULL,'JV-2026-03-0005','2026-03-07',NULL,NULL,'general','Mandays ae & Persekot ae',1780000.00,1780000.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(81,1,1,NULL,'JV-2026-03-0006','2026-03-07',NULL,NULL,'general','Persekot proper & Mandays proper',330000.00,330000.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(82,1,1,NULL,'JV-2026-03-0007','2026-03-07',NULL,NULL,'general','Fee Instruktur',3000000.00,3000000.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(83,1,1,NULL,'JV-2026-03-0008','2026-03-07',NULL,NULL,'general','Biaya Adm',2500.00,2500.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(84,1,1,NULL,'JV-2026-03-0009','2026-03-07',NULL,NULL,'general','THR BOD',12000000.00,12000000.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(85,1,1,NULL,'JV-2026-03-0010','2026-03-07',NULL,NULL,'general','Biaya Adm',5000.00,5000.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(86,1,1,NULL,'JV-2026-03-0011','2026-03-07',NULL,NULL,'general','Bayar Sertifikasi',30000000.00,30000000.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(87,1,1,NULL,'JV-2026-03-0012','2026-03-07',NULL,NULL,'general','Biaya Adm',2500.00,2500.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(88,1,1,NULL,'JV-2026-03-0013','2026-03-07',NULL,NULL,'general','Bayar Sertifikasi RTI',16500000.00,16500000.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(89,1,1,NULL,'JV-2026-03-0014','2026-03-12',NULL,NULL,'general','Cicilan Laptop',929527.00,929527.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(90,1,1,NULL,'JV-2026-03-0015','2026-03-12',NULL,NULL,'general','Biaya Adm',2500.00,2500.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(91,1,1,NULL,'JV-2026-03-0016','2026-03-12',NULL,NULL,'general','Dp Perpanjang ISO',4000000.00,4000000.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(92,1,1,NULL,'JV-2026-03-0017','2026-03-12',NULL,NULL,'general','Biaya Adm',2500.00,2500.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(93,1,1,NULL,'JV-2026-03-0018','2026-03-12',NULL,NULL,'general','Internet Kantor',412975.00,412975.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(94,1,1,NULL,'JV-2026-03-0019','2026-03-12',NULL,NULL,'general','THR Tria',1600000.00,1600000.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(95,1,1,NULL,'JV-2026-03-0020','2026-03-12',NULL,NULL,'general','Petycash Maret',1000000.00,1000000.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(96,1,1,NULL,'JV-2026-03-0021','2026-03-13',NULL,NULL,'general','Pemasukan Pelatihan',13500000.00,13500000.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(97,1,1,NULL,'JV-2026-03-0022','2026-03-27',NULL,NULL,'general','Fee Instruktur',9000000.00,9000000.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(98,1,1,NULL,'JV-2026-03-0023','2026-03-27',NULL,NULL,'general','Biaya Adm',2500.00,2500.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(99,1,1,NULL,'JV-2026-03-0024','2026-03-27',NULL,NULL,'general','Gaji Maret',3300000.00,3300000.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(100,1,1,NULL,'JV-2026-03-0025','2026-03-27',NULL,NULL,'general','Biaya Adm',2500.00,2500.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(101,1,1,NULL,'JV-2026-03-0026','2026-03-27',NULL,NULL,'general','Iklan Google',352000.00,352000.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(102,1,1,NULL,'JV-2026-03-0027','2026-03-30',NULL,NULL,'general','Petycash April',1000000.00,1000000.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(103,1,1,NULL,'JV-2026-03-0028','2026-03-30',NULL,NULL,'general','Token Listrik',501600.00,501600.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(104,1,1,NULL,'JV-2026-03-0029','2026-03-31',NULL,NULL,'general','Pembayaran Sertifikasi',10000000.00,10000000.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(105,1,1,NULL,'JV-2026-03-0030','2026-03-31',NULL,NULL,'general','Biaya Adm',30000.00,30000.00,'posted','2026-10-09 05:43:04',1,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04');
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
) ENGINE=InnoDB AUTO_INCREMENT=211 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journal_lines`
--

LOCK TABLES `journal_lines` WRITE;
/*!40000 ALTER TABLE `journal_lines` DISABLE KEYS */;
INSERT INTO `journal_lines` VALUES (1,1,3,52000000.00,0.00,'Piutang Invoice INV-2026-03-0001','2026-10-08 22:37:51','2026-10-08 22:37:51'),(2,1,16,0.00,52000000.00,'Pendapatan Jasa Pelatihan Batch 01 K3','2026-10-08 22:37:51','2026-10-08 22:37:51'),(3,2,2,26000000.00,0.00,'Penerimaan Transfer Bank BCA PT Semen Nusantara Perkasa','2026-10-08 22:37:51','2026-10-08 22:37:51'),(4,2,3,0.00,26000000.00,'Pelunasan Sebagian Piutang Invoice INV-2026-03-0001','2026-10-08 22:37:51','2026-10-08 22:37:51'),(5,3,20,21600000.00,0.00,'Biaya Sertifikasi LSP Batch 01 K3','2026-10-08 22:37:51','2026-10-08 22:37:51'),(6,3,8,0.00,21600000.00,'Utang Sertifikasi ke LSP Keselamatan Kerja Nasional (K3)','2026-10-08 22:37:51','2026-10-08 22:37:51'),(7,4,2,0.00,929527.00,'cicilan 1','2026-10-08 22:43:02','2026-10-08 22:43:02'),(8,4,31,929527.00,0.00,'cicilan 1','2026-10-08 22:43:02','2026-10-08 22:43:02'),(9,5,2,0.00,2500.00,'cicilan','2026-10-08 22:43:02','2026-10-08 22:43:02'),(10,5,30,2500.00,0.00,'cicilan','2026-10-08 22:43:02','2026-10-08 22:43:02'),(11,6,2,0.00,1000000.00,'ifah','2026-10-08 22:43:02','2026-10-08 22:43:02'),(12,6,1,1000000.00,0.00,'ifah','2026-10-08 22:43:02','2026-10-08 22:43:02'),(13,7,2,0.00,332000.00,'2 bulan','2026-10-08 22:43:02','2026-10-08 22:43:02'),(14,7,29,332000.00,0.00,'2 bulan','2026-10-08 22:43:02','2026-10-08 22:43:02'),(15,8,2,0.00,150000000.00,'2024-2025','2026-10-08 22:43:02','2026-10-08 22:43:02'),(16,8,20,150000000.00,0.00,'2024-2025','2026-10-08 22:43:02','2026-10-08 22:43:02'),(17,9,2,0.00,2500.00,'cicilan','2026-10-08 22:43:02','2026-10-08 22:43:02'),(18,9,30,2500.00,0.00,'cicilan','2026-10-08 22:43:02','2026-10-08 22:43:02'),(19,10,2,0.00,10000000.00,'Triana','2026-10-08 22:43:02','2026-10-08 22:43:02'),(20,10,33,10000000.00,0.00,'Triana','2026-10-08 22:43:02','2026-10-08 22:43:02'),(21,11,2,0.00,2500.00,'cicilan','2026-10-08 22:43:02','2026-10-08 22:43:02'),(22,11,30,2500.00,0.00,'cicilan','2026-10-08 22:43:02','2026-10-08 22:43:02'),(23,12,2,0.00,9000000.00,'Endang','2026-10-08 22:43:02','2026-10-08 22:43:02'),(24,12,21,9000000.00,0.00,'Endang','2026-10-08 22:43:02','2026-10-08 22:43:02'),(25,13,2,0.00,2500.00,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(26,13,30,2500.00,0.00,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(27,14,2,0.00,506400.00,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(28,14,31,506400.00,0.00,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(29,15,2,14210000.00,0.00,'PT Merdeka','2026-10-08 22:43:02','2026-10-08 22:43:02'),(30,15,16,0.00,14210000.00,'PT Merdeka','2026-10-08 22:43:02','2026-10-08 22:43:02'),(31,16,2,0.00,933225.00,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(32,16,29,933225.00,0.00,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(33,17,2,0.00,2000000.00,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(34,17,27,2000000.00,0.00,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(35,18,2,0.00,1000000.00,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(36,18,27,1000000.00,0.00,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(37,19,2,0.00,500000.00,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(38,19,27,500000.00,0.00,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(39,20,2,0.00,2500.00,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(40,20,30,2500.00,0.00,NULL,'2026-10-08 22:43:02','2026-10-08 22:43:02'),(41,21,2,0.00,500000.00,'Januari','2026-10-08 22:43:02','2026-10-08 22:43:02'),(42,21,1,500000.00,0.00,'Januari','2026-10-08 22:43:02','2026-10-08 22:43:02'),(43,22,2,0.00,7913261.00,'Indodepp','2026-10-08 22:43:03','2026-10-08 22:43:03'),(44,22,19,7913261.00,0.00,'Indodepp','2026-10-08 22:43:03','2026-10-08 22:43:03'),(45,23,2,0.00,1889469.00,'Indodepp','2026-10-08 22:43:03','2026-10-08 22:43:03'),(46,23,19,1889469.00,0.00,'Indodepp','2026-10-08 22:43:03','2026-10-08 22:43:03'),(47,24,2,0.00,1901801.00,'Indodepp','2026-10-08 22:43:03','2026-10-08 22:43:03'),(48,24,19,1901801.00,0.00,'Indodepp','2026-10-08 22:43:03','2026-10-08 22:43:03'),(49,25,2,0.00,12398500.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(50,25,24,12398500.00,0.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(51,26,2,0.00,5000.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(52,26,30,5000.00,0.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(53,27,2,0.00,121000.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(54,27,27,121000.00,0.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(55,28,2,0.00,2520000.00,'Indodepp','2026-10-08 22:43:03','2026-10-08 22:43:03'),(56,28,19,2520000.00,0.00,'Indodepp','2026-10-08 22:43:03','2026-10-08 22:43:03'),(57,29,2,13500000.00,0.00,'Econova','2026-10-08 22:43:03','2026-10-08 22:43:03'),(58,29,16,0.00,13500000.00,'Econova','2026-10-08 22:43:03','2026-10-08 22:43:03'),(59,30,2,0.00,45000.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(60,30,29,45000.00,0.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(61,31,2,39687500.00,0.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(62,31,16,0.00,39687500.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(63,32,2,0.00,1275000.00,'Instruktur jakarta','2026-10-08 22:43:03','2026-10-08 22:43:03'),(64,32,23,1275000.00,0.00,'Instruktur jakarta','2026-10-08 22:43:03','2026-10-08 22:43:03'),(65,33,2,0.00,6000000.00,'Asyana','2026-10-08 22:43:03','2026-10-08 22:43:03'),(66,33,23,6000000.00,0.00,'Asyana','2026-10-08 22:43:03','2026-10-08 22:43:03'),(67,34,2,0.00,2500.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(68,34,30,2500.00,0.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(69,35,2,0.00,1420000.00,'ME 2-5 Feb','2026-10-08 22:43:03','2026-10-08 22:43:03'),(70,35,19,1420000.00,0.00,'ME 2-5 Feb','2026-10-08 22:43:03','2026-10-08 22:43:03'),(71,36,2,0.00,500000.00,'MTI','2026-10-08 22:43:03','2026-10-08 22:43:03'),(72,36,27,500000.00,0.00,'MTI','2026-10-08 22:43:03','2026-10-08 22:43:03'),(73,37,2,0.00,2500.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(74,37,30,2500.00,0.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(75,38,2,13717100.00,0.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(76,38,16,0.00,13717100.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(77,39,2,0.00,3375000.00,'Indodepp','2026-10-08 22:43:03','2026-10-08 22:43:03'),(78,39,23,3375000.00,0.00,'Indodepp','2026-10-08 22:43:03','2026-10-08 22:43:03'),(79,40,2,0.00,2500.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(80,40,30,2500.00,0.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(81,41,2,0.00,1929527.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(82,41,19,1929527.00,0.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(83,42,2,0.00,2500.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(84,42,30,2500.00,0.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(85,43,2,0.00,30000.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(86,43,30,30000.00,0.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(87,44,2,0.00,15000000.00,'HAKE','2026-10-08 22:43:03','2026-10-08 22:43:03'),(88,44,20,15000000.00,0.00,'HAKE','2026-10-08 22:43:03','2026-10-08 22:43:03'),(89,45,2,0.00,2500.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(90,45,30,2500.00,0.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(91,46,2,0.00,2736000.00,'Indodepp','2026-10-08 22:43:03','2026-10-08 22:43:03'),(92,46,23,2736000.00,0.00,'Indodepp','2026-10-08 22:43:03','2026-10-08 22:43:03'),(93,47,2,0.00,2500.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(94,47,30,2500.00,0.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(95,48,2,0.00,242000.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(96,48,33,242000.00,0.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(97,49,2,0.00,2500.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(98,49,30,2500.00,0.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(99,50,2,0.00,9000000.00,'Indodepp','2026-10-08 22:43:03','2026-10-08 22:43:03'),(100,50,21,9000000.00,0.00,'Indodepp','2026-10-08 22:43:03','2026-10-08 22:43:03'),(101,51,2,0.00,2043530.00,'Indodepp','2026-10-08 22:43:03','2026-10-08 22:43:03'),(102,51,19,2043530.00,0.00,'Indodepp','2026-10-08 22:43:03','2026-10-08 22:43:03'),(103,52,2,0.00,2500.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(104,52,30,2500.00,0.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(105,53,2,0.00,2465500.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(106,53,19,2465500.00,0.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(107,54,2,0.00,1500000.00,'Indodepp','2026-10-08 22:43:03','2026-10-08 22:43:03'),(108,54,19,1500000.00,0.00,'Indodepp','2026-10-08 22:43:03','2026-10-08 22:43:03'),(109,55,2,0.00,215100.00,'GBPN','2026-10-08 22:43:03','2026-10-08 22:43:03'),(110,55,19,215100.00,0.00,'GBPN','2026-10-08 22:43:03','2026-10-08 22:43:03'),(111,56,2,0.00,2500.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(112,56,30,2500.00,0.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(113,57,2,49800000.00,0.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(114,57,16,0.00,49800000.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(115,58,2,0.00,4740000.00,'Indodepp','2026-10-08 22:43:03','2026-10-08 22:43:03'),(116,58,19,4740000.00,0.00,'Indodepp','2026-10-08 22:43:03','2026-10-08 22:43:03'),(117,59,2,0.00,2500.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(118,59,30,2500.00,0.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(119,60,2,0.00,1000000.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(120,60,1,1000000.00,0.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(121,61,2,0.00,568475.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(122,61,29,568475.00,0.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(123,62,2,0.00,500000.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(124,62,24,500000.00,0.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(125,63,2,0.00,1653001.00,'Indodepp','2026-10-08 22:43:03','2026-10-08 22:43:03'),(126,63,19,1653001.00,0.00,'Indodepp','2026-10-08 22:43:03','2026-10-08 22:43:03'),(127,64,2,0.00,7500000.00,'Shalahudin','2026-10-08 22:43:03','2026-10-08 22:43:03'),(128,64,21,7500000.00,0.00,'Shalahudin','2026-10-08 22:43:03','2026-10-08 22:43:03'),(129,65,2,0.00,3620000.00,'Tria & Surya','2026-10-08 22:43:03','2026-10-08 22:43:03'),(130,65,24,3620000.00,0.00,'Tria & Surya','2026-10-08 22:43:03','2026-10-08 22:43:03'),(131,66,2,0.00,2500.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(132,66,30,2500.00,0.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(133,67,2,0.00,1000000.00,'Inalum','2026-10-08 22:43:03','2026-10-08 22:43:03'),(134,67,27,1000000.00,0.00,'Inalum','2026-10-08 22:43:03','2026-10-08 22:43:03'),(135,68,2,0.00,2500.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(136,68,30,2500.00,0.00,NULL,'2026-10-08 22:43:03','2026-10-08 22:43:03'),(137,69,2,0.00,2000000.00,'Ardian','2026-10-08 22:43:03','2026-10-08 22:43:03'),(138,69,21,2000000.00,0.00,'Ardian','2026-10-08 22:43:03','2026-10-08 22:43:03'),(139,70,2,0.00,2000000.00,'Unggung','2026-10-08 22:43:03','2026-10-08 22:43:03'),(140,70,21,2000000.00,0.00,'Unggung','2026-10-08 22:43:03','2026-10-08 22:43:03'),(141,71,2,0.00,2500.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(142,71,30,2500.00,0.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(143,72,2,0.00,729000.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(144,72,27,729000.00,0.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(145,73,2,0.00,15000000.00,'3','2026-10-08 22:43:04','2026-10-08 22:43:04'),(146,73,33,15000000.00,0.00,'3','2026-10-08 22:43:04','2026-10-08 22:43:04'),(147,74,2,0.00,2500.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(148,74,30,2500.00,0.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(149,75,2,0.00,30000.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(150,75,30,30000.00,0.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(151,76,2,0.00,1650000.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(152,76,24,1650000.00,0.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(153,77,2,0.00,141000.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(154,77,29,141000.00,0.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(155,78,2,0.00,8660000.00,'Hotel Asyana','2026-10-08 22:43:04','2026-10-08 22:43:04'),(156,78,23,8660000.00,0.00,'Hotel Asyana','2026-10-08 22:43:04','2026-10-08 22:43:04'),(157,79,2,0.00,2500.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(158,79,30,2500.00,0.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(159,80,2,0.00,1780000.00,'Tria','2026-10-08 22:43:04','2026-10-08 22:43:04'),(160,80,19,1780000.00,0.00,'Tria','2026-10-08 22:43:04','2026-10-08 22:43:04'),(161,81,2,0.00,330000.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(162,81,19,330000.00,0.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(163,82,2,0.00,3000000.00,'Arif','2026-10-08 22:43:04','2026-10-08 22:43:04'),(164,82,21,3000000.00,0.00,'Arif','2026-10-08 22:43:04','2026-10-08 22:43:04'),(165,83,2,0.00,2500.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(166,83,30,2500.00,0.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(167,84,2,0.00,12000000.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(168,84,33,12000000.00,0.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(169,85,2,0.00,5000.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(170,85,30,5000.00,0.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(171,86,2,0.00,30000000.00,'HAKE','2026-10-08 22:43:04','2026-10-08 22:43:04'),(172,86,20,30000000.00,0.00,'HAKE','2026-10-08 22:43:04','2026-10-08 22:43:04'),(173,87,2,0.00,2500.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(174,87,30,2500.00,0.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(175,88,2,0.00,16500000.00,'HAKE free 1','2026-10-08 22:43:04','2026-10-08 22:43:04'),(176,88,20,16500000.00,0.00,'HAKE free 1','2026-10-08 22:43:04','2026-10-08 22:43:04'),(177,89,2,0.00,929527.00,'Cicilan 3','2026-10-08 22:43:04','2026-10-08 22:43:04'),(178,89,4,929527.00,0.00,'Cicilan 3','2026-10-08 22:43:04','2026-10-08 22:43:04'),(179,90,2,0.00,2500.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(180,90,30,2500.00,0.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(181,91,2,0.00,4000000.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(182,91,4,4000000.00,0.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(183,92,2,0.00,2500.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(184,92,30,2500.00,0.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(185,93,2,0.00,412975.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(186,93,29,412975.00,0.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(187,94,2,0.00,1600000.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(188,94,24,1600000.00,0.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(189,95,2,0.00,1000000.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(190,95,1,1000000.00,0.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(191,96,2,13500000.00,0.00,'PETNESIA RESINDO P','2026-10-08 22:43:04','2026-10-08 22:43:04'),(192,96,16,0.00,13500000.00,'PETNESIA RESINDO P','2026-10-08 22:43:04','2026-10-08 22:43:04'),(193,97,2,0.00,9000000.00,'Gunawan','2026-10-08 22:43:04','2026-10-08 22:43:04'),(194,97,21,9000000.00,0.00,'Gunawan','2026-10-08 22:43:04','2026-10-08 22:43:04'),(195,98,2,0.00,2500.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(196,98,30,2500.00,0.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(197,99,2,0.00,3300000.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(198,99,24,3300000.00,0.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(199,100,2,0.00,2500.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(200,100,30,2500.00,0.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(201,101,2,0.00,352000.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(202,101,27,352000.00,0.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(203,102,2,0.00,1000000.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(204,102,1,1000000.00,0.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(205,103,2,0.00,501600.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(206,103,29,501600.00,0.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(207,104,2,0.00,10000000.00,'Hake','2026-10-08 22:43:04','2026-10-08 22:43:04'),(208,104,20,10000000.00,0.00,'Hake','2026-10-08 22:43:04','2026-10-08 22:43:04'),(209,105,2,0.00,30000.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04'),(210,105,30,30000.00,0.00,NULL,'2026-10-08 22:43:04','2026-10-08 22:43:04');
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lsps`
--

LOCK TABLES `lsps` WRITE;
/*!40000 ALTER TABLE `lsps` DISABLE KEYS */;
INSERT INTO `lsps` VALUES (1,1,'LSP-K3-NAS','LSP Keselamatan Kerja Nasional (K3)','BNSP-LSP-045-ID/2022','Ir. Hendra Gunawan','081298765432','admin@lsp-k3nasional.org','Jl. Salemba Raya No. 12, Jakarta Pusat','Bank Mandiri','122-00-9876543-1','LSP Keselamatan Kerja Nasional',1,'2026-10-08 22:37:51','2026-10-08 22:37:51'),(2,1,'LSP-DIGIMA','LSP Digital Marketing Indonesia','BNSP-LSP-112-ID/2023','Anita Wulandari, S.Kom','081388990011','sertifikasi@lsp-digima.id','Gedung Cyber 2 Lt. 8, Kuningan, Jakarta Selatan','Bank BCA','542-019-8811','LSP Digital Marketing Indonesia',1,'2026-10-08 22:37:51','2026-10-08 22:37:51');
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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_10_09_023324_create_permission_tables',1),(5,'2026_10_09_030001_create_companies_table',1),(6,'2026_10_09_030002_add_company_and_profile_to_users_table',1),(7,'2026_10_09_030003_create_customers_lsps_vendors_trainers_tables',1),(8,'2026_10_09_030004_create_training_programs_and_batches_tables',1),(9,'2026_10_09_030005_create_accounting_core_tables',1),(10,'2026_10_09_030006_create_cash_and_bank_tables',1),(11,'2026_10_09_030007_create_invoices_and_receivables_tables',1),(12,'2026_10_09_030008_create_vendor_bills_and_payables_tables',1),(13,'2026_10_09_030009_create_budgets_and_rab_tables',1),(14,'2026_10_09_030010_create_audit_logs_and_attachments_tables',1),(15,'2026_10_09_042716_create_deletion_requests_table',1),(16,'2026_10_11_045837_create_participants_table',2);
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
-- Table structure for table `participants`
--

DROP TABLE IF EXISTS `participants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `participants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned DEFAULT NULL,
  `company_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nama Perusahaan / Klien',
  `category` enum('public','iht','webinar') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'public' COMMENT 'Kategori: Public, IHT, Webinar',
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nama Peserta',
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'No Telp / WhatsApp',
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email Peserta',
  `training_title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Jenis Pelatihan',
  `training_program_id` bigint unsigned DEFAULT NULL,
  `training_batch_id` bigint unsigned DEFAULT NULL,
  `training_date` date NOT NULL COMMENT 'Tanggal Pelaksanaan Pelatihan',
  `location` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Hotel / In-House / Online' COMMENT 'Lokasi Pelatihan',
  `price` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Harga Biaya Pelatihan',
  `source` enum('web','ig','bod','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'web' COMMENT 'Sumber Leads: Web, IG, BOD, Lainnya',
  `lsp_id` bigint unsigned DEFAULT NULL,
  `payment_status` enum('unpaid','partial','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid' COMMENT 'Status Pembayaran: Belum Bayar, Sebagian/DP, Lunas',
  `certificate_status` enum('process','passed','failed','issued') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'process' COMMENT 'Status Sertifikat: Proses, Kompeten, Belum Kompeten, Terbit',
  `notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Catatan Tambahan',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `participants_customer_id_foreign` (`customer_id`),
  KEY `participants_training_program_id_foreign` (`training_program_id`),
  KEY `participants_training_batch_id_foreign` (`training_batch_id`),
  KEY `participants_lsp_id_foreign` (`lsp_id`),
  KEY `participants_company_id_category_index` (`company_id`,`category`),
  KEY `participants_company_id_payment_status_index` (`company_id`,`payment_status`),
  KEY `participants_company_id_certificate_status_index` (`company_id`,`certificate_status`),
  KEY `participants_company_id_training_date_index` (`company_id`,`training_date`),
  CONSTRAINT `participants_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `participants_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `participants_lsp_id_foreign` FOREIGN KEY (`lsp_id`) REFERENCES `lsps` (`id`) ON DELETE SET NULL,
  CONSTRAINT `participants_training_batch_id_foreign` FOREIGN KEY (`training_batch_id`) REFERENCES `training_batches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `participants_training_program_id_foreign` FOREIGN KEY (`training_program_id`) REFERENCES `training_programs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `participants`
--

LOCK TABLES `participants` WRITE;
/*!40000 ALTER TABLE `participants` DISABLE KEYS */;
INSERT INTO `participants` VALUES (1,1,1,'PT Semen Nusantara Perkasa','iht','Ahmad Fauzi, S.T.','081288992211','ahmad.fauzi@semen-nusantara.co.id','Ahli K3 Umum Sertifikasi Kemnaker & BNSP',1,1,'2026-10-06','In-House Plant Cilegon (PT Semen Nusantara)',5500000.00,'bod',1,'paid','issued','Peserta perwakilan divisi HSE, lulus asesmen dengan predikat sangat memuaskan.','2026-10-10 22:01:53','2026-10-10 22:01:53'),(2,1,1,'PT Semen Nusantara Perkasa','iht','Rina Marlina, S.Psi','081377889900','rina.m@semen-nusantara.co.id','Human Resource Supervisor Sertifikasi BNSP',NULL,NULL,'2026-10-09','Head Office Semen Nusantara Jakarta',4500000.00,'bod',2,'paid','passed','Telah menyelesaikan asesmen portofolio.','2026-10-10 22:01:53','2026-10-10 22:01:53'),(3,1,2,'Dinas Ketenagakerjaan & Transmigrasi','public','Bayu Pratama','085611223344','bayu.pratama@disnakertrans.go.id','Petugas K3 Madya (Occupational Health & Safety)',1,1,'2026-10-14','Hotel Santika Premiere Slipi, Jakarta Barat',4800000.00,'web',1,'partial','process','DP 50% diterima, pelunasan saat registrasi ulang hari H.','2026-10-10 22:01:53','2026-10-10 22:01:53'),(4,1,NULL,'PT Karya Mandiri Solusindo','public','Dewi Sartika, S.E.','087799001122','dewi.sartika@karyamandiri.co.id','Digital Marketing & Growth Specialist BNSP',NULL,NULL,'2026-10-18','Gedung Optima Training Center, Tebet',3750000.00,'ig',2,'paid','process','Mendaftar via campaign Instagram Ads Batch 24.','2026-10-10 22:01:53','2026-10-10 22:01:53'),(5,1,NULL,'Universitas Indonesia (FKM)','webinar','Gilang Ramadhan','081912345678','gilang.ramadhan@ui.ac.id','Webinar Nasional: Implementasi SMK3 di Era Industri 5.0',NULL,NULL,'2026-10-23','Online via Zoom Meeting Live Streaming',150000.00,'web',NULL,'paid','process','E-Certificate kehadiran dan materi PDF akan dikirimkan otomatis H+1.','2026-10-10 22:01:53','2026-10-10 22:01:53'),(6,1,1,'PT Semen Nusantara Perkasa','webinar','Hendro Siswanto, S.T.','082133445566','hendro.s@semen-nusantara.co.id','Webinar Eksekutif: Manajemen Risiko & Audit Internal',NULL,NULL,'2026-10-26','Online Zoom Meeting',250000.00,'bod',NULL,'unpaid','process','Invoice kolektif bersama 5 peserta lain dari bagian General Affair.','2026-10-10 22:01:53','2026-10-10 22:01:53');
/*!40000 ALTER TABLE `participants` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_allocations`
--

LOCK TABLES `payment_allocations` WRITE;
/*!40000 ALTER TABLE `payment_allocations` DISABLE KEYS */;
INSERT INTO `payment_allocations` VALUES (1,1,1,26000000.00,'2026-10-08 22:37:51','2026-10-08 22:37:51');
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
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'view-accounts','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(2,'create-accounts','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(3,'edit-accounts','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(4,'delete-accounts','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(5,'view-journals','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(6,'create-journals','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(7,'post-journals','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(8,'reverse-journals','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(9,'view-ledger','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(10,'view-financial-reports','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(11,'close-period','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(12,'view-invoices','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(13,'create-invoices','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(14,'edit-invoices','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(15,'approve-invoices','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(16,'delete-invoices','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(17,'record-customer-payments','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(18,'view-customer-payments','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(19,'view-bills','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(20,'create-bills','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(21,'edit-bills','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(22,'approve-bills','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(23,'delete-bills','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(24,'record-vendor-payments','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(25,'view-vendor-payments','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(26,'view-cash-bank','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(27,'manage-cash-bank','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(28,'reconcile-bank','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(29,'view-batches','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(30,'create-batches','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(31,'edit-batches','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(32,'close-batches','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(33,'view-programs','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(34,'create-programs','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(35,'edit-programs','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(36,'delete-programs','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(37,'view-customers','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(38,'create-customers','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(39,'edit-customers','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(40,'view-lsps','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(41,'create-lsps','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(42,'edit-lsps','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(43,'delete-lsps','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(44,'view-vendors','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(45,'create-vendors','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(46,'edit-vendors','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(47,'view-trainers','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(48,'create-trainers','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(49,'edit-trainers','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(50,'delete-trainers','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(51,'view-budgets','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(52,'create-budgets','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(53,'edit-budgets','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(54,'approve-budgets','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(55,'manage-users','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(56,'view-users','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(57,'create-users','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(58,'edit-users','web','2026-10-08 22:37:48','2026-10-08 22:37:48'),(59,'delete-users','web','2026-10-08 22:37:49','2026-10-08 22:37:49'),(60,'approve-deletion','web','2026-10-08 22:37:49','2026-10-08 22:37:49'),(61,'manage-roles','web','2026-10-08 22:37:49','2026-10-08 22:37:49'),(62,'view-audit-logs','web','2026-10-08 22:37:49','2026-10-08 22:37:49'),(63,'manage-settings','web','2026-10-08 22:37:49','2026-10-08 22:37:49'),(64,'view-participants','web','2026-10-10 22:01:14','2026-10-10 22:01:14'),(65,'create-participants','web','2026-10-10 22:01:14','2026-10-10 22:01:14'),(66,'edit-participants','web','2026-10-10 22:01:14','2026-10-10 22:01:14'),(67,'delete-participants','web','2026-10-10 22:01:14','2026-10-10 22:01:14');
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
INSERT INTO `role_has_permissions` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(15,1),(16,1),(17,1),(18,1),(19,1),(20,1),(21,1),(22,1),(23,1),(24,1),(25,1),(26,1),(27,1),(28,1),(29,1),(30,1),(31,1),(32,1),(33,1),(34,1),(35,1),(36,1),(37,1),(38,1),(39,1),(40,1),(41,1),(42,1),(43,1),(44,1),(45,1),(46,1),(47,1),(48,1),(49,1),(50,1),(51,1),(52,1),(53,1),(54,1),(55,1),(56,1),(57,1),(58,1),(59,1),(60,1),(61,1),(62,1),(63,1),(64,1),(65,1),(66,1),(67,1),(1,2),(2,2),(3,2),(5,2),(6,2),(7,2),(8,2),(9,2),(10,2),(11,2),(12,2),(13,2),(15,2),(17,2),(18,2),(19,2),(20,2),(22,2),(24,2),(25,2),(26,2),(27,2),(28,2),(29,2),(33,2),(37,2),(40,2),(44,2),(47,2),(51,2),(54,2),(62,2),(64,2),(65,2),(66,2),(1,3),(5,3),(6,3),(9,3),(10,3),(12,3),(13,3),(17,3),(18,3),(19,3),(20,3),(24,3),(25,3),(26,3),(29,3),(37,3),(40,3),(44,3),(47,3),(51,3),(64,3),(29,4),(30,4),(31,4),(33,4),(37,4),(38,4),(39,4),(40,4),(47,4),(51,4),(52,4),(53,4),(64,4),(65,4),(66,4),(67,4),(1,5),(5,5),(9,5),(10,5),(12,5),(18,5),(19,5),(25,5),(26,5),(29,5),(51,5),(62,5),(64,5);
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
INSERT INTO `roles` VALUES (1,'Super Admin','web','2026-10-08 22:37:49','2026-10-08 22:37:49'),(2,'Finance Manager','web','2026-10-08 22:37:49','2026-10-08 22:37:49'),(3,'Accountant','web','2026-10-08 22:37:49','2026-10-08 22:37:49'),(4,'Operations Coordinator','web','2026-10-08 22:37:49','2026-10-08 22:37:49'),(5,'Auditor','web','2026-10-08 22:37:49','2026-10-08 22:37:49');
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
INSERT INTO `sessions` VALUES ('1QXub4j31Z1mNyv43uhQr46IprmPQZMlUhAYUF5S',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','eyJfdG9rZW4iOiI5d1h5dGx4VDdTM0RYZGdBWDhUam1OMFowMHRzZFg4dk5PbUs2V0JVIiwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjEsInBhc3N3b3JkX2hhc2hfd2ViIjoiZjhmMWIzZTE5MDFmNTUwOTk5NjNmYWY0YmEzMzQyY2M3MjY4OTI3ZGIyZDRkYzBmMGRmOWJkOTFkNjAwODk4NCIsIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC9jYXNoLWJhbms/YWNjb3VudF9pZD0yIiwicm91dGUiOiJjYXNoLWJhbmsuaW5kZXgifX0=',1791524735),('5A2OWYFzlQaplhSpmX5Jbh8THXL1a1Gf0fFHcTak',4,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJaVHdqdXJ1ZmVxakxwQVo2ZUIzclBudXlEVHdBbjJiYTBsVVVyZnI2IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL29wdGltYS50ZXN0XC9wYXJ0aWNpcGFudHMiLCJyb3V0ZSI6InBhcnRpY2lwYW50cy5pbmRleCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sInVybCI6W10sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjo0LCJwYXNzd29yZF9oYXNoX3dlYiI6ImE2Zjk1MDc3M2Y1YTM2MGM0N2E5MmM5NTM0NDI2MTVhOWJiNzI0ODhlYzlkYmI5ZGQ5ZDI3OTQxNWFiMzBlMjAifQ==',1791695327),('DCO0CUmoKIuuBi9IOp3MpcCoA0laAygtZyByhDXR',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.9444','eyJfdG9rZW4iOiJVS0RiUzk5OGFFNXA5SllJbUNESGFkOHRUclh3SzFzbVVhbkpsRFczIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL29wdGltYS50ZXN0XC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJ1cmwiOnsiaW50ZW5kZWQiOiJodHRwOlwvXC9vcHRpbWEudGVzdFwvZGFzaGJvYXJkIn19',1791693477),('DpFSeUMLCgV3uNWKDQV64jkZGTJ3R382d56eTi3T',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','eyJfdG9rZW4iOiI0Z3FvTFUycTY0MjhiUW9XUzRUcnVjcnZqRDFlWXZMVEh5S1dCa0JMIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL29wdGltYS50ZXN0XC9wYXJ0aWNpcGFudHNcLzYiLCJyb3V0ZSI6InBhcnRpY2lwYW50cy5zaG93In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjEsInBhc3N3b3JkX2hhc2hfd2ViIjoiZjhmMWIzZTE5MDFmNTUwOTk5NjNmYWY0YmEzMzQyY2M3MjY4OTI3ZGIyZDRkYzBmMGRmOWJkOTFkNjAwODk4NCJ9',1791698118);
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trainers`
--

LOCK TABLES `trainers` WRITE;
/*!40000 ALTER TABLE `trainers` DISABLE KEYS */;
INSERT INTO `trainers` VALUES (1,1,'TRN-001','Dr. Ir. Rudi Hartono, M.KKK','Ahli K3 Umum & Higiene Industri BNSP','081122334455','rudi.k3trainer@gmail.com','14.555.666.7-011.000','3171012304750001',3500000.00,'Bank BCA','542-881-2299','Rudi Hartono',1,'2026-10-08 22:37:51','2026-10-08 22:37:51');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `training_batches`
--

LOCK TABLES `training_batches` WRITE;
/*!40000 ALTER TABLE `training_batches` DISABLE KEYS */;
INSERT INTO `training_batches` VALUES (1,1,1,1,1,4,'BTH-2026-03-001','Batch 01 - Sertifikasi Ahli K3 Umum BNSP Maret 2026','2026-03-16','2026-03-19','2026-03-20','offline_hotel','Hotel Santika Premiere Slipi, Jakarta','Jakarta Barat',15,12,6500000.00,'ongoing','Batch berjalan. Klien utama PT Semen Nusantara Perkasa (8 peserta).','2026-10-08 22:37:51','2026-10-08 22:37:51');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `training_programs`
--

LOCK TABLES `training_programs` WRITE;
/*!40000 ALTER TABLE `training_programs` DISABLE KEYS */;
INSERT INTO `training_programs` VALUES (1,1,1,'PRG-K3U','Pelatihan & Sertifikasi Ahli K3 Umum BNSP','Skema Okupasi Ahli K3 Umum Kemenaker / BNSP','certification',4,6500000.00,1800000.00,'Paket pelatihan intensif K3, praktikum asesmen, dan uji kompetensi lisensi BNSP',1,'2026-10-08 22:37:51','2026-10-08 22:37:51');
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,'Super Administrator','admin@optima.local','081100000001',NULL,1,NULL,'$2y$12$cCl4RXFltrdFk5dpjgCDteHF3H9SAliAd9ihpR.UlwBytTa1q17Fe',NULL,'2026-10-08 22:37:50','2026-10-08 22:37:50'),(2,1,'Budi Santoso (Finance Manager)','finance.manager@optima.local','081100000002',NULL,1,NULL,'$2y$12$Bi6KXGy0Pj3rp32BcHYa0.y6l/WKeNhoNV5LmxljGNMZVxhQ9x8Fu',NULL,'2026-10-08 22:37:50','2026-10-08 22:37:50'),(3,1,'Siti Rahma (Staff Kasir & Akuntansi)','accountant@optima.local','081100000003',NULL,1,NULL,'$2y$12$G4Hv190pySZIyXBCi1rEv.5P8vTOkUqHKmlIjnERH//Jbkh7doTHC',NULL,'2026-10-08 22:37:50','2026-10-08 22:37:50'),(4,1,'Dimas Prasetyo (Koordinator Batch)','operations@optima.local','081100000004',NULL,1,NULL,'$2y$12$/ktMbUMu10VXTpozfDTOROzykmaxSH6cnmwF.twe.KDQ0CenCPXmK',NULL,'2026-10-08 22:37:50','2026-10-08 22:37:50'),(5,1,'Dewi Lestari (Internal Auditor)','auditor@optima.local','081100000005',NULL,1,NULL,'$2y$12$Gne7cdO7X5lTSt3wFp3O3OlnWQeH74A/VdVPt.fLDknA2QH2th.Lu',NULL,'2026-10-08 22:37:50','2026-10-08 22:37:50');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendor_bill_items`
--

LOCK TABLES `vendor_bill_items` WRITE;
/*!40000 ALTER TABLE `vendor_bill_items` DISABLE KEYS */;
INSERT INTO `vendor_bill_items` VALUES (1,1,20,'Uji Kompetensi & Sertifikat BNSP Ahli K3 Umum (12 asesi)',12,1800000.00,21600000.00,'2026-10-08 22:37:51','2026-10-08 22:37:51');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendor_bills`
--

LOCK TABLES `vendor_bills` WRITE;
/*!40000 ALTER TABLE `vendor_bills` DISABLE KEYS */;
INSERT INTO `vendor_bills` VALUES (1,1,1,NULL,NULL,1,3,'BILL-2026-03-0001','INV/LSP-K3/2026/041','2026-03-15','2026-03-29',21600000.00,0.00,21600000.00,0.00,21600000.00,'approved','Tagihan asesmen uji kompetensi K3 12 asesi',1,1,'2026-10-09 05:37:51','2026-10-08 22:37:51','2026-10-08 22:37:51');
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
INSERT INTO `vendors` VALUES (1,1,'VEND-HTL-01','Hotel Santika Premiere Slipi','hotel_venue','Fiona Sales Banquet','021-5330350','banquet@slipi.santika.com','01.999.888.7-014.000','Jl. Aipda KS Tubun No. 7, Jakarta Barat','Bank BCA','001-229-8765','PT Santika Graha Slipi',14,1,'2026-10-08 22:37:51','2026-10-08 22:37:51'),(2,1,'VEND-ATK-01','CV Grafika Mandiri Printing','stationery_printing','Rahmat Hidayat','081277665544','order@grafikamandiri.com','02.333.444.5-015.000','Jl. Percetakan Negara No. 88, Jakarta Pusat','Bank Mandiri','119-00-554433-2','CV Grafika Mandiri',14,1,'2026-10-08 22:37:51','2026-10-08 22:37:51');
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

-- Dump completed on 2026-10-11 13:06:22
