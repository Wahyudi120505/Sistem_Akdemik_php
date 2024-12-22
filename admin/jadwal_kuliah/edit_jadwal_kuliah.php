<?php
session_start();
include '../../config/koneksi.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

if (isset($_GET['id'])) {
    $id_jadwal = $_GET['id'];

    // Query untuk mendapatkan data jadwal kuliah berdasarkan id
    $query_jadwal = "SELECT * FROM jadwal_kuliah WHERE id = '$id_jadwal'";
    $result_jadwal = mysqli_query($conn, $query_jadwal);
    $jadwal = mysqli_fetch_assoc($result_jadwal);
} else {
    header("Location: jadwal_matkul.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $mata_kuliah = $_POST['mata_kuliah'];
    $dosen = $_POST['dosen'];
    $hari = $_POST['hari'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    $ruangan = $_POST['ruangan'];

    // Cek apakah sudah ada jadwal kuliah yang sama
    $query_check = "SELECT * FROM jadwal_kuliah 
                    WHERE hari = '$hari' 
                    AND id != '$id_jadwal' 
                    AND (
                        (jam_mulai BETWEEN '$jam_mulai' AND '$jam_selesai') 
                        OR (jam_selesai BETWEEN '$jam_mulai' AND '$jam_selesai') 
                        OR ('$jam_mulai' BETWEEN jam_mulai AND jam_selesai) 
                        OR ('$jam_selesai' BETWEEN jam_mulai AND jam_selesai)
                    )
                    AND ruangan = '$ruangan'";

    $result_check = mysqli_query($conn, $query_check);

    if (mysqli_num_rows($result_check) > 0) {
        // Jika ada tabrakan jadwal
        echo "Jadwal kuliah ini sudah ada pada waktu dan ruangan yang dipilih.";
    } else {
        // Query untuk update data jadwal kuliah
        $query = "UPDATE jadwal_kuliah 
                  SET id_mata_kuliah = '$mata_kuliah', id_dosen = '$dosen', hari = '$hari', 
                      jam_mulai = '$jam_mulai', jam_selesai = '$jam_selesai', ruangan = '$ruangan'
                  WHERE id = '$id_jadwal'";

        if (mysqli_query($conn, $query)) {
            // Jika berhasil, redirect ke halaman jadwal_matkul.php
            header("Location: jadwal_kuliah.php");
            exit;
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}

// Query untuk mendapatkan data mata kuliah
$query_matkul = "SELECT id, nama_mk FROM mata_kuliah";
$result_matkul = mysqli_query($conn, $query_matkul);

// Query untuk mendapatkan data dosen
$query_dosen = "SELECT id, nama FROM dosen";
$result_dosen = mysqli_query($conn, $query_dosen);

// Query untuk mendapatkan data ruangan
$query_ruangan = "SELECT id, nama_ruangan FROM ruangan";
$result_ruangan = mysqli_query($conn, $query_ruangan);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jadwal Kuliah</title>
    <!-- Link ke Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Edit Jadwal Kuliah</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="mata_kuliah" class="form-label">Mata Kuliah</label>
                <select id="mata_kuliah" name="mata_kuliah" class="form-select" required>
                    <option value="">Pilih Mata Kuliah</option>
                    <?php while ($row = mysqli_fetch_assoc($result_matkul)): ?>
                        <option value="<?= $row['id']; ?>" <?= $jadwal['id_mata_kuliah'] == $row['id'] ? 'selected' : ''; ?>><?= $row['nama_mk']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="dosen" class="form-label">Dosen Pengajar</label>
                <select id="dosen" name="dosen" class="form-select" required>
                    <option value="">Pilih Dosen</option>
                    <?php while ($row = mysqli_fetch_assoc($result_dosen)): ?>
                        <option value="<?= $row['id']; ?>" <?= $jadwal['id_dosen'] == $row['id'] ? 'selected' : ''; ?>><?= $row['nama']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="hari" class="form-label">Hari</label>
                <select id="hari" name="hari" class="form-select" required>
                    <option value="">Pilih Hari</option>
                    <option value="Senin" <?= $jadwal['hari'] == 'Senin' ? 'selected' : ''; ?>>Senin</option>
                    <option value="Selasa" <?= $jadwal['hari'] == 'Selasa' ? 'selected' : ''; ?>>Selasa</option>
                    <option value="Rabu" <?= $jadwal['hari'] == 'Rabu' ? 'selected' : ''; ?>>Rabu</option>
                    <option value="Kamis" <?= $jadwal['hari'] == 'Kamis' ? 'selected' : ''; ?>>Kamis</option>
                    <option value="Jumat" <?= $jadwal['hari'] == 'Jumat' ? 'selected' : ''; ?>>Jumat</option>
                    <option value="Sabtu" <?= $jadwal['hari'] == 'Sabtu' ? 'selected' : ''; ?>>Sabtu</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="jam_mulai" class="form-label">Jam Mulai</label>
                <input type="time" id="jam_mulai" name="jam_mulai" class="form-control" value="<?= $jadwal['jam_mulai']; ?>" required>
            </div>

            <div class="mb-3">
                <label for="jam_selesai" class="form-label">Jam Selesai</label>
                <input type="time" id="jam_selesai" name="jam_selesai" class="form-control" value="<?= $jadwal['jam_selesai']; ?>" required>
            </div>

            <div class="mb-3">
                <label for="ruangan" class="form-label">Ruangan</label>
                <select id="ruangan" name="ruangan" class="form-select" required>
                    <option value="">Pilih Ruangan</option>
                    <?php while ($row = mysqli_fetch_assoc($result_ruangan)): ?>
                        <option value="<?= $row['id']; ?>" <?= $jadwal['id'] == $row['id'] ? 'selected' : ''; ?>><?= $row['nama_ruangan']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </div>

    <!-- Link ke Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
