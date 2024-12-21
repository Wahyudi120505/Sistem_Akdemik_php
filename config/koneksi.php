<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "akademik_sistem";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>
