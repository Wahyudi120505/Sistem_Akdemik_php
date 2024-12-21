<?php
session_start();
include('../../config/koneksi.php');

// Cek apakah user sudah login dan memiliki peran 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Ambil data mahasiswa berdasarkan ID
if (isset($_GET['id'])) {
    $id_mahasiswa = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "SELECT m.*, u.username FROM mahasiswa m
              JOIN users u ON m.user_id = u.id
              WHERE m.id = '$id_mahasiswa'";
    $result = mysqli_query($conn, $query);
    $mahasiswa = mysqli_fetch_assoc($result);

    if (!$mahasiswa) {
        echo "Mahasiswa tidak ditemukan.";
        exit;
    }
} else {
    header("Location: mahasiswa.php");
    exit;
}

// Proses edit data mahasiswa
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nim = mysqli_real_escape_string($conn, $_POST['nim']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $tanggal_lahir = mysqli_real_escape_string($conn, $_POST['tanggal_lahir']);
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
        // Update data mahasiswa
        $query_update_mahasiswa = "UPDATE mahasiswa SET 
            nim = '$nim',
            nama = '$nama',
            tanggal_lahir = '$tanggal_lahir',
            alamat = '$alamat',
            email = '$email',
            no_telepon = '$no_telepon',
            angkatan = '$angkatan',
            id_prodi = '$prodi_id'
            WHERE id = '$id_mahasiswa'";
        if (!mysqli_query($conn, $query_update_mahasiswa)) {
            throw new Exception("Gagal memperbarui data mahasiswa: " . mysqli_error($conn));
        }

        // Update data user
        $query_update_user = "UPDATE users SET 
            username = '$nim',
            password = '$hashed_password'
            WHERE id = '{$mahasiswa['user_id']}'";
        if (!mysqli_query($conn, $query_update_user)) {
            throw new Exception("Gagal memperbarui data user: " . mysqli_error($conn));
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
$query_prodi = "SELECT * FROM program_studi";
$result_prodi = mysqli_query($conn, $query_prodi);
$prodi = mysqli_fetch_all($result_prodi, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Mahasiswa</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Edit Data Mahasiswa</h2>
        <form method="POST" action="" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label for="nim" class="form-label">NIM</label>
                <input type="text" class="form-control" id="nim" name="nim" value="<?php echo htmlspecialchars($mahasiswa['nim']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="nama" class="form-label">Nama</label>
                <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($mahasiswa['nama']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" value="<?php echo htmlspecialchars($mahasiswa['tanggal_lahir']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="alamat" class="form-label">Alamat</label>
                <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?php echo htmlspecialchars($mahasiswa['alamat']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($mahasiswa['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="no_telepon" class="form-label">No. Telepon</label>
                <input type="text" class="form-control" id="no_telepon" name="no_telepon" value="<?php echo htmlspecialchars($mahasiswa['no_telepon']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="angkatan" class="form-label">Angkatan</label>
                <input type="number" class="form-control" id="angkatan" name="angkatan" value="<?php echo htmlspecialchars($mahasiswa['angkatan']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="prodi_id" class="form-label">Program Studi</label>
                <select id="prodi_id" name="prodi_id" class="form-select" required>
                    <?php foreach ($prodi as $p): ?>
                        <option value="<?php echo $p['id']; ?>" <?php echo $p['id'] == $mahasiswa['id_prodi'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p['nama_prodi']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="mahasiswa.php" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
