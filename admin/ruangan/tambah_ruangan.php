<?php
session_start();
include('../../config/koneksi.php');

// Cek apakah user sudah login dan memiliki peran 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Proses ketika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_ruangan = mysqli_real_escape_string($conn, $_POST['kode_ruangan']);
    $nama_ruangan = mysqli_real_escape_string($conn, $_POST['nama_ruangan']);
    $kapasitas = $_POST['kapasitas'];
    $lokasi = $_POST['lokasi'];

    $query = "INSERT INTO ruangan (kode_ruangan, nama_ruangan, kapasitas, lokasi) 
              VALUES ('$kode_ruangan', '$nama_ruangan', '$kapasitas', '$lokasi')";

    if (mysqli_query($conn, $query)) {
        header("Location: ruangan.php");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Ruangan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4">Tambah Ruangan</h1>

        <form method="POST">
            <div class="mb-3">
                <label for="kode_ruangan" class="form-label">Kode Ruangan</label>
                <input type="text" class="form-control" id="kode_ruangan" name="kode_ruangan" required>
            </div>
            <div class="mb-3">
                <label for="nama_ruangan" class="form-label">Nama Ruangan</label>
                <input type="text" class="form-control" id="nama_ruangan" name="nama_ruangan" required>
            </div>
            <div class="mb-3">
                <label for="kapasitas" class="form-label">Kapasitas</label>
                <input type="number" class="form-control" id="kapasitas" name="kapasitas" required>
            </div>
            <div class="mb-3">
                <label for="lokasi" class="form-label">Lokasi</label>
                <select class="form-select" id="lokasi" name="lokasi" required>
                    <option value="">Pilih Lantai</option>
                    <option value="Lantai 1">Lantai 1</option>
                    <option value="Lantai 2">Lantai 2</option>
                    <option value="Lantai 3">Lantai 3</option>
                    <option value="Lantai 4">Lantai 4</option>
                    <option value="Lantai 5">Lantai 5</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
