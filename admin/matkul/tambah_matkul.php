<?php
session_start();
include('../../config/koneksi.php'); // Pastikan untuk menyertakan file koneksi database

// Cek apakah user sudah login dan memiliki peran 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Proses tambah mata kuliah
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_mk'])) {
    $kode_mk = $_POST['kode_mk'];
    $nama_mk = $_POST['nama_mk'];
    $sks = $_POST['sks'];
    $semester = $_POST['semester'];
    $id_prodi = $_POST['id_prodi'];

    // Query untuk menambahkan mata kuliah
    $query = "INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, semester, id_prodi) 
              VALUES ('$kode_mk', '$nama_mk', '$sks', '$semester', '$id_prodi')";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Mata kuliah berhasil ditambahkan'); window.location.href='matkul.php';</script>";
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
    <title>Tambah Mata Kuliah</title>
    <!-- Link ke Bootstrap CSS -->
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
        <h1 class="mb-4">Tambah Mata Kuliah</h1>

        <!-- Form tambah mata kuliah -->
        <form method="POST">
            <div class="mb-3">
                <label for="kode_mk" class="form-label">Kode Mata Kuliah</label>
                <input type="text" class="form-control" id="kode_mk" name="kode_mk" required>
            </div>
            <div class="mb-3">
                <label for="nama_mk" class="form-label">Nama Mata Kuliah</label>
                <input type="text" class="form-control" id="nama_mk" name="nama_mk" required>
            </div>
            <div class="mb-3">
                <label for="sks" class="form-label">Jumlah SKS</label>
                <select id="sks" name="sks" class="form-select" required>
                    <option value="">Pilih SKS</option>
                    <option value="2">2 SKS</option>
                    <option value="3">3 SKS</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="semester" class="form-label">Semester</label>
                <input type="number" class="form-control" id="semester" name="semester" required>
            </div>
            <div class="mb-3">
                <label for="id_prodi" class="form-label">Program Studi</label>
                <select class="form-select" id="id_prodi" name="id_prodi" required>
                    <?php
                    // Ambil data program studi
                    $prodi_query = "SELECT * FROM program_studi";
                    $prodi_result = mysqli_query($conn, $prodi_query);
                    while ($row = mysqli_fetch_assoc($prodi_result)) {
                        echo "<option value='" . $row['id'] . "'>" . $row['nama_prodi'] . "</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" name="add_mk" class="btn btn-primary">Tambah Mata Kuliah</button>
        </form>
    </div>

    <!-- Link ke Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
