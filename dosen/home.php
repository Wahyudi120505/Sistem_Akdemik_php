<?php
session_start();
include('../config/koneksi.php'); // Pastikan koneksi sudah benar

// Check if user is logged in and has 'dosen' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dosen') {
    header("Location: ../index.php");
    exit;
}

// Query untuk mengambil data dosen
$query_dosen = "SELECT id, nama, email, jabatan, no_telepon FROM dosen WHERE user_id = ?";
$stmt_dosen = $conn->prepare($query_dosen);
$stmt_dosen->bind_param("i", $_SESSION['user_id']);
$stmt_dosen->execute();
$result_dosen = $stmt_dosen->get_result();

// Periksa apakah data dosen ditemukan
if ($result_dosen->num_rows > 0) {
    $dosen = $result_dosen->fetch_assoc();
    $dosen_id = $dosen['id']; 
    // Pastikan data dosen ada
} else {
    die("Data dosen tidak ditemukan.");
}

// Query untuk mata kuliah yang diampu
$query_mk = "SELECT mk.kode_mk, mk.nama_mk, mk.sks, mk.semester 
             FROM mata_kuliah mk 
             JOIN jadwal_kuliah jk ON mk.id = jk.id_mata_kuliah 
             WHERE jk.id_dosen = ?";
$stmt_mk = $conn->prepare($query_mk);
$stmt_mk->bind_param("i", $dosen_id);
$stmt_mk->execute();
$mata_kuliah = $stmt_mk->get_result();

// Query untuk jadwal kuliah dosen
$query_jadwal = "SELECT jk.hari, jk.jam_mulai, jk.jam_selesai, jk.ruangan, mk.nama_mk 
                 FROM jadwal_kuliah jk 
                 JOIN mata_kuliah mk ON jk.id_mata_kuliah = mk.id 
                 WHERE jk.id_dosen = ?";
$stmt_jadwal = $conn->prepare($query_jadwal);
$stmt_jadwal->bind_param("i", $dosen_id);
$stmt_jadwal->execute();
$jadwal = $stmt_jadwal->get_result();

// Query untuk daftar mahasiswa yang mengambil mata kuliah dosen
$query_mahasiswa = "SELECT m.nim, m.nama, k.semester, k.tahun_ajaran, mk.nama_mk
                    FROM mahasiswa m
                    JOIN krs k ON m.id = k.id_mahasiswa
                    JOIN mata_kuliah mk ON k.id_mata_kuliah = mk.id
                    JOIN jadwal_kuliah jk ON jk.id_mata_kuliah = mk.id
                    WHERE jk.id_dosen = ?";
$stmt_mahasiswa = $conn->prepare($query_mahasiswa);
$stmt_mahasiswa->bind_param("i", $dosen_id);
$stmt_mahasiswa->execute();
$mahasiswa_list = $stmt_mahasiswa->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Dosen - Sistem Informasi Akademik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEJw4J8q3V0Tg9yb2YcflsoSIJ5Mha1Y6U2poZQZ8fjP4SeOxMyJ9VcspsgXY" crossorigin="anonymous">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Sistem Informasi Akademik</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">

        <!-- Profil Dosen -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4>Profil Dosen</h4>
            </div>
            <div class="card-body">
                <p><strong>Nama:</strong> <?php echo htmlspecialchars($dosen['nama']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($dosen['email']); ?></p>
                <p><strong>Jabatan:</strong> <?php echo htmlspecialchars($dosen['jabatan']); ?></p>
                <p><strong>No Telepon:</strong> <?php echo htmlspecialchars($dosen['no_telepon']); ?></p>
                <a href="update_profile.php" class="btn btn-warning">Edit Profil</a>
            </div>
        </div>

        <!-- Mata Kuliah yang Diampu -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h4>Mata Kuliah yang Diampu</h4>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Kode MK</th>
                            <th>Nama Mata Kuliah</th>
                            <th>SKS</th>
                            <th>Semester</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($mk = mysqli_fetch_assoc($mata_kuliah)) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($mk['kode_mk']); ?></td>
                                <td><?php echo htmlspecialchars($mk['nama_mk']); ?></td>
                                <td><?php echo htmlspecialchars($mk['sks']); ?></td>
                                <td><?php echo htmlspecialchars($mk['semester']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Jadwal Kuliah -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h4>Jadwal Kuliah</h4>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Hari</th>
                            <th>Jam Mulai</th>
                            <th>Jam Selesai</th>
                            <th>Ruangan</th>
                            <th>Mata Kuliah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $jadwal->fetch_assoc()) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['hari']); ?></td>
                                <td><?php echo htmlspecialchars($row['jam_mulai']); ?></td>
                                <td><?php echo htmlspecialchars($row['jam_selesai']); ?></td>
                                <td><?php echo htmlspecialchars($row['ruangan']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_mk']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Daftar Mahasiswa yang Mengambil Mata Kuliah -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h4>Daftar Mahasiswa yang Mengambil Mata Kuliah</h4>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th>Mata Kuliah</th>
                            <th>Semester</th>
                            <th>Tahun Ajaran</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($mahasiswa = $mahasiswa_list->fetch_assoc()) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($mahasiswa['nim']); ?></td>
                                <td><?php echo htmlspecialchars($mahasiswa['nama']); ?></td>
                                <td><?php echo htmlspecialchars($mahasiswa['nama_mk']); ?></td>
                                <td><?php echo htmlspecialchars($mahasiswa['semester']); ?></td>
                                <td><?php echo htmlspecialchars($mahasiswa['tahun_ajaran']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-pzjw8f+ua7Kw1TIq0G5YYmA8wXs7w5nA7U2vM26z4wDgZ5g0D6JwGs6XqFh8Jfkp" crossorigin="anonymous"></script>
</body>
</html>
