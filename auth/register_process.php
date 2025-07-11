<?php
// Include file koneksi database Anda
include 'koneksi.php';

// Pastikan request method adalah POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil dan bersihkan input dari form
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password']; // Jangan langsung escape, kita akan hash
    $confirm_password = $_POST['confirm_password'];
    $id_role = mysqli_real_escape_string($koneksi, $_POST['id_role']); // ID role default dari hidden input

    // --- Validasi Input ---
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($id_role)) {
        header("Location: register.php?error=Semua bidang harus diisi.");
        exit();
    }

    if ($password !== $confirm_password) {
        header("Location: register.php?error=Konfirmasi password tidak cocok.");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: register.php?error=Format email tidak valid.");
        exit();
    }

    // Periksa apakah username atau email sudah ada
    $check_query = "SELECT id_user FROM users WHERE username = '$username' OR email = '$email'";
    $check_result = mysqli_query($koneksi, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        header("Location: register.php?error=Username atau email sudah terdaftar.");
        exit();
    }

    // --- Hashing Password ---
    // --- PENTING: INI SANGAT TIDAK AMAN DAN TIDAK DIREKOMENDASIKAN ---
    $hashed_password = $password; // Menyimpan password dalam bentuk plain text

    // --- Masukkan data ke database ---
    $created_at = date('Y-m-d H:i:s'); // Waktu saat ini
    $updated_at = date('Y-m-d H:i:s'); // Waktu saat ini

    $insert_query = "INSERT INTO users (username, email, password, id_role, createdAt, updateAt)
                     VALUES ('$username', '$email', '$hashed_password', '$id_role', '$created_at', '$updated_at')";

    if (mysqli_query($koneksi, $insert_query)) {
        header("Location: register.php?success=Registrasi berhasil! Silakan login.");
        exit();
    } else {
        header("Location: register.php?error=Terjadi kesalahan saat registrasi: " . mysqli_error($koneksi));
        exit();
    }
} else {
    // Jika diakses langsung tanpa POST request
    header("Location: register.php");
    exit();
}

// Tutup koneksi database
mysqli_close($koneksi);
