<?php
include '../koneksi.php'; // sesuaikan path dengan file koneksi kamu
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sejarah = $_POST['sejarah'];

    // Upload gambar
    $gambar = $_FILES['gambar']['name'];
    $tmp = $_FILES['gambar']['tmp_name'];
    $path = "../uploads/" . $gambar;

    if (move_uploaded_file($tmp, $path)) {
        // Simpan data ke database (tanpa id_home)
        $sql = "INSERT INTO home (sejarah, gambar) VALUES ('$sejarah', '$gambar')";
        $result = mysqli_query($koneksi, $sql);

        if ($result) {
            echo "✅ Data berhasil ditambahkan.";
        } else {
            echo "❌ Gagal menambahkan data: " . mysqli_error($koneksi);
        }
    } else {
        echo "❌ Gagal mengupload gambar.";
    }
}
?>

<!-- Form Input -->
<!DOCTYPE html>
<html>

<head>
    <title>Tambah Data Home</title>
</head>

<body>
    <h2>Form Tambah Data Home</h2>
    <form method="POST" enctype="multipart/form-data">
        <label>Sejarah:</label><br>
        <textarea name="sejarah" rows="5" required></textarea><br><br>

        <label>Upload Gambar:</label><br>
        <input type="file" name="gambar" accept="image/*" required><br><br>

        <input type="submit" value="Simpan">
    </form>
</body>

</html>