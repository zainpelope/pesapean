<?php
// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Mulai sesi (penting untuk mengecek status login dan mendapatkan id_user)
session_start();

include 'koneksi.php'; // Pastikan path ini benar untuk koneksi database Anda

// --- PENTING: Cek status login dan peran pengguna ---
if (!isset($_SESSION['id_user']) || $_SESSION['nama_role'] !== 'Penjual') {
    // Jika tidak login atau bukan penjual, redirect ke halaman login dengan pesan error
    header("Location: ../auth/login.php?error=Akses tidak diizinkan. Anda harus login sebagai Penjual untuk membuat lelang.");
    exit();
}

// Ambil ID user penjual yang sedang login dari sesi
$id_user_penjual_login = $_SESSION['id_user'];

// Ambil semua kategori sapi dari macamSapi
$kategoriQuery = mysqli_query($koneksi, "SELECT id_macamSapi, name FROM macamSapi ORDER BY name ASC");
$kategori_options = [];
if ($kategoriQuery) {
    while ($row = mysqli_fetch_assoc($kategoriQuery)) {
        $kategori_options[] = $row;
    }
}

// Jika form sudah dipilih
$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
$dataSapi = [];

if ($jenis != '') {
    // Ambil ID kategori
    // Menggunakan prepared statement untuk mengambil id_macamSapi
    $stmt_get_kategori = mysqli_prepare($koneksi, "SELECT id_macamSapi FROM macamSapi WHERE name = ?");
    if ($stmt_get_kategori) {
        mysqli_stmt_bind_param($stmt_get_kategori, "s", $jenis);
        mysqli_stmt_execute($stmt_get_kategori);
        $result_get_kategori = mysqli_stmt_get_result($stmt_get_kategori);
        $kategori = mysqli_fetch_assoc($result_get_kategori);
        mysqli_stmt_close($stmt_get_kategori);

        if ($kategori) {
            $id_macam = $kategori['id_macamSapi'];

            // Ambil data sapi dari data_sapi berdasarkan macamSapi DAN id_user_penjual
            // Menggunakan prepared statement untuk query data sapi
            $queryData = "SELECT id_sapi, nama_pemilik, harga_sapi FROM data_sapi WHERE id_macamSapi = ? AND id_user_penjual = ?";
            $stmt_data = mysqli_prepare($koneksi, $queryData);

            if ($stmt_data) {
                mysqli_stmt_bind_param($stmt_data, "ii", $id_macam, $id_user_penjual_login);
                mysqli_stmt_execute($stmt_data);
                $result_data = mysqli_stmt_get_result($stmt_data);

                while ($row = mysqli_fetch_assoc($result_data)) {
                    $dataSapi[] = $row;
                }
                mysqli_stmt_close($stmt_data);
            } else {
                // Handle error jika prepared statement gagal
                die("Error prepared statement for data sapi: " . mysqli_error($koneksi));
            }
        }
    } else {
        die("Error prepared statement for category: " . mysqli_error($koneksi));
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Form Lelang Sapi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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

        h3 {
            color: #007bff;
            margin-bottom: 30px;
            text-align: center;
        }

        .form-label {
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
    </style>
</head>

<body class="bg-light">
    <div class="container mt-4">
        <h3>Pendaftaran Lelang Sapi</h3>

        <!-- Pilih Jenis Sapi -->
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
                <label for="jenis" class="form-label">Pilih Jenis Sapi</label>
                <select name="jenis" id="jenis" class="form-select" onchange="this.form.submit()" required>
                    <option value="">-- Pilih Jenis --</option>
                    <?php foreach ($kategori_options as $kategori) : ?>
                        <option value="<?= htmlspecialchars($kategori['name']); ?>" <?= ($kategori['name'] == $jenis) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($kategori['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <!-- Tampilkan sapi berdasarkan jenis -->
        <?php if ($jenis != '') : ?>
            <form method="POST" action="proses_lelang.php">
                <input type="hidden" name="jenis" value="<?= htmlspecialchars($jenis); ?>">
                <div class="mb-3">
                    <label for="id_sapi" class="form-label">Pilih Sapi</label>
                    <select name="id_sapi" id="id_sapi" class="form-select" required>
                        <option value="">-- Pilih Sapi --</option>
                        <?php if (empty($dataSapi)): ?>
                            <option value="" disabled>Tidak ada sapi yang Anda miliki untuk jenis ini.</option>
                        <?php else: ?>
                            <?php foreach ($dataSapi as $sapi) : ?>
                                <option value="<?= htmlspecialchars($sapi['id_sapi']); ?>">
                                    <?= "ID: {$sapi['id_sapi']} - Pemilik: {$sapi['nama_pemilik']} - Harga: Rp" . number_format($sapi['harga_sapi']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Form input data lelang -->
                <div class="mb-3">
                    <label class="form-label">Harga Awal (Limit)</label>
                    <input type="number" name="harga_awal" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Uang Jaminan (Harga Tertinggi Sementara)</label>
                    <input type="number" name="harga_tertinggi" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Batas Waktu</label>
                    <input type="datetime-local" name="batas_waktu" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="Aktif">Aktif</option> <!-- Default to Aktif for new auctions -->
                        <option value="Sedang Berlangsung">Sedang Berlangsung</option>
                        <option value="Lewat">Lewat</option>
                    </select>
                </div>

                <button type="submit" name="submit" class="btn btn-primary">Simpan Lelang</button>
            </form>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>