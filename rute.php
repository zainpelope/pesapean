<?php
include 'koneksi.php';

$kategoriQuery = mysqli_query($koneksi, "SELECT * FROM macamSapi");

$selectedKategori = $_GET['kategori'] ?? '';
$selectedSapiId = $_GET['id_sapi'] ?? '';

$sapiList = [];
$destinationLatitude = '';
$destinationLongitude = '';
$cowName = '';

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
        SELECT d.latitude, d.longitude, d.nama_pemilik
        FROM $tabelSapi s
        JOIN data_sapi d ON s.id_sapi = d.id_sapi
        WHERE s.id_sapi = '$selectedSapiId'
    ");
    $lokasi = mysqli_fetch_assoc($lokasiQuery);
    $destinationLatitude = $lokasi['latitude'] ?? '';
    $destinationLongitude = $lokasi['longitude'] ?? '';
    $cowName = $lokasi['nama_pemilik'] ?? '';
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Rute Sapi</title>
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

        #routeMap {
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

    <h2>Rute Menuju Lokasi Sapi</h2>

    <form method="GET" action="rute.php">
        <label for="kategori">Pilih Kategori Sapi:</label>
        <select name="kategori" onchange="this.form.submit()">
            <option value="">-- Pilih Kategori --</option>
            <?php mysqli_data_seek($kategoriQuery, 0); ?>
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

    <?php if (!empty($destinationLatitude) && !empty($destinationLongitude)): ?>
        <h3>Rute ke Sapi: <?= htmlspecialchars($cowName) ?></h3>
        <p>Klik tombol di bawah untuk melihat rute di Google Maps:</p>
        <button onclick="getRoute()">Lihat Rute</button>

        <p>Atau klik tautan berikut (menggunakan lokasi Anda saat ini):</p>
        <a id="googleMapsLink" href="#" target="_blank" style="display: none;">Buka di Google Maps</a>

        <script>
            function getRoute() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        var originLat = position.coords.latitude;
                        var originLon = position.coords.longitude;
                        var destinationLat = <?= $destinationLatitude ?>;
                        var destinationLon = <?= $destinationLongitude ?>;
                        var googleMapsUrl = `https://www.google.com/maps/dir/?api=1&origin=${originLat},${originLon}&destination=${destinationLat},${destinationLon}&travelmode=driving`;
                        window.open(googleMapsUrl, '_blank');
                    }, function(error) {
                        alert('Gagal mendapatkan lokasi Anda. Pastikan layanan lokasi diaktifkan. Anda dapat mencoba tautan manual di bawah.');
                        // Fallback to direct link if geolocation fails
                        var destinationLat = <?= $destinationLatitude ?>;
                        var destinationLon = <?= $destinationLongitude ?>;
                        var googleMapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${destinationLat},${destinationLon}&travelmode=driving`;
                        document.getElementById('googleMapsLink').href = googleMapsUrl;
                        document.getElementById('googleMapsLink').style.display = 'block';
                    });
                } else {
                    alert('Geolocation tidak didukung oleh browser Anda. Anda dapat mencoba tautan manual di bawah.');
                    // Fallback to direct link if geolocation not supported
                    var destinationLat = <?= $destinationLatitude ?>;
                    var destinationLon = <?= $destinationLongitude ?>;
                    var googleMapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${destinationLat},${destinationLon}&travelmode=driving`;
                    document.getElementById('googleMapsLink').href = googleMapsUrl;
                    document.getElementById('googleMapsLink').style.display = 'block';
                }
            }

            // Display manual link if no geolocation or user declines
            if (!navigator.geolocation) {
                var destinationLat = <?= $destinationLatitude ?>;
                var destinationLon = <?= $destinationLongitude ?>;
                var googleMapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${destinationLat},${destinationLon}&travelmode=driving`;
                document.getElementById('googleMapsLink').href = googleMapsUrl;
                document.getElementById('googleMapsLink').style.display = 'block';
            }
        </script>
    <?php elseif (!empty($selectedKategori)): ?>
        <p>Silakan pilih sapi untuk melihat rute.</p>
    <?php endif; ?>

</body>

</html>