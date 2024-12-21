<?php
session_start();
include('../config/koneksi.php'); // Pastikan untuk menyertakan file koneksi database

// Cek apakah user sudah login dan memiliki peran 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Ambil data ringkasan dari database
$query = "SELECT COUNT(*) AS mahasiswa_count FROM mahasiswa";
$result = mysqli_query($conn, $query);
$mahasiswa = mysqli_fetch_assoc($result)['mahasiswa_count'];

$query = "SELECT COUNT(*) AS dosen_count FROM dosen";
$result = mysqli_query($conn, $query);
$dosen = mysqli_fetch_assoc($result)['dosen_count'];

$query = "SELECT COUNT(*) AS prodi_count FROM program_studi";
$result = mysqli_query($conn, $query);
$prodi = mysqli_fetch_assoc($result)['prodi_count'];

$query = "SELECT COUNT(*) AS mk_count FROM mata_kuliah";
$result = mysqli_query($conn, $query);
$mk = mysqli_fetch_assoc($result)['mk_count'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <!-- Link ke Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .summary ul {
            list-style: none;
            padding: 0;
        }
        .summary li {
            font-size: 18px;
        }
        .features a {
            font-size: 18px;
            color: #007bff;
            text-decoration: none;
        }
        .features a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Sistem Akademik</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4">Selamat Datang, Admin</h1>

        <!-- Ringkasan Data -->
        <div class="summary mb-4">
            <h2>Ringkasan Data</h2>
            <ul class="list-group">
                <li class="list-group-item">Jumlah Mahasiswa: <?= $mahasiswa ?></li>
                <li class="list-group-item">Jumlah Dosen: <?= $dosen ?></li>
                <li class="list-group-item">Jumlah Program Studi: <?= $prodi ?></li>
                <li class="list-group-item">Jumlah Mata Kuliah: <?= $mk ?></li>
            </ul>
        </div>

        <!-- Fitur Admin -->
        <div class="features">
            <h3>Fitur Admin:</h3>
            <div class="list-group">
                <a href="program_studi/program_studi.php" class="list-group-item list-group-item-action">Manajemen Program Studi</a>
                <a href="mahasiswa/mahasiswa.php" class="list-group-item list-group-item-action">Manajemen Mahasiswa</a>
                <a href="dosen/dosen.php" class="list-group-item list-group-item-action">Manajemen Dosen</a>
                <a href="matkul/matkul.php" class="list-group-item list-group-item-action">Manajemen Mata Kuliah</a>
                <a href="ruangan.php" class="list-group-item list-group-item-action">Manajemen Ruangan</a>
                <a href="jadwal_kuliah.php" class="list-group-item list-group-item-action">Penjadwalan Kuliah</a>
            </div>
        </div>
    </div>

    <!-- Link ke Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
