<?php
session_start();
include('../../config/koneksi.php');

// Cek apakah user sudah login dan memiliki peran 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Ambil data ruangan berdasarkan ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM ruangan WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
}

// Proses ketika form disubmit
$error_message = ''; // Variabel untuk menampung pesan error
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_ruangan = mysqli_real_escape_string($conn, $_POST['kode_ruangan']);
    $nama_ruangan = mysqli_real_escape_string($conn, $_POST['nama_ruangan']);
    $kapasitas = $_POST['kapasitas'];
    $lokasi = $_POST['lokasi'];

    // Validasi input
    if (empty($kode_ruangan) || empty($nama_ruangan) || empty($kapasitas) || empty($lokasi)) {
        $error_message = "Semua field harus diisi!";
    } elseif (!is_numeric($kapasitas) || $kapasitas <= 0) {
        $error_message = "Kapasitas harus berupa angka positif!";
    } elseif (strlen($kode_ruangan) < 3 || strlen($nama_ruangan) < 3) {
        $error_message = "Kode Ruangan dan Nama Ruangan harus memiliki minimal 3 karakter!";
    } else {
        // Cek apakah kode_ruangan atau nama_ruangan sudah ada di database, kecuali untuk yang sedang diedit
        $check_query = "SELECT * FROM ruangan WHERE (kode_ruangan = '$kode_ruangan' OR nama_ruangan = '$nama_ruangan') AND id != '$id'";
        $check_result = mysqli_query($conn, $check_query);
        if (mysqli_num_rows($check_result) > 0) {
            $error_message = "Kode Ruangan atau Nama Ruangan sudah ada, silakan gunakan yang berbeda.";
        } else {
            // Jika tidak ada duplikasi, lanjutkan dengan query update
            $query = "UPDATE ruangan SET kode_ruangan = '$kode_ruangan', nama_ruangan = '$nama_ruangan', kapasitas = '$kapasitas', lokasi = '$lokasi' WHERE id = '$id'";

            if (mysqli_query($conn, $query)) {
                header("Location: ruangan.php");
                exit;
            } else {
                $error_message = "Error: " . mysqli_error($conn);
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
    <title>Edit Ruangan</title>
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
        </div>
    </nav>

    <?php if (!empty($error_message)): ?>
        <div id="error_message"> <?= $error_message ?> </div>
    <?php endif; ?>

    <div class="container mt-4">
        <h1 class="mb-4">Edit Ruangan</h1>
        <form method="POST" action="" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label for="kode_ruangan" class="form-label">Kode Ruangan</label>
                <input type="text" class="form-control" id="kode_ruangan" name="kode_ruangan" value="<?php echo $row['kode_ruangan']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="nama_ruangan" class="form-label">Nama Ruangan</label>
                <input type="text" class="form-control" id="nama_ruangan" name="nama_ruangan" value="<?php echo $row['nama_ruangan']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="kapasitas" class="form-label">Kapasitas</label>
                <input type="number" class="form-control" id="kapasitas" name="kapasitas" value="<?php echo $row['kapasitas']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="lokasi" class="form-label">Lokasi</label>
                <select class="form-select" id="lokasi" name="lokasi" required>
                    <option value="Lantai 1" <?php echo ($row['lokasi'] == 'Lantai 1') ? 'selected' : ''; ?>>Lantai 1</option>
                    <option value="Lantai 2" <?php echo ($row['lokasi'] == 'Lantai 2') ? 'selected' : ''; ?>>Lantai 2</option>
                    <option value="Lantai 3" <?php echo ($row['lokasi'] == 'Lantai 3') ? 'selected' : ''; ?>>Lantai 3</option>
                    <option value="Lantai 4" <?php echo ($row['lokasi'] == 'Lantai 4') ? 'selected' : ''; ?>>Lantai 4</option>
                    <option value="Lantai 5" <?php echo ($row['lokasi'] == 'Lantai 5') ? 'selected' : ''; ?>>Lantai 5</option>
                </select>
            </div>
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="ruangan.php" class="btn btn-secondary">Batal</a>
            </div>        
        </form>
    </div>

</body>
</html>
