<?php
// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Mulai sesi (penting untuk mengecek status login)
session_start();

// Include koneksi.php for database connection
include 'koneksi.php';

// Inisialisasi $data dengan nilai default null
$data = null;

// Pastikan koneksi berhasil sebelum menjalankan query
if ($koneksi) {
    // Ambil data terbaru dari tabel home
    $query = "SELECT * FROM home ORDER BY id_home DESC LIMIT 1";
    $result = mysqli_query($koneksi, $query);

    // Periksa apakah query berhasil dieksekusi dan data ditemukan
    if ($result && mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesapean - Preferensi Sapi dan Penjualan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Optional: Add some minor styling if needed for the login/profile buttons */
        .auth-links {
            display: flex;
            gap: 10px;
            /* Jarak antar tombol */
            align-items: center;
        }

        .auth-links .btn {
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
        }

        .auth-links .btn-primary {
            background-color: rgb(204, 145, 96);
            color: white;
            border: none;
        }

        .auth-links .btn-primary:hover {
            background-color: #0056b3;
        }

        .auth-links .btn-outline-primary {
            background-color: transparent;
            color: #007bff;
            border: 1px solid #007bff;
        }

        .auth-links .btn-outline-primary:hover {
            background-color: #007bff;
            color: white;
        }

        .auth-links .btn-danger {
            /* For Logout button */
            background-color: #dc3545;
            color: white;
            border: none;
        }

        .auth-links .btn-danger:hover {
            background-color: #c82333;
        }
    </style>
</head>

<body>
    <header class="main-header">
        <nav class="navbar">
            <div class="logo">
                <a href="index.php">Pesapean</a>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Beranda</a></li>
                <li><a href="pengunjung/peta.php">Peta Interaktif</a></li>
                <li><a href="pengunjung/data_sapi.php">Data Sapi</a></li>
                <li><a href="pengunjung/lelang.php">Lelang</a></li>
                <li><a href="pembeli/pesan.php" <?php if (!isset($_SESSION['id_user'])) echo 'data-bs-toggle="modal" data-bs-target="#loginPromptModal" onclick="return false;"'; ?>>Pesan</a></li>
            </ul>
            <div class="auth-links">
                <?php if (isset($_SESSION['id_user'])): ?>
                    <a href="auth/profile.php" class="btn btn-primary">Profile</a>

                <?php else: ?>
                    <a href="auth/login.php" class="btn btn-primary">Login</a>
                    <a href="auth/register.php" class="btn btn-outline-primary">Daftar</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main>
        <section id="home" class="hero-section">
            <img src="sapi.jpg" alt="Two cows adorned for an event" class="hero-image">
            <div class="hero-content">
                <h1>Pesapean (Preferensi Sapi dan Penjualan)</h1>
                <p>Website ini membantu anda dalam menentukan preferensi sapi dan penjualan yang anda inginkan.</p>
                <a href="chat_admin.php" class="btn btn-secondary" <?php if (!isset($_SESSION['id_user'])) echo 'data-bs-toggle="modal" data-bs-target="#loginPromptModal" onclick="return false;"'; ?>>Join With Us</a>
            </div>
        </section>

        <section id="about" class="about-section">
            <div class="about-image">
                <?php if ($data && isset($data['gambar'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($data['gambar']); ?>" alt="A decorated cow">
                <?php else: ?>
                    <img src="placeholder.jpg" alt="No Image Available">
                <?php endif; ?>
            </div>
            <div class="about-text">
                <h2>Tentang Sape Sonok</h2>
                <?php if ($data && isset($data['sejarah'])): ?>
                    <p><?php echo nl2br(htmlspecialchars($data['sejarah'])); ?></p>
                <?php else: ?>
                    <p>Informasi tentang Sape Sonok belum tersedia. Silakan cek kembali nanti.</p>
                <?php endif; ?>
            </div>
        </section>
        <section id="team" class="team-section">
            <h3 class="section-title">Tim Kami</h3>
            <div class="team-container">
                <div class="team-member">
                    <img src="images/1.png" alt="Tim 1">
                    <p class="member-name">Nama Anggota 1</p>
                </div>
                <div class="team-member">
                    <img src="images/2.png" alt="Tim 2">
                    <p class="member-name">Nama Anggota 2</p>
                </div>
                <div class="team-member">
                    <img src="images/3.png" alt="Tim 3">
                    <p class="member-name">Nama Anggota 3</p>
                </div>
                <div class="team-member">
                    <img src="images/4.png" alt="Tim 4">
                    <p class="member-name">Nama Anggota 4</p>
                </div>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>

    <div class="modal fade" id="loginPromptModal" tabindex="-1" aria-labelledby="loginPromptModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginPromptModalLabel">Akses Dibatasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Silakan **login** terlebih dahulu untuk mengakses fitur ini.</p>
                    <p>Jika Anda belum memiliki akun, silakan **daftar** sekarang!</p>
                </div>
                <div class="modal-footer">

                    <a href="auth/login.php" class="btn btn-primary">Login</a>
                    <a href="auth/login.php" class="btn btn-secondary">Register</a>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>