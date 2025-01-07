<?php
session_start();
include('../../config/koneksi.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dosen') {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$query_dosen = "SELECT * FROM dosen WHERE user_id = '$user_id'";
$result_dosen = mysqli_query($conn, $query_dosen);

if ($result_dosen && mysqli_num_rows($result_dosen) > 0) {
    $dosen = mysqli_fetch_assoc($result_dosen);
    $dosen_id = $dosen['id'];
} else {
    echo "Data dosen tidak ditemukan.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $file_soal = $_FILES['file_soal']['name'];
    $lokasi = $_FILES['file_soal']['tmp_name'];
    $id_mata_kuliah = $_POST['id_mata_kuliah'];
    $id_jenis_soal = $_POST['id_jenis_soal'];
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];

    move_uploaded_file($lokasi,"file_soal/$file_soal"); 
    
    date_default_timezone_set('Asia/Jakarta');
    $batas_hari = isset($_POST['batas_hari']) && $_POST['batas_hari'] !== '' ? $_POST['batas_hari'] : date('Y-m-d');
    $batas_jam = $_POST['batas_jam'];
    $batas_waktu = $batas_hari . ' ' . $batas_jam;
    
    $status = $_POST['status'];

    switch ($id_jenis_soal) {
        case 1:
            $bobot_nilai = 10;
            break;
        case 2:
            $bobot_nilai = 20;
            break;
        case 3:
            $bobot_nilai = 30;
            break;
        case 4:
            $bobot_nilai = 40;
            break;
        default:
            $bobot_nilai = 0;
            break;
    }
   
    $query_bank_soal = "INSERT INTO bank_soal (id_dosen, id_mata_kuliah, id_jenis_soal, judul, deskripsi, batas_waktu, STATUS) 
                        VALUES ('$dosen_id', '$id_mata_kuliah', '$id_jenis_soal', '$judul', '$deskripsi', '$batas_waktu', '$status')";
    $result_bank_soal = mysqli_query($conn, $query_bank_soal);

    if ($result_bank_soal) {
        $id_bank_soal = mysqli_insert_id($conn);
        $query_soal = "INSERT INTO soal (id_bank_soal, pertanyaan, bobot_nilai) 
                       VALUES ('$id_bank_soal', '$deskripsi', '$bobot_nilai')";
        $result_soal = mysqli_query($conn, $query_soal);

        if ($result_soal) {
            header("Location: soal.php");
        } else {
            $error_message = "Gagal menambahkan soal ke tabel soal.";
        }
    } else {
        $error_message = "Gagal menambahkan soal ke tabel bank_soal.";
    }
}

$query_mata_kuliah = "SELECT * FROM mata_kuliah WHERE id_dosen = '$dosen_id'";
$result_mata_kuliah = mysqli_query($conn, $query_mata_kuliah);
$query_jenis_soal = "SELECT * FROM jenis_soal";
$result_jenis_soal = mysqli_query($conn, $query_jenis_soal);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Soal</title>
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
            <a class="navbar-brand" href="../home.php">
                <i class="fas fa-university me-2"></i>
                Sistem Akademik
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>

    <?php if (!empty($error_message)): ?>
        <div id="error_message"><?= $error_message ?></div>
    <?php endif; ?>

    <div class="container mt-4">
        <h1 class="mb-4">Tambah Soal Baru</h1>
        <form method="POST" action="tambah_soal.php" enctype="multipart/form-data" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label for="id_mata_kuliah" class="form-label">Mata Kuliah</label>
                <select class="form-select" name="id_mata_kuliah" id="id_mata_kuliah" required>
                    <?php while ($mata_kuliah = mysqli_fetch_assoc($result_mata_kuliah)): ?>
                        <option value="<?php echo $mata_kuliah['id']; ?>"><?php echo $mata_kuliah['nama_mk']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="id_jenis_soal" class="form-label">Jenis Soal</label>
                <select class="form-select" name="id_jenis_soal" id="id_jenis_soal" required>
                    <?php while ($jenis_soal = mysqli_fetch_assoc($result_jenis_soal)): ?>
                        <option value="<?php echo $jenis_soal['id']; ?>"><?php echo $jenis_soal['nama']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="judul" class="form-label">Judul Soal</label>
                <input type="text" class="form-control" name="judul" id="judul" required>
            </div>

            <div class="mb-3">
                <label for="deskripsi" class="form-label">Deskripsi Soal</label>
                <textarea class="form-control" name="deskripsi" id="deskripsi" rows="3" required></textarea>
            </div>

            <div class="mb-3">
                <label for="batas_hari" class="form-label">Batas Hari</label>
                <input type="date" class="form-control" name="batas_hari" id="batas_hari">
            </div>

            <div class="mb-3">
                <label for="batas_jam" class="form-label">Batas Waktu</label>
                <input type="time" class="form-control" name="batas_jam" id="batas_jam" required>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" name="status" id="status" required>
                    <option value="aktif">Aktif</option>
                    <option value="tidak_aktif">Tidak Aktif</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="file_soal" class="form-label">Upload File Soal</label>
                <input type="file" class="form-control" name="file_soal" id="file_soal" accept="*/*" required>
            </div>

            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Simpan Soal</button>
                <a href="soal.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>