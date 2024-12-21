<?php
session_start();
require 'config/koneksi.php';

// Cek apakah akun admin sudah ada, jika belum maka buat
$usernameAdmin = 'admin';
$passwordAdmin = 'admin123';
$roleAdmin = 'admin';

$hashedPasswordAdmin = password_hash($passwordAdmin, PASSWORD_DEFAULT);
$queryCheck = "SELECT * FROM users WHERE username='$usernameAdmin'";
$resultCheck = mysqli_query($conn, $queryCheck);

if (mysqli_num_rows($resultCheck) === 0) {
    $queryInsert = "INSERT INTO users (username, password, role) VALUES ('$usernameAdmin', '$hashedPasswordAdmin', '$roleAdmin')";
    if (!mysqli_query($conn, $queryInsert)) {
        die("Gagal membuat akun admin: " . mysqli_error($conn));
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);    
        
        // Cek apakah user adalah mahasiswa
        if ($user['role'] === 'mahasiswa' && strlen($password) == 8) {
            // Format ulang password mahasiswa (YYYYMMDD -> YYYY-MM-DD)
            $password = substr($password, 0, 4) . '-' . substr($password, 4, 2) . '-' . substr($password, 6, 2);
        }
        
        // Verifikasi password (baik admin maupun mahasiswa)
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            // Redirect sesuai role
            if ($user['role'] === 'admin') {
                header("Location: admin/dashboard.php");
            } elseif ($user['role'] === 'dosen') {
                header("Location: dosen/home.php");
            } elseif ($user['role'] === 'mahasiswa') {
                header("Location: mahasiswa/home.php");
            }
            exit;
        } else {
            $error = "Username atau password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Sistem Akademik</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-container">
                    <h2>Login Sistem Akademik</h2>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                        </div>
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
