<?php
include 'koneksi.php';

$kategoriQuery = mysqli_query($koneksi, "SELECT * FROM macamSapi");

$selectedKategori = $_GET['kategori'] ?? '';
$selectedSapiId = $_GET['id_sapi'] ?? '';

$sapiList = [];
$latitude = '';
$longitude = '';

$tabelMapping = [
    '1' => 'sapiSonok',
    '2' => 'sapiKerap',
    '3' => 'sapiTangghek',
    '4' => 'sapiTernak',
    '5' => 'sapiPotong'
];

$tabelSapi = $tabelMapping[$selectedKategori] ?? null;

if (!empty($selectedKategori) && $tabelSapi) {
    $query = mysqli_query($koneksi, "
        SELECT s.id_sapi, d.nama_pemilik AS nama_sapi
        FROM $tabelSapi s
        JOIN data_sapi d ON s.id_sapi = d.id_sapi
    ");
    while ($row = mysqli_fetch_assoc($query)) {
        $sapiList[] = $row;
    }
}

if (!empty($selectedSapiId) && $tabelSapi) {
    $lokasiQuery = mysqli_query($koneksi, "
        SELECT d.latitude, d.longitude
        FROM $tabelSapi s
        JOIN data_sapi d ON s.id_sapi = d.id_sapi
        WHERE s.id_sapi = '$selectedSapiId'
    ");
    $lokasi = mysqli_fetch_assoc($lokasiQuery);
    $latitude = $lokasi['latitude'] ?? '';
    $longitude = $lokasi['longitude'] ?? '';
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Peta Sapi</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            margin: 0;
        }

        .navbar {
            background-color: #333;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .navbar a {
            float: left;
            display: block;
            color: white;
            text-align: center;
            padding: 14px 20px;
            text-decoration: none;
        }

        .navbar a:hover {
            background-color: #ddd;
            color: black;
        }

        #map {
            height: 400px;
            width: 100%;
            margin-top: 20px;
            border: 1px solid #ccc;
        }
    </style>
</head>

<body>

    <div class="navbar">
        <a href="peta.php">Map Sapi</a>
        <a href="rute.php">Rute Sapi</a>
        <a href="populasi.php">Populasi Sapi</a>
    </div>

    <h2>Peta Lokasi Sapi</h2>

    <form method="GET" action="peta.php">
        <label for="kategori">Pilih Kategori Sapi:</label>
        <select name="kategori" onchange="this.form.submit()">
            <option value="">-- Pilih Kategori --</option>
            <?php mysqli_data_seek($kategoriQuery, 0); // Reset pointer for second use 
            ?>
            <?php while ($kat = mysqli_fetch_assoc($kategoriQuery)): ?>
                <option value="<?= $kat['id_macamSapi'] ?>" <?= ($selectedKategori == $kat['id_macamSapi']) ? 'selected' : '' ?>>
                    <?= $kat['name'] ?>
                </option>
            <?php endwhile; ?>
        </select>

        <?php if (!empty($sapiList)): ?>
            <label for="id_sapi">Pilih Sapi:</label>
            <select name="id_sapi" onchange="this.form.submit()">
                <option value="">-- Pilih Sapi --</option>
                <?php foreach ($sapiList as $sapi): ?>
                    <option value="<?= $sapi['id_sapi'] ?>" <?= ($selectedSapiId == $sapi['id_sapi']) ? 'selected' : '' ?>>
                        <?= $sapi['nama_sapi'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
    </form>

    <?php if (!empty($latitude) && !empty($longitude)): ?>
        <div id="map"></div>
        <script>
            var map = L.map('map').setView([<?= $latitude ?>, <?= $longitude ?>], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);
            L.marker([<?= $latitude ?>, <?= $longitude ?>]).addTo(map)
                .bindPopup('Lokasi Sapi')
                .openPopup();
        </script>
    <?php endif; ?>

</body>

</html>