<?php
// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Mulai sesi (PENTING: Harus di paling atas)
session_start();

include '../koneksi.php'; // Pastikan path ini benar sesuai struktur folder Anda

// Cek apakah user sudah login
if (!isset($_SESSION['id_user'])) {
    $_SESSION['payment_status'] = 'not_logged_in';
    $_SESSION['payment_message'] = 'Anda harus login untuk mengakses halaman pembayaran.';
    header("Location: ../login.php");
    exit;
}

$loggedInUserId = $_SESSION['id_user'];

// Pastikan id_lelang ada di URL
if (!isset($_GET['id_lelang'])) {
    echo "ID Lelang tidak ditemukan.";
    exit;
}

$id_lelang = $_GET['id_lelang'];

// Ambil data detail lelang untuk pembayaran
$query = mysqli_query($koneksi, "
    SELECT
        ds.foto_sapi,
        ms.name AS kategori,
        l.id_lelang,
        l.harga_tertinggi,
        l.status,
        l.batas_waktu,
        u.username AS nama_penawar_tertinggi,
        u.id_user AS id_penawar_tertinggi
    FROM lelang l
    INNER JOIN data_sapi ds ON l.id_sapi = ds.id_sapi
    INNER JOIN macamSapi ms ON ds.id_macamSapi = ms.id_macamSapi
    LEFT JOIN Penawaran p ON l.id_penawaranTertinggi = p.id_penawaran
    LEFT JOIN users u ON p.id_user = u.id_user
    WHERE l.id_lelang = '" . mysqli_real_escape_string($koneksi, $id_lelang) . "'
");

// Cek apakah data lelang ditemukan
if (mysqli_num_rows($query) == 0) {
    echo "Data lelang tidak ditemukan.";
    exit;
}

$lelang = mysqli_fetch_assoc($query);

// Cek apakah lelang sudah berakhir dan user ini adalah pemenangnya
if ($lelang['status'] != 'Lewat' || $lelang['id_penawar_tertinggi'] != $loggedInUserId) {
    echo "Anda tidak memiliki akses ke halaman pembayaran untuk lelang ini atau lelang belum berakhir dengan Anda sebagai pemenang.";
    exit;
}

// Cek apakah pembayaran sudah ada untuk lelang ini oleh user ini
$queryCheckPayment = mysqli_query($koneksi, "
    SELECT * FROM pembayaran
    WHERE id_lelang = '" . mysqli_real_escape_string($koneksi, $id_lelang) . "'
    AND id_user = '" . mysqli_real_escape_string($koneksi, $loggedInUserId) . "'
    ORDER BY createdAt DESC LIMIT 1
");

$paymentExists = mysqli_num_rows($queryCheckPayment) > 0;
$existingPayment = mysqli_fetch_assoc($queryCheckPayment);


// Proses submit pembayaran
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_payment'])) {
    $metode_pembayaran = mysqli_real_escape_string($koneksi, $_POST['metode_pembayaran']);
    $jumlah_bayar = $lelang['harga_tertinggi']; // Jumlah bayar otomatis dari harga tertinggi lelang

    $bukti_transfer = null;
    $upload_success = false;
    $upload_error_message = '';

    if (isset($_FILES['bukti_transfer']) && $_FILES['bukti_transfer']['error'] != UPLOAD_ERR_NO_FILE) {
        $file_error_code = $_FILES['bukti_transfer']['error'];

        if ($file_error_code == UPLOAD_ERR_OK) {
            $targetDir = "../uploads_pembayaran/";
            // Pastikan direktori target ada dan writable
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true); // Buat direktori jika belum ada
            }

            $fileName = uniqid() . '_' . basename($_FILES['bukti_transfer']['name']);
            $targetFilePath = $targetDir . $fileName;
            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

            // Allow certain file formats
            $allowTypes = array('jpg', 'png', 'jpeg', 'pdf');
            if (in_array($fileType, $allowTypes)) {
                if (move_uploaded_file($_FILES['bukti_transfer']['tmp_name'], $targetFilePath)) {
                    $bukti_transfer = $fileName;
                    $upload_success = true;
                } else {
                    $upload_error_message = 'Gagal memindahkan file yang diunggah. Cek izin folder.';
                }
            } else {
                $upload_error_message = 'Tipe file tidak diizinkan. Hanya JPG, JPEG, PNG, dan PDF.';
            }
        } else {
            // Map PHP upload error codes to user-friendly messages
            switch ($file_error_code) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $upload_error_message = 'Ukuran file terlalu besar. Maksimal ' . ini_get('upload_max_filesize');
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $upload_error_message = 'File hanya terunggah sebagian. Coba lagi.';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $upload_error_message = 'Direktori temporary tidak ditemukan di server.';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $upload_error_message = 'Gagal menyimpan file ke disk. Cek izin folder server.';
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $upload_error_message = 'Ekstensi PHP menghentikan unggahan file.';
                    break;
                default:
                    $upload_error_message = 'Terjadi kesalahan tidak dikenal saat mengunggah bukti transfer (Kode: ' . $file_error_code . ').';
            }
        }
    } else {
        $upload_error_message = 'Mohon unggah bukti transfer.';
    }

    if ($upload_success) {
        if ($paymentExists) {
            // Update existing payment
            // Only update if current status allows re-submission (e.g., if it was cancelled or waiting)
            // Or just update anyway, and admin can re-confirm
            $updatePaymentQuery = "UPDATE pembayaran SET
                metode_pembayaran = '" . mysqli_real_escape_string($koneksi, $metode_pembayaran) . "',
                jumlah_bayar = '" . mysqli_real_escape_string($koneksi, $jumlah_bayar) . "',
                bukti_transfer = '" . mysqli_real_escape_string($koneksi, $bukti_transfer) . "',
                status_pembayaran = 'Menunggu Konfirmasi',
                updatedAt = NOW()
                WHERE id_lelang = '" . mysqli_real_escape_string($koneksi, $id_lelang) . "' AND id_user = '" . mysqli_real_escape_string($koneksi, $loggedInUserId) . "'";

            if (mysqli_query($koneksi, $updatePaymentQuery)) {
                $_SESSION['payment_status'] = 'success_update';
                $_SESSION['payment_message'] = 'Pembayaran Anda berhasil diperbarui dan menunggu konfirmasi.';
            } else {
                $_SESSION['payment_status'] = 'failed_update';
                $_SESSION['payment_message'] = 'Gagal memperbarui data pembayaran: ' . mysqli_error($koneksi);
            }
        } else {
            // Insert new payment
            $insertPaymentQuery = "INSERT INTO pembayaran (id_lelang, id_user, jumlah_bayar, metode_pembayaran, bukti_transfer, status_pembayaran)
                                   VALUES (
                                       '" . mysqli_real_escape_string($koneksi, $id_lelang) . "',
                                       '" . mysqli_real_escape_string($koneksi, $loggedInUserId) . "',
                                       '" . mysqli_real_escape_string($koneksi, $jumlah_bayar) . "',
                                       '" . mysqli_real_escape_string($koneksi, $metode_pembayaran) . "',
                                       '" . mysqli_real_escape_string($koneksi, $bukti_transfer) . "',
                                       'Menunggu Konfirmasi'
                                   )";

            if (mysqli_query($koneksi, $insertPaymentQuery)) {
                $_SESSION['payment_status'] = 'success';
                $_SESSION['payment_message'] = 'Pembayaran Anda berhasil diajukan dan menunggu konfirmasi.';
            } else {
                $_SESSION['payment_status'] = 'failed';
                $_SESSION['payment_message'] = 'Gagal mengajukan pembayaran: ' . mysqli_error($koneksi);
            }
        }
    } else {
        $_SESSION['payment_status'] = 'failed_upload_overall';
        $_SESSION['payment_message'] = 'Gagal mengunggah bukti transfer: ' . $upload_error_message;
    }

    header("Location: pembayaran.php?id_lelang=" . $id_lelang);
    exit;
}

// SweetAlert message handling
$paymentStatus = '';
$paymentMessage = '';
if (isset($_SESSION['payment_status'])) {
    $paymentStatus = $_SESSION['payment_status'];
    $paymentMessage = $_SESSION['payment_message'];
    unset($_SESSION['payment_status']);
    unset($_SESSION['payment_message']);
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Pembayaran Lelang - <?= htmlspecialchars($lelang['kategori']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../style.css">
    <style>
        .payment-box {
            max-width: 700px;
            margin: auto;
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .payment-box img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .label {
            font-weight: bold;
            color: #495057;
        }

        /* Navbar styles from previous code */
        .main-header {
            background-color: #343a40;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
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
            color: #ffffff;
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
            color: #343a40;
            border: none;
        }

        .auth-links .btn-primary:hover {
            background-color: #f0f0f0;
            color: #343a40;
        }

        .auth-links .btn-outline-primary {
            background-color: transparent;
            color: #ffffff;
            border: 1px solid #ffffff;
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
        <div class="payment-box shadow">
            <h3 class="mb-4 text-center">Pembayaran Lelang Sapi</h3>

            <img src="../uploads_sapi/<?= htmlspecialchars($lelang['foto_sapi']); ?>" alt="Foto sapi">

            <div class="mt-4">
                <p><span class="label">Kategori Sapi:</span> <?= htmlspecialchars($lelang['kategori']); ?></p>
                <p><span class="label">Harga Lelang (Harga Tertinggi):</span> Rp<?= number_format($lelang['harga_tertinggi']); ?></p>
                <p><span class="label">Pemenang Lelang:</span>
                    <strong><?= htmlspecialchars($lelang['nama_penawar_tertinggi']); ?></strong>
                    <span class="badge bg-success ms-1">Anda</span>
                </p>
                <p><span class="label">Batas Waktu Lelang Berakhir:</span> <?= date("d M Y H:i", strtotime($lelang['batas_waktu'])); ?></p>
            </div>

            <hr>

            <?php if ($paymentExists): ?>
                <div class="alert alert-info text-center">
                    <p>Anda sudah mengajukan pembayaran untuk lelang ini.</p>
                    <p>Status Pembayaran Anda: <strong><?= htmlspecialchars($existingPayment['status_pembayaran']); ?></strong></p>
                    <?php if ($existingPayment['bukti_transfer']): ?>
                        <p>Bukti transfer terakhir Anda:</p>
                        <a href="../uploads_pembayaran/<?= htmlspecialchars($existingPayment['bukti_transfer']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">Lihat Bukti Transfer</a>
                    <?php endif; ?>
                    <?php if ($existingPayment['status_pembayaran'] == 'Menunggu Konfirmasi'): ?>
                        <p class="mt-2">Pembayaran Anda sedang dalam proses verifikasi. Mohon tunggu konfirmasi.</p>
                        <p>Jika Anda ingin mengganti bukti transfer, silakan gunakan form di bawah.</p>
                    <?php elseif ($existingPayment['status_pembayaran'] == 'Dikonfirmasi'): ?>
                        <p class="mt-2">Pembayaran Anda telah **dikonfirmasi!**</p>
                        <p>Silakan segera hubungi pemilik sapi untuk proses serah terima.</p>
                    <?php elseif ($existingPayment['status_pembayaran'] == 'Dibatalkan'): ?>
                        <p class="mt-2 text-danger">Pembayaran Anda dibatalkan.</p>
                        <p>Silakan ajukan pembayaran baru atau hubungi admin untuk informasi lebih lanjut.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($paymentExists && $existingPayment['status_pembayaran'] == 'Dikonfirmasi'): ?>
            <?php else: ?>
                <h5 class="mt-4">Form Pembayaran</h5>
                <p>Silakan lakukan transfer sejumlah **Rp<?= number_format($lelang['harga_tertinggi']); ?>** ke rekening berikut:</p>
                <ul class="list-group mb-3">
                    <li class="list-group-item"><strong>Bank:</strong> Bank Rakyat Indonesia (BRI)</li>
                    <li class="list-group-item"><strong>Nomor Rekening:</strong> 1234-5678-9012</li>
                    <li class="list-group-item"><strong>Atas Nama:</strong> PT. Pesapean Lelang Sapi</li>
                </ul>

                <form action="pembayaran.php?id_lelang=<?= htmlspecialchars($lelang['id_lelang']); ?>" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="metode_pembayaran" class="form-label">Metode Pembayaran:</label>
                        <select class="form-select" id="metode_pembayaran" name="metode_pembayaran" required>
                            <option value="">Pilih Metode</option>
                            <option value="Bank Transfer" <?= ($paymentExists && $existingPayment['metode_pembayaran'] == 'Bank Transfer') ? 'selected' : ''; ?>>Bank Transfer</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="bukti_transfer" class="form-label">Unggah Bukti Transfer (JPG, JPEG, PNG, PDF):</label>
                        <input type="file" class="form-control" id="bukti_transfer" name="bukti_transfer" accept=".jpg, .jpeg, .png, .pdf" required>
                        <small class="form-text text-muted">Pastikan file bukti transfer jelas dan terbaca. Maksimal ukuran file: <?= ini_get('upload_max_filesize'); ?>.</small>
                    </div>
                    <button type="submit" name="submit_payment" class="btn btn-success w-100">Kirim Bukti Pembayaran</button>
                </form>
            <?php endif; ?>

            <div class="text-center mt-4">
                <a href="../pembeli/lelang_diikuti.php" class="btn btn-secondary">Kembali ke Lelang Saya</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const paymentStatus = "<?= $paymentStatus; ?>";
            const paymentMessage = "<?= $paymentMessage; ?>";

            if (paymentStatus) {
                let icon = '';
                let title = '';

                switch (paymentStatus) {
                    case 'success':
                    case 'success_update':
                        icon = 'success';
                        title = 'Pembayaran Berhasil!';
                        break;
                    case 'failed':
                    case 'failed_update':
                    case 'failed_upload_overall': // Specific for general upload failure
                        icon = 'error';
                        title = 'Pembayaran Gagal!';
                        break;
                    case 'not_logged_in':
                        icon = 'warning';
                        title = 'Akses Ditolak!';
                        break;
                    case 'invalid_file_type':
                    case 'no_file':
                        icon = 'warning';
                        title = 'Unggah Bukti Pembayaran!';
                        break;
                    default:
                        icon = 'info';
                        title = 'Notifikasi Pembayaran';
                }

                Swal.fire({
                    icon: icon,
                    title: title,
                    text: paymentMessage,
                    confirmButtonText: 'OK'
                }).then(() => {
                    // Clean the URL, ensuring id_lelang remains
                    const url = new URL(window.location.href);
                    url.searchParams.delete('status'); // Remove existing status
                    // Preserve other parameters like id_lelang
                    const cleanUrl = url.origin + url.pathname + '?id_lelang=' + url.searchParams.get('id_lelang');
                    history.replaceState({}, document.title, cleanUrl);
                });
            }
        });
    </script>
</body>

</html>