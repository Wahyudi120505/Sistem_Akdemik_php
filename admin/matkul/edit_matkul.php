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
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_mk = mysqli_real_escape_string($conn, $_POST['kode_mk']);
    $nama_mk = mysqli_real_escape_string($conn, $_POST['nama_mk']);
    $sks = mysqli_real_escape_string($conn, $_POST['sks']);
    $semester = mysqli_real_escape_string($conn, $_POST['semester']);
    $id_prodi = mysqli_real_escape_string($conn, $_POST['id_prodi']);

    // Cek apakah kode mata kuliah sudah ada di database
    $check_query = "SELECT * FROM mata_kuliah WHERE kode_mk = '$kode_mk' AND id != '$id'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $error_message = "Kode mata kuliah sudah terdaftar.";
    } else {
        // Cek apakah ada mata kuliah lain yang memiliki kombinasi semester, SKS, dan program studi yang sama
        $duplicate_query = "SELECT * FROM mata_kuliah 
                            WHERE semester = '$semester' AND sks = '$sks' AND id_prodi = '$id_prodi' AND id != '$id'";
        $duplicate_result = mysqli_query($conn, $duplicate_query);

        if (mysqli_num_rows($duplicate_result) > 0) {
            $error_message = "Mata kuliah dengan kombinasi semester, SKS, dan program studi yang sama sudah ada.";
        } else {
            // Query untuk update mata kuliah
            $update_query = "UPDATE mata_kuliah SET kode_mk='$kode_mk', nama_mk='$nama_mk', sks='$sks', semester='$semester', id_prodi='$id_prodi' WHERE id='$id'";

            if (mysqli_query($conn, $update_query)) {
                echo "<script>alert('Mata kuliah berhasil diperbarui'); window.location.href='matkul.php';</script>";
            } else {
                $error_message = "Terjadi kesalahan saat memperbarui data.";
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
    <title>Edit Mata Kuliah</title>
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
        <h1 class="mb-4">Edit Mata Kuliah</h1>
        <form method="POST" action="" class="card p-4 shadow-sm">
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
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="matkul.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>

</body>
</html>
