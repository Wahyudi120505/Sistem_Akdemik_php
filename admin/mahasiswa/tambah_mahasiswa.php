<?php
session_start();
include('../../config/koneksi.php');

// Cek apakah user sudah login dan memiliki peran 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari input
    $nim = mysqli_real_escape_string($conn, $_POST['nim']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $tanggal_lahir = mysqli_real_escape_string($conn, $_POST['tanggal_lahir']); // Format: YYYY-MM-DD
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $no_telepon = mysqli_real_escape_string($conn, $_POST['no_telepon']);
    $angkatan = mysqli_real_escape_string($conn, $_POST['angkatan']);
    $prodi_id = mysqli_real_escape_string($conn, $_POST['prodi_id']);

    // Hash password berdasarkan tanggal lahir
    $hashed_password = password_hash($tanggal_lahir, PASSWORD_BCRYPT);

    // Mulai transaksi
    mysqli_begin_transaction($conn);
    try {
        // Tambahkan data user ke tabel users
        $query_user = "INSERT INTO users (username, password, role) VALUES ('$nim', '$hashed_password', 'mahasiswa')";
        if (!mysqli_query($conn, $query_user)) {
            throw new Exception("Gagal menambahkan data user: " . mysqli_error($conn));
        }

        // Ambil ID user yang baru ditambahkan
        $user_id = mysqli_insert_id($conn);

        // Tambahkan data mahasiswa ke tabel mahasiswa
        $query_mahasiswa = "INSERT INTO mahasiswa (nim, nama, tanggal_lahir, alamat, email, no_telepon, angkatan, id_prodi, user_id)
                            VALUES ('$nim', '$nama', '$tanggal_lahir', '$alamat', '$email', '$no_telepon', '$angkatan', '$prodi_id', '$user_id')";
        if (!mysqli_query($conn, $query_mahasiswa)) {
            throw new Exception("Gagal menambahkan data mahasiswa: " . mysqli_error($conn));
        }

        // Commit transaksi
        mysqli_commit($conn);

        // Redirect ke halaman mahasiswa
        header("Location: mahasiswa.php");
        exit;

    } catch (Exception $e) {
        // Rollback jika ada kesalahan
        mysqli_rollback($conn);
        echo "Terjadi kesalahan: " . $e->getMessage();
    }
}

// Ambil data program studi untuk dropdown
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
</head>
<body>
    <div class="container mt-4">
        <h1>Tambah Mahasiswa</h1>

        <form action="" method="POST">
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
                <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
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
            <button type="submit" class="btn btn-primary">Tambah Mahasiswa</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

