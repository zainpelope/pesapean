<?php
// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Mulai sesi (penting untuk mengecek status login)
session_start();

include '../koneksi.php'; // Pastikan path ini benar untuk koneksi database Anda

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
    // Menggunakan prepared statements untuk mencegah SQL injection
    $stmt = mysqli_prepare($koneksi, $query . " WHERE ds.id_macamSapi = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id_m);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        // Handle error jika prepared statement gagal
        die("Error prepared statement: " . mysqli_error($koneksi));
    }
} else {
    $result = mysqli_query($koneksi, $query);
}

// Pastikan $result bukan false
if (!$result) {
    die("Error query: " . mysqli_error($koneksi));
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
        /* Variabel warna global */
        :root {
            --primary-color: rgb(240, 161, 44);
            /* Biru utama */
            --secondary-color: rgb(48, 52, 56);
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

        /* Styling Navbar */
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
            /* Primary color sedikit lebih gelap saat hover */
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

        .auth-links {
            display: flex;
            gap: 10px;
            /* Jarak antar tombol */
            align-items: center;
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

        .auth-links .btn-outline-primary {
            background-color: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }

        .auth-links .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: var(--white-color);
        }

        /* Filter Buttons (Tombol jenis sapi) */
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

        /* Penempatan tombol "Tambah Data Sapi" */
        .data-sapi-actions {
            display: flex;
            justify-content: flex-start;
            /* Menggeser tombol ke kiri */
            align-items: center;
            margin-bottom: 2.5rem;
            /* Tambah jarak bawah */
            padding-right: 15px;
            /* Sesuaikan dengan padding container */
            padding-left: 15px;
        }

        /* Styling tombol "Tambah Data Sapi" */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--white-color);
            padding: 0.75rem 1.75rem;
            /* Sedikit lebih besar */
            font-size: 1.1rem;
            /* Ukuran teks lebih besar */
            font-weight: 600;
            border-radius: 0.75rem;
            /* Sudut sedikit lebih membulat */
            box-shadow: 0 4px 10px rgba(0, 123, 255, 0.25);
            /* Bayangan lebih jelas */
            transition: all 0.3s ease;
            /* Transisi untuk hover */
            display: inline-flex;
            /* Agar ikon dan teks sejajar vertikal */
            align-items: center;
            /* Sejajarkan ikon dan teks di tengah */
            gap: 0.75rem;
            /* Jarak antara ikon dan teks */
        }

        .btn-primary:hover {
            background-color: #0056b3;
            /* Warna sedikit lebih gelap saat hover */
            border-color: #0056b3;
            transform: translateY(-2px);
            /* Efek naik sedikit saat hover */
            box-shadow: 0 6px 15px rgba(0, 123, 255, 0.35);
            /* Bayangan lebih kuat saat hover */
        }


        /* Card Styles */
        .custom-card {
            border: none;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            /* Using rgba directly for consistency */
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background-color: #ffffff;
            /* Use white directly */
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
            background-color: #f8f9fa;
            /* Use light-bg directly */
            border-bottom: 1px solid #dee2e6;
            /* Use border-color directly */
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
            background-color: #f8f9fa;
            /* Use light-bg directly */
            color: rgb(48, 52, 56);
            /* Use secondary-color directly */
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
            color: #333;
            /* Use dark-color directly */
            margin-bottom: 0.5rem;
        }

        .price-tag {
            font-size: 1.3rem;
            font-weight: 700;
            color: rgb(240, 161, 44);
            /* Use primary-color directly */
        }

        .detail-list {
            padding-left: 0;
            margin-bottom: 0;
        }

        .detail-list li {
            font-size: 0.95rem;
            line-height: 1.8;
            border-bottom: 1px dashed #dee2e6;
            /* Use border-color directly */
            padding: 0.3rem 0;
        }

        .detail-list li:last-child {
            border-bottom: none;
        }

        .detail-list strong {
            color: rgb(240, 161, 44);
            /* Use primary-color directly */
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
            background-color: #ffffff;
            /* Use white-color directly */
            border-top: 1px solid #dee2e6;
            /* Use border-color directly */
            padding: 1rem 1.5rem;
            display: flex;
            /* Make footer a flex container */
            justify-content: space-between;
            /* Space out items */
            gap: 10px;
            /* Gap between buttons */
        }

        .card-footer .btn {
            font-weight: 600;
            padding: 0.75rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: background-color 0.3s ease, border-color 0.3s ease, transform 0.2s ease;
            flex: 1;
            /* Allow buttons to grow and shrink */
        }

        .card-footer .btn-success {
            background-color: #28a745;
            /* Green for WhatsApp */
            border-color: #28a745;
            color: #fff;
        }

        .card-footer .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
            transform: translateY(-2px);
        }

        .card-footer .btn-info {
            background-color: #17a2b8;
            /* Blue for Edit */
            border-color: #17a2b8;
            color: #fff;
        }

        .card-footer .btn-info:hover {
            background-color: #138496;
            border-color: #117a8b;
            transform: translateY(-2px);
        }

        .card-footer .btn-danger {
            background-color: #dc3545;
            /* Red for Delete */
            border-color: #dc3545;
            color: #fff;
        }

        .card-footer .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
            transform: translateY(-2px);
        }

        /* Penyesuaian padding container */
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
                /* Sembunyikan link nav di layar kecil, pertimbangkan toggler */
            }

            .navbar {
                padding: 0 1rem;
            }

            .auth-links {
                margin-left: auto;
                /* Dorong tombol login ke kanan */
            }

            .btn-filter {
                display: block;
                width: calc(100% - 1rem);
                /* Lebar penuh dikurangi margin */
                margin: 0.5rem auto;
            }

            .data-sapi-actions {
                justify-content: center;
                /* Di tengah untuk mobile */
            }

            .btn-primary {
                width: 100%;
                /* Tombol tambah sapi jadi full width di mobile */
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
                flex-direction: column;
                /* Stack buttons vertically on small screens */
                padding: 0.75rem 1rem;
            }
        }
    </style>
</head>

<body>

    <header class="main-header">
        <nav class="navbar">
            <div class="logo">
                <a href="../penjual/beranda.php">Pesapean</a>
            </div>
            <ul class="nav-links">
                <li><a href="../penjual/beranda.php">Beranda</a></li>
                <li><a href="../penjual/peta.php">Peta Interaktif</a></li>
                <li><a href="../penjual/data_sapi.php?jenis=sonok">Data Sapi</a></li>
                <li><a href="../penjual/lelang.php">Lelang</a></li>
            </ul>
            <div class="auth-links">
                <?php
                // Cek apakah pengguna sudah login
                if (isset($_SESSION['id_user'])) {
                    // Pengguna sudah login, tampilkan tombol Profil
                    echo '<a href="../profile.php" class="btn btn-primary">Profile</a>';
                } else {
                    // Pengguna belum login, tampilkan tombol Login dan Daftar
                    echo '<a href="../login.php" class="btn btn-primary">Login</a>';
                    echo '<a href="../register.php" class="btn btn-outline-primary">Daftar</a>';
                }
                ?>
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

        <div class="data-sapi-actions">
            <a href="../penjual/form_tambah_sapi.php" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i> Tambah Data Sapi
            </a>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($r = mysqli_fetch_assoc($result)): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm custom-card">
                            <div class="card-img-top-container">
                                <?php if (!empty($r['foto_sapi']) && file_exists("../uploads/{$r['foto_sapi']}")): ?>
                                    <img src="../uploads/<?= htmlspecialchars($r['foto_sapi']) ?>" class="card-img-top" alt="Foto Sapi <?= htmlspecialchars($r['jenis_sapi'] ?? '') ?>">
                                <?php else: ?>
                                    <div class="no-image-placeholder">
                                        <i class="fas fa-image"></i><span>Tidak ada foto</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="card-body">
                                <h5 class="card-title text-center mb-2"><?= htmlspecialchars($r['jenis_sapi'] ?? '') ?> – <?= htmlspecialchars($r['nama_pemilik'] ?? '') ?></h5>
                                <p class="card-text text-center price-tag">
                                    <strong style="color:var(--danger-color);">Harga:</strong> Rp <?= number_format($r['harga_sapi'] ?? 0, 0, ',', '.') ?>
                                </p>
                                <hr class="my-3">
                                <h6 class="text-primary mb-2">Detail Umum Sapi:</h6>
                                <ul class="list-unstyled detail-list">
                                    <?php
                                    $excluded_keys = ['id_sapi', 'id_macamSapi', 'foto_sapi', 'harga_sapi', 'jenis_sapi', 'nama_pemilik', 'contact_person'];
                                    foreach ($r as $key => $val):
                                        if (!in_array($key, $excluded_keys)):
                                            $display_key = ucfirst(str_replace('_', ' ', $key));
                                            echo "<li><strong>" . htmlspecialchars($display_key) . ":</strong> " . htmlspecialchars($val ?? '') . "</li>";
                                        endif;
                                    endforeach;
                                    ?>
                                </ul>

                                <?php
                                // Mengambil dan menampilkan detail spesifik berdasarkan filter
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
                                        if ($jenis_filter == 'tangghek') $display_name = 'Tangeh'; // Kasus khusus untuk "Tangeh"
                                        echo '<hr><h6 class="text-primary">Detail Sapi ' . $display_name . ':</h6><ul class="list-unstyled">';
                                        foreach ($s_detail as $k => $v) {
                                            if ($k !== 'id' && $k !== 'id_sapi' && ($jenis_filter !== 'sonok' || $k !== 'sapiSonok')) {
                                                echo "<li><strong>" . htmlspecialchars(ucfirst(str_replace('_', ' ', $k))) . ":</strong> " . htmlspecialchars($v ?? '') . "</li>";
                                            }
                                        }
                                        echo '</ul>';

                                        // Untuk Sapi Sonok, tampilkan generasi
                                        if ($jenis_filter == 'sonok') {
                                            $g1_result = mysqli_query($koneksi, "SELECT * FROM generasiSatu WHERE sapiSonok = {$s_detail['id']}");
                                            $g1 = mysqli_fetch_assoc($g1_result);
                                            if ($g1) {
                                                echo '<div class="generation-box mt-3 p-3 border rounded"><h6 class="text-success">Generasi 1</h6><ul class="list-unstyled">';
                                                foreach ($g1 as $k => $v) {
                                                    if ($k !== 'id' && $k !== 'sapiSonok') {
                                                        echo "<li><strong>" . htmlspecialchars(ucfirst(str_replace('_', ' ', $k))) . ":</strong> " . htmlspecialchars($v ?? '') . "</li>";
                                                    }
                                                }
                                                echo '</ul></div>';
                                            }

                                            $g2_result = mysqli_query($koneksi, "SELECT * FROM generasiDua WHERE sapiSonok = {$s_detail['id']}");
                                            $g2 = mysqli_fetch_assoc($g2_result);
                                            if ($g2) {
                                                echo '<div class="generation-box mt-3 p-3 border rounded"><h6 class="text-info">Generasi 2</h6><ul class="list-unstyled">';
                                                foreach ($g2 as $k => $v) {
                                                    if ($k !== 'id' && $k !== 'sapiSonok') {
                                                        echo "<li><strong>" . htmlspecialchars(ucfirst(str_replace('_', ' ', $k))) . ":</strong> " . htmlspecialchars($v ?? '') . "</li>";
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
                                <a href="https://wa.me/<?= htmlspecialchars($wa) ?>" target="_blank" class="btn btn-success">
                                    <i class="fab fa-whatsapp me-2"></i> Chat Penjual
                                </a>
                                <a href="edit_sapi.php?id=<?= htmlspecialchars($r['id_sapi']) ?>&jenis=<?= htmlspecialchars($jenis_filter) ?>" class="btn btn-info">
                                    <i class="fas fa-edit me-2"></i> Edit
                                </a>
                                <a href="delete_sapi.php?id=<?= htmlspecialchars($r['id_sapi']) ?>&jenis=<?= htmlspecialchars($jenis_filter) ?>" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data sapi ini?');">
                                    <i class="fas fa-trash-alt me-2"></i> Hapus
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