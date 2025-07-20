<?php
// Hati-hati: Error reporting diaktifkan untuk debugging. Nonaktifkan di lingkungan produksi.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// >>> PENTING: session_start() DAN SEMUA CEK SESI DIHAPUS SESUAI PERMINTAAN <<<
// Ini membuat halaman dapat diakses tanpa login.
// Harap pahami implikasi keamanan dari keputusan ini.

include '../koneksi.php'; // Pastikan path ini benar (dari penjual/ ke pesapean/koneksi.php)

// Ambil id_lelang dan id_pemenang dari URL
if (!isset($_GET['id_lelang']) || !isset($_GET['id_pemenang'])) {
    // Redirect ke halaman daftar lelang dengan status error jika parameter tidak lengkap
    header("Location: ../penjual/lelang.php?status=invalid_data");
    exit();
}

$id_lelang = mysqli_real_escape_string($koneksi, $_GET['id_lelang']);
$id_pemenang = mysqli_real_escape_string($koneksi, $_GET['id_pemenang']); // Ini adalah ID User Pemenang

// Ambil detail lelang untuk menampilkan informasi dan memproses konfirmasi
$queryLelang = mysqli_query($koneksi, "
    SELECT
        l.id_lelang,
        l.status,
        l.id_penawaranTertinggi,
        l.harga_tertinggi,
        ds.id_sapi,                  -- Pastikan id_sapi diambil dari data_sapi
        ds.id_user_penjual,          -- Masih mengambil ini, tapi tidak digunakan untuk otorisasi akses
        p_tertinggi.harga_tawaran,   -- Mengambil harga tawaran dari penawaran tertinggi
        u_pemenang.username AS nama_pemenang,
        u_pemenang.email AS email_pemenang,
        ds.nama_pemilik AS nama_penjual,
        ds.email_pemilik AS email_penjual,
        ds.foto_sapi,
        ms.name AS kategori_sapi
    FROM lelang l
    INNER JOIN data_sapi ds ON l.id_sapi = ds.id_sapi
    LEFT JOIN penawaran p_tertinggi ON l.id_penawaranTertinggi = p_tertinggi.id_penawaran
    LEFT JOIN users u_pemenang ON p_tertinggi.id_user = u_pemenang.id_user
    INNER JOIN macamsapi ms ON ds.id_macamSapi = ms.id_macamSapi
    WHERE l.id_lelang = '$id_lelang'
");

if (mysqli_num_rows($queryLelang) == 0) {
    header("Location: ../penjual/lelang.php?status=lelang_not_found");
    exit();
}

$lelang = mysqli_fetch_assoc($queryLelang);

// Jika status lelang sudah 'Selesai', cegah konfirmasi ulang
if ($lelang['status'] === 'Selesai') {
    header("Location: ../penjual/detail_lelang.php?id=" . htmlspecialchars($lelang['id_sapi'] ?? '') . "&status=already_confirmed");
    exit();
}

// Proses konfirmasi jika form disubmit
if (isset($_POST['confirm_payment'])) {
    // Cek apakah status saat ini adalah 'Lewat' (berarti sudah berakhir tapi belum dikonfirmasi)
    if ($lelang['status'] === 'Lewat') {
        $updateQuery = "
            UPDATE lelang
            SET status = 'Selesai', updatedAt = NOW()
            WHERE id_lelang = '$id_lelang' AND id_penawaranTertinggi = '" . mysqli_real_escape_string($koneksi, $lelang['id_penawaranTertinggi']) . "'
        ";
        $updateResult = mysqli_query($koneksi, $updateQuery);

        if ($updateResult) {
            header("Location: ../penjual/detail_lelang.php?id=" . htmlspecialchars($lelang['id_sapi'] ?? '') . "&status=success_payment_confirmed");
            exit();
        } else {
            header("Location: ../penjual/detail_lelang.php?id=" . htmlspecialchars($lelang['id_sapi'] ?? '') . "&status=failed_db_update");
            exit();
        }
    } else {
        // Lelang tidak dalam status 'Lewat', tidak bisa dikonfirmasi
        header("Location: ../penjual/detail_lelang.php?id=" . htmlspecialchars($lelang['id_sapi'] ?? '') . "&status=cannot_confirm_yet");
        exit();
    }
}

// Harga yang dikonfirmasi diambil dari harga_tawaran di penawaran tertinggi atau harga_tertinggi di lelang
$confirmedPrice = $lelang['harga_tawaran'] ?? $lelang['harga_tertinggi'];

?>

<!DOCTYPE html>
<html>

<head>
    <title>Konfirmasi Pembayaran Lelang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../style.css">
    <style>
        .confirmation-box {
            max-width: 600px;
            margin: auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }

        .confirmation-box img {
            max-width: 150px;
            height: auto;
            border-radius: 5px;
            margin-bottom: 20px;
            display: block;
            margin-left: auto;
            margin-right: auto;
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
            </div>
        </nav>
    </header>

    <div class="container">
        <div class="confirmation-box text-center">
            <h3 class="mb-4">Konfirmasi Pembayaran Lelang</h3>
            <?php if (!empty($lelang['foto_sapi'])): ?>
                <img src="../uploads_sapi/<?= htmlspecialchars($lelang['foto_sapi']); ?>" alt="Foto Sapi">
            <?php endif; ?>
            <p>Anda akan mengkonfirmasi pembayaran untuk lelang:</p>
            <ul class="list-group list-group-flush mb-4">
                <li class="list-group-item"><strong>ID Lelang:</strong> <?= htmlspecialchars($lelang['id_lelang'] ?? ''); ?></li>
                <li class="list-group-item"><strong>Kategori Sapi:</strong> <?= htmlspecialchars($lelang['kategori_sapi'] ?? ''); ?></li>
                <li class="list-group-item"><strong>Harga Penawaran Tertinggi:</strong> Rp<?= number_format($confirmedPrice ?? 0); ?></li>
                <li class="list-group-item"><strong>Pemenang Lelang:</strong> <?= htmlspecialchars($lelang['nama_pemenang'] ?? 'N/A'); ?> (<?= htmlspecialchars($lelang['email_pemenang'] ?? 'N/A'); ?>)</li>
                <li class="list-group-item"><strong>Penjual:</strong> <?= htmlspecialchars($lelang['nama_penjual'] ?? 'N/A'); ?> (<?= htmlspecialchars($lelang['email_penjual'] ?? 'N/A'); ?>)</li>
                <li class="list-group-item"><strong>Status Saat Ini:</strong> <span class="badge bg-warning"><?= htmlspecialchars($lelang['status'] ?? 'N/A'); ?></span></li>
            </ul>

            <form method="POST">
                <p class="text-danger">**Peringatan:** Tindakan ini akan mengubah status lelang menjadi 'Selesai' dan tidak dapat diubah kembali. Pastikan pembayaran sudah diterima.</p>
                <button type="submit" name="confirm_payment" class="btn btn-success btn-lg mt-3">Konfirmasi Pembayaran Telah Diterima</button>
                <a href="../penjual/detail_lelang.php?id=<?= htmlspecialchars($lelang['id_sapi'] ?? ''); ?>" class="btn btn-secondary btn-lg mt-3">Batalkan</a>
            </form>
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
                    case 'success_payment_confirmed':
                        icon = 'success';
                        title = 'Konfirmasi Berhasil!';
                        text = 'Lelang telah berhasil dikonfirmasi sebagai "Selesai".';
                        break;
                    case 'failed_db_update':
                        icon = 'error';
                        title = 'Gagal Mengupdate Data!';
                        text = 'Terjadi kesalahan saat menyimpan konfirmasi ke database.';
                        break;
                    case 'lelang_not_found':
                        icon = 'error';
                        title = 'Lelang Tidak Ditemukan!';
                        text = 'Data lelang yang diminta tidak ada.';
                        break;
                    case 'already_confirmed':
                        icon = 'info';
                        title = 'Sudah Dikonfirmasi!';
                        text = 'Lelang ini sudah berstatus "Selesai" sebelumnya.';
                        break;
                    case 'cannot_confirm_yet':
                        icon = 'warning';
                        title = 'Tidak Dapat Dikonfirmasi!';
                        text = 'Lelang ini belum berakhir atau statusnya tidak valid untuk dikonfirmasi.';
                        break;
                    case 'invalid_data':
                        icon = 'warning';
                        title = 'Data Tidak Valid!';
                        text = 'ID lelang atau ID pemenang tidak ditemukan.';
                        break;
                    case 'unauthorized_access': // Status ini mungkin masih muncul jika ada redirect dari halaman lain
                        icon = 'error';
                        title = 'Akses Tidak Sah!';
                        text = 'Anda tidak memiliki izin untuk mengakses halaman ini.';
                        break;
                    default:
                        return; // Jangan lakukan apa-apa jika status tidak dikenal
                }

                Swal.fire({
                    icon: icon,
                    title: title,
                    text: text,
                    confirmButtonText: 'OK'
                }).then(() => {
                    // Redirect ke detail_lelang setelah menunjukkan alert, atau hapus jika ingin tetap di halaman ini
                    if (status !== 'success_payment_confirmed' && status !== 'already_confirmed') {
                        const currentUrl = window.location.href;
                        const cleanUrl = currentUrl.split('?')[0]; // Hapus query params untuk URL yang lebih bersih
                        window.history.replaceState({}, document.title, cleanUrl);
                    }
                });
            }
        });
    </script>
</body>

</html>