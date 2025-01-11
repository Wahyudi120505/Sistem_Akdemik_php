<?php
session_start();
include('../../config/koneksi.php'); // Pastikan untuk menyertakan file koneksi database

// Cek apakah user sudah login dan memiliki peran 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$error_message = ''; // Variabel untuk menampung pesan error

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_mk = mysqli_real_escape_string($conn, $_POST['kode_mk']);
    $nama_mk = mysqli_real_escape_string($conn, $_POST['nama_mk']);
    $sks = mysqli_real_escape_string($conn, $_POST['sks']);
    $semester = mysqli_real_escape_string($conn, $_POST['semester']);
    $id_prodi = mysqli_real_escape_string($conn, $_POST['id_prodi']);
    $id_dosen = mysqli_real_escape_string($conn, $_POST['id_dosen']);

    // Cek apakah kode_mk atau nama_mk sudah ada dalam database
    $check_query = "SELECT * FROM mata_kuliah WHERE kode_mk = ? OR nama_mk = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "ss", $kode_mk, $nama_mk);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($result) > 0) {
        $error_message = "Kode Mata Kuliah atau Nama Mata Kuliah sudah ada. Silakan masukkan data yang berbeda.";
    } else {
        // Jika tidak ada duplikasi, lanjutkan menambah data
        $query = "INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, semester, id_prodi, id_dosen) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $query)) {
            mysqli_stmt_bind_param($stmt, "ssiiii", $kode_mk, $nama_mk, $sks, $semester, $id_prodi, $id_dosen);
            if (mysqli_stmt_execute($stmt)) {
                echo "<script>alert('Mata kuliah berhasil ditambahkan'); window.location.href='matkul.php';</script>";
            } else {
                $error_message = "Gagal menambahkan mata kuliah: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $error_message = "Kesalahan query: " . mysqli_error($conn);
        }
    }

    mysqli_stmt_close($check_stmt);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Mata Kuliah</title>
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
    <script>
        function hideErrorMessage() {
            var errorMessage = document.getElementById("error_message");
            if (errorMessage) {
                setTimeout(function() {
                    errorMessage.style.display = "none";
                }, 3000);
            }
        }

        window.onload = hideErrorMessage;
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="../dashboard.php">
                <i class="fas fa-university me-2"></i>
                Sistem Akademik
            </a>
        </div>
    </nav>

    <?php if (!empty($error_message)): ?>
        <div id="error_message"> <?= $error_message ?> </div>
    <?php endif; ?>

    <div class="container mt-4">
        <h1 class="mb-4">Tambah Mata Kuliah</h1>

        <!-- Form tambah mata kuliah -->
        <form action="tambah_matkul.php" method="POST" class="card p-4 shadow-sm">
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
            <div class="mb-3">
                <label for="dosen" class="form-label">Dosen Pengajar</label>
                <select id="dosen" name="id_dosen" class="form-select" required>
                    <option value="">Pilih Dosen</option>
                    <?php
                    // Ambil data dosen
                    $dosen_query = "SELECT * FROM dosen";
                    $result_dosen = mysqli_query($conn, $dosen_query);
                    while ($row = mysqli_fetch_assoc($result_dosen)) {
                        echo "<option value='" . $row['id'] . "'>" . $row['nama'] . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Tambah Mata Kuliah</button>
                <a href="matkul.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</body>
</html>
