<?php
include 'koneksi.php';

// Ambil semua data sapi sonok beserta generasi
$sapi_sonok = mysqli_query($koneksi, "
    SELECT ds.*, ss.*, 
           gs1.namaPejantanGenerasiSatu, gs1.jenisPejantanGenerasiSatu, gs1.namaIndukGenerasiSatu, gs1.jenisIndukGenerasiSatu,
           gd2.namaPejantanGenerasiDua, gd2.jenisPejantanGenerasiDua, gd2.namaIndukGenerasiDua, gd2.jenisIndukGenerasiDua,
           gd2.namaKakekPejantanGenerasiDua, gd2.namaNenekPejantanGenerasiDua,
           gd2.namaKakekIndukGenerasiDua, gd2.namaNenekIndukGenerasiDua
    FROM data_sapi ds
    INNER JOIN sapiSonok ss ON ds.id_sapi = ss.id
    LEFT JOIN generasiSatu gs1 ON ss.generasiSatu = gs1.id
    LEFT JOIN generasiDua gd2 ON ss.generasiDua = gd2.id
");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Detail Sapi Sonok</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .carousel-item {
            padding: 30px 15px;
        }

        .sapi-img {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 10px;
        }

        .box {
            background-color: #f8f9fa;
            padding: 15px;
            margin-top: 15px;
            border-radius: 10px;
        }

        .label {
            font-weight: bold;
            color: darkred;
        }
    </style>
</head>

<body class="container mt-5">

    <h2 class="mb-4 text-center">Detail Sapi Sonok</h2>

    <div id="carouselSapiSonok" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php
            $first = true;
            while ($sapi = mysqli_fetch_assoc($sapi_sonok)):
            ?>
                <div class="carousel-item <?= $first ? 'active' : '' ?>">
                    <div class="row">
                        <div class="about-image">
                            <?php if ($data && isset($data['gambar'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($data['gambar']); ?>" alt="A decorated cow">
                            <?php else: ?>
                                <img src="placeholder.jpg" alt="No Image Available">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h4><?= $sapi['nama_sapi'] ?></h4>
                            <p><span class="label">Harga:</span> Rp <?= number_format($sapi['harga_sapi'], 0, ',', '.') ?></p>
                            <p><span class="label">Umur:</span> <?= $sapi['umur'] ?></p>
                            <p><span class="label">Lingkar Dada:</span> <?= $sapi['lingkar_dada'] ?> cm</p>
                            <p><span class="label">Panjang Badan:</span> <?= $sapi['panjang_badan'] ?> cm</p>
                            <p><span class="label">Tinggi Badan:</span> <?= $sapi['tinggi_badan'] ?> cm</p>
                            <p><span class="label">Tinggi Pundak:</span> <?= $sapi['tinggi_pundak'] ?> cm</p>
                            <p><span class="label">Lebar Dahi:</span> <?= $sapi['lebar_dahi'] ?> cm</p>
                            <p><span class="label">Panjang Wajah:</span> <?= $sapi['panjang_wajah'] ?> cm</p>
                            <p><span class="label">Lebar Pinggul:</span> <?= $sapi['lebar_pinggul'] ?> cm</p>
                            <p><span class="label">Tinggi Kaki:</span> <?= $sapi['tinggi_kaki'] ?> cm</p>
                            <p><span class="label">Warna Bulu:</span> <?= $sapi['warna_bulu'] ?></p>

                            <!-- Contact -->
                            <div class="box">
                                <h6>Contact Person</h6>
                                <p>Nama: <?= $sapi['nama_pemilik'] ?></p>
                                <p>Alamat: <?= $sapi['alamat_pemilik'] ?></p>
                                <p>Nomor HP: <?= $sapi['nomor_pemilik'] ?></p>
                                <p>Email: <?= $sapi['email_pemilik'] ?></p>
                            </div>

                            <!-- Generasi 1 -->
                            <?php if (!empty($sapi['namaPejantanGenerasiSatu'])): ?>
                                <div class="box">
                                    <h6>Generasi 1</h6>
                                    <p>Pejantan: <?= $sapi['namaPejantanGenerasiSatu'] ?> (<?= $sapi['jenisPejantanGenerasiSatu'] ?>)</p>
                                    <p>Induk: <?= $sapi['namaIndukGenerasiSatu'] ?> (<?= $sapi['jenisIndukGenerasiSatu'] ?>)</p>
                                </div>
                            <?php endif; ?>

                            <!-- Generasi 2 -->
                            <?php if (!empty($sapi['namaPejantanGenerasiDua'])): ?>
                                <div class="box">
                                    <h6>Generasi 2</h6>
                                    <p>Pejantan: <?= $sapi['namaPejantanGenerasiDua'] ?> (<?= $sapi['jenisPejantanGenerasiDua'] ?>)</p>
                                    <p>Induk: <?= $sapi['namaIndukGenerasiDua'] ?> (<?= $sapi['jenisIndukGenerasiDua'] ?>)</p>
                                    <p>Kakek Pejantan: <?= $sapi['namaKakekPejantanGenerasiDua'] ?></p>
                                    <p>Nenek Pejantan: <?= $sapi['namaNenekPejantanGenerasiDua'] ?></p>
                                    <p>Kakek Induk: <?= $sapi['namaKakekIndukGenerasiDua'] ?></p>
                                    <p>Nenek Induk: <?= $sapi['namaNenekIndukGenerasiDua'] ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php
                $first = false;
            endwhile;
            ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselSapiSonok" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselSapiSonok" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>