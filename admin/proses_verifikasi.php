<?php
include '../koneksi.php'; // Sesuaikan path ke file koneksi Anda

// Aktifkan pelaporan error untuk debugging (hapus saat deployment)
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_lelang = mysqli_real_escape_string($koneksi, $_POST['id_lelang']);
    $id_penawaran_tertinggi = isset($_POST['id_penawaran_tertinggi']) ? mysqli_real_escape_string($koneksi, $_POST['id_penawaran_tertinggi']) : null;

    // --- Validasi Awal ---
    if (empty($id_lelang)) {
        header("Location: verifikasi_lelang.php?status=error&message=ID lelang tidak ditemukan.");
        exit;
    }

    // Ambil status lelang saat ini dan harga_tertinggi dari DB
    $check_lelang_q = mysqli_query($koneksi, "SELECT status, harga_tertinggi FROM lelang WHERE id_lelang = '$id_lelang'");
    $lelang_data = mysqli_fetch_assoc($check_lelang_q);

    if (!$lelang_data) {
        header("Location: verifikasi_lelang.php?status=error&message=Lelang tidak ditemukan.");
        exit;
    }

    // Pastikan lelang sudah 'Lewat' dan ada penawaran tertinggi yang tercatat
    if ($lelang_data['status'] != 'Lewat' || $id_penawaran_tertinggi === null || $lelang_data['harga_tertinggi'] == 0) {
        header("Location: verifikasi_lelang.php?status=no_bids");
        exit;
    }

    // --- Proses Verifikasi ---
    // Update status lelang menjadi 'Terverifikasi'
    $update_lelang = mysqli_query($koneksi, "
        UPDATE lelang
        SET
            status = 'Terverifikasi',
            updatedAt = NOW()
        WHERE id_lelang = '$id_lelang'
    ");

    if ($update_lelang) {
        // Redirect ke halaman verifikasi_lelang.php dengan status sukses
        header("Location: verifikasi_lelang.php?status=verified");
    } else {
        // Jika update gagal
        header("Location: verifikasi_lelang.php?status=error&message=" . mysqli_error($koneksi));
    }
    exit;
} else {
    // Jika diakses langsung tanpa metode POST
    header("Location: verifikasi_lelang.php");
    exit;
}
