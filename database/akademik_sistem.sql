CREATE DATABASE akademik_sistem;
USE akademik_sistem;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    PASSWORD VARCHAR(255) NOT NULL,
    role ENUM('admin', 'dosen', 'mahasiswa') NOT NULL
);

CREATE TABLE program_studi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_prodi VARCHAR(100) NOT NULL,
    fakultas VARCHAR(100) NOT NULL
);

CREATE TABLE mahasiswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(15) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    tanggal_lahir DATE NOT NULL,
    alamat TEXT NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    no_telepon VARCHAR(15) NOT NULL,
    angkatan YEAR NOT NULL,
    id_prodi INT NOT NULL,
    user_id INT NOT NULL,
    FOREIGN KEY (id_prodi) REFERENCES program_studi(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE dosen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nip VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    no_telepon VARCHAR(15) NOT NULL,
    jabatan VARCHAR(50) NOT NULL,
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE mata_kuliah (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_mk VARCHAR(10) NOT NULL UNIQUE,
    nama_mk VARCHAR(100) NOT NULL,
    sks INT NOT NULL,
    semester INT NOT NULL,
    id_prodi INT NOT NULL,
    id_dosen INT NOT NULL,
    FOREIGN KEY (id_dosen) REFERENCES dosen(id) ON DELETE SET NULL,
    FOREIGN KEY (id_prodi) REFERENCES program_studi(id) ON DELETE CASCADE
);

CREATE TABLE jadwal_kuliah (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_mata_kuliah INT NOT NULL,
    id_dosen INT NOT NULL,
    hari ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu') NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    ruangan VARCHAR(50) NOT NULL,
    FOREIGN KEY (id_mata_kuliah) REFERENCES mata_kuliah(id) ON DELETE CASCADE,
    FOREIGN KEY (id_dosen) REFERENCES dosen(id) ON DELETE CASCADE
);

CREATE TABLE krs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_mahasiswa INT NOT NULL,
    id_mata_kuliah INT NOT NULL,
    semester INT NOT NULL,
    tahun_ajaran VARCHAR(10) NOT NULL,
    FOREIGN KEY (id_mahasiswa) REFERENCES mahasiswa(id) ON DELETE CASCADE,
    FOREIGN KEY (id_mata_kuliah) REFERENCES mata_kuliah(id) ON DELETE CASCADE
);

CREATE TABLE khs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_mahasiswa INT NOT NULL,
    id_mata_kuliah INT NOT NULL,
    nilai_angka DECIMAL(4, 2) NOT NULL,
    nilai_huruf CHAR(2) NOT NULL,
    semester INT NOT NULL,
    tahun_ajaran VARCHAR(10) NOT NULL,
    FOREIGN KEY (id_mahasiswa) REFERENCES mahasiswa(id) ON DELETE CASCADE,
    FOREIGN KEY (id_mata_kuliah) REFERENCES mata_kuliah(id) ON DELETE CASCADE
);

CREATE TABLE ruangan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_ruangan VARCHAR(10) NOT NULL UNIQUE,
    nama_ruangan VARCHAR(50) NOT NULL,
    kapasitas INT NOT NULL,
    lokasi VARCHAR(100) NOT NULL
);

CREATE TABLE semester (
    id INT AUTO_INCREMENT PRIMARY KEY,
    semester_aktif ENUM('Ganjil', 'Genap') NOT NULL,
    tahun_ajaran VARCHAR(10) NOT NULL
);

CREATE TABLE absensi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jadwal_id INT NOT NULL,
    mahasiswa_id INT NOT NULL,
    status_absen ENUM('hadir', 'tidak hadir', 'izin', 'alfa') NOT NULL,
    waktu_absen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (jadwal_id) REFERENCES jadwal_kuliah(id),
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id)
);


CREATE TABLE jenis_soal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(50) NOT NULL -- ('Tugas', 'Kuis', 'UTS', 'UAS')
);

CREATE TABLE bank_soal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_dosen INT NOT NULL,
    id_mata_kuliah INT NOT NULL,
    id_jenis_soal INT NOT NULL,
    judul VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    tanggal_dibuat DATETIME DEFAULT CURRENT_TIMESTAMP,
    batas_waktu DATETIME,
    STATUS ENUM('aktif', 'tidak aktif') DEFAULT 'aktif',
    FOREIGN KEY (id_dosen) REFERENCES dosen(id) ON DELETE CASCADE,
    FOREIGN KEY (id_mata_kuliah) REFERENCES mata_kuliah(id) ON DELETE CASCADE,
    FOREIGN KEY (id_jenis_soal) REFERENCES jenis_soal(id)
);

CREATE TABLE soal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_bank_soal INT NOT NULL,
    pertanyaan TEXT NOT NULL,
    bobot_nilai DECIMAL(5,2) NOT NULL,
    FOREIGN KEY (id_bank_soal) REFERENCES bank_soal(id) ON DELETE CASCADE
);


-- Insert basic exam types
INSERT INTO jenis_soal (nama) VALUES 
('Tugas'),
('Kuis'),
('UTS'),
('UAS');

-- Admin User
INSERT INTO users (username, PASSWORD, role) VALUES 
('admin', 'admin123', 'admin');

-- Program Studi
INSERT INTO program_studi (nama_prodi, fakultas) VALUES 
('Teknik Informatika', 'Fakultas Teknik'),
('Sistem Informasi', 'Fakultas Teknik');

-- Mahasiswa
INSERT INTO users (username, password, role) VALUES 
('mahasiswa1', 'mahasiswa123', 'mahasiswa');

INSERT INTO mahasiswa (nim, nama, tanggal_lahir, alamat, email, no_telepon, angkatan, id_prodi, user_id) VALUES 
('123456789', 'Budi Santoso', '2000-01-01', 'Jl. Merdeka', 'budi@example.com', '081234567890', 2020, 1, 2);

-- Dosen
INSERT INTO users (username, password, role) VALUES 
('dosen1', 'dosen123', 'dosen');

INSERT INTO dosen (nip, nama, email, no_telepon, jabatan, user_id) VALUES 
('1987654321', 'Dr. Andi', 'andi@example.com', '081298765432', 'Dosen Tetap', 3);

-- Insert data KRS untuk mahasiswa
INSERT INTO krs (id_mahasiswa, id_mata_kuliah, semester, tahun_ajaran) 
VALUES 
(7, 5, 1, '2023/2024'),  -- Semester 1, Tahun Ajaran 2023/2024 untuk mahasiswa 2
(8, 6, 1, '2023/2024');   -- Semester 1, Tahun Ajaran 2023/2024 untuk mahasiswa 2


