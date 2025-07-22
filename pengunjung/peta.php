<?php
// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Mulai sesi (penting untuk mengecek status login)
session_start();

include '../koneksi.php'; // Pastikan path ini benar sesuai struktur folder Anda

$kategoriQuery = mysqli_query($koneksi, "SELECT * FROM macamSapi");

$selectedKategori = $_GET['kategori'] ?? '';
$selectedSapiId = $_GET['id_sapi'] ?? '';

$sapiList = [];
$latitude = '';
$longitude = '';
$cowName = ''; // Tambahkan variabel untuk nama sapi

$tabelMapping = [
    '1' => 'sapiSonok',
    '2' => 'sapiKerap',
    '3' => 'sapiTangghek',
    '4' => 'sapiTernak',
    '5' => 'sapiPotong'
];

$tabelSapi = $tabelMapping[$selectedKategori] ?? null;

if (!empty($selectedKategori) && $tabelSapi) {
    // Modified query to fetch both cow's specific name and owner's name
    $query = mysqli_query($koneksi, "
        SELECT ds.id_sapi, s.nama_sapi, ds.nama_pemilik
        FROM $tabelSapi s
        JOIN data_sapi ds ON s.id_sapi = ds.id_sapi
    ");
    while ($row = mysqli_fetch_assoc($query)) {
        $sapiList[] = $row;
    }
}

if (!empty($selectedSapiId) && $tabelSapi) {
    $lokasiQuery = mysqli_query($koneksi, "
        SELECT ds.latitude, ds.longitude, s.nama_sapi AS cow_name, ds.nama_pemilik AS owner_name
        FROM $tabelSapi s
        JOIN data_sapi ds ON s.id_sapi = ds.id_sapi
        WHERE s.id_sapi = '$selectedSapiId'
    ");
    $lokasi = mysqli_fetch_assoc($lokasiQuery);
    $latitude = $lokasi['latitude'] ?? '';
    $longitude = $lokasi['longitude'] ?? '';
    // Concatenate cow's name and owner's name for display
    if (!empty($lokasi['cow_name']) && !empty($lokasi['owner_name'])) {
        $cowName = $lokasi['cow_name'] . ' (' . $lokasi['owner_name'] . ')';
    } elseif (!empty($lokasi['cow_name'])) {
        $cowName = $lokasi['cow_name'];
    } elseif (!empty($lokasi['owner_name'])) {
        $cowName = 'Pemilik: ' . $lokasi['owner_name'];
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesapean - Peta Sapi</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">

    <style>
        /* Variabel CSS untuk konsistensi */
        :root {
            --primary-color: rgb(240, 161, 44);
            /* Biru utama */
            --secondary-color: #28a745;
            /* Hijau */
            --tertiary-color: #6c757d;
            /* Abu-abu */
            --dark-color: #333;
            /* Warna gelap untuk navbar */
            --dark-text: #212529;
            --light-bg: #f8f9fa;
            --white-bg: #ffffff;
            --border-color: #dee2e6;
            --box-shadow-light: 0 4px 15px rgba(0, 0, 0, 0.08);
            --box-shadow-medium: 0 8px 25px rgba(0, 0, 0, 0.15);
            --border-radius-sm: 8px;
            --border-radius-md: 10px;
            --border-radius-lg: 12px;
        }

        body {
            font-family: 'Open Sans', sans-serif;
            margin: 0;
            background-color: var(--light-bg);
            color: var(--dark-text);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* --- Header dan Navigasi Utama --- */
        .main-header {
            background-color: var(--dark-color);
            /* Menggunakan dark-color untuk background navbar */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            /* Bayangan sedikit lebih gelap untuk kontras */
            padding: 15px 0;
            position: relative;
            z-index: 1000;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .logo a {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 1.8em;
            color: var(--primary-color);
            /* Warna logo tetap primary-color */
            text-decoration: none;
        }

        .nav-links {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
        }

        .nav-links li {
            margin-left: 30px;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--white-bg);
            /* Warna teks link navbar jadi putih */
            font-weight: 600;
            font-size: 1em;
            padding: 5px 0;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: var(--primary-color);
            /* Warna hover tetap primary-color */
        }

        /* Styling for auth-links (Login/Profile/Logout) */
        .auth-links {
            display: flex;
            gap: 10px;
            /* Jarak antar tombol */
            align-items: center;
        }

        .auth-links .btn {
            padding: 10px 20px;
            border-radius: var(--border-radius-sm);
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .auth-links .btn-primary {
            background-color: var(--primary-color);
            color: var(--white-bg);
            border: none;
        }

        .auth-links .btn-primary:hover {
            background-color: #0056b3;
            /* Slightly darker blue */
            transform: translateY(-1px);
        }

        .auth-links .btn-outline-primary {
            background-color: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }

        .auth-links .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: var(--white-bg);
        }

        .auth-links .btn-danger {
            background-color: #dc3545;
            /* Red for logout */
            color: white;
            border: none;
        }

        .auth-links .btn-danger:hover {
            background-color: #c82333;
        }


        /* --- Navigasi Sekunder (Map Sapi, Rute Sapi, Populasi Sapi) --- */
        .secondary-navbar {
            background-color: var(--light-bg);
            padding: 12px 0;
            text-align: center;
            border-bottom: 1px solid #eee;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }

        .secondary-navbar a {
            color: #555;
            text-decoration: none;
            padding: 10px 20px;
            margin: 0 10px;
            border-radius: var(--border-radius-sm);
            transition: background-color 0.3s ease, color 0.3s ease, transform 0.2s ease;
            font-weight: 600;
            font-size: 1.05em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .secondary-navbar a:hover {
            background-color: #e9ecef;
            color: #333;
            transform: translateY(-2px);
        }

        .secondary-navbar a.active {
            background-color: var(--primary-color);
            color: var(--white-bg);
            box-shadow: 0 4px 10px rgba(0, 123, 255, 0.2);
            pointer-events: none;
            /* Nonaktifkan klik pada link aktif */
        }

        /* --- Konten Utama Container --- */
        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 30px;
            background-color: var(--white-bg);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--box-shadow-medium);
            text-align: center;
            /* Pusatkan konten di dalamnya */
            transition: all 0.3s ease;
        }

        /* Judul Bagian */
        .section-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 2.5em;
            color: var(--dark-text);
            margin-bottom: 35px;
            font-weight: 700;
            position: relative;
            padding-bottom: 15px;
        }

        .section-title::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%);
            width: 80px;
            height: 5px;
            background-color: var(--primary-color);
            border-radius: 3px;
        }

        /* Card Formulir Pilihan */
        .form-card {
            background-color: var(--light-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-md);
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--box-shadow-light);
        }

        .cow-selection-form {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            justify-content: center;
            align-items: flex-end;
            /* Align items to the bottom */
        }

        .form-group {
            flex: 1;
            min-width: 280px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--dark-text);
            font-size: 1em;
        }

        .form-group select {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid #ced4da;
            border-radius: var(--border-radius-sm);
            font-size: 1.05em;
            color: var(--dark-text);
            background-color: var(--white-bg);
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23007bff%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13.2-5.4H18.4c-6.5%200-12.2%203.2-15.6%208.1-3.4%204.9-3.4%2011.1%200%2016l128%20128c3.4%203.4%208%205.4%2013.2%205.4s9.8-2%2013.2-5.4l128-128c3.4-4.9%203.4-11.1%200-16z%22%2F%3E%3C%2Fsvg%3E');
            background-repeat: no-repeat;
            background-position: right 18px center;
            background-size: 14px auto;
            cursor: pointer;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.3);
        }

        /* Gaya untuk Peta */
        #map {
            height: 500px;
            width: 100%;
            margin-top: 30px;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-md);
            box-shadow: var(--box-shadow-light);
            background-color: var(--white-bg);
        }

        /* Pesan Informasi */
        .info-message {
            background-color: #fff3cd;
            /* Kuning terang */
            border: 1px solid #ffeeba;
            border-radius: var(--border-radius-md);
            padding: 25px;
            margin-top: 30px;
            color: #856404;
            /* Teks kuning gelap */
            font-size: 1.15em;
            box-shadow: var(--box-shadow-light);
            font-weight: 500;
        }

        /* Nama Sapi di Popup Peta */
        .leaflet-popup-content-wrapper {
            border-radius: var(--border-radius-sm);
            box-shadow: var(--box-shadow-light);
            font-family: 'Open Sans', sans-serif;
        }

        .leaflet-popup-content {
            font-weight: 600;
            color: var(--dark-text);
            font-size: 1.1em;
        }

        /* --- Responsive Adjustments --- */
        @media (max-width: 992px) {
            .navbar {
                padding: 0 15px;
            }

            .nav-links li {
                margin-left: 20px;
            }

            .container {
                margin: 30px auto;
                padding: 25px;
                max-width: 90%;
            }

            .section-title {
                font-size: 2em;
            }

            .secondary-navbar a {
                margin: 0 8px;
                padding: 8px 15px;
                font-size: 1em;
            }

            .cow-selection-form {
                flex-direction: column;
                gap: 20px;
            }

            .form-group {
                min-width: 100%;
            }
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                align-items: flex-start;
                padding: 15px 20px;
            }

            .nav-links {
                flex-direction: column;
                width: 100%;
                margin-top: 15px;
            }

            .nav-links li {
                margin: 0 0 10px 0;
            }

            .auth-links {
                width: 100%;
                text-align: center;
                margin-top: 10px;
            }

            .auth-links .btn {
                width: calc(100% - 20px);
                margin: 0 10px;
            }

            .container {
                margin: 20px auto;
                padding: 20px;
                border-radius: var(--border-radius-sm);
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            }

            .section-title {
                font-size: 1.8em;
                margin-bottom: 25px;
            }

            .secondary-navbar {
                flex-direction: column;
                gap: 10px;
                padding: 10px 0;
            }

            .secondary-navbar a {
                margin: 0;
                width: 80%;
                text-align: center;
                justify-content: center;
            }

            .form-card,
            .info-message {
                padding: 20px;
                border-radius: var(--border-radius-sm);
            }

            #map {
                height: 350px;
            }
        }
    </style>
</head>

<body>
    <header class="main-header">
        <nav class="navbar">
            <div class="logo">
                <a href="../index.php">Pesapean</a>
            </div>
            <ul class="nav-links">
                <li><a href="../index.php">Beranda</a></li>
                <li><a href="../pengunjung/peta.php">Peta Interaktif</a></li>
                <li><a href="../pengunjung/data_sapi.php?jenis=sonok">Data Sapi</a></li>
                <li><a href="../pengunjung/lelang.php">Lelang</a></li>
                <li><a href="pengunjung/pesan.php" <?php if (!isset($_SESSION['id_user'])) echo 'data-bs-toggle="modal" data-bs-target="#loginPromptModal" onclick="return false;"'; ?>>Pesan</a></li>
            </ul>
            <div class="auth-links">
                <?php if (isset($_SESSION['id_user'])): ?>
                    <a href="../auth/profile.php" class="btn btn-primary">Profile</a>

                <?php else: ?>
                    <a href="../auth/login.php" class="btn btn-primary">Login</a>
                    <a href="../auth/register.php" class="btn btn-outline-primary">Daftar</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <div class="secondary-navbar">
        <a href="peta.php" class="active">Map Sapi</a>
        <a href="rute.php">Rute Sapi</a>
        <a href="populasi.php">Populasi Sapi</a>
    </div>

    <div class="container">
        <h2 class="section-title">Peta Lokasi Sapi</h2>

        <div class="form-card">
            <form method="GET" action="peta.php" class="cow-selection-form">
                <div class="form-group">
                    <label for="kategori">Pilih Kategori Sapi:</label>
                    <select name="kategori" id="kategori" onchange="this.form.submit()">
                        <option value="">-- Pilih Kategori --</option>
                        <?php mysqli_data_seek($kategoriQuery, 0); // Reset pointer for second use
                        ?>
                        <?php while ($kat = mysqli_fetch_assoc($kategoriQuery)) : ?>
                            <option value="<?= $kat['id_macamSapi'] ?>" <?= ($selectedKategori == $kat['id_macamSapi']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kat['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <?php if (!empty($sapiList)) : ?>
                    <div class="form-group">
                        <label for="id_sapi">Pilih Sapi:</label>
                        <select name="id_sapi" id="id_sapi" onchange="this.form.submit()">
                            <option value="">-- Pilih Sapi --</option>
                            <?php foreach ($sapiList as $sapi) : ?>
                                <option value="<?= $sapi['id_sapi'] ?>" <?= ($selectedSapiId == $sapi['id_sapi']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sapi['nama_sapi']) ?> (Pemilik: <?= htmlspecialchars($sapi['nama_pemilik']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <?php if (!empty($latitude) && !empty($longitude)) : ?>
            <div id="map"></div>
            <script>
                // Pastikan variabel PHP di-escape dengan baik untuk JavaScript
                var lat = <?= json_encode($latitude) ?>;
                var lon = <?= json_encode($longitude) ?>;
                var cowName = <?= json_encode($cowName) ?>;

                var map = L.map('map').setView([lat, lon], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);

                L.marker([lat, lon]).addTo(map)
                    .bindPopup('<b>Lokasi Sapi:</b> ' + cowName)
                    .openPopup();
            </script>
        <?php else : ?>
            <div class="info-message">
                <p>Silakan pilih kategori dan sapi untuk melihat lokasinya di peta.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../footer.php'; ?>
</body>

</html>