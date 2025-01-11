<?php
session_start();
include('../../config/koneksi.php');

// Cek apakah user sudah login dan memiliki peran 'dosen'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dosen') {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data dosen berdasarkan user_id
$query_dosen = "SELECT * FROM dosen WHERE user_id = '$user_id'";
$result_dosen = mysqli_query($conn, $query_dosen);

if ($result_dosen && mysqli_num_rows($result_dosen) > 0) {
    $dosen = mysqli_fetch_assoc($result_dosen);
    $dosen_id = $dosen['id'];
    $dosen_nip = $dosen['nip'];
} else {
    echo "Data dosen tidak ditemukan.";
    exit;
}

// Ambil nilai pencarian dari URL
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';

// Query dasar untuk mendapatkan mata kuliah
$query_matkul = "SELECT mk.kode_mk, mk.nama_mk, mk.sks, mk.semester, ps.nama_prodi 
                FROM mata_kuliah mk
                JOIN program_studi ps ON mk.id_prodi = ps.id
                WHERE mk.id_dosen = '$dosen_id'";

// Tambahkan kondisi pencarian jika ada kata kunci
if (!empty($search)) {
    $query_matkul .= " AND (
        mk.kode_mk LIKE '%$search%' OR 
        mk.nama_mk LIKE '%$search%' OR 
        mk.semester LIKE '%$search%' OR 
        ps.nama_prodi LIKE '%$search%'
    )";
}

// Tambahkan pengurutan
$query_matkul .= " ORDER BY mk.semester ASC, mk.nama_mk ASC";

$result_matkul = mysqli_query($conn, $query_matkul);

// Hitung total mata kuliah
$total_matkul = mysqli_num_rows($result_matkul);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mata Kuliah Dosen</title>
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

        .navbar-brand {
            font-size: 1.25rem;
            font-weight: 600;
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
            transition: all 0.3s ease;
        }

        .search-form .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .search-form .form-control:focus {
            background: rgba(255, 255, 255, 0.2);
            box-shadow: none;
            border-color: rgba(255, 255, 255, 0.3);
            width: 320px;
        }

        .search-results-info {
            margin-bottom: 1rem;
            color: var(--primary-color);
            font-size: 0.9rem;
        }
        
        .search-highlight {
            background-color: rgba(52, 152, 219, 0.1);
            padding: 0.2rem 0.4rem;
            border-radius: 4px;
            font-weight: 500;
        }

        .page-header {
            background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .content-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background-color: var(--light-gray);
            color: var(--primary-color);
            font-weight: 600;
            padding: 1rem;
            border-bottom: 2px solid #dee2e6;
            text-align: center;
            vertical-align: middle;
        }

        .table tbody td {
            padding: 1rem;
            text-align: center;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
            cursor: pointer;
        }

        .table tbody tr td:first-child {
            font-weight: 500;
            color: var(--primary-color);
        }

        .btn-outline-light:hover {
            background-color: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .alert {
            margin-bottom: 0;
            padding: 1rem;
            border-radius: 10px;
        }

        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffeeba;
            color: #856404;
        }

        @media (max-width: 768px) {
            .search-form .form-control {
                width: 200px;
            }
            
            .search-form .form-control:focus {
                width: 220px;
            }

            .page-header {
                padding: 1.5rem;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }

            .content-card {
                padding: 1rem;
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
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <form class="search-form" action="" method="GET">
                            <input class="form-control" type="text" name="search" 
                                   placeholder="Cari kode, nama MK, semester..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
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
            <div class="d-flex align-items-center gap-3">
                <i class="fas fa-book-open fa-2x text-primary"></i>
                <h1 class="mb-0">Mata Kuliah yang Diajar</h1>
            </div>
        </div>

        <div class="content-card">
            <?php if (!empty($search)): ?>
            <div class="search-results-info">
                <?php if ($total_matkul > 0): ?>
                    Menampilkan <span class="search-highlight"><?php echo $total_matkul; ?></span> hasil pencarian untuk: 
                    <span class="search-highlight"><?php echo htmlspecialchars($search); ?></span>
                <?php else: ?>
                    Tidak ditemukan hasil untuk pencarian: 
                    <span class="search-highlight"><?php echo htmlspecialchars($search); ?></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Kode MK</th>
                            <th>Nama Mata Kuliah</th>
                            <th>SKS</th>
                            <th>Semester</th>
                            <th>Program Studi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_matkul && mysqli_num_rows($result_matkul) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result_matkul)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['kode_mk']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_mk']) ?></td>
                                    <td><?= htmlspecialchars($row['sks']) ?></td>
                                    <td><?= htmlspecialchars($row['semester']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_prodi']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">
                                    <div class="alert alert-warning mb-0">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <?php if (!empty($search)): ?>
                                            Tidak ditemukan mata kuliah yang sesuai dengan pencarian Anda.
                                        <?php else: ?>
                                            Tidak ada mata kuliah yang diajar oleh Anda.
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>