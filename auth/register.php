<?php
// Include file koneksi database Anda
include 'koneksi.php';

// Ambil data peran (roles) dari tabel 'role'
// Kita akan mengambil semua role, tapi mungkin hanya akan menggunakan satu role default untuk registrasi
$roles = [];
$query = "SELECT id_role, nama_role FROM role ORDER BY nama_role ASC";
$result = mysqli_query($koneksi, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $roles[] = $row;
    }
} else {
    echo "Error: " . mysqli_error($koneksi);
}

// Cari ID untuk role 'Pembeli' atau 'Penawar' sebagai default, jika ada
$default_role_id = null;
foreach ($roles as $role) {
    if (strtolower($role['nama_role']) === 'pembeli' || strtolower($role['nama_role']) === 'penawar') {
        $default_role_id = $role['id_role'];
        break;
    }
}

// Jika tidak ditemukan 'Pembeli' atau 'Penawar', Anda bisa set default ke role dengan ID tertentu atau biarkan null
if (is_null($default_role_id) && !empty($roles)) {
    // Misalnya, set default ke role pertama yang ditemukan jika tidak ada 'Pembeli'/'Penawar'
    $default_role_id = $roles[0]['id_role'];
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Baru</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .register-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 350px;
        }

        .register-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"] {
            width: calc(100% - 20px);
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .form-group input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            /* Green for register */
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .form-group input[type="submit"]:hover {
            background-color: #218838;
        }

        .message {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }

        .success-message {
            color: green;
        }

        .link-to-login {
            text-align: center;
            margin-top: 20px;
        }

        .link-to-login a {
            color: #007bff;
            text-decoration: none;
        }

        .link-to-login a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="register-container">
        <h2>Daftar Akun Baru</h2>
        <?php
        // Tampilkan pesan sukses atau error jika ada
        if (isset($_GET['success'])) {
            echo '<p class="message success-message">' . htmlspecialchars($_GET['success']) . '</p>';
        } elseif (isset($_GET['error'])) {
            echo '<p class="message">' . htmlspecialchars($_GET['error']) . '</p>';
        }
        ?>
        <form action="register_process.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <input type="hidden" name="id_role" value="<?php echo htmlspecialchars($default_role_id); ?>">

            <div class="form-group">
                <input type="submit" value="Daftar">
            </div>
        </form>
        <div class="link-to-login">
            Sudah punya akun? <a href="login.php">Login di sini</a>
        </div>
    </div>
</body>

</html>