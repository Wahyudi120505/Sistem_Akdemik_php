<?php
session_start();
include('../../config/koneksi.php');

// Cek apakah user sudah login dan memiliki peran 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Cek apakah ada parameter 'id' yang dikirimkan
if (isset($_GET['id'])) {
    $id_prodi = $_GET['id'];

    // Ambil data program studi berdasarkan ID
    $query = "SELECT * FROM program_studi WHERE id = $id_prodi";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
} else {
    header("Location: program_studi.php");
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
        $check_query = "SELECT * FROM program_studi WHERE nama_prodi = '$nama_prodi' AND id != $id_prodi";
        $result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($result) > 0) {
            $error_message = "Program studi dengan nama tersebut sudah ada.";
        }
    }

    // Jika validasi berhasil, lanjutkan proses update ke database
    if (!isset($error_message)) {
        // Query untuk memperbarui data program studi
        $query = "UPDATE program_studi SET nama_prodi='$nama_prodi', fakultas='$fakultas' WHERE id=$id_prodi";
        if (mysqli_query($conn, $query)) {
            header("Location: program_studi.php");
            exit;
        } else {
            $error_message = $e->getMessage();
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
    <title>Edit Program Studi</title>
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
        <h1 class="mb-4">Edit Program Studi</h1>

        <form action="edit_program_studi.php?id=<?php echo $row['id']; ?>" method="POST"  class="card p-4 shadow-sm">
            <div class="mb-3">
                <label for="nama_prodi" class="form-label">Nama Program Studi</label>
                <input type="text" class="form-control" id="nama_prodi" name="nama_prodi" value="<?php echo $row['nama_prodi']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="fakultas" class="form-label">Fakultas</label>
                <input type="text" class="form-control" id="fakultas" name="fakultas" value="<?php echo $row['fakultas']; ?>" required>
            </div>
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="program_studi.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>

</body>
</html>
