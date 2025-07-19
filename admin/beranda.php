<?php
session_start();
include '../koneksi.php'; // Pastikan path ini benar


}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesapean - Beranda Admin</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Variabel warna global - PASTIKAN SESUAI DENGAN style.css atau tambahkan di sini */
        :root {
            --primary-color: rgb(240, 161, 44);
            --secondary-color: rgb(48, 52, 56);
            --tertiary-color: #6c757d;
            --dark-color: #333;
            --dark-text: #212529;
            --light-bg: #f8f9fa;
            --white-bg: #ffffff;
            --border-color: #dee2e6;
            --box-shadow-light: 0 4px 15px rgba(0, 0, 0, 0.08);
            --box-shadow-medium: 0 8px 25px rgba(0, 0, 0, 0.15);
            --border-radius-sm: 8px;
            --border-radius-md: 10px;
            --border-radius-lg: 12px;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: var(--light-bg);
        }

        .main-header {
            background-color: var(--white-bg);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .navbar .logo a {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .nav-links {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            gap: 1.5rem;
        }

        .nav-links li a {
            text-decoration: none;
            color: var(--dark-color);
            font-weight: 600;
            padding: 0.5rem 0;
            transition: color 0.3s ease, border-bottom 0.3s ease;
        }

        .nav-links li a:hover,
        .nav-links li a.active {
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
        }

        .auth-links {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .auth-links .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .auth-links .btn-primary {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }

        .auth-links .btn-outline-primary {
            background-color: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }

        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 50px 20px;
        }

        .admin-dashboard {
            background-color: var(--white-bg);
            padding: 40px;
            border-radius: 1rem;
            box-shadow: var(--box-shadow-medium);
            max-width: 600px;
            width: 100%;
        }

        .admin-dashboard h1 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .admin-dashboard p {
            font-size: 1.1rem;
            color: var(--dark-text);
            margin-bottom: 30px;
        }
    </style>
</head>

<body>

    <header class="main-header">
        <nav class="navbar">
            <div class="logo">
                <a href="beranda.php">Pesapean</a>
            </div>
            <ul class="nav-links">
                <li><a href="beranda.php" class="active">Beranda Admin</a></li>
                <li><a href="pesan.php">Pesan</a></li>
            </ul>
            <div class="auth-links">
                <?php
                if (isset($_SESSION['id_user'])) {
                    echo '<a href="../auth/profile.php" class="btn btn-primary">Profile</a>';
                } else {
                    echo '<a href="../auth/login.php" class="btn btn-primary">Login</a>';
                    echo '<a href="../auth/register.php" class="btn btn-outline-primary">Daftar</a>';
                }
                ?>
            </div>
        </nav>
    </header>

    <div class="main-content">
        <div class="admin-dashboard">
            <h1>Selamat Datang, Admin <?= htmlspecialchars($_SESSION['username'] ?? '') ?>!</h1>
            <p>Ini adalah panel administrasi Anda. Gunakan navigasi di atas untuk mengelola sistem.</p>
            <a href="pesan.php" class="btn btn-primary btn-lg"><i class="fas fa-envelope me-2"></i> Lihat Pesan</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivriel.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include '../footer.php'; ?>
</body>

</html>