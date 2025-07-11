<?php
// Aktifkan error reporting untuk debugging selama pengembangan.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Mulai sesi PHP (untuk pesan sukses/error)
session_start();

// Sertakan file koneksi database Anda.
include '../koneksi.php';

$id_sapi = null;
$sapi_data = []; // Untuk menyimpan data sapi utama
$detail_sapi_data = []; // Untuk menyimpan data detail sapi (Sapi Kerap, Sonok, dll.)
$generasi_satu_data = []; // Untuk Sapi Sonok
$generasi_dua_data = []; // Untuk Sapi Sonok

// --- PROSES MENGAMBIL DATA SAPI LAMA UNTUK DITAMPILKAN DI FORM ---
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_sapi = $_GET['id'];

    if (!is_numeric($id_sapi)) {
        $_SESSION['pesan_error'] = "ID sapi tidak valid.";
        header("Location: data_sapi.php");
        exit();
    }

    // Query untuk mengambil data utama dari tabel `data_sapi`
    $query_main = "SELECT * FROM data_sapi WHERE id_sapi = ?";
    $stmt_main = mysqli_prepare($koneksi, $query_main);
    if ($stmt_main) {
        mysqli_stmt_bind_param($stmt_main, "i", $id_sapi);
        mysqli_stmt_execute($stmt_main);
        $result_main = mysqli_stmt_get_result($stmt_main);
        $sapi_data = mysqli_fetch_assoc($result_main);
        mysqli_stmt_close($stmt_main);

        if (!$sapi_data) {
            $_SESSION['pesan_error'] = "Data sapi tidak ditemukan.";
            header("Location: data_sapi.php");
            exit();
        }

        // Ambil nama macam sapi untuk menentukan form detail
        $id_macam_sapi = $sapi_data['id_macamSapi'];
        $query_macam_sapi = "SELECT name FROM macamSapi WHERE id_macamSapi = ?";
        $stmt_macam_sapi = mysqli_prepare($koneksi, $query_macam_sapi);
        if ($stmt_macam_sapi) {
            mysqli_stmt_bind_param($stmt_macam_sapi, "i", $id_macam_sapi);
            mysqli_stmt_execute($stmt_macam_sapi);
            $result_macam_sapi = mysqli_stmt_get_result($stmt_macam_sapi);
            $macam_sapi_row = mysqli_fetch_assoc($result_macam_sapi);
            if ($macam_sapi_row) {
                $sapi_data['macam_nama'] = $macam_sapi_row['name'];
            } else {
                $_SESSION['pesan_error'] = "Jenis sapi tidak dikenal.";
                header("Location: data_sapi.php");
                exit();
            }
            mysqli_stmt_close($stmt_macam_sapi);
        } else {
            $_SESSION['pesan_error'] = "Gagal mempersiapkan query jenis sapi: " . mysqli_error($koneksi);
            header("Location: data_sapi.php");
            exit();
        }

        // Ambil data dari tabel detail berdasarkan jenis sapi
        $table_name = '';
        $id_column = 'id_sapi'; // Kolom kunci untuk sebagian besar tabel detail
        switch ($sapi_data['macam_nama']) {
            case 'Sapi Kerap':
                $table_name = 'sapiKerap';
                break;
            case 'Sapi Sonok':
                $table_name = 'sapiSonok';
                // Untuk Sapi Sonok, kita juga perlu mengambil data generasi
                $query_sonok = "SELECT * FROM sapiSonok WHERE id_sapi = ?";
                $stmt_sonok = mysqli_prepare($koneksi, $query_sonok);
                if ($stmt_sonok) {
                    mysqli_stmt_bind_param($stmt_sonok, "i", $id_sapi);
                    mysqli_stmt_execute($stmt_sonok);
                    $result_sonok = mysqli_stmt_get_result($stmt_sonok);
                    $detail_sapi_data = mysqli_fetch_assoc($result_sonok);
                    mysqli_stmt_close($stmt_sonok);

                    if ($detail_sapi_data) {
                        $id_sapi_sonok = $detail_sapi_data['id']; // ID dari tabel sapiSonok
                        $query_gen1 = "SELECT * FROM generasiSatu WHERE sapiSonok = ?";
                        $stmt_gen1 = mysqli_prepare($koneksi, $query_gen1);
                        if ($stmt_gen1) {
                            mysqli_stmt_bind_param($stmt_gen1, "i", $id_sapi_sonok);
                            mysqli_stmt_execute($stmt_gen1);
                            $result_gen1 = mysqli_stmt_get_result($stmt_gen1);
                            $generasi_satu_data = mysqli_fetch_assoc($result_gen1);
                            mysqli_stmt_close($stmt_gen1);
                        }

                        $query_gen2 = "SELECT * FROM generasiDua WHERE sapiSonok = ?";
                        $stmt_gen2 = mysqli_prepare($koneksi, $query_gen2);
                        if ($stmt_gen2) {
                            mysqli_stmt_bind_param($stmt_gen2, "i", $id_sapi_sonok);
                            mysqli_stmt_execute($stmt_gen2);
                            $result_gen2 = mysqli_stmt_get_result($stmt_gen2);
                            $generasi_dua_data = mysqli_fetch_assoc($result_gen2);
                            mysqli_stmt_close($stmt_gen2);
                        }
                    }
                }
                break;
            case 'Sapi Tangghek':
                $table_name = 'sapiTangghek';
                break;
            case 'Sapi Potong':
                $table_name = 'sapiPotong';
                break;
            case 'Sapi Ternak':
                $table_name = 'sapiTernak';
                break;
        }

        // Ambil data detail jika bukan Sapi Sonok (karena sudah diambil di atas)
        if ($table_name && $sapi_data['macam_nama'] !== 'Sapi Sonok') {
            $query_detail = "SELECT * FROM " . $table_name . " WHERE " . $id_column . " = ?";
            $stmt_detail = mysqli_prepare($koneksi, $query_detail);
            if ($stmt_detail) {
                mysqli_stmt_bind_param($stmt_detail, "i", $id_sapi);
                mysqli_stmt_execute($stmt_detail);
                $result_detail = mysqli_stmt_get_result($stmt_detail);
                $detail_sapi_data = mysqli_fetch_assoc($result_detail);
                mysqli_stmt_close($stmt_detail);
            }
        }
    } else {
        $_SESSION['pesan_error'] = "Gagal mempersiapkan query utama: " . mysqli_error($koneksi);
        header("Location: data_sapi.php");
        exit();
    }
} else {
    $_SESSION['pesan_error'] = "ID sapi tidak ditemukan untuk diedit.";
    header("Location: data_sapi.php");
    exit();
}


// --- PROSES UPDATE DATA KETIKA FORM DI SUBMIT ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil input utama dari form
    $id_sapi_post = $_POST['id_sapi']; // Pastikan Anda menambahkan hidden input untuk id_sapi
    $id_macamSapi_post = $_POST['id_macamSapi'];
    $macam_nama_post = $_POST['macam_nama'];
    $harga_sapi_post = $_POST['harga_sapi'];
    $nama_pemilik_post = $_POST['nama_pemilik'];
    $alamat_pemilik_post = $_POST['alamat_pemilik'];
    $nomor_pemilik_post = $_POST['nomor_pemilik'];
    $email_pemilik_post = $_POST['email_pemilik'];
    $jenis_kelamin_post = $_POST['jenis_kelamin']; // Input jenis kelamin
    $latitude_post = $_POST['latitude'];
    $longitude_post = $_POST['longitude'];

    $updatedAt = date('Y-m-d H:i:s');
    $foto_sapi_post = $sapi_data['foto_sapi']; // Default ke foto lama

    // Penanganan upload foto baru
    if (isset($_FILES['foto_sapi']) && $_FILES['foto_sapi']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "../uploads/";
        $new_foto_name = basename($_FILES['foto_sapi']['name']);
        $target_file = $target_dir . $new_foto_name;

        // Pastikan direktori 'uploads/' ada
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Pindahkan file yang diupload
        if (move_uploaded_file($_FILES["foto_sapi"]["tmp_name"], $target_file)) {
            // Hapus foto lama jika ada dan berbeda dengan foto baru
            if (!empty($sapi_data['foto_sapi']) && $sapi_data['foto_sapi'] != $new_foto_name && file_exists($target_dir . $sapi_data['foto_sapi'])) {
                unlink($target_dir . $sapi_data['foto_sapi']);
            }
            $foto_sapi_post = $new_foto_name; // Gunakan nama foto baru
        } else {
            $_SESSION['pesan_error'] = "Gagal mengupload foto baru.";
            header("Location: edit_sapi.php?id=" . $id_sapi_post);
            exit();
        }
    }

    // Mulai transaksi untuk memastikan semua update berhasil atau tidak sama sekali
    mysqli_begin_transaction($koneksi);
    $update_successful = true;

    // --- Update data di tabel `data_sapi` ---
    $query_update_main = "UPDATE data_sapi SET id_macamSapi = ?, foto_sapi = ?, harga_sapi = ?, nama_pemilik = ?, alamat_pemilik = ?, nomor_pemilik = ?, email_pemilik = ?, updatedAt = ?, latitude = ?, longitude = ?, jenis_kelamin = ? WHERE id_sapi = ?";
    $stmt_update_main = mysqli_prepare($koneksi, $query_update_main);

    if ($stmt_update_main === false) {
        $_SESSION['pesan_error'] = "Gagal mempersiapkan statement update data utama: " . mysqli_error($koneksi);
        $update_successful = false;
    } else {
        mysqli_stmt_bind_param(
            $stmt_update_main,
            "isdssssssdss",
            $id_macamSapi_post,
            $foto_sapi_post,
            $harga_sapi_post,
            $nama_pemilik_post,
            $alamat_pemilik_post,
            $nomor_pemilik_post,
            $email_pemilik_post,
            $updatedAt,
            $latitude_post,
            $longitude_post,
            $jenis_kelamin_post,
            $id_sapi_post // WHERE clause
        );

        if (!mysqli_stmt_execute($stmt_update_main)) {
            $_SESSION['pesan_error'] = "Error saat mengupdate data sapi utama: " . mysqli_stmt_error($stmt_update_main);
            $update_successful = false;
        }
        mysqli_stmt_close($stmt_update_main);
    }

    // --- Update data di tabel spesifik berdasarkan jenis sapi ---
    if ($update_successful) {
        switch ($macam_nama_post) {
            case 'Sapi Kerap':
                $nama = $_POST['kerap_nama'];
                $fisik = $_POST['kerap_fisik'];
                $lari = $_POST['kerap_lari'];
                $penghargaan = $_POST['kerap_penghargaan'];
                $queryKerap = "UPDATE sapiKerap SET nama_sapi = ?, ketahanan_fisik = ?, kecepatan_lari = ?, penghargaan = ? WHERE id_sapi = ?";
                $stmtKerap = mysqli_prepare($koneksi, $queryKerap);
                if ($stmtKerap) {
                    mysqli_stmt_bind_param($stmtKerap, "ssssi", $nama, $fisik, $lari, $penghargaan, $id_sapi_post);
                    if (!mysqli_stmt_execute($stmtKerap)) {
                        $_SESSION['pesan_error'] = "Error saat mengupdate data Sapi Kerap: " . mysqli_stmt_error($stmtKerap);
                        $update_successful = false;
                    }
                    mysqli_stmt_close($stmtKerap);
                } else {
                    $_SESSION['pesan_error'] = "Gagal mempersiapkan update Sapi Kerap: " . mysqli_error($koneksi);
                    $update_successful = false;
                }
                break;

            case 'Sapi Sonok':
                $nama = $_POST['sonok_nama'];
                $umur = $_POST['sonok_umur'];
                $dada = $_POST['sonok_dada'];
                $panjang = $_POST['sonok_panjang'];
                $tinggi_pundak = $_POST['sonok_tinggi_pundak'];
                $tinggi_punggung = $_POST['sonok_tinggi_punggung'];
                $wajah = $_POST['sonok_wajah'];
                $punggul = $_POST['sonok_punggul'];
                $dada_lebar = $_POST['sonok_dada_lebar'];
                $kaki = $_POST['sonok_kaki'];
                $kesehatan = $_POST['sonok_kesehatan'];

                // Dapatkan ID dari sapiSonok yang terkait
                $id_sapiSonok = null;
                $query_get_sonok_id = "SELECT id FROM sapiSonok WHERE id_sapi = ?";
                $stmt_get_sonok_id = mysqli_prepare($koneksi, $query_get_sonok_id);
                if ($stmt_get_sonok_id) {
                    mysqli_stmt_bind_param($stmt_get_sonok_id, "i", $id_sapi_post);
                    mysqli_stmt_execute($stmt_get_sonok_id);
                    $result_sonok_id = mysqli_stmt_get_result($stmt_get_sonok_id);
                    $sonok_row = mysqli_fetch_assoc($result_sonok_id);
                    if ($sonok_row) {
                        $id_sapiSonok = $sonok_row['id'];
                    }
                    mysqli_stmt_close($stmt_get_sonok_id);
                }

                if ($id_sapiSonok) {
                    $querySonok = "UPDATE sapiSonok SET nama_sapi = ?, umur = ?, lingkar_dada = ?, panjang_badan = ?, tinggi_pundak = ?, tinggi_punggung = ?, panjang_wajah = ?, lebar_punggul = ?, lebar_dada = ?, tinggi_kaki = ?, kesehatan = ? WHERE id_sapi = ?";
                    $stmtSonok = mysqli_prepare($koneksi, $querySonok);
                    if ($stmtSonok) {
                        mysqli_stmt_bind_param($stmtSonok, "sssssssssssi", $nama, $umur, $dada, $panjang, $tinggi_pundak, $tinggi_punggung, $wajah, $punggul, $dada_lebar, $kaki, $kesehatan, $id_sapi_post);
                        if (!mysqli_stmt_execute($stmtSonok)) {
                            $_SESSION['pesan_error'] = "Error saat mengupdate data Sapi Sonok: " . mysqli_stmt_error($stmtSonok);
                            $update_successful = false;
                        }
                        mysqli_stmt_close($stmtSonok);
                    } else {
                        $_SESSION['pesan_error'] = "Gagal mempersiapkan update Sapi Sonok: " . mysqli_error($koneksi);
                        $update_successful = false;
                    }

                    // Update Generasi Satu
                    $gen1_pejantan = $_POST['gen1_pejantan'];
                    $gen1_jenis_pejantan = $_POST['gen1_jenis_pejantan'];
                    $gen1_induk = $_POST['gen1_induk'];
                    $gen1_jenis_induk = $_POST['gen1_jenis_induk'];
                    $gen1_kakek = $_POST['gen1_kakek'];

                    $queryGen1 = "UPDATE generasiSatu SET namaPejantanGenerasiSatu = ?, jenisPejantanGenerasiSatu = ?, namaIndukGenerasiSatu = ?, jenisIndukGenerasiSatu = ?, namaKakekPejantanGenerasiSatu = ?, updatedAt = ? WHERE sapiSonok = ?";
                    $stmtGen1 = mysqli_prepare($koneksi, $queryGen1);
                    if ($stmtGen1) {
                        mysqli_stmt_bind_param($stmtGen1, "ssssssi", $gen1_pejantan, $gen1_jenis_pejantan, $gen1_induk, $gen1_jenis_induk, $gen1_kakek, $updatedAt, $id_sapiSonok);
                        if (!mysqli_stmt_execute($stmtGen1)) {
                            $_SESSION['pesan_error'] = "Error saat mengupdate data Generasi Satu: " . mysqli_stmt_error($stmtGen1);
                            $update_successful = false;
                        }
                        mysqli_stmt_close($stmtGen1);
                    } else {
                        $_SESSION['pesan_error'] = "Gagal mempersiapkan update Generasi Satu: " . mysqli_error($koneksi);
                        $update_successful = false;
                    }

                    // Update Generasi Dua
                    $gen2_pejantan = $_POST['gen2_pejantan'];
                    $gen2_jenis_pejantan = $_POST['gen2_jenis_pejantan'];
                    $gen2_induk = $_POST['gen2_induk'];
                    $gen2_jenis_induk = $_POST['gen2_jenis_induk'];
                    $gen2_jenis_kakek = $_POST['gen2_jenis_kakek'];
                    $gen2_nenek = $_POST['gen2_nenek'];

                    $queryGen2 = "UPDATE generasiDua SET namaPejantanGenerasiDua = ?, jenisPejantanGenerasiDua = ?, namaIndukGenerasiDua = ?, jenisIndukGenerasiDua = ?, jenisKakekPejantanGenerasiDua = ?, namaNenekIndukGenerasiDua = ?, updatedAt = ? WHERE sapiSonok = ?";
                    $stmtGen2 = mysqli_prepare($koneksi, $queryGen2);
                    if ($stmtGen2) {
                        mysqli_stmt_bind_param($stmtGen2, "sssssssi", $gen2_pejantan, $gen2_jenis_pejantan, $gen2_induk, $gen2_jenis_induk, $gen2_jenis_kakek, $gen2_nenek, $updatedAt, $id_sapiSonok);
                        if (!mysqli_stmt_execute($stmtGen2)) {
                            $_SESSION['pesan_error'] = "Error saat mengupdate data Generasi Dua: " . mysqli_stmt_error($stmtGen2);
                            $update_successful = false;
                        }
                        mysqli_stmt_close($stmtGen2);
                    } else {
                        $_SESSION['pesan_error'] = "Gagal mempersiapkan update Generasi Dua: " . mysqli_error($koneksi);
                        $update_successful = false;
                    }
                } else {
                    $_SESSION['pesan_error'] = "ID Sapi Sonok tidak ditemukan untuk mengupdate detail.";
                    $update_successful = false;
                }
                break;

            case 'Sapi Tangghek':
                $tinggi = $_POST['tangeh_tinggi'];
                $panjang = $_POST['tangeh_panjang'];
                $dada = $_POST['tangeh_dada'];
                $bobot = $_POST['tangeh_bobot'];
                $latihan = $_POST['tangeh_latihan'];
                $jarak = $_POST['tangeh_jarak'];
                $prestasi = $_POST['tangeh_prestasi'];
                $kesehatan = $_POST['tangeh_kesehatan'];
                $queryTangghek = "UPDATE sapiTangghek SET tinggi_badan = ?, panjang_badan = ?, lingkar_dada = ?, bobot_badan = ?, intensitas_latihan = ?, jarak_latihan = ?, prestasi = ?, kesehatan = ? WHERE id_sapi = ?";
                $stmtTangghek = mysqli_prepare($koneksi, $queryTangghek);
                if ($stmtTangghek) {
                    mysqli_stmt_bind_param($stmtTangghek, "ssssssssi", $tinggi, $panjang, $dada, $bobot, $latihan, $jarak, $prestasi, $kesehatan, $id_sapi_post);
                    if (!mysqli_stmt_execute($stmtTangghek)) {
                        $_SESSION['pesan_error'] = "Error saat mengupdate data Sapi Tangghek: " . mysqli_stmt_error($stmtTangghek);
                        $update_successful = false;
                    }
                    mysqli_stmt_close($stmtTangghek);
                } else {
                    $_SESSION['pesan_error'] = "Gagal mempersiapkan update Sapi Tangghek: " . mysqli_error($koneksi);
                    $update_successful = false;
                }
                break;

            case 'Sapi Potong':
                $nama = $_POST['potong_nama'];
                $berat = $_POST['potong_berat'];
                $persentase = $_POST['potong_persen'];
                $queryPotong = "UPDATE sapiPotong SET nama_sapi = ?, berat_badan = ?, persentase_daging = ? WHERE id_sapi = ?";
                $stmtPotong = mysqli_prepare($koneksi, $queryPotong);
                if ($stmtPotong) {
                    mysqli_stmt_bind_param($stmtPotong, "sssi", $nama, $berat, $persentase, $id_sapi_post);
                    if (!mysqli_stmt_execute($stmtPotong)) {
                        $_SESSION['pesan_error'] = "Error saat mengupdate data Sapi Potong: " . mysqli_stmt_error($stmtPotong);
                        $update_successful = false;
                    }
                    mysqli_stmt_close($stmtPotong);
                } else {
                    $_SESSION['pesan_error'] = "Gagal mempersiapkan update Sapi Potong: " . mysqli_error($koneksi);
                    $update_successful = false;
                }
                break;

            case 'Sapi Ternak':
                $nama = $_POST['termak_nama'];
                $kesuburan = $_POST['termak_subur'];
                $riwayat = $_POST['termak_riwayat'];
                $queryTernak = "UPDATE sapiTernak SET nama_sapi = ?, kesuburan = ?, riwayat_kesehatan = ? WHERE id_sapi = ?";
                $stmtTernak = mysqli_prepare($koneksi, $queryTernak);
                if ($stmtTernak) {
                    mysqli_stmt_bind_param($stmtTernak, "sssi", $nama, $kesuburan, $riwayat, $id_sapi_post);
                    if (!mysqli_stmt_execute($stmtTernak)) {
                        $_SESSION['pesan_error'] = "Error saat mengupdate data Sapi Ternak: " . mysqli_stmt_error($stmtTernak);
                        $update_successful = false;
                    }
                    mysqli_stmt_close($stmtTernak);
                } else {
                    $_SESSION['pesan_error'] = "Gagal mempersiapkan update Sapi Ternak: " . mysqli_error($koneksi);
                    $update_successful = false;
                }
                break;
        }
    }

    // --- Komit atau Rollback transaksi ---
    if ($update_successful) {
        mysqli_commit($koneksi);
        $_SESSION['pesan_sukses'] = "Data sapi berhasil diupdate!";
        header("Location: data_sapi.php");
        exit();
    } else {
        mysqli_rollback($koneksi);
        // Pesan error sudah diatur di atas, jika tidak ada tambahkan generic
        if (!isset($_SESSION['pesan_error'])) {
            $_SESSION['pesan_error'] = "Gagal mengupdate data sapi. Silakan coba lagi.";
        }
        // Redirect kembali ke halaman edit dengan ID yang sama
        header("Location: edit_sapi.php?id=" . $id_sapi_post);
        exit();
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Form Edit Data Sapi</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            max-width: 800px;
            margin-top: 50px;
            margin-bottom: 50px;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #007bff;
            margin-bottom: 30px;
            text-align: center;
        }

        h4 {
            color: #6c757d;
            margin-top: 25px;
            margin-bottom: 15px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
        }

        .form-group label {
            font-weight: bold;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .current-photo {
            max-width: 150px;
            height: auto;
            margin-top: 10px;
            display: block;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<body>
    <div class="container">
        <h2>Form Edit Data Sapi</h2>
        <?php
        // Tampilkan pesan sukses/error
        if (isset($_SESSION['pesan_sukses'])) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . $_SESSION['pesan_sukses'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            unset($_SESSION['pesan_sukses']);
        }
        if (isset($_SESSION['pesan_error'])) {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . $_SESSION['pesan_error'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            unset($_SESSION['pesan_error']);
        }
        ?>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="id_sapi" value="<?= htmlspecialchars($id_sapi) ?>">

            <div class="form-group">
                <label for="macamSapi">Jenis Sapi:</label>
                <select name="id_macamSapi" id="macamSapi" class="form-control" required disabled>
                    <option value="">-- Pilih Jenis Sapi --</option>
                    <?php
                    $result_macam = mysqli_query($koneksi, "SELECT id_macamSapi, name FROM macamSapi");
                    while ($row_macam = mysqli_fetch_assoc($result_macam)) {
                        $selected = ($sapi_data['id_macamSapi'] == $row_macam['id_macamSapi']) ? 'selected' : '';
                        echo "<option value='{$row_macam['id_macamSapi']}' data-nama='{$row_macam['name']}' {$selected}>{$row_macam['name']}</option>";
                    }
                    ?>
                </select>
                <input type="hidden" name="id_macamSapi" value="<?= htmlspecialchars($sapi_data['id_macamSapi'] ?? '') ?>">
                <input type="hidden" name="macam_nama" id="macamNama" value="<?= htmlspecialchars($sapi_data['macam_nama'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="jenis_kelamin">Jenis Kelamin:</label>
                <select name="jenis_kelamin" id="jenis_kelamin" class="form-control" required>
                    <option value="">-- Pilih Jenis Kelamin --</option>
                    <option value="jantan" <?= ($sapi_data['jenis_kelamin'] ?? '') == 'jantan' ? 'selected' : '' ?>>Jantan</option>
                    <option value="betina" <?= ($sapi_data['jenis_kelamin'] ?? '') == 'betina' ? 'selected' : '' ?>>Betina</option>
                </select>
            </div>

            <div class="form-group">
                <label for="foto_sapi">Foto Sapi:</label>
                <?php if (!empty($sapi_data['foto_sapi'])) : ?>
                    <img src="../uploads/<?= htmlspecialchars($sapi_data['foto_sapi']) ?>" alt="Foto Sapi Saat Ini" class="current-photo">
                    <small class="form-text text-muted">Foto saat ini. Upload foto baru jika ingin mengubahnya.</small>
                <?php endif; ?>
                <input type="file" name="foto_sapi" id="foto_sapi" class="form-control-file">
            </div>

            <div class="form-group">
                <label for="harga_sapi">Harga Sapi:</label>
                <input type="number" name="harga_sapi" id="harga_sapi" class="form-control" value="<?= htmlspecialchars($sapi_data['harga_sapi'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="nama_pemilik">Nama Pemilik:</label>
                <input type="text" name="nama_pemilik" id="nama_pemilik" class="form-control" value="<?= htmlspecialchars($sapi_data['nama_pemilik'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="alamat_pemilik">Alamat Pemilik:</label>
                <textarea name="alamat_pemilik" id="alamat_pemilik" class="form-control" rows="3" required><?= htmlspecialchars($sapi_data['alamat_pemilik'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="nomor_pemilik">Nomor Pemilik:</label>
                <input type="text" name="nomor_pemilik" id="nomor_pemilik" class="form-control" value="<?= htmlspecialchars($sapi_data['nomor_pemilik'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="email_pemilik">Email Pemilik:</label>
                <input type="email" name="email_pemilik" id="email_pemilik" class="form-control" value="<?= htmlspecialchars($sapi_data['email_pemilik'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="latitude">Latitude:</label>
                <input type="text" name="latitude" id="latitude" class="form-control" placeholder="Contoh: -7.2575" value="<?= htmlspecialchars($sapi_data['latitude'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="longitude">Longitude:</label>
                <input type="text" name="longitude" id="longitude" class="form-control" placeholder="Contoh: 112.7522" value="<?= htmlspecialchars($sapi_data['longitude'] ?? '') ?>" required>
            </div>

            <div id="formJenis">
            </div>

            <button type="submit" class="btn btn-primary btn-block">Update Data</button>
            <a href="../penjual/data_sapi.php?jenis=all" class="btn btn-secondary btn-block">Batal dan Kembali</a>
        </form>
    </div>

    <script>
        // Data forms detail ini sama seperti yang Anda miliki di form tambah
        const forms = {
            "Sapi Kerap": `
                <h4>Form Sapi Kerap</h4>
                <div class="form-group">
                    <input type="text" name="kerap_nama" class="form-control" placeholder="Nama Sapi" value="<?= htmlspecialchars($detail_sapi_data['nama_sapi'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="kerap_fisik" class="form-control" placeholder="Ketahanan Fisik" value="<?= htmlspecialchars($detail_sapi_data['ketahanan_fisik'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="kerap_lari" class="form-control" placeholder="Kecepatan Lari" value="<?= htmlspecialchars($detail_sapi_data['kecepatan_lari'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="kerap_penghargaan" class="form-control" placeholder="Penghargaan" value="<?= htmlspecialchars($detail_sapi_data['penghargaan'] ?? '') ?>" required>
                </div>
            `,
            "Sapi Sonok": `
                <h4>Form Sapi Sonok</h4>
                <div class="form-group">
                    <input type="text" name="sonok_nama" class="form-control" placeholder="Nama Sapi" value="<?= htmlspecialchars($detail_sapi_data['nama_sapi'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="sonok_umur" class="form-control" placeholder="Umur" value="<?= htmlspecialchars($detail_sapi_data['umur'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="sonok_dada" class="form-control" placeholder="Lingkar Dada" value="<?= htmlspecialchars($detail_sapi_data['lingkar_dada'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="sonok_panjang" class="form-control" placeholder="Panjang Badan" value="<?= htmlspecialchars($detail_sapi_data['panjang_badan'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="sonok_tinggi_pundak" class="form-control" placeholder="Tinggi Pundak" value="<?= htmlspecialchars($detail_sapi_data['tinggi_pundak'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="sonok_tinggi_punggung" class="form-control" placeholder="Tinggi Punggung" value="<?= htmlspecialchars($detail_sapi_data['tinggi_punggung'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="sonok_wajah" class="form-control" placeholder="Panjang Wajah" value="<?= htmlspecialchars($detail_sapi_data['panjang_wajah'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="sonok_punggul" class="form-control" placeholder="Lebar Punggul" value="<?= htmlspecialchars($detail_sapi_data['lebar_punggul'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="sonok_dada_lebar" class="form-control" placeholder="Lebar Dada" value="<?= htmlspecialchars($detail_sapi_data['lebar_dada'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="sonok_kaki" class="form-control" placeholder="Tinggi Kaki" value="<?= htmlspecialchars($detail_sapi_data['tinggi_kaki'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="sonok_kesehatan" class="form-control" placeholder="Kesehatan" value="<?= htmlspecialchars($detail_sapi_data['kesehatan'] ?? '') ?>" required>
                </div>

                <h4>Generasi Satu</h4>
                <div class="form-group">
                    <input type="text" name="gen1_pejantan" class="form-control" placeholder="Nama Pejantan" value="<?= htmlspecialchars($generasi_satu_data['namaPejantanGenerasiSatu'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="gen1_jenis_pejantan" class="form-control" placeholder="Jenis Pejantan" value="<?= htmlspecialchars($generasi_satu_data['jenisPejantanGenerasiSatu'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="gen1_induk" class="form-control" placeholder="Nama Induk" value="<?= htmlspecialchars($generasi_satu_data['namaIndukGenerasiSatu'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="gen1_jenis_induk" class="form-control" placeholder="Jenis Induk" value="<?= htmlspecialchars($generasi_satu_data['jenisIndukGenerasiSatu'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="gen1_kakek" class="form-control" placeholder="Nama Kakek Pejantan" value="<?= htmlspecialchars($generasi_satu_data['namaKakekPejantanGenerasiSatu'] ?? '') ?>" required>
                </div>

                <h4>Generasi Dua</h4>
                <div class="form-group">
                    <input type="text" name="gen2_pejantan" class="form-control" placeholder="Nama Pejantan Generasi Dua" value="<?= htmlspecialchars($generasi_dua_data['namaPejantanGenerasiDua'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="gen2_jenis_pejantan" class="form-control" placeholder="Jenis Pejantan Generasi Dua" value="<?= htmlspecialchars($generasi_dua_data['jenisPejantanGenerasiDua'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="gen2_induk" class="form-control" placeholder="Nama Induk Generasi Dua" value="<?= htmlspecialchars($generasi_dua_data['namaIndukGenerasiDua'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="gen2_jenis_induk" class="form-control" placeholder="Jenis Induk Generasi Dua" value="<?= htmlspecialchars($generasi_dua_data['jenisIndukGenerasiDua'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="gen2_jenis_kakek" class="form-control" placeholder="Jenis Kakek Pejantan" value="<?= htmlspecialchars($generasi_dua_data['jenisKakekPejantanGenerasiDua'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="gen2_nenek" class="form-control" placeholder="Nama Nenek Induk" value="<?= htmlspecialchars($generasi_dua_data['namaNenekIndukGenerasiDua'] ?? '') ?>" required>
                </div>
            `,
            "Sapi Tangghek": `
                <h4>Form Sapi Tangghek</h4>
                <div class="form-group">
                    <input type="text" name="tangeh_tinggi" class="form-control" placeholder="Tinggi Badan" value="<?= htmlspecialchars($detail_sapi_data['tinggi_badan'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="tangeh_panjang" class="form-control" placeholder="Panjang Badan" value="<?= htmlspecialchars($detail_sapi_data['panjang_badan'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="tangeh_dada" class="form-control" placeholder="Lingkar Dada" value="<?= htmlspecialchars($detail_sapi_data['lingkar_dada'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="tangeh_bobot" class="form-control" placeholder="Bobot Badan" value="<?= htmlspecialchars($detail_sapi_data['bobot_badan'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="tangeh_latihan" class="form-control" placeholder="Intensitas Latihan" value="<?= htmlspecialchars($detail_sapi_data['intensitas_latihan'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="tangeh_jarak" class="form-control" placeholder="Jarak Latihan" value="<?= htmlspecialchars($detail_sapi_data['jarak_latihan'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="tangeh_prestasi" class="form-control" placeholder="Prestasi" value="<?= htmlspecialchars($detail_sapi_data['prestasi'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="tangeh_kesehatan" class="form-control" placeholder="Kesehatan" value="<?= htmlspecialchars($detail_sapi_data['kesehatan'] ?? '') ?>" required>
                </div>
            `,
            "Sapi Potong": `
                <h4>Form Sapi Potong</h4>
                <div class="form-group">
                    <input type="text" name="potong_nama" class="form-control" placeholder="Nama Sapi" value="<?= htmlspecialchars($detail_sapi_data['nama_sapi'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="potong_berat" class="form-control" placeholder="Berat Badan" value="<?= htmlspecialchars($detail_sapi_data['berat_badan'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="potong_persen" class="form-control" placeholder="Persentase Daging" value="<?= htmlspecialchars($detail_sapi_data['persentase_daging'] ?? '') ?>" required>
                </div>
            `,
            "Sapi Ternak": `
                <h4>Form Sapi Ternak</h4>
                <div class="form-group">
                    <input type="text" name="termak_nama" class="form-control" placeholder="Nama Sapi" value="<?= htmlspecialchars($detail_sapi_data['nama_sapi'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="termak_subur" class="form-control" placeholder="Kesuburan" value="<?= htmlspecialchars($detail_sapi_data['kesuburan'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="termak_riwayat" class="form-control" placeholder="Riwayat Kesehatan" value="<?= htmlspecialchars($detail_sapi_data['riwayat_kesehatan'] ?? '') ?>" required>
                </div>
            `
        };

        // Ketika dokumen siap, tampilkan form detail yang sesuai
        $(document).ready(function() {
            const selectedMacamSapiName = $('#macamSapi option:selected').attr('data-nama');
            if (selectedMacamSapiName) {
                $('#formJenis').html(forms[selectedMacamSapiName] || '');
            }
            // Karena select jenis sapi di-disable, tidak perlu event listener .change()
        });
    </script>
</body>

</html>