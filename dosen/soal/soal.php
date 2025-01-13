<?php
session_start();
include('../../config/koneksi.php');

// Cek apakah user sudah login dan memiliki peran 'dosen'
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
    $dosen_nip = $dosen['nip']; // Mendapatkan NIP dosen
} else {
    echo "Data dosen tidak ditemukan.";
    exit;
}

date_default_timezone_set('Asia/Jakarta'); // Sesuaikan dengan zona waktu Anda

// Cek batas waktu untuk update status menjadi tidak aktif
$current_time = date('Y-m-d H:i'); // Mendapatkan waktu saat ini

// Ambil data soal sebelum update status
$query_soal = "SELECT 
    soal.id AS soal_id,
    soal.pertanyaan,
    soal.bobot_nilai,
    bank_soal.id AS bank_soal_id,
    bank_soal.judul AS bank_soal_judul,
    bank_soal.deskripsi AS bank_soal_deskripsi,
    bank_soal.tanggal_dibuat AS bank_soal_tanggal_dibuat,
    bank_soal.batas_waktu AS bank_soal_batas_waktu,
    bank_soal.STATUS AS bank_soal_status,
    mata_kuliah.kode_mk AS mata_kuliah_kode,
    mata_kuliah.nama_mk AS mata_kuliah_nama,
    dosen.nip AS dosen_nip,
    dosen.nama AS dosen_nama,
    dosen.jabatan AS dosen_jabatan,
    jenis_soal.nama AS jenis_soal_nama
FROM 
    soal
JOIN bank_soal ON soal.id_bank_soal = bank_soal.id
JOIN mata_kuliah ON bank_soal.id_mata_kuliah = mata_kuliah.id
JOIN dosen ON bank_soal.id_dosen = dosen.id
JOIN jenis_soal ON bank_soal.id_jenis_soal = jenis_soal.id
WHERE dosen.nip = '$dosen_nip'"; // Filter soal berdasarkan NIP dosen

// Ambil nilai pencarian dari form dan bersihkan
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim(strtolower($_GET['search']))) : '';

// Modifikasi query untuk filter berdasarkan pencarian
if (!empty($search)) {
    // Jika pencarian terkait status
    if ($search === 'aktif' || $search === 'tidak aktif' || $search === 'tidak_aktif') {
        $status_search = ($search === 'aktif') ? 'aktif' : 'tidak_aktif';
        $query_soal .= " AND bank_soal.STATUS = '$status_search'";
    } else {
        // Pencarian untuk field lainnya
        $query_soal .= " AND (
            LOWER(mata_kuliah.nama_mk) LIKE '%$search%' OR 
            LOWER(bank_soal.judul) LIKE '%$search%' OR 
            LOWER(dosen.nama) LIKE '%$search%' OR 
            LOWER(jenis_soal.nama) LIKE '%$search%'
        )";
    }
}

$result_soal = mysqli_query($conn, $query_soal);

// Update status soal jika sudah melewati batas waktu
while ($soal = mysqli_fetch_assoc($result_soal)) {
    $waktu_batas = date('Y-m-d H:i', strtotime($soal['bank_soal_batas_waktu']));
    if (strtotime($waktu_batas) < strtotime($current_time) && $soal['bank_soal_status'] !== 'tidak_aktif') {
        $update_status_query = "UPDATE bank_soal SET STATUS = 'tidak_aktif' WHERE id = {$soal['bank_soal_id']}";
        mysqli_query($conn, $update_status_query);
    }
}

// Ambil data soal kembali setelah status diperbarui
$result_soal = mysqli_query($conn, $query_soal);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Soal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --light-gray: #f8f9fa;
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

        .search-form {
            display: flex;
            gap: 0.5rem;
        }

        .search-form .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            width: 300px;
        }

        .search-form .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .search-form .form-control:focus {
            background: rgba(255, 255, 255, 0.2);
            box-shadow: none;
            border-color: rgba(255, 255, 255, 0.3);
        }

        .page-header {
            background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .stats-mini {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .stats-mini:hover {
            transform: translateY(-2px);
        }

        .content-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .table thead th {
            background-color: var(--light-gray);
            color: var(--primary-color);
            font-weight: 600;
        }

        .btn-action {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .btn-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <form class="search-form" action="" method="GET">
                            <input class="form-control" type="text" name="search" placeholder="Cari Soal, Mata Kuliah, Jenis Soal, Status..." value="<?php echo htmlspecialchars($search ?? '');?>">
                            <button class="btn btn-outline-light" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <i class="fas fa-book fa-2x text-primary"></i>
                    <h1 class="mb-0">Soal yang Dibuat</h1>
                </div>
                <a href="tambah_soal.php" class="btn btn-primary btn-action">
                    <i class="fas fa-plus me-2"></i>Tambah Soal Baru
                </a>
            </div>
        </div>

        <div class="content-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th>Mata Kuliah</th>
                    <th>Jenis Soal</th>
                    <th>Judul Soal</th>
                    <th>Tanggal Dibuat</th>
                    <th>Batas Waktu</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result_soal) > 0) {
                    $no = 1;
                    while ($soal = mysqli_fetch_assoc($result_soal)): ?>
                        <tr onclick="window.location.href='cek.php?id=<?php echo $soal['bank_soal_id']; ?>'">                            <td><?php echo $no; ?></td>
                            <td><?php echo $soal['mata_kuliah_nama']; ?></td>
                            <td><?php echo $soal['jenis_soal_nama']; ?></td>
                            <td><?php echo $soal['bank_soal_judul']; ?></td>
                            <td><?php echo date('d M Y', strtotime($soal['bank_soal_tanggal_dibuat'])); ?></td>
                            <td><?php echo date('d M Y H:i', strtotime($soal['bank_soal_batas_waktu'])); ?></td>
                            <td>
                                <span class="badge <?php echo $soal['bank_soal_status'] === 'published' ? 'bg-success' : 'bg-secondary'; ?>">
                                    <?php echo ucfirst($soal['bank_soal_status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="edit_soal.php?id=<?php echo $soal['soal_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="hapus_soal.php?id=<?php echo $soal['soal_id']; ?>" class="btn btn-danger btn-sm">Hapus</a>
                            </td>
                        </tr>
                    <?php
                        $no++;
                    endwhile;
                } else {
                    echo "<tr><td colspan='8' class='text-center'>Tidak ada data soal.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
