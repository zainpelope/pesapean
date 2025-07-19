<?php
// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Mulai sesi
session_start();

include '../koneksi.php'; // Pastikan path ini benar

// Cek apakah pengguna sudah login
if (!isset($_SESSION['id_user'])) {
    // Redirect ke halaman login jika belum login
    header("Location: ../login.php"); // Sesuaikan dengan path halaman login Anda
    exit();
}

$loggedInUserId = $_SESSION['id_user'];

// Query untuk mendapatkan lelang yang diikuti oleh user yang sedang login
// Menggunakan DISTINCT untuk menghindari duplikasi lelang jika user menawar berkali-kali
$queryLelangDiikuti = mysqli_query($koneksi, "
    SELECT DISTINCT
        l.id_lelang,
        ds.id_sapi,
        ds.foto_sapi,
        ms.name AS kategori,
        l.harga_awal,
        l.harga_tertinggi,
        l.status,
        l.batas_waktu,
        l.createdAt, -- Kolom ini ditambahkan untuk mengatasi error DISTINCT dan ORDER BY
        -- Tambahkan info penawar tertinggi untuk setiap lelang (opsional, tapi berguna)
        u_highest.username AS nama_penawar_tertinggi,
        u_highest.id_user AS id_penawar_tertinggi,
        -- Ambil penawaran tertinggi yang dilakukan oleh user saat ini di lelang ini
        (SELECT MAX(p_my.harga_tawaran)
         FROM Penawaran p_my
         WHERE p_my.id_lelang = l.id_lelang AND p_my.id_user = '$loggedInUserId') AS my_highest_bid
    FROM Penawaran p
    INNER JOIN lelang l ON p.id_lelang = l.id_lelang
    INNER JOIN data_sapi ds ON l.id_sapi = ds.id_sapi
    INNER JOIN macamSapi ms ON ds.id_macamSapi = ms.id_macamSapi
    LEFT JOIN Penawaran p_highest ON l.id_penawaranTertinggi = p_highest.id_penawaran
    LEFT JOIN users u_highest ON p_highest.id_user = u_highest.id_user
    WHERE p.id_user = '$loggedInUserId'
    ORDER BY l.createdAt DESC
");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Lelang yang Saya Ikuti</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Gaya CSS dari lelang.php atau style.css Anda, sesuaikan jika Anda punya file style.css terpisah */
        :root {
            --primary-color: #343a40;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --white-color: #ffffff;
            --border-color: #e9ecef;
            --shadow-color: rgba(0, 0, 0, 0.08);
        }

        .card-sapi {
            width: 18rem;
            margin: 10px;
        }

        .status {
            font-size: 0.9em;
            font-weight: bold;
            padding: 0.5rem 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, .125);
        }

        .status.sedang {
            color: orange;
        }

        /* Atur warna untuk status 'Sedang' jika ada */
        .status.lewat {
            color: red;
        }

        .status.aktif {
            color: green;
        }

        /* Styling Navbar */
        .main-header {
            background-color: var(--primary-color);
            box-shadow: 0 2px 10px var(--shadow-color);
            padding: 1rem 0;
            margin-bottom: 2rem;
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
            color: var(--white-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .navbar .logo a:hover {
            color: #cccccc;
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
            color: var(--white-color);
            font-weight: 600;
            padding: 0.5rem 0;
            transition: color 0.3s ease, border-bottom 0.3s ease;
        }

        .nav-links li a:hover {
            color: #cccccc;
        }

        .auth-links {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .auth-links .btn-primary {
            background-color: var(--white-color);
            color: var(--primary-color);
            border: none;
        }

        .auth-links .btn-primary:hover {
            background-color: #f0f0f0;
            color: var(--primary-color);
        }

        .auth-links .btn-outline-primary {
            background-color: transparent;
            color: var(--white-color);
            border: 1px solid var(--white-color);
        }

        .auth-links .btn-outline-primary:hover {
            background-color: var(--white-color);
            color: var(--primary-color);
            border: 1px solid var(--white-color);
        }

        @media (max-width: 991.98px) {
            .nav-links {
                display: none;
            }

            .navbar {
                padding: 0 1rem;
            }

            .auth-links {
                margin-left: auto;
            }
        }
    </style>
</head>

<body class="bg-light">
    <header class="main-header">
        <nav class="navbar">
            <div class="logo">
                <a href="../pembeli/beranda.php">Pesapean</a>
            </div>
            <ul class="nav-links">
                <li><a href="../pembeli/beranda.php">Beranda</a></li>
                <li><a href="../pembeli/peta.php">Peta Interaktif</a></li>
                <li><a href="../pembeli/data_sapi.php?jenis=sonok">Data Sapi</a></li>
                <li><a href="../pembeli/lelang.php">Lelang</a></li>
                <li><a href="../pembeli/lelang_diikuti.php">Lelang Saya</a></li>
            </ul>
            <div class="auth-links">
                <?php
                if (isset($_SESSION['id_user'])) {
                    echo '<a href="../auth/profile.php" class="btn btn-primary">Profile</a>'; // Sesuaikan path profile Anda
                } else {
                    echo '<a href="../login.php" class="btn btn-primary">Login</a>';
                    echo '<a href="../register.php" class="btn btn-outline-primary">Daftar</a>';
                }
                ?>
            </div>
        </nav>
    </header>

    <div class="container mt-5">
        <h2 class="mb-4 text-center">Lelang yang Saya Ikuti</h2>
        <div class="row d-flex flex-wrap justify-content-center">
            <?php if (mysqli_num_rows($queryLelangDiikuti) == 0) : ?>
                <div class="alert alert-info w-75 text-center" role="alert">
                    Anda belum mengikuti lelang sapi manapun.
                </div>
            <?php endif; ?>

            <?php while ($sapi = mysqli_fetch_assoc($queryLelangDiikuti)) : ?>
                <div class="card card-sapi">
                    <div class="card-header status <?= strtolower($sapi['status']); ?>">
                        <?= htmlspecialchars($sapi['status']); ?>
                    </div>
                    <img src="../uploads_sapi/<?= htmlspecialchars($sapi['foto_sapi']); ?>" class="card-img-top" alt="gambar sapi" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <p class="card-text text-center text-muted small mb-1">Kategori: <?= htmlspecialchars($sapi['kategori']); ?></p>
                        <p class="card-text text-center small mb-2">
                            Nilai limit: <strong>Rp<?= number_format($sapi['harga_awal']); ?></strong><br>
                            Harga tertinggi: <strong>Rp<?= number_format($sapi['harga_tertinggi']); ?></strong>
                        </p>
                        <p class="card-text text-center small mb-2">
                            Penawaran tertinggi Anda: <strong>Rp<?= number_format($sapi['my_highest_bid']); ?></strong>
                            <?php if ($sapi['id_penawar_tertinggi'] == $loggedInUserId && $sapi['my_highest_bid'] == $sapi['harga_tertinggi']) : ?>
                                <span class="badge bg-success ms-1">Anda Tertinggi!</span>
                            <?php endif; ?>
                        </p>
                        <?php if ($sapi['status'] == 'Aktif' || $sapi['status'] == 'Sedang'): // Sesuaikan status yang valid untuk ditampilkan 
                        ?>
                            <?php if (!empty($sapi['nama_penawar_tertinggi'])): ?>
                                <p class="card-text text-center small mt-2">
                                    Penawar Tertinggi:
                                    <strong>
                                        <?= htmlspecialchars($sapi['nama_penawar_tertinggi']); ?>
                                        <?php if ($sapi['id_penawar_tertinggi'] == $loggedInUserId) : ?>
                                            <span class="badge bg-success ms-1">Anda</span>
                                        <?php endif; ?>
                                    </strong>
                                </p>
                            <?php else: ?>
                                <p class="card-text text-center small mt-2 text-info">
                                    Belum ada penawaran lain.
                                </p>
                            <?php endif; ?>
                        <?php elseif ($sapi['status'] == 'Lewat'): ?>
                            <p class="card-text text-center small mt-2 text-danger">
                                Lelang telah berakhir.
                            </p>
                            <?php if (!empty($sapi['nama_penawar_tertinggi'])): ?>
                                <p class="card-text text-center small mt-1">
                                    Pemenang: <strong><?= htmlspecialchars($sapi['nama_penawar_tertinggi']); ?></strong>
                                </p>
                                <?php if ($sapi['id_penawar_tertinggi'] == $loggedInUserId) : ?>
                                    <p class="card-text text-center small mt-1 text-success">
                                        Selamat, Anda memenangkan lelang ini!
                                    </p>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="card-text text-center small mt-1">
                                    Tidak ada pemenang.
                                </p>
                            <?php endif; ?>
                        <?php endif; ?>
                        <a href="../pembeli/detail.php?id=<?= htmlspecialchars($sapi['id_sapi']); ?>" class="btn btn-success w-100 mt-2">Lihat Detail Lelang</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php include '../footer.php'; ?> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>