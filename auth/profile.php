<?php
session_start(); // Mulai sesi untuk mengakses data sesi

// Include file koneksi database Anda
include '../koneksi.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['id_user'])) {
    // Jika belum login, redirect ke halaman login
    header("Location: ../auth/login.php?error=Anda harus login untuk melihat profil.");
    exit();
}

$user_id = $_SESSION['id_user']; // Ambil ID pengguna dari sesi

$user_data = null; // Variabel untuk menyimpan data pengguna

// Query untuk mengambil data pengguna dari tabel users
// dan juga nama role dari tabel role
$query = "
    SELECT
        u.username,
        u.email,
        u.createdAt,
        u.updateAt,
        r.nama_role
    FROM
        users u
    JOIN
        role r ON u.id_role = r.id_role
    WHERE
        u.id_user = '$user_id'
";

$result = mysqli_query($koneksi, $query);

if ($result) {
    if (mysqli_num_rows($result) == 1) {
        $user_data = mysqli_fetch_assoc($result);
    } else {
        // Jika data user tidak ditemukan (meskipun ID ada di sesi, ini bisa terjadi jika data dihapus)
        // Hancurkan sesi dan redirect ke login
        session_unset();
        session_destroy();
        header("Location: ../auth/login.php?error=Data pengguna tidak ditemukan.");
        exit();
    }
} else {
    // Error saat query
    echo "Error: " . mysqli_error($koneksi);
    exit(); // Hentikan eksekusi skrip jika ada error database
}

// Tutup koneksi database (opsional, jika tidak ada operasi DB lain setelah ini)
mysqli_close($koneksi);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pengguna</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            flex-direction: column;
            /* Untuk menumpuk elemen secara vertikal */
        }

        .profile-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            margin-bottom: 20px;
            /* Jarak antara container profil dan link di bawahnya */
        }

        .profile-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        .profile-info p {
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .profile-info p strong {
            display: inline-block;
            width: 120px;
            /* Lebar tetap untuk label agar rapi */
            color: #555;
        }

        .action-links {
            text-align: center;
            margin-top: 20px;
        }

        .action-links a {
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
            display: inline-block;
            /* Agar bisa berdampingan jika ada beberapa link */
            margin: 0 5px;
            /* Jarak antar tombol */
        }

        .back-link a {
            background-color: #007bff;
            color: white;
        }

        .back-link a:hover {
            background-color: #0056b3;
        }

        .logout-link a {
            background-color: #dc3545;
            /* Warna merah untuk logout */
            color: white;
        }

        .logout-link a:hover {
            background-color: #c82333;
        }
    </style>
</head>

<body>
    <div class="profile-container">
        <h2>Profil Pengguna</h2>
        <?php if ($user_data): ?>
            <div class="profile-info">
                <p><strong>Username:</strong> <?php echo htmlspecialchars($user_data['username']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user_data['email']); ?></p>
                <p><strong>Peran:</strong> <?php echo htmlspecialchars($user_data['nama_role']); ?></p>
                <p><strong>Terdaftar Sejak:</strong> <?php echo htmlspecialchars($user_data['createdAt']); ?></p>
                <p><strong>Terakhir Diperbarui:</strong> <?php echo htmlspecialchars($user_data['updateAt']); ?></p>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: red;">Maaf, data profil tidak dapat dimuat.</p>
        <?php endif; ?>
    </div>

    <div class="action-links">
        <span class="back-link">
            <a href="javascript:history.back()">Kembali</a>
        </span>
        <span class="logout-link">
            <a href="../auth/logout.php">Logout</a>
        </span>
    </div>
</body>

</html>