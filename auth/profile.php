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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient to bottom right, #6a11cb, #2575fc);
            /* Modern gradient background */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            flex-direction: column;
            color: #333;
        }

        .profile-container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            /* Stronger, softer shadow */
            width: 450px;
            max-width: 90%;
            /* Responsive width */
            margin-bottom: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
            /* For subtle background effects */
        }

        .profile-container::before {
            content: '';
            position: absolute;
            top: -50px;
            left: -50px;
            width: 150px;
            height: 150px;
            background: rgba(106, 17, 203, 0.05);
            /* Light accent circle */
            border-radius: 50%;
            z-index: 0;
        }

        .profile-container::after {
            content: '';
            position: absolute;
            bottom: -30px;
            right: -30px;
            width: 100px;
            height: 100px;
            background: rgba(37, 117, 252, 0.05);
            /* Light accent circle */
            border-radius: 50%;
            z-index: 0;
        }

        .profile-content {
            position: relative;
            /* To bring content above pseudo-elements */
            z-index: 1;
        }

        .profile-container h2 {
            font-size: 2.2em;
            margin-bottom: 30px;
            color: #333;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .profile-info p {
            margin-bottom: 15px;
            line-height: 1.8;
            font-size: 1.1em;
            display: flex;
            justify-content: space-between;
            /* Aligns label and value */
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            /* Subtle separator */
        }

        .profile-info p:last-child {
            border-bottom: none;
            /* No border for the last item */
        }

        .profile-info p strong {
            font-weight: 600;
            color: #555;
            flex-basis: 40%;
            /* Adjust width for labels */
            text-align: left;
        }

        .profile-info p span {
            flex-basis: 60%;
            /* Adjust width for values */
            text-align: right;
            color: #666;
        }

        .action-links {
            text-align: center;
            margin-top: 30px;
            display: flex;
            justify-content: center;
            gap: 15px;
            /* Space between buttons */
        }

        .action-links a {
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: 1em;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .back-link a {
            background-color: #2575fc;
            /* Primary blue */
            color: white;
        }

        .back-link a:hover {
            background-color: #1a5acb;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(37, 117, 252, 0.3);
        }

        .logout-link a {
            background-color: #dc3545;
            /* Red for danger/logout */
            color: white;
        }

        .logout-link a:hover {
            background-color: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(220, 53, 69, 0.3);
        }

        /* Responsive adjustments */
        @media (max-width: 600px) {
            .profile-container {
                padding: 25px;
            }

            .profile-info p {
                flex-direction: column;
                /* Stack label and value on small screens */
                align-items: flex-start;
            }

            .profile-info p strong,
            .profile-info p span {
                flex-basis: auto;
                width: 100%;
                text-align: left;
            }

            .action-links {
                flex-direction: column;
                /* Stack buttons on small screens */
            }

            .action-links a {
                width: 100%;
                margin: 5px 0;
            }
        }
    </style>
</head>

<body>
    <div class="profile-container">
        <div class="profile-content">
            <h2>Profil Pengguna</h2>
            <?php if ($user_data): ?>
                <div class="profile-info">
                    <p><strong>Username:</strong> <span><?php echo htmlspecialchars($user_data['username']); ?></span></p>
                    <p><strong>Email:</strong> <span><?php echo htmlspecialchars($user_data['email']); ?></span></p>
                    <p><strong>Peran:</strong> <span><?php echo htmlspecialchars($user_data['nama_role']); ?></span></p>
                    <p><strong>Terdaftar Sejak:</strong> <span><?php echo htmlspecialchars($user_data['createdAt']); ?></span></p>
                    <p><strong>Terakhir Diperbarui:</strong> <span><?php echo htmlspecialchars($user_data['updateAt']); ?></span></p>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: red;">Maaf, data profil tidak dapat dimuat.</p>
            <?php endif; ?>
        </div>
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