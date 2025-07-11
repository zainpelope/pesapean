<?php
include 'koneksi.php'; // Sesuaikan path ke file koneksi Anda

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_lelang = $_POST['id_lelang'];
    $id_penawaran_tertinggi = $_POST['id_penawaran_tertinggi'];

    // Pastikan ada ID lelang dan ID penawaran tertinggi
    if (empty($id_lelang) || empty($id_penawaran_tertinggi)) {
        header("Location: verifikasi_lelang.php?status=no_bids");
        exit();
    }

    // Mulai transaksi untuk memastikan konsistensi data
    mysqli_begin_transaction($koneksi);

    try {
        // 1. Ambil id_pengguna dari penawaran tertinggi
        $query_get_pemenang = mysqli_query($koneksi, "SELECT id_pengguna FROM Penawaran WHERE id_penawaran = '$id_penawaran_tertinggi'");
        if (!$query_get_pemenang || mysqli_num_rows($query_get_pemenang) == 0) {
            throw new Exception("Penawaran tertinggi tidak ditemukan.");
        }
        $data_pemenang = mysqli_fetch_assoc($query_get_pemenang);
        $id_pemenang = $data_pemenang['id_pengguna'];

        // 2. Update status lelang menjadi 'Terverifikasi' dan set id_pemenang
        $query_update_lelang = mysqli_query($koneksi, "
            UPDATE lelang
            SET status = 'Terverifikasi', id_pemenang = '$id_pemenang', updatedAt = NOW()
            WHERE id_lelang = '$id_lelang'
        ");

        if (!$query_update_lelang) {
            throw new Exception("Gagal update status lelang.");
        }

        // 3. (Opsional) Nonaktifkan atau tandai penawaran lain untuk lelang ini
        //    Ini bisa dilakukan jika Anda ingin memastikan hanya penawaran pemenang yang valid
        // mysqli_query($koneksi, "
        //     UPDATE Penawaran
        //     SET status = 'Tidak Terpilih'
        //     WHERE id_lelang = '$id_lelang' AND id_penawaran != '$id_penawaran_tertinggi'
        // ");

        // Commit transaksi jika semua query berhasil
        mysqli_commit($koneksi);
        header("Location: verifikasi_lelang.php?status=verified");
        exit();
    } catch (Exception $e) {
        // Rollback transaksi jika ada kesalahan
        mysqli_rollback($koneksi);
        error_log("Error in proses_verifikasi.php: " . $e->getMessage()); // Log error for debugging
        header("Location: verifikasi_lelang.php?status=error");
        exit();
    }
} else {
    // Jika diakses langsung tanpa POST request
    header("Location: verifikasi_lelang.php");
    exit();
}
