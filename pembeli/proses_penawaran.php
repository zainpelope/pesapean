<?php
error_reporting(E_ALL); // Melaporkan semua jenis error
ini_set('display_errors', 1);
include '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_lelang = $_POST['id_lelang'];
    $id_sapi = $_POST['id_sapi']; // Tambahkan id_sapi untuk redirect yang benar
    $harga_tawaran = $_POST['harga_tawaran'];
    $waktu_tawaran = date('Y-m-d H:i:s'); // Waktu saat penawaran diajukan

    // Ambil harga tertinggi saat ini dari tabel lelang
    $query_lelang = mysqli_query($koneksi, "SELECT harga_tertinggi, batas_waktu, status FROM lelang WHERE id_lelang = '$id_lelang'");
    $data_lelang = mysqli_fetch_assoc($query_lelang);

    if (!$data_lelang || $data_lelang['status'] != 'Aktif' || strtotime($data_lelang['batas_waktu']) < time()) {
        // Lelang tidak ditemukan, tidak aktif, atau sudah berakhir
        header("Location: detail.php?id=" . $id_sapi . "&status=failed_inactive");
        exit;
    }

    $harga_tertinggi_saat_ini = $data_lelang['harga_tertinggi'];

    // Validasi harga penawaran
    if ($harga_tawaran <= $harga_tertinggi_saat_ini) {
        // Harga penawaran tidak lebih tinggi dari harga tertinggi saat ini
        header("Location: detail.php?id=" . $id_sapi . "&status=failed");
        exit;
    }

    // Masukkan data penawaran ke tabel Penawaran
    $insert_penawaran = mysqli_query($koneksi, "
        INSERT INTO Penawaran (id_lelang, harga_tawaran, waktu_tawaran)
        VALUES ('$id_lelang', '$harga_tawaran', '$waktu_tawaran')
    ");

    if ($insert_penawaran) {
        // Ambil ID penawaran yang baru saja di-insert
        $id_penawaran_baru = mysqli_insert_id($koneksi);

        // Update harga_tertinggi dan id_penawaranTertinggi di tabel lelang
        $update_lelang = mysqli_query($koneksi, "
            UPDATE lelang
            SET
                harga_tertinggi = '$harga_tawaran',
                id_penawaranTertinggi = '$id_penawaran_baru',
                updatedAt = NOW()
            WHERE id_lelang = '$id_lelang'
        ");

        if ($update_lelang) {
            header("Location: detail.php?id=" . $id_sapi . "&status=success");
        } else {
            // Jika update lelang gagal, hapus penawaran yang baru saja di-insert (rollback sederhana)
            mysqli_query($koneksi, "DELETE FROM Penawaran WHERE id_penawaran = '$id_penawaran_baru'");
            header("Location: detail.php?id=" . $id_sapi . "&status=failed_update_lelang");
        }
    } else {
        header("Location: detail.php?id=" . $id_sapi . "&status=failed_insert_penawaran");
    }
} else {
    // Jika diakses langsung tanpa POST
    header("Location: ../pembeli/lelang.php");
    exit;
}
