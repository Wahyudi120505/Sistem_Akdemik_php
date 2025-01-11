<?php
    session_start();
    include('../../config/koneksi.php'); // Pastikan untuk menyertakan file koneksi database
    
    // Cek apakah user sudah login dan memiliki peran 'mahasiswa'
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
        header("Location: ../index.php");
        exit;
    }
    
    // Mengambil user_id dari session
    $user_id = $_SESSION['user_id'];
    
    // Query untuk mengambil data mahasiswa berdasarkan user_id yang sedang login
    $query_users = "SELECT username FROM users WHERE id = '$user_id' AND role = 'mahasiswa'";
    $result_user = mysqli_query($conn, $query_users);
    
    // Cek apakah query berhasil dijalankan
    if ($result_user && mysqli_num_rows($result_user) > 0) {
        $user = mysqli_fetch_assoc($result_user);
        $username = $user['username'];

        // Query untuk mengambil data mahasiswa berdasarkan username
        $query_mahasiswa = "SELECT * FROM mahasiswa WHERE nim = '$username'";
        $result_mahasiswa = mysqli_query($conn, $query_mahasiswa);
        
        // Cek apakah data mahasiswa ditemukan
        if ($result_mahasiswa && mysqli_num_rows($result_mahasiswa) > 0) {
            $mahasiswa = mysqli_fetch_assoc($result_mahasiswa);
            
            // Menampilkan data mahasiswa yang sedang login
            $data_mahasiswa = "
            <div class='container'>
                <h2>Data Mahasiswa yang sedang login</h2>
                <div class='card'>
                    <div class='card-header'>
                        <strong>Profil Mahasiswa</strong>
                    </div>
                    <div class='card-body'>
                        <p><strong>NIM:</strong> {$mahasiswa['nim']}</p>
                        <p><strong>Nama:</strong> {$mahasiswa['nama']}</p>
                        <p><strong>Tanggal Lahir:</strong> {$mahasiswa['tanggal_lahir']}</p>
                        <p><strong>Alamat:</strong> {$mahasiswa['alamat']}</p>
                        <p><strong>Email:</strong> {$mahasiswa['email']}</p>
                        <p><strong>No Telepon:</strong> {$mahasiswa['no_telepon']}</p>
                        <p><strong>Angkatan:</strong> {$mahasiswa['angkatan']}</p>
                    </div>
                    <div class='card-footer'>
                        <a href='edit_mahasiswa.php?nim={$mahasiswa['nim']}' class='btn btn-warning'>Edit</a>
                    </div>
                </div>
            </div>";
            echo $data_mahasiswa;
        } else {
            echo "Data mahasiswa tidak ditemukan.";
        }
    } else {
        echo "Pengguna tidak ditemukan.";
    }
?>

<!-- Styling -->
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f7fa;
        margin: 0;
        padding: 0;
    }
    .container {
        width: 80%;
        margin: 20px auto;
        padding: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    h2 {
        text-align: center;
        color: #333;
    }
    .card {
        border: 1px solid #ddd;
        border-radius: 8px;
        margin-top: 20px;
    }
    .card-header {
        background-color: #007bff;
        color: white;
        padding: 10px;
        font-size: 18px;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }
    .card-body {
        padding: 20px;
    }
    p {
        font-size: 16px;
        color: #555;
        margin-bottom: 10px;
    }
    strong {
        color: #007bff;
    }
    .card-footer {
        text-align: center;
        padding: 10px;
    }
    .btn {
        text-decoration: none;
        padding: 10px 20px;
        border-radius: 5px;
        font-weight: bold;
        color: white;
    }
    .btn-warning {
        background-color: #f0ad4e;
    }
    .btn-warning:hover {
        background-color: #ec971f;
    }
</style>
