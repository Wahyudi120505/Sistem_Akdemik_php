<?php
session_start();
include('../../config/koneksi.php'); // Pastikan koneksi database sudah benar

// Cek apakah user sudah login dan memiliki peran 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Menambahkan dosen
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form dan amankan dengan mysqli_real_escape_string
    $nip = mysqli_real_escape_string($conn, $_POST['nip']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $no_telepon = mysqli_real_escape_string($conn, $_POST['no_telepon']);
    $jabatan = mysqli_real_escape_string($conn, $_POST['jabatan']);
    $password = mysqli_real_escape_string($conn, $_POST['password']); 

    // Enkripsi password menggunakan bcrypt
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Mulai transaksi
    mysqli_begin_transaction($conn);
    try{
        // Tambahkan data user ke tabel users
        $query_user = "INSERT INTO users (username, password, role) VALUES ('$nip', '$hashed_password', 'dosen')";
        if (!mysqli_query($conn, $query_user)) {
            throw new Exception("Gagal menambahkan data user: " . mysqli_error($conn));
        }

        // Ambil ID user yang baru ditambahkan
        $user_id = mysqli_insert_id($conn);

        // Query untuk menambahkan data dosen
        $query_dosen = "INSERT INTO dosen (nip, nama, email, no_telepon, jabatan, user_id) 
                VALUES ('$nip', '$nama', '$email', '$no_telepon', '$jabatan', '$user_id')";

        if (!mysqli_query($conn, $query_dosen)) {
            throw new Exception("Gagal menambahkan data dosen: " . mysqli_error($conn));
        }

        // Commit transaksi
        mysqli_commit($conn);

        // Redirect ke halaman mahasiswa
        header("Location: dosen.php");
        exit;
    }catch (Exception $e) {
        // Rollback jika ada kesalahan
        mysqli_rollback($conn);
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Dosen</title>
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
            min-height: 100vh;
        }

        .navbar {
            background-color: var(--primary-color) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-weight: 600;
            font-size: 1.5rem;
            color: white !important;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: white !important;
            transform: translateY(-1px);
        }

        #error_message {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: var(--danger-color);
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            font-weight: 500;
            z-index: 1050;
            animation: slideIn 0.5s ease, fadeOut 0.5s 3s forwards;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
            }
            to {
                transform: translateX(0);
            }
        }

        @keyframes fadeOut {
            to {
                opacity: 0;
                visibility: hidden;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="../dashboard.php">
                <i class="fas fa-university me-2"></i>
                Sistem Akademik
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            </div>
        </div>
    </nav>

    <?php if (!empty($error_message)): ?>
        <div id="error_message"> <?= $error_message ?> </div>
    <?php endif; ?>

    <div class="container mt-4">
        <h1 class="mb-4">Tambah Dosen</h1>
        <form method="POST" action="tambah_dosen.php" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label for="nip" class="form-label">NIP</label>
                <input type="text" class="form-control" id="nip" name="nip" required>
            </div>
            <div class="mb-3">
                <label for="nama" class="form-label">Nama Dosen</label>
                <input type="text" class="form-control" id="nama" name="nama" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="no_telepon" class="form-label">No Telepon</label>
                <input type="text" class="form-control" id="no_telepon" name="no_telepon" required>
            </div>
            <div class="mb-3">
                <label for="jabatan" class="form-label">Jabatan</label>
                <select class="form-select" id="jabatan" name="jabatan" required>
                    <option value="Dosen">Dosen</option>
                    <option value="Koordinator Prodi">Koordinator Prodi</option>
                    <option value="Ketua Program Studi">Ketua Program Studi</option>
                    <option value="Sekretaris Program Studi">Sekretaris Program Studi</option>
                    <option value="Dekan Fakultas">Dekan Fakultas</option>
                    <option value="Wakil Dekan">Wakil Dekan</option>
                    <option value="Rektor">Rektor</option>
                    <option value="Wakil Rektor">Wakil Rektor</option>
                    <option value="Direktur">Direktur</option>
                    <option value="Pembantu Direktur">Pembantu Direktur</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Tambah Dosen</button>
                <a href="dosen.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</body>
</html>

