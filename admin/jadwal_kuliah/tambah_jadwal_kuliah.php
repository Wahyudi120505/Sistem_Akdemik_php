<?php
session_start();
include '../../config/koneksi.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$error_message = ""; // Variabel untuk menyimpan pesan kesalahan

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
                    WHERE hari = ? 
                    AND (
                        (jam_mulai BETWEEN ? AND ?) 
                        OR (jam_selesai BETWEEN ? AND ?) 
                        OR (? BETWEEN jam_mulai AND jam_selesai)
                    )
                    AND ruangan = ?";

    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bind_param("sssssss", $hari, $jam_mulai, $jam_selesai, $jam_mulai, $jam_selesai, $jam_mulai, $ruangan);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Jika ada tabrakan jadwal
        $error_message = "Jadwal kuliah ini sudah ada pada waktu dan ruangan yang dipilih.";
    } else {
        // Query untuk menyimpan data jadwal kuliah
        $query = "INSERT INTO jadwal_kuliah (id_mata_kuliah, id_dosen, hari, jam_mulai, jam_selesai, ruangan)
                  VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("iissss", $mata_kuliah, $dosen, $hari, $jam_mulai, $jam_selesai, $ruangan);

        if ($stmt->execute()) {
            // Jika berhasil, redirect ke halaman jadwal_kuliah.php
            header("Location: jadwal_kuliah.php");
            exit;
        } else {
            $error_message = "Error: " . $stmt->error;
        }
    }
}

// Query untuk mendapatkan data mata kuliah
$query_matkul = "SELECT id, nama_mk, sks FROM mata_kuliah";
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
    <title>Tambah Jadwal Kuliah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Tambah Jadwal Kuliah</h2>
        
        <!-- Tampilkan pesan kesalahan jika ada -->
        <?php if ($error_message): ?>
            <div class="alert alert-danger"> <?= $error_message; ?> </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="mata_kuliah" class="form-label">Mata Kuliah</label>
                <select id="mata_kuliah" name="mata_kuliah" class="form-select" required>
                    <option value="">Pilih Mata Kuliah</option>
                    <?php while ($row = mysqli_fetch_assoc($result_matkul)): ?>
                        <option value="<?= $row['id']; ?>" data-sks="<?= $row['sks']; ?>">
                            <?= $row['nama_mk']; ?> (<?= $row['sks']; ?> SKS)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="dosen" class="form-label">Dosen Pengajar</label>
                <select id="dosen" name="dosen" class="form-select" required>
                    <option value="">Pilih Dosen</option>
                    <?php while ($row = mysqli_fetch_assoc($result_dosen)): ?>
                        <option value="<?= $row['id']; ?>"> <?= $row['nama']; ?> </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="hari" class="form-label">Hari</label>
                <select id="hari" name="hari" class="form-select" required>
                    <option value="">Pilih Hari</option>
                    <option value="Senin">Senin</option>
                    <option value="Selasa">Selasa</option>
                    <option value="Rabu">Rabu</option>
                    <option value="Kamis">Kamis</option>
                    <option value="Jumat">Jumat</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="jam_mulai" class="form-label">Jam Mulai</label>
                <input type="time" id="jam_mulai" name="jam_mulai" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="jam_selesai" class="form-label">Jam Selesai</label>
                <input type="time" id="jam_selesai" name="jam_selesai" class="form-control" readonly>
            </div>

            <div class="mb-3">
                <label for="ruangan" class="form-label">Ruangan</label>
                <select id="ruangan" name="ruangan" class="form-select" required>
                    <option value="">Pilih Ruangan</option>
                    <?php while ($row = mysqli_fetch_assoc($result_ruangan)): ?>
                        <option value="<?= $row['id']; ?>"> <?= $row['nama_ruangan']; ?> </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </div>

    <script>
        document.getElementById('jam_mulai').addEventListener('input', hitungJamSelesai);
        document.getElementById('mata_kuliah').addEventListener('change', hitungJamSelesai);

        function hitungJamSelesai() {
            let jamMulai = document.getElementById('jam_mulai').value;
            let sks = document.querySelector('#mata_kuliah option:checked').dataset.sks;
            if (!sks || !jamMulai) return;
            
            let jam = parseInt(jamMulai.split(':')[0]);
            let menit = parseInt(jamMulai.split(':')[1]);
            let totalMenit = sks * 50;
            
            let jamSelesai = new Date();
            jamSelesai.setHours(jam);
            jamSelesai.setMinutes(menit + totalMenit);
            
            let hasilJam = jamSelesai.getHours().toString().padStart(2, '0');
            let hasilMenit = jamSelesai.getMinutes().toString().padStart(2, '0');
            
            document.getElementById('jam_selesai').value = `${hasilJam}:${hasilMenit}`;
        }
    </script>
</body>
</html>
