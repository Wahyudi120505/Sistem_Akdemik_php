<?php
session_start();
include('../../config/koneksi.php');

// Cek apakah user sudah login dan memiliki peran 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Ambil nilai pencarian dari form
$search = isset($_GET['search']) ? $_GET['search'] : '';


// Ambil data ruangan dari database
if ($search) {
    $query = "SELECT * FROM ruangan WHERE nama_ruangan LIKE '$search%' OR kode_ruangan LIKE '$search%' OR kapasitas LIKE '$search%' OR lokasi LIKE '$search%'";
} else {
    $query = "SELECT * FROM ruangan";
}
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Ruangan</title>
    <!-- Link ke Bootstrap CSS -->
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

        .search-form {
            display: flex;
            gap: 0.5rem;
        }

        .search-form .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.5rem 1rem;
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
            border: 1px solid rgba(0,0,0,0.05);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-title h1 {
            color: var(--primary-color);
            font-size: 2rem;
            font-weight: 600;
            margin: 0;
        }

        .header-title .fas {
            color: var(--secondary-color);
            font-size: 1.8rem;
        }

        .header-action-button {
            background-color: var(--secondary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .header-action-button:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            color: white;
        }

        .stats-mini {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .stats-mini:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .stats-mini i {
            font-size: 2.5rem;
            color: var(--secondary-color);
            margin-right: 1rem;
        }

        .stats-mini h5 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            color: var(--primary-color);
        }

        .stats-mini small {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .content-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background-color: var(--light-gray);
            border-bottom: 2px solid #dee2e6;
            color: var(--primary-color);
            font-weight: 600;
            padding: 1rem;
            text-align: center;
            vertical-align: middle;
        }

        .table tbody td {
            padding: 1rem;
            text-align: center;
            vertical-align: middle;
        }

        .btn-action {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-warning {
            background-color: var(--warning-color);
            border-color: var(--warning-color);
            color: var(--dark-gray);
        }

        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }

        .btn-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 1.5rem;
            }

            .header-content {
                flex-direction: column;
                text-align: center;
            }

            .header-title {
                justify-content: center;
            }

            .stats-mini {
                text-align: center;
            }

            .search-form {
                margin-top: 1rem;
                width: 100%;
            }

            .search-form .form-control {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="../dashboard.php">
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
                            <input class="form-control" type="text" name="search" placeholder="Cari Ruangan..." value="<?php echo htmlspecialchars($search); ?>">
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
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-title">
                    <i class="fas fa-door-open feature-icon"></i>
                    <h1>Manajemen Ruangan</h1>
                </div>
                <a href="tambah_ruangan.php" class="header-action-button">
                    <i class="fas fa-plus"></i>
                    Tambah Ruangan
                </a>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-mini d-flex align-items-center">
                    <i class="fas fa-door-open feature-icon"></i>
                    <div>
                        <h5><?php echo mysqli_num_rows($result); ?></h5>
                        <small class="text-muted">Total Ruangan</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Daftar Ruangan -->
        <div class="content-card">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="20%">Kode Ruangan</th>
                            <th width="15%">Nama Ruangan</th>
                            <th width="10%">kapasitas</th>
                            <th width="5%">lokasi</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
            <tbody>
                <?php 
                if (empty($result)) {
                    echo "<tr><td colspan='6' class='text-center'>Tidak ada data mahasiswa.</td></tr>";
                } else {
                $no = 1;
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>{$no}</td>";
                    echo "<td>{$row['kode_ruangan']}</td>";
                    echo "<td>{$row['nama_ruangan']}</td>";
                    echo "<td>{$row['kapasitas']}</td>";
                    echo "<td>{$row['lokasi']}</td>";
                    echo "<td>
                            <a href='edit_ruangan.php?id={$row['id']}' class='btn btn-warning btn-action me-2'>
                                <i class='fas fa-edit'></i>
                                Edit
                            </a>
                            <a href='hapus_ruangan.php?id={$row['id']}' class='btn btn-danger btn-action' onclick='return confirm(\"Apakah Anda yakin ingin menghapus program studi ini?\")'>
                                <i class='fas fa-trash'></i>
                                Hapus
                            </a>
                          </td>";
                    echo "</tr>";
                    $no++;
                }}?>
            </tbody>
        </table>
    </div>
</body>
</html>
