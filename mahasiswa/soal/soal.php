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
$query = "SELECT m.*, ps.nama_prodi, ps.fakultas 
          FROM mahasiswa m 
          JOIN program_studi ps ON m.id_prodi = ps.id 
          WHERE m.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$mahasiswa = $result->fetch_assoc();

// Cek jika mahasiswa ditemukan
if (!$mahasiswa) {
    echo "Mahasiswa tidak ditemukan!";
    exit;
}

// Ambil mata kuliah yang diambil mahasiswa
$query_mata_kuliah = "SELECT k.id_mata_kuliah, mk.nama_mk 
                      FROM krs k 
                      JOIN mata_kuliah mk ON k.id_mata_kuliah = mk.id 
                      WHERE k.id_mahasiswa = ?";
$stmt_mk = $conn->prepare($query_mata_kuliah);
$stmt_mk->bind_param("i", $mahasiswa['id']);
$stmt_mk->execute();
$result_mk = $stmt_mk->get_result();

$mata_kuliah_ids = [];
while ($mk = $result_mk->fetch_assoc()) {
    $mata_kuliah_ids[] = $mk['id_mata_kuliah'];
}

if (empty($mata_kuliah_ids)) {
    $message = "Mahasiswa tidak terdaftar pada mata kuliah manapun.";
    $message_type = "warning";
} else {
    // Ambil data soal berdasarkan mata kuliah yang diambil mahasiswa
    $mk_ids = implode(',', $mata_kuliah_ids);
    $query_soal = "SELECT 
        bank_soal.id AS id_bank_soal, 
        bank_soal.judul AS bank_soal_judul,
        bank_soal.deskripsi AS bank_soal_deskripsi,
        bank_soal.tanggal_dibuat AS bank_soal_tanggal_dibuat,
        bank_soal.batas_waktu AS bank_soal_batas_waktu,
        bank_soal.STATUS AS bank_soal_status,
        mata_kuliah.nama_mk AS mata_kuliah_nama,
        jenis_soal.nama AS jenis_soal_nama
    FROM 
        bank_soal 
    JOIN mata_kuliah ON bank_soal.id_mata_kuliah = mata_kuliah.id 
    JOIN jenis_soal ON bank_soal.id_jenis_soal = jenis_soal.id
    WHERE bank_soal.id_mata_kuliah IN ($mk_ids)";

    $result_soal = mysqli_query($conn, $query_soal);
    $total_soal = mysqli_num_rows($result_soal);

    if ($total_soal == 0) {
        $message = "Tidak ada soal yang tersedia saat ini.";
        $message_type = "warning";
    } else {
        $message = "Daftar soal yang tersedia untuk " . $mahasiswa['nama'];
        $message_type = "success";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soal Mata Kuliah</title>
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

        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffeeba;
            color: #856404;
        }

        tr.disabled {
            background-color: rgba(0, 0, 0, 0.05);
            cursor: not-allowed;
        }

        tr.disabled:hover {
            background-color: rgba(0, 0, 0, 0.08);
        }

        .badge {
            padding: 0.5em 1em;
            font-weight: 500;
            font-size: 0.85em;
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
                <i class="fas fa-book fa-2x text-primary"></i>
                <h1>Soal Mata Kuliah</h1>
            </div>
        </div>

        <div class="content-card">
            <div class="info-grid">
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

            <?php if (isset($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <i class="fas <?php echo $message_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> me-2"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($result_soal) && mysqli_num_rows($result_soal) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Mata Kuliah</th>
                                <th>Jenis Soal</th>
                                <th>Judul Soal</th>
                                <th>Tanggal Dibuat</th>
                                <th>Batas Waktu</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while ($soal = mysqli_fetch_assoc($result_soal)): 
                                $is_active = $soal['bank_soal_status'] === 'aktif';
                            ?>
                                <tr class="<?php echo $is_active ? '' : 'disabled'; ?>" 
                                    style="<?php echo $is_active ? 'cursor: pointer;' : ''; ?>"
                                    onclick="<?php echo $is_active ? "window.location.href='kerjakan_soal.php?id_bank_soal={$soal['id_bank_soal']}'" : "showPopup()"; ?>">
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($soal['mata_kuliah_nama']); ?></td>
                                    <td><?php echo htmlspecialchars($soal['jenis_soal_nama']); ?></td>
                                    <td><?php echo htmlspecialchars($soal['bank_soal_judul']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($soal['bank_soal_tanggal_dibuat'])); ?></td>
                                    <td><?php echo date('d M Y H:i', strtotime($soal['bank_soal_batas_waktu'])); ?></td>
                                    <td>
                                        <span class="badge <?php echo $is_active ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?php echo ucfirst($soal['bank_soal_status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showPopup() {
            alert("Soal ini sudah tidak aktif atau batas waktu sudah lewat.");
        }
    </script>
</body>
</html>