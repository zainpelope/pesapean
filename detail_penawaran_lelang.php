<?php
// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Mulai sesi
session_start();

// Sertakan file koneksi database
// Path diubah menjadi 'koneksi.php' karena detail_penawaran_lelang.php
// tampaknya berada di direktori yang sama dengan koneksi.php (misal: pesapean/)
include 'koneksi.php';

// Ambil ID lelang dari parameter GET
$id_lelang = isset($_GET['id_lelang']) ? (int)$_GET['id_lelang'] : 0;

// Redirect jika id_lelang tidak valid
if ($id_lelang === 0) {
    header("Location: lelang.php?error=ID lelang tidak valid.");
    exit();
}

$lelang_data = null;
$riwayat_penawaran = [];
$pesan_error = '';
$pesan_sukses = '';

// Query untuk mengambil detail lelang
$query_lelang = "
    SELECT
        l.id_lelang,
        l.id_sapi,
        l.harga_awal,
        l.harga_tertinggi,
        l.batas_waktu,
        l.status,
        l.id_user AS lelang_creator_id, -- ID user yang membuat lelang
        ds.foto_sapi,
        ds.nama_pemilik,
        ds.nomor_pemilik,
        ms.name AS kategori_sapi,
        ds.jenis_kelamin,
        ds.alamat_pemilik
    FROM lelang l
    INNER JOIN data_sapi ds ON l.id_sapi = ds.id_sapi
    INNER JOIN macamSapi ms ON ds.id_macamSapi = ms.id_macamSapi
    WHERE l.id_lelang = ?
";

$stmt_lelang = mysqli_prepare($koneksi, $query_lelang);
if ($stmt_lelang) {
    mysqli_stmt_bind_param($stmt_lelang, "i", $id_lelang);
    mysqli_stmt_execute($stmt_lelang);
    $result_lelang = mysqli_stmt_get_result($stmt_lelang);
    $lelang_data = mysqli_fetch_assoc($result_lelang);
    mysqli_stmt_close($stmt_lelang);
} else {
    $pesan_error = "Gagal mengambil data lelang: " . mysqli_error($koneksi);
}

// Jika lelang tidak ditemukan
if (!$lelang_data) {
    $pesan_error = "Lelang tidak ditemukan.";
}

// Proses penawaran baru jika ada POST request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_penawaran'])) {
    if (!isset($_SESSION['id_user'])) {
        $pesan_error = "Anda harus login untuk melakukan penawaran.";
    } else {
        $id_user_penawar = $_SESSION['id_user'];
        $jumlah_penawaran = $_POST['jumlah_penawaran'];
        $waktu_penawaran = date('Y-m-d H:i:s');

        // Validasi penawaran
        if ($id_user_penawar == $lelang_data['lelang_creator_id']) {
            $pesan_error = "Anda tidak bisa menawar lelang yang Anda buat sendiri.";
        } elseif ($jumlah_penawaran <= $lelang_data['harga_tertinggi']) {
            $pesan_error = "Penawaran Anda harus lebih tinggi dari harga tertinggi saat ini (Rp" . number_format($lelang_data['harga_tertinggi'], 0, ',', '.') . ").";
        } elseif (strtotime($lelang_data['batas_waktu']) < time()) {
            $pesan_error = "Lelang ini sudah berakhir.";
        } elseif ($lelang_data['status'] !== 'Aktif' && $lelang_data['status'] !== 'Sedang Berlangsung') {
            $pesan_error = "Lelang ini tidak dalam status aktif untuk penawaran.";
        } else {
            // Mulai transaksi
            mysqli_begin_transaction($koneksi);

            try {
                // 1. Simpan penawaran ke tabel penawaran_lelang
                $query_insert_penawaran = "INSERT INTO penawaran_lelang (id_lelang, id_user, jumlah_penawaran, waktu_penawaran) VALUES (?, ?, ?, ?)";
                $stmt_insert_penawaran = mysqli_prepare($koneksi, $query_insert_penawaran);
                if (!$stmt_insert_penawaran) {
                    throw new Exception("Gagal menyiapkan statement penawaran: " . mysqli_error($koneksi));
                }
                mysqli_stmt_bind_param($stmt_insert_penawaran, "iiis", $id_lelang, $id_user_penawar, $jumlah_penawaran, $waktu_penawaran);
                if (!mysqli_stmt_execute($stmt_insert_penawaran)) {
                    throw new Exception("Gagal menyimpan penawaran: " . mysqli_error($koneksi));
                }
                mysqli_stmt_close($stmt_insert_penawaran);

                // 2. Update harga_tertinggi dan id_penawaranTertinggi di tabel lelang
                // Asumsi id_penawaranTertinggi di tabel lelang akan menyimpan id_user dari penawar tertinggi
                $query_update_lelang = "UPDATE lelang SET harga_tertinggi = ?, id_penawaranTertinggi = ?, updatedAt = NOW() WHERE id_lelang = ?";
                $stmt_update_lelang = mysqli_prepare($koneksi, $query_update_lelang);
                if (!$stmt_update_lelang) {
                    throw new Exception("Gagal menyiapkan statement update lelang: " . mysqli_error($koneksi));
                }
                mysqli_stmt_bind_param($stmt_update_lelang, "iii", $jumlah_penawaran, $id_user_penawar, $id_lelang);
                if (!mysqli_stmt_execute($stmt_update_lelang)) {
                    throw new Exception("Gagal mengupdate lelang: " . mysqli_error($koneksi));
                }
                mysqli_stmt_close($stmt_update_lelang);

                mysqli_commit($koneksi); // Commit transaksi
                $pesan_sukses = "Penawaran Anda sebesar Rp" . number_format($jumlah_penawaran, 0, ',', '.') . " berhasil disimpan.";

                // Refresh data lelang setelah penawaran berhasil
                $stmt_lelang = mysqli_prepare($koneksi, $query_lelang);
                if ($stmt_lelang) {
                    mysqli_stmt_bind_param($stmt_lelang, "i", $id_lelang);
                    mysqli_stmt_execute($stmt_lelang);
                    $result_lelang = mysqli_stmt_get_result($stmt_lelang);
                    $lelang_data = mysqli_fetch_assoc($result_lelang);
                    mysqli_stmt_close($stmt_lelang);
                }
            } catch (Exception $e) {
                mysqli_rollback($koneksi); // Rollback transaksi jika ada error
                $pesan_error = "Terjadi kesalahan saat memproses penawaran: " . $e->getMessage();
            }
        }
    }
}

// Query untuk mengambil riwayat penawaran
$query_riwayat = "
    SELECT
        pl.jumlah_penawaran,
        pl.waktu_penawaran,
        u.username,
        u.nama_lengkap
    FROM penawaran_lelang pl
    INNER JOIN users u ON pl.id_user = u.id_user
    WHERE pl.id_lelang = ?
    ORDER BY pl.jumlah_penawaran DESC, pl.waktu_penawaran DESC
";

$stmt_riwayat = mysqli_prepare($koneksi, $query_riwayat);
if ($stmt_riwayat) {
    mysqli_stmt_bind_param($stmt_riwayat, "i", $id_lelang);
    mysqli_stmt_execute($stmt_riwayat);
    $result_riwayat = mysqli_stmt_get_result($stmt_riwayat);
    while ($row = mysqli_fetch_assoc($result_riwayat)) {
        $riwayat_penawaran[] = $row;
    }
    mysqli_stmt_close($stmt_riwayat);
} else {
    $pesan_error = "Gagal mengambil riwayat penawaran: " . mysqli_error($koneksi);
}

// Hitung sisa waktu
$sisa_waktu_detik = 0;
if ($lelang_data && strtotime($lelang_data['batas_waktu']) > time()) {
    $sisa_waktu_detik = strtotime($lelang_data['batas_waktu']) - time();
}

// Fungsi untuk format sisa waktu
function formatSisaWaktu($detik)
{
    if ($detik <= 0) return "Lelang Selesai";
    $hari = floor($detik / (60 * 60 * 24));
    $detik %= (60 * 60 * 24);
    $jam = floor($detik / (60 * 60));
    $detik %= (60 * 60);
    $menit = floor($detik / 60);
    $detik %= 60;
    return sprintf("%d hari, %02d jam, %02d menit, %02d detik", $hari, $jam, $menit, $detik);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Lelang Sapi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" xintegrity="sha512-pFQhV+Cq+BfS2Z2v2E2L2R2/2N2P2g2B2D2G2H2I2J2K2L2M2N2O2P2Q2R2S2T2U2V2W2X2Y2Z2==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        :root {
            --primary-color: rgb(240, 161, 44);
            /* Orange-brown */
            --secondary-color: rgb(48, 52, 56);
            /* Dark Grey */
            --tertiary-color: #6c757d;
            /* Grey */
            --dark-color: #333;
            /* Dark text */
            --dark-text: #212529;
            --light-bg: #f8f9fa;
            --white-bg: #ffffff;
            --border-color: #dee2e6;
            --box-shadow-light: 0 4px 15px rgba(0, 0, 0, 0.08);
            --box-shadow-medium: 0 8px 25px rgba(0, 0, 0, 0.15);
            --border-radius-sm: 8px;
            --border-radius-md: 10px;
            --border-radius-lg: 12px;
            --success-color: #28a745;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Open Sans', sans-serif;
        }

        .container {
            max-width: 960px;
            margin-top: 30px;
            margin-bottom: 30px;
            background-color: var(--white-bg);
            padding: 30px;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--box-shadow-medium);
        }

        .card-img-top-container {
            width: 100%;
            padding-top: 75%;
            /* 4:3 Aspect Ratio */
            position: relative;
            background-color: var(--light-bg);
            border-radius: var(--border-radius-md);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .card-img-top {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .no-image-placeholder {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: var(--light-bg);
            color: var(--tertiary-color);
            font-size: 1.5rem;
            text-align: center;
        }

        h2 {
            color: var(--primary-color);
            margin-bottom: 25px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
        }

        .detail-item {
            margin-bottom: 10px;
        }

        .detail-item strong {
            color: var(--secondary-color);
        }

        .price-tag {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-top: 15px;
            margin-bottom: 20px;
        }

        .status-badge {
            font-size: 1.1rem;
            padding: 8px 15px;
            border-radius: var(--border-radius-sm);
            font-weight: 600;
        }

        .status-Aktif {
            background-color: var(--success-color);
            color: white;
        }

        .status-Sedang.Berlangsung {
            background-color: var(--warning-color);
            color: var(--dark-text);
        }

        .status-Lewat {
            background-color: var(--danger-color);
            color: white;
        }

        .bid-form,
        .bid-history {
            background-color: var(--light-bg);
            padding: 20px;
            border-radius: var(--border-radius-md);
            box-shadow: var(--box-shadow-light);
            margin-top: 30px;
        }

        .bid-history ul {
            list-style: none;
            padding: 0;
        }

        .bid-history ul li {
            padding: 10px 0;
            border-bottom: 1px dashed var(--border-color);
        }

        .bid-history ul li:last-child {
            border-bottom: none;
        }

        .bid-history ul li strong {
            color: var(--primary-color);
        }

        .bid-history ul li span {
            font-size: 0.9em;
            color: var(--tertiary-color);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #d09050;
            border-color: #d09050;
        }

        .alert-custom {
            margin-top: 20px;
            padding: 15px;
            border-radius: var(--border-radius-md);
        }
    </style>
</head>

<body>
    <div class="container">
        <?php if ($pesan_error): ?>
            <div class="alert alert-danger alert-custom" role="alert">
                <?= htmlspecialchars($pesan_error); ?>
            </div>
        <?php endif; ?>

        <?php if ($pesan_sukses): ?>
            <div class="alert alert-success alert-custom" role="alert">
                <?= htmlspecialchars($pesan_sukses); ?>
            </div>
        <?php endif; ?>

        <?php if ($lelang_data): ?>
            <h2 class="text-center mb-4">Detail Lelang Sapi</h2>
            <div class="row">
                <div class="col-md-6">
                    <div class="card-img-top-container">
                        <?php if (!empty($lelang_data['foto_sapi']) && file_exists("../uploads_sapi/{$lelang_data['foto_sapi']}")): ?>
                            <img src="../uploads_sapi/<?= htmlspecialchars($lelang_data['foto_sapi']); ?>" class="card-img-top" alt="Foto Sapi <?= htmlspecialchars($lelang_data['kategori_sapi']); ?>">
                        <?php else: ?>
                            <div class="no-image-placeholder">
                                <i class="fas fa-image"></i><span>Tidak ada foto</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <h3 class="mb-3 text-secondary"><?= htmlspecialchars($lelang_data['kategori_sapi']); ?></h3>
                    <div class="detail-item">
                        <strong>Pemilik:</strong> <?= htmlspecialchars($lelang_data['nama_pemilik']); ?>
                    </div>
                    <div class="detail-item">
                        <strong>Alamat:</strong> <?= htmlspecialchars($lelang_data['alamat_pemilik']); ?>
                    </div>
                    <div class="detail-item">
                        <strong>Kontak:</strong> <?= htmlspecialchars($lelang_data['nomor_pemilik']); ?>
                    </div>
                    <div class="detail-item">
                        <strong>Jenis Kelamin:</strong> <?= htmlspecialchars(ucfirst($lelang_data['jenis_kelamin'])); ?>
                    </div>
                    <div class="detail-item">
                        <strong>Harga Awal:</strong> Rp<?= number_format($lelang_data['harga_awal'], 0, ',', '.'); ?>
                    </div>
                    <div class="detail-item">
                        <strong>Harga Tertinggi Saat Ini:</strong>
                        <span class="price-tag">Rp<?= number_format($lelang_data['harga_tertinggi'], 0, ',', '.'); ?></span>
                    </div>
                    <div class="detail-item">
                        <strong>Batas Waktu:</strong> <?= date('d M Y H:i:s', strtotime($lelang_data['batas_waktu'])); ?>
                    </div>
                    <div class="detail-item">
                        <strong>Sisa Waktu:</strong> <span id="sisa-waktu"><?= formatSisaWaktu($sisa_waktu_detik); ?></span>
                    </div>
                    <div class="detail-item">
                        <strong>Status Lelang:</strong>
                        <span class="badge status-badge status-<?= strtolower(str_replace(' ', '.', $lelang_data['status'])); ?>">
                            <?= htmlspecialchars($lelang_data['status']); ?>
                        </span>
                    </div>

                    <?php
                    // Tautan WhatsApp ke pemilik lelang
                    $wa_number = preg_replace('/[^0-9]/', '', $lelang_data['nomor_pemilik'] ?? '');
                    if (!empty($wa_number) && substr($wa_number, 0, 1) === '0') {
                        $wa_number = '62' . substr($wa_number, 1);
                    }
                    if (!empty($wa_number)) {
                        echo '<a href="https://wa.me/' . htmlspecialchars($wa_number) . '" class="btn btn-success mt-3 w-100" target="_blank">';
                        echo '<i class="fab fa-whatsapp me-2"></i> Hubungi Penjual';
                        echo '</a>';
                    }
                    ?>
                </div>
            </div>

            <?php
            // Tampilkan formulir penawaran hanya jika user login, bukan pemilik lelang, dan lelang masih aktif
            $is_logged_in = isset($_SESSION['id_user']);
            $is_owner = $is_logged_in && $_SESSION['id_user'] == $lelang_data['lelang_creator_id'];
            $is_auction_active = (strtotime($lelang_data['batas_waktu']) > time() && ($lelang_data['status'] === 'Aktif' || $lelang_data['status'] === 'Sedang Berlangsung'));

            if ($is_logged_in && !$is_owner && $is_auction_active):
            ?>
                <div class="bid-form mt-4">
                    <h4 class="text-primary mb-3">Lakukan Penawaran Anda</h4>
                    <form method="POST" action="">
                        <input type="hidden" name="id_lelang" value="<?= htmlspecialchars($id_lelang); ?>">
                        <div class="mb-3">
                            <label for="jumlah_penawaran" class="form-label">Jumlah Penawaran (Rp)</label>
                            <input type="number" name="jumlah_penawaran" id="jumlah_penawaran" class="form-control"
                                min="<?= $lelang_data['harga_tertinggi'] + 1; ?>"
                                placeholder="Minimal Rp<?= number_format($lelang_data['harga_tertinggi'] + 1, 0, ',', '.'); ?>"
                                required>
                        </div>
                        <button type="submit" name="submit_penawaran" class="btn btn-primary w-100">Kirim Penawaran</button>
                    </form>
                </div>
            <?php elseif ($is_owner): ?>
                <div class="alert alert-info alert-custom mt-4 text-center">
                    Anda adalah pemilik lelang ini. Anda tidak dapat melakukan penawaran.
                </div>
            <?php elseif (!$is_logged_in): ?>
                <div class="alert alert-warning alert-custom mt-4 text-center">
                    Silakan <a href="../auth/login.php">login</a> untuk melakukan penawaran.
                </div>
            <?php elseif (!$is_auction_active): ?>
                <div class="alert alert-danger alert-custom mt-4 text-center">
                    Lelang ini sudah berakhir atau tidak aktif untuk penawaran.
                </div>
            <?php endif; ?>

            <div class="bid-history mt-4">
                <h4 class="text-primary mb-3">Riwayat Penawaran</h4>
                <?php if (!empty($riwayat_penawaran)): ?>
                    <ul class="list-unstyled">
                        <?php foreach ($riwayat_penawaran as $penawaran): ?>
                            <li>
                                <strong><?= htmlspecialchars($penawaran['nama_lengkap'] ?? $penawaran['username']); ?>:</strong>
                                Rp<?= number_format($penawaran['jumlah_penawaran'], 0, ',', '.'); ?>
                                <span>(<?= date('d M Y H:i:s', strtotime($penawaran['waktu_penawaran'])); ?>)</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted text-center">Belum ada penawaran untuk lelang ini.</p>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <div class="alert alert-danger alert-custom text-center" role="alert">
                Detail lelang tidak dapat dimuat.
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Script untuk update sisa waktu secara real-time
        function updateCountdown() {
            const sisaWaktuElement = document.getElementById('sisa-waktu');
            if (!sisaWaktuElement) return;

            let sisaDetik = <?= $sisa_waktu_detik; ?>;

            const interval = setInterval(() => {
                sisaDetik--;
                if (sisaDetik <= 0) {
                    sisaWaktuElement.textContent = "Lelang Selesai";
                    clearInterval(interval);
                    // Opsional: nonaktifkan form penawaran jika lelang selesai
                    const bidForm = document.querySelector('.bid-form form');
                    if (bidForm) {
                        bidForm.querySelectorAll('input, button').forEach(el => el.disabled = true);
                    }
                } else {
                    const hari = Math.floor(sisaDetik / (60 * 60 * 24));
                    const jam = Math.floor((sisaDetik % (60 * 60 * 24)) / (60 * 60));
                    const menit = Math.floor((sisaDetik % (60 * 60)) / 60);
                    const detik = sisaDetik % 60;
                    sisaWaktuElement.textContent = `${hari} hari, ${String(jam).padStart(2, '0')} jam, ${String(menit).padStart(2, '0')} menit, ${String(detik).padStart(2, '0')} detik`;
                }
            }, 1000);
        }

        // Jalankan countdown saat halaman dimuat
        window.onload = updateCountdown;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>