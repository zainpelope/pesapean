<?php
// Mulai sesi untuk menyimpan informasi pengguna setelah login
session_start();

// Include file koneksi database Anda
include '../koneksi.php';

// Pastikan request method adalah POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil dan bersihkan input dari form
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    $role_id = mysqli_real_escape_string($koneksi, $_POST['role_id']); // id_role dari dropdown

    // Validasi input sederhana
    if (empty($username) || empty($password) || empty($role_id)) {
        header("Location: login.php?error=Semua bidang harus diisi.");
        exit();
    }

    // Query untuk mencari user berdasarkan username dan id_role
    // Catatan: Password harusnya di-hash (misal: menggunakan password_hash()) saat disimpan di database
    // dan diverifikasi menggunakan password_verify() saat login.
    // Untuk contoh ini, saya menggunakan perbandingan langsung, tetapi ini TIDAK AMAN untuk produksi.
    $query = "SELECT id_user, username, password, id_role FROM users WHERE username = '$username' AND id_role = '$role_id'";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);

            // Verifikasi password
            // Idealnya: if (password_verify($password, $user['password'])) {
            if ($password === $user['password']) { // Ini hanya untuk contoh sederhana, GANTI dengan hashing!
                // Login berhasil
                $_SESSION['id_user'] = $user['id_user'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['id_role'] = $user['id_role'];

                // Dapatkan nama peran untuk kemudahan (opsional)
                $role_name_query = "SELECT nama_role FROM role WHERE id_role = " . $user['id_role'];
                $role_name_result = mysqli_query($koneksi, $role_name_query);
                if ($role_name_result && mysqli_num_rows($role_name_result) > 0) {
                    $role_name_row = mysqli_fetch_assoc($role_name_result);
                    $_SESSION['nama_role'] = $role_name_row['nama_role'];
                }

                // Redirect ke halaman dashboard atau halaman sesuai peran
                switch ($_SESSION['nama_role']) {
                    case 'Admin':
                        header("Location: ../admin/admin.php");
                        break;
                    case 'Penjual': // Asumsi 'Penjual' adalah nama role untuk penjual
                        header("Location: ../penjual/beranda.php");
                        break;
                    case 'Pembeli': // Asumsi 'Pembeli' adalah nama role untuk penawar/pembeli
                        header("Location: ../pembeli/beranda.php");
                        break;
                    default:
                        header("Location: ../penjual/beranda.php"); // Halaman default jika role tidak spesifik
                        break;
                }
                exit();
            } else {
                // Password salah
                header("Location: login.php?error=Username atau password salah.");
                exit();
            }
        } else {
            // Username atau role_id tidak ditemukan
            header("Location: login.php?error=Username atau password salah.");
            exit();
        }
    } else {
        // Error query
        header("Location: login.php?error=Terjadi kesalahan database: " . mysqli_error($koneksi));
        exit();
    }
} else {
    // Jika diakses langsung tanpa POST request
    header("Location: login.php");
    exit();
}

// Tutup koneksi database
mysqli_close($koneksi);
