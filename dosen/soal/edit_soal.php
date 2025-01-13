<?php
session_start();
include('../../config/koneksi.php');

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
} else {
    echo "Data dosen tidak ditemukan.";
    exit;
}

// Ambil ID soal dari parameter URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $soal_id = $_GET['id'];
    $query_soal = "SELECT * FROM bank_soal bs JOIN soal s ON bs.id = s.id_bank_soal WHERE s.id = '$soal_id'";
    $result_soal = mysqli_query($conn, $query_soal);
    
    if ($result_soal && mysqli_num_rows($result_soal) > 0) {
        $soal = mysqli_fetch_assoc($result_soal);
    } else {
        echo "Soal tidak ditemukan.";
        exit;
    }
} else {
    echo "ID soal tidak valid.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $file_soal = $_FILES['file_soal']['name'];
    $lokasi = $_FILES['file_soal']['tmp_name'];
    $id_mata_kuliah = $_POST['id_mata_kuliah'];
    $id_jenis_soal = $_POST['id_jenis_soal'];
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    
    // Jika file soal baru diupload
    if (!empty($file_soal)) {
        move_uploaded_file($lokasi, "file_soal/$file_soal");
        
        $file_path = "file_soal/" . $soal['file_soal'];
        if (file_exists($file_path)) {
            unlink($file_path); // Hapus file dari folder
        }

    } else {
        $file_soal = $soal['pertanyaan']; // Tetap menggunakan file soal lama
    }

    date_default_timezone_set('Asia/Jakarta');
    $batas_hari = isset($_POST['batas_hari']) && $_POST['batas_hari'] !== '' ? $_POST['batas_hari'] : date('Y-m-d');
    $batas_jam = $_POST['batas_jam'];
    $batas_waktu = $batas_hari . ' ' . $batas_jam;
    
    switch ($id_jenis_soal) {
        case 1:
            $bobot_nilai = 10;
            break;
        case 2:
            $bobot_nilai = 20;
            break;
        case 3:
            $bobot_nilai = 30;
            break;
        case 4:
            $bobot_nilai = 40;
            break;
        default:
            $bobot_nilai = 0;
            break;
    }
   
    $query_bank_soal = "UPDATE bank_soal 
                    SET id_dosen = '$dosen_id', id_mata_kuliah = '$id_mata_kuliah', id_jenis_soal = '$id_jenis_soal', 
                        judul = '$judul', deskripsi = '$deskripsi', batas_waktu = '$batas_waktu', file_soal = '$file_soal' 
                    WHERE id = '$soal[id_bank_soal]'";
    $result_bank_soal = mysqli_query($conn, $query_bank_soal);

if ($result_bank_soal) {
    $query_soal_update = "UPDATE soal 
                          SET pertanyaan = '$deskripsi', bobot_nilai = '$bobot_nilai' 
                          WHERE id = '$soal_id'";
    $result_soal_update = mysqli_query($conn, $query_soal_update);

    // Ambil batas waktu lama
    $old_batas_waktu = $soal['batas_waktu'];

    // Bandingkan batas waktu lama dengan batas waktu baru
    if (strtotime($batas_waktu) > strtotime($old_batas_waktu)) {
        // Jika batas waktu baru lebih lama, perbarui status menjadi aktif
        $update_status_query = "UPDATE bank_soal SET STATUS = 'aktif' WHERE id = '$soal[id_bank_soal]'";
        mysqli_query($conn, $update_status_query);
    }


    if ($result_soal_update) {
        header("Location: soal.php");
    } else {
        $error_message = "Gagal mengupdate soal.";
    }
} else {
    $error_message = "Gagal mengupdate data soal.";
}
}
$query_mata_kuliah = "SELECT * FROM mata_kuliah WHERE id_dosen = '$dosen_id'";
$result_mata_kuliah = mysqli_query($conn, $query_mata_kuliah);
$query_jenis_soal = "SELECT * FROM jenis_soal";
$result_jenis_soal = mysqli_query($conn, $query_jenis_soal);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Soal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Styling yang sama seperti halaman tambah soal */
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
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

    <?php if (!empty($error_message)): ?>
        <div id="error_message"><?= $error_message ?></div>
    <?php endif; ?>

    <div class="container mt-4">
        <h1 class="mb-4">Edit Soal</h1>
        <form method="POST" action="edit_soal.php?id=<?php echo $soal_id; ?>" enctype="multipart/form-data" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label for="id_mata_kuliah" class="form-label">Mata Kuliah</label>
                <select class="form-select" name="id_mata_kuliah" id="id_mata_kuliah" required>
                    <?php while ($mata_kuliah = mysqli_fetch_assoc($result_mata_kuliah)): ?>
                        <option value="<?php echo $mata_kuliah['id']; ?>" <?php echo $mata_kuliah['id'] == $soal['id_mata_kuliah'] ? 'selected' : ''; ?>>
                            <?php echo $mata_kuliah['nama_mk']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="id_jenis_soal" class="form-label">Jenis Soal</label>
                <select class="form-select" name="id_jenis_soal" id="id_jenis_soal" required>
                    <?php while ($jenis_soal = mysqli_fetch_assoc($result_jenis_soal)): ?>
                        <option value="<?php echo $jenis_soal['id']; ?>" <?php echo $jenis_soal['id'] == $soal['id_jenis_soal'] ? 'selected' : ''; ?>>
                            <?php echo $jenis_soal['nama']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="judul" class="form-label">Judul Soal</label>
                <input type="text" class="form-control" name="judul" id="judul" value="<?php echo $soal['judul']; ?>" required>
            </div>

            <div class="mb-3">
                <label for="deskripsi" class="form-label">Deskripsi Soal</label>
                <textarea class="form-control" name="deskripsi" id="deskripsi" rows="3" required><?php echo $soal['deskripsi']; ?></textarea>
            </div>

            <div class="mb-3">
                <label for="batas_hari" class="form-label">Batas Hari</label>
                <input type="date" class="form-control" name="batas_hari" id="batas_hari" value="<?php echo date('Y-m-d', strtotime($soal['batas_waktu'])); ?>">
            </div>

            <div class="mb-3">
                <label for="batas_jam" class="form-label">Batas Waktu</label>
                <input type="time" class="form-control" name="batas_jam" id="batas_jam" value="<?php echo date('H:i', strtotime($soal['batas_waktu'])); ?>" required>
            </div>

            <div class="mb-3">
                <label for="file_soal" class="form-label">Upload File Soal</label>
                <input type="file" class="form-control" name="file_soal" id="file_soal" accept="*/*">
                <small class="form-text text-muted">Biarkan kosong jika tidak ingin mengganti file soal.</small>
            </div>

            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Update Soal</button>
                <a href="soal.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</body>
</html>