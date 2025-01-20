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

// Ambil total SKS dari KRS
$query_total_sks = "SELECT SUM(mk.sks) AS total_sks
                    FROM krs k
                    JOIN mata_kuliah mk ON k.id_mata_kuliah = mk.id
                    WHERE k.id_mahasiswa = ?";
$stmt_total_sks = $conn->prepare($query_total_sks);
$stmt_total_sks->bind_param("i", $mahasiswa['id']);
$stmt_total_sks->execute();
$result_total_sks = $stmt_total_sks->get_result();
$row_total_sks = $result_total_sks->fetch_assoc();
$total_sks = $row_total_sks['total_sks'] ?: 0;

// Hitung bobot dan nilai akhir
$total_bobot_sks = 0;
$total_sks_hitung = 0;

while ($row = $result_nilai->fetch_assoc()) {
    switch ($row['nilai_huruf']) {
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
        default:
            $bobot = 0;
            break;
    }

    // Tambahkan nilai bobot * SKS untuk setiap mata kuliah
    $total_bobot_sks += $bobot * $row['sks'];
    $total_sks_hitung += $row['sks'];
}

// Menghitung nilai akhir
if ($total_sks_hitung > 0) {
    $nilai_akhir = $total_bobot_sks / $total_sks_hitung;
} else {
    $nilai_akhir = 0;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transkrip Nilai</title>
</head>
<body>
    <h1>Transkrip Nilai</h1>
    <p>Nama: <?= htmlspecialchars($mahasiswa['nama']) ?></p>
    <p>Program Studi: <?= htmlspecialchars($mahasiswa['nama_prodi']) ?></p>
    <p>Fakultas: <?= htmlspecialchars($mahasiswa['fakultas']) ?></p>
    <p>Semester: <?= htmlspecialchars($mahasiswa['semester']) ?></p>

    <table border="1">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode MK</th>
                <th>Nama Mata Kuliah</th>
                <th>SKS</th>
                <th>Tugas</th>
                <th>Kuis</th>
                <th>UTS</th>
                <th>UAS</th>
                <th>Nilai Angka</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            $result_nilai->data_seek(0); // Reset result pointer to the beginning
            while ($row = $result_nilai->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $no++ . "</td>";
                echo "<td>" . htmlspecialchars($row['kode_mk']) . "</td>";
                echo "<td>" . htmlspecialchars($row['nama_mk']) . "</td>";
                echo "<td>" . htmlspecialchars($row['sks']) . "</td>";
                echo "<td>" . htmlspecialchars($row['tugas']) . "</td>";
                echo "<td>" . htmlspecialchars($row['kuis']) . "</td>";
                echo "<td>" . htmlspecialchars($row['uts']) . "</td>";
                echo "<td>" . htmlspecialchars($row['uas']) . "</td>";
                echo "<td>" . htmlspecialchars($row['nilai_angka']) . "</td>";
                echo "<td>" . htmlspecialchars($row['nilai_huruf']) . "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="8" align="right"><strong>Total Nilai:</strong></td>
                <td colspan="2"><?= htmlspecialchars(number_format($nilai_akhir, 2)) ?></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
