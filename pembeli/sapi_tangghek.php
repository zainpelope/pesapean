<?php

include 'koneksi.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_sapi = $_POST['id_sapi'];
    $tinggi_badan = $_POST['tinggi_badan'];
    $panjang_badan = $_POST['panjang_badan'];
    $lingkar_dada = $_POST['lingkar_dada'];
    $bobot_badan = $_POST['bobot_badan'];
    $intensitas_latihan = $_POST['intensitas_latihan'];
    $jarak_latihan = $_POST['jarak_latihan'];
    $prestasi = $_POST['prestasi'];
    $kesehatan = $_POST['kesehatan'];

    $insert = "INSERT INTO sapiTangeh (
        id_sapi, tinggi_badan, panjang_badan, lingkar_dada,
        bobot_badan, intensitas_latihan, jarak_latihan,
        prestasi, kesehatan
    ) VALUES (
        '$id_sapi', '$tinggi_badan', '$panjang_badan', '$lingkar_dada',
        '$bobot_badan', '$intensitas_latihan', '$jarak_latihan',
        '$prestasi', '$kesehatan'
    )";

    if (mysqli_query($koneksi, $insert)) {
        $_SESSION['success'] = "Data sapi tangeh berhasil disimpan.";
        header("Location: pembeli/data_sapi.php");
        exit();
    } else {
        echo "<div style='color: red;'>❌ Gagal menyimpan data sapi tangeh: " . mysqli_error($koneksi) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Form Tambah Sapi Tangeh</title>
</head>

<body>
    <h2>Form Tambah Sapi Tangeh</h2>
    <form method="POST">
        <label>Sapi (pilih dari data_sapi):</label><br>
        <select name="id_sapi" required>
            <option value="">-- Pilih Sapi Tangeh --</option>
            <?php
            $sapi = mysqli_query($koneksi, "SELECT * FROM data_sapi WHERE id_macamSapi = 3");
            while ($d = mysqli_fetch_assoc($sapi)) {
                echo "<option value='{$d['id_sapi']}'>{$d['id_sapi']} - {$d['nama_pemilik']}</option>";
            }
            ?>
        </select><br><br>

        <label>Tinggi Badan:</label><br>
        <input type="text" name="tinggi_badan" required><br><br>

        <label>Panjang Badan:</label><br>
        <input type="text" name="panjang_badan" required><br><br>

        <label>Lingkar Dada:</label><br>
        <input type="text" name="lingkar_dada" required><br><br>

        <label>Bobot Badan:</label><br>
        <input type="text" name="bobot_badan" required><br><br>

        <label>Intensitas Latihan:</label><br>
        <input type="text" name="intensitas_latihan"><br><br>

        <label>Jarak Latihan:</label><br>
        <input type="text" name="jarak_latihan"><br><br>

        <label>Prestasi:</label><br>
        <input type="text" name="prestasi"><br><br>

        <label>Kesehatan:</label><br>
        <input type="text" name="kesehatan"><br><br>

        <button type="submit">Simpan</button>
    </form>
</body>

</html>