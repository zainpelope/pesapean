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
    // Using prepared statements to prevent SQL injection
    $stmt = mysqli_prepare($koneksi, $query . " WHERE ds.id_macamSapi = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_m);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($koneksi, $query);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesapean - Data Sapi</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-pFQhV+Cq+BfS2Z2v2E2L2R2/2N2P2g2B2D2G2H2I2J2K2L2M2N2O2P2Q2R2S2T2U2V2W2X2Y2Z2==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        :root {


            --success-color: #28a745;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --white-color: #ffffff;
            --border-color: #e9ecef;
            --shadow-color: rgba(0, 0, 0, 0.08);
        }




        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .navbar .logo a {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .navbar .logo a:hover {
            color: #0056b3;
            /* Darker primary for hover */
        }

        .nav-links {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            gap: 1.5rem;
        }

        .nav-links li a {
            text-decoration: none;
            color: var(--dark-color);
            font-weight: 600;
            padding: 0.5rem 0;
            transition: color 0.3s ease, border-bottom 0.3s ease;
        }



        .auth-links .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .auth-links .btn-primary {
            background-color: var(--primary-color);
            color: var(--white-color);
            border: none;
        }

        .auth-links .btn-primary:hover {
            background-color: #0056b3;
        }

        /* Filter Buttons */
        .btn-filter {
            margin: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            border: 1px solid var(--secondary-color);
            color: var(--secondary-color);
            background-color: var(--white-color);
        }

        .btn-filter:hover {
            background-color: var(--secondary-color);
            color: var(--white-color);
        }

        .btn-filter.active {
            background-color: var(--primary-color);
            color: var(--white-color);
            border-color: var(--primary-color);
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.2);
        }

        /* Card Styles */
        .custom-card {
            border: none;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 8px 20px var(--shadow-color);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background-color: var(--white-color);
            display: flex;
            flex-direction: column;
        }

        .custom-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
        }

        .card-img-top-container {
            width: 100%;
            padding-top: 75%;
            /* 4:3 Aspect Ratio */
            position: relative;
            background-color: var(--light-color);
            border-bottom: 1px solid var(--border-color);
            overflow: hidden;
        }

        .card-img-top {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .no-image-placeholder {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: var(--light-color);
            color: var(--secondary-color);
            font-size: 1.5rem;
            text-align: center;
        }

        .no-image-placeholder .fas {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }

        .card-body {
            padding: 1.5rem;
            flex-grow: 1;
        }

        .card-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .price-tag {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .detail-list {
            padding-left: 0;
            margin-bottom: 0;
        }

        .detail-list li {
            font-size: 0.95rem;
            line-height: 1.8;
            border-bottom: 1px dashed var(--border-color);
            padding: 0.3rem 0;
        }

        .detail-list li:last-child {
            border-bottom: none;
        }

        .detail-list strong {
            color: var(--primary-color);
        }

        .generation-box {
            background-color: #e9f7fe;
            /* Light blue for generation details */
            border-color: #b3e0ff !important;
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 0.75rem;
        }

        .generation-box h6 {
            font-weight: 700;
            margin-bottom: 0.75rem;
        }

        .generation-box ul {
            list-style: none;
            padding-left: 0;
            margin-bottom: 0;
        }

        .generation-box ul li {
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .card-footer {
            background-color: var(--white-color);
            border-top: 1px solid var(--border-color);
            padding: 1rem 1.5rem;
        }

        .card-footer .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
            font-weight: 600;
            padding: 0.75rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: background-color 0.3s ease, border-color 0.3s ease, transform 0.2s ease;
        }

        .card-footer .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
            transform: translateY(-2px);
        }

        .text-center.mt-3 {
            padding-top: 1.5rem;
            padding-bottom: 1rem;
        }

        .container.mt-4 {
            padding-top: 1rem;
            padding-bottom: 3rem;
        }

        /* Responsive Adjustments */
        @media (max-width: 991.98px) {
            .nav-links {
                display: none;
                /* Hide nav links on smaller screens for now, consider a toggler */
            }

            .navbar {
                padding: 0 1rem;
            }

            .auth-links {
                margin-left: auto;
                /* Push login button to the right */
            }

            .btn-filter {
                display: block;
                width: calc(100% - 1rem);
                /* Full width minus margin */
                margin: 0.5rem auto;
            }
        }

        @media (max-width: 767.98px) {
            .card-title {
                font-size: 1.2rem;
            }

            .price-tag {
                font-size: 1.1rem;
            }

            .detail-list li,
            .generation-box ul li {
                font-size: 0.85rem;
            }

            .card-body {
                padding: 1rem;
            }

            .card-footer {
                padding: 0.75rem 1rem;
            }
        }
    </style>
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
            <a href="?jenis=<?= $jk ?>" class="btn btn-filter <?= ($jenis_filter == $jk) ? 'active' : '' ?>">
                Sapi <?= ucfirst($jk) ?>
            </a>
        <?php endforeach; ?>
        <a href="?jenis=all" class="btn btn-filter <?= ($jenis_filter == 'all') ? 'active' : '' ?>">
            Semua
        </a>
    </div>

    <div class="container mt-4">

        <div class="d-flex justify-content-end align-items-center mb-4">
            <a href="../sapi.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Tambah Data Sapi
            </a>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($r = mysqli_fetch_assoc($result)): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm custom-card">
                            <div class="card-img-top-container">
                                <?php if (!empty($r['foto_sapi']) && file_exists("../uploads/{$r['foto_sapi']}")): ?>
                                    <img src="../uploads/<?= htmlspecialchars($r['foto_sapi']) ?>" class="card-img-top" alt="Foto Sapi <?= htmlspecialchars($r['jenis_sapi']) ?>">
                                <?php else: ?>
                                    <div class="no-image-placeholder">
                                        <i class="fas fa-image"></i><span>Tidak ada foto</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="card-body">
                                <h5 class="card-title text-center mb-2"><?= htmlspecialchars($r['jenis_sapi']) ?> – <?= htmlspecialchars($r['nama_pemilik']) ?></h5>
                                <p class="card-text text-center price-tag">
                                    <strong style="color:var(--danger-color);">Harga:</strong> Rp <?= number_format($r['harga_sapi'], 0, ',', '.') ?>
                                </p>
                                <hr class="my-3">
                                <h6 class="text-primary mb-2">Detail Umum Sapi:</h6>
                                <ul class="list-unstyled detail-list">
                                    <?php
                                    $excluded_keys = ['id_sapi', 'id_macamSapi', 'foto_sapi', 'harga_sapi', 'jenis_sapi', 'nama_pemilik', 'contact_person'];
                                    foreach ($r as $key => $val):
                                        if (!in_array($key, $excluded_keys)):
                                            $display_key = ucfirst(str_replace('_', ' ', $key));
                                            echo "<li><strong>" . htmlspecialchars($display_key) . ":</strong> " . htmlspecialchars($val) . "</li>";
                                        endif;
                                    endforeach;
                                    ?>
                                </ul>

                                <?php
                                // Fetch and display specific details based on filter
                                $detail_tables = [
                                    'sonok' => 'sapiSonok',
                                    'kerap' => 'sapiKerap',
                                    'tangghek' => 'sapiTangghek',
                                    'ternak' => 'sapiTernak',
                                    'potong' => 'sapiPotong'
                                ];

                                if (isset($detail_tables[$jenis_filter])) {
                                    $table_name = $detail_tables[$jenis_filter];
                                    $q_detail = mysqli_query($koneksi, "SELECT * FROM $table_name WHERE id_sapi = {$r['id_sapi']}");
                                    $s_detail = mysqli_fetch_assoc($q_detail);

                                    if ($s_detail) {
                                        $display_name = ucfirst($jenis_filter);
                                        if ($jenis_filter == 'tangghek') $display_name = 'Tangeh'; // Special case for "Tangeh"
                                        echo '<hr><h6 class="text-primary">Detail Sapi ' . $display_name . ':</h6><ul class="list-unstyled">';
                                        foreach ($s_detail as $k => $v) {
                                            if ($k !== 'id' && $k !== 'id_sapi' && $k !== 'sapiSonok') { // 'sapiSonok' is only relevant for generation tables
                                                echo "<li><strong>" . htmlspecialchars(ucfirst(str_replace('_', ' ', $k))) . ":</strong> " . htmlspecialchars($v) . "</li>";
                                            }
                                        }
                                        echo '</ul>';

                                        // For Sapi Sonok, display generations
                                        if ($jenis_filter == 'sonok') {
                                            $g1 = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM generasiSatu WHERE sapiSonok = {$s_detail['id']}"));
                                            if ($g1) {
                                                echo '<div class="generation-box mt-3 p-3 border rounded"><h6 class="text-success">Generasi 1</h6><ul class="list-unstyled">';
                                                foreach ($g1 as $k => $v) {
                                                    if ($k !== 'id' && $k !== 'sapiSonok') {
                                                        echo "<li><strong>" . htmlspecialchars(ucfirst(str_replace('_', ' ', $k))) . ":</strong> " . htmlspecialchars($v) . "</li>";
                                                    }
                                                }
                                                echo '</ul></div>';
                                            }

                                            $g2 = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM generasiDua WHERE sapiSonok = {$s_detail['id']}"));
                                            if ($g2) {
                                                echo '<div class="generation-box mt-3 p-3 border rounded"><h6 class="text-info">Generasi 2</h6><ul class="list-unstyled">';
                                                foreach ($g2 as $k => $v) {
                                                    if ($k !== 'id' && $k !== 'sapiSonok') {
                                                        echo "<li><strong>" . htmlspecialchars(ucfirst(str_replace('_', ' ', $k))) . ":</strong> " . htmlspecialchars($v) . "</li>";
                                                    }
                                                }
                                                echo '</ul></div>';
                                            }
                                        }
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
                                <a href="https://wa.me/<?= htmlspecialchars($wa) ?>" target="_blank" class="btn btn-success w-100">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <?php include '../footer.php'; ?>
</body>

</html>