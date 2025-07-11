<?php
include '../koneksi.php';

// Aktifkan pelaporan error
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
$query_penawaran = mysqli_query($koneksi, "
    SELECT
        p.harga_tawaran,
        p.waktu_tawaran
        -- Jika ada kolom id_user di tabel Penawaran, bisa di-join ke tabel users
        -- u.username AS nama_penawar
    FROM Penawaran p
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
        .detail-card {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            border-radius: 8px;
            background-color: #fff;
        }

        .detail-card img {
            max-width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="detail-card shadow-sm">
            <h3 class="mb-4 text-center">Detail Penawaran untuk Lelang</h3>

            <img src="../uploads/<?= htmlspecialchars($lelang_detail['foto_sapi']) ?>" alt="Foto Sapi">

            <p><strong>Kategori Sapi:</strong> <?= htmlspecialchars($lelang_detail['kategori_sapi']) ?></p>
            <p><strong>Nama Pemilik:</strong> <?= htmlspecialchars($lelang_detail['nama_pemilik']) ?></p>
            <p><strong>Harga Awal:</strong> Rp<?= number_format($lelang_detail['harga_awal']) ?></p>
            <p><strong>Harga Tertinggi Saat Ini:</strong> Rp<?= number_format($lelang_detail['harga_tertinggi']) ?></p>
            <p><strong>Batas Waktu:</strong> <?= date("d M Y H:i", strtotime($lelang_detail['batas_waktu'])) ?></p>
            <p><strong>Status Lelang:</strong> <?= htmlspecialchars($lelang_detail['status']) ?></p>

            <hr>

            <h4>Daftar Penawaran:</h4>
            <?php if (mysqli_num_rows($query_penawaran) > 0): ?>
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Harga Tawaran</th>
                            <th>Waktu Tawaran</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($penawaran = mysqli_fetch_assoc($query_penawaran)): ?>
                            <tr>
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