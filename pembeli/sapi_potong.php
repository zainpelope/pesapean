<?php
include 'koneksi.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_sapi = $_POST['id_sapi'];
    $nama_sapi = $_POST['nama_sapi'];
    $berat_badan = $_POST['berat_badan'];
    $persentase_daging = $_POST['persentase_daging'];

    $insert = "INSERT INTO sapiPotong (
        id_sapi, nama_sapi, berat_badan, persentase_daging
    ) VALUES (
        '$id_sapi', '$nama_sapi', '$berat_badan', '$persentase_daging'
    )";

    if (mysqli_query($koneksi, $insert)) {
        $_SESSION['success'] = "Data sapi potong berhasil disimpan.";
        header("Location: pembeli/data_sapi.php");
        exit();
    } else {
        echo "<div style='color: red;'>❌ Gagal menyimpan data sapi potong: " . mysqli_error($koneksi) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Form Tambah Sapi Potong</title>
</head>

<body>
    <h2>Form Tambah Sapi Potong</h2>
    <form method="POST">
        <label>Sapi (pilih dari data_sapi):</label><br>
        <select name="id_sapi" required>
            <option value="">-- Pilih Sapi Potong --</option>
            <?php
            $sapi = mysqli_query($koneksi, "SELECT * FROM data_sapi WHERE id_macamSapi = 5");
            while ($d = mysqli_fetch_assoc($sapi)) {
                echo "<option value='{$d['id_sapi']}'>{$d['id_sapi']} - {$d['nama_pemilik']}</option>";
            }
            ?>
        </select><br><br>

        <label>Nama Sapi:</label><br>
        <input type="text" name="nama_sapi" required><br><br>

        <label>Berat Badan:</label><br>
        <input type="text" name="berat_badan"><br><br>

        <label>Persentase Daging (%):</label><br>
        <input type="text" name="persentase_daging"><br><br>

        <button type="submit">Simpan</button>
    </form>
</body>

</html>