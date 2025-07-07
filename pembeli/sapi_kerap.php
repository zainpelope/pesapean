<?php
// Sertakan file koneksi database
include '../koneksi.php';

$message = ''; // Variabel untuk menyimpan pesan sukses atau error

// Proses form jika disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $id_sapi = $_POST['id_sapi'];
    $nama_sapi = $_POST['nama_sapi'];
    $ketahanan_fisik = $_POST['ketahanan_fisik'];
    $kecepatan_lari = $_POST['kecepatan_lari'];
    $penghargaan = $_POST['penghargaan'];

    // Validasi sederhana (Anda bisa menambahkan validasi yang lebih kompleks)
    if (empty($id_sapi) || empty($nama_sapi) || empty($ketahanan_fisik) || empty($kecepatan_lari) || empty($penghargaan)) {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <strong>Error!</strong> Semua field harus diisi.
                    </div>';
    } else {
        // Lindungi dari SQL Injection
        $id_sapi = mysqli_real_escape_string($koneksi, $id_sapi);
        $nama_sapi = mysqli_real_escape_string($koneksi, $nama_sapi);
        $ketahanan_fisik = mysqli_real_escape_string($koneksi, $ketahanan_fisik);
        $kecepatan_lari = mysqli_real_escape_string($koneksi, $kecepatan_lari);
        $penghargaan = mysqli_real_escape_string($koneksi, $penghargaan);

        // Query untuk memasukkan data ke tabel sapiKerap
        // Karena id_sapi adalah PRIMARY KEY di sapiKerap, kita akan menggunakan INSERT ... ON DUPLICATE KEY UPDATE
        // atau UPDATE jika data sudah ada, atau INSERT jika belum ada.
        // Untuk kasus ini, karena id_sapi juga PK di sapiKerap dan FK ke data_sapi,
        // kita asumsikan setiap id_sapi hanya memiliki satu entri di sapiKerap.
        // Jadi, jika id_sapi sudah ada, kita akan update. Jika belum, kita insert.

        $sql = "INSERT INTO sapiKerap (id_sapi, nama_sapi, ketahanan_fisik, kecepatan_lari, penghargaan)
                VALUES ('$id_sapi', '$nama_sapi', '$ketahanan_fisik', '$kecepatan_lari', '$penghargaan')
                ON DUPLICATE KEY UPDATE
                nama_sapi = VALUES(nama_sapi),
                ketahanan_fisik = VALUES(ketahanan_fisik),
                kecepatan_lari = VALUES(kecepatan_lari),
                penghargaan = VALUES(penghargaan)";

        if (mysqli_query($koneksi, $sql)) {
            $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            Data sapi Kerap berhasil disimpan!
                        </div>';
        } else {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong>Error:</strong> ' . mysqli_error($koneksi) . '
                        </div>';
        }
    }
}

// Ambil data id_sapi dari tabel data_sapi untuk dropdown
$data_sapi_options = [];
$sql_data_sapi = "SELECT id_sapi, nama_pemilik FROM data_sapi";
$result_data_sapi = mysqli_query($koneksi, $sql_data_sapi);

if (mysqli_num_rows($result_data_sapi) > 0) {
    while ($row = mysqli_fetch_assoc($result_data_sapi)) {
        $data_sapi_options[] = $row;
    }
} else {
    $message .= '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">
                    Tidak ada data sapi di tabel `data_sapi`. Harap tambahkan data sapi terlebih dahulu.
                </div>';
}

// Tutup koneksi database
mysqli_close($koneksi);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Data Sapi Kerap</title>
    <!-- Tailwind CSS CDN -->
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
            max-width: 600px;
            width: 100%;
        }

        .form-group label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
            display: block;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            color: #4b5563;
            transition: border-color 0.2s ease-in-out;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }

        .submit-button {
            background-color: #2563eb;
            color: #ffffff;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
            width: 100%;
        }

        .submit-button:hover {
            background-color: #1d4ed8;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">Input Data Sapi Kerap</h2>

        <?php echo $message; // Tampilkan pesan 
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-6">
            <div class="form-group">
                <label for="id_sapi">ID Sapi:</label>
                <select id="id_sapi" name="id_sapi" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                    <option value="">-- Pilih ID Sapi --</option>
                    <?php
                    foreach ($data_sapi_options as $option) {
                        echo '<option value="' . htmlspecialchars($option['id_sapi']) . '">' . htmlspecialchars($option['id_sapi']) . ' - ' . htmlspecialchars($option['nama_pemilik']) . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="nama_sapi">Nama Sapi:</label>
                <input type="text" id="nama_sapi" name="nama_sapi" class="mt-1 block w-full" placeholder="Masukkan nama sapi" required>
            </div>

            <div class="form-group">
                <label for="ketahanan_fisik">Ketahanan Fisik:</label>
                <input type="text" id="ketahanan_fisik" name="ketahanan_fisik" class="mt-1 block w-full" placeholder="Contoh: Sangat Baik, Kuat" required>
            </div>

            <div class="form-group">
                <label for="kecepatan_lari">Kecepatan Lari:</label>
                <input type="text" id="kecepatan_lari" name="kecepatan_lari" class="mt-1 block w-full" placeholder="Contoh: Sangat Cepat, Rata-rata" required>
            </div>

            <div class="form-group">
                <label for="penghargaan">Penghargaan:</label>
                <input type="text" id="penghargaan" name="penghargaan" class="mt-1 block w-full" placeholder="Contoh: Juara 1 Karapan Sapi, Tidak Ada" required>
            </div>

            <button type="submit" class="submit-button">Simpan Data Sapi Kerap</button>
        </form>
    </div>
</body>

</html>