<?php
session_start();
include('../config/koneksi.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$query = "SELECT m.*, ps.nama_prodi, ps.fakultas 
          FROM mahasiswa m 
          JOIN program_studi ps ON m.id_prodi = ps.id 
          WHERE m.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$mahasiswa = $result->fetch_assoc();

// Ambil data transkrip nilai
$query_nilai = "SELECT mk.kode_mk, mk.nama_mk, mk.sks, 
                       k.tugas, k.kuis, k.uts, k.uas, 
                       k.nilai_angka, k.nilai_huruf 
                FROM khs k
                JOIN mata_kuliah mk ON k.id_mata_kuliah = mk.id
                WHERE k.id_mahasiswa = ?";
$stmt_nilai = $conn->prepare($query_nilai);
$stmt_nilai->bind_param("i", $mahasiswa['id']);
$stmt_nilai->execute();
$result_nilai = $stmt_nilai->get_result();

// Ambil total SKS dari KRS
$query_total_sks = "SELECT SUM(mk.sks) AS total_sks
                    FROM krs k
                    JOIN mata_kuliah mk ON k.id_mata_kuliah = mk.id
                    WHERE k.id_mahasiswa = ?";
$stmt_total_sks = $conn->prepare($query_total_sks);
$stmt_total_sks->bind_param("i", $mahasiswa['id']);
$stmt_total_sks->execute();
$result_total_sks = $stmt_total_sks->get_result();
$row_total_sks = $result_total_sks->fetch_assoc();
$total_sks = $row_total_sks['total_sks'] ?: 0;

// Hitung bobot dan nilai akhir
$total_bobot_sks = 0;
$total_sks_hitung = 0;

while ($row = $result_nilai->fetch_assoc()) {
    switch ($row['nilai_huruf']) {
        case 'A':
            $bobot = 4;
            break;
        case 'B':
            $bobot = 3;
            break;
        case 'C':
            $bobot = 2;
            break;
        case 'D':
            $bobot = 1;
            break;
        case 'E':
            $bobot = 0;
            break;
        default:
            $bobot = 0;
            break;
    }

    // Tambahkan nilai bobot * SKS untuk setiap mata kuliah
    $total_bobot_sks += $bobot * $row['sks'];
    $total_sks_hitung += $row['sks'];
}

// Menghitung nilai akhir
if ($total_sks_hitung > 0) {
    $ipk = $total_bobot_sks / $total_sks_hitung;
} else {
    $ipk = 0;
}

$hari = date('l');
$hari_indo = [
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu',
    'Sunday' => 'Minggu'
];
$hari = $hari_indo[$hari];

$jadwal_query = "SELECT jk.*, mk.nama_mk, mk.kode_mk, d.nama as nama_dosen, r.nama_ruangan
                 FROM jadwal_kuliah jk
                 JOIN mata_kuliah mk ON jk.id_mata_kuliah = mk.id
                 JOIN dosen d ON jk.id_dosen = d.id
                 JOIN krs ON krs.id_mata_kuliah = mk.id
                 JOIN ruangan r ON jk.ruangan = r.kode_ruangan
                 WHERE krs.id_mahasiswa = ? AND jk.hari = ?";
$stmt = $conn->prepare($jadwal_query);
$stmt->bind_param("is", $mahasiswa['id'], $hari);
$stmt->execute();
$jadwal_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa - Sistem Akademik</title>
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
        }

        .navbar {
            background-color: var(--primary-color) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-weight: 600;
            font-size: 1.5rem;
        }

        .dashboard-header {
            background-color: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .feature-card {
            border: none;
            border-radius: 10px;
            transition: all 0.3s ease;
            background: var(--light-gray);
            margin-bottom: 1rem;
            padding: 1.5rem;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .btn-logout {
            background-color: var(--danger-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .btn-logout:hover {
            background-color: #c0392b;
            color: white;
        }

        .schedule-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-university me-2"></i>
                Sistem Akademik
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="fas fa-user me-1"></i>
                            <?php echo htmlspecialchars($mahasiswa['nama']); ?>
                        </span>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-logout" href="../logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>
                            Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <h1 class="mb-3">
                <i class="fas fa-user-graduate me-2"></i>
                Selamat Datang, <?php echo htmlspecialchars($mahasiswa['nama']); ?>
            </h1>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>NIM:</strong> <?php echo htmlspecialchars($mahasiswa['nim']); ?></p>
                    <p class="mb-1"><strong>Program Studi:</strong> <?php echo htmlspecialchars($mahasiswa['nama_prodi']); ?></p>
                    <p class="mb-1"><strong>Fakultas:</strong> <?php echo htmlspecialchars($mahasiswa['fakultas']); ?></p>
                    <p class="mb-1"><strong>IPK:</strong> <?php echo $ipk; ?></p>
                    <p class="mb-1"><strong>Semester:</strong> <?php echo htmlspecialchars($mahasiswa['semester']); ?></p>
                    <p class="mb-1"><strong>Angkatan:</strong> <?php echo htmlspecialchars($mahasiswa['angkatan']); ?></p>
                </div>
                <div class="col-md-6">
                    <div class="stats-card bg-primary text-white">
                        <h3 class="mb-2">IPK Kumulatif</h3>
                        <h2 class="display-4 mb-0"><?= htmlspecialchars(number_format($ipk, 2)) ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu Features -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <a href="krs/krs.php" class="text-decoration-none">
                    <div class="feature-card text-center">
                        <i class="fas fa-edit fa-3x mb-3 text-primary"></i>
                        <h4>KRS</h4>
                        <p class="text-muted">Pengisian Kartu Rencana Studi</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="matkul/matkul.php" class="text-decoration-none">
                    <div class="feature-card text-center">
                        <i class="fas fa-book fa-3x mb-3 text-secondary"></i>
                        <h4>Mata Kuliah</h4>
                        <p class="text-muted">Lihat Daftar Mata Kuliah</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="transkip/transkip.php" class="text-decoration-none">
                    <div class="feature-card text-center">
                        <i class="fas fa-file-alt fa-3x mb-3 text-success"></i>
                        <h4>Transkrip</h4>
                        <p class="text-muted">Lihat Transkrip Nilai</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="jadwal_kuliah/jadwal_kuliah.php" class="text-decoration-none">
                    <div class="feature-card text-center">
                        <i class="fas fa-calendar fa-3x mb-3 text-warning"></i>
                        <h4>Jadwal Kuliah</h4>
                        <p class="text-muted">Lihat Jadwal Perkuliahan</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="soal/soal.php" class="text-decoration-none">
                    <div class="feature-card text-center">
                        <i class="fas fa-tasks fa-3x mb-3 text-danger"></i>
                        <h4>Tugas</h4>
                        <p class="text-muted">Daftar Tugas Perkuliahan</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="profile/profile.php" class="text-decoration-none">
                    <div class="feature-card text-center">
                        <i class="fas fa-user fa-3x mb-3 text-info"></i>
                        <h4>Profile</h4>
                        <p class="text-muted">Pengaturan Profil</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Today's Schedule -->
        <div class="schedule-card">
            <h3 class="mb-4">
                <i class="fas fa-calendar-day me-2"></i>
                Jadwal Kuliah Hari Ini (<?php echo $hari; ?>)
            </h3>
            <?php if ($jadwal_result->num_rows > 0): ?>
                <div class="row">
                    <?php while ($jadwal = $jadwal_result->fetch_assoc()): ?>
                        <div class="col-md-6 mb-3">
                            <div class="feature-card">
                                <h4 class="text-primary"><?php echo htmlspecialchars($jadwal['nama_mk']); ?></h4>
                                <p class="mb-2">
                                    <i class="fas fa-clock me-2"></i>
                                    <?php echo htmlspecialchars($jadwal['jam_mulai']) . ' - ' . htmlspecialchars($jadwal['jam_selesai']); ?>
                                </p>
                                <p class="mb-2">
                                    <i class="fas fa-door-open me-2"></i>
                                    Ruang: <?php echo htmlspecialchars($jadwal['nama_ruangan']); ?>
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-chalkboard-teacher me-2"></i>
                                    Dosen: <?php echo htmlspecialchars($jadwal['nama_dosen']); ?>
                                </p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Tidak ada jadwal kuliah hari ini.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>