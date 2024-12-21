<?php
session_start();
include('../../config/koneksi.php'); // Pastikan koneksi database sudah benar

// Cek apakah user sudah login dan memiliki peran 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Mengambil ID dosen dari URL
if (!isset($_GET['id'])) {
    echo "ID dosen tidak ditemukan.";
    exit;
}
$id_dosen = $_GET['id'];

// Ambil data dosen berdasarkan ID
$query_dosen = "SELECT * FROM dosen WHERE id = '$id_dosen'";
$result_dosen = mysqli_query($conn, $query_dosen);
if (!$result_dosen || mysqli_num_rows($result_dosen) == 0) {
    echo "Dosen tidak ditemukan.";
    exit;
}
$dosen = mysqli_fetch_assoc($result_dosen);

// Mengambil data user terkait untuk edit password
$query_user = "SELECT * FROM users WHERE id = '{$dosen['user_id']}'";
$result_user = mysqli_query($conn, $query_user);
$user = mysqli_fetch_assoc($result_user);

// Proses edit data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nip = mysqli_real_escape_string($conn, $_POST['nip']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $no_telepon = mysqli_real_escape_string($conn, $_POST['no_telepon']);
    $jabatan = mysqli_real_escape_string($conn, $_POST['jabatan']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Update data user (termasuk password jika diubah)
    if ($password != "") {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $query_user_update = "UPDATE users SET username = '$nip', password = '$hashed_password' WHERE id = '{$dosen['user_id']}'";
    } else {
        $query_user_update = "UPDATE users SET username = '$nip' WHERE id = '{$dosen['user_id']}'";
    }
    if (!mysqli_query($conn, $query_user_update)) {
        echo "Gagal memperbarui data user: " . mysqli_error($conn);
        exit;
    }

    // Update data dosen
    $query_dosen_update = "UPDATE dosen SET nip = '$nip', nama = '$nama', email = '$email', no_telepon = '$no_telepon', jabatan = '$jabatan' WHERE id = '$id_dosen'";
    if (mysqli_query($conn, $query_dosen_update)) {
        header("Location: dosen.php");
        exit;
    } else {
        echo "Gagal memperbarui data dosen: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Dosen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Sistem Akademik</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Edit Dosen</h1>
        <form method="POST" action="edit_dosen.php?id=<?= $id_dosen ?>">
            <div class="mb-3">
                <label for="nip" class="form-label">NIP</label>
                <input type="text" class="form-control" id="nip" name="nip" value="<?= $dosen['nip'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="nama" class="form-label">Nama Dosen</label>
                <input type="text" class="form-control" id="nama" name="nama" value="<?= $dosen['nama'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= $dosen['email'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="no_telepon" class="form-label">No Telepon</label>
                <input type="text" class="form-control" id="no_telepon" name="no_telepon" value="<?= $dosen['no_telepon'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="jabatan" class="form-label">Jabatan</label>
                <select class="form-select" id="jabatan" name="jabatan" required>
                    <option value="Dosen" <?= ($dosen['jabatan'] == 'Dosen') ? 'selected' : ''; ?>>Dosen</option>
                    <option value="Koordinator Prodi" <?= ($dosen['jabatan'] == 'Koordinator Prodi') ? 'selected' : ''; ?>>Koordinator Prodi</option>
                    <option value="Ketua Program Studi" <?= ($dosen['jabatan'] == 'Ketua Program Studi') ? 'selected' : ''; ?>>Ketua Program Studi</option>
                    <option value="Sekretaris Program Studi" <?= ($dosen['jabatan'] == 'Sekretaris Program Studi') ? 'selected' : ''; ?>>Sekretaris Program Studi</option>
                    <option value="Dekan Fakultas" <?= ($dosen['jabatan'] == 'Dekan Fakultas') ? 'selected' : ''; ?>>Dekan Fakultas</option>
                    <option value="Wakil Dekan" <?= ($dosen['jabatan'] == 'Wakil Dekan') ? 'selected' : ''; ?>>Wakil Dekan</option>
                    <option value="Rektor" <?= ($dosen['jabatan'] == 'Rektor') ? 'selected' : ''; ?>>Rektor</option>
                    <option value="Wakil Rektor" <?= ($dosen['jabatan'] == 'Wakil Rektor') ? 'selected' : ''; ?>>Wakil Rektor</option>
                    <option value="Direktur" <?= ($dosen['jabatan'] == 'Direktur') ? 'selected' : ''; ?>>Direktur</option>
                    <option value="Pembantu Direktur" <?= ($dosen['jabatan'] == 'Pembantu Direktur') ? 'selected' : ''; ?>>Pembantu Direktur</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password (Kosongkan jika tidak ingin mengubah)</label>
                <input type="password" class="form-control" id="password" name="password">
            </div>
            <button type="submit" name="submit" class="btn btn-primary">Perbarui Dosen</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
