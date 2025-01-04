<?php
session_start();
include('../../config/koneksi.php');

// Cek apakah user sudah login dan memiliki peran 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$error_message = ""; // Variabel untuk menampung pesan error

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nim = mysqli_real_escape_string($conn, $_POST['nim']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $tanggal_lahir = mysqli_real_escape_string($conn, $_POST['tanggal_lahir']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $no_telepon = mysqli_real_escape_string($conn, $_POST['no_telepon']);
    $angkatan = mysqli_real_escape_string($conn, $_POST['angkatan']);
    $prodi_id = mysqli_real_escape_string($conn, $_POST['prodi_id']);

    $hashed_password = password_hash($tanggal_lahir, PASSWORD_BCRYPT);

    mysqli_begin_transaction($conn);
    try {
        $query_user = "INSERT INTO users (username, password, role) VALUES ('$nim', '$hashed_password', 'mahasiswa')";
        if (!mysqli_query($conn, $query_user)) {
            throw new Exception("Gagal menambahkan data user: " . mysqli_error($conn));
        }

        $user_id = mysqli_insert_id($conn);

        $query_mahasiswa = "INSERT INTO mahasiswa (nim, nama, tanggal_lahir, alamat, email, no_telepon, angkatan, id_prodi, user_id)
                            VALUES ('$nim', '$nama', '$tanggal_lahir', '$alamat', '$email', '$no_telepon', '$angkatan', '$prodi_id', '$user_id')";
        if (!mysqli_query($conn, $query_mahasiswa)) {
            throw new Exception("Gagal menambahkan data mahasiswa: " . mysqli_error($conn));
        }

        mysqli_commit($conn);
        header("Location: mahasiswa.php");
        exit;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error_message = $e->getMessage(); // Simpan pesan error ke dalam variabel
    }
}

$query = "SELECT * FROM program_studi";
$result = mysqli_query($conn, $query);
$prodi = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Mahasiswa</title>
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
        <h1 class="mb-4">Tambah Mahasiswa</h1>
        <form action="tambah_mahasiswa.php" method="POST"  class="card p-4 shadow-sm">
            <div class="mb-3">
                <label for="nim" class="form-label">NIM</label>
                <input type="text" class="form-control" id="nim" name="nim" required>
            </div>
            <div class="mb-3">
                <label for="nama" class="form-label">Nama</label>
                <input type="text" class="form-control" id="nama" name="nama" required>
            </div>
            <div class="mb-3">
                <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" required>
            </div>
            <div class="mb-3">
                <label for="alamat" class="form-label">Alamat</label>
                <textarea class="form-control" id="alamat" name="alamat" required></textarea>
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
                <label for="angkatan" class="form-label">Angkatan</label>
                <input type="text" class="form-control" id="angkatan" name="angkatan" required>
            </div>
            <div class="mb-3">
                <label for="prodi_id" class="form-label">Program Studi</label>
                <select class="form-control" id="prodi_id" name="prodi_id" required>
                    <?php foreach ($prodi as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= $p['nama_prodi'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Tambah Mahasiswa</button>
                <a href="mahasiswa.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</body>
</html>
