<?php
session_start();
include('../../config/koneksi.php');

// Cek jika user sudah login dan memiliki peran 'mahasiswa'
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

// Pastikan id_bank_soal ada di URL
if (isset($_GET['id_bank_soal'])) {
    $id_bank_soal = $_GET['id_bank_soal'];

    // Ambil data soal berdasarkan id_bank_soal
    $query_soal = "SELECT 
        bank_soal.id AS id_bank_soal, 
        bank_soal.judul AS bank_soal_judul,
        bank_soal.deskripsi AS bank_soal_deskripsi,
        bank_soal.tanggal_dibuat AS bank_soal_tanggal_dibuat,
        bank_soal.batas_waktu AS bank_soal_batas_waktu,
        mata_kuliah.nama_mk AS mata_kuliah_nama,
        jenis_soal.nama AS jenis_soal_nama,
        bank_soal.file_soal AS file_soal
    FROM 
        bank_soal
    JOIN mata_kuliah ON bank_soal.id_mata_kuliah = mata_kuliah.id
    JOIN jenis_soal ON bank_soal.id_jenis_soal = jenis_soal.id
    WHERE bank_soal.id = ?";

    $stmt_soal = $conn->prepare($query_soal);
    $stmt_soal->bind_param("i", $id_bank_soal);
    $stmt_soal->execute();
    $result_soal = $stmt_soal->get_result();
    $soal = $result_soal->fetch_assoc();

    if (!$soal) {
        echo "Soal tidak ditemukan!";
        exit;
    }
} else {
    echo "ID soal tidak ditemukan!";
    exit;
}

$message = '';
$message_type = '';

// Cek jika form disubmit dan file diupload
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Periksa apakah file jawaban ada
    if (isset($_FILES['file_jawaban']) && $_FILES['file_jawaban']['error'] == 0) {
        $file_jawaban = $_FILES['file_jawaban']['name'];
        $file_tmp = $_FILES['file_jawaban']['tmp_name'];

        // Pindahkan file yang diunggah ke direktori tujuan
        if (move_uploaded_file($file_tmp, "uploads_jawaban/$file_jawaban")) {
            // Masukkan data jawaban ke tabel done_tugas
            $query = "INSERT INTO done_tugas (nilai, file_jawaban, STATUS, id_bank_soal, id_mahasiswa) 
                VALUES (0, ?, 'selesai', ?, ?)";  
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sii", $file_jawaban, $id_bank_soal, $mahasiswa['id']);

            if ($stmt->execute()) {
                $message = "Jawaban berhasil dikirim!";
                $message_type = "success";
            } else {
                $message = "Terjadi kesalahan saat menyimpan jawaban.";
                $message_type = "danger";
            }
        } else {
            $message = "Gagal mengunggah file.";
            $message_type = "danger";
        }
    } else {
        $message = "File jawaban tidak ditemukan atau terjadi kesalahan saat mengunggah!";
        $message_type = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kerjakan Soal</title>
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

        .soal-section {
            background: var(--light-gray);
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .soal-section h2 {
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .description-box {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .file-download {
            display: inline-block;
            background: var(--secondary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            margin-bottom: 1.5rem;
            transition: background-color 0.2s;
        }

        .file-download:hover {
            background-color: #2980b9;
            color: white;
        }

        .file-upload-form {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .custom-file-upload {
            border: 2px dashed #ddd;
            padding: 2rem;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            cursor: pointer;
        }

        .custom-file-upload i {
            font-size: 2rem;
            color: var(--secondary-color);
            margin-bottom: 1rem;
        }

        .btn-submit {
            background-color: var(--success-color);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 500;
            transition: background-color 0.2s;
        }

        .btn-submit:hover {
            background-color: #27ae60;
        }

        .alert {
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 1.5rem;
            }

            .content-card {
                padding: 1rem;
            }

            .soal-section {
                padding: 1.5rem;
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
                <i class="fas fa-edit fa-2x text-primary"></i>
                <h1>Kerjakan Soal</h1>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <i class="fas <?php echo $message_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> me-2"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="content-card">
            <div class="info-grid">
                <div class="info-card">
                    <h3>Mata Kuliah</h3>
                    <p><?php echo htmlspecialchars($soal['mata_kuliah_nama']); ?></p>
                </div>
                <div class="info-card">
                    <h3>Jenis Soal</h3>
                    <p><?php echo htmlspecialchars($soal['jenis_soal_nama']); ?></p>
                </div>
                <div class="info-card">
                    <h3>Batas Waktu</h3>
                    <p><?php echo date('d M Y H:i', strtotime($soal['bank_soal_batas_waktu'])); ?></p>
                </div>
            </div>

            <div class="soal-section">
                <h2><?php echo htmlspecialchars($soal['bank_soal_judul']); ?></h2>
                
                <div class="description-box">
                    <h3 class="h5 mb-3">Deskripsi Soal:</h3>
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($soal['bank_soal_deskripsi'])); ?></p>
                </div>

                <?php
                $file_soal = $soal['file_soal'];
                $file_path = "../../dosen/soal/file_soal/" . $file_soal;
                if (file_exists($file_path)):
                ?>
                    <a href="<?php echo $file_path; ?>" class="file-download" target="_blank">
                        <i class="fas fa-download me-2"></i>
                        Download File Soal
                    </a>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        File soal tidak ditemukan!
                    </div>
                <?php endif; ?>
            </div>

            <div class="file-upload-form">
                <h3 class="h4 mb-4">Upload Jawaban</h3>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="custom-file-upload" id="dropZone">
                        <i class="fas fa-cloud-upload-alt d-block mb-3"></i>
                        <input type="file" name="file_jawaban" id="file_jawaban" class="d-none" required>
                        <label for="file_jawaban" class="mb-0">
                            Klik atau seret file jawaban Anda ke sini
                        </label>
                        <p class="selected-file mt-2 mb-0" style="display: none;"></p>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-submit">
                            <i class="fas fa-paper-plane me-2"></i>
                            Kirim Jawaban
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // File upload preview
        const fileInput = document.getElementById('file_jawaban');
        const dropZone = document.getElementById('dropZone');
        const selectedFile = document.querySelector('.selected-file');

        fileInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                selectedFile.textContent = `File terpilih: ${this.files[0].name}`;
                selectedFile.style.display = 'block';
            }
        });

        dropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            dropZone.style.borderColor = '#ddd';
        });

        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            dropZone.style.borderColor = '#ddd';
            
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                fileInput.files = e.dataTransfer.files;
                selectedFile.textContent = `File terpilih: ${e.dataTransfer.files[0].name}`;
                selectedFile.style.display = 'block';
            }
        });
    </script>
</body>
</html>
