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

    // Cek apakah NIP sudah digunakan oleh user lain
    $query_check_nip = "SELECT * FROM users WHERE username = '$nip' AND id != '{$dosen['user_id']}'";
    $result_check_nip = mysqli_query($conn, $query_check_nip);
    if (mysqli_num_rows($result_check_nip) > 0) {
        $error_message = "NIP sudah digunakan oleh user lain. Silakan pilih NIP yang lain.";
    } else {
        // Update data user (termasuk password jika diubah)
        if ($password != "") {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $query_user_update = "UPDATE users SET username = '$nip', password = '$hashed_password' WHERE id = '{$dosen['user_id']}'";
        } else {
            $query_user_update = "UPDATE users SET username = '$nip' WHERE id = '{$dosen['user_id']}'";
        }

        if (!mysqli_query($conn, $query_user_update)) {
            $error_message = "Terjadi kesalahan saat memperbarui data user.";
        } else {
            // Update data dosen
            $query_dosen_update = "UPDATE dosen SET nip = '$nip', nama = '$nama', email = '$email', no_telepon = '$no_telepon', jabatan = '$jabatan' WHERE id = '$id_dosen'";
            if (mysqli_query($conn, $query_dosen_update)) {
                header("Location: dosen.php");
                exit;
            } else {
                $error_message = "Terjadi kesalahan saat memperbarui data dosen.";
            }
        }
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Styling yang sudah ada */
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
            animation: slideIn 0.5s ease, fadeOut 0.5s 2.5s forwards;
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
</nav>

<?php if (!empty($error_message)): ?>
    <div id="error_message"> <?= $error_message ?> </div>
<?php endif; ?>

<div class="container mt-4">
    <h1 class="mb-4">Edit Dosen</h1>
    <form method="POST" action="edit_dosen.php?id=<?= $id_dosen ?>" class="card p-4 shadow-sm">
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
        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="dosen.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
</body>
</html>
