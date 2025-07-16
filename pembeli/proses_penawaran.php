<?php
// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Mulai sesi (PENTING: Harus di paling atas sebelum output HTML)
session_start();

include '../koneksi.php'; // Pastikan path ini benar

// Pastikan request adalah POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    // Jika diakses langsung tanpa POST, redirect ke halaman lelang
    header("Location: ../pembeli/lelang.php");
    exit;
}

// 1. Cek apakah pengguna sudah login
if (!isset($_SESSION['id_user'])) {
    // Redirect ke halaman detail dengan status 'not_logged_in' jika belum login
    // Pastikan id_sapi ada dari POST untuk redirect yang benar
    $id_sapi = $_POST['id_sapi'] ?? null;
    header("Location: detail.php?id=" . urlencode($id_sapi) . "&status=not_logged_in");
    exit();
}

// Ambil ID pengguna yang sedang login dari sesi
$id_user = $_SESSION['id_user'];

// Ambil data dari form POST
$id_lelang = $_POST['id_lelang'] ?? null;
$id_sapi = $_POST['id_sapi'] ?? null;
$harga_tawaran = $_POST['harga_tawaran'] ?? null;

// 2. Validasi data awal
if (!$id_lelang || !$id_sapi || !is_numeric($harga_tawaran) || $harga_tawaran <= 0) {
    header("Location: detail.php?id=" . urlencode($id_sapi) . "&status=invalid_data");
    exit();
}

// Konversi harga_tawaran ke integer untuk memastikan tipe data yang benar
$harga_tawaran = (int)$harga_tawaran;

// --- Memulai Transaksi Database ---
// Ini memastikan bahwa semua operasi database di bawah akan sukses bersamaan, atau tidak sama sekali.
mysqli_begin_transaction($koneksi);

try {
    // 3. Verifikasi ulang status lelang dan harga tertinggi saat ini dari database
    // Menggunakan SELECT ... FOR UPDATE untuk mengunci baris lelang
    // Ini mencegah race condition (dua user menawar di waktu yang sangat bersamaan)
    $stmt_check_lelang = mysqli_prepare($koneksi, "SELECT status, harga_tertinggi, batas_waktu, id_penawaranTertinggi FROM lelang WHERE id_lelang = ? FOR UPDATE");
    if (!$stmt_check_lelang) {
        throw new Exception("Prepared statement failed: " . mysqli_error($koneksi));
    }
    mysqli_stmt_bind_param($stmt_check_lelang, "i", $id_lelang);
    mysqli_stmt_execute($stmt_check_lelang);
    $result_check_lelang = mysqli_stmt_get_result($stmt_check_lelang);
    $lelang_data = mysqli_fetch_assoc($result_check_lelang);
    mysqli_stmt_close($stmt_check_lelang);

    // Cek apakah lelang ditemukan, aktif, dan belum melewati batas waktu
    if (!$lelang_data || $lelang_data['status'] != 'Aktif' || strtotime($lelang_data['batas_waktu']) < time()) {
        throw new Exception("Lelang tidak aktif atau sudah berakhir.", 1); // Code 1 for inactive/ended
    }

    // 4. Validasi harga penawaran terhadap harga tertinggi di database (lebih akurat)
    if ($harga_tawaran <= $lelang_data['harga_tertinggi']) {
        throw new Exception("Harga penawaran harus lebih tinggi dari harga tertinggi saat ini.", 2); // Code 2 for bid too low
    }

    // --- Tambahan Logika: Cek apakah pengguna sudah pernah menawar di lelang ini dan bukan penawar tertinggi ---
    $is_current_highest_bidder = false;
    if ($lelang_data['id_penawaranTertinggi']) {
        $stmt_get_highest_bidder = mysqli_prepare($koneksi, "SELECT id_user FROM Penawaran WHERE id_penawaran = ?");
        if (!$stmt_get_highest_bidder) {
            throw new Exception("Prepared statement for highest bidder failed: " . mysqli_error($koneksi));
        }
        mysqli_stmt_bind_param($stmt_get_highest_bidder, "i", $lelang_data['id_penawaranTertinggi']);
        mysqli_stmt_execute($stmt_get_highest_bidder);
        $result_get_highest_bidder = mysqli_stmt_get_result($stmt_get_highest_bidder);
        $highest_bidder_data = mysqli_fetch_assoc($result_get_highest_bidder);
        mysqli_stmt_close($stmt_get_highest_bidder);

        if ($highest_bidder_data && $highest_bidder_data['id_user'] == $id_user) {
            $is_current_highest_bidder = true;
        }
    }

    // Cek apakah user sudah pernah menawar di lelang ini sebelumnya
    $stmt_user_has_bid = mysqli_prepare($koneksi, "SELECT COUNT(*) FROM Penawaran WHERE id_lelang = ? AND id_user = ?");
    if (!$stmt_user_has_bid) {
        throw new Exception("Prepared statement for user bid count failed: " . mysqli_error($koneksi));
    }
    mysqli_stmt_bind_param($stmt_user_has_bid, "ii", $id_lelang, $id_user);
    mysqli_stmt_execute($stmt_user_has_bid);
    mysqli_stmt_bind_result($stmt_user_has_bid, $bid_count);
    mysqli_stmt_fetch($stmt_user_has_bid);
    mysqli_stmt_close($stmt_user_has_bid);

    // Jika pengguna sudah pernah menawar (bid_count > 0) DAN dia BUKAN penawar tertinggi saat ini, maka tolak
    if ($bid_count > 0 && !$is_current_highest_bidder) {
        throw new Exception("Anda sudah pernah mengajukan penawaran di lelang ini dan bukan penawar tertinggi saat ini. Silakan cari lelang sapi lainnya.", 5); // Code 5 for already bid
    }
    // --- Akhir Tambahan Logika ---


    // 5. Masukkan penawaran baru ke tabel Penawaran
    $stmt_insert_penawaran = mysqli_prepare($koneksi, "INSERT INTO Penawaran (id_lelang, id_user, harga_tawaran, waktu_tawaran) VALUES (?, ?, ?, NOW())");
    if (!$stmt_insert_penawaran) {
        throw new Exception("Prepared statement failed: " . mysqli_error($koneksi));
    }
    mysqli_stmt_bind_param($stmt_insert_penawaran, "iii", $id_lelang, $id_user, $harga_tawaran);

    if (!mysqli_stmt_execute($stmt_insert_penawaran)) {
        throw new Exception("Gagal menyimpan penawaran: " . mysqli_stmt_error($stmt_insert_penawaran), 3); // Code 3 for insert failure
    }
    $new_penawaran_id = mysqli_insert_id($koneksi); // Dapatkan ID penawaran yang baru saja di-insert
    mysqli_stmt_close($stmt_insert_penawaran);

    // 6. Update tabel lelang dengan harga tertinggi baru dan ID penawaran tertinggi
    $stmt_update_lelang = mysqli_prepare($koneksi, "UPDATE lelang SET harga_tertinggi = ?, id_penawaranTertinggi = ?, updatedAt = NOW() WHERE id_lelang = ?");
    if (!$stmt_update_lelang) {
        throw new Exception("Prepared statement failed: " . mysqli_error($koneksi));
    }
    mysqli_stmt_bind_param($stmt_update_lelang, "iii", $harga_tawaran, $new_penawaran_id, $id_lelang);

    if (!mysqli_stmt_execute($stmt_update_lelang)) {
        throw new Exception("Gagal memperbarui lelang: " . mysqli_stmt_error($stmt_update_lelang), 4); // Code 4 for update failure
    }
    mysqli_stmt_close($stmt_update_lelang);

    // Jika semua operasi berhasil, commit transaksi
    mysqli_commit($koneksi);
    header("Location: detail.php?id=" . urlencode($id_sapi) . "&status=success");
    exit();
} catch (Exception $e) {
    // Jika terjadi error, rollback transaksi untuk membatalkan semua perubahan
    mysqli_rollback($koneksi);
    error_log("Bid processing error: " . $e->getMessage()); // Log error ke server

    // Redirect berdasarkan kode error atau pesan
    $status_redirect = 'server_error'; // Default error
    switch ($e->getCode()) {
        case 1: // Lelang tidak aktif
            $status_redirect = 'failed_inactive';
            break;
        case 2: // Penawaran terlalu rendah
            $status_redirect = 'failed';
            break;
        case 3: // Gagal insert penawaran
            $status_redirect = 'failed_insert_penawaran';
            break;
        case 4: // Gagal update lelang
            $status_redirect = 'failed_update_lelang';
            break;
        case 5: // Sudah pernah menawar
            $status_redirect = 'already_bid';
            break;
        default: // Error umum
            $status_redirect = 'server_error';
            break;
    }
    header("Location: detail.php?id=" . urlencode($id_sapi) . "&status=" . $status_redirect);
    exit();
} finally {
    // Pastikan koneksi ditutup
    mysqli_close($koneksi);
}
