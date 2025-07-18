<?php
include '../koneksi.php'; // Adjust path as needed
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$id_home = '';
$sejarah = '';
$gambar_lama = '';
$message = '';

if (isset($_GET['id'])) {
    $id_home = mysqli_real_escape_string($koneksi, $_GET['id']);

    // Fetch existing data
    $sql = "SELECT sejarah, gambar FROM home WHERE id_home = '$id_home'";
    $result = mysqli_query($koneksi, $sql);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $sejarah = $row['sejarah'];
        $gambar_lama = $row['gambar'];
    } else {
        $message = "❌ Data tidak ditemukan.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_home = mysqli_real_escape_string($koneksi, $_POST['id_home']);
    $sejarah = mysqli_real_escape_string($koneksi, $_POST['sejarah']);
    $gambar_lama_post = mysqli_real_escape_string($koneksi, $_POST['gambar_lama']);
    $gambar_baru = $_FILES['gambar']['name'];

    // If a new image is uploaded
    if (!empty($gambar_baru)) {
        $tmp = $_FILES['gambar']['tmp_name'];
        $path = "uploads/" . basename($gambar_baru); // Use basename for security

        if (move_uploaded_file($tmp, $path)) {
            // Delete old image if it exists
            if (!empty($gambar_lama_post) && file_exists("uploads/" . $gambar_lama_post)) {
                unlink("uploads/" . $gambar_lama_post);
            }
            $gambar_to_save = basename($gambar_baru);
        } else {
            $message = "❌ Gagal mengupload gambar baru.";
            $gambar_to_save = $gambar_lama_post; // Fallback to old image
        }
    } else {
        $gambar_to_save = $gambar_lama_post; // Keep old image if no new one is uploaded
    }

    // Update data in database
    $sql_update = "UPDATE home SET sejarah = '$sejarah', gambar = '$gambar_to_save' WHERE id_home = '$id_home'";
    $result_update = mysqli_query($koneksi, $sql_update);

    if ($result_update) {
        $message = "✅ Data berhasil diperbarui.";
        // Refresh data to show updated image if any
        $sql_refresh = "SELECT gambar FROM home WHERE id_home = '$id_home'";
        $res_refresh = mysqli_query($koneksi, $sql_refresh);
        if (mysqli_num_rows($res_refresh) > 0) {
            $row_refresh = mysqli_fetch_assoc($res_refresh);
            $gambar_lama = $row_refresh['gambar']; // Update gambar_lama for display
        }
    } else {
        $message = "❌ Gagal memperbarui data: " . mysqli_error($koneksi);
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Data Home</title>
</head>

<body>
    <h2>Form Edit Data Home</h2>
    <?php if (!empty($message)): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id_home" value="<?php echo htmlspecialchars($id_home); ?>">
        <input type="hidden" name="gambar_lama" value="<?php echo htmlspecialchars($gambar_lama); ?>">

        <label>Sejarah:</label><br>
        <textarea name="sejarah" rows="5" required><?php echo htmlspecialchars($sejarah); ?></textarea><br><br>

        <label>Gambar Saat Ini:</label><br>
        <?php if (!empty($gambar_lama)): ?>
            <img src="uploads/<?php echo htmlspecialchars($gambar_lama); ?>" width="150"><br>
        <?php else: ?>
            Tidak ada gambar.
        <?php endif; ?><br>

        <label>Upload Gambar Baru (Opsional):</label><br>
        <input type="file" name="gambar" accept="image/*"><br><br>

        <input type="submit" value="Simpan Perubahan">
        <button type="button" onclick="window.location.href='index.php'">Kembali</button>
    </form>
</body>

</html>