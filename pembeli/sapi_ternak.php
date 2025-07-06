<?php
include 'koneksi.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_sapi = $_POST['id_sapi'];
    $nama_sapi = $_POST['nama_sapi'];
    $kesuburan = $_POST['kesuburan'];
    $riwayat_kesehatan = $_POST['riwayat_kesehatan'];

    $insert = "INSERT INTO sapiTermak (
        id_sapi, nama_sapi, kesuburan, riwayat_kesehatan
    ) VALUES (
        '$id_sapi', '$nama_sapi', '$kesuburan', '$riwayat_kesehatan'
    )";

    if (mysqli_query($koneksi, $insert)) {
        $_SESSION['success'] = "Data sapi termak berhasil disimpan.";
        header("Location: pembeli/data_sapi.php");
        exit();
    } else {
        echo "<div style='color: red;'>❌ Gagal menyimpan data sapi termak: " . mysqli_error($koneksi) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Form Tambah Sapi Termak</title>
</head>

<body>
    <h2>Form Tambah Sapi Termak</h2>
    <form method="POST">
        <label>Sapi (pilih dari data_sapi):</label><br>
        <select name="id_sapi" required>
            <option value="">-- Pilih Sapi Termak --</option>
            <?php
            $sapi = mysqli_query($koneksi, "SELECT * FROM data_sapi WHERE id_macamSapi = 4");
            while ($d = mysqli_fetch_assoc($sapi)) {
                echo "<option value='{$d['id_sapi']}'>{$d['id_sapi']} - {$d['nama_pemilik']}</option>";
            }
            ?>
        </select><br><br>

        <label>Nama Sapi:</label><br>
        <input type="text" name="nama_sapi" required><br><br>

        <label>Kesuburan:</label><br>
        <input type="text" name="kesuburan"><br><br>

        <label>Riwayat Kesehatan:</label><br>
        <input type="text" name="riwayat_kesehatan"><br><br>

        <button type="submit">Simpan</button>
    </form>
</body>

</html>