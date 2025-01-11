<?php
session_start();
include('../../config/koneksi.php');

// Cek apakah user sudah login dan memiliki role sebagai mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data mahasiswa berdasarkan user_id
$queryMahasiswa = "SELECT m.*, ps.nama_prodi, ps.fakultas 
                   FROM mahasiswa m 
                   JOIN program_studi ps ON m.id_prodi = ps.id 
                   WHERE m.user_id = ?";
$stmtMahasiswa = $conn->prepare($queryMahasiswa);
$stmtMahasiswa->bind_param("i", $user_id);
$stmtMahasiswa->execute();
$resultMahasiswa = $stmtMahasiswa->get_result();
$mahasiswa = $resultMahasiswa->fetch_assoc();

// Cek jika mahasiswa ditemukan
if (!$mahasiswa) {
    echo "Mahasiswa tidak ditemukan!";
    exit;
}

// Ambil jadwal kuliah berdasarkan mata kuliah yang diambil mahasiswa
$queryJadwal = "SELECT 
                    mk.nama_mk AS mata_kuliah,
                    mk.kode_mk AS kode_mk,
                    mk.sks AS sks,
                    jk.hari AS hari,
                    jk.jam_mulai AS jam_mulai,
                    jk.jam_selesai AS jam_selesai,
                    jk.ruangan AS ruangan,
                    d.nama AS dosen
                FROM krs k
                JOIN mata_kuliah mk ON k.id_mata_kuliah = mk.id
                JOIN jadwal_kuliah jk ON jk.id_mata_kuliah = mk.id
                JOIN dosen d ON jk.id_dosen = d.id
                WHERE k.id_mahasiswa = ?";
$stmtJadwal = $conn->prepare($queryJadwal);
$stmtJadwal->bind_param("i", $mahasiswa['id']);
$stmtJadwal->execute();
$resultJadwal = $stmtJadwal->get_result();
$total_jadwal = $resultJadwal->num_rows;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Kuliah Mahasiswa</title>
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

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-card {
            background: var(--light-gray);
            padding: 1.5rem;
            border-radius: 10px;
            border-left: 4px solid var(--secondary-color);
        }

        .info-card h3 {
            color: var(--primary-color);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-card p {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0;
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

        .alert {
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffeeba;
            color: #856404;
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 1.5rem;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }

            .content-card {
                padding: 1rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .table-responsive {
                overflow-x: auto;
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
                <i class="fas fa-calendar-alt fa-2x text-primary"></i>
                <h1>Jadwal Kuliah Mahasiswa</h1>
            </div>
        </div>

        <div class="content-card">
            <div class="info-grid">
                <div class="info-card">
                    <h3>NIM</h3>
                    <p><?php echo htmlspecialchars($mahasiswa['nim']); ?></p>
                </div>
                <div class="info-card">
                    <h3>Nama Mahasiswa</h3>
                    <p><?php echo htmlspecialchars($mahasiswa['nama']); ?></p>
                </div>
                <div class="info-card">
                    <h3>Program Studi</h3>
                    <p><?php echo htmlspecialchars($mahasiswa['nama_prodi']); ?></p>
                </div>
                <div class="info-card">
                    <h3>Fakultas</h3>
                    <p><?php echo htmlspecialchars($mahasiswa['fakultas']); ?></p>
                </div>
            </div>

            <?php if ($total_jadwal == 0): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Tidak ada jadwal kuliah yang ditemukan.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Kode MK</th>
                                <th>Mata Kuliah</th>
                                <th>SKS</th>
                                <th>Hari</th>
                                <th>Jam Mulai</th>
                                <th>Jam Selesai</th>
                                <th>Ruangan</th>
                                <th>Dosen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($jadwal = $resultJadwal->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($jadwal['kode_mk']); ?></td>
                                    <td><?php echo htmlspecialchars($jadwal['mata_kuliah']); ?></td>
                                    <td><?php echo htmlspecialchars($jadwal['sks']); ?></td>
                                    <td><?php echo htmlspecialchars($jadwal['hari']); ?></td>
                                    <td><?php echo htmlspecialchars($jadwal['jam_mulai']); ?></td>
                                    <td><?php echo htmlspecialchars($jadwal['jam_selesai']); ?></td>
                                    <td><?php echo htmlspecialchars($jadwal['ruangan']); ?></td>
                                    <td><?php echo htmlspecialchars($jadwal['dosen']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>