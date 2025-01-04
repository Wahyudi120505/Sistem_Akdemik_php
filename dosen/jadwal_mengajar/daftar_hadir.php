<?php
session_start();
include('../../config/koneksi.php');

// Cek apakah user sudah login dan memiliki peran 'dosen'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dosen') {
    header("Location: ../index.php");
    exit;
}

// Mengambil username dari session
$user_id = $_SESSION['user_id'];
$query_users = "SELECT username,password FROM users WHERE id = '$user_id'";
$result_user = mysqli_query($conn, $query_users);

// Cek apakah query berhasil dijalankan
if ($result_user && mysqli_num_rows($result_user) > 0) {
    $user = mysqli_fetch_assoc($result_user);
    $username = $user['username'];
}

// Mengambil ID jadwal yang dikirimkan melalui URL
if (!isset($_GET['id'])) {
    echo "ID jadwal kuliah tidak ditemukan.";
    exit;
}

$jadwal_id = $_GET['id'];

// Ambil data jadwal berdasarkan ID yang diberikan
$query_jadwal = "SELECT k.hari, k.jam_mulai, k.jam_selesai, k.ruangan, m.nama_mk
                 FROM jadwal_kuliah k
                 JOIN mata_kuliah m ON k.id_mata_kuliah = m.id
                 JOIN dosen d ON k.id_dosen = d.id
                 WHERE k.id = '{$jadwal_id}' AND d.nip = '{$username}'";
$result_jadwal = mysqli_query($conn, $query_jadwal);

// Inisialisasi variabel jadwal untuk menangani jika query gagal
$jadwal = null;

// Periksa apakah query jadwal berhasil dijalankan dan ada hasilnya
if ($result_jadwal && mysqli_num_rows($result_jadwal) > 0) {
    $jadwal = mysqli_fetch_assoc($result_jadwal);
} else {
    // Jika query gagal, beri pesan atau lakukan penanganan error lain
    echo "<p>Jadwal tidak ditemukan atau ada masalah dengan query.</p>";
    exit;
}

// Ambil daftar mahasiswa yang mengikuti mata kuliah
$query_mahasiswa = "SELECT m.id, m.nama, m.nim
                    FROM mahasiswa m
                    JOIN krs k ON m.id = k.id_mahasiswa
                    WHERE k.id_mata_kuliah = (SELECT id_mata_kuliah FROM jadwal_kuliah WHERE id = '$jadwal_id')";
$result_mahasiswa = mysqli_query($conn, $query_mahasiswa);

// Handle form submit absensi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['absen'] as $mahasiswa_id => $status_absen) {
        $query_absen = "INSERT INTO absensi (jadwal_id, mahasiswa_id, status_absen)
                        VALUES ('$jadwal_id', '$mahasiswa_id', '$status_absen')";
        mysqli_query($conn, $query_absen);
    }
    echo "<p>Absensi berhasil disimpan.</p>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Hadir Mahasiswa</title>
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

        .header-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            border-left: 4px solid #0d6efd;
        }
        .course-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .info-item {
            background: white;
            padding: 10px 15px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .form-select {
            border-radius: 6px;
        }
        .form-select option {
            padding: 10px;
        }
        .attendance-status-hadir { background-color: #d1e7dd; }
        .attendance-status-tidak-hadir { background-color: #f8d7da; }
        .attendance-status-izin { background-color: #fff3cd; }
        .attendance-status-alfa { background-color: #cff4fc; }
        .btn-submit {
            padding: 10px 30px;
            font-weight: 500;
            margin-top: 20px;
        }
    </style>
</head>
<body class="bg-light">
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
    <div class="container py-4">
        <div class="header-section">
            <h1 class="h3 mb-3">Daftar Hadir Mahasiswa</h1>
            <h2 class="h4 text-primary mb-4">Mata Kuliah: <?php echo htmlspecialchars($jadwal['nama_mk']); ?></h2>
            
            <div class="course-info">
                <div class="info-item">
                    <small class="text-muted d-block">Hari</small>
                    <strong><?php echo htmlspecialchars($jadwal['hari']); ?></strong>
                </div>
                <div class="info-item">
                    <small class="text-muted d-block">Jam</small>
                    <strong><?php echo htmlspecialchars($jadwal['jam_mulai']); ?> - <?php echo htmlspecialchars($jadwal['jam_selesai']); ?></strong>
                </div>
                <div class="info-item">
                    <small class="text-muted d-block">Ruangan</small>
                    <strong><?php echo htmlspecialchars($jadwal['ruangan']); ?></strong>
                </div>
            </div>
        </div>

        <div class="table-container">
            <form method="POST">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" width="70">#</th>
                                <th>Nama Mahasiswa</th>
                                <th>NIM</th>
                                <th width="200">Status Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            if (mysqli_num_rows($result_mahasiswa) > 0) {
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($result_mahasiswa)) {
                                    echo "<tr>";
                                    echo "<td class='text-center' >{$no}</td>";
                                    echo "<td>{$row['nama']}</td>";
                                    echo "<td>{$row['nim']}</td>";
                                    echo "<td>
                                            <select name='absen[{$row['id']}]' class='form-select form-select-sm'>
                                                <option value='hadir'>Hadir</option>
                                                <option value='tidak hadir'>Tidak Hadir</option>
                                                <option value='izin'>Izin</option>
                                                <option value='alfa'>Alfa</option>
                                            </select>
                                        </td>";
                                    echo "</tr>";
                                    $no++;
                                }
                            } else {
                                echo "<tr><td colspan='4' class='text-center'>Tidak ada mahasiswa yang terdaftar.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-primary btn-submit">
                        Simpan Absensi
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>