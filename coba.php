<?php
include 'koneksi.php';

// Update otomatis status jadi 'Lewat' jika sudah melewati batas waktu
mysqli_query($koneksi, "
    UPDATE lelang 
    SET status = 'Lewat', updatedAt = NOW() 
    WHERE batas_waktu < NOW() AND status = 'Aktif'
");

// Ambil semua kategori dari tabel macamSapi
$queryKategori = mysqli_query($koneksi, "SELECT * FROM macamSapi");

// Ambil kategori terpilih dari URL (GET)
$selectedKategori = isset($_GET['kategori']) ? $_GET['kategori'] : 'semua';

// Query sapi yang sedang dilelang
$queryDataSapi = mysqli_query($koneksi, "
    SELECT 
        ds.id_sapi,
        ds.foto_sapi,
        ds.alamat_pemilik,
        ms.name AS kategori,
        l.harga_awal,
        l.harga_tertinggi,
        l.status
    FROM data_sapi ds
    INNER JOIN macamSapi ms ON ds.id_macamSapi = ms.id_macamSapi
    INNER JOIN lelang l ON ds.id_sapi = l.id_sapi
    " . ($selectedKategori != 'semua' ? "WHERE ms.id_macamSapi = '$selectedKategori'" : "") . "
    ORDER BY l.createdAt DESC
");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Daftar Sapi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .filter-box {
            background-color: #e0e0e0;
            padding: 20px;
            border-radius: 8px;
        }

        .card-sapi {
            width: 18rem;
            margin: 10px;
        }

        .status {
            font-size: 0.9em;
            font-weight: bold;
        }

        .status.sedang {
            color: orange;
        }

        .status.lewat {
            color: red;
        }

        .status.aktif {
            color: green;
        }
    </style>
</head>

<body class="bg-light">

    <div class="container mt-5">
        <div class="row">

            <!-- Filter Kategori -->
            <div class="col-md-3 filter-box">
                <h5>Kategori sapi :</h5>
                <form method="GET" action="">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="kategori" id="semua" value="semua" onchange="this.form.submit()" <?= ($selectedKategori == 'semua') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="semua">Semua</label>
                    </div>

                    <?php while ($kategori = mysqli_fetch_assoc($queryKategori)) : ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="kategori" value="<?= $kategori['id_macamSapi']; ?>" id="kategori<?= $kategori['id_macamSapi']; ?>" onchange="this.form.submit()" <?= ($selectedKategori == $kategori['id_macamSapi']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="kategori<?= $kategori['id_macamSapi']; ?>">
                                <?= htmlspecialchars($kategori['name']); ?>
                            </label>
                        </div>
                    <?php endwhile; ?>
                </form>
            </div>

            <!-- Kartu Sapi -->
            <div class="col-md-9 d-flex flex-wrap">
                <?php if (mysqli_num_rows($queryDataSapi) == 0): ?>
                    <div class="alert alert-warning w-100">Tidak ada sapi untuk kategori ini.</div>
                <?php endif; ?>

                <?php while ($sapi = mysqli_fetch_assoc($queryDataSapi)) : ?>
                    <div class="card card-sapi">
                        <div class="card-header status <?= strtolower($sapi['status']); ?>">
                            <?= htmlspecialchars($sapi['status']); ?>
                        </div>
                        <img src="uploads/<?= htmlspecialchars($sapi['foto_sapi']); ?>" class="card-img-top" alt="gambar sapi" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <p class="card-text text-center"><?= htmlspecialchars($sapi['alamat_pemilik']); ?></p>
                            <p class="card-text text-center small">
                                Nilai limit: <strong>Rp<?= number_format($sapi['harga_awal']); ?></strong><br>
                                Uang jaminan: <strong>Rp<?= number_format($sapi['harga_tertinggi']); ?></strong>
                            </p>
                            <a href="detail.php?id=<?= $sapi['id_sapi']; ?>" class="btn btn-success w-100">Detail</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Tombol prosedur dan kontak -->
        <div class="row mt-5">
            <div class="col text-center">
                <a href="prosedur.php" class="btn btn-dark btn-lg w-50 mb-2">Prosedur Lelang</a><br>
                <a href="kontak.php" class="btn btn-secondary btn-lg w-50">Contact Person</a>
            </div>
        </div>
    </div>

</body>

</html>