/*
SQLyog Job Agent v12.4.3 (64 bit) Copyright(c) Webyog Inc. All Rights Reserved.


MySQL - 10.4.32-MariaDB : Database - akademik_sistem
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`akademik_sistem` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `akademik_sistem`;

/*Table structure for table `absensi` */

DROP TABLE IF EXISTS `absensi`;

CREATE TABLE `absensi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jadwal_id` int(11) NOT NULL,
  `mahasiswa_id` int(11) NOT NULL,
  `status_absen` enum('hadir','tidak hadir','izin','alfa') NOT NULL,
  `waktu_absen` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `jadwal_id` (`jadwal_id`),
  KEY `mahasiswa_id` (`mahasiswa_id`),
  CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`jadwal_id`) REFERENCES `jadwal_kuliah` (`id`),
  CONSTRAINT `absensi_ibfk_2` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `absensi` */

insert  into `absensi` values 
(1,14,7,'hadir','2025-01-05 05:18:20'),
(2,14,7,'hadir','2025-01-05 05:21:08'),
(3,14,7,'hadir','2025-01-05 05:22:09'),
(4,14,7,'hadir','2025-01-05 05:24:42'),
(5,14,7,'hadir','2025-01-05 05:25:36'),
(6,14,7,'hadir','2025-01-05 05:27:23'),
(7,14,7,'hadir','2025-01-05 05:28:28'),
(8,14,7,'hadir','2025-01-05 05:29:12'),
(9,14,7,'hadir','2025-01-05 05:29:39'),
(10,14,7,'hadir','2025-01-05 05:32:00'),
(11,14,7,'hadir','2025-01-05 05:32:22'),
(12,14,7,'hadir','2025-01-05 05:32:33');

/*Table structure for table `bank_soal` */

DROP TABLE IF EXISTS `bank_soal`;

CREATE TABLE `bank_soal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_dosen` int(11) NOT NULL,
  `id_mata_kuliah` int(11) NOT NULL,
  `id_jenis_soal` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tanggal_dibuat` datetime DEFAULT current_timestamp(),
  `batas_waktu` datetime DEFAULT NULL,
  `STATUS` enum('aktif','tidak_aktif') DEFAULT 'aktif',
  `file_soal` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_dosen` (`id_dosen`),
  KEY `id_mata_kuliah` (`id_mata_kuliah`),
  KEY `id_jenis_soal` (`id_jenis_soal`),
  CONSTRAINT `bank_soal_ibfk_1` FOREIGN KEY (`id_dosen`) REFERENCES `dosen` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bank_soal_ibfk_2` FOREIGN KEY (`id_mata_kuliah`) REFERENCES `mata_kuliah` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bank_soal_ibfk_3` FOREIGN KEY (`id_jenis_soal`) REFERENCES `jenis_soal` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `bank_soal` */

insert  into `bank_soal` values 
(49,13,4,1,'asd','asdasd','2025-01-13 01:39:35','2025-01-20 08:00:00','aktif','UTS RPL AHMAD WAHYUDI TANJUNG.docx'),
(51,13,9,1,'Tugas 1','asdqwezxasd','2025-01-13 05:18:54','2025-01-15 23:59:00','aktif','Depedensial & Normalisasi.pdf');

/*Table structure for table `done_tugas` */

DROP TABLE IF EXISTS `done_tugas`;

CREATE TABLE `done_tugas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nilai` decimal(5,2) DEFAULT 0.00,
  `id_bank_soal` int(11) NOT NULL,
  `id_mahasiswa` int(11) NOT NULL,
  `file_jawaban` varchar(255) DEFAULT NULL,
  `status` enum('selesai','belum selesai') NOT NULL DEFAULT 'belum selesai',
  PRIMARY KEY (`id`),
  KEY `id_bank_soal` (`id_bank_soal`),
  KEY `id_mahasiswa` (`id_mahasiswa`),
  CONSTRAINT `done_tugas_ibfk_1` FOREIGN KEY (`id_bank_soal`) REFERENCES `bank_soal` (`id`) ON DELETE CASCADE,
  CONSTRAINT `done_tugas_ibfk_2` FOREIGN KEY (`id_mahasiswa`) REFERENCES `mahasiswa` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `done_tugas` */

insert  into `done_tugas` values 
(6,100.00,49,7,'Depedensial & Normalisasi.pdf','selesai'),
(7,60.00,51,7,'Update Modul Logika & Algortima (Bahasa C) 2024-2025.pdf','selesai');

/*Table structure for table `dosen` */

DROP TABLE IF EXISTS `dosen`;

CREATE TABLE `dosen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nip` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_telepon` varchar(15) NOT NULL,
  `jabatan` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nip` (`nip`),
  UNIQUE KEY `email` (`email`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `dosen_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `dosen` */

insert  into `dosen` values 
(13,'020000001','qweqwe','wahyutjg010@gmail.com','08765323456','Dosen',53),
(16,'020000002','asdasd','asdasd@gmail.com','081234323456765','Dosen',66);

/*Table structure for table `jadwal_kuliah` */

DROP TABLE IF EXISTS `jadwal_kuliah`;

CREATE TABLE `jadwal_kuliah` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mata_kuliah` int(11) NOT NULL,
  `id_dosen` int(11) NOT NULL,
  `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu') NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `ruangan` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_mata_kuliah` (`id_mata_kuliah`),
  KEY `id_dosen` (`id_dosen`),
  CONSTRAINT `jadwal_kuliah_ibfk_1` FOREIGN KEY (`id_mata_kuliah`) REFERENCES `mata_kuliah` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jadwal_kuliah_ibfk_2` FOREIGN KEY (`id_dosen`) REFERENCES `dosen` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `jadwal_kuliah` */

insert  into `jadwal_kuliah` values 
(14,4,13,'Senin','08:00:00','10:30:00','B31'),
(15,5,13,'Selasa','10:00:00','12:30:00','2B'),
(18,10,13,'Selasa','08:00:00','09:40:00','B31'),
(19,11,16,'Selasa','10:00:00','11:40:00','B31'),
(20,9,13,'Senin','11:00:00','13:30:00','2B');

/*Table structure for table `jenis_soal` */

DROP TABLE IF EXISTS `jenis_soal`;

CREATE TABLE `jenis_soal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `jenis_soal` */

insert  into `jenis_soal` values 
(1,'Tugas'),
(2,'Kuis'),
(3,'UTS'),
(4,'UAS');

/*Table structure for table `khs` */

DROP TABLE IF EXISTS `khs`;

CREATE TABLE `khs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mahasiswa` int(11) NOT NULL,
  `id_mata_kuliah` int(11) NOT NULL,
  `nilai_angka` decimal(5,2) NOT NULL DEFAULT 0.00,
  `nilai_huruf` char(2) NOT NULL,
  `semester` int(11) NOT NULL,
  `uts` int(11) NOT NULL DEFAULT 0,
  `kuis` int(11) NOT NULL DEFAULT 0,
  `uas` int(11) NOT NULL DEFAULT 0,
  `tugas` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `id_mahasiswa` (`id_mahasiswa`),
  KEY `id_mata_kuliah` (`id_mata_kuliah`),
  CONSTRAINT `khs_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `khs_ibfk_2` FOREIGN KEY (`id_mata_kuliah`) REFERENCES `mata_kuliah` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `khs` */

insert  into `khs` values 
(8,7,4,0.00,'E',1,0,0,0,11);

/*Table structure for table `krs` */

DROP TABLE IF EXISTS `krs`;

CREATE TABLE `krs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mahasiswa` int(11) NOT NULL,
  `id_mata_kuliah` int(11) NOT NULL,
  `tahun_ajaran` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_mahasiswa` (`id_mahasiswa`),
  KEY `id_mata_kuliah` (`id_mata_kuliah`),
  CONSTRAINT `krs_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `krs_ibfk_2` FOREIGN KEY (`id_mata_kuliah`) REFERENCES `mata_kuliah` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `krs` */

insert  into `krs` values 
(70,7,5,'2025/2026'),
(78,7,4,'2025/2026'),
(79,7,9,'2025/2026');

/*Table structure for table `mahasiswa` */

DROP TABLE IF EXISTS `mahasiswa`;

CREATE TABLE `mahasiswa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nim` varchar(15) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `alamat` text NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_telepon` varchar(15) NOT NULL,
  `angkatan` year(4) NOT NULL,
  `id_prodi` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `semester` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nim` (`nim`),
  UNIQUE KEY `email` (`email`),
  KEY `id_prodi` (`id_prodi`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `mahasiswa_ibfk_1` FOREIGN KEY (`id_prodi`) REFERENCES `program_studi` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mahasiswa_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `mahasiswa` */

insert  into `mahasiswa` values 
(7,'02042311001','AHMAD WAHYUDI TANJUNG','2025-05-12','gxfcrtsrsrs ytrs','wahyutjg123@gmail.com','08123456789',2022,1,60,1),
(9,'02032311002','Vira','2025-01-07','asdasd','vira123@gmail.com','08123321123',2023,5,65,1);

/*Table structure for table `mata_kuliah` */

DROP TABLE IF EXISTS `mata_kuliah`;

CREATE TABLE `mata_kuliah` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_mk` varchar(10) NOT NULL,
  `nama_mk` varchar(100) NOT NULL,
  `sks` int(11) NOT NULL,
  `semester` int(11) NOT NULL,
  `id_prodi` int(11) NOT NULL,
  `id_dosen` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_mk` (`kode_mk`),
  KEY `id_prodi` (`id_prodi`),
  KEY `id_dosen` (`id_dosen`),
  CONSTRAINT `mata_kuliah_ibfk_1` FOREIGN KEY (`id_prodi`) REFERENCES `program_studi` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mata_kuliah_ibfk_2` FOREIGN KEY (`id_dosen`) REFERENCES `dosen` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `mata_kuliah` */

insert  into `mata_kuliah` values 
(4,'002','C++',3,3,1,13),
(5,'003','C',3,3,1,13),
(9,'001','JAVA',3,3,1,13),
(10,'004','VB',2,2,5,13),
(11,'005','React',2,3,3,16);

/*Table structure for table `program_studi` */

DROP TABLE IF EXISTS `program_studi`;

CREATE TABLE `program_studi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_prodi` varchar(100) NOT NULL,
  `fakultas` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `program_studi` */

insert  into `program_studi` values 
(1,'Teknik Informatika','Ilmu Komputer'),
(3,'Akutansi','Akutansi'),
(5,'Manajemen Informatika','Ilmu Komputer');

/*Table structure for table `ruangan` */

DROP TABLE IF EXISTS `ruangan`;

CREATE TABLE `ruangan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_ruangan` varchar(10) NOT NULL,
  `nama_ruangan` varchar(50) NOT NULL,
  `kapasitas` int(11) NOT NULL,
  `lokasi` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_ruangan` (`kode_ruangan`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `ruangan` */

insert  into `ruangan` values 
(1,'002','B31',30,'Lantai 1'),
(2,'003','2B',20,'Lantai 1');

/*Table structure for table `semester` */

DROP TABLE IF EXISTS `semester`;

CREATE TABLE `semester` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `semester_aktif` enum('Ganjil','Genap') NOT NULL,
  `tahun_ajaran` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `semester` */

/*Table structure for table `soal` */

DROP TABLE IF EXISTS `soal`;

CREATE TABLE `soal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_bank_soal` int(11) NOT NULL,
  `pertanyaan` text NOT NULL,
  `bobot_nilai` decimal(5,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_bank_soal` (`id_bank_soal`),
  CONSTRAINT `soal_ibfk_1` FOREIGN KEY (`id_bank_soal`) REFERENCES `bank_soal` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `soal` */

insert  into `soal` values 
(43,49,'asdasd',10.00),
(45,51,'asdqwezxasd',10.00);

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','dosen','mahasiswa') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `users` */

insert  into `users` values 
(1,'dosen1','dosen123','dosen'),
(8,'admin','$2y$10$RqZBcHYb3kTxa8ejoLH8BupTG6XB8HbF8OwhKWZ7AXe2YP69LyJs.','admin'),
(36,'02042311005','$2y$10$Ek.5NDV5JdWt9YhvU8W6vefOtb2V3pxHVzFtK5Cd/RrK.i6OTfPCa','mahasiswa'),
(53,'020000001','$2y$10$I1EVHghDNn8L8sZKScMssueqIuuopmkNYlgaU5JDfP00vlcbHMiOC','dosen'),
(60,'02042311001','$2y$10$xBPCXWCmsLVo95a6lOaLuusbKjtBE7aUoANZ7k7iNs4aSfPI/quFC','mahasiswa'),
(64,'020000003','$2y$10$oB4RFV5LENgZ/lWzd7Y9OuRBvAm/D/eyetmmV4RgIpMjY7d2TP262','dosen'),
(65,'02032311002','$2y$10$AQrbTFHyK.KW2nVYUAQXZ.D9V5.7cNlznZJxLLxXAs2c..jFcp3k.','mahasiswa'),
(66,'020000002','$2y$10$S9GMAlU8t1j4s./QZmYBcemvDpVoY41R8rehJOcLv7SNYyiLPaDtS','dosen');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
