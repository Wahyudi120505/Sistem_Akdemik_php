<?php
session_start();
include('../../config/koneksi.php');

// Cek apakah user sudah login dan memiliki peran 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Periksa apakah ada parameter 'id' yang dikirimkan melalui URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Query untuk menghapus program studi berdasarkan ID
    $query = "DELETE FROM program_studi WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        // Jika berhasil menghapus, redirect ke halaman program studi
        header("Location: program_studi.php?message=Data berhasil dihapus");
    } else {
        // Jika gagal, tampilkan pesan error
        echo "Terjadi kesalahan saat menghapus data.";
    }

    mysqli_stmt_close($stmt);
} else {
    echo "ID program studi tidak ditemukan.";
}

mysqli_close($conn);
?>
