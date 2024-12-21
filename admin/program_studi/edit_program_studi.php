<?php
session_start();
include('../../config/koneksi.php');

// Cek apakah user sudah login dan memiliki peran 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Cek apakah ada parameter 'id' yang dikirimkan
if (isset($_GET['id'])) {
    $id_prodi = $_GET['id'];

    // Ambil data program studi berdasarkan ID
    $query = "SELECT * FROM program_studi WHERE id = $id_prodi";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
} else {
    header("Location: program_studi.php");
    exit;
}

// Proses jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_prodi = mysqli_real_escape_string($conn, $_POST['nama_prodi']);
    $fakultas = mysqli_real_escape_string($conn, $_POST['fakultas']);

    // Query untuk memperbarui data program studi
    $query = "UPDATE program_studi SET nama_prodi='$nama_prodi', fakultas='$fakultas' WHERE id=$id_prodi";
    if (mysqli_query($conn, $query)) {
        header("Location: program_studi.php");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Program Studi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Sistem Akademik</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4">Edit Program Studi</h1>

        <form action="edit_program_studi.php?id=<?php echo $row['id']; ?>" method="POST">
            <div class="mb-3">
                <label for="nama_prodi" class="form-label">Nama Program Studi</label>
                <input type="text" class="form-control" id="nama_prodi" name="nama_prodi" value="<?php echo $row['nama_prodi']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="fakultas" class="form-label">Fakultas</label>
                <input type="text" class="form-control" id="fakultas" name="fakultas" value="<?php echo $row['fakultas']; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Perbarui Program Studi</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
