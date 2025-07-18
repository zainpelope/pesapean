<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start the session
session_start();

include '../koneksi.php'; // Ensure this path is correct for your database connection

// Check login status and user role (only sellers can submit auctions)
if (!isset($_SESSION['id_user']) || $_SESSION['nama_role'] !== 'Penjual') {
    header("Location: ../auth/login.php?error=Akses tidak diizinkan. Anda harus login sebagai Penjual.");
    exit();
}

$id_user_penjual_login = $_SESSION['id_user'];

if (isset($_POST['submit'])) {
    $id_sapi = $_POST['id_sapi'];
    $harga_awal = $_POST['harga_awal'];
    $harga_tertinggi = $_POST['harga_tertinggi']; // This will be the initial highest bid
    $batas_waktu = $_POST['batas_waktu'];

    // Initial status is 'Pending' and not yet approved by admin
    $status_lelang = 'Pending';
    $approved_by_admin = 0;

    // Get current date and time for createdAt and updatedAt
    $createdAt = date('Y-m-d H:i:s');
    $updatedAt = date('Y-m-d H:i:s');

    // Use prepared statement to prevent SQL Injection
    $stmt = mysqli_prepare($koneksi, "INSERT INTO lelang (id_sapi, id_user, harga_awal, harga_tertinggi, batas_waktu, status, createdAt, updatedAt, approved_by_admin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if ($stmt) {
        // 'i' for integer, 'd' for double/decimal, 's' for string
        mysqli_stmt_bind_param(
            $stmt,
            "iiidssssi",
            $id_sapi,
            $id_user_penjual_login,
            $harga_awal,
            $harga_tertinggi,
            $batas_waktu,
            $status_lelang,
            $createdAt,
            $updatedAt,
            $approved_by_admin
        );

        if (mysqli_stmt_execute($stmt)) {
            // Redirect to a success page or the seller's auction list
            header("Location: ../penjual/lelang.php?status=success_pending");
            exit();
        } else {
            // If failed
            echo "Error: " . mysqli_error($koneksi);
        }
        mysqli_stmt_close($stmt);
    } else {
        die("Error preparing statement: " . mysqli_error($koneksi));
    }
} else {
    // If direct access to proses_lelang.php without form submission
    header("Location: ../penjual/form_lelang.php");
    exit();
}

mysqli_close($koneksi);
