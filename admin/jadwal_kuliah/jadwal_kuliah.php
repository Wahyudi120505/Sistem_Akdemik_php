<?php
session_start();
include('../../config/koneksi.php');

// Cek apakah user sudah login dan memiliki peran 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Ambil semua jadwal kuliah dari database
$query = "SELECT j.id, mk.nama_mk, d.nama, j.hari, j.jam_mulai, j.jam_selesai, j.ruangan
          FROM jadwal_kuliah j
          JOIN mata_kuliah mk ON j.id_mata_kuliah = mk.id
          JOIN dosen d ON j.id_dosen = d.id";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penjadwalan Kuliah</title>
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
                        <a class="nav-link active" href="#">Penjadwalan Kuliah</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Manajemen Jadwal Kuliah</h1>

        <!-- Tabel Jadwal Kuliah -->
        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Mata Kuliah</th>
                    <th>Nama Dosen</th>
                    <th>Hari</th>
                    <th>Jam Mulai</th>
                    <th>Jam Selesai</th>
                    <th>Ruangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['nama_mk'] ?></td>
                        <td><?= $row['nama'] ?></td>
                        <td><?= $row['hari'] ?></td>
                        <td><?= $row['jam_mulai'] ?></td>
                        <td><?= $row['jam_selesai'] ?></td>
                        <td><?= $row['ruangan'] ?></td>
                        <td>
                            <!-- Edit dan Hapus -->
                            <a href="edit_jadwal_kuliah.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="hapus_jadwal_kuliah.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Tambah Jadwal Kuliah -->
        <a href="tambah_jadwal_kuliah.php" class="btn btn-primary mt-4">Tambah Jadwal Kuliah</a>
    </div>

    <!-- Link ke Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
