<?php
include 'koneksi.php';

if (!isset($_GET['id'])) {
    echo "ID sapi tidak ditemukan.";
    exit;
}

$id_sapi = $_GET['id'];

// Ambil data detail sapi + lelang
$query = mysqli_query($koneksi, "
    SELECT 
        ds.*,
        ms.name AS kategori,
        l.harga_awal,
        l.harga_tertinggi,
        l.status,
        l.batas_waktu
    FROM data_sapi ds
    INNER JOIN macamSapi ms ON ds.id_macamSapi = ms.id_macamSapi
    INNER JOIN lelang l ON ds.id_sapi = l.id_sapi
    WHERE ds.id_sapi = '$id_sapi'
");

if (mysqli_num_rows($query) == 0) {
    echo "Data sapi tidak ditemukan.";
    exit;
}

$sapi = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Detail Sapi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .detail-box {
            max-width: 700px;
            margin: auto;
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
        }

        .detail-box img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 10px;
        }

        .label {
            font-weight: bold;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="detail-box shadow">
            <h3 class="mb-4 text-center">Detail Sapi Lelang</h3>
            <img src="uploads/<?= htmlspecialchars($sapi['foto_sapi']); ?>" alt="Foto sapi">

            <div class="mt-4">
                <p><span class="label">Kategori:</span> <?= htmlspecialchars($sapi['kategori']); ?></p>
                <p><span class="label">Nama Pemilik:</span> <?= htmlspecialchars($sapi['nama_pemilik']); ?></p>
                <p><span class="label">Alamat Pemilik:</span> <?= htmlspecialchars($sapi['alamat_pemilik']); ?></p>
                <p><span class="label">Nomor Pemilik:</span> <?= htmlspecialchars($sapi['nomor_pemilik']); ?></p>
                <p><span class="label">Email Pemilik:</span> <?= htmlspecialchars($sapi['email_pemilik']); ?></p>
                <p><span class="label">Status Lelang:</span>
                    <span class="<?= strtolower($sapi['status']) == 'aktif' ? 'text-success' : (strtolower($sapi['status']) == 'lewat' ? 'text-danger' : 'text-warning'); ?>">
                        <?= htmlspecialchars($sapi['status']); ?>
                    </span>
                </p>
                <p><span class="label">Nilai Limit:</span> Rp<?= number_format($sapi['harga_awal']); ?></p>
                <p><span class="label">Uang Jaminan (Harga Tertinggi):</span> Rp<?= number_format($sapi['harga_tertinggi']); ?></p>
                <p><span class="label">Batas Waktu:</span> <?= date("d M Y H:i", strtotime($sapi['batas_waktu'])); ?></p>
            </div>

            <div class="text-center mt-4">
                <a href="coba.php" class="btn btn-secondary">Kembali ke Daftar</a>
            </div>
        </div>
    </div>
</body>

</html>