<?php
    session_start();
    include('../../config/koneksi.php');

    // Cek apakah user sudah login dan memiliki peran 'dosen'
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dosen') {
        header("Location: ../index.php");
        exit;
    }

    if (isset($_GET['id'])) {
        $soal_id = $_GET['id'];

        // Hapus soal dari database
        $query_hapus = "DELETE FROM soal WHERE id = '$soal_id'";

        if (mysqli_query($conn, $query_hapus)) {
            echo "Soal berhasil dihapus.";
            header("Location: soal.php"); // Redirect ke daftar soal
        } else {
            echo "Terjadi kesalahan: " . mysqli_error($conn);
        }
    } else {
        echo "ID soal tidak ditemukan.";
        exit;
    }
?>
