<?php
session_start();
include('../../config/koneksi.php');

// Cek apakah user sudah login dan memiliki peran 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Proses hapus data
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "DELETE FROM ruangan WHERE id = '$id'";

    if (mysqli_query($conn, $query)) {
        header("Location: ruangan.php");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
