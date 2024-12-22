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
    <title>Sistem Informasi Akademik - Universitas Nasional Pasim</title>
    <!-- External CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-gray: #f8f9fa;
            --border-color: #e9ecef;
        }

        body {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px 0;
        }

        .main-container {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
        }

        .login-container {
            background-color: rgba(255, 255, 255, 0.98);
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .login-header {
            background-color: var(--primary-color);
            padding: 2rem;
            text-align: center;
            color: white;
        }

        .login-header img {
            width: 120px;
            height: auto;
            margin-bottom: 1rem;
        }

        .login-body {
            padding: 2.5rem;
        }

        .form-control {
            border-radius: 8px;
            padding: 0.75rem;
            border: 2px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .btn-login {
            background-color: var(--secondary-color);
            border: none;
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
        }

        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: var(--secondary-color);
        }

        .info-section {
            background-color: var(--light-gray);
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 2rem;
        }

        .info-section ul {
            padding-left: 1.2rem;
            margin-bottom: 0;
        }

        .info-section li {
            margin-bottom: 0.5rem;
        }

        .info-section li:last-child {
            margin-bottom: 0;
        }

        .announcement {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 1.5rem;
            margin-top: 1.5rem;
            border-radius: 4px;
        }

        .announcement h5 {
            color: #664d03;
            margin-bottom: 0.75rem;
        }

        .announcement p {
            color: #664d03;
            margin-bottom: 0;
        }

        .input-group-text {
            background-color: transparent;
            border-left: none;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .alert {
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="login-container">
            <div class="login-header">
                <img src="assets/logo_pasim.png" alt="Logo Universitas Nasional Pasim" class="img-fluid">
                <h1 class="h3 mb-2">Sistem Informasi Akademik</h1>
                <p class="mb-0">Universitas Nasional Pasim</p>
            </div>
            
            <div class="login-body">
                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-4">
                        <label for="username" class="form-label">
                            <i class="fas fa-user me-2"></i>Username
                        </label>
                        <input type="text" class="form-control" id="username" name="username" 
                            placeholder="Masukkan NIM/NIP/Username" required>
                        <div class="invalid-feedback">Username tidak boleh kosong</div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-2"></i>Password
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" 
                                placeholder="Masukkan password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">Password tidak boleh kosong</div>
                    </div>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-login btn-primary w-100 mb-4">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>

                    <div class="info-section">
                        <h5 class="mb-3">
                            <i class="fas fa-info-circle me-2"></i>Informasi Login
                        </h5>
                        <ul>
                            <li>Mahasiswa: Gunakan NIM sebagai username</li>
                            <li>Dosen: Gunakan NIP sebagai username</li>
                            <li>Format password default: YYYY-MM-DD (tanggal lahir)</li>
                        </ul>
                    </div>

                    <div class="announcement">
                        <h5>
                            <i class="fas fa-bullhorn me-2"></i>Pengumuman
                        </h5>
                        <p>Sistem akan mengalami pemeliharaan rutin pada tanggal 25 December 2024 pukul 00:00 - 03:00 WIB.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Form validation
        (function() {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html>