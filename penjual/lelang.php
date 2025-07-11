<?php
// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Mulai sesi (penting untuk mengecek status login)
session_start();

include '../koneksi.php';

// Update otomatis status jadi 'Lewat' jika sudah melewati batas waktu
mysqli_query($koneksi, "
    UPDATE lelang
    SET status = 'Lewat', updatedAt = NOW()
    WHERE batas_waktu < NOW() AND status = 'Aktif'
");

// Ambil semua kategori dari tabel macamSapi
$queryKategori = mysqli_query($koneksi, "SELECT * FROM macamSapi");

// Ambil kategori terpilih dari URL (GET)
$selectedKategori = isset($_GET['kategori']) ? $_GET['kategori'] : 'semua';

// Query sapi yang sedang dilelang
$queryDataSapi = mysqli_query($koneksi, "
    SELECT
        ds.id_sapi,
        ds.foto_sapi,
        ds.alamat_pemilik,
        ms.name AS kategori,
        l.harga_awal,
        l.harga_tertinggi,
        l.status
    FROM data_sapi ds
    INNER JOIN macamSapi ms ON ds.id_macamSapi = ms.id_macamSapi
    INNER JOIN lelang l ON ds.id_sapi = l.id_sapi
    " . ($selectedKategori != 'semua' ? "WHERE ms.id_macamSapi = '$selectedKategori'" : "") . "
    ORDER BY l.createdAt DESC
");
?>

<!DOCTYPE html>
<html>

<head>

    <title>Daftar Sapi</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-pFQhV+Cq+BfS2Z2v2E2L2R2/2N2P2g2B2D2G2H2I2J2K2L2M2N2O2P2Q2R2S2T2U2V2W2X2Y2Z2==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Variabel CSS yang mungkin dibutuhkan di navbar, disalin dari style sebelumnya */
        :root {
            --primary-color: #343a40;
            ;
            /* Example primary color */
            --secondary-color: #6c757d;
            /* Example secondary color */
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

        .filter-box {
            background-color: #e0e0e0;
            padding: 20px;
            border-radius: 8px;
        }

        .card-sapi {
            width: 18rem;
            margin: 10px;
        }

        .status {
            font-size: 0.9em;
            font-weight: bold;
        }

        .status.sedang {
            color: orange;
        }

        .status.lewat {
            color: red;
        }

        .status.aktif {
            color: green;
        }

        /* Styling Navbar yang disesuaikan dari file lain */
        .main-header {
            background-color: var(--primary-color);
            /* Ubah background navbar menjadi warna primer (biru) atau gelap */
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
            /* Ubah warna logo menjadi putih */
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .navbar .logo a:hover {
            color: #cccccc;
            /* Warna putih lebih terang untuk hover */
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
            /* Jadikan warna teks menu putih */
            font-weight: 600;
            padding: 0.5rem 0;
            transition: color 0.3s ease, border-bottom 0.3s ease;
        }

        .nav-links li a:hover {
            color: #cccccc;
            /* Warna putih lebih terang untuk hover */
        }


        .auth-links {
            display: flex;
            gap: 10px;
            /* Jarak antar tombol */
            align-items: center;
        }

        /* Untuk tombol "Profile" atau "Login" */
        .auth-links .btn-primary {
            background-color: var(--white-color);
            /* Ubah background tombol primer jadi putih */
            color: var(--primary-color);
            /* Ubah teks tombol primer jadi warna primer (biru) */
            border: none;
        }

        .auth-links .btn-primary:hover {
            background-color: #f0f0f0;
            /* Sedikit abu-abu untuk hover */
            color: var(--primary-color);
        }

        /* Untuk tombol "Daftar" */
        .auth-links .btn-outline-primary {
            background-color: transparent;
            color: var(--white-color);
            /* Ubah teks tombol outline jadi putih */
            border: 1px solid var(--white-color);
            /* Ubah border tombol outline jadi putih */
        }

        .auth-links .btn-outline-primary:hover {
            background-color: var(--white-color);
            color: var(--primary-color);
            border: 1px solid var(--white-color);
        }

        /* Responsive Adjustments */
        @media (max-width: 991.98px) {
            .nav-links {
                display: none;
                /* Hide nav links on smaller screens for now, consider a toggler */
            }

            .navbar {
                padding: 0 1rem;
            }

            .auth-links {
                margin-left: auto;
                /* Push login button to the right */
            }
        }
    </style>
</head>


<body class="bg-light">
    <header class="main-header">
        <nav class="navbar">
            <div class="logo">
                <a href="../penjual/beranda.php">Pesapean</a>
            </div>
            <ul class="nav-links">
                <li><a href="../penjual/beranda.php">Beranda</a></li>
                <li><a href="../penjual/peta.php">Peta Interaktif</a></li>
                <li><a href="../penjual/data_sapi.php?jenis=sonok">Data Sapi</a></li>
                <li><a href="../penjual/lelang.php">Lelang</a></li>

            </ul>
            <div class="auth-links">
                <?php
                // Check if the user is logged in
                if (isset($_SESSION['id_user'])) {
                    // User is logged in, display Profile button
                    echo '<a href="../auth/profile.php" class="btn btn-primary">Profile</a>';
                } else {
                    // User is not logged in, display Login and Daftar buttons
                    echo '<a href="../auth/login.php" class="btn btn-primary">Login</a>';
                    echo '<a href="../auth/register.php" class="btn btn-outline-primary">Daftar</a>';
                }
                ?>
            </div>
        </nav>
    </header>

    <div class="container mt-5">
        <div class="row">

            <div class="col-md-3 filter-box">
                <h5>Kategori sapi :</h5>
                <form method="GET" action="">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="kategori" id="semua" value="semua" onchange="this.form.submit()" <?= ($selectedKategori == 'semua') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="semua">Semua</label>
                    </div>

                    <?php while ($kategori = mysqli_fetch_assoc($queryKategori)) : ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="kategori" value="<?= $kategori['id_macamSapi']; ?>" id="kategori<?= $kategori['id_macamSapi']; ?>" onchange="this.form.submit()" <?= ($selectedKategori == $kategori['id_macamSapi']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="kategori<?= $kategori['id_macamSapi']; ?>">
                                <?= htmlspecialchars($kategori['name']); ?>
                            </label>
                        </div>
                    <?php endwhile; ?>
                </form>
            </div>

            <div class="col-md-9 d-flex flex-wrap">
                <?php if (mysqli_num_rows($queryDataSapi) == 0): ?>
                    <div class="alert alert-warning w-100">Tidak ada sapi untuk kategori ini.</div>
                <?php endif; ?>

                <?php while ($sapi = mysqli_fetch_assoc($queryDataSapi)) : ?>
                    <div class="card card-sapi">
                        <div class="card-header status <?= strtolower($sapi['status']); ?>">
                            <?= htmlspecialchars($sapi['status']); ?>
                        </div>
                        <img src="../uploads/<?= htmlspecialchars($sapi['foto_sapi']); ?>" class="card-img-top" alt="gambar sapi" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <p class="card-text text-center"><?= htmlspecialchars($sapi['alamat_pemilik']); ?></p>
                            <p class="card-text text-center small">
                                Nilai limit: <strong>Rp<?= number_format($sapi['harga_awal']); ?></strong><br>
                                Uang jaminan: <strong>Rp<?= number_format($sapi['harga_tertinggi']); ?></strong>
                            </p>
                            <a href="../penjual/detail.php?id=<?= $sapi['id_sapi']; ?>" class="btn btn-success w-100">Detail</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col text-center">
                <a href="prosedur.php" class="btn btn-dark btn-lg w-50 mb-2">Prosedur Lelang</a><br>

            </div>
        </div>
    </div>
    <?php include '../footer.php'; ?>
</body>

</html>