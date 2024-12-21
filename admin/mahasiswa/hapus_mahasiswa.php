<?php
session_start();
include('../../config/koneksi.php');

// Cek apakah user sudah login dan memiliki peran 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Pastikan ada ID mahasiswa yang diterima melalui URL
if (!isset($_GET['id'])) {
    header("Location: mahasiswa.php");
    exit;
}

$id_mahasiswa = $_GET['id'];

// Mulai transaksi untuk memastikan konsistensi data
mysqli_begin_transaction($conn);
try {
    // Ambil user_id dari mahasiswa yang akan dihapus
    $query_user_id = "SELECT user_id FROM mahasiswa WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query_user_id);
    mysqli_stmt_bind_param($stmt, 'i', $id_mahasiswa);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $user_id);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if (!$user_id) {
        throw new Exception("Data mahasiswa tidak ditemukan.");
    }

    // Hapus data mahasiswa
    $query_delete_mahasiswa = "DELETE FROM mahasiswa WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query_delete_mahasiswa);
    mysqli_stmt_bind_param($stmt, 'i', $id_mahasiswa);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Hapus data user terkait
    $query_delete_user = "DELETE FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query_delete_user);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Commit transaksi
    mysqli_commit($conn);

    // Redirect kembali ke halaman mahasiswa
    header("Location: mahasiswa.php");
    exit;

} catch (Exception $e) {
    // Rollback transaksi jika terjadi error
    mysqli_rollback($conn);
    echo "Terjadi kesalahan: " . $e->getMessage();
}
?>
