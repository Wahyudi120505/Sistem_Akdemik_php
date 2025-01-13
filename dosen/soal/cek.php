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
    $dosen_nip = $dosen['nip'];
} else {
    echo "Data dosen tidak ditemukan.";
    exit;
}

$bank_soal_id = isset($_GET['id']) ? $_GET['id'] : '';

// Ambil data mahasiswa yang terdaftar di mata kuliah terkait soal
$query_mahasiswa = "
    SELECT 
        mahasiswa.id AS mahasiswa_id,
        mahasiswa.nama AS mahasiswa_nama,
        program_studi.nama_prodi AS prodi,
        done_tugas.id AS done_id,
        COALESCE(done_tugas.status, 'Belum Dikerjakan') AS tugas_status,
        bank_soal.file_soal AS file_soal
    FROM 
        mahasiswa
    JOIN program_studi ON mahasiswa.id_prodi = program_studi.id
    JOIN krs ON mahasiswa.id = krs.id_mahasiswa
    JOIN mata_kuliah ON krs.id_mata_kuliah = mata_kuliah.id
    JOIN bank_soal ON mata_kuliah.id = bank_soal.id_mata_kuliah
    LEFT JOIN done_tugas ON mahasiswa.id = done_tugas.id_mahasiswa AND done_tugas.id_bank_soal = bank_soal.id
    WHERE bank_soal.id = '$bank_soal_id' AND bank_soal.id_dosen = '$dosen_id';
";

$result_mahasiswa = mysqli_query($conn, $query_mahasiswa);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Mahasiswa yang Mengambil Soal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #2ecc71;
            --warning-color: #f1c40f;
            --danger-color: #e74c3c;
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
            margin: 0;
        }

        .content-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .info-card {
            background: var(--light-gray);
            padding: 1.5rem;
            border-radius: 10px;
            border-left: 4px solid var(--secondary-color);
            margin-bottom: 1.5rem;
        }

        .info-card h3 {
            color: var(--primary-color);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-card a {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--secondary-color);
            text-decoration: none;
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
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 1.5rem;
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
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <div class="d-flex align-items-center gap-3">
                <i class="fas fa-users fa-2x text-primary"></i>
                <h1>Daftar Mahasiswa yang Mengambil Soal</h1>
            </div>
        </div>

        <div class="content-card">
            <div class="info-card">
                <h3>File Soal</h3>
                <?php
                if ($result_mahasiswa && mysqli_num_rows($result_mahasiswa) > 0) {
                    $mahasiswa = mysqli_fetch_assoc($result_mahasiswa);
                    $file_soal = $mahasiswa['file_soal'];
                    $file_path = "file_soal/" . $file_soal;

                    if (file_exists($file_path)) {
                        echo '<a href="' . $file_path . '" target="_blank"><i class="fas fa-file-pdf me-2"></i>Lihat File Soal</a>';
                    } else {
                        echo '<p class="text-danger mb-0"><i class="fas fa-exclamation-triangle me-2"></i>File soal tidak ditemukan!</p>';
                    }
                }
                ?>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Mahasiswa</th>
                            <th>Program Studi</th>
                            <th>Status Tugas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        mysqli_data_seek($result_mahasiswa, 0); // Reset hasil query
                        if ($result_mahasiswa && mysqli_num_rows($result_mahasiswa) > 0) {
                            $no = 1;
                            while ($mahasiswa = mysqli_fetch_assoc($result_mahasiswa)) {
                                $status_class = '';
                                if ($mahasiswa['tugas_status'] == 'selesai') {
                                    $status_class = 'text-success';
                                } elseif ($mahasiswa['tugas_status'] == 'Belum Dikerjakan') {
                                    $status_class = 'text-warning';
                                }
                                
                                $url = "nilai.php?id={$mahasiswa['done_id']}";
                                echo "<tr onclick='window.location.href=\"{$url}\"' style='cursor: pointer;'>
                                        <td>{$no}</td>
                                        <td>{$mahasiswa['mahasiswa_nama']}</td>
                                        <td>{$mahasiswa['prodi']}</td>
                                        <td class='{$status_class}'>{$mahasiswa['tugas_status']}</td>
                                    </tr>";
                                $no++;
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center'>Tidak ada mahasiswa yang terdaftar untuk mata kuliah ini.</td></tr>";
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