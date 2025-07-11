<?php
include '../koneksi.php';

// Pastikan id_sapi ada di URL
if (!isset($_GET['id'])) {
    echo "ID sapi tidak ditemukan.";
    exit;
}

$id_sapi = $_GET['id'];

// Update otomatis status jadi 'Lewat' jika sudah melewati batas waktu
// Ini sebaiknya juga ada di lelang.php, namun juga aman jika ada di sini
mysqli_query($koneksi, "
    UPDATE lelang
    SET status = 'Lewat', updatedAt = NOW()
    WHERE batas_waktu < NOW() AND status = 'Aktif'
");

// Ambil data detail sapi + lelang
$query = mysqli_query($koneksi, "
    SELECT
        ds.*,
        ms.name AS kategori,
        l.id_lelang, /* Ambil id_lelang untuk form penawaran */
        l.harga_awal,
        l.harga_tertinggi,
        l.status,
        l.batas_waktu
    FROM data_sapi ds
    INNER JOIN macamSapi ms ON ds.id_macamSapi = ms.id_macamSapi
    INNER JOIN lelang l ON ds.id_sapi = l.id_sapi
    WHERE ds.id_sapi = '" . mysqli_real_escape_string($koneksi, $id_sapi) . "'
");

// Cek apakah data sapi ditemukan
if (mysqli_num_rows($query) == 0) {
    echo "Data sapi tidak ditemukan.";
    exit;
}

$sapi = mysqli_fetch_assoc($query);

// Cek apakah lelang masih aktif berdasarkan status dan batas waktu
$lelang_aktif = ($sapi['status'] == 'Aktif' && strtotime($sapi['batas_waktu']) > time());

?>

<!DOCTYPE html>
<html>

<head>
    <title>Detail Sapi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
            <img src="../uploads/<?= htmlspecialchars($sapi['foto_sapi']); ?>" alt="Foto sapi">

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

            <?php if ($lelang_aktif) : ?>
                <hr>
                <h5 class="mt-4">Ikuti Lelang Ini</h5>
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
                <div class="alert alert-info text-center mt-4">
                    Lelang ini sudah berakhir atau tidak aktif.
                </div>
            <?php endif; ?>

            <div class="text-center mt-4">
                <a href="../pembeli/lelang.php" class="btn btn-secondary">Kembali ke Daftar</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        // Logika untuk menampilkan SweetAlert
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
                    default:
                        return; // Jangan tampilkan apa-apa jika status tidak dikenal
                }

                Swal.fire({
                    icon: icon,
                    title: title,
                    text: text,
                    confirmButtonText: 'OK'
                }).then(() => {
                    // Hapus parameter status dari URL setelah notifikasi ditampilkan untuk menghindari tampil berulang
                    const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + window.location.hash;
                    window.history.replaceState({
                        path: newUrl
                    }, '', newUrl);
                });
            }
        });
    </script>
</body>

</html>