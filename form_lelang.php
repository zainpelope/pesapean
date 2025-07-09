<?php
include 'koneksi.php';

// Ambil semua kategori sapi dari macamSapi
$kategoriQuery = mysqli_query($koneksi, "SELECT * FROM macamSapi");

// Jika form sudah dipilih
$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
$dataSapi = [];

if ($jenis != '') {
    // Ambil ID kategori
    $getKategori = mysqli_query($koneksi, "SELECT id_macamSapi FROM macamSapi WHERE name = '$jenis'");
    $kategori = mysqli_fetch_assoc($getKategori);
    $id_macam = $kategori['id_macamSapi'];

    // Ambil data sapi dari data_sapi berdasarkan macamSapi
    $queryData = mysqli_query($koneksi, "
        SELECT * FROM data_sapi WHERE id_macamSapi = '$id_macam'
    ");

    while ($row = mysqli_fetch_assoc($queryData)) {
        $dataSapi[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Form Lelang Sapi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
                    <?php while ($row = mysqli_fetch_assoc($kategoriQuery)) : ?>
                        <option value="<?= $row['name']; ?>" <?= ($row['name'] == $jenis) ? 'selected' : ''; ?>>
                            <?= $row['name']; ?>
                        </option>
                    <?php endwhile; ?>
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
                        <?php foreach ($dataSapi as $sapi) : ?>
                            <option value="<?= $sapi['id_sapi']; ?>">
                                <?= "ID: {$sapi['id_sapi']} - Pemilik: {$sapi['nama_pemilik']} - Harga: Rp" . number_format($sapi['harga_sapi']); ?>
                            </option>
                        <?php endforeach; ?>
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
                        <option value="Sedang Berlangsung">Sedang Berlangsung</option>
                        <option value="Aktif">Aktif</option>
                        <option value="Lewat">Lewat</option>
                    </select>
                </div>

                <button type="submit" name="submit" class="btn btn-primary">Simpan Lelang</button>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>