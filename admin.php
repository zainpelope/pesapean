<?php
include 'koneksi.php'; // Sesuaikan path ke file koneksi Anda

// --- Logika Update Status Lelang Otomatis ---
// Pastikan ini juga berjalan untuk admin agar data selalu up-to-date
mysqli_query($koneksi, "
    UPDATE lelang
    SET status = 'Lewat', updatedAt = NOW()
    WHERE batas_waktu < NOW() AND status = 'Aktif'
");

// --- Ambil Data Lelang yang Perlu Diverifikasi ---
// Kita akan ambil lelang yang statusnya 'Aktif' tapi batas waktunya sudah lewat,
// atau lelang yang statusnya 'Lewat' tapi belum memiliki pemenang (id_penawaranTertinggi belum final).
// Atau lelang yang aktif tapi ingin dilihat penawarannya
$query_lelang = mysqli_query($koneksi, "
    SELECT
        l.id_lelang,
        l.harga_awal,
        l.harga_tertinggi,
        l.batas_waktu,
        l.status,
        ds.foto_sapi,
        ds.nama_pemilik,
        ms.name AS kategori_sapi,
        COALESCE(p.harga_tawaran, l.harga_tertinggi) AS current_highest_bid_display,
        COALESCE(p.waktu_tawaran, l.updatedAt) AS highest_bid_time,
        p.id_penawaran AS id_penawaran_tertinggi_saat_ini,
        ds.id_sapi
    FROM lelang l
    INNER JOIN data_sapi ds ON l.id_sapi = ds.id_sapi
    INNER JOIN macamSapi ms ON ds.id_macamSapi = ms.id_macamSapi
    LEFT JOIN Penawaran p ON l.id_penawaranTertinggi = p.id_penawaran
    ORDER BY l.status DESC, l.batas_waktu ASC
");

// Pesan notifikasi setelah verifikasi
$message = '';
$alert_type = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'verified') {
        $message = 'Lelang berhasil diverifikasi dan pemenang telah ditetapkan!';
        $alert_type = 'success';
    } elseif ($_GET['status'] == 'no_bids') {
        $message = 'Lelang ini belum memiliki penawaran tertinggi untuk diverifikasi.';
        $alert_type = 'info';
    } elseif ($_GET['status'] == 'error') {
        $message = 'Terjadi kesalahan saat memverifikasi lelang.';
        $alert_type = 'danger';
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Verifikasi Lelang Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .lelang-card {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }

        .lelang-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .lelang-card .card-body {
            padding: 15px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            color: white;
        }

        .status-aktif {
            background-color: #28a745;
        }

        /* Green */
        .status-lewat {
            background-color: #ffc107;
        }

        /* Yellow */
        .status-terverifikasi {
            background-color: #007bff;
        }

        /* Blue */
    </style>
</head>

<body class="bg-light">
    <div class="container mt-5">
        <h2 class="mb-4 text-center">Verifikasi Lelang Sapi</h2>

        <?php if ($message): ?>
            <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <?php if (mysqli_num_rows($query_lelang) == 0): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center" role="alert">
                        Tidak ada lelang yang perlu diverifikasi atau saat ini sedang berlangsung.
                    </div>
                </div>
            <?php else: ?>
                <?php while ($lelang = mysqli_fetch_assoc($query_lelang)): ?>
                    <div class="col-md-4">
                        <div class="lelang-card shadow-sm">
                            <img src="../uploads/<?= htmlspecialchars($lelang['foto_sapi']) ?>" alt="Foto Sapi">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($lelang['kategori_sapi']) ?> oleh <?= htmlspecialchars($lelang['nama_pemilik']) ?></h5>
                                <p>Harga Awal: Rp<?= number_format($lelang['harga_awal']) ?></p>
                                <p>Harga Tertinggi: <strong>Rp<?= number_format($lelang['current_highest_bid_display']) ?></strong></p>
                                <p>Batas Waktu: <?= date("d M Y H:i", strtotime($lelang['batas_waktu'])) ?></p>
                                <p>Status:
                                    <span class="status-badge status-<?= strtolower(str_replace(' ', '', $lelang['status'])) ?>">
                                        <?= htmlspecialchars($lelang['status']) ?>
                                    </span>
                                </p>

                                <?php if ($lelang['status'] == 'Lewat' && $lelang['id_penawaran_tertinggi_saat_ini'] !== null): // Jika sudah lewat dan ada penawar 
                                ?>
                                    <p class="text-success">Siap Diverifikasi</p>
                                    <form action="proses_verifikasi.php" method="POST">
                                        <input type="hidden" name="id_lelang" value="<?= htmlspecialchars($lelang['id_lelang']) ?>">
                                        <input type="hidden" name="id_penawaran_tertinggi" value="<?= htmlspecialchars($lelang['id_penawaran_tertinggi_saat_ini']) ?>">
                                        <button type="submit" class="btn btn-primary btn-sm w-100"
                                            onclick="return confirm('Anda yakin ingin memverifikasi lelang ini dan menetapkan pemenang?')">Verifikasi Lelang Ini</button>
                                    </form>
                                <?php elseif ($lelang['status'] == 'Lewat' && $lelang['id_penawaran_tertinggi_saat_ini'] === null): // Jika sudah lewat tapi tidak ada penawaran 
                                ?>
                                    <p class="text-warning">Belum ada penawaran sah.</p>
                                    <button type="button" class="btn btn-secondary btn-sm w-100" disabled>Tidak Ada Pemenang</button>
                                <?php elseif ($lelang['status'] == 'Terverifikasi'): // Jika sudah terverifikasi 
                                ?>
                                    <p class="text-info">Lelang sudah diverifikasi.</p>
                                    <button type="button" class="btn btn-success btn-sm w-100" disabled>Sudah Diverifikasi</button>
                                <?php else: // Jika masih aktif 
                                ?>
                                    <p class="text-primary">Lelang masih aktif.</p>
                                    <button type="button" class="btn btn-info btn-sm w-100" disabled>Lelang Aktif</button>
                                <?php endif; ?>

                                <a href="detail_penawaran_lelang.php?id_lelang=<?= htmlspecialchars($lelang['id_lelang']) ?>" class="btn btn-outline-info btn-sm w-100 mt-2">Lihat Semua Penawaran</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
</body>

</html>