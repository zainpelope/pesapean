<?php
include '../koneksi.php'; // Adjust path to your koneksi.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Using prepared statements for security
    $sql = "DELETE FROM home WHERE id_home = ?"; // Assuming 'id_home' is your primary key
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id); // 'i' for integer type

    if (mysqli_stmt_execute($stmt)) {
        echo "✅ Data berhasil dihapus.";
        // JavaScript redirect after success
        echo '<script>
                setTimeout(function() {
                    window.location.href = "../admin/admin.php";
                }, 1000); // Redirect after 1 second
              </script>';
    } else {
        echo "❌ Gagal menghapus data: " . mysqli_error($koneksi);
    }
    mysqli_stmt_close($stmt);
} else {
    echo "ID tidak ditemukan.";
}
