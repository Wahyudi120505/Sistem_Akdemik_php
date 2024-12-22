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
    <title>Dashboard Admin - Sistem Akademik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #2ecc71;
            --warning-color: #f1c40f;
            --danger-color: #e74c3c;
            --light-gray: #f8f9fa;
            --dark-gray: #343a40;
        }

        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background-color: var(--primary-color) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-weight: 600;
            font-size: 1.5rem;
        }

        .nav-link {
            font-weight: 500;
        }

        .dashboard-header {
            background-color: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stats-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .stats-icon i {
            font-size: 1.5rem;
            color: white;
        }

        .stats-info h3 {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .stats-info p {
            color: #6c757d;
            margin: 0;
        }

        .features-section {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .feature-card {
            border: none;
            border-radius: 10px;
            transition: all 0.3s ease;
            background: var(--light-gray);
            margin-bottom: 1rem;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .feature-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--secondary-color);
        }

        .btn-logout {
            background-color: var(--danger-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .btn-logout:hover {
            background-color: #c0392b;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-university me-2"></i>
                Sistem Akademik 
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">
                            <i class="fas fa-tachometer-alt me-1"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-logout" href="../logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>
                            Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <h1 class="mb-0">
                <i class="fas fa-user-shield me-2"></i>
                Selamat Datang, Admin
            </h1>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background-color: var(--primary-color);">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stats-info">
                        <h3><?= $mahasiswa ?></h3>
                        <p>Mahasiswa</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background-color: var(--success-color);">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stats-info">
                        <h3><?= $dosen ?></h3>
                        <p>Dosen</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background-color: var(--warning-color);">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="stats-info">
                        <h3><?= $prodi ?></h3>
                        <p>Program Studi</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background-color: var(--secondary-color);">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stats-info">
                        <h3><?= $mk ?></h3>
                        <p>Mata Kuliah</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="features-section">
            <h2 class="mb-4">
                <i class="fas fa-cogs me-2"></i>
                Manajemen Sistem
            </h2>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <a href="program_studi/program_studi.php" class="text-decoration-none">
                        <div class="feature-card card h-100 p-4">
                            <div class="text-center">
                                <i class="fas fa-graduation-cap feature-icon"></i>
                                <h4>Program Studi</h4>
                                <p class="text-muted">Kelola program studi dan kurikulum</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4 mb-3">
                    <a href="mahasiswa/mahasiswa.php" class="text-decoration-none">
                        <div class="feature-card card h-100 p-4">
                            <div class="text-center">
                                <i class="fas fa-user-graduate feature-icon"></i>
                                <h4>Mahasiswa</h4>
                                <p class="text-muted">Kelola data dan informasi mahasiswa</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4 mb-3">
                    <a href="dosen/dosen.php" class="text-decoration-none">
                        <div class="feature-card card h-100 p-4">
                            <div class="text-center">
                                <i class="fas fa-chalkboard-teacher feature-icon"></i>
                                <h4>Dosen</h4>
                                <p class="text-muted">Kelola data dan informasi dosen</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4 mb-3">
                    <a href="matkul/matkul.php" class="text-decoration-none">
                        <div class="feature-card card h-100 p-4">
                            <div class="text-center">
                                <i class="fas fa-book feature-icon"></i>
                                <h4>Mata Kuliah</h4>
                                <p class="text-muted">Kelola mata kuliah dan silabus</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4 mb-3">
                    <a href="ruangan/ruangan.php" class="text-decoration-none">
                        <div class="feature-card card h-100 p-4">
                            <div class="text-center">
                                <i class="fas fa-door-open feature-icon"></i>
                                <h4>Ruangan</h4>
                                <p class="text-muted">Kelola ruangan dan fasilitas</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4 mb-3">
                    <a href="jadwal_kuliah/jadwal_kuliah.php" class="text-decoration-none">
                        <div class="feature-card card h-100 p-4">
                            <div class="text-center">
                                <i class="fas fa-calendar-alt feature-icon"></i>
                                <h4>Jadwal Kuliah</h4>
                                <p class="text-muted">Atur jadwal dan agenda perkuliahan</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>