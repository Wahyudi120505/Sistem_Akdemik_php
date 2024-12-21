<?php
session_start();
include('../../config/koneksi.php');

// Cek apakah user sudah login dan memiliki peran 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Cek apakah ada ID mata kuliah yang akan dihapus
if (isset($_GET['id'])) {
    $delete_id = $_GET['id'];

    // Query untuk menghapus mata kuliah
    $delete_query = "DELETE FROM mata_kuliah WHERE id = '$delete_id'";
    
    if (mysqli_query($conn, $delete_query)) {
        // Jika berhasil, redirect ke halaman mata kuliah
        echo "<script>alert('Mata kuliah berhasil dihapus'); window.location.href='matkul.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    echo "ID mata kuliah tidak ditemukan.";
}
?>
