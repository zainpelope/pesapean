<?php
include '../../koneksi.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Data Sapi Sonok
    $id_sapi = $_POST['id_sapi'];
    $nama_sapi = $_POST['nama_sapi'];
    $umur = $_POST['umur'];
    $lingkar_dada = $_POST['lingkar_dada'];
    $panjang_badan = $_POST['panjang_badan'];
    $tinggi_pundak = $_POST['tinggi_pundak'];
    $tinggi_punggung = $_POST['tinggi_punggung'];
    $panjang_wajah = $_POST['panjang_wajah'];
    $lebar_punggul = $_POST['lebar_punggul'];
    $lebar_dada = $_POST['lebar_dada'];
    $tinggi_kaki = $_POST['tinggi_kaki'];
    $kesehatan = $_POST['kesehatan'];
    $now = date('Y-m-d H:i:s');

    // Simpan ke tabel sapiSonok
    $insertSonok = "INSERT INTO sapiSonok (
        id_sapi, nama_sapi, umur, lingkar_dada, panjang_badan,
        tinggi_pundak, tinggi_punggung, panjang_wajah,
        lebar_punggul, lebar_dada, tinggi_kaki, kesehatan
    ) VALUES (
        '$id_sapi', '$nama_sapi', '$umur', '$lingkar_dada', '$panjang_badan',
        '$tinggi_pundak', '$tinggi_punggung', '$panjang_wajah',
        '$lebar_punggul', '$lebar_dada', '$tinggi_kaki', '$kesehatan'
    )";

    if (mysqli_query($koneksi, $insertSonok)) {
        $id_sonok = mysqli_insert_id($koneksi); // Ambil ID sapiSonok terakhir

        // Ambil data generasi 1
        $namaPejantan1 = $_POST['namaPejantanGenerasiSatu'] ?? '';
        $jenisPejantan1 = $_POST['jenisPejantanGenerasiSatu'] ?? '';
        $namaInduk1 = $_POST['namaIndukGenerasiSatu'] ?? '';
        $jenisInduk1 = $_POST['jenisIndukGenerasiSatu'] ?? '';
        $kakekPejantan1 = $_POST['namaKakekPejantanGenerasiSatu'] ?? '';

        // Simpan ke generasiSatu jika diisi
        if (!empty($namaPejantan1) || !empty($namaInduk1)) {
            $insertGen1 = "INSERT INTO generasiSatu (
                sapiSonok, namaPejantanGenerasiSatu, jenisPejantanGenerasiSatu,
                namaIndukGenerasiSatu, jenisIndukGenerasiSatu,
                namaKakekPejantanGenerasiSatu, updatedAt
            ) VALUES (
                '$id_sonok', '$namaPejantan1', '$jenisPejantan1',
                '$namaInduk1', '$jenisInduk1', '$kakekPejantan1', '$now'
            )";
            mysqli_query($koneksi, $insertGen1);
        }

        // Ambil data generasi 2
        $namaPejantan2 = $_POST['namaPejantanGenerasiDua'] ?? '';
        $jenisPejantan2 = $_POST['jenisPejantanGenerasiDua'] ?? '';
        $namaInduk2 = $_POST['namaIndukGenerasiDua'] ?? '';
        $jenisInduk2 = $_POST['jenisIndukGenerasiDua'] ?? '';
        $jenisKakekPejantan2 = $_POST['jenisKakekPejantanGenerasiDua'] ?? '';
        $namaNenekInduk2 = $_POST['namaNenekIndukGenerasiDua'] ?? '';

        // Simpan ke generasiDua jika diisi
        if (!empty($namaPejantan2) || !empty($namaInduk2)) {
            $insertGen2 = "INSERT INTO generasiDua (
                sapiSonok, namaPejantanGenerasiDua, jenisPejantanGenerasiDua,
                namaIndukGenerasiDua, jenisIndukGenerasiDua,
                jenisKakekPejantanGenerasiDua, namaNenekIndukGenerasiDua,
                createdAt, updatedAt
            ) VALUES (
                '$id_sonok', '$namaPejantan2', '$jenisPejantan2',
                '$namaInduk2', '$jenisInduk2', '$jenisKakekPejantan2',
                '$namaNenekInduk2', '$now', '$now'
            )";
            mysqli_query($koneksi, $insertGen2);
        }

        // Set session pesan sukses dan redirect
        $_SESSION['success'] = 'Data sapi sonok dan generasi berhasil disimpan.';
        header("Location: ../../pembeli/data_sapi.php");
        exit();
    } else {
        echo "<div style='color: red;'>❌ Gagal menyimpan data sapi sonok: " . mysqli_error($koneksi) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Form Tambah Sapi Sonok</title>
</head>

<body>
    <h2>Form Tambah Sapi Sonok & Generasi</h2>
    <form method="POST">
        <fieldset>
            <legend><strong>Data Sapi Sonok</strong></legend>
            <label>Sapi (pilih dari data_sapi):</label><br>
            <select name="id_sapi" required>
                <option value="">-- Pilih Sapi --</option>
                <?php
                $sapi = mysqli_query($koneksi, "SELECT * FROM data_sapi WHERE id_macamSapi = 1");
                while ($d = mysqli_fetch_assoc($sapi)) {
                    echo "<option value='{$d['id_sapi']}'>{$d['id_sapi']} - {$d['nama_pemilik']}</option>";
                }
                ?>
            </select><br><br>

            <label>Nama Sapi:</label><br><input type="text" name="nama_sapi" required><br><br>
            <label>Umur:</label><br><input type="text" name="umur"><br><br>
            <label>Lingkar Dada:</label><br><input type="text" name="lingkar_dada"><br><br>
            <label>Panjang Badan:</label><br><input type="text" name="panjang_badan"><br><br>
            <label>Tinggi Pundak:</label><br><input type="text" name="tinggi_pundak"><br><br>
            <label>Tinggi Punggung:</label><br><input type="text" name="tinggi_punggung"><br><br>
            <label>Panjang Wajah:</label><br><input type="text" name="panjang_wajah"><br><br>
            <label>Lebar Punggul:</label><br><input type="text" name="lebar_punggul"><br><br>
            <label>Lebar Dada:</label><br><input type="text" name="lebar_dada"><br><br>
            <label>Tinggi Kaki:</label><br><input type="text" name="tinggi_kaki"><br><br>
            <label>Kesehatan:</label><br><input type="text" name="kesehatan"><br><br>
        </fieldset>

        <fieldset>
            <legend><strong>Generasi 1</strong></legend>
            <label>Nama Pejantan:</label><br><input type="text" name="namaPejantanGenerasiSatu"><br><br>
            <label>Jenis Pejantan:</label><br><input type="text" name="jenisPejantanGenerasiSatu"><br><br>
            <label>Nama Induk:</label><br><input type="text" name="namaIndukGenerasiSatu"><br><br>
            <label>Jenis Induk:</label><br><input type="text" name="jenisIndukGenerasiSatu"><br><br>
            <label>Nama Kakek Pejantan:</label><br><input type="text" name="namaKakekPejantanGenerasiSatu"><br><br>
        </fieldset>

        <fieldset>
            <legend><strong>Generasi 2</strong></legend>
            <label>Nama Pejantan:</label><br><input type="text" name="namaPejantanGenerasiDua"><br><br>
            <label>Jenis Pejantan:</label><br><input type="text" name="jenisPejantanGenerasiDua"><br><br>
            <label>Nama Induk:</label><br><input type="text" name="namaIndukGenerasiDua"><br><br>
            <label>Jenis Induk:</label><br><input type="text" name="jenisIndukGenerasiDua"><br><br>
            <label>Jenis Kakek Pejantan:</label><br><input type="text" name="jenisKakekPejantanGenerasiDua"><br><br>
            <label>Nama Nenek Induk:</label><br><input type="text" name="namaNenekIndukGenerasiDua"><br><br>
        </fieldset>

        <button type="submit">Simpan Semua</button>
    </form>
</body>

</html>