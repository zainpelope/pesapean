<?php

include '../koneksi.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_sapi = $_POST['id_sapi'];
    $nama_sapi = $_POST['nama_sapi'];
    $ketahanan_fisik = $_POST['ketahanan_fisik'];
    $kecepatan_lari = $_POST['kecepatan_lari'];
    $penghargaan = $_POST['penghargaan'];

    // Simpan ke tabel sapiKerap
    $insertKerap = "INSERT INTO sapiKerap (
        id_sapi, nama_sapi, ketahanan_fisik, kecepatan_lari, penghargaan
    ) VALUES (
        '$id_sapi', '$nama_sapi', '$ketahanan_fisik', '$kecepatan_lari', '$penghargaan'
    )";

    if (mysqli_query($koneksi, $insertKerap)) {
        $_SESSION['success'] = "Data sapi kerap berhasil disimpan.";
        header("Location: ../pembeli/data_sapi.php");
        exit();
    } else {
        echo "<div style='color: red;'>❌ Gagal menyimpan data sapi kerap: " . mysqli_error($koneksi) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Form Tambah Sapi Kerap</title>
</head>

<body>
    <h2>Form Tambah Sapi Kerap</h2>
    <form method="POST">
        <label>Sapi (pilih dari data_sapi):</label><br>
        <select name="id_sapi" required>
            <option value="">-- Pilih Sapi Kerap --</option>
            <?php
            $sapi = mysqli_query($koneksi, "SELECT * FROM data_sapi WHERE id_macamSapi = 2");
            while ($d = mysqli_fetch_assoc($sapi)) {
                echo "<option value='{$d['id_sapi']}'>{$d['id_sapi']} - {$d['nama_pemilik']}</option>";
            }
            ?>
        </select><br><br>

        <label>Nama Sapi:</label><br>
        <input type="text" name="nama_sapi" required><br><br>

        <label>Ketahanan Fisik:</label><br>
        <input type="text" name="ketahanan_fisik"><br><br>

        <label>Kecepatan Lari:</label><br>
        <input type="text" name="kecepatan_lari"><br><br>

        <label>Penghargaan:</label><br>
        <input type="text" name="penghargaan"><br><br>

        <button type="submit">Simpan</button>
    </form>
</body>

</html>