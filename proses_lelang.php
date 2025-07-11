<?php
include 'koneksi.php';

if (isset($_POST['submit'])) {
    $id_sapi = $_POST['id_sapi'];
    $harga_awal = $_POST['harga_awal'];
    $harga_tertinggi = $_POST['harga_tertinggi'];
    $batas_waktu = $_POST['batas_waktu'];
    $status = $_POST['status'];

    $now = date('Y-m-d H:i:s');

    // Cek apakah sapi sudah terdaftar di lelang
    $cek = mysqli_query($koneksi, "SELECT * FROM lelang WHERE id_sapi = '$id_sapi'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Sapi ini sudah terdaftar di lelang!'); window.history.back();</script>";
        exit;
    }

    // Insert ke tabel lelang
    $insert = mysqli_query($koneksi, "
        INSERT INTO lelang (id_sapi, harga_awal, harga_tertinggi, id_penawaranTertinggi, batas_waktu, status, createdAt, updatedAt)
        VALUES ('$id_sapi', '$harga_awal', '$harga_tertinggi', NULL, '$batas_waktu', '$status', '$now', '$now')
    ");

    if ($insert) {
        echo "<script>alert('Lelang berhasil disimpan!'); window.location='pembeli/lelang.php';</script>";
    } else {
        echo "Gagal: " . mysqli_error($koneksi);
    }
}
