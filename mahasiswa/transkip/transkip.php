<?php
session_start();
include('../../config/koneksi.php');

// Cek apakah user sudah login dan memiliki peran 'mahasiswa'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data mahasiswa berdasarkan user_id
$query_mahasiswa = "SELECT m.*, ps.nama_prodi, ps.fakultas 
                    FROM mahasiswa m 
                    JOIN program_studi ps ON m.id_prodi = ps.id 
                    WHERE m.user_id = ?";
$stmt_mahasiswa = $conn->prepare($query_mahasiswa);
$stmt_mahasiswa->bind_param("i", $user_id);
$stmt_mahasiswa->execute();
$result_mahasiswa = $stmt_mahasiswa->get_result();
$mahasiswa = $result_mahasiswa->fetch_assoc();

// Cek jika mahasiswa ditemukan
if (!$mahasiswa) {
    echo "Mahasiswa tidak ditemukan!";
    exit;
}

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
$total_matkul = $result_nilai->num_rows;

// Hitung IPK
$total_bobot_sks = 0;
$total_sks = 0;

while ($row = $result_nilai->fetch_assoc()) {
    $bobot = 0;
    switch ($row['nilai_huruf']) {
        case 'A': $bobot = 4; break;
        case 'B': $bobot = 3; break;
        case 'C': $bobot = 2; break;
        case 'D': $bobot = 1; break;
        case 'E': $bobot = 0; break;
    }
    $total_bobot_sks += $bobot * $row['sks'];
    $total_sks += $row['sks'];
}

$ipk = $total_sks > 0 ? $total_bobot_sks / $total_sks : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transkrip Nilai Akademik</title>
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
            white-space: nowrap;
        }

        .table tbody td {
            padding: 1rem;
            text-align: center;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }

        .summary-section {
            margin-top: 2rem;
            padding: 1.5rem;
            background: linear-gradient(to right, #f8f9fa, #ffffff);
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .summary-item {
            display: flex;
            align-items: center;
            padding: 1.5rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .summary-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .summary-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border-radius: 12px;
            margin-right: 1.5rem;
            color: white;
            font-size: 1.5rem;
        }

        .summary-content {
            flex: 1;
        }

        .summary-label {
            font-size: 0.9rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.3rem;
            font-weight: 500;
        }

        .summary-value {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--primary-color);
            line-height: 1.2;
            margin: 0;
        }

        @media print {
            body {
                background-color: white;
            }
            .navbar, .no-print {
                display: none;
            }
            .content-card {
                box-shadow: none;
            }
            .summary-item {
                break-inside: avoid;
            }
            .table thead th {
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
            }
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
            .summary-grid {
                grid-template-columns: 1fr;
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
                <i class="fas fa-file-alt fa-2x text-primary"></i>
                <h1>Transkrip Nilai Akademik</h1>
            </div>
        </div>

        <div class="content-card">
            <div class="info-grid">
                <div class="info-card">
                    <h3>Nama Mahasiswa</h3>
                    <p><?php echo htmlspecialchars($mahasiswa['nama']); ?></p>
                </div>
                <div class="info-card">
                    <h3>NIM</h3>
                    <p><?php echo htmlspecialchars($mahasiswa['nim']); ?></p>
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

            <?php if ($total_matkul > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode MK</th>
                                <th>Mata Kuliah</th>
                                <th>SKS</th>
                                <th>Tugas</th>
                                <th>Kuis</th>
                                <th>UTS</th>
                                <th>UAS</th>
                                <th>Nilai Angka</th>
                                <th>Nilai Huruf</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            $result_nilai->data_seek(0);
                            while ($row = $result_nilai->fetch_assoc()): 
                            ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row['kode_mk']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_mk']); ?></td>
                                    <td><?php echo htmlspecialchars($row['sks']); ?></td>
                                    <td><?php echo htmlspecialchars($row['tugas']); ?></td>
                                    <td><?php echo htmlspecialchars($row['kuis']); ?></td>
                                    <td><?php echo htmlspecialchars($row['uts']); ?></td>
                                    <td><?php echo htmlspecialchars($row['uas']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nilai_angka']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nilai_huruf']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div class="summary-section">
                    <div class="summary-grid">
                        <div class="summary-item">
                            <div class="summary-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="summary-content">
                                <div class="summary-label">Total SKS</div>
                                <div class="summary-value"><?php echo $total_sks; ?></div>
                            </div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-icon">
                                <i class="fas fa-award"></i>
                            </div>
                            <div class="summary-content">
                                <div class="summary-label">Index Prestasi Kumulatif</div>
                                <div class="summary-value"><?php echo number_format($ipk, 2); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Belum ada data nilai untuk ditampilkan.
                </div>
            <?php endif; ?>

            <div class="text-end mt-4">
                <button onclick="window.print()" class="btn btn-primary no-print">
                    <i class="fas fa-print me-2"></i>Cetak Transkrip
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>