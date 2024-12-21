<?php
session_start();
include('../../config/koneksi.php');

// Cek apakah user sudah login dan memiliki peran 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Ambil ID mata kuliah yang akan diedit
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Ambil data mata kuliah berdasarkan ID
    $query = "SELECT * FROM mata_kuliah WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    $mata_kuliah = mysqli_fetch_assoc($result);

    if (!$mata_kuliah) {
        echo "Mata kuliah tidak ditemukan.";
        exit;
    }
}

// Proses update mata kuliah
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_mk'])) {
    $kode_mk = $_POST['kode_mk'];
    $nama_mk = $_POST['nama_mk'];
    $sks = $_POST['sks'];
    $semester = $_POST['semester'];
    $id_prodi = $_POST['id_prodi'];

    // Query untuk update mata kuliah
    $update_query = "UPDATE mata_kuliah SET kode_mk='$kode_mk', nama_mk='$nama_mk', sks='$sks', semester='$semester', id_prodi='$id_prodi' WHERE id='$id'";
    
    if (mysqli_query($conn, $update_query)) {
        echo "<script>alert('Mata kuliah berhasil diperbarui'); window.location.href='matkul.php';</script>";
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
    <title>Edit Mata Kuliah</title>
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
        <h1 class="mb-4">Edit Mata Kuliah</h1>
        
        <!-- Form edit mata kuliah -->
        <form method="POST">
            <div class="mb-3">
                <label for="kode_mk" class="form-label">Kode Mata Kuliah</label>
                <input type="text" class="form-control" id="kode_mk" name="kode_mk" value="<?= $mata_kuliah['kode_mk'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="nama_mk" class="form-label">Nama Mata Kuliah</label>
                <input type="text" class="form-control" id="nama_mk" name="nama_mk" value="<?= $mata_kuliah['nama_mk'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="sks" class="form-label">Jumlah SKS</label>
                <input type="number" class="form-control" id="sks" name="sks" value="<?= $mata_kuliah['sks'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="semester" class="form-label">Semester</label>
                <input type="number" class="form-control" id="semester" name="semester" value="<?= $mata_kuliah['semester'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="id_prodi" class="form-label">Program Studi</label>
                <select class="form-select" id="id_prodi" name="id_prodi" required>
                    <option value="">Pilih Program Studi</option>
                    <?php
                    // Ambil data program studi untuk dropdown
                    $prodi_query = "SELECT * FROM program_studi";
                    $prodi_result = mysqli_query($conn, $prodi_query);
                    while ($prodi = mysqli_fetch_assoc($prodi_result)) {
                        $selected = ($prodi['id'] == $mata_kuliah['id_prodi']) ? 'selected' : '';
                        echo "<option value='{$prodi['id']}' $selected>{$prodi['nama_prodi']}</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" name="update_mk" class="btn btn-primary">Update Mata Kuliah</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
