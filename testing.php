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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Sapi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
        }

        .nav-link.active {
            color: red !important;
        }

        .btn-filter {
            margin: 5px;
        }

        .foto-sapi {
            width: 100%;
            height: 250px;
            background-color: #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-top-left-radius: 0.375rem;
            border-top-right-radius: 0.375rem;
        }

        .foto-sapi img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .detail-sapi {
            font-size: 14px;
        }

        .chat-btn-container {
            position: absolute;
            right: 20px;
            top: 20px;
            z-index: 10;
            /* Ensure the button is on top */
        }

        .generation-box {
            background-color: #555;
            color: white;
            padding: 15px;
            text-align: center;
            margin-bottom: 15px;
            border-radius: 6px;
        }

        .contact-box {
            background-color: #ccc;
            text-align: center;
            padding: 10px;
            border-radius: 6px;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light px-4">
        <a class="navbar-brand" href="#"><button class="btn btn-secondary">logo</button></a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="#">Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Peta Interaktif</a></li>
                <li class="nav-item"><a class="nav-link active" href="#">Data Sapi</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Lelang</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Login</a></li>
            </ul>
        </div>
    </nav>

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
        <div class="row">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($r = mysqli_fetch_assoc($result)): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="foto-sapi">
                                <?php if (!empty($r['foto_sapi']) && file_exists("../uploads/{$r['foto_sapi']}")): ?>
                                    <img src="../uploads/<?= $r['foto_sapi'] ?>" alt="Foto Sapi">
                                <?php else: ?>
                                    <span>Tidak ada foto</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body position-relative">
                                <div class="chat-btn-container">
                                    <?php
                                    // Clean the contact number to ensure only digits are passed to WhatsApp
                                    // Assumes contact_person column contains the phone number
                                    $whatsapp_number = preg_replace('/[^0-9]/', '', $r['contact_person']);
                                    // Optional: Add '62' prefix if the number doesn't start with it (for Indonesia)
                                    if (!empty($whatsapp_number) && substr($whatsapp_number, 0, 1) === '0') {
                                        $whatsapp_number = '62' . substr($whatsapp_number, 1);
                                    }
                                    ?>
                                    <a href="https://wa.me/<?= $whatsapp_number ?>" target="_blank" class="btn btn-secondary chat-btn">Chat Penjual</a>
                                </div>
                                <h5 class="card-title"><?= $r['jenis_sapi'] ?> – <?= $r['nama_pemilik'] ?></h5>

                                <p class="card-text detail-sapi"><strong style="color:red;">Harga Sapi:</strong> Rp <?= number_format($r['harga_sapi'], 0, ',', '.') ?></p>

                                <hr>
                                <h6>Detail Umum Sapi:</h6>
                                <ul class="list-unstyled detail-sapi">
                                    <?php
                                    // Loop through all columns of data_sapi, excluding ones already displayed or not relevant
                                    $excluded_keys = ['id_sapi', 'id_macamSapi', 'foto_sapi', 'harga_sapi', 'jenis_sapi', 'nama_pemilik', 'contact_person'];
                                    foreach ($r as $key => $val):
                                        if (!in_array($key, $excluded_keys)):
                                            // Make keys more readable
                                            $display_key = ucfirst(str_replace('_', ' ', $key));
                                    ?>
                                            <li><strong><?= $display_key ?>:</strong> <?= $val ?></li>
                                    <?php
                                        endif;
                                    endforeach;
                                    ?>
                                </ul>

                                <?php
                                // Fetch additional details only for 'sonok' sapi
                                if ($jenis_filter == 'sonok'):
                                    $q = mysqli_query($koneksi, "SELECT * FROM sapiSonok WHERE id_sapi = {$r['id_sapi']}");
                                    $s = mysqli_fetch_assoc($q);
                                    if ($s):
                                ?>
                                        <hr>
                                        <h6>Detail Sapi Sonok Khusus:</h6>
                                        <ul class="list-unstyled detail-sapi">
                                            <?php
                                            // Exclude 'id' and 'id_sapi' from general display if they're not meaningful
                                            foreach ($s as $key => $val) {
                                                if ($key !== 'id' && $key !== 'id_sapi') {
                                                    echo "<li><strong>" . ucfirst(str_replace('_', ' ', $key)) . ":</strong> " . $val . "</li>";
                                                }
                                            }
                                            ?>
                                        </ul>

                                        <?php
                                        $g1 = mysqli_fetch_assoc(mysqli_query(
                                            $koneksi,
                                            "SELECT * FROM generasiSatu WHERE sapiSonok = {$s['id']}"
                                        ));
                                        if ($g1):
                                        ?>
                                            <div class="generation-box">
                                                <h6>Generasi 1</h6>
                                                <ul class="list-unstyled detail-sapi text-start">
                                                    <?php
                                                    foreach ($g1 as $key => $val) {
                                                        if ($key !== 'id' && $key !== 'sapiSonok') {
                                                            echo "<li><strong>" . ucfirst(str_replace('_', ' ', $key)) . ":</strong> " . $val . "</li>";
                                                        }
                                                    }
                                                    ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>

                                        <?php
                                        $g2 = mysqli_fetch_assoc(mysqli_query(
                                            $koneksi,
                                            "SELECT * FROM generasiDua WHERE sapiSonok = {$s['id']}"
                                        ));
                                        if ($g2):
                                        ?>
                                            <div class="generation-box">
                                                <h6>Generasi 2</h6>
                                                <ul class="list-unstyled detail-sapi text-start">
                                                    <?php
                                                    foreach ($g2 as $key => $val) {
                                                        if ($key !== 'id' && $key !== 'sapiSonok') {
                                                            echo "<li><strong>" . ucfirst(str_replace('_', ' ', $key)) . ":</strong> " . $val . "</li>";
                                                        }
                                                    }
                                                    ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>

                                <?php
                                    endif;
                                endif;
                                ?>

                                <div class="contact-box">
                                    <strong>Contact Person:</strong> <?= $r['contact_person'] ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <p class="text-center">Tidak ada data sapi untuk jenis ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>