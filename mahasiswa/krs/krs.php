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

// Cek apakah mahasiswa sudah mengisi KRS
$query_krs = "SELECT * FROM krs WHERE id_mahasiswa = ?";
$stmt_krs = $conn->prepare($query_krs);
$stmt_krs->bind_param("i", $mahasiswa['id']);
$stmt_krs->execute();
$result_krs = $stmt_krs->get_result();

// Jika sudah ada KRS, tombol simpan KRS dinonaktifkan
$krs_dibuat = $result_krs->num_rows > 0;
if ($krs_dibuat) {
    // Redirect langsung ke halaman edit_krs.php
    header('Location: edit_krs.php');
    exit;
}

// Query untuk mengambil mata kuliah sesuai program studi mahasiswa
$query = "SELECT mk.*, d.nama 
          FROM mata_kuliah mk
          JOIN dosen d ON mk.id_dosen = d.id
          WHERE mk.id_prodi = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $mahasiswa['id_prodi']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Tidak ada mata kuliah yang tersedia untuk program studi Anda.";
    exit;
}


// Ubah query untuk mengambil nama dosen
$query = "SELECT mk.*, d.nama 
          FROM mata_kuliah mk
          JOIN dosen d ON mk.id_dosen = d.id";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    echo "Tidak ada mata kuliah yang tersedia.";
    exit;
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mata_kuliah_ids = $_POST['mata_kuliah'] ?? [];
    $mahasiswa_id = $mahasiswa['id'];

    if (!empty($mata_kuliah_ids) && $mahasiswa_id > 0) {
        $success = true;
            // Verifikasi bahwa mata kuliah yang dipilih sesuai dengan prodi mahasiswa
            $verify_query = "SELECT COUNT(*) as count FROM mata_kuliah 
            WHERE id IN (" . str_repeat('?,', count($mata_kuliah_ids) - 1) . "?) 
            AND id_prodi = ?";
            $verify_types = str_repeat('i', count($mata_kuliah_ids)) . 'i';
            $verify_params = array_merge($mata_kuliah_ids, [$mahasiswa['id_prodi']]);

            $stmt = $conn->prepare($verify_query);
            $stmt->bind_param($verify_types, ...$verify_params);
            $stmt->execute();
            $verify_result = $stmt->get_result()->fetch_assoc();

            if ($verify_result['count'] != count($mata_kuliah_ids)) {
                $message = "Terdapat mata kuliah yang tidak sesuai dengan program studi Anda.";
                $message_type = 'danger';
            } else {
            foreach ($mata_kuliah_ids as $mata_kuliah_id) {
                $tahun_sekarang = date('Y');
                $tahun_depan = $tahun_sekarang + 1;
                $tahun_ajaran = "{$tahun_sekarang}/{$tahun_depan}";

                $stmt = $conn->prepare("INSERT INTO krs (id_mahasiswa, id_mata_kuliah, tahun_ajaran) VALUES (?, ?, ?)");
                $stmt->bind_param("iis", $mahasiswa_id, $mata_kuliah_id, $tahun_ajaran);

                if (!$stmt->execute()) {
                    $success = false;
                    $message = "Gagal menyimpan KRS: " . $stmt->error;
                    $message_type = 'danger';
                    break;
                }
            }
            if ($success) {
                $message = "KRS berhasil disimpan!";
                $message_type = 'success';
            }
        }
    } else {
        $message = "Tidak ada mata kuliah yang dipilih atau ID mahasiswa tidak valid.";
        $message_type = 'warning';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Akademik - Pengisian KRS</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            animation: slideIn 0.5s ease, fadeOut 0.5s 2.5s forwards;
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

        .main-content {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-bottom: none;
        }

        .table-hover tbody tr:hover {
            background-color: var(--light-gray);
        }

        .selected-row {
            background-color: #e3f2fd !important;
        }

        .modal-header {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-close {
            filter: brightness(0) invert(1);
        }

        .form-check-input:checked {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
    </style>
</head>

<body>
    <?php if ($message): ?>
        <div id="error_message" class="<?= $message_type === 'success' ? 'bg-success' : ($message_type === 'warning' ? 'bg-warning' : 'bg-danger') ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <!-- Navbar -->
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

    <div class="container-fluid d-flex justify-content-center">
        <div class="col-md-9 col-lg-10 main-content">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Data Mahasiswa</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nama:</strong> <?= $mahasiswa['nama'] ?></p>
                            <p><strong>NIM:</strong> <?= $mahasiswa['nim'] ?></p>
                            <p><strong>Program Studi:</strong> <?= $mahasiswa['nama_prodi'] ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Fakultas:</strong> <?= $mahasiswa['fakultas'] ?></p>
                            <p><strong>Tahun Ajaran:</strong> <?= date('Y') ?>/<?= date('Y')+1 ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Mata Kuliah</h5>
                    <div>
                        <span class="badge bg-primary" id="total-sks">Total SKS: 0</span>
                    </div>
                </div>
                <div class="card-body">
                    <form action="" method="POST" id="krsForm">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="checkAll">
                                            </div>
                                        </th>
                                        <th>Kode MK</th>
                                        <th>Mata Kuliah</th>
                                        <th>SKS</th>
                                        <th>Semester</th>
                                        <th>Dosen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input mk-checkbox" type="checkbox" 
                                                        name="mata_kuliah[]" value="<?= $row['id'] ?>"
                                                        data-sks="<?= $row['sks'] ?>">
                                                </div>
                                            </td>
                                            <td><?= $row['kode_mk'] ?></td>
                                            <td><?= $row['nama_mk'] ?></td>
                                            <td><?= $row['sks'] ?></td>
                                            <td><?= $row['semester'] ?></td>
                                            <td><?= $row['nama'] ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (!$krs_dibuat): ?>
                            <button type="submit" class="btn btn-success mt-3">Simpan KRS</button>                            
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // SKS Calculation
            const checkAll = document.getElementById('checkAll');
            const checkboxes = document.querySelectorAll('.mk-checkbox');
            const totalSksDisplay = document.getElementById('total-sks');

            // Handle Check All
            checkAll.addEventListener('change', function() {
                checkboxes.forEach(checkbox => checkbox.checked = checkAll.checked);
                updateTotalSks();
                updateKrs(); // Update KRS when "Check All" is clicked
            });

            // Handle individual checkbox change
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateTotalSks();
                    updateKrs(); // Update KRS on checkbox change
                });
            });
        });

    </script>
</body>
</html>
