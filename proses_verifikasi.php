<?php
include 'koneksi.php'; // Sesuaikan path ke file koneksi Anda

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_lelang = $_POST['id_lelang'];
    $id_penawaran_tertinggi = $_POST['id_penawaran_tertinggi'];

    // Pastikan ada ID lelang dan ID penawaran tertinggi yang dikirimkan
    if (empty($id_lelang) || empty($id_penawaran_tertinggi)) {
        // Redirect dengan pesan jika ID tidak lengkap atau tidak valid
        header("Location: verifikasi_lelang.php?status=no_bids");
        exit();
    }

    // Mulai transaksi untuk memastikan konsistensi data.
    // Jika ada bagian dari proses yang gagal, semua perubahan akan dibatalkan.
    mysqli_begin_transaction($koneksi);

    try {
        // 1. Ambil id_user (yang disebut id_pengguna di kode) dari penawaran tertinggi
        // Ini penting untuk mengetahui siapa pemenangnya.
        // Menggunakan prepared statement untuk mencegah SQL Injection.
        $stmt_get_pemenang = mysqli_prepare($koneksi, "SELECT id_user FROM Penawaran WHERE id_penawaran = ?");
        if (!$stmt_get_pemenang) {
            // Jika prepared statement gagal, lempar exception dengan detail error MySQL
            throw new Exception("Prepare statement untuk mendapatkan pemenang gagal: " . mysqli_error($koneksi));
        }
        // Bind parameter 'id_penawaran_tertinggi' ke placeholder '?'
        mysqli_stmt_bind_param($stmt_get_pemenang, "i", $id_penawaran_tertinggi); // 'i' untuk integer
        // Eksekusi prepared statement
        mysqli_stmt_execute($stmt_get_pemenang);
        // Dapatkan hasil dari eksekusi statement
        $result_get_pemenang = mysqli_stmt_get_result($stmt_get_pemenang);

        // Periksa apakah penawaran tertinggi ditemukan
        if (mysqli_num_rows($result_get_pemenang) == 0) {
            // Jika tidak ditemukan, lempar exception
            throw new Exception("Penawaran tertinggi tidak ditemukan atau tidak valid.");
        }
        // Ambil data pemenang
        $data_pemenang = mysqli_fetch_assoc($result_get_pemenang);
        $id_pengguna = $data_pemenang['id_user']; // Mengambil ID pengguna pemenang
        // Tutup statement
        mysqli_stmt_close($stmt_get_pemenang);

        // 2. Update status lelang menjadi 'Terverifikasi' di tabel 'lelang'
        // Kolom 'id_penawaranTertinggi' di tabel 'lelang' sudah menyimpan ID penawaran tertinggi yang akan menjadi pemenang.
        // Jadi, kita hanya perlu mengubah statusnya dan waktu update.
        // Menggunakan prepared statement untuk keamanan.
        $stmt_update_lelang = mysqli_prepare($koneksi, "
            UPDATE lelang
            SET status = 'Terverifikasi', updatedAt = NOW()
            WHERE id_lelang = ?
        ");
        if (!$stmt_update_lelang) {
            // Jika prepared statement gagal, lempar exception
            throw new Exception("Prepare statement untuk update lelang gagal: " . mysqli_error($koneksi));
        }
        // Bind parameter 'id_lelang'
        mysqli_stmt_bind_param($stmt_update_lelang, "i", $id_lelang); // 'i' untuk integer
        // Eksekusi prepared statement
        if (!mysqli_stmt_execute($stmt_update_lelang)) {
            // Jika eksekusi gagal, lempar exception dengan detail error statement
            throw new Exception("Eksekusi statement update lelang gagal: " . mysqli_stmt_error($stmt_update_lelang));
        }
        // Tutup statement
        mysqli_stmt_close($stmt_update_lelang);

        // --- Catatan: Bagian untuk tabel 'pemenang_lelang' telah dihapus ---
        // Berdasarkan skema database yang Anda berikan, tabel 'pemenang_lelang' tidak ada.
        // Jika di masa depan Anda ingin mencatat pemenang secara terpisah dalam tabel baru,
        // Anda perlu membuat tabel 'pemenang_lelang' terlebih dahulu dengan kolom yang sesuai
        // (misalnya: id_lelang, id_pengguna, id_penawaranTertinggi, tanggal_menang).
        // Setelah itu, Anda bisa menambahkan kembali kode INSERT di sini.

        // Commit transaksi jika semua query di atas berhasil dieksekusi tanpa error
        mysqli_commit($koneksi);
        // Redirect ke halaman verifikasi dengan status 'verified'
        header("Location: verifikasi_lelang.php?status=verified");
        exit();
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi kesalahan di salah satu langkah di dalam blok try
        mysqli_rollback($koneksi);
        // Log error untuk debugging (SANGAT PENTING!)
        // Pesan error ini akan muncul di log error PHP server Anda.
        // Ini adalah kunci untuk mengetahui akar masalah jika masih ada error.
        error_log("Error in proses_verifikasi.php: " . $e->getMessage());
        // Redirect ke halaman verifikasi dengan status 'error'
        header("Location: verifikasi_lelang.php?status=error");
        exit();
    } finally {
        // Pastikan koneksi database ditutup setelah semua operasi selesai atau jika terjadi error
        if ($koneksi) {
            mysqli_close($koneksi);
        }
    }
} else {
    // Jika file ini diakses langsung melalui URL tanpa metode POST
    header("Location: verifikasi_lelang.php");
    exit();
}
