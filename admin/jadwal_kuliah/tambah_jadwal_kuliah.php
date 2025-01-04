<?php
session_start();
include '../../config/koneksi.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$error_message = ""; // Variabel untuk menyimpan pesan kesalahan

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $mata_kuliah = $_POST['mata_kuliah'];
    $dosen = $_POST['dosen'];
    $hari = $_POST['hari'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    $ruangan = $_POST['ruangan'];

    // Cek apakah sudah ada jadwal kuliah yang sama
    $query_check = "SELECT * FROM jadwal_kuliah 
    WHERE hari = ? 
    AND (
        (jam_mulai BETWEEN ? AND ?) 
        OR (jam_selesai BETWEEN ? AND ?) 
        OR (? BETWEEN jam_mulai AND jam_selesai)
    )
    AND (ruangan = ? OR id_dosen = ?)"; // Menambahkan kondisi untuk mengecek dosen yang sama
$stmt_check = $conn->prepare($query_check);
$stmt_check->bind_param("ssssssss", $hari, $jam_mulai, $jam_selesai, $jam_mulai, $jam_selesai, $jam_mulai, $ruangan, $dosen);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Jika ada tabrakan jadwal
        $error_message = "Jadwal kuliah ini sudah ada pada waktu dan ruangan yang dipilih.";
    } else {
        // Query untuk menyimpan data jadwal kuliah
        $query = "INSERT INTO jadwal_kuliah (id_mata_kuliah, id_dosen, hari, jam_mulai, jam_selesai, ruangan)
                  VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("iissss", $mata_kuliah, $dosen, $hari, $jam_mulai, $jam_selesai, $ruangan);

        if ($stmt->execute()) {
            // Jika berhasil, redirect ke halaman jadwal_kuliah.php
            header("Location: jadwal_kuliah.php");
            exit;
        } else {
            $error_message = "Error: " . $stmt->error;
        }
    }
}

// Query untuk mendapatkan data mata kuliah
$query_matkul = "SELECT id, nama_mk, sks FROM mata_kuliah";
$result_matkul = mysqli_query($conn, $query_matkul);

// Query untuk mendapatkan data dosen
$query_dosen = "SELECT id, nama FROM dosen";
$result_dosen = mysqli_query($conn, $query_dosen);

// Query untuk mendapatkan data ruangan
$query_ruangan = "SELECT id, nama_ruangan FROM ruangan";
$result_ruangan = mysqli_query($conn, $query_ruangan);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Jadwal Kuliah</title>
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
            animation: slideIn 0.5s ease, fadeOut 0.5s 3s forwards;
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
        <h1 class="mb-4">Tambah Jadwak Kuliah</h1>
        <form method="POST" action="tambah_jadwal_kuliah.php" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label for="mata_kuliah" class="form-label">Mata Kuliah</label>
                <select id="mata_kuliah" name="mata_kuliah" class="form-select" required>
                    <option value="">Pilih Mata Kuliah</option>
                    <?php while ($row = mysqli_fetch_assoc($result_matkul)): ?>
                        <option value="<?= $row['id']; ?>" data-sks="<?= $row['sks']; ?>">
                            <?= $row['nama_mk']; ?> (<?= $row['sks']; ?> SKS)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="dosen" class="form-label">Dosen Pengajar</label>
                <select id="dosen" name="dosen" class="form-select" required>
                    <option value="">Pilih Dosen</option>
                    <?php while ($row = mysqli_fetch_assoc($result_dosen)): ?>
                        <option value="<?= $row['id']; ?>"> <?= $row['nama']; ?> </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="hari" class="form-label">Hari</label>
                <select id="hari" name="hari" class="form-select" required>
                    <option value="">Pilih Hari</option>
                    <option value="Senin">Senin</option>
                    <option value="Selasa">Selasa</option>
                    <option value="Rabu">Rabu</option>
                    <option value="Kamis">Kamis</option>
                    <option value="Jumat">Jumat</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="jam_mulai" class="form-label">Jam Mulai</label>
                <input type="time" id="jam_mulai" name="jam_mulai" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="jam_selesai" class="form-label">Jam Selesai</label>
                <input type="time" id="jam_selesai" name="jam_selesai" class="form-control" readonly>
            </div>

            <div class="mb-3">
                <label for="ruangan" class="form-label">Ruangan</label>
                <select id="ruangan" name="ruangan" class="form-select" required>
                    <option value="">Pilih Ruangan</option>
                    <?php while ($row = mysqli_fetch_assoc($result_ruangan)): ?>
                        <option value="<?= $row['nama_ruangan']; ?>"> <?= $row['nama_ruangan']; ?> </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Tambah Dosen</button>
                <a href="jadwal_kuliah.php" class="btn btn-secondary">Batal</a>
            </div>        
        </form>
    </div>

    <script>
        document.getElementById('jam_mulai').addEventListener('input', hitungJamSelesai);
        document.getElementById('mata_kuliah').addEventListener('change', hitungJamSelesai);

        function hitungJamSelesai() {
            let jamMulai = document.getElementById('jam_mulai').value;
            let sks = document.querySelector('#mata_kuliah option:checked').dataset.sks;
            if (!sks || !jamMulai) return;
            
            let jam = parseInt(jamMulai.split(':')[0]);
            let menit = parseInt(jamMulai.split(':')[1]);
            let totalMenit = sks * 50;
            
            let jamSelesai = new Date();
            jamSelesai.setHours(jam);
            jamSelesai.setMinutes(menit + totalMenit);
            
            let hasilJam = jamSelesai.getHours().toString().padStart(2, '0');
            let hasilMenit = jamSelesai.getMinutes().toString().padStart(2, '0');
            
            document.getElementById('jam_selesai').value = `${hasilJam}:${hasilMenit}`;
        }
    </script>
</body>
</html>
