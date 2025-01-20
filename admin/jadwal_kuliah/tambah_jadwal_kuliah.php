<?php
session_start();
include '../../config/koneksi.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Ambil data dari form
        $mata_kuliah = $_POST['mata_kuliah'];
        $hari = $_POST['hari'];
        $jam_mulai = $_POST['jam_mulai'];
        $jam_selesai = $_POST['jam_selesai'];
        $ruangan = $_POST['ruangan'];

        // Validasi mata_kuliah tidak kosong
        if (empty($mata_kuliah)) {
            throw new Exception("Mata kuliah harus dipilih!");
        }

        // Ambil ID dosen dari mata kuliah yang dipilih dengan validasi tambahan
        $query_dosen = "SELECT id_dosen FROM mata_kuliah WHERE id = ? AND id_dosen IS NOT NULL";
        $stmt_dosen = $conn->prepare($query_dosen);
        $stmt_dosen->bind_param("i", $mata_kuliah);
        $stmt_dosen->execute();
        $result_dosen = $stmt_dosen->get_result();
        
        if ($result_dosen->num_rows === 0) {
            throw new Exception("Mata kuliah ini belum memiliki dosen yang ditugaskan. Silakan pilih mata kuliah lain atau hubungi administrator.");
        }

        $row_dosen = $result_dosen->fetch_assoc();
        $dosen = $row_dosen['id_dosen'];

        // Double check untuk memastikan id_dosen tidak null
        if (empty($dosen)) {
            throw new Exception("Data dosen tidak valid. Silakan pilih mata kuliah lain.");
        }

        // Cek jadwal yang bentrok
        $query_check = "SELECT jk.*, mk.nama_mk, d.nama as nama_dosen 
                       FROM jadwal_kuliah jk
                       JOIN mata_kuliah mk ON jk.id_mata_kuliah = mk.id
                       JOIN dosen d ON jk.id_dosen = d.id
                       WHERE jk.hari = ? 
                       AND (
                           (jk.jam_mulai BETWEEN ? AND ?) 
                           OR (jk.jam_selesai BETWEEN ? AND ?) 
                           OR (? BETWEEN jk.jam_mulai AND jk.jam_selesai)
                       )
                       AND (jk.ruangan = ? OR jk.id_dosen = ?)";
        
        $stmt_check = $conn->prepare($query_check);
        $stmt_check->bind_param("ssssssss", $hari, $jam_mulai, $jam_selesai, $jam_mulai, $jam_selesai, $jam_mulai, $ruangan, $dosen);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $bentrok = $result_check->fetch_assoc();
            throw new Exception(
                "Terjadi bentrok jadwal dengan:<br>" .
                "Mata Kuliah: {$bentrok['nama_mk']}<br>" .
                "Dosen: {$bentrok['nama_dosen']}<br>" .
                "Waktu: {$bentrok['jam_mulai']} - {$bentrok['jam_selesai']}<br>" .
                "Ruangan: {$bentrok['ruangan']}"
            );
        }

        // Query untuk menyimpan data jadwal kuliah
        $query = "INSERT INTO jadwal_kuliah (id_mata_kuliah, id_dosen, hari, jam_mulai, jam_selesai, ruangan)
                 VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("iissss", $mata_kuliah, $dosen, $hari, $jam_mulai, $jam_selesai, $ruangan);

        if ($stmt->execute()) {
            header("Location: jadwal_kuliah.php");
            exit();
        } else {
            throw new Exception("Error saat menyimpan jadwal: " . $stmt->error);
        }
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Query untuk mendapatkan data mata kuliah dengan dosen (hanya yang memiliki dosen)
$query_matkul = "SELECT mk.id, mk.nama_mk, mk.sks, mk.id_dosen, d.nama as nama_dosen 
                 FROM mata_kuliah mk 
                 INNER JOIN dosen d ON mk.id_dosen = d.id
                 ORDER BY mk.nama_mk ASC";
$result_matkul = mysqli_query($conn, $query_matkul);

if (!$result_matkul) {
    die("Error dalam query mata kuliah: " . mysqli_error($conn));
}

// Store mata kuliah data
$matkul_data = array();
while ($row = mysqli_fetch_assoc($result_matkul)) {
    $matkul_data[] = $row;
}

// Query untuk mendapatkan data ruangan
$query_ruangan = "SELECT id, nama_ruangan FROM ruangan ORDER BY nama_ruangan ASC";
$result_ruangan = mysqli_query($conn, $query_ruangan);

if (!$result_ruangan) {
    die("Error dalam query ruangan: " . mysqli_error($conn));
}
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

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .form-label {
            font-weight: 500;
            color: var(--dark-gray);
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 0.75rem 1rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: #95a5a6;
            border-color: #95a5a6;
        }

        .btn-secondary:hover {
            background-color: #7f8c8d;
            border-color: #7f8c8d;
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
    </nav>

    <?php if (!empty($error_message)): ?>
        <div id="error_message"><?= $error_message ?></div>
    <?php endif; ?>

    <div class="container mt-4">
        <h1 class="mb-4">Tambah Jadwal Kuliah</h1>
        <form method="POST" action="tambah_jadwal_kuliah.php" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label for="mata_kuliah" class="form-label">Mata Kuliah</label>
                <select id="mata_kuliah" name="mata_kuliah" class="form-select" required>
                    <option value="">Pilih Mata Kuliah</option>
                    <?php foreach ($matkul_data as $row): ?>
                        <option value="<?= htmlspecialchars($row['id']); ?>" 
                                data-sks="<?= htmlspecialchars($row['sks']); ?>"
                                data-dosen="<?= htmlspecialchars($row['id_dosen']); ?>">
                            <?= htmlspecialchars($row['nama_mk']); ?> 
                            (<?= htmlspecialchars($row['sks']); ?> SKS) - 
                            <?= htmlspecialchars($row['nama_dosen']); ?>
                        </option>
                    <?php endforeach; ?>
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
                <input type="time" id="jam_selesai" name="jam_selesai" class="form-control" readonly required>
            </div>

            <div class="mb-3">
                <label for="ruangan" class="form-label">Ruangan</label>
                <select id="ruangan" name="ruangan" class="form-select" required>
                    <option value="">Pilih Ruangan</option>
                    <?php while ($row = mysqli_fetch_assoc($result_ruangan)): ?>
                        <option value="<?= $row['nama_ruangan']; ?>"><?= $row['nama_ruangan']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Tambah Jadwal</button>
                <a href="jadwal_kuliah.php" class="btn btn-secondary">Batal</a>
            </div>        
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const matkulData = <?= json_encode($matkul_data); ?>;

    document.getElementById('mata_kuliah').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const dosenId = selectedOption.getAttribute('data-dosen');
        
        // Call the existing function to calculate end time
        hitungJamSelesai();
    });

    document.getElementById('jam_mulai').addEventListener('input', hitungJamSelesai);
    
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

    // Auto-hide error message
    const errorMessage = document.getElementById('error_message');
    if (errorMessage) {
        setTimeout(() => {
            errorMessage.style.display = 'none';
        }, 1000000);
    }
</script>
</body>
</html>