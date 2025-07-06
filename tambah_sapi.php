<?php
include 'koneksi.php';

// Proses jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $foto = $_FILES['foto']['name'];
    $tmp = $_FILES['foto']['tmp_name'];
    $path = "uploads/" . $foto;

    // Buat folder jika belum ada
    if (!file_exists('uploads')) {
        mkdir('uploads', 0777, true);
    }

    if (move_uploaded_file($tmp, $path)) {
        $harga = $_POST['harga'];
        $nama = $_POST['nama_pemilik'];
        $alamat = $_POST['alamat_pemilik'];
        $nomor = $_POST['nomor_pemilik'];
        $email = $_POST['email_pemilik'];
        $jenis = $_POST['id_macamSapi'];
        $now = date('Y-m-d H:i:s');

        // Ambil ID terakhir dari data_sapi
        $result = mysqli_query($koneksi, "SELECT MAX(id_sapi) AS last_id FROM data_sapi");
        $row = mysqli_fetch_assoc($result);
        $new_id = $row['last_id'] + 1;

        // Simpan data ke database
        $query = "INSERT INTO data_sapi 
        (id_sapi, foto_sapi, harga_sapi, nama_pemilik, alamat_pemilik, nomor_pemilik, email_pemilik, createdAt, updatedAt, id_macamSapi)
        VALUES
        ('$new_id', '$foto', '$harga', '$nama', '$alamat', '$nomor', '$email', '$now', '$now', '$jenis')";

        if (mysqli_query($koneksi, $query)) {
            echo "<div style='color: green;'>✅ Data sapi berhasil ditambahkan.</div>";
        } else {
            echo "<div style='color: red;'>❌ Gagal menambahkan data sapi: " . mysqli_error($koneksi) . "</div>";
        }
    } else {
        echo "<div style='color: red;'>❌ Gagal upload foto sapi.</div>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Tambah Data Sapi</title>
</head>

<body>
    <h2>Form Tambah Data Sapi</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <label>Foto Sapi:</label><br>
        <input type="file" name="foto" required><br><br>

        <label>Harga Sapi:</label><br>
        <input type="number" name="harga" required><br><br>

        <label>Nama Pemilik:</label><br>
        <input type="text" name="nama_pemilik" required><br><br>

        <label>Alamat Pemilik:</label><br>
        <input type="text" name="alamat_pemilik" required><br><br>

        <label>Nomor Pemilik:</label><br>
        <input type="text" name="nomor_pemilik" required><br><br>

        <label>Email Pemilik:</label><br>
        <input type="email" name="email_pemilik" required><br><br>

        <label>Jenis Sapi:</label><br>
        <select name="id_macamSapi" required>
            <option value="">-- Pilih Jenis --</option>
            <option value="1">Sapi Sonok</option>
            <option value="2">Sapi Kerap</option>
            <option value="3">Sapi Tangghek</option>
            <option value="4">Sapi Ternak</option>
            <option value="5">Sapi Potong</option>
        </select><br><br>

        <button type="submit">Simpan</button>
    </form>
</body>

</html>