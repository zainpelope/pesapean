<?php
include '../koneksi.php';

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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesapean - Rute Sapi</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        /* CSS Khusus Halaman Rute Sapi */

        /* Gaya untuk navigasi sekunder */
        .secondary-navbar {

            --primary-color: rgb(240, 161, 44);
            background-color: #f8f8f8;
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
            border-radius: 8px;
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
            background-color: rgb(245, 172, 15);
            color: #fff;
            box-shadow: 0 4px 10px rgba(0, 123, 255, 0.2);
            pointer-events: none;
        }

        /* Gaya untuk container utama */
        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            text-align: center;
            /* Memusatkan semua konten di dalamnya */
            transition: all 0.3s ease;
        }

        /* Gaya untuk Judul Bagian */
        .section-title {
            font-size: 2.5em;
            color: #212529;
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
            background-color: #007bff;
            border-radius: 3px;
        }

        /* Gaya untuk pembungkus konten di dalam container */
        .content-wrapper {
            background-color: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 25px;
            margin-top: 30px;
            box-shadow: inset 0 1px 5px rgba(0, 0, 0, 0.08);
            text-align: center;
            overflow-x: auto;
        }

        /* Gaya untuk tabel data (jika ada di halaman populasi atau data sapi) */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto;
            /* Pusatkan tabel */
            font-size: 0.95em;
            color: #333;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #dee2e6;
            padding: 12px 15px;
            text-align: left;
        }

        .data-table th {
            background-color: #e9ecef;
            font-weight: 600;
            color: #495057;
            text-transform: uppercase;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .data-table tbody tr:hover {
            background-color: #e6f7ff;
        }

        /* Gaya tombol umum */
        .btn {
            display: inline-block;
            padding: 12px 28px;
            border: none;
            border-radius: 8px;
            font-size: 1.05em;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
            margin: 8px;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            /* Sudah ada di style.css, ini hanya contoh */
            background-color: #007bff;
            color: #fff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.2);
        }

        .btn-secondary {
            background-color: #28a745;
            color: #fff;
        }

        .btn-secondary:hover {
            background-color: #218838;
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(40, 167, 69, 0.3);
        }

        .btn-tertiary {
            background-color: #6c757d;
            color: #fff;
        }

        .btn-tertiary:hover {
            background-color: #5a6268;
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(108, 117, 125, 0.3);
        }


        /* Gaya khusus untuk form */
        .form-card {
            background-color: #fff;
            border: 1px solid #dcdcdc;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .route-form {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            justify-content: center;
            align-items: flex-end;
        }

        .form-group {
            flex: 1;
            min-width: 280px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 700;
            color: #444;
            font-size: 1em;
        }

        .form-group select {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            font-size: 1.05em;
            color: #333;
            background-color: #fff;
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
            border-color: #007bff;
            box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.3);
            outline: none;
        }

        /* Gaya untuk info rute */
        .route-info-card {
            background-color: #eaf6ff;
            border: 1px solid #b3d9ff;
            border-radius: 10px;
            padding: 30px;
            margin-top: 30px;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.1);
        }

        .route-info-card h3 {
            color: #0056b3;
            font-size: 2em;
            margin-bottom: 20px;
        }

        .route-info-card .cow-name {
            color: #007bff;
            font-weight: 700;
        }

        .route-info-card p {
            color: #444;
            font-size: 1.1em;
            line-height: 1.7;
            margin-bottom: 25px;
        }

        .route-info-card .fallback-text {
            margin-top: 30px;
            font-size: 0.95em;
            color: #666;
        }

        /* Gaya untuk pesan informasi (jika tidak ada data) */
        .info-message {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            border-radius: 10px;
            padding: 25px;
            margin-top: 30px;
            color: #856404;
            font-size: 1.15em;
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.15);
            font-weight: 500;
        }


        /* Responsif Dasar */
        @media (max-width: 992px) {
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

            .route-form {
                flex-direction: column;
                gap: 20px;
            }

            .form-group {
                min-width: 100%;
            }
        }

        @media (max-width: 768px) {
            .container {
                margin: 20px auto;
                padding: 20px;
                border-radius: 8px;
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
            .route-info-card,
            .info-message {
                padding: 20px;
                border-radius: 8px;
            }

            .btn {
                padding: 10px 20px;
                font-size: 0.95em;
                margin: 5px;
            }
        }
    </style>
</head>

<body>
    <header class="main-header">
        <nav class="navbar">
            <div class="logo">
                <a href="../pembeli/beranda.php">Pesapean</a>
            </div>
            <ul class="nav-links">
                <li><a href="../pembeli/beranda.php">Beranda</a></li>
                <li><a href="../pembeli/peta.php">Peta Interaktif</a></li>
                <li><a href="../pembeli/data_sapi.php?jenis=sonok">Data Sapi</a></li>
                <li><a href="../pembeli/lelang.php">Lelang</a></li>
                <li><a href="../pembeli/pesan.php">Pesan</a></li>

            </ul>
            <div class="auth-links">
                <a href="#login" class="btn btn-primary">Profile</a>
            </div>
        </nav>
    </header>

    <div class="secondary-navbar">
        <a href="peta.php">Map Sapi</a>
        <a href="rute.php" class="active">Rute Sapi</a>
        <a href="populasi.php">Populasi Sapi</a>
    </div>

    <div class="container">
        <h2 class="section-title">Rute Menuju Lokasi Sapi</h2>

        <div class="form-card">
            <form method="GET" action="rute.php" class="route-form">
                <div class="form-group">
                    <label for="kategori">Pilih Kategori Sapi:</label>
                    <select name="kategori" id="kategori" onchange="this.form.submit()">
                        <option value="">-- Pilih Kategori --</option>
                        <?php mysqli_data_seek($kategoriQuery, 0); ?>
                        <?php while ($kat = mysqli_fetch_assoc($kategoriQuery)) : ?>
                            <option value="<?= $kat['id_macamSapi'] ?>" <?= ($selectedKategori == $kat['id_macamSapi']) ? 'selected' : '' ?>>
                                <?= $kat['name'] ?>
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
                                    <?= $sapi['nama_sapi'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <?php if (!empty($destinationLatitude) && !empty($destinationLongitude)) : ?>
            <div class="route-info-card">
                <h3>Rute ke Sapi: <span class="cow-name"><?= htmlspecialchars($cowName) ?></span></h3>
                <p>Klik tombol "Lihat Rute" untuk mendapatkan petunjuk arah dari lokasi Anda saat ini ke lokasi sapi.</p>
                <button class="btn btn-secondary" onclick="getRoute()">Lihat Rute</button>

                <p class="fallback-text">Jika geolokasi tidak tersedia atau Anda ingin membuka langsung:</p>
                <a id="googleMapsLink" href="#" target="_blank" class="btn btn-tertiary" style="display: none;">Buka di Google Maps (Tujuan Saja)</a>
            </div>

            <script>
                function getRoute() {
                    var destinationLat = <?= $destinationLatitude ?>;
                    var destinationLon = <?= $destinationLongitude ?>;
                    var googleMapsLink = document.getElementById('googleMapsLink');

                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(function(position) {
                            var originLat = position.coords.latitude;
                            var originLon = position.coords.longitude;
                            // Correct Google Maps URL for directions from origin to destination
                            var googleMapsUrl = `https://www.google.com/maps/dir/${originLat},${originLon}/${destinationLat},${destinationLon}`;
                            window.open(googleMapsUrl, '_blank');
                        }, function(error) {
                            alert('Gagal mendapatkan lokasi Anda. Pastikan layanan lokasi diaktifkan. Anda dapat menggunakan tautan "Buka di Google Maps (Tujuan Saja)" di bawah.');
                            // Fallback to direct link to destination only
                            var googleMapsUrl = `https://www.google.com/maps/search/?api=1&query=${destinationLat},${destinationLon}`;
                            googleMapsLink.href = googleMapsUrl;
                            googleMapsLink.textContent = 'Buka di Google Maps (Tujuan Saja)';
                            googleMapsLink.style.display = 'inline-block';
                        });
                    } else {
                        alert('Geolocation tidak didukung oleh browser Anda. Anda dapat menggunakan tautan "Buka di Google Maps (Tujuan Saja)" di bawah.');
                        // Fallback to direct link to destination only
                        var googleMapsUrl = `https://www.google.com/maps/search/?api=1&query=${destinationLat},${destinationLon}`;
                        googleMapsLink.href = googleMapsUrl;
                        googleMapsLink.textContent = 'Buka di Google Maps (Tujuan Saja)';
                        googleMapsLink.style.display = 'inline-block';
                    }
                }

                // Initial setup for the manual link (if geolocation is not supported or not used)
                document.addEventListener('DOMContentLoaded', function() {
                    var destinationLat = <?= $destinationLatitude ?>;
                    var destinationLon = <?= $destinationLongitude ?>;
                    var googleMapsLink = document.getElementById('googleMapsLink');
                    // This link will open Google Maps centered on the destination, without a route from current location
                    var googleMapsUrl = `https://www.google.com/maps/search/?api=1&query=${destinationLat},${destinationLon}`;
                    googleMapsLink.href = googleMapsUrl;
                    googleMapsLink.textContent = 'Buka di Google Maps (Tujuan Saja)';
                    googleMapsLink.style.display = 'inline-block';
                });
            </script>
        <?php elseif (!empty($selectedKategori)) : ?>
            <div class="info-message">
                <p>Silakan pilih sapi untuk melihat rute menuju lokasinya.</p>
            </div>
        <?php else : ?>
            <div class="info-message">
                <p>Silakan pilih kategori dan sapi untuk melihat rute menuju lokasinya.</p>
            </div>
        <?php endif; ?>
    </div>
    <?php include '../footer.php'; ?>
</body>

</html>