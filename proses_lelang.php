<?php
// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Mulai sesi (penting untuk mengecek status login dan mendapatkan id_user)
session_start();

// Sertakan file koneksi database Anda
include 'koneksi.php'; // Pastikan path ini benar

// --- PENTING: Cek status login dan peran pengguna ---
// Pastikan hanya penjual yang login yang dapat mengakses proses ini
if (!isset($_SESSION['id_user']) || $_SESSION['nama_role'] !== 'Penjual') {
    header("Location: ../auth/login.php?error=Akses tidak diizinkan. Anda harus login sebagai Penjual untuk membuat lelang.");
    exit();
}

// Ambil ID user penjual yang sedang login dari sesi
$id_user_penjual_login = $_SESSION['id_user'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil input dari form
    $id_sapi = $_POST['id_sapi'];
    $harga_awal = $_POST['harga_awal'];
    $harga_tertinggi = $_POST['harga_tertinggi']; // Ini akan menjadi uang jaminan awal
    $batas_waktu = $_POST['batas_waktu'];
    $status = $_POST['status'];

    $createdAt = date('Y-m-d H:i:s');
    $updatedAt = date('Y-m-d H:i:s');

    // Query untuk menyimpan data ke tabel `lelang`
    // Tambahkan kolom `id_user` untuk menyimpan ID penjual yang membuat lelang
    // id_penawaranTertinggi akan diisi NULL atau 0 pada awalnya, karena belum ada penawaran
    $query_lelang = "INSERT INTO lelang (id_sapi, harga_awal, harga_tertinggi, batas_waktu, status, createdAt, updatedAt, id_user)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt_lelang = mysqli_prepare($koneksi, $query_lelang);

    if ($stmt_lelang) {
        // Bind parameter ke statement
        // "iidssssi" berarti:
        // i = id_sapi (integer)
        // i = harga_awal (integer)
        // i = harga_tertinggi (integer)
        // s = batas_waktu (string, karena format datetime)
        // s = status (string)
        // s = createdAt (string)
        // s = updatedAt (string)
        // i = id_user (integer) <-- Kolom baru yang ditambahkan
        mysqli_stmt_bind_param(
            $stmt_lelang,
            "iiissssi", // String binding yang diperbarui
            $id_sapi,
            $harga_awal,
            $harga_tertinggi,
            $batas_waktu,
            $status,
            $createdAt,
            $updatedAt,
            $id_user_penjual_login // Ambil dari sesi
        );

        // Eksekusi statement
        if (mysqli_stmt_execute($stmt_lelang)) {
            // Lelang berhasil disimpan
            // Redirect ke halaman daftar lelang penjual, dengan filter 'Lelang Saya' aktif
            header("Location: penjual/lelang.php");
            exit(); // Penting untuk menghentikan eksekusi skrip setelah header redirect
        } else {
            // Gagal menyimpan lelang
            echo "<div class='alert alert-danger' role='alert'>Gagal menyimpan lelang: " . mysqli_error($koneksi) . "</div>";
            // Anda bisa menambahkan redirect kembali ke form dengan pesan error
            // header("Location: form_tambah_lelang.php?error=Gagal menyimpan lelang");
            // exit();
        }
        mysqli_stmt_close($stmt_lelang); // Tutup statement
    } else {
        // Handle error jika prepared statement gagal dibuat
        die("Error prepared statement for lelang: " . mysqli_error($koneksi));
    }
} else {
    // Jika akses bukan melalui metode POST, redirect kembali ke form
    header("Location: form_tambah_lelang.php");
    exit();
}

// Tutup koneksi database
mysqli_close($koneksi);
