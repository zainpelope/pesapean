<?php
include 'koneksi.php';

$id = $_GET['id'];

// Hapus data sapi, semua data terkait akan otomatis terhapus karena ON DELETE CASCADE
mysqli_query($koneksi, "DELETE FROM data_sapi WHERE id_sapi = $id");

header("Location: data_sapi.php");
exit;
