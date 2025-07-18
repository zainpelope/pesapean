<?php
include '../koneksi.php'; // Adjust path as needed
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_GET['id'])) {
    $id_home = mysqli_real_escape_string($koneksi, $_GET['id']);

    // First, get the image name to delete the file
    $sql_select_image = "SELECT gambar FROM home WHERE id_home = '$id_home'";
    $result_select_image = mysqli_query($koneksi, $sql_select_image);

    if ($result_select_image && mysqli_num_rows($result_select_image) > 0) {
        $row = mysqli_fetch_assoc($result_select_image);
        $image_to_delete = $row['gambar'];

        // Delete data from database
        $sql_delete = "DELETE FROM home WHERE id_home = '$id_home'";
        $result_delete = mysqli_query($koneksi, $sql_delete);

        if ($result_delete) {
            // Delete the image file from the server
            if (!empty($image_to_delete) && file_exists("../uploads/" . $image_to_delete)) {
                unlink("../uploads/" . $image_to_delete);
            }
            echo "✅ Data berhasil dihapus.";
        } else {
            echo "❌ Gagal menghapus data: " . mysqli_error($koneksi);
        }
    } else {
        echo "❌ Data tidak ditemukan untuk dihapus.";
    }
} else {
    echo "❌ ID data tidak ditemukan.";
}

mysqli_close($koneksi);
echo "<br><a href='../admin/admin.php'>Kembali ke Halaman Utama</a>";
