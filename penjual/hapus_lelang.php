<?php
// Aktifkan pelaporan error untuk debugging (HANYA UNTUK PENGEMBANGAN!)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../koneksi.php'; // Pastikan jalur ini benar untuk koneksi database Anda

// Redirect jika pengguna tidak login atau bukan 'Penjual'
if (!isset($_SESSION['id_user']) || $_SESSION['nama_role'] !== 'Penjual') {
    $_SESSION['message'] = 'Anda harus login sebagai Penjual untuk menghapus lelang.';
    $_SESSION['message_type'] = 'danger';
    header('Location: ../auth/login.php');
    exit();
}

// Ambil ID sapi dari URL. Ini adalah id_sapi dari tabel data_sapi.
$id_sapi = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_sapi > 0) {
    // 1. Verifikasi kepemilikan lelang dan dapatkan semua ID yang diperlukan
    // (id_lelang, foto_sapi, id_macamSapi, id_user_penjual)
    $stmt_get_auction_data = mysqli_prepare($koneksi, "
        SELECT 
            l.id_lelang, 
            l.id_user AS lelang_id_user,
            ds.foto_sapi, 
            ds.id_macamSapi,
            ds.id_user_penjual
        FROM lelang l
        INNER JOIN data_sapi ds ON l.id_sapi = ds.id_sapi
        WHERE l.id_sapi = ? AND l.id_user = ?
    ");

    if (!$stmt_get_auction_data) {
        $_SESSION['message'] = 'Error persiapan query data lelang: ' . mysqli_error($koneksi);
        $_SESSION['message_type'] = 'danger';
        header('Location: lelang.php');
        exit();
    }

    mysqli_stmt_bind_param($stmt_get_auction_data, "ii", $id_sapi, $_SESSION['id_user']);
    mysqli_stmt_execute($stmt_get_auction_data);
    $result_get_auction_data = mysqli_stmt_get_result($stmt_get_auction_data);
    $auction_details = mysqli_fetch_assoc($result_get_auction_data);
    mysqli_stmt_close($stmt_get_auction_data);

    if ($auction_details) {
        $id_lelang = $auction_details['id_lelang'];
        $foto_sapi = $auction_details['foto_sapi'];
        $id_macamSapi = $auction_details['id_macamSapi'];

        // Mulai transaksi database
        mysqli_begin_transaction($koneksi);

        try {
            // --- Hapus dari tabel CHATMESSAGE (Child dari CHATROOMS) ---
            // Pertama, cari id_chatRooms yang terkait dengan id_sapi ini
            $stmt_get_chatrooms = mysqli_prepare($koneksi, "SELECT id_chatRooms FROM chatrooms WHERE id_sapi = ?");
            if (!$stmt_get_chatrooms) throw new Exception("Prepare get chatrooms failed: " . mysqli_error($koneksi));
            mysqli_stmt_bind_param($stmt_get_chatrooms, "i", $id_sapi);
            mysqli_stmt_execute($stmt_get_chatrooms);
            $result_chatrooms = mysqli_stmt_get_result($stmt_get_chatrooms);
            $chatrooms_to_delete = [];
            while ($row = mysqli_fetch_assoc($result_chatrooms)) {
                $chatrooms_to_delete[] = $row['id_chatRooms'];
            }
            mysqli_stmt_close($stmt_get_chatrooms);

            if (!empty($chatrooms_to_delete)) {
                $chatroom_ids_str = implode(',', $chatrooms_to_delete);
                $stmt_delete_chatmessage = mysqli_prepare($koneksi, "DELETE FROM chatmessage WHERE id_chatRooms IN (?)");
                if (!$stmt_delete_chatmessage) throw new Exception("Prepare delete chatmessage failed: " . mysqli_error($koneksi));
                mysqli_stmt_bind_param($stmt_delete_chatmessage, "s", $chatroom_ids_str); // "s" karena implode menghasilkan string
                $success_chatmessage = mysqli_stmt_execute($stmt_delete_chatmessage);
                mysqli_stmt_close($stmt_delete_chatmessage);
                if (!$success_chatmessage) throw new Exception("Gagal menghapus data dari 'chatmessage'.");
            }

            // --- Hapus dari tabel CHATROOMS (Child dari DATA_SAPI) ---
            $stmt_delete_chatrooms = mysqli_prepare($koneksi, "DELETE FROM chatrooms WHERE id_sapi = ?");
            if (!$stmt_delete_chatrooms) throw new Exception("Prepare delete chatrooms failed: " . mysqli_error($koneksi));
            mysqli_stmt_bind_param($stmt_delete_chatrooms, "i", $id_sapi);
            $success_chatrooms = mysqli_stmt_execute($stmt_delete_chatrooms);
            mysqli_stmt_close($stmt_delete_chatrooms);
            if (!$success_chatrooms) throw new Exception("Gagal menghapus data dari 'chatrooms'.");


            // --- Hapus dari tabel PEMBAYARAN (Child dari LELANG) ---
            $stmt_delete_pembayaran = mysqli_prepare($koneksi, "DELETE FROM pembayaran WHERE id_lelang = ?");
            if (!$stmt_delete_pembayaran) throw new Exception("Prepare delete pembayaran failed: " . mysqli_error($koneksi));
            mysqli_stmt_bind_param($stmt_delete_pembayaran, "i", $id_lelang);
            $success_pembayaran = mysqli_stmt_execute($stmt_delete_pembayaran);
            mysqli_stmt_close($stmt_delete_pembayaran);
            if (!$success_pembayaran) throw new Exception("Gagal menghapus data dari 'pembayaran'.");

            // --- Hapus dari tabel PENAWARAN (Child dari LELANG) ---
            $stmt_delete_penawaran = mysqli_prepare($koneksi, "DELETE FROM penawaran WHERE id_lelang = ?");
            if (!$stmt_delete_penawaran) throw new Exception("Prepare delete penawaran failed: " . mysqli_error($koneksi));
            mysqli_stmt_bind_param($stmt_delete_penawaran, "i", $id_lelang);
            $success_penawaran = mysqli_stmt_execute($stmt_delete_penawaran);
            mysqli_stmt_close($stmt_delete_penawaran);
            if (!$success_penawaran) throw new Exception("Gagal menghapus data dari 'penawaran'.");

            // --- Hapus dari tabel LELANG (Child dari DATA_SAPI) ---
            $stmt_delete_lelang = mysqli_prepare($koneksi, "DELETE FROM lelang WHERE id_lelang = ? AND id_sapi = ? AND id_user = ?");
            if (!$stmt_delete_lelang) throw new Exception("Prepare delete lelang failed: " . mysqli_error($koneksi));
            mysqli_stmt_bind_param($stmt_delete_lelang, "iii", $id_lelang, $id_sapi, $_SESSION['id_user']);
            $success_lelang = mysqli_stmt_execute($stmt_delete_lelang);
            mysqli_stmt_close($stmt_delete_lelang);
            if (!$success_lelang) throw new Exception("Gagal menghapus entri lelang.");

            // --- Hapus dari tabel sapi spesifik (Child dari DATA_SAPI) ---
            // Kita perlu menggunakan id_macamSapi untuk menentukan tabel mana yang akan dihapus
            $specific_sapi_table = '';
            $specific_sapi_id_col = ''; // Kolom ID di tabel spesifik (bisa 'id' atau 'id_sapi')

            switch ($id_macamSapi) {
                case 1: // Sapi Sonok
                    $specific_sapi_table = 'sapisonok';
                    $specific_sapi_id_col = 'id_sapi'; // Kolom id_sapi di sapisonok
                    // Jika ada data di generasiSatu/generasiDua yang merujuk sapisonok.id, itu juga harus dihapus
                    // (Diasumsikan sapisonok.id adalah PK dan diGenerasiSatu/Dua merujuk sapisonok.id)
                    // Hapus dari generasidua (child dari sapisonok)
                    $stmt_delete_gen2 = mysqli_prepare($koneksi, "DELETE FROM generasidua WHERE sapiSonok = (SELECT id FROM sapisonok WHERE id_sapi = ?)");
                    if (!$stmt_delete_gen2) throw new Exception("Prepare delete generasidua failed: " . mysqli_error($koneksi));
                    mysqli_stmt_bind_param($stmt_delete_gen2, "i", $id_sapi);
                    mysqli_stmt_execute($stmt_delete_gen2);
                    mysqli_stmt_close($stmt_delete_gen2);
                    // Hapus dari generasisatu (child dari sapisonok)
                    $stmt_delete_gen1 = mysqli_prepare($koneksi, "DELETE FROM generasisatu WHERE sapiSonok = (SELECT id FROM sapisonok WHERE id_sapi = ?)");
                    if (!$stmt_delete_gen1) throw new Exception("Prepare delete generasisatu failed: " . mysqli_error($koneksi));
                    mysqli_stmt_bind_param($stmt_delete_gen1, "i", $id_sapi);
                    mysqli_stmt_execute($stmt_delete_gen1);
                    mysqli_stmt_close($stmt_delete_gen1);
                    break;
                case 2: // Sapi Kerap
                    $specific_sapi_table = 'sapikerap';
                    $specific_sapi_id_col = 'id_sapi'; // Kolom id_sapi di sapikerap
                    break;
                case 3: // Sapi Tangghek
                    $specific_sapi_table = 'sapitangghek';
                    $specific_sapi_id_col = 'id_sapi'; // Kolom id_sapi di sapitangghek
                    break;
                case 4: // Sapi Ternak
                    $specific_sapi_table = 'sapiternak';
                    $specific_sapi_id_col = 'id_sapi'; // Kolom id_sapi di sapiternak
                    break;
                case 5: // Sapi Potong
                    $specific_sapi_table = 'sapipotong';
                    $specific_sapi_id_col = 'id_sapi'; // Kolom id_sapi di sapipotong
                    break;
            }

            if (!empty($specific_sapi_table) && !empty($specific_sapi_id_col)) {
                $stmt_delete_specific_sapi = mysqli_prepare($koneksi, "DELETE FROM {$specific_sapi_table} WHERE {$specific_sapi_id_col} = ?");
                if (!$stmt_delete_specific_sapi) throw new Exception("Prepare delete specific sapi failed for {$specific_sapi_table}: " . mysqli_error($koneksi));
                mysqli_stmt_bind_param($stmt_delete_specific_sapi, "i", $id_sapi);
                $success_specific_sapi = mysqli_stmt_execute($stmt_delete_specific_sapi);
                mysqli_stmt_close($stmt_delete_specific_sapi);
                if (!$success_specific_sapi) throw new Exception("Gagal menghapus data dari tabel sapi spesifik: '{$specific_sapi_table}'.");
            }

            // --- Hapus dari tabel DATA_SAPI (Parent paling utama) ---
            $stmt_delete_data_sapi = mysqli_prepare($koneksi, "DELETE FROM data_sapi WHERE id_sapi = ?");
            if (!$stmt_delete_data_sapi) throw new Exception("Prepare delete data_sapi failed: " . mysqli_error($koneksi));
            mysqli_stmt_bind_param($stmt_delete_data_sapi, "i", $id_sapi);
            $success_data_sapi = mysqli_stmt_execute($stmt_delete_data_sapi);
            mysqli_stmt_close($stmt_delete_data_sapi);
            if (!$success_data_sapi) throw new Exception("Gagal menghapus data sapi utama.");

            // --- Hapus file foto terkait dari server ---
            $target_dir = "../uploads_sapi/";
            if ($foto_sapi && file_exists($target_dir . $foto_sapi) && $foto_sapi !== 'default.jpg') {
                unlink($target_dir . $foto_sapi); // Hapus file fisik
            }

            // Commit transaksi jika semua operasi berhasil
            mysqli_commit($koneksi);
            $_SESSION['message'] = 'Lelang berhasil dihapus!';
            $_SESSION['message_type'] = 'success';
        } catch (Exception $e) {
            // Rollback transaksi jika ada Exception (kesalahan)
            mysqli_rollback($koneksi);
            $_SESSION['message'] = 'Terjadi kesalahan saat menghapus lelang: ' . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
        }
    } else {
        $_SESSION['message'] = 'Lelang tidak ditemukan atau Anda tidak memiliki izin untuk menghapusnya.';
        $_SESSION['message_type'] = 'danger';
    }
} else {
    $_SESSION['message'] = 'ID lelang tidak valid untuk dihapus.';
    $_SESSION['message_type'] = 'danger';
}

// Selalu redirect kembali ke halaman lelang.php setelah selesai
header('Location: lelang.php');
exit();
