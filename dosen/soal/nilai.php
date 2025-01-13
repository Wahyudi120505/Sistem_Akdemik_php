<?php
session_start();
include('../../config/koneksi.php');

// Cek apakah user sudah login dan memiliki peran 'dosen'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dosen') {
    header("Location: ../index.php");
    exit;
}

$done_id = isset($_GET['id']) ? $_GET['id'] : '';
if (empty($done_id)) {
    echo "ID jawaban tidak ditemukan.";
    exit;
}

// Ambil data dari tabel done_tugas berdasarkan ID
$query_done = "SELECT done_tugas.*, mahasiswa.nama AS mahasiswa_nama, 
                        mahasiswa.semester AS semester, 
                        bank_soal.id AS soal_id, 
                        mata_kuliah.id AS mata_kuliah_id,
                        mata_kuliah.sks AS mata_kuliah_sks,
                        jenis_soal.nama AS jenis_soal_nama
               FROM done_tugas 
               JOIN mahasiswa ON done_tugas.id_mahasiswa = mahasiswa.id 
               JOIN bank_soal ON done_tugas.id_bank_soal = bank_soal.id
               JOIN jenis_soal ON bank_soal.id_jenis_soal = jenis_soal.id
               JOIN mata_kuliah ON bank_soal.id_mata_kuliah = mata_kuliah.id
               WHERE done_tugas.id = '$done_id'";
$result_done = mysqli_query($conn, $query_done);

if ($result_done && mysqli_num_rows($result_done) > 0) {
    $jawaban = mysqli_fetch_assoc($result_done);
} else {
    echo "Data jawaban tidak ditemukan.";
    exit;
}

// Proses penilaian
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nilai = mysqli_real_escape_string($conn, $_POST['nilai']);
    
    $query_update = "UPDATE done_tugas SET nilai = '$nilai' WHERE id = '$done_id'";
    if (mysqli_query($conn, $query_update)) {
        // Cek apakah data KHS sudah ada
        $id_mahasiswa = $jawaban['id_mahasiswa'];
        $id_mata_kuliah = $jawaban['mata_kuliah_id'];
        $semester = $jawaban['semester'];

        $query_khs_all = "SELECT * FROM khs WHERE id_mahasiswa = '$id_mahasiswa'";
        $result_khs = mysqli_query($conn, $query_khs_all);
        $khs = mysqli_fetch_assoc($result_khs);

        $tugas = $khs['tugas'] ?? 0;
        $kuis = $khs['kuis'] ?? 0;
        $uts = $khs['uts'] ?? 0;
        $uas = $khs['uas'] ?? 0;

        if ($jawaban['jenis_soal_nama'] === 'Tugas') $tugas += $nilai;
        if ($jawaban['jenis_soal_nama'] === 'Kuis') $kuis += $nilai;
        if ($jawaban['jenis_soal_nama'] === 'Uts') $uts += $nilai;
        if ($jawaban['jenis_soal_nama'] === 'Uas') $uas += $nilai;

        $query_total_sks = "SELECT SUM(mk.sks) AS total_sks
                                FROM krs k
                                JOIN mata_kuliah mk ON k.id_mata_kuliah = mk.id
                                WHERE k.id_mahasiswa = $id_mahasiswa"; 
        $result_total_sks = mysqli_query($conn, $query_total_sks);

        if ($result_total_sks && mysqli_num_rows($result_total_sks) > 0) {
            $row_total_sks = mysqli_fetch_assoc($result_total_sks);
            $total_sks = $row_total_sks['total_sks']; 
        } else {
            $total_sks = 0; 
        }

        $nilai_total_matkul = ($uts * 0.3) + ($kuis *= 0.2) + ($uas * 0.4) + ($tugas *= 0.1) ;
        $grade = ($nilai_total_matkul >= 85) ? 'A' : (($nilai_total_matkul >= 70) ? 'B' : (($nilai_total_matkul >= 60) ? 'C' : (($nilai_total_matkul >= 50) ? 'D' : 'E')));

        switch($grade){
            case 'A':
                $bobot = 4;
            break;
            case 'B':
                $bobot = 3;
            break;
            case 'C':
                $bobot = 2;
            break;
            case 'D':
                $bobot = 1;
            break;
            case 'E':
                $bobot = 0;
            break;
        }

        // Menghitung nilai akhir
        if ($total_sks > 0) {
            $nilai_akhir = ($bobot * $jawaban['mata_kuliah_sks']) / $total_sks;
        } else {
            $nilai_akhir = 100; // Jika total_sks 0, nilai akhir menjadi 0
        }

        if (mysqli_num_rows($result_khs) > 0) {
            $query_update_khs = "UPDATE khs SET 
                tugas = '$tugas', 
                kuis = '$kuis', 
                uts = '$uts', 
                uas = '$uas', 
                nilai_angka = '$nilai_akhir', 
                nilai_huruf = '$grade'
                WHERE id_mahasiswa = '$id_mahasiswa'";
            mysqli_query($conn, $query_update_khs);
        } else {
            $query_khs = "INSERT INTO khs (id_mahasiswa, id_mata_kuliah, nilai_angka, nilai_huruf, semester, tugas, kuis, uts, uas) 
                VALUES ('$id_mahasiswa', '$id_mata_kuliah', '$nilai_akhir', '$grade', '$semester', '$tugas', '$kuis', '$uts', '$uas')";
            mysqli_query($conn, $query_khs);
        }
        echo "<script>alert('Nilai berhasil disimpan dan tabel KHS diperbarui!'); window.location.href='cek.php?id=" . $jawaban['soal_id'] . "';</script>";
    } else {
        echo "Gagal menyimpan nilai: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nilai Jawaban</title>
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

        .info-card a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .info-card a:hover {
            color: #2980b9;
        }

        .form-container {
            background: var(--light-gray);
            padding: 2rem;
            border-radius: 10px;
            margin-top: 1.5rem;
        }

        .form-label {
            color: var(--primary-color);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 0.75rem;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .btn-primary {
            background-color: var(--secondary-color);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 500;
            transition: background-color 0.2s;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 1.5rem;
            }

            .content-card {
                padding: 1rem;
            }

            .form-container {
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
                <i class="fas fa-star fa-2x text-primary"></i>
                <h1>Penilaian Jawaban</h1>
            </div>
        </div>

        <div class="content-card">
            <div class="info-grid">
                <div class="info-card">
                    <h3>Nama Mahasiswa</h3>
                    <p><?php echo htmlspecialchars($jawaban['mahasiswa_nama']); ?></p>
                </div>
                <div class="info-card">
                    <h3>Jenis Soal</h3>
                    <p><?php echo htmlspecialchars($jawaban['jenis_soal_nama']); ?></p>
                </div>
                <div class="info-card">
                    <h3>File Jawaban</h3>
                    <?php
                    $file_jawaban = $jawaban['file_jawaban'];
                    $file_path = "../../mahasiswa/soal/uploads_jawaban/" . $file_jawaban;
                    if (file_exists($file_path)) {
                        echo '<a href="' . $file_path . '" target="_blank">
                                <i class="fas fa-file-alt me-2"></i>
                                Lihat File Jawaban
                            </a>';
                    } else {
                        echo '<p class="text-danger mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                File jawaban tidak ditemukan!
                            </p>';
                    }
                    ?>
                </div>
            </div>

            <div class="form-container">
                <form method="POST">
                    <div class="mb-4">
                        <label for="nilai" class="form-label">Nilai</label>
                        <input type="number" 
                               class="form-control" 
                               id="nilai" 
                               name="nilai" 
                               value="<?php echo $jawaban['nilai'] ?? ''; ?>" 
                               min="0" 
                               max="100" 
                               required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        Simpan Nilai
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>