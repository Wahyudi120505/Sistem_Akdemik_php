<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dosen') {
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home Dosen</title>
</head>
<body>
    <h1>Selamat Datang, Dosen</h1>
    <a href="jadwal.php">Lihat Jadwal</a>
    <a href="nilai.php">Input Nilai</a>
    <a href="../logout.php">Logout</a>
</body>
</html>
