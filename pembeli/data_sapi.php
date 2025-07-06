<?php
include '../koneksi.php';

$jenis_filter = isset($_GET['jenis']) ? $_GET['jenis'] : 'all';

$jenis_map = [
    'sonok' => 1,
    'kerap' => 2,
    'tangghek' => 3,
    'ternak' => 4,
    'potong' => 5,
];

$query = "SELECT ds.*, ms.name AS jenis_sapi FROM data_sapi ds
          LEFT JOIN macamSapi ms ON ds.id_macamSapi = ms.id_macamSapi";
if ($jenis_filter != 'all' && isset($jenis_map[$jenis_filter])) {
    $id_m = $jenis_map[$jenis_filter];
    $query .= " WHERE ds.id_macamSapi = $id_m";
}
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Pesapean - Data Sapi</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-pFQhV+Cq+..." crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>

    <header class="main-header">
        <nav class="navbar">
            <div class="logo">
                <a href="#">Pesapean</a>
            </div>
            <ul class="nav-links">
                <li><a href="../pembeli/beranda.php">Beranda</a></li>
                <li><a href="../pembeli/peta_interaktif.php">Peta Interaktif</a></li>
                <li><a href="../pembeli/data_sapi.php?jenis=sonok">Data Sapi</a></li>
                <li><a href="../pembeli/lelang.php">Lelang</a></li>
            </ul>
            <div class="auth-links">
                <a href="#login" class="btn btn-primary">Login</a>
            </div>
        </nav>
    </header>

    <div class="text-center mt-3">
        <?php foreach ($jenis_map as $jk => $idm): ?>
            <a href="?jenis=<?= $jk ?>" class="btn btn-secondary btn-filter <?= ($jenis_filter == $jk) ? 'active' : '' ?>">
                Sapi <?= ucfirst($jk) ?>
            </a>
        <?php endforeach; ?>
        <a href="?jenis=all" class="btn btn-secondary btn-filter <?= ($jenis_filter == 'all') ? 'active' : '' ?>">
            Semua
        </a>
    </div>

    <div class="container mt-4">

        <!-- Tombol Tambah Data Sapi -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="../pembeli/form_inputan_sapi.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Data Sapi
            </a>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($r = mysqli_fetch_assoc($result)): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm custom-card">
                            <div class="card-img-top-container">
                                <?php if (!empty($r['foto_sapi']) && file_exists("../uploads/{$r['foto_sapi']}")): ?>
                                    <img src="../uploads/<?= $r['foto_sapi'] ?>" class="card-img-top" alt="Foto Sapi">
                                <?php else: ?>
                                    <div class="no-image-placeholder">
                                        <i class="fas fa-image"></i><span>Tidak ada foto</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="card-body">
                                <h5 class="card-title text-center mb-2"><?= $r['jenis_sapi'] ?> – <?= $r['nama_pemilik'] ?></h5>
                                <p class="card-text text-center price-tag">
                                    <strong style="color:red;">Harga:</strong> Rp <?= number_format($r['harga_sapi'], 0, ',', '.') ?>
                                </p>
                                <hr class="my-3">
                                <h6 class="text-primary mb-2">Detail Umum Sapi:</h6>
                                <ul class="list-unstyled detail-list">
                                    <?php
                                    $excluded_keys = ['id_sapi', 'id_macamSapi', 'foto_sapi', 'harga_sapi', 'jenis_sapi', 'nama_pemilik', 'contact_person'];
                                    foreach ($r as $key => $val):
                                        if (!in_array($key, $excluded_keys)):
                                            $display_key = ucfirst(str_replace('_', ' ', $key));
                                            echo "<li><strong>$display_key:</strong> $val</li>";
                                        endif;
                                    endforeach;
                                    ?>
                                </ul>

                                <?php
                                if ($jenis_filter == 'sonok') {
                                    $q = mysqli_query($koneksi, "SELECT * FROM sapiSonok WHERE id_sapi = {$r['id_sapi']}");
                                    $s = mysqli_fetch_assoc($q);
                                    if ($s) {
                                        echo '<hr><h6 class="text-primary">Detail Sapi Sonok:</h6><ul class="list-unstyled">';
                                        foreach ($s as $k => $v) {
                                            if ($k !== 'id' && $k !== 'id_sapi') {
                                                echo "<li><strong>" . ucfirst(str_replace('_', ' ', $k)) . ":</strong> $v</li>";
                                            }
                                        }
                                        echo '</ul>';

                                        $g1 = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM generasiSatu WHERE sapiSonok = {$s['id']}"));
                                        if ($g1) {
                                            echo '<div class="generation-box mt-3 p-3 border rounded"><h6 class="text-success">Generasi 1</h6><ul class="list-unstyled">';
                                            foreach ($g1 as $k => $v) {
                                                if ($k !== 'id' && $k !== 'sapiSonok') {
                                                    echo "<li><strong>" . ucfirst(str_replace('_', ' ', $k)) . ":</strong> $v</li>";
                                                }
                                            }
                                            echo '</ul></div>';
                                        }

                                        $g2 = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM generasiDua WHERE sapiSonok = {$s['id']}"));
                                        if ($g2) {
                                            echo '<div class="generation-box mt-3 p-3 border rounded"><h6 class="text-info">Generasi 2</h6><ul class="list-unstyled">';
                                            foreach ($g2 as $k => $v) {
                                                if ($k !== 'id' && $k !== 'sapiSonok') {
                                                    echo "<li><strong>" . ucfirst(str_replace('_', ' ', $k)) . ":</strong> $v</li>";
                                                }
                                            }
                                            echo '</ul></div>';
                                        }
                                    }
                                }

                                if ($jenis_filter == 'kerap') {
                                    $s = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM sapiKerap WHERE id_sapi = {$r['id_sapi']}"));
                                    if ($s) {
                                        echo '<hr><h6 class="text-primary">Detail Sapi Kerap:</h6><ul class="list-unstyled">';
                                        foreach ($s as $k => $v) {
                                            if ($k !== 'id_sapi') {
                                                echo "<li><strong>" . ucfirst(str_replace('_', ' ', $k)) . ":</strong> $v</li>";
                                            }
                                        }
                                        echo '</ul>';
                                    }
                                }

                                if ($jenis_filter == 'tangghek') {
                                    $s = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM sapiTangeh WHERE id_sapi = {$r['id_sapi']}"));
                                    if ($s) {
                                        echo '<hr><h6 class="text-primary">Detail Sapi Tangeh:</h6><ul class="list-unstyled">';
                                        foreach ($s as $k => $v) {
                                            if ($k !== 'id' && $k !== 'id_sapi') {
                                                echo "<li><strong>" . ucfirst(str_replace('_', ' ', $k)) . ":</strong> $v</li>";
                                            }
                                        }
                                        echo '</ul>';
                                    }
                                }

                                if ($jenis_filter == 'ternak') {
                                    $s = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM sapiTermak WHERE id_sapi = {$r['id_sapi']}"));
                                    if ($s) {
                                        echo '<hr><h6 class="text-primary">Detail Sapi Termak:</h6><ul class="list-unstyled">';
                                        foreach ($s as $k => $v) {
                                            if ($k !== 'id_sapi') {
                                                echo "<li><strong>" . ucfirst(str_replace('_', ' ', $k)) . ":</strong> $v</li>";
                                            }
                                        }
                                        echo '</ul>';
                                    }
                                }

                                if ($jenis_filter == 'potong') {
                                    $s = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM sapiPotong WHERE id_sapi = {$r['id_sapi']}"));
                                    if ($s) {
                                        echo '<hr><h6 class="text-primary">Detail Sapi Potong:</h6><ul class="list-unstyled">';
                                        foreach ($s as $k => $v) {
                                            if ($k !== 'id' && $k !== 'id_sapi') {
                                                echo "<li><strong>" . ucfirst(str_replace('_', ' ', $k)) . ":</strong> $v</li>";
                                            }
                                        }
                                        echo '</ul>';
                                    }
                                }
                                ?>
                            </div>

                            <div class="card-footer text-center">
                                <?php
                                $wa = preg_replace('/[^0-9]/', '', $r['contact_person'] ?? '');
                                if (!empty($wa) && substr($wa, 0, 1) === '0') {
                                    $wa = '62' . substr($wa, 1);
                                }
                                ?>
                                <a href="https://wa.me/<?= $wa ?>" target="_blank" class="btn btn-success w-100 d-flex align-items-center justify-content-center">
                                    <i class="fab fa-whatsapp me-2"></i> Chat Penjual
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center" role="alert">
                        <i class="fas fa-info-circle me-2"></i> Tidak ada data sapi untuk jenis ini.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include '../footer.php'; ?>
</body>

</html>