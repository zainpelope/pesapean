<?php
include '../koneksi.php';

$jenis_filter = isset($_GET['jenis']) ? $_GET['jenis'] : 'all'; // Default ke 'all' jika tidak ada filter

// Query untuk mengambil data berdasarkan filter
$query_string = "SELECT ds.*, ms.name as jenis_sapi, ds.foto_sapi FROM data_sapi ds
                 LEFT JOIN macamSapi ms ON ds.id_macamSapi = ms.id_macamSapi";

if ($jenis_filter != 'all') {
    // Memetakan jenis yang bisa dibaca manusia ke ID macamSapi (pastikan ini sesuai dengan tabel macamSapi Anda)
    $id_macamSapi_map = [
        'sonok' => 1,
        'kerap' => 2,
        'tangghek' => 3,
        'ternak' => 4,
        'potong' => 5,
    ];
    $filter_id_macamSapi = isset($id_macamSapi_map[$jenis_filter]) ? $id_macamSapi_map[$jenis_filter] : 0;

    if ($filter_id_macamSapi > 0) {
        $query_string .= " WHERE ds.id_macamSapi = $filter_id_macamSapi";
    } else {
        // Jika jenis tidak dikenal, defaultkan untuk tidak menampilkan hasil
        $query_string .= " WHERE 1 = 0"; // Kondisi ini akan selalu salah
    }
}

$filtered_sapi_query = mysqli_query($koneksi, $query_string);

/**
 * Fungsi untuk mengambil detail spesifik sapi.
 *
 * @param mysqli $koneksi Objek koneksi database.
 * @param int $id_sapi ID sapi.
 * @param string $jenis Jenis sapi (sonok, kerap, dll.).
 * @return array Array berisi detail sapi, generasi satu, dan generasi dua.
 */
function getCowDetails($koneksi, $id_sapi, $jenis)
{
    $detail = null;
    $gen1 = null;
    $gen2 = null;

    if ($jenis == 'sonok') {
        $q = mysqli_query($koneksi, "SELECT * FROM sapiSonok WHERE id = $id_sapi");
        $detail = mysqli_fetch_assoc($q);
        if ($detail) {
            if (!empty($detail['generasiSatu'])) {
                $q1 = mysqli_query($koneksi, "SELECT * FROM generasiSatu WHERE id = {$detail['generasiSatu']}");
                $gen1 = mysqli_fetch_assoc($q1);
            }
            if (!empty($detail['generasiDua'])) {
                $q2 = mysqli_query($koneksi, "SELECT * FROM generasiDua WHERE id = {$detail['generasiDua']}");
                $gen2 = mysqli_fetch_assoc($q2);
            }
        }
    } elseif ($jenis == 'kerap') {
        $q = mysqli_query($koneksi, "SELECT * FROM sapiKerap WHERE id_sapi = $id_sapi");
        $detail = mysqli_fetch_assoc($q);
    } elseif ($jenis == 'tangghek') {
        $q = mysqli_query($koneksi, "SELECT * FROM sapiTangghek WHERE id = $id_sapi");
        $detail = mysqli_fetch_assoc($q);
    } elseif ($jenis == 'ternak') {
        $q = mysqli_query($koneksi, "SELECT * FROM sapiTernak WHERE id_sapi = $id_sapi");
        $detail = mysqli_fetch_assoc($q);
    } elseif ($jenis == 'potong') {
        $q = mysqli_query($koneksi, "SELECT * FROM sapiPotong WHERE id = $id_sapi");
        $detail = mysqli_fetch_assoc($q);
    }
    return ['detail' => $detail, 'gen1' => $gen1, 'gen2' => $gen2];
}

/**
 * Fungsi untuk mendapatkan semua jalur gambar untuk ID sapi tertentu.
 * Mengasumsikan konvensi penamaan gambar:
 * - Gambar utama: [nama_file_dari_kolom_foto_sapi].{ext} (misal: 1.jpg, sapi_merah.png)
 * - Gambar tambahan: [id_sapi]_[nomor_urutan].{ext} (misal: 1_2.jpg, 1_3.png)
 *
 * @param int $id_sapi ID sapi.
 * @param string $main_photo_filename Nama file gambar utama dari database (misal: "sapi_merah.jpg").
 * @return array Array jalur gambar relatif terhadap root web.
 */
function getCowImages($id_sapi, $main_photo_filename)
{
    $images = [];
    $uploads_dir = '../uploads/'; // Direktori tempat gambar disimpan

    // 1. Tambahkan gambar utama dari database terlebih dahulu
    $main_photo_path = $uploads_dir . basename($main_photo_filename); // Gunakan basename untuk memastikan hanya nama file
    if (!empty($main_photo_filename) && file_exists($main_photo_path)) {
        $images[] = $main_photo_path;
    }

    // 2. Cari gambar tambahan menggunakan pola seperti [id_sapi]_[nomor].{ext}
    $additional_image_pattern = $uploads_dir . $id_sapi . '_*.{jpg,jpeg,png,gif,JPG,JPEG,PNG,GIF}'; // Menambahkan lebih banyak ekstensi dan case-insensitivity
    $additional_images = glob($additional_image_pattern, GLOB_BRACE);

    // 3. Gabungkan dan pastikan jalur unik (jika gambar utama juga cocok dengan pola)
    foreach ($additional_images as $img_path) {
        if (!in_array($img_path, $images)) {
            $images[] = $img_path;
        }
    }
    // Urutkan untuk memastikan urutan yang konsisten, misal: 1_1.jpg, 1_2.jpg
    sort($images);
    return $images;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Data Sapi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        .btn-group {
            margin-bottom: 30px;
        }

        .cow-image-wrapper {
            /* Container untuk gambar atau carousel */
            width: 100%;
            max-height: 300px;
            /* Batasi tinggi */
            overflow: hidden;
            /* Sembunyikan overflow dari max-height */
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            /* Untuk menengahkan gambar tunggal */
            align-items: center;
            justify-content: center;
            background-color: #f0f0f0;
            /* Latar belakang placeholder */
        }

        .cow-image-wrapper img,
        .carousel-inner img {
            width: 100%;
            height: auto;
            /* Pertahankan rasio aspek */
            object-fit: cover;
            /* Tutupi wadah sambil mempertahankan rasio aspek */
            max-height: 300px;
            /* Pastikan gambar tidak melebihi tinggi wadah */
        }

        .label {
            font-weight: bold;
            color: darkred;
        }

        .box {
            background-color: #f8f9fa;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            border: 1px solid #e9ecef;
        }

        .cow-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        /* Penyesuaian untuk carousel bersarang di tampilan utama "semua sapi" */
        #allCowsMainCarousel .carousel-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-bottom: 20px;
            /* Ruang untuk caption di bawah gambar */
        }

        #allCowsMainCarousel .carousel-caption {
            position: static;
            /* Timpa default Bootstrap untuk carousel dalam */
            background-color: rgba(255, 255, 255, 0.95);
            color: black;
            padding: 20px;
            border-radius: 8px;
            margin-top: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 90%;
            /* Sesuaikan lebar untuk tampilan yang lebih baik */
            max-width: 700px;
            /* Lebar maksimal untuk keterbacaan */
        }
    </style>
</head>

<body class="container mt-5">

    <h3 class="mb-4">Daftar Sapi</h3>

    <div class="btn-group" role="group">
        <a href="?jenis=sonok" class="btn <?= ($jenis_filter == 'sonok') ? 'btn-primary' : 'btn-secondary' ?>">Sapi Sonok</a>
        <a href="?jenis=kerap" class="btn <?= ($jenis_filter == 'kerap') ? 'btn-primary' : 'btn-secondary' ?>">Sapi Kerap</a>
        <a href="?jenis=tangghek" class="btn <?= ($jenis_filter == 'tangghek') ? 'btn-primary' : 'btn-secondary' ?>">Sapi Tangghek</a>
        <a href="?jenis=ternak" class="btn <?= ($jenis_filter == 'ternak') ? 'btn-primary' : 'btn-secondary' ?>">Sapi Ternak</a>
        <a href="?jenis=potong" class="btn <?= ($jenis_filter == 'potong') ? 'btn-primary' : 'btn-secondary' ?>">Sapi Potong</a>
    </div>

    <?php if ($jenis_filter == 'all'): ?>
        <div id="allCowsMainCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php
                $active_main_carousel_item = true;
                // Mundurkan penunjuk hasil jika sudah digunakan atau jika ini pertama kali
                if (mysqli_num_rows($filtered_sapi_query) > 0) {
                    mysqli_data_seek($filtered_sapi_query, 0);
                }
                while ($sapi_carousel = mysqli_fetch_assoc($filtered_sapi_query)):
                    $cow_data = getCowDetails($koneksi, $sapi_carousel['id_sapi'], strtolower(str_replace(' ', '', $sapi_carousel['jenis_sapi'])));
                    $current_detail_carousel = $cow_data['detail'];
                    $current_gen1_carousel = $cow_data['gen1'];
                    $current_gen2_carousel = $cow_data['gen2'];
                    // Panggil fungsi baru untuk mendapatkan semua gambar sapi
                    $cow_images = getCowImages($sapi_carousel['id_sapi'], $sapi_carousel['foto_sapi']);
                ?>
                    <div class="carousel-item <?= $active_main_carousel_item ? 'active' : '' ?>">
                        <div class="cow-image-wrapper">
                            <?php if (count($cow_images) > 1): // Jika ada lebih dari satu gambar, buat carousel 
                            ?>
                                <div id="cowImagesCarousel_<?= $sapi_carousel['id_sapi'] ?>" class="carousel slide" data-bs-ride="carousel">
                                    <div class="carousel-inner">
                                        <?php foreach ($cow_images as $index => $image_path): ?>
                                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                                <img src="<?= $image_path ?>" class="d-block w-100" alt="Foto Sapi <?= $sapi_carousel['id_sapi'] ?> - <?= $index + 1 ?>">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#cowImagesCarousel_<?= $sapi_carousel['id_sapi'] ?>" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#cowImagesCarousel_<?= $sapi_carousel['id_sapi'] ?>" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Next</span>
                                    </button>
                                </div>
                            <?php elseif (count($cow_images) === 1): // Jika hanya satu gambar, tampilkan img tunggal 
                            ?>
                                <img src="<?= $cow_images[0] ?>" class="img-fluid" alt="Foto Sapi <?= $sapi_carousel['id_sapi'] ?>">
                            <?php else: // Jika tidak ada gambar, tampilkan placeholder 
                            ?>
                                <img src="../uploads/default.jpg" class="img-fluid" alt="Foto Sapi Default">
                            <?php endif; ?>
                        </div>

                        <div class="carousel-caption">
                            <h5><?= $sapi_carousel['jenis_sapi'] ?> - <?= $sapi_carousel['nama_pemilik'] ?></h5>
                            <p>Harga: Rp <?= number_format($sapi_carousel['harga_sapi'], 0, ',', '.') ?></p>

                            <?php if ($current_detail_carousel): ?>
                                <hr>
                                <h6 class="label">Detail Sapi (<?= $sapi_carousel['jenis_sapi'] ?>)</h6>
                                <?php if (strtolower(str_replace(' ', '', $sapi_carousel['jenis_sapi'])) == 'sonok'): ?>
                                    <p>Nama: <?= $current_detail_carousel['nama_sapi'] ?></p>
                                    <p>Umur: <?= $current_detail_carousel['umur'] ?></p>
                                    <p>Lingkar Dada: <?= $current_detail_carousel['lingkar_dada'] ?></p>
                                    <p>Panjang Badan: <?= $current_detail_carousel['panjang_badan'] ?></p>
                                    <p>Tinggi Badan: <?= $current_detail_carousel['tinggi_badan'] ?></p>
                                    <p>Tinggi Pundak: <?= $current_detail_carousel['tinggi_pundak'] ?></p>
                                    <p>Lebar Dahi: <?= $current_detail_carousel['lebar_dahi'] ?></p>
                                    <p>Panjang Wajah: <?= $current_detail_carousel['panjang_wajah'] ?></p>
                                    <p>Lebar Pinggul: <?= $current_detail_carousel['lebar_pinggul'] ?></p>
                                    <p>Lebar Dada: <?= $current_detail_carousel['lebar_dada'] ?></p>
                                    <p>Tinggi Kaki: <?= $current_detail_carousel['tinggi_kaki'] ?></p>
                                    <p>Warna Bulu: <?= $current_detail_carousel['warna_bulu'] ?></p>

                                    <?php if ($current_gen1_carousel): ?>
                                        <div class="box">
                                            <h6>Generasi 1</h6>
                                            <p>Pejantan: <?= $current_gen1_carousel['namaPejantanGenerasiSatu'] ?> (<?= $current_gen1_carousel['jenisPejantanGenerasiSatu'] ?>)</p>
                                            <p>Induk: <?= $current_gen1_carousel['namaIndukGenerasiSatu'] ?> (<?= $current_gen1_carousel['jenisIndukGenerasiSatu'] ?>)</p>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($current_gen2_carousel): ?>
                                        <div class="box">
                                            <h6>Generasi 2</h6>
                                            <p>Pejantan: <?= $current_gen2_carousel['namaPejantanGenerasiDua'] ?> (<?= $current_gen2_carousel['jenisPejantanGenerasiDua'] ?>)</p>
                                            <p>Induk: <?= $current_gen2_carousel['namaIndukGenerasiDua'] ?> (<?= $current_gen2_carousel['jenisIndukGenerasiDua'] ?>)</p>
                                            <p>Kakek Pejantan: <?= $current_gen2_carousel['namaKakekPejantanGenerasiDua'] ?></p>
                                            <p>Nenek Pejantan: <?= $current_gen2_carousel['namaNenekPejantanGenerasiDua'] ?></p>
                                            <p>Kakek Induk: <?= $current_gen2_carousel['namaKakekIndukGenerasiDua'] ?></p>
                                            <p>Nenek Induk: <?= $current_gen2_carousel['namaNenekIndukGenerasiDua'] ?></p>
                                        </div>
                                    <?php endif; ?>

                                <?php elseif (strtolower(str_replace(' ', '', $sapi_carousel['jenis_sapi'])) == 'kerap'): ?>
                                    <p>Ketahanan Fisik: <?= $current_detail_carousel['ketahanan_fisik'] ?></p>
                                    <p>Kecepatan Lari: <?= $current_detail_carousel['kecepatan_lari'] ?></p>
                                    <p>Penghargaan: <?= $current_detail_carousel['penghargaan'] ?></p>

                                <?php elseif (strtolower(str_replace(' ', '', $sapi_carousel['jenis_sapi'])) == 'tangghek'): ?>
                                    <p>Tinggi Badan: <?= $current_detail_carousel['tinggi_badan'] ?> cm</p>
                                    <p>Panjang Badan: <?= $current_detail_carousel['panjang_badan'] ?> cm</p>
                                    <p>Lingkar Dada: <?= $current_detail_carousel['lingkar_dada'] ?> cm</p>
                                    <p>Bobot Badan: <?= $current_detail_carousel['bobot_badan'] ?> kg</p>
                                    <p>Frekuensi Latihan: <?= $current_detail_carousel['frekuensi_latihan'] ?></p>
                                    <p>Jarak Latihan: <?= $current_detail_carousel['jarak_latihan'] ?></p>
                                    <p>Prestasi: <?= $current_detail_carousel['prestasi'] ?></p>
                                    <p>Kesehatan: <?= $current_detail_carousel['kesehatan'] ?></p>

                                <?php elseif (strtolower(str_replace(' ', '', $sapi_carousel['jenis_sapi'])) == 'ternak'): ?>
                                    <p>Kesuburan: <?= $current_detail_carousel['kesuburan'] ?></p>
                                    <p>Riwayat Kesehatan: <?= $current_detail_carousel['riwayat_kesehatan'] ?></p>

                                <?php elseif (strtolower(str_replace(' ', '', $sapi_carousel['jenis_sapi'])) == 'potong'): ?>
                                    <p>Nama: <?= $current_detail_carousel['nama_sapi'] ?></p>
                                    <p>Berat Badan: <?= $current_detail_carousel['berat_badan'] ?> kg</p>
                                    <p>Persentase Daging: <?= $current_detail_carousel['persentase_daging'] ?>%</p>

                                <?php endif; ?>

                                <hr>
                                <h6 class="label">Contact Person</h6>
                                <p>Nama: <?= $sapi_carousel['nama_pemilik'] ?></p>
                                <p>Alamat: <?= $sapi_carousel['alamat_pemilik'] ?></p>
                                <p>Nomor HP: <?= $sapi_carousel['nomor_pemilik'] ?></p>
                                <p>Email: <?= $sapi_carousel['email_pemilik'] ?></p>

                            <?php else: ?>
                                <p>Data detail untuk jenis sapi ini belum tersedia.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php
                    $active_main_carousel_item = false;
                endwhile;
                ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#allCowsMainCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#allCowsMainCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    <?php else: // Tampilkan sapi dalam tampilan daftar ketika filter spesifik aktif 
    ?>
        <div class="row">
            <?php
            if (mysqli_num_rows($filtered_sapi_query) > 0) {
                while ($sapi_item = mysqli_fetch_assoc($filtered_sapi_query)):
                    $cow_data = getCowDetails($koneksi, $sapi_item['id_sapi'], strtolower(str_replace(' ', '', $sapi_item['jenis_sapi'])));
                    $item_detail = $cow_data['detail'];
                    $item_gen1 = $cow_data['gen1'];
                    $item_gen2 = $cow_data['gen2'];
                    // Panggil fungsi baru untuk mendapatkan semua gambar sapi
                    $cow_images = getCowImages($sapi_item['id_sapi'], $sapi_item['foto_sapi']);
            ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="cow-card">
                            <div class="cow-image-wrapper">
                                <?php if (count($cow_images) > 1): // Jika ada lebih dari satu gambar, buat carousel 
                                ?>
                                    <div id="cowImagesCarousel_<?= $sapi_item['id_sapi'] ?>" class="carousel slide" data-bs-ride="carousel">
                                        <div class="carousel-inner">
                                            <?php foreach ($cow_images as $index => $image_path): ?>
                                                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                                    <img src="<?= $image_path ?>" class="d-block w-100" alt="Foto Sapi <?= $sapi_item['id_sapi'] ?> - <?= $index + 1 ?>">
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <button class="carousel-control-prev" type="button" data-bs-target="#cowImagesCarousel_<?= $sapi_item['id_sapi'] ?>" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#cowImagesCarousel_<?= $sapi_item['id_sapi'] ?>" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    </div>
                                <?php elseif (count($cow_images) === 1): // Jika hanya satu gambar, tampilkan img tunggal 
                                ?>
                                    <img src="<?= $cow_images[0] ?>" class="img-fluid" alt="Foto Sapi <?= $sapi_item['id_sapi'] ?>">
                                <?php else: // Jika tidak ada gambar, tampilkan placeholder 
                                ?>
                                    <img src="../uploads/default.jpg" class="img-fluid" alt="Foto Sapi Default">
                                <?php endif; ?>
                            </div>

                            <h5><?= $sapi_item['jenis_sapi'] ?> - <?= $sapi_item['nama_pemilik'] ?></h5>
                            <p>Harga: Rp <?= number_format($sapi_item['harga_sapi'], 0, ',', '.') ?></p>

                            <?php if ($item_detail): ?>
                                <hr>
                                <h6 class="label">Detail Sapi (<?= $sapi_item['jenis_sapi'] ?>)</h6>
                                <?php if (strtolower(str_replace(' ', '', $sapi_item['jenis_sapi'])) == 'sonok'): ?>
                                    <p>Nama: <?= $item_detail['nama_sapi'] ?></p>
                                    <p>Umur: <?= $item_detail['umur'] ?></p>
                                    <p>Lingkar Dada: <?= $item_detail['lingkar_dada'] ?></p>
                                    <p>Panjang Badan: <?= $item_detail['panjang_badan'] ?></p>
                                    <p>Tinggi Badan: <?= $item_detail['tinggi_badan'] ?></p>
                                    <p>Tinggi Pundak: <?= $item_detail['tinggi_pundak'] ?></p>
                                    <p>Lebar Dahi: <?= $item_detail['lebar_dahi'] ?></p>
                                    <p>Panjang Wajah: <?= $item_detail['panjang_wajah'] ?></p>
                                    <p>Lebar Pinggul: <?= $item_detail['lebar_pinggul'] ?></p>
                                    <p>Lebar Dada: <?= $item_detail['lebar_dada'] ?></p>
                                    <p>Tinggi Kaki: <?= $item_detail['tinggi_kaki'] ?></p>
                                    <p>Warna Bulu: <?= $item_detail['warna_bulu'] ?></p>

                                    <?php if ($item_gen1): ?>
                                        <div class="box">
                                            <h6>Generasi 1</h6>
                                            <p>Pejantan: <?= $item_gen1['namaPejantanGenerasiSatu'] ?> (<?= $item_gen1['jenisPejantanGenerasiSatu'] ?>)</p>
                                            <p>Induk: <?= $item_gen1['namaIndukGenerasiSatu'] ?> (<?= $item_gen1['jenisIndukGenerasiSatu'] ?>)</p>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($item_gen2): ?>
                                        <div class="box">
                                            <h6>Generasi 2</h6>
                                            <p>Pejantan: <?= $item_gen2['namaPejantanGenerasiDua'] ?> (<?= $item_gen2['jenisPejantanGenerasiDua'] ?>)</p>
                                            <p>Induk: <?= $item_gen2['namaIndukGenerasiDua'] ?> (<?= $item_gen2['jenisIndukGenerasiDua'] ?>)</p>
                                            <p>Kakek Pejantan: <?= $item_gen2['namaKakekPejantanGenerasiDua'] ?></p>
                                            <p>Nenek Pejantan: <?= $item_gen2['namaNenekPejantanGenerasiDua'] ?></p>
                                            <p>Kakek Induk: <?= $item_gen2['namaKakekIndukGenerasiDua'] ?></p>
                                            <p>Nenek Induk: <?= $item_gen2['namaNenekIndukGenerasiDua'] ?></p>
                                        </div>
                                    <?php endif; ?>

                                <?php elseif (strtolower(str_replace(' ', '', $sapi_item['jenis_sapi'])) == 'kerap'): ?>
                                    <p>Ketahanan Fisik: <?= $item_detail['ketahanan_fisik'] ?></p>
                                    <p>Kecepatan Lari: <?= $item_detail['kecepatan_lari'] ?></p>
                                    <p>Penghargaan: <?= $item_detail['penghargaan'] ?></p>

                                <?php elseif (strtolower(str_replace(' ', '', $sapi_item['jenis_sapi'])) == 'tangghek'): ?>
                                    <p>Tinggi Badan: <?= $item_detail['tinggi_badan'] ?> cm</p>
                                    <p>Panjang Badan: <?= $item_detail['panjang_badan'] ?> cm</p>
                                    <p>Lingkar Dada: <?= $item_detail['lingkar_dada'] ?> cm</p>
                                    <p>Bobot Badan: <?= $item_detail['bobot_badan'] ?> kg</p>
                                    <p>Frekuensi Latihan: <?= $item_detail['frekuensi_latihan'] ?></p>
                                    <p>Jarak Latihan: <?= $item_detail['jarak_latihan'] ?></p>
                                    <p>Prestasi: <?= $item_detail['prestasi'] ?></p>
                                    <p>Kesehatan: <?= $item_detail['kesehatan'] ?></p>

                                <?php elseif (strtolower(str_replace(' ', '', $sapi_item['jenis_sapi'])) == 'ternak'): ?>
                                    <p>Kesuburan: <?= $item_detail['kesuburan'] ?></p>
                                    <p>Riwayat Kesehatan: <?= $item_detail['riwayat_kesehatan'] ?></p>

                                <?php elseif (strtolower(str_replace(' ', '', $sapi_item['jenis_sapi'])) == 'potong'): ?>
                                    <p>Nama: <?= $item_detail['nama_sapi'] ?></p>
                                    <p>Berat Badan: <?= $item_detail['berat_badan'] ?> kg</p>
                                    <p>Persentase Daging: <?= $item_detail['persentase_daging'] ?>%</p>

                                <?php endif; ?>
                                <hr>
                                <h6 class="label">Contact Person</h6>
                                <p>Nama: <?= $sapi_item['nama_pemilik'] ?></p>
                                <p>Alamat: <?= $sapi_item['alamat_pemilik'] ?></p>
                                <p>Nomor HP: <?= $sapi_item['nomor_pemilik'] ?></p>
                                <p>Email: <?= $sapi_item['email_pemilik'] ?></p>
                            <?php else: ?>
                                <p>Data detail untuk jenis sapi ini belum tersedia.</p>
                            <?php endif; ?>
                        </div>
                    </div>
            <?php
                endwhile;
            } else {
                echo '<div class="col-12"><p>Tidak ada data sapi untuk jenis ini.</p></div>';
            }
            ?>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>