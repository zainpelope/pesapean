<?php
// process_form.php
// File ini akan memproses data yang dikirim dari formulir dan menyimpannya ke database.

include 'db_connect.php'; // Memasukkan file koneksi database

// Inisialisasi variabel untuk pesan sukses/error
$message = '';
$messageType = ''; // 'success' atau 'error'

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Ambil dan sanitasi data dari tabel data_sapi
    $id_macamSapi = isset($_POST['id_macamSapi']) ? sanitize_input($_POST['id_macamSapi']) : '';
    $foto_sapi = isset($_POST['foto_sapi']) ? sanitize_input($_POST['foto_sapi']) : '';
    $harga_sapi = isset($_POST['harga_sapi']) ? (int)sanitize_input($_POST['harga_sapi']) : 0;
    $nama_pemilik = isset($_POST['nama_pemilik']) ? sanitize_input($_POST['nama_pemilik']) : '';
    $alamat_pemilik = isset($_POST['alamat_pemilik']) ? sanitize_input($_POST['alamat_pemilik']) : '';
    $nomor_pemilik = isset($_POST['nomor_pemilik']) ? sanitize_input($_POST['nomor_pemilik']) : '';
    $email_pemilik = isset($_POST['email_pemilik']) ? sanitize_input($_POST['email_pemilik']) : '';
    $createdAt = date('Y-m-d H:i:s');
    $updatedAt = date('Y-m-d H:i:s');

    // Validasi dasar
    if (empty($id_macamSapi) || empty($harga_sapi) || empty($nama_pemilik) || empty($alamat_pemilik) || empty($nomor_pemilik) || empty($email_pemilik)) {
        $message = "Error: Semua kolom utama harus diisi.";
        $messageType = "error";
    } else {
        // Mulai transaksi untuk memastikan integritas data
        $conn->begin_transaction();

        try {
            // 2. Masukkan data ke tabel data_sapi
            $stmt_data_sapi = $conn->prepare("INSERT INTO data_sapi (id_macamSapi, foto_sapi, harga_sapi, nama_pemilik, alamat_pemilik, nomor_pemilik, email_pemilik, createdAt, updatedAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_data_sapi->bind_param("isdssssss", $id_macamSapi, $foto_sapi, $harga_sapi, $nama_pemilik, $alamat_pemilik, $nomor_pemilik, $email_pemilik, $createdAt, $updatedAt);

            if (!$stmt_data_sapi->execute()) {
                throw new Exception("Error saat menyimpan data_sapi: " . $stmt_data_sapi->error);
            }

            $id_sapi = $conn->insert_id; // Dapatkan ID sapi yang baru saja dimasukkan
            $stmt_data_sapi->close();

            // 3. Masukkan data ke tabel spesifik jenis sapi
            switch ($id_macamSapi) {
                case '1': // Sapi Sonok
                    $nama_sapi = sanitize_input($_POST['sonok_nama_sapi'] ?? '');
                    $umur = sanitize_input($_POST['sonok_umur'] ?? '');
                    $lingkar_dada = sanitize_input($_POST['sonok_lingkar_dada'] ?? '');
                    $panjang_badan = sanitize_input($_POST['sonok_panjang_badan'] ?? '');
                    $tinggi_pundak = sanitize_input($_POST['sonok_tinggi_pundak'] ?? '');
                    $tinggi_punggung = sanitize_input($_POST['sonok_tinggi_punggung'] ?? '');
                    $panjang_wajah = sanitize_input($_POST['sonok_panjang_wajah'] ?? '');
                    $lebar_punggul = sanitize_input($_POST['sonok_lebar_punggul'] ?? '');
                    $lebar_dada = sanitize_input($_POST['sonok_lebar_dada'] ?? '');
                    $tinggi_kaki = sanitize_input($_POST['sonok_tinggi_kaki'] ?? '');
                    $kesehatan = sanitize_input($_POST['sonok_kesehatan'] ?? '');
                    $generasiSatu = (int)sanitize_input($_POST['sonok_generasiSatu'] ?? 0);
                    $generasiDua = (int)sanitize_input($_POST['sonok_generasiDua'] ?? 0);

                    $stmt_sonok = $conn->prepare("INSERT INTO sapiSonok (id_sapi, nama_sapi, umur, lingkar_dada, panjang_badan, tinggi_pundak, tinggi_punggung, panjang_wajah, lebar_punggul, lebar_dada, tinggi_kaki, kesehatan, generasiSatu, generasiDua) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt_sonok->bind_param("isssssssssssii", $id_sapi, $nama_sapi, $umur, $lingkar_dada, $panjang_badan, $tinggi_pundak, $tinggi_punggung, $panjang_wajah, $lebar_punggul, $lebar_dada, $tinggi_kaki, $kesehatan, $generasiSatu, $generasiDua);
                    if (!$stmt_sonok->execute()) {
                        throw new Exception("Error saat menyimpan sapiSonok: " . $stmt_sonok->error);
                    }
                    $stmt_sonok->close();
                    break;

                case '2': // Sapi Kerap
                    $nama_sapi = sanitize_input($_POST['kerap_nama_sapi'] ?? '');
                    $ketahanan_fisik = sanitize_input($_POST['kerap_ketahanan_fisik'] ?? '');
                    $kecepatan_lari = sanitize_input($_POST['kerap_kecepatan_lari'] ?? '');
                    $penghargaan = sanitize_input($_POST['kerap_penghargaan'] ?? '');

                    $stmt_kerap = $conn->prepare("INSERT INTO sapiKerap (id_sapi, nama_sapi, ketahanan_fisik, kecepatan_lari, penghargaan) VALUES (?, ?, ?, ?, ?)");
                    $stmt_kerap->bind_param("issss", $id_sapi, $nama_sapi, $ketahanan_fisik, $kecepatan_lari, $penghargaan);
                    if (!$stmt_kerap->execute()) {
                        throw new Exception("Error saat menyimpan sapiKerap: " . $stmt_kerap->error);
                    }
                    $stmt_kerap->close();
                    break;

                case '3': // Sapi Tangeh
                    $tinggi_badan = sanitize_input($_POST['tangeh_tinggi_badan'] ?? '');
                    $panjang_badan = sanitize_input($_POST['tangeh_panjang_badan'] ?? '');
                    $lingkar_dada = sanitize_input($_POST['tangeh_lingkar_dada'] ?? '');
                    $bobot_badan = sanitize_input($_POST['tangeh_bobot_badan'] ?? '');
                    $intensitas_latihan = sanitize_input($_POST['tangeh_intensitas_latihan'] ?? '');
                    $jarak_latihan = sanitize_input($_POST['tangeh_jarak_latihan'] ?? '');
                    $prestasi = sanitize_input($_POST['tangeh_prestasi'] ?? '');
                    $kesehatan = sanitize_input($_POST['tangeh_kesehatan'] ?? '');

                    $stmt_tangeh = $conn->prepare("INSERT INTO sapiTangeh (id_sapi, tinggi_badan, panjang_badan, lingkar_dada, bobot_badan, intensitas_latihan, jarak_latihan, prestasi, kesehatan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt_tangeh->bind_param("issssssss", $id_sapi, $tinggi_badan, $panjang_badan, $lingkar_dada, $bobot_badan, $intensitas_latihan, $jarak_latihan, $prestasi, $kesehatan);
                    if (!$stmt_tangeh->execute()) {
                        throw new Exception("Error saat menyimpan sapiTangeh: " . $stmt_tangeh->error);
                    }
                    $stmt_tangeh->close();
                    break;

                case '4': // Sapi Termak
                    $nama_sapi = sanitize_input($_POST['termak_nama_sapi'] ?? '');
                    $kesuburan = sanitize_input($_POST['termak_kesuburan'] ?? '');
                    $riwayat_kesehatan = sanitize_input($_POST['termak_riwayat_kesehatan'] ?? '');

                    $stmt_termak = $conn->prepare("INSERT INTO sapiTermak (id_sapi, nama_sapi, kesuburan, riwayat_kesehatan) VALUES (?, ?, ?, ?)");
                    $stmt_termak->bind_param("isss", $id_sapi, $nama_sapi, $kesuburan, $riwayat_kesehatan);
                    if (!$stmt_termak->execute()) {
                        throw new Exception("Error saat menyimpan sapiTermak: " . $stmt_termak->error);
                    }
                    $stmt_termak->close();
                    break;

                case '5': // Sapi Potong
                    $nama_sapi = sanitize_input($_POST['potong_nama_sapi'] ?? '');
                    $berat_badan = sanitize_input($_POST['potong_berat_badan'] ?? '');
                    $persentase_daging = sanitize_input($_POST['potong_persentase_daging'] ?? '');

                    $stmt_potong = $conn->prepare("INSERT INTO sapiPotong (id_sapi, nama_sapi, berat_badan, persentase_daging) VALUES (?, ?, ?, ?)");
                    $stmt_potong->bind_param("isss", $id_sapi, $nama_sapi, $berat_badan, $persentase_daging);
                    if (!$stmt_potong->execute()) {
                        throw new Exception("Error saat menyimpan sapiPotong: " . $stmt_potong->error);
                    }
                    $stmt_potong->close();
                    break;

                default:
                    // Tidak ada tindakan spesifik jika id_macamSapi tidak cocok
                    break;
            }

            $conn->commit(); // Commit transaksi jika semua berhasil
            $message = "Data sapi berhasil disimpan!";
            $messageType = "success";
        } catch (Exception $e) {
            $conn->rollback(); // Rollback transaksi jika ada error
            $message = "Error: " . $e->getMessage();
            $messageType = "error";
        }
    }

    $conn->close();
} else {
    $message = "Akses tidak valid.";
    $messageType = "error";
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Penyimpanan Data</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
            text-align: center;
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .message.success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #34d399;
        }

        .message.error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
        }

        .btn-back {
            background-color: #6b7280;
            color: white;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
            text-decoration: none;
            display: inline-block;
        }

        .btn-back:hover {
            background-color: #4b5563;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2 class="text-3xl font-bold text-gray-800 mb-6">Status Penyimpanan Data</h2>
        <div class="message <?php echo htmlspecialchars($messageType); ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <a href="index.php" class="btn-back">Kembali ke Formulir</a>
    </div>
</body>

</html>