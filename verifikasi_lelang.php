<?php
include 'koneksi.php'; // Sesuaikan path ke file koneksi Anda

// --- Logika Update Status Lelang Otomatis ---
// Memperbarui status lelang yang batas waktunya sudah lewat menjadi 'Lewat'
// Menggunakan prepared statement untuk keamanan, meskipun tidak ada input user langsung
$stmt_auto_update = mysqli_prepare($koneksi, "
    UPDATE lelang
    SET status = 'Lewat', updatedAt = NOW()
    WHERE batas_waktu < NOW() AND status = 'Aktif'
");
if ($stmt_auto_update) {
    mysqli_stmt_execute($stmt_auto_update);
    mysqli_stmt_close($stmt_auto_update);
} else {
    // Log error jika prepared statement gagal (ini jarang terjadi untuk query statis)
    error_log("Error preparing auto-update query: " . mysqli_error($koneksi));
}


// --- Ambil Data Lelang yang Perlu Diverifikasi ---
// Mengambil data lelang beserta informasi sapi dan penawaran tertinggi
$query_lelang_sql = "
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
        l.id_penawaranTertinggi AS id_penawaran_tertinggi_saat_ini, -- Kolom yang menyimpan ID penawaran tertinggi
        ds.id_sapi
    FROM lelang l
    INNER JOIN data_sapi ds ON l.id_sapi = ds.id_sapi
    INNER JOIN macamSapi ms ON ds.id_macamSapi = ms.id_macamSapi
    LEFT JOIN Penawaran p ON l.id_penawaranTertinggi = p.id_penawaran
    ORDER BY l.status DESC, l.batas_waktu ASC
";

$query_lelang = mysqli_query($koneksi, $query_lelang_sql);

// Pesan notifikasi setelah verifikasi (dari parameter URL 'status')
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
        $message = 'Terjadi kesalahan saat memverifikasi lelang. Silakan periksa log server untuk detailnya.';
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
        /* Gaya umum untuk body */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            /* Warna latar belakang terang */
        }

        /* Gaya untuk kartu lelang */
        .lelang-card {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .lelang-card:hover {
            transform: translateY(-5px);
            /* Efek angkat saat hover */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            /* Efek bayangan saat hover */
        }

        .lelang-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            /* Memastikan gambar mengisi area tanpa distorsi */
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        .lelang-card .card-body {
            padding: 15px;
        }

        /* Gaya untuk badge status */
        .status-badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            color: white;
            display: inline-block;
            /* Agar bisa diatur padding/margin */
        }

        .status-aktif {
            background-color: #28a745;
            /* Hijau */
        }

        .status-lewat {
            background-color: #ffc107;
            /* Kuning */
            color: #333;
            /* Warna teks lebih gelap untuk kontras */
        }

        .status-terverifikasi {
            background-color: #007bff;
            /* Biru */
        }

        /* Gaya untuk tombol */
        .btn-sm.w-100 {
            border-radius: 5px;
            padding: 8px 15px;
            font-size: 0.9rem;
        }
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
                            <img src="uploads/<?= htmlspecialchars($lelang['foto_sapi']) ?>" alt="Foto Sapi">
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

                                <?php
                                // Logic untuk menampilkan tombol verifikasi atau status
                                $can_verify = ($lelang['status'] == 'Lewat' && $lelang['id_penawaran_tertinggi_saat_ini'] !== null);
                                $no_valid_bid = ($lelang['status'] == 'Lewat' && $lelang['id_penawaran_tertinggi_saat_ini'] === null);
                                $is_verified = ($lelang['status'] == 'Terverifikasi');
                                $is_active = ($lelang['status'] == 'Aktif');

                                if ($can_verify): ?>
                                    <p class="text-success">Siap Diverifikasi</p>
                                    <form action="proses_verifikasi.php" method="POST">
                                        <input type="hidden" name="id_lelang" value="<?= htmlspecialchars($lelang['id_lelang']) ?>">
                                        <input type="hidden" name="id_penawaran_tertinggi" value="<?= htmlspecialchars($lelang['id_penawaran_tertinggi_saat_ini']) ?>">
                                        <button type="submit" class="btn btn-primary btn-sm w-100"
                                            onclick="return confirm('Anda yakin ingin memverifikasi lelang ini dan menetapkan pemenang?')">Verifikasi Lelang Ini</button>
                                    </form>
                                <?php elseif ($no_valid_bid): ?>
                                    <p class="text-warning">Belum ada penawaran sah.</p>
                                    <button type="button" class="btn btn-secondary btn-sm w-100" disabled>Tidak Ada Pemenang</button>
                                <?php elseif ($is_verified): ?>
                                    <p class="text-info">Lelang sudah diverifikasi.</p>
                                    <button type="button" class="btn btn-success btn-sm w-100" disabled>Sudah Diverifikasi</button>
                                <?php elseif ($is_active): ?>
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