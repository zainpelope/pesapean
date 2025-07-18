<?php
// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Mulai sesi (penting untuk mengecek status login)
session_start();

// Sertakan file koneksi database
include '../koneksi.php'; // Pastikan path ini benar

// Ambil ID pengguna yang sedang login dari sesi
$loggedInUserId = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : null;

// Update otomatis status lelang jadi 'Lewat' jika sudah melewati batas waktu
// Pastikan hanya update yang statusnya 'Aktif' agar tidak menimpa status 'Selesai' jika ada
mysqli_query($koneksi, "
    UPDATE lelang
    SET status = 'Lewat', updatedAt = NOW()
    WHERE batas_waktu < NOW() AND status = 'Aktif'
");

// Ambil semua kategori dari tabel macamSapi
$queryKategori = mysqli_query($koneksi, "SELECT id_macamSapi, name FROM macamSapi ORDER BY name ASC");

// Ambil kategori terpilih dari URL (GET), default 'semua'
$selectedKategori = isset($_GET['kategori']) ? $_GET['kategori'] : 'semua';

// Query untuk mengambil data sapi yang sedang dilelang
// Ditambahkan LEFT JOIN ke Penawaran dan users untuk mendapatkan detail penawar tertinggi
$queryDataSapi = mysqli_query($koneksi, "
    SELECT
        ds.id_sapi,
        ds.foto_sapi,
        ds.alamat_pemilik,
        ms.name AS kategori,
        l.id_lelang,
        l.harga_awal,
        l.harga_tertinggi,
        l.status,
        l.batas_waktu,
        u.username AS nama_penawar_tertinggi,
        u.id_user AS id_penawar_tertinggi -- Menambahkan ID penawar tertinggi
    FROM data_sapi ds
    INNER JOIN macamSapi ms ON ds.id_macamSapi = ms.id_macamSapi
    INNER JOIN lelang l ON ds.id_sapi = l.id_sapi
    LEFT JOIN Penawaran p ON l.id_penawaranTertinggi = p.id_penawaran -- Gabung dengan Penawaran
    LEFT JOIN users u ON p.id_user = u.id_user -- Gabung dengan users untuk detail penawar
    WHERE l.status != 'Pending' " . // <--- Tambahkan kondisi ini untuk mengecualikan status 'Pending'
    ($selectedKategori != 'semua' ? "AND ms.id_macamSapi = '" . mysqli_real_escape_string($koneksi, $selectedKategori) . "'" : "") . "
    ORDER BY l.createdAt DESC
");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Daftar Sapi Lelang</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-pFQhV+Cq+BfS2Z2v2E2L2R2/2N2P2g2B2D2G2H2I2J2K2L2M2N2O2P2Q2R2S2T2U2V2W2X2Y2Z2==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
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
            padding: 0.5rem 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, .125);
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
                    echo '<a href="../auth/profile.php" class="btn btn-primary">Profile</a>';
                } else {
                    echo '<a href="../login.php" class="btn btn-primary">Login</a>';
                    echo '<a href="../register.php" class="btn btn-outline-primary">Daftar</a>';
                }
                ?>
            </div>
        </nav>
    </header>

    <div class="container mt-5">
        <div class="row">

            <div class="col-md-3 filter-box">
                <h5>Kategori Sapi:</h5>
                <form method="GET" action="">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="kategori" id="semua" value="semua" onchange="this.form.submit()" <?= ($selectedKategori == 'semua') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="semua">Semua</label>
                    </div>

                    <?php while ($kategori = mysqli_fetch_assoc($queryKategori)) : ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="kategori" value="<?= htmlspecialchars($kategori['id_macamSapi']); ?>" id="kategori<?= htmlspecialchars($kategori['id_macamSapi']); ?>" onchange="this.form.submit()" <?= ($selectedKategori == $kategori['id_macamSapi']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="kategori<?= htmlspecialchars($kategori['id_macamSapi']); ?>">
                                <?= htmlspecialchars($kategori['name']); ?>
                            </label>
                        </div>
                    <?php endwhile; ?>
                </form>
            </div>

            <div class="col-md-9 d-flex flex-wrap">
                <?php if (mysqli_num_rows($queryDataSapi) == 0) : ?>
                    <div class="alert alert-warning w-100">Tidak ada sapi untuk lelang pada kategori ini.</div>
                <?php endif; ?>

                <?php while ($sapi = mysqli_fetch_assoc($queryDataSapi)) : ?>
                    <div class="card card-sapi">
                        <div class="card-header status <?= strtolower($sapi['status']); ?>">
                            <?= htmlspecialchars($sapi['status']); ?>
                        </div>
                        <img src="../uploads_sapi/<?= htmlspecialchars($sapi['foto_sapi']); ?>" class="card-img-top" alt="gambar sapi" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <p class="card-text text-center text-muted small mb-1"><?= htmlspecialchars($sapi['alamat_pemilik']); ?></p>
                            <p class="card-text text-center small mb-2">
                                Nilai limit: <strong>Rp<?= number_format($sapi['harga_awal']); ?></strong><br>
                                Harga tertinggi: <strong>Rp<?= number_format($sapi['harga_tertinggi']); ?></strong>
                            </p>
                            <?php if ($sapi['status'] == 'Aktif' || $sapi['status'] == 'Sedang') : // Tampilkan informasi penawar tertinggi hanya jika lelang aktif/sedang 
                            ?>
                                <?php if (!empty($sapi['nama_penawar_tertinggi'])) : ?>
                                    <p class="card-text text-center small mt-2">
                                        Penawar Tertinggi:
                                        <strong>
                                            <?= htmlspecialchars($sapi['nama_penawar_tertinggi']); ?>
                                            <?php if ($loggedInUserId && $sapi['id_penawar_tertinggi'] == $loggedInUserId) : ?>
                                                <span class="badge bg-success ms-1">Anda</span>
                                            <?php endif; ?>
                                        </strong>
                                    </p>
                                <?php else : ?>
                                    <p class="card-text text-center small mt-2 text-info">
                                        Belum ada penawaran.
                                    </p>
                                <?php endif; ?>
                            <?php elseif ($sapi['status'] == 'Lewat') : ?>
                                <p class="card-text text-center small mt-2 text-danger">
                                    Lelang telah berakhir.
                                </p>
                                <?php if (!empty($sapi['nama_penawar_tertinggi'])) : ?>
                                    <p class="card-text text-center small mt-1">
                                        Pemenang: <strong><?= htmlspecialchars($sapi['nama_penawar_tertinggi']); ?></strong>
                                    </p>
                                <?php else : ?>
                                    <p class="card-text text-center small mt-1">
                                        Tidak ada pemenang.
                                    </p>
                                <?php endif; ?>
                            <?php endif; ?>
                            <a href="../pembeli/detail.php?id=<?= htmlspecialchars($sapi['id_sapi']); ?>&id_lelang=<?= htmlspecialchars($sapi['id_lelang']); ?>" class="btn btn-success w-100 mt-2">Detail Lelang</a>
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
    <?php include '../footer.php'; ?> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>