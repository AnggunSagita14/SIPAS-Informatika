-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: localhost    Database: sipas_informatika
-- ------------------------------------------------------
-- Server version	8.0.46

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
-- Current Database: `sipas_informatika`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `sipas_informatika` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `sipas_informatika`;

--
-- Table structure for table `applications`
--

DROP TABLE IF EXISTS `applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `applications` (
  `id_application` int NOT NULL AUTO_INCREMENT,
  `kode_surat` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kode_akses_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_user` int NOT NULL,
  `id_letter_type` int DEFAULT NULL,
  `jenis_surat_lainnya` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tujuan_pengajuan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_pengajuan` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status_pengajuan` enum('draft','menunggu_verifikasi','perlu_perbaikan','menunggu_persetujuan','disetujui','ditolak','selesai') COLLATE utf8mb4_unicode_ci DEFAULT 'menunggu_verifikasi',
  `catatan_admin` text COLLATE utf8mb4_unicode_ci,
  `kode_akses_enkripsi` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id_application`),
  UNIQUE KEY `kode_surat` (`kode_surat`),
  KEY `fk_applications_users` (`id_user`),
  KEY `fk_applications_letter_types` (`id_letter_type`),
  CONSTRAINT `fk_applications_letter_types` FOREIGN KEY (`id_letter_type`) REFERENCES `letter_types` (`id_letter_type`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_applications_users` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `applications`
--

LOCK TABLES `applications` WRITE;
/*!40000 ALTER TABLE `applications` DISABLE KEYS */;
INSERT INTO `applications` VALUES (6,'SIPAS-20261118-99FDC6','$2y$10$JrYpP5o73KEFOdkpPZVqVObjQArpwUa372LM0LNrobvKSAAWx.WLq',4,14,NULL,'Untuk memenuhi kebutuhan magang.','2026-11-18 12:37:08','selesai',NULL,NULL);
/*!40000 ALTER TABLE `applications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `approval_logs`
--

DROP TABLE IF EXISTS `approval_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `approval_logs` (
  `id_approval` int NOT NULL AUTO_INCREMENT,
  `id_application` int NOT NULL,
  `id_user` int NOT NULL,
  `tindakan` enum('verifikasi','perlu_perbaikan','disetujui','ditolak') COLLATE utf8mb4_unicode_ci NOT NULL,
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `tanggal_tindakan` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_approval`),
  KEY `fk_approval_applications` (`id_application`),
  KEY `fk_approval_users` (`id_user`),
  CONSTRAINT `fk_approval_applications` FOREIGN KEY (`id_application`) REFERENCES `applications` (`id_application`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_approval_users` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `approval_logs`
--

LOCK TABLES `approval_logs` WRITE;
/*!40000 ALTER TABLE `approval_logs` DISABLE KEYS */;
INSERT INTO `approval_logs` VALUES (6,6,1,'disetujui',NULL,'2026-11-18 13:58:14');
/*!40000 ALTER TABLE `approval_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `issued_letters`
--

DROP TABLE IF EXISTS `issued_letters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `issued_letters` (
  `id_issued_letter` int NOT NULL AUTO_INCREMENT,
  `id_application` int NOT NULL,
  `nomor_surat` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_terbit` date NOT NULL,
  `file_surat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `diterbitkan_oleh` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_issued_letter`),
  UNIQUE KEY `id_application` (`id_application`),
  UNIQUE KEY `nomor_surat` (`nomor_surat`),
  KEY `fk_issued_users` (`diterbitkan_oleh`),
  CONSTRAINT `fk_issued_applications` FOREIGN KEY (`id_application`) REFERENCES `applications` (`id_application`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_issued_users` FOREIGN KEY (`diterbitkan_oleh`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `issued_letters`
--

LOCK TABLES `issued_letters` WRITE;
/*!40000 ALTER TABLE `issued_letters` DISABLE KEYS */;
INSERT INTO `issued_letters` VALUES (4,6,'INF123','2026-11-18','surat_6_1f569f1431893221.pdf',1,'2026-11-18 14:02:12');
/*!40000 ALTER TABLE `issued_letters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `letter_types`
--

DROP TABLE IF EXISTS `letter_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `letter_types` (
  `id_letter_type` int NOT NULL AUTO_INCREMENT,
  `nama_surat` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `status_aktif` enum('aktif','nonaktif') COLLATE utf8mb4_unicode_ci DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_letter_type`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `letter_types`
--

LOCK TABLES `letter_types` WRITE;
/*!40000 ALTER TABLE `letter_types` DISABLE KEYS */;
INSERT INTO `letter_types` VALUES (1,'Surat Keterangan Mahasiswa','Surat keterangan bahwa seseorang terdaftar sebagai mahasiswa aktif.','aktif','2026-11-17 15:55:19'),(2,'Surat Keterangan Lulus','Surat keterangan telah menyelesaikan pendidikan.','aktif','2026-11-17 15:55:19'),(3,'Surat Keterangan Tidak Menerima Beasiswa','Surat keterangan untuk keperluan administrasi beasiswa.','aktif','2026-11-17 15:55:19'),(4,'Surat Keterangan Pengganti KTM','Surat keterangan untuk keperluan penggantian kartu tanda mahasiswa.','aktif','2026-11-17 15:55:19'),(5,'Surat Keterangan Perubahan Data Mahasiswa','Surat keterangan perubahan data mahasiswa.','aktif','2026-11-17 15:55:19'),(6,'Surat Keterangan Cuti Akademik','Surat keterangan terkait cuti akademik mahasiswa.','aktif','2026-11-17 15:55:19'),(7,'Surat Keterangan Tidak Sedang Cuti','Surat keterangan bahwa mahasiswa tidak sedang menjalani cuti akademik.','aktif','2026-11-17 15:55:19'),(8,'Surat Izin Penelitian','Surat izin untuk melaksanakan kegiatan penelitian.','aktif','2026-11-17 15:55:19'),(9,'Surat Pengantar Observasi','Surat pengantar untuk kegiatan observasi.','aktif','2026-11-17 15:55:19'),(10,'Surat Pengantar Wawancara','Surat pengantar untuk kegiatan wawancara.','aktif','2026-11-17 15:55:19'),(11,'Surat Pengantar Pengambilan Data','Surat pengantar untuk kegiatan pengambilan data.','aktif','2026-11-17 15:55:19'),(12,'Surat Pengantar Praktik Kerja Lapangan (PKL)','Surat pengantar pelaksanaan praktik kerja lapangan.','aktif','2026-11-17 15:55:19'),(13,'Surat Pengantar Kerja Praktik (KP)','Surat pengantar pelaksanaan kerja praktik.','aktif','2026-11-17 15:55:19'),(14,'Surat Pengantar Magang','Surat pengantar untuk kegiatan magang.','aktif','2026-11-17 15:55:19'),(15,'Surat Rekomendasi Beasiswa','Surat rekomendasi untuk keperluan pendaftaran beasiswa.','aktif','2026-11-17 15:55:19'),(16,'Surat Rekomendasi Mengikuti Kegiatan','Surat rekomendasi untuk mengikuti kegiatan tertentu.','aktif','2026-11-17 15:55:19'),(17,'Surat Keterangan Prestasi Akademik','Surat keterangan mengenai prestasi akademik mahasiswa.','aktif','2026-11-17 15:55:19'),(18,'Surat Pengantar Kompetisi atau Lomba','Surat pengantar untuk mengikuti kompetisi atau perlombaan.','aktif','2026-11-17 15:55:19'),(19,'Surat Rekomendasi Organisasi','Surat rekomendasi untuk keperluan organisasi.','aktif','2026-11-17 15:55:19'),(20,'Surat Keterangan untuk Keperluan Administrasi Eksternal','Surat keterangan untuk keperluan administrasi di luar kampus.','aktif','2026-11-17 15:55:19');
/*!40000 ALTER TABLE `letter_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id_role` int NOT NULL AUTO_INCREMENT,
  `nama_role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_role`),
  UNIQUE KEY `nama_role` (`nama_role`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (2,'admin'),(3,'ketua_prodi'),(1,'mahasiswa');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `id_role` int NOT NULL,
  `nim` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_lengkap` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_akun` enum('aktif','nonaktif') COLLATE utf8mb4_unicode_ci DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `nim` (`nim`),
  KEY `fk_users_roles` (`id_role`),
  CONSTRAINT `fk_users_roles` FOREIGN KEY (`id_role`) REFERENCES `roles` (`id_role`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,2,NULL,'Admin SIPAS','admin@sipas.com','$2y$10$hT61iIQMegQaeiF.EX4uTe6ugUNNWoaewSTwjRv.lG9UKlXlPTCGS',NULL,'aktif','2026-11-17 13:13:15'),(2,1,'123456789','Mahasiswa Percobaan','mahasiswa@sipas.com','$2y$10$oyKsmRWvyfxf7N8gYks9QOH.O6qTJQsZ4SGIZr/Ie4MI9ylzqc0pW',NULL,'aktif','2026-11-17 13:29:58'),(3,3,NULL,'Ketua Program Studi','ketuaprodi@sipas.com','$2y$10$0lTPy92AT8YSEp4nbkjWjOdFvdJQY235Izd73pOZJxnLS23dMsIGK','081234567890','nonaktif','2026-11-17 14:37:28'),(4,1,'D1041241085','Anggun Sagita',NULL,NULL,'08111111111111','aktif','2026-11-18 12:37:08'),(5,1,'D1041241001','Sagita Permana',NULL,NULL,'081111111111111','aktif','2026-11-18 13:57:54'),(6,1,'D1041241002','Faturrahman Pasha',NULL,NULL,'123456789','aktif','2026-11-18 14:07:31'),(7,1,'D1041241072','Oktavia Namu Erdita',NULL,NULL,'08111111111111','aktif','2026-11-19 15:41:39'),(8,1,'D1041241000','Mahasiswa Uji SIPAS',NULL,NULL,'081234567890','aktif','2026-11-20 06:37:35');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-20 20:35:46
