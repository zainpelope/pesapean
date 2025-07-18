<?php
// Pastikan path ke koneksi.php sudah benar
include 'koneksi.php';

// Aktifkan pelaporan error
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Mulai sesi (jika diperlukan untuk admin, misalnya untuk cek login admin)
// session_start(); 

// Cek apakah id_lelang ada di URL
if (!isset($_GET['id_lelang'])) {
    echo "ID Lelang tidak ditemukan.";
    exit;
}

$id_lelang = mysqli_real_escape_string($koneksi, $_GET['id_lelang']);

// Ambil detail lelang
$query_lelang_detail = mysqli_query($koneksi, "
    SELECT
        l.id_lelang,
        l.harga_awal,
        l.harga_tertinggi,
        l.batas_waktu,
        l.status,
        ds.foto_sapi,
        ds.nama_pemilik,
        ms.name AS kategori_sapi
    FROM lelang l
    INNER JOIN data_sapi ds ON l.id_sapi = ds.id_sapi
    INNER JOIN macamSapi ms ON ds.id_macamSapi = ms.id_macamSapi
    WHERE l.id_lelang = '$id_lelang'
");
$lelang_detail = mysqli_fetch_assoc($query_lelang_detail);

if (!$lelang_detail) {
    echo "Lelang tidak ditemukan.";
    exit;
}

// Ambil semua penawaran untuk lelang ini
// *** NAMA TABEL DI SINI HARUS 'Penawaran' SESUAI SCREENSHOT ANDA ***
$query_penawaran = mysqli_query($koneksi, "
    SELECT
        p.harga_tawaran,
        p.waktu_tawaran,
        u.username AS nama_penawar
    FROM Penawaran p  -- PASTIKAN INI ADALAH 'penawaran' (dengan P kapital)
    INNER JOIN users u ON p.id_user = u.id_user 
    WHERE p.id_lelang = '$id_lelang'
    ORDER BY p.harga_tawaran DESC, p.waktu_tawaran ASC
");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Detail Penawaran Lelang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .detail-card {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .detail-card img {
            max-width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .table thead th {
            background-color: #f2f2f2;
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
    </style>
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="detail-card shadow-sm">
            <h3 class="mb-4 text-center">Detail Penawaran untuk Lelang</h3>

            <img src="uploads/<?= htmlspecialchars($lelang_detail['foto_sapi']) ?>" alt="Foto Sapi">

            <p><strong>Kategori Sapi:</strong> <?= htmlspecialchars($lelang_detail['kategori_sapi']) ?></p>
            <p><strong>Nama Pemilik:</strong> <?= htmlspecialchars($lelang_detail['nama_pemilik']) ?></p>
            <p><strong>Harga Awal:</strong> Rp<?= number_format($lelang_detail['harga_awal']) ?></p>
            <p><strong>Harga Tertinggi Saat Ini:</strong> Rp<?= number_format($lelang_detail['harga_tertinggi']) ?></p>
            <p><strong>Batas Waktu:</strong> <?= date("d M Y H:i", strtotime($lelang_detail['batas_waktu'])) ?></p>
            <p><strong>Status Lelang:</strong>
                <span class="status-badge <?= strtolower($lelang_detail['status']) ?>">
                    <?= htmlspecialchars($lelang_detail['status']) ?>
                </span>
            </p>

            <hr>

            <h4>Daftar Penawaran:</h4>
            <?php if (mysqli_num_rows($query_penawaran) > 0): ?>
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Penawar</th>
                            <th>Harga Tawaran</th>
                            <th>Waktu Tawaran</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($penawaran = mysqli_fetch_assoc($query_penawaran)): ?>
                            <tr>
                                <td><?= htmlspecialchars($penawaran['nama_penawar']) ?></td>
                                <td>Rp<?= number_format($penawaran['harga_tawaran']) ?></td>
                                <td><?= date("d M Y H:i:s", strtotime($penawaran['waktu_tawaran'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-warning text-center">Belum ada penawaran untuk lelang ini.</div>
            <?php endif; ?>

            <div class="text-center mt-4">
                <a href="verifikasi_lelang.php" class="btn btn-secondary">Kembali ke Daftar Verifikasi</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>