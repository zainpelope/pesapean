<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include file koneksi database Anda
include '../koneksi.php';

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
    // Lebih baik mencatat error ke log dan menampilkan pesan user-friendly
    error_log("Database error: " . mysqli_error($koneksi));
    // Tampilkan pesan error hanya jika Anda tidak ingin redirect atau memiliki penanganan error yang lebih canggih
    // echo "Error: " . mysqli_error($koneksi); // Ini bisa dihilangkan di produksi
}

// Cari ID untuk role 'Pembeli' atau 'Penawar' sebagai default untuk registrasi
$default_role_id = null;
foreach ($roles as $role) {
    if (strtolower($role['nama_role']) === 'pembeli' || strtolower($role['nama_role']) === 'penawar') {
        $default_role_id = $role['id_role'];
        break;
    }
}

// Jika tidak ditemukan 'Pembeli' atau 'Penawar', Anda bisa set default ke role pertama yang ditemukan
// Ini adalah fallback agar selalu ada role yang terpilih
if (is_null($default_role_id) && !empty($roles)) {
    $default_role_id = $roles[0]['id_role'];
} else if (is_null($default_role_id)) {
    // Jika tidak ada role sama sekali di database, set ke nilai default yang Anda inginkan
    // Misalnya, 2 adalah ID default untuk 'Pembeli' jika Anda sudah tahu
    $default_role_id = 2; // Ganti dengan ID role default yang sesuai jika perlu
    error_log("Warning: Default roles 'Pembeli' or 'Penawar' not found. Using fallback ID: " . $default_role_id);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Baru Pesapean</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            /* Menggunakan gradient yang sama dengan halaman login */
            background: linear-gradient(135deg, #71b7e6, #9b59b6);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            color: #333;
        }

        .register-container {
            background-color: #fff;
            padding: 40px;
            /* Padding lebih besar */
            border-radius: 12px;
            /* Border radius lebih besar */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            /* Bayangan lebih modern */
            width: 380px;
            /* Sedikit lebih lebar dari login jika perlu */
            text-align: center;
        }

        .register-container h2 {
            margin-bottom: 30px;
            color: #333;
            font-weight: 600;
            font-size: 28px;
        }

        .form-group {
            margin-bottom: 20px;
            /* Spasi antar grup form lebih besar */
            text-align: left;
            position: relative;
            /* Untuk ikon mata */
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            /* Spasi label ke input lebih besar */
            color: #555;
            font-weight: 500;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            /* Padding input lebih besar */
            border: 1px solid #ddd;
            border-radius: 8px;
            /* Border radius input lebih besar */
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="email"]:focus,
        .form-group input[type="password"]:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
            outline: none;
        }

        /* Gaya untuk ikon mata password */
        .password-toggle-icon {
            position: absolute;
            right: 15px;
            top: 65%;
            /* Sesuaikan posisi vertikal ikon agar pas */
            transform: translateY(-50%);
            cursor: pointer;
            color: #888;
            transition: color 0.3s ease;
        }

        .password-toggle-icon:hover {
            color: #333;
        }

        .btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            margin-top: 10px;
        }

        .btn-primary {
            background-color: #28a745;
            /* Green for register button */
            color: white;
        }

        .btn-primary:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        .message {
            text-align: center;
            margin-bottom: 15px;
            font-size: 14px;
            padding: 10px;
            border-radius: 8px;
        }

        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }

        .success-message {
            color: #28a745;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }

        .link-to-login {
            margin-top: 25px;
            font-size: 15px;
            color: #555;
        }

        .link-to-login a {
            color: #007bff;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .link-to-login a:hover {
            color: #0056b3;
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="register-container">
        <h2>Daftar Akun Baru Pesapean</h2>
        <?php
        // Tampilkan pesan sukses atau error jika ada
        if (isset($_GET['success'])) {
            echo '<p class="message success-message">' . htmlspecialchars($_GET['success']) . '</p>';
        } elseif (isset($_GET['error'])) {
            echo '<p class="message error-message">' . htmlspecialchars($_GET['error']) . '</p>';
        }
        ?>
        <form action="register_process.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required autocomplete="email">
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required autocomplete="new-password">
                <span class="password-toggle-icon" onclick="togglePasswordVisibility('password')">
                    <i class="fas fa-eye" id="toggleEyePassword"></i>
                </span>
            </div>
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
                <span class="password-toggle-icon" onclick="togglePasswordVisibility('confirm_password')">
                    <i class="fas fa-eye" id="toggleEyeConfirmPassword"></i>
                </span>
            </div>
            <input type="hidden" name="id_role" value="<?php echo htmlspecialchars($default_role_id); ?>">

            <div class="form-group">
                <button type="submit" class="btn btn-primary">Daftar</button>
            </div>
        </form>
        <div class="link-to-login">
            Sudah punya akun? <a href="login.php">Login di sini</a>
        </div>
    </div>

    <script>
        function togglePasswordVisibility(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const toggleEye = document.getElementById('toggleEye' + (fieldId === 'password' ? 'Password' : 'ConfirmPassword'));

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleEye.classList.remove('fa-eye');
                toggleEye.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleEye.classList.remove('fa-eye-slash');
                toggleEye.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>