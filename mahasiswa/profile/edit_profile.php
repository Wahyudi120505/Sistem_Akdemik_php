<?php
session_start();
include('../../config/koneksi.php');

// Cek apakah user sudah login dan memiliki role sebagai mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error_msg = "";
$success_msg = "";

// Fetch current data
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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $no_telepon = trim($_POST['no_telepon']);
    $alamat = trim($_POST['alamat']);
    $tanggal_lahir = trim($_POST['tanggal_lahir']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validation
    if (empty($nama) || empty($email) || empty($no_telepon) || empty($alamat) || empty($tanggal_lahir)) {
        $error_msg = "Semua field wajib diisi kecuali password!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Format email tidak valid!";
    } else {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Update mahasiswa table
            $update_query = "UPDATE mahasiswa SET 
                           nama = ?, 
                           email = ?, 
                           no_telepon = ?, 
                           alamat = ?,
                           tanggal_lahir = ?
                           WHERE user_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("sssssi", $nama, $email, $no_telepon, $alamat, $tanggal_lahir, $user_id);
            $stmt->execute();

            // Update password if provided
            if (!empty($password)) {
                if ($password !== $confirm_password) {
                    throw new Exception("Password dan konfirmasi password tidak cocok!");
                }
                
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $update_password = "UPDATE users SET password = ? WHERE id = ?";
                $stmt = $conn->prepare($update_password);
                $stmt->bind_param("si", $hashed_password, $user_id);
                $stmt->execute();
            }

            $conn->commit();
            $success_msg = "Profil berhasil diperbarui!";
            
            // Refresh mahasiswa data
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $mahasiswa = $result->fetch_assoc();

            header("Location: profile.php");
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $error_msg = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil Mahasiswa</title>
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

        .page-header {
            background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .content-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--primary-color);
        }

        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }

        .readonly-field {
            background-color: var(--light-gray);
            cursor: not-allowed;
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
                <i class="fas fa-user-edit fa-2x text-primary"></i>
                <h1>Edit Profil Mahasiswa</h1>
            </div>
        </div>

        <?php if ($error_msg): ?>
        <div class="alert alert-danger" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo htmlspecialchars($error_msg); ?>
        </div>
        <?php endif; ?>

        <?php if ($success_msg): ?>
        <div class="alert alert-success" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo htmlspecialchars($success_msg); ?>
        </div>
        <?php endif; ?>

        <div class="content-card">
            <form method="POST" action="">
                <!-- Data yang tidak bisa diedit -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">NIM</label>
                        <input type="text" class="form-control readonly-field" value="<?php echo htmlspecialchars($mahasiswa['nim']); ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Program Studi</label>
                        <input type="text" class="form-control readonly-field" value="<?php echo htmlspecialchars($mahasiswa['nama_prodi']); ?>" readonly>
                    </div>
                </div>

                <!-- Data yang bisa diedit -->
                <div class="mb-3">
                    <label class="form-label">Nama Lengkap *</label>
                    <input type="text" class="form-control" name="nama" value="<?php echo htmlspecialchars($mahasiswa['nama']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal Lahir *</label>
                    <input type="date" class="form-control" name="tanggal_lahir" value="<?php echo htmlspecialchars($mahasiswa['tanggal_lahir']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email *</label>
                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($mahasiswa['email']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">No. Telepon *</label>
                    <input type="tel" class="form-control" name="no_telepon" value="<?php echo htmlspecialchars($mahasiswa['no_telepon']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Alamat *</label>
                    <textarea class="form-control" name="alamat" rows="3" required><?php echo htmlspecialchars($mahasiswa['alamat']); ?></textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Password Baru (Kosongkan jika tidak ingin mengubah)</label>
                        <input type="password" class="form-control" name="password">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" name="confirm_password">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="profile.php" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>