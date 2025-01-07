<?php
    session_start();
    include('../config/koneksi.php'); // Pastikan untuk menyertakan file koneksi database
    
    // Cek apakah user sudah login dan memiliki peran 'dosen'
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dosen') {
        header("Location: ../index.php");
        exit;
    }
    
    // Mengambil username dari session
    $user_id = $_SESSION['user_id'];
    $query_users = "SELECT username,password FROM users WHERE id = '$user_id'";
    $result_user = mysqli_query($conn, $query_users);

    // Cek apakah query berhasil dijalankan
    if ($result_user && mysqli_num_rows($result_user) > 0) {
        $user = mysqli_fetch_assoc($result_user);
        $username = $user['username'];
        $password = mysqli_real_escape_string($conn,$user['password']);
        
        // Mengambil data dosen yang sesuai dengan username
        $query_dosen = "SELECT * FROM dosen WHERE nip = '$username'";
        $result = mysqli_query($conn, $query_dosen);
        
        // Mengambil data dosen ke dalam array
        if ($result && mysqli_num_rows($result) > 0) {
            $dosen = mysqli_fetch_assoc($result);
        } else {
            echo "Data dosen tidak ditemukan.";
            exit;
        }
    } else {
        echo "Pengguna tidak ditemukan.";
        exit;
    }
?>    

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Dosen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

        .dashboard-header {
            background-color: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .card {
            transition: transform 0.2s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card:hover {
            transform: translateY(-5px);
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
        <div class="dashboard-header">
            <h1 class="mb-0">
            <i class="fas fa-user-tie me-2"></i>
                Selamat Datang, <?php echo htmlspecialchars($dosen['nama']); ?>
            </h1>
        </div>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card text-center h-100">
                    <div class="card-body d-flex flex-column">
                        <i class="fas fa-calendar-alt feature-icon"></i>
                        <h5 class="card-title mb-3">Jadwal Mengajar</h5>
                        <p class="card-text flex-grow-1">Lihat jadwal mata kuliah yang Anda ampu.</p>
                        <a href="jadwal_mengajar/jadwal.php" class="btn btn-primary mt-auto">
                            <i class="fas fa-eye me-1"></i>
                            Lihat Jadwal
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card text-center h-100">
                    <div class="card-body d-flex flex-column">
                        <i class="fas fa-tasks feature-icon"></i>
                        <h5 class="card-title mb-3">Buat Soal</h5>
                        <p class="card-text flex-grow-1">Buat soal tugas, kuis, UTS, atau UAS untuk mahasiswa.</p>
                        <a href="soal/soal.php" class="btn btn-primary mt-auto">
                            <i class="fas fa-plus me-1"></i>
                            Buat Soal
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card text-center h-100">
                    <div class="card-body d-flex flex-column">
                        <i class="fas fa-book feature-icon"></i>
                        <h5 class="card-title mb-3">Mata Kuliah</h5>
                        <p class="card-text flex-grow-1">Daftar mata kuliah yang Anda ajar.</p>
                        <a href="#" class="btn btn-primary mt-auto">
                            <i class="fas fa-list me-1"></i>
                            Lihat Mata Kuliah
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user-circle me-2"></i>
                            Profil Dosen
                        </h5>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Nama Dosen : <?php echo htmlspecialchars($dosen['nama']); ?></h5>
                        <p class="card-text mb-2">NIP : <?php echo htmlspecialchars($dosen['nip']); ?></p>
                        <p class="mb-2">Email : <?php echo htmlspecialchars($dosen['email']); ?></p>
                        <p class="mb-2">No Telepon : <?php echo htmlspecialchars($dosen['no_telepon']); ?></p>
                        <p class="mb-2">Jabatan : <?php echo htmlspecialchars($dosen['jabatan']); ?></p>
                        <p class="mb-4">Password : <?php echo str_repeat('*', strlen($password) / 7) ?></p>
                        <a href="edit_profile.php" class="btn btn-secondary">
                            <i class="fas fa-edit me-1"></i>
                            Edit Profil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>