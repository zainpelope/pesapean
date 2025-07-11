<?php
// Aktifkan error reporting untuk debugging selama pengembangan.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Mulai sesi PHP.
session_start();

// Sertakan file koneksi database Anda.
include '../koneksi.php';

// Cek apakah parameter 'id' ada di URL dan tidak kosong.
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_sapi_to_delete = $_GET['id'];

    if (!is_numeric($id_sapi_to_delete)) {
        $_SESSION['pesan_error'] = "ID sapi tidak valid.";
        header("Location: data_sapi.php");
        exit();
    }

    // --- PENTING: Tentukan jenis sapi untuk menghapus data terkait ---
    // Anda perlu mendapatkan id_macamSapi dari data_sapi terlebih dahulu
    // untuk mengetahui tabel detail mana yang harus dihapus.
    $stmt_get_jenis = mysqli_prepare($koneksi, "SELECT id_macamSapi FROM data_sapi WHERE id_sapi = ?");
    if (!$stmt_get_jenis) {
        $_SESSION['pesan_error'] = "Gagal mempersiapkan statement untuk mendapatkan jenis sapi: " . mysqli_error($koneksi);
        header("Location: data_sapi.php");
        exit();
    }
    mysqli_stmt_bind_param($stmt_get_jenis, "i", $id_sapi_to_delete);
    mysqli_stmt_execute($stmt_get_jenis);
    $result_get_jenis = mysqli_stmt_get_result($stmt_get_jenis);
    $sapi_data = mysqli_fetch_assoc($result_get_jenis);
    mysqli_stmt_close($stmt_get_jenis);

    if (!$sapi_data) {
        $_SESSION['pesan_info'] = "Sapi dengan ID " . htmlspecialchars($id_sapi_to_delete) . " tidak ditemukan.";
        header("Location: data_sapi.php");
        exit();
    }

    $id_macam_sapi = $sapi_data['id_macamSapi'];

    // Map id_macamSapi ke nama tabel detail yang sesuai
    // Sesuaikan ini jika struktur tabel Anda berbeda atau ada lebih banyak jenis
    $detail_tables_map = [
        1 => ['sapiSonok', 'generasiSatu', 'generasiDua'], // Sonok memiliki 2 tabel turunan lagi
        2 => ['sapiKerap'],
        3 => ['sapiTangghek'],
        4 => ['sapiTernak'],
        5 => ['sapiPotong']
    ];

    // Mulai transaksi untuk memastikan semua penghapusan berhasil atau tidak sama sekali
    mysqli_begin_transaction($koneksi);
    $deletion_successful = true;

    // --- Hapus data dari tabel anak (child tables) terlebih dahulu ---
    // Urutan penghapusan harus dari paling 'dalam' (generasi) ke 'luar' (sapi detail)
    // dan terakhir tabel 'data_sapi'.

    if (isset($detail_tables_map[$id_macam_sapi])) {
        $tables_to_delete = $detail_tables_map[$id_macam_sapi];

        // Urutkan tabel untuk penghapusan yang benar (paling dalam ke luar)
        // Jika ada tabel generasi, pastikan mereka dihapus sebelum sapiSonok
        usort($tables_to_delete, function ($a, $b) {
            $order = ['generasiDua', 'generasiSatu', 'sapiSonok', 'sapiKerap', 'sapiTangghek', 'sapiTernak', 'sapiPotong'];
            return array_search($a, $order) <=> array_search($b, $order);
        });

        foreach ($tables_to_delete as $table_name) {
            // Khusus untuk tabel generasi (generasiSatu, generasiDua), mereka merujuk ke id dari sapiSonok,
            // BUKAN id_sapi dari data_sapi.
            // Jadi, kita perlu ID dari sapiSonok terlebih dahulu jika $table_name adalah generasi.
            if ($table_name === 'generasiSatu' || $table_name === 'generasiDua') {
                $stmt_get_sapi_sonok_id = mysqli_prepare($koneksi, "SELECT id FROM sapiSonok WHERE id_sapi = ?");
                if (!$stmt_get_sapi_sonok_id) {
                    $_SESSION['pesan_error'] = "Gagal mempersiapkan statement untuk mendapatkan ID sapiSonok: " . mysqli_error($koneksi);
                    $deletion_successful = false;
                    break;
                }
                mysqli_stmt_bind_param($stmt_get_sapi_sonok_id, "i", $id_sapi_to_delete);
                mysqli_stmt_execute($stmt_get_sapi_sonok_id);
                $result_sapi_sonok_id = mysqli_stmt_get_result($stmt_get_sapi_sonok_id);
                $sapi_sonok_id_row = mysqli_fetch_assoc($result_sapi_sonok_id);
                mysqli_stmt_close($stmt_get_sapi_sonok_id);

                if ($sapi_sonok_id_row) {
                    $sapi_sonok_detail_id = $sapi_sonok_id_row['id']; // Ini adalah ID dari tabel sapiSonok
                    $sql_delete_child = "DELETE FROM " . $table_name . " WHERE sapiSonok = ?";
                    $stmt_delete_child = mysqli_prepare($koneksi, $sql_delete_child);
                    if ($stmt_delete_child) {
                        mysqli_stmt_bind_param($stmt_delete_child, "i", $sapi_sonok_detail_id);
                        mysqli_stmt_execute($stmt_delete_child);
                        if (mysqli_stmt_error($stmt_delete_child)) {
                            $_SESSION['pesan_error'] = "Error saat menghapus dari " . $table_name . ": " . mysqli_stmt_error($stmt_delete_child);
                            $deletion_successful = false;
                            break;
                        }
                        mysqli_stmt_close($stmt_delete_child);
                    } else {
                        $_SESSION['pesan_error'] = "Gagal mempersiapkan statement DELETE untuk " . $table_name . ": " . mysqli_error($koneksi);
                        $deletion_successful = false;
                        break;
                    }
                }
            } else {
                // Untuk tabel detail sapi lainnya (sapiSonok, sapiKerap, dll.)
                $sql_delete_child = "DELETE FROM " . $table_name . " WHERE id_sapi = ?";
                $stmt_delete_child = mysqli_prepare($koneksi, $sql_delete_child);
                if ($stmt_delete_child) {
                    mysqli_stmt_bind_param($stmt_delete_child, "i", $id_sapi_to_delete);
                    mysqli_stmt_execute($stmt_delete_child);
                    if (mysqli_stmt_error($stmt_delete_child)) {
                        $_SESSION['pesan_error'] = "Error saat menghapus dari " . $table_name . ": " . mysqli_stmt_error($stmt_delete_child);
                        $deletion_successful = false;
                        break;
                    }
                    mysqli_stmt_close($stmt_delete_child);
                } else {
                    $_SESSION['pesan_error'] = "Gagal mempersiapkan statement DELETE untuk " . $table_name . ": " . mysqli_error($koneksi);
                    $deletion_successful = false;
                    break;
                }
            }
        }
    }

    // --- Hapus data dari tabel parent (data_sapi) jika penghapusan child berhasil ---
    if ($deletion_successful) {
        $sql_delete_parent = "DELETE FROM data_sapi WHERE id_sapi = ?";
        $stmt_delete_parent = mysqli_prepare($koneksi, $sql_delete_parent);

        if ($stmt_delete_parent === false) {
            $_SESSION['pesan_error'] = "Gagal mempersiapkan statement penghapusan data_sapi: " . mysqli_error($koneksi);
            $deletion_successful = false;
        } else {
            mysqli_stmt_bind_param($stmt_delete_parent, "i", $id_sapi_to_delete);
            if (mysqli_stmt_execute($stmt_delete_parent)) {
                if (mysqli_stmt_affected_rows($stmt_delete_parent) > 0) {
                    $_SESSION['pesan_sukses'] = "Data sapi dengan ID " . htmlspecialchars($id_sapi_to_delete) . " berhasil dihapus.";
                } else {
                    $_SESSION['pesan_info'] = "Tidak ada sapi dengan ID " . htmlspecialchars($id_sapi_to_delete) . " ditemukan untuk dihapus.";
                }
            } else {
                $_SESSION['pesan_error'] = "Error saat menghapus data dari data_sapi: " . mysqli_stmt_error($stmt_delete_parent);
                $deletion_successful = false;
            }
            mysqli_stmt_close($stmt_delete_parent);
        }
    }

    // --- Komit atau Rollback transaksi ---
    if ($deletion_successful) {
        mysqli_commit($koneksi);
    } else {
        mysqli_rollback($koneksi);
        // Pastikan pesan error sudah diatur di $_SESSION
        if (!isset($_SESSION['pesan_error'])) {
            $_SESSION['pesan_error'] = "Gagal menghapus sapi dengan ID " . htmlspecialchars($id_sapi_to_delete) . " karena kesalahan internal.";
        }
    }
} else {
    $_SESSION['pesan_error'] = "ID sapi tidak ditemukan. Harap berikan ID sapi yang akan dihapus.";
}

// Tutup koneksi database
mysqli_close($koneksi);

// Redirect kembali ke halaman data_sapi.php
header("Location: data_sapi.php");
exit();
