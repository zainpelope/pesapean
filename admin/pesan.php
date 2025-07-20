<?php
// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../koneksi.php'; // Pastikan path ini benar

// --- PENTING: Cek status login dan peran pengguna ---
if (!isset($_SESSION['id_user']) || $_SESSION['nama_role'] !== 'Admin') {
    header("Location: ../auth/login.php?error=Akses tidak diizinkan. Anda harus login sebagai Admin.");
    exit();
}

$current_user_id = $_SESSION['id_user']; // ID admin yang sedang login

// Query untuk mengambil semua chatroom di mana admin ini adalah salah satu pesertanya
$query = "SELECT
            cr.id_chatRooms,
            cr.id_sapi,
            cr.user1_id,
            cr.user2_id,
            cr.chat_type,
            ds.jenis_kelamin AS sapi_jenis_kelamin,
            ms.name AS sapi_jenis_sapi,
            CASE
                WHEN cr.user1_id = ? THEN u2.username
                ELSE u1.username
            END AS chat_partner_username,
            CASE
                WHEN cr.user1_id = ? THEN cr.user2_id
                ELSE cr.user1_id
            END AS chat_partner_id,
            (SELECT pesan FROM chatMessage WHERE id_chatRooms = cr.id_chatRooms ORDER BY waktu_kirim DESC LIMIT 1) AS last_message,
            (SELECT waktu_kirim FROM chatMessage WHERE id_chatRooms = cr.id_chatRooms ORDER BY waktu_kirim DESC LIMIT 1) AS last_message_time
          FROM
            chatRooms cr
          LEFT JOIN
            data_sapi ds ON cr.id_sapi = ds.id_sapi
          LEFT JOIN
            macamSapi ms ON ds.id_macamSapi = ms.id_macamSapi
          LEFT JOIN
            users u1 ON cr.user1_id = u1.id_user
          LEFT JOIN
            users u2 ON cr.user2_id = u2.id_user
          WHERE
            cr.user1_id = ? OR cr.user2_id = ?
          ORDER BY
            last_message_time DESC, cr.updatedAt DESC";

$stmt = mysqli_prepare($koneksi, $query);
if (!$stmt) {
    die("Error prepared statement: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "iiii", $current_user_id, $current_user_id, $current_user_id, $current_user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    die("Error fetching chatrooms: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesapean - Pesan Admin</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* style.css */

        /* --- Variabel CSS Global --- */
        :root {
            --primary-color: rgb(240, 161, 44);
            --secondary-color: rgb(48, 52, 56);
            --tertiary-color: #6c757d;
            --dark-color: #333;
            --dark-text: #212529;
            --light-bg: #f8f9fa;
            --white-bg: #ffffff;
            --border-color: #dee2e6;
            --box-shadow-light: 0 4px 15px rgba(0, 0, 0, 0.08);
            --box-shadow-medium: 0 8px 25px rgba(0, 0, 0, 0.15);
            --border-radius-sm: 8px;
            --border-radius-md: 10px;
            --border-radius-lg: 12px;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: var(--light-bg);
            font-family: 'Open Sans', sans-serif;
            /* Pastikan font ini ada atau ganti dengan font yang Anda punya */
            color: var(--dark-text);
            line-height: 1.6;
        }

        /* --- Header & Navbar --- */
        .main-header {
            background-color: black;
            /* Latar belakang navbar menjadi hitam */
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .navbar .logo a {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            /* Warna logo tetap oranye */
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .nav-links {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            gap: 1.5rem;
        }

        .nav-links li a {
            text-decoration: none;
            color: white;
            /* Warna teks link navigasi menjadi putih */
            font-weight: 600;
            padding: 0.5rem 0;
            transition: color 0.3s ease, border-bottom 0.3s ease;
        }

        .nav-links li a:hover,
        .nav-links li a.active {
            color: var(--primary-color);
            /* Warna hover tetap oranye */
            border-bottom: 2px solid var(--primary-color);
        }

        .auth-links {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .auth-links .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .auth-links .btn-primary {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }

        .auth-links .btn-outline-primary {
            background-color: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }

        /* --- Main Content --- */
        .main-content {
            flex: 1;
            padding: 30px 0;
        }

        .messages-container {
            max-width: 900px;
            margin: 0 auto;
            background-color: var(--white-bg);
            border-radius: 1rem;
            box-shadow: var(--box-shadow-light);
            padding: 20px;
        }

        .messages-container h2 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 25px;
            text-align: center;
        }

        .chat-list-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            text-decoration: none;
            color: var(--dark-text);
            transition: background-color 0.2s ease;
        }

        .chat-list-item:hover {
            background-color: #f0f0f0;
            border-radius: 0.5rem;
        }

        .chat-list-item:last-child {
            border-bottom: none;
        }

        .chat-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5rem;
            font-weight: 600;
            flex-shrink: 0;
            margin-right: 15px;
        }

        .chat-details {
            flex-grow: 1;
        }

        .chat-details h5 {
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--secondary-color);
        }

        .chat-details p {
            margin-bottom: 0;
            font-size: 0.9rem;
            color: var(--tertiary-color);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 90%;
        }

        .chat-time {
            font-size: 0.8em;
            color: var(--tertiary-color);
            white-space: nowrap;
        }

        /* --- Footer (tambahan, jika belum ada di style.css) --- */
        .footer {
            background-color: var(--secondary-color);
            /* Warna latar belakang footer */
            color: white;
            padding: 2rem 0;
            text-align: center;
            margin-top: auto;
            /* Memastikan footer selalu di bagian bawah */
        }

        .footer .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .footer p {
            margin: 0;
            font-size: 0.9rem;
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0.5rem 0 0;
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .footer-links li a {
            color: white;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .footer-links li a:hover {
            color: var(--primary-color);
        }

        /* --- Responsif (Contoh sederhana) --- */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 1rem;
            }

            .nav-links {
                flex-direction: column;
                gap: 0.5rem;
                align-items: center;
            }

            .auth-links {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>

<body>

    <header class="main-header">
        <nav class="navbar">
            <div class="logo">
                <a href="../admin/admin.php">Pesapean</a>
            </div>
            <ul class="nav-links">
                <li><a href="../admin/admin.php">Beranda</a></li>
                <li><a href="../admin/data_sapi.php?jenis=sonok">Data Sapi</a></li>
                <li><a href="../admin/lelang.php">Lelang</a></li>
                <li><a href="../admin/data_user.php">Data User</a></li>
                <li><a href="../admin/pesan.php">Pesan</a></li>
            </ul>
            <div class="auth-links">
                <?php if (isset($_SESSION['id_user'])): ?>
                    <a href="../auth/profile.php" class="btn btn-primary">Profile</a>
                <?php else: ?>
                    <a href="../auth/login.php" class="btn btn-primary">Login</a>
                    <a href="../auth/register.php" class="btn btn-outline-primary">Daftar</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <div class="main-content">
        <div class="container">
            <div class="messages-container">
                <h2><i class="fas fa-envelope me-2"></i> Pesan Anda</h2>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($chat = mysqli_fetch_assoc($result)): ?>
                        <?php
                        $chat_partner_id_for_link = ($chat['user1_id'] == $current_user_id) ? $chat['user2_id'] : $chat['user1_id'];

                        // Tentukan teks tambahan berdasarkan jenis chat
                        $chat_identifier = '';
                        if ($chat['chat_type'] == 'sapi_chat' && !empty($chat['id_sapi'])) {
                            $sapi_display = ($chat['sapi_jenis_sapi'] ?? 'Sapi') . ' - ' . ($chat['sapi_jenis_kelamin'] ?? '');
                            $chat_identifier = ' (Sapi: ' . htmlspecialchars($sapi_display) . ')';
                        } else if ($chat['chat_type'] == 'admin_chat') {
                            $chat_identifier = '';
                        }
                        ?>
                        <a href="chat_admin.php?sapi_id=<?= htmlspecialchars($chat['id_sapi'] ?? '0') ?>&recipient_id=<?= htmlspecialchars($chat_partner_id_for_link) ?>&chat_type=<?= htmlspecialchars($chat['chat_type']) ?>" class="chat-list-item">
                            <div class="chat-avatar">
                                <?= strtoupper(substr($chat['chat_partner_username'], 0, 1)) ?>
                            </div>
                            <div class="chat-details">
                                <h5><?= htmlspecialchars($chat['chat_partner_username']) ?> <small class="text-muted"><?= $chat_identifier ?></small></h5>
                                <p><?= htmlspecialchars($chat['last_message'] ?? 'Belum ada pesan.') ?></p>
                            </div>
                            <div class="chat-time">
                                <?php
                                if ($chat['last_message_time']) {
                                    echo date('H:i', strtotime($chat['last_message_time']));
                                }
                                ?>
                            </div>
                        </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="alert alert-info text-center" role="alert">
                        <i class="fas fa-info-circle me-2"></i> Belum ada percakapan untuk Anda sebagai Admin.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include '../footer.php'; ?>
</body>

</html>