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

    // Query untuk menghapus jadwal kuliah
    $query = "DELETE FROM jadwal_kuliah WHERE id = '$id_jadwal'";

    if (mysqli_query($conn, $query)) {
        // Jika berhasil, redirect ke halaman jadwal_matkul.php
        header("Location: jadwal_kuliah.php");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    header("Location: jadwal_kuliah.php");
    exit;
}
?>
