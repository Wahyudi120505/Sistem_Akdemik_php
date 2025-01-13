<?php
session_start();
include('../../config/koneksi.php');

// Cek apakah user sudah login dan memiliki role sebagai mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Updated query to include tanggal_lahir and password
$query = "SELECT m.*, ps.nama_prodi, ps.fakultas, u.password 
          FROM mahasiswa m 
          JOIN program_studi ps ON m.id_prodi = ps.id 
          JOIN users u ON m.user_id = u.id
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

// Create masked password
$maskedPassword = str_repeat('*', strlen($mahasiswa['password'])/7);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Previous styles remain unchanged */
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

        .data-table {
            width: 100%;
        }

        .data-table th {
            background-color: var(--light-gray);
            color: var(--primary-color);
            font-weight: 600;
            padding: 1rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
        }

        .data-table td {
            padding: 1rem;
            color: var(--primary-color);
            font-weight: 500;
            border-top: 1px solid #eee;
        }

        .data-table tr:hover {
            background-color: var(--light-gray);
        }

        .label-column {
            background-color: var(--light-gray);
            font-weight: 600;
            width: 200px;
        }

        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            padding: 0.5rem 1.5rem;
            font-weight: 500;
        }

        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
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

            .data-table td,
            .data-table th {
                padding: 0.75rem;
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
                <i class="fas fa-user-circle fa-2x text-primary"></i>
                <h1>Profil Mahasiswa</h1>
            </div>
        </div>

        <div class="content-card">
            <div class="table-responsive">
                <table class="data-table table">
                    <tbody>
                        <tr>
                            <td class="label-column">NIM</td>
                            <td><?php echo htmlspecialchars($mahasiswa['nim']); ?></td>
                        </tr>
                        <tr>
                            <td class="label-column">Nama Lengkap</td>
                            <td><?php echo htmlspecialchars($mahasiswa['nama']); ?></td>
                        </tr>
                        <tr>
                            <td class="label-column">Tanggal Lahir</td>
                            <td><?php echo htmlspecialchars($mahasiswa['tanggal_lahir']); ?></td>
                        </tr>
                        <tr>
                            <td class="label-column">Program Studi</td>
                            <td><?php echo htmlspecialchars($mahasiswa['nama_prodi']); ?></td>
                        </tr>
                        <tr>
                            <td class="label-column">Fakultas</td>
                            <td><?php echo htmlspecialchars($mahasiswa['fakultas']); ?></td>
                        </tr>
                        <tr>
                            <td class="label-column">Angkatan</td>
                            <td><?php echo htmlspecialchars($mahasiswa['angkatan']); ?></td>
                        </tr>
                        <tr>
                            <td class="label-column">Email</td>
                            <td><?php echo htmlspecialchars($mahasiswa['email']); ?></td>
                        </tr>
                        <tr>
                            <td class="label-column">No. Telepon</td>
                            <td><?php echo htmlspecialchars($mahasiswa['no_telepon']); ?></td>
                        </tr>
                        <tr>
                            <td class="label-column">Alamat</td>
                            <td><?php echo htmlspecialchars($mahasiswa['alamat']); ?></td>
                        </tr>
                        <tr>
                            <td class="label-column">Password</td>
                            <td><?php echo $maskedPassword; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="text-end mt-4">
                <a href="edit_profile.php?nim=<?php echo htmlspecialchars($mahasiswa['nim']); ?>" class="btn btn-primary">
                    <i class="fas fa-edit me-2"></i>Edit Profil
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>