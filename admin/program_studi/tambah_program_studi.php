<?php
session_start();
include('../../config/koneksi.php');

// Cek apakah user sudah login dan memiliki peran 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Proses jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_prodi = mysqli_real_escape_string($conn, $_POST['nama_prodi']);
    $fakultas = mysqli_real_escape_string($conn, $_POST['fakultas']);

    // Validasi Nama Program Studi
    if (empty($nama_prodi)) {
        $error_message = "Nama program studi tidak boleh kosong.";
    }

    // Validasi Fakultas
    if (empty($fakultas)) {
        $error_message = "Fakultas tidak boleh kosong.";
    }

    // Cek apakah nama program studi sudah ada
    if (!isset($error_message)) {
        $check_query = "SELECT * FROM program_studi WHERE nama_prodi = '$nama_prodi'";
        $result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($result) > 0) {
            $error_message = "Program studi dengan nama tersebut sudah ada.";
        }
    }

    // Jika validasi berhasil, lanjutkan proses insert ke database
    if (!isset($error_message)) {
        // Query untuk menambahkan data program studi
        $query = "INSERT INTO program_studi (nama_prodi, fakultas) VALUES ('$nama_prodi', '$fakultas')";
        if (mysqli_query($conn, $query)) {
            header("Location: program_studi.php");
            exit;
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        echo "<div class='alert alert-danger' id='error_message'>$error_message</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Program Studi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        // Fungsi untuk menghilangkan pesan error setelah 3 detik
        function hideErrorMessage() {
            var errorMessage = document.getElementById("error_message");
            if (errorMessage) {
                setTimeout(function() {
                    errorMessage.style.display = "none";
                }, 3000); // Menghilangkan pesan setelah 3 detik
            }
        }

        // Jalankan hideErrorMessage ketika halaman selesai dimuat
        window.onload = hideErrorMessage;
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Sistem Akademik</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4">Tambah Program Studi</h1>

        <form action="tambah_program_studi.php" method="POST">
            <div class="mb-3">
                <label for="nama_prodi" class="form-label">Nama Program Studi</label>
                <input type="text" class="form-control" id="nama_prodi" name="nama_prodi" required>
            </div>
            <div class="mb-3">
                <label for="fakultas" class="form-label">Fakultas</label>
                <input type="text" class="form-control" id="fakultas" name="fakultas" required>
            </div>
            <button type="submit" class="btn btn-primary">Tambah Program Studi</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
