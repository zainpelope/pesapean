<?php
// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Mulai sesi (PENTING: Harus di paling atas)
session_start();

include '../koneksi.php'; // Pastikan path ini benar sesuai struktur folder Anda

// Pastikan id_sapi ada di URL
if (!isset($_GET['id'])) {
    echo "ID sapi tidak ditemukan.";
    exit;
}

$id_sapi = $_GET['id'];

// Update otomatis status lelang jadi 'Lewat' jika sudah melewati batas waktu
// Ini penting untuk menjaga status lelang tetap up-to-date
mysqli_query($koneksi, "
    UPDATE lelang
    SET status = 'Lewat', updatedAt = NOW()
    WHERE batas_waktu < NOW() AND status = 'Aktif'
");

// Ambil data detail sapi + lelang
// Tambahkan LEFT JOIN ke Penawaran dan users untuk mendapatkan detail penawar tertinggi
$query = mysqli_query($koneksi, "
    SELECT
        ds.*,
        ms.name AS kategori,
        l.id_lelang,
        l.harga_tertinggi,
        l.status,
        l.batas_waktu,
        u.username AS nama_penawar_tertinggi,
        u.id_user AS id_penawar_tertinggi
    FROM data_sapi ds
    INNER JOIN macamSapi ms ON ds.id_macamSapi = ms.id_macamSapi
    INNER JOIN lelang l ON ds.id_sapi = l.id_sapi
    LEFT JOIN Penawaran p ON l.id_penawaranTertinggi = p.id_penawaran
    LEFT JOIN users u ON p.id_user = u.id_user
    WHERE ds.id_sapi = '" . mysqli_real_escape_string($koneksi, $id_sapi) . "'
");

// Cek apakah data sapi ditemukan
if (mysqli_num_rows($query) == 0) {
    echo "Data sapi tidak ditemukan atau tidak memiliki lelang aktif.";
    exit;
}

$sapi = mysqli_fetch_assoc($query);

// Cek apakah lelang masih aktif berdasarkan status dan batas waktu
$lelang_aktif = ($sapi['status'] == 'Aktif' && strtotime($sapi['batas_waktu']) > time());

// Ambil ID pengguna yang sedang login dari sesi
$loggedInUserId = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : null;

?>

<!DOCTYPE html>
<html>

<head>
    <title>Detail Sapi Lelang - <?= htmlspecialchars($sapi['kategori']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../style.css">
    <style>
        /* Gaya spesifik untuk halaman detail */
        .detail-box {
            max-width: 800px;
            margin: auto;
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .detail-box img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .label {
            font-weight: bold;
            color: #495057;
        }

        .list-group-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.95rem;
        }

        .status-badge {
            padding: 0.4em 0.7em;
            border-radius: 0.25rem;
            font-weight: bold;
        }

        .status-badge.aktif {
            background-color: #d4edda;
            color: #155724;
        }

        .status-badge.sedang {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-badge.lewat {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Styling Navbar yang disesuaikan dari file lain, atau Anda bisa memindahkannya ke style.css */
        .main-header {
            background-color: #343a40;
            /* primary-color */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            /* shadow-color */
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
            /* Pastikan font ini tersedia */
            font-size: 1.8rem;
            font-weight: 700;
            color: #ffffff;
            /* white-color */
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
            color: #ffffff;
            /* white-color */
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
            background-color: #ffffff;
            /* white-color */
            color: #343a40;
            /* primary-color */
            border: none;
        }

        .auth-links .btn-primary:hover {
            background-color: #f0f0f0;
            color: #343a40;
        }

        .auth-links .btn-outline-primary {
            background-color: transparent;
            color: #ffffff;
            /* white-color */
            border: 1px solid #ffffff;
            /* white-color */
        }

        .auth-links .btn-outline-primary:hover {
            background-color: #ffffff;
            color: #343a40;
            border: 1px solid #ffffff;
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
                <a href="../index.php">Pesapean</a>
            </div>
            <ul class="nav-links">
                <li><a href="../index.php">Beranda</a></li>
                <li><a href="../pengunjung/peta.php">Peta Interaktif</a></li>
                <li><a href="../pengunjung/data_sapi.php?jenis=sonok">Data Sapi</a></li>
                <li><a href="../pengunjung/lelang.php">Lelang</a></li>
                <li><a href="../pengunjung/lelang_diikuti.php">Lelang Saya</a></li>
                <li><a href="../pengunjung/pesan.php">Pesan</a></li>
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
        <div class="detail-box shadow">
            <h3 class="mb-4 text-center">Detail Sapi Lelang</h3>
            <img src="../uploads_sapi/<?= htmlspecialchars($sapi['foto_sapi']); ?>" alt="Foto sapi">

            <div class="mt-4">
                <p><span class="label">Kategori:</span> <?= htmlspecialchars($sapi['kategori']); ?></p>
                <p><span class="label">Nama Pemilik:</span> <?= htmlspecialchars($sapi['nama_pemilik']); ?></p>
                <p><span class="label">Alamat Pemilik:</span> <?= htmlspecialchars($sapi['alamat_pemilik']); ?></p>
                <p><span class="label">Nomor Pemilik:</span> <?= htmlspecialchars($sapi['nomor_pemilik']); ?></p>
                <p><span class="label">Email Pemilik:</span> <?= htmlspecialchars($sapi['email_pemilik']); ?></p>
                <p><span class="label">Status Lelang:</span>
                    <span class="status-badge <?= strtolower($sapi['status']); ?>">
                        <?= htmlspecialchars($sapi['status']); ?>
                    </span>
                </p>

                <p><span class="label">Harga Tertinggi Saat Ini:</span> Rp<?= number_format($sapi['harga_tertinggi']); ?></p>
                <p><span class="label">Batas Waktu:</span> <?= date("d M Y H:i", strtotime($sapi['batas_waktu'])); ?></p>
                <?php if (!empty($sapi['nama_penawar_tertinggi'])): ?>
                    <p><span class="label">Penawar Tertinggi:</span>
                        <strong>
                            <?= htmlspecialchars($sapi['nama_penawar_tertinggi']); ?>
                            <?php if ($loggedInUserId && $sapi['id_penawar_tertinggi'] == $loggedInUserId) : ?>
                                <span class="badge bg-success ms-1">Anda</span>
                            <?php endif; ?>
                        </strong>
                    </p>
                <?php else: ?>
                    <p class="text-muted">Belum ada penawaran.</p>
                <?php endif; ?>
            </div>

            <?php if ($lelang_aktif) : ?>
                <hr>
                <h5 class="mt-4">Ikuti Lelang Ini</h5>
                <?php if ($loggedInUserId) : // Tampilkan form penawaran hanya jika user sudah login 
                ?>
                    <form action="proses_penawaran.php" method="POST">
                        <input type="hidden" name="id_lelang" value="<?= htmlspecialchars($sapi['id_lelang']); ?>">
                        <input type="hidden" name="id_sapi" value="<?= htmlspecialchars($sapi['id_sapi']); ?>">
                        <div class="mb-3">
                            <label for="harga_tawaran" class="form-label">Harga Penawaran Anda:</label>
                            <input type="number" class="form-control" id="harga_tawaran" name="harga_tawaran" min="<?= $sapi['harga_tertinggi'] + 1; ?>" required>
                            <small class="form-text text-muted">Masukkan harga lebih tinggi dari Rp<?= number_format($sapi['harga_tertinggi']); ?></small>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Ajukan Penawaran</button>
                    </form>
                <?php else : ?>
                    <div class="alert alert-warning text-center">
                        Anda harus <a href="../login.php" class="alert-link">login</a> untuk mengajukan penawaran.
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <div class="alert alert-info text-center mt-4">
                    Lelang ini sudah berakhir atau tidak aktif.
                    <?php if ($sapi['status'] == 'Lewat' && !empty($sapi['nama_penawar_tertinggi'])): ?>
                        <br>Pemenang: <strong><?= htmlspecialchars($sapi['nama_penawar_tertinggi']); ?></strong> dengan harga Rp<?= number_format($sapi['harga_tertinggi']); ?>
                        <?php if ($loggedInUserId && $sapi['id_penawar_tertinggi'] == $loggedInUserId): ?>
                            <br><span class="badge bg-success mt-1">Selamat, Anda memenangkan lelang ini!</span>
                            <p class="mt-3">
                                Silakan klik <a href="pembayaran.php?id_lelang=<?= htmlspecialchars($sapi['id_lelang']); ?>" class="alert-link">di sini</a> untuk melakukan pembayaran.
                            </p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php
            // Query untuk mendapatkan riwayat penawaran untuk lelang ini saja
            // TIDAK ADA PERUBAHAN PADA BAGIAN INI KARENA LOGIC-nya SUDAH BENAR
            $queryBidHistory = mysqli_query($koneksi, "
                SELECT
                    p.harga_tawaran,
                    p.waktu_tawaran,
                    u.username,
                    u.id_user
                FROM Penawaran p
                INNER JOIN users u ON p.id_user = u.id_user
                WHERE p.id_lelang = '" . mysqli_real_escape_string($koneksi, $sapi['id_lelang']) . "'
                ORDER BY p.harga_tawaran DESC, p.waktu_tawaran ASC
            ");
            ?>
            <?php if (mysqli_num_rows($queryBidHistory) > 0): ?>
                <hr>
                <h5 class="mt-4">Riwayat Penawaran Lelang Ini</h5>
                <ul class="list-group">
                    <?php while ($bid = mysqli_fetch_assoc($queryBidHistory)): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= htmlspecialchars($bid['username']); ?></strong>
                                <?php if ($loggedInUserId && $bid['id_user'] == $loggedInUserId): ?>
                                    <span class="badge bg-primary ms-1">Anda</span>
                                <?php endif; ?>
                                <br><small class="text-muted"><?= date("d M Y H:i", strtotime($bid['waktu_tawaran'])); ?></small>
                            </div>
                            <span class="badge bg-info text-dark">Rp<?= number_format($bid['harga_tawaran']); ?></span>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <hr>
                <p class="text-muted text-center">Belum ada riwayat penawaran untuk lelang ini.</p>
            <?php endif; ?>

            <div class="text-center mt-4">
                <a href="../pengunjung/lelang.php" class="btn btn-secondary">Kembali ke Daftar Lelang</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');

            if (status) {
                let icon, title, text;
                switch (status) {
                    case 'success':
                        icon = 'success';
                        title = 'Penawaran Berhasil!';
                        text = 'Penawaran Anda telah berhasil diajukan.';
                        break;
                    case 'failed':
                        icon = 'error';
                        title = 'Penawaran Gagal!';
                        text = 'Harga penawaran harus lebih tinggi dari harga tertinggi saat ini.';
                        break;
                    case 'not_logged_in':
                        icon = 'warning';
                        title = 'Login Diperlukan!';
                        text = 'Anda harus login untuk melakukan penawaran.';
                        break;
                    case 'failed_inactive':
                        icon = 'warning';
                        title = 'Lelang Tidak Aktif!';
                        text = 'Maaf, lelang ini sudah berakhir atau tidak aktif.';
                        break;
                    case 'failed_update_lelang':
                        icon = 'error';
                        title = 'Kesalahan Sistem!';
                        text = 'Terjadi kesalahan saat memperbarui data lelang. Silakan coba lagi.';
                        break;
                    case 'failed_insert_penawaran':
                        icon = 'error';
                        title = 'Kesalahan Sistem!';
                        text = 'Terjadi kesalahan saat menyimpan penawaran. Silakan coba lagi.';
                        break;
                    case 'invalid_data':
                        icon = 'error';
                        title = 'Data Tidak Valid!';
                        text = 'Data penawaran tidak lengkap atau tidak valid.';
                        break;
                    case 'already_bid': // Status baru untuk penawaran berulang
                        icon = 'info';
                        title = 'Penawaran Ditolak!';
                        text = 'Anda sudah pernah mengajukan penawaran di lelang ini dan bukan penawar tertinggi saat ini. Silakan cari lelang sapi lainnya.';
                        break;
                    case 'server_error': // Status untuk error umum dari server
                        icon = 'error';
                        title = 'Kesalahan Server!';
                        text = 'Terjadi kesalahan tak terduga di server. Silakan coba lagi nanti.';
                        break;
                    default:
                        return;
                }

                Swal.fire({
                    icon: icon,
                    title: title,
                    text: text,
                    confirmButtonText: 'OK'
                }).then(() => {
                    // Hapus parameter 'status' dari URL setelah notifikasi ditampilkan
                    // Penting: pastikan id_sapi tetap ada di URL setelah penghapusan status
                    const currentUrl = window.location.href;
                    const cleanUrl = currentUrl.split('?')[0] + '?id=<?= htmlspecialchars($sapi['id_sapi']); ?>';
                    window.history.replaceState({
                        path: cleanUrl
                    }, '', cleanUrl);
                });
            }
        });
    </script>
</body>

</html>