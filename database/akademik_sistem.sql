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


