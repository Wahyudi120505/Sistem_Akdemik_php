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

    // Ambil nama file soal dari database
    $query_soal = "SELECT id_bank_soal, pertanyaan FROM soal WHERE id = '$soal_id'";
    $result_soal = mysqli_query($conn, $query_soal);

    if ($result_soal && mysqli_num_rows($result_soal) > 0) {
        $row = mysqli_fetch_assoc($result_soal);
        $id_bank_soal = $row['id_bank_soal'];
        
        $query_bank_soal = "SELECT file_soal FROM bank_soal WHERE id = '$id_bank_soal'";
        $result_bank_soal = mysqli_query($conn, $query_bank_soal);
        $file = mysqli_fetch_assoc($result_bank_soal);
        $file_soal = $file['file_soal'];

        // Periksa apakah file ada di folder file_soal dan hapus jika ada
        $file_path = "file_soal/" . $file_soal;
        if (file_exists($file_path)) {
            unlink($file_path); // Hapus file dari folder
        }

        // Hapus soal dari database
        $query_hapus_soal = "DELETE FROM soal WHERE id = '$soal_id'";
        $query_hapus_soal_bank = "DELETE FROM bank_soal WHERE id = '$id_bank_soal'";

        if (mysqli_query($conn, $query_hapus_soal) && mysqli_query($conn, $query_hapus_soal_bank)) {
            header("Location: soal.php"); // Redirect ke daftar soal
            exit;
        } else {
            echo "Terjadi kesalahan saat menghapus soal: " . mysqli_error($conn);
        }
    } else {
        echo "Data soal tidak ditemukan.";
    }
} else {
    echo "ID soal tidak ditemukan.";
    exit;
}
?>
