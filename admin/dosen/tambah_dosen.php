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
    $password = mysqli_real_escape_string($conn, $_POST['password']); // Ambil password dari form

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
        echo "Terjadi kesalahan: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Dosen</title>
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
                        <a class="nav-link active" href="dashboard_admin.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Tambah Dosen</h1>
        <form method="POST" action="tambah_dosen.php">
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
            <button type="submit" name="submit" class="btn btn-primary">Tambah Dosen</button>
        </form>
    </div>

    <!-- Link ke Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

