<?php
session_start();
include('../../config/koneksi.php');

// Cek apakah user sudah login dan memiliki peran 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Pastikan ada ID dosen yang diterima melalui URL
if (!isset($_GET['id'])) {
    header("Location: manajemen_dosen.php");
    exit;
}

$id_dosen = $_GET['id'];

// Mulai transaksi untuk menjaga konsistensi data
mysqli_begin_transaction($conn);

try {
    // Ambil user_id dari dosen yang akan dihapus
    $query_user_id = "SELECT user_id FROM dosen WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query_user_id);
    mysqli_stmt_bind_param($stmt, 'i', $id_dosen);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $user_id);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if (!$user_id) {
        throw new Exception("Data dosen tidak ditemukan.");
    }

    // Hapus data dosen
    $query_delete_dosen = "DELETE FROM dosen WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query_delete_dosen);
    mysqli_stmt_bind_param($stmt, 'i', $id_dosen);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Hapus data user terkait
    $query_delete_user = "DELETE FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query_delete_user);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Commit transaksi jika berhasil
    mysqli_commit($conn);

    // Redirect kembali ke halaman manajemen dosen
    $_SESSION['success'] = "Data dosen berhasil dihapus.";
    header("Location: dosen.php");
    exit;

} catch (Exception $e) {
    // Rollback transaksi jika ada error
    mysqli_rollback($conn);
    $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
    header("Location: dosen.php");
    exit;
}
?>
