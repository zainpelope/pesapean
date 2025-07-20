<?php
ini_set('display_errors', 1); // Enable error display for debugging
ini_set('display_startup_errors', 1); // Enable startup error display
error_reporting(E_ALL); // Report all errors

session_start();
include '../koneksi.php'; // Pastikan path ini benar (naik satu level ke folder utama)

// --- PENTING: Cek status login dan peran pengguna ---
if (!isset($_SESSION['id_user']) || $_SESSION['nama_role'] !== 'Penjual') {
    header("Location: ../auth/login.php?error=Akses tidak diizinkan. Anda harus login sebagai Penjual.");
    exit();
}

$current_user_id = $_SESSION['id_user']; // ID user penjual yang sedang login

$sapi_id = isset($_GET['sapi_id']) ? (int)$_GET['sapi_id'] : 0;
$recipient_id = isset($_GET['recipient_id']) ? (int)$_GET['recipient_id'] : 0; // ID pembeli

// Validasi input
if ($sapi_id <= 0 || $recipient_id <= 0) {
    // Removed alert, redirecting directly
    header('Location: pesan.php');
    exit();
}

// Ambil info lawan bicara (pembeli)
$chat_partner_username = 'Pengguna';
$stmt_partner = mysqli_prepare($koneksi, "SELECT username FROM users WHERE id_user = ?");
mysqli_stmt_bind_param($stmt_partner, "i", $recipient_id);
mysqli_stmt_execute($stmt_partner);
$result_partner = mysqli_stmt_get_result($stmt_partner);
$partner_info = mysqli_fetch_assoc($result_partner);
if ($partner_info) {
    $chat_partner_username = $partner_info['username'];
}
mysqli_stmt_close($stmt_partner);

// Ambil info sapi
$sapi_nama_display = 'Tidak Tersedia';
$stmt_sapi = mysqli_prepare($koneksi, "SELECT ds.jenis_kelamin, ms.name AS jenis_sapi
                                         FROM data_sapi ds
                                         LEFT JOIN macamsapi ms ON ds.id_macamSapi = ms.id_macamSapi
                                         WHERE ds.id_sapi = ?");
mysqli_stmt_bind_param($stmt_sapi, "i", $sapi_id);
mysqli_stmt_execute($stmt_sapi);
$result_sapi = mysqli_stmt_get_result($stmt_sapi);
$sapi_info = mysqli_fetch_assoc($result_sapi);
if ($sapi_info) {
    $sapi_nama_display = ($sapi_info['jenis_sapi'] ?? 'Sapi') . ' - ' . ($sapi_info['jenis_kelamin'] ?? '');
}
mysqli_stmt_close($stmt_sapi);


// Cek atau Buat Chatroom
// Kita perlu memastikan user1_id selalu lebih kecil dari user2_id untuk menghindari duplikasi chatroom
// (misal: user A-B sama dengan user B-A)
$user_id_a = min($current_user_id, $recipient_id);
$user_id_b = max($current_user_id, $recipient_id);
$chat_type = 'sapi_chat'; // Karena ini chat sapi

$stmt_chatroom = mysqli_prepare($koneksi, "SELECT id_chatRooms FROM chatRooms
                                           WHERE user1_id = ? AND user2_id = ? AND id_sapi = ? AND chat_type = ?");

mysqli_stmt_bind_param($stmt_chatroom, "iiis", $user_id_a, $user_id_b, $sapi_id, $chat_type);
mysqli_stmt_execute($stmt_chatroom);
$result_chatroom = mysqli_stmt_get_result($stmt_chatroom);
$chatroom = mysqli_fetch_assoc($result_chatroom);

$chatroom_id = null;
if ($chatroom) {
    $chatroom_id = $chatroom['id_chatRooms'];
} else {
    // Ini seharusnya jarang terjadi jika chatroom sudah dibuat dari sisi pembeli
    // Tapi sebagai fallback, buat chatroom baru jika belum ada
    $stmt_insert_chatroom = mysqli_prepare($koneksi, "INSERT INTO chatRooms (user1_id, user2_id, id_sapi, chat_type, createdAt, updatedAt) VALUES (?, ?, ?, ?, NOW(), NOW())");
    mysqli_stmt_bind_param($stmt_insert_chatroom, "iiis", $user_id_a, $user_id_b, $sapi_id, $chat_type);
    if (mysqli_stmt_execute($stmt_insert_chatroom)) {
        $chatroom_id = mysqli_insert_id($koneksi);
    } else {
        // Removed alert, redirecting directly
        header('Location: pesan.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat dengan <?= htmlspecialchars($chat_partner_username) ?></title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* CSS yang sama seperti sebelumnya untuk chat container */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: var(--light-bg);
            /* Menggunakan variabel CSS */
        }

        .main-content {
            flex: 1;
            padding-top: 20px;
            padding-bottom: 20px;
        }

        .chat-container {
            max-width: 800px;
            margin: 20px auto;
            background-color: var(--white-bg);
            /* Menggunakan variabel CSS */
            border-radius: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            /* Menggunakan rgba langsung */
            display: flex;
            flex-direction: column;
            height: 70vh;
            /* Tinggi tetap untuk chat */
        }

        .chat-header {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem 1.5rem;
            border-top-left-radius: 1rem;
            border-top-right-radius: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chat-header h4 {
            margin-bottom: 0;
            font-weight: 600;
        }

        .chat-messages {
            flex: 1;
            padding: 1.5rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .message-bubble {
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 15px;
            margin-bottom: 10px;
            word-wrap: break-word;
        }

        .message-bubble.sent {
            background-color: #dcf8c6;
            /* Light green for sent messages */
            align-self: flex-end;
            border-bottom-right-radius: 2px;
        }

        .message-bubble.received {
            background-color: #e0e0e0;
            /* Light grey for received messages */
            align-self: flex-start;
            border-bottom-left-radius: 2px;
        }

        .message-bubble small {
            display: block;
            margin-top: 5px;
            font-size: 0.75em;
            color: #666;
            text-align: right;
        }

        .message-bubble.received small {
            text-align: left;
        }

        .chat-input {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border-color);
            background-color: var(--white-bg);
            /* Menggunakan variabel CSS */
            border-bottom-left-radius: 1rem;
            border-bottom-right-radius: 1rem;
        }

        .chat-input form {
            display: flex;
            gap: 10px;
        }

        .chat-input .form-control {
            flex: 1;
            border-radius: 0.5rem;
        }

        .chat-input .btn {
            border-radius: 0.5rem;
        }

        /* Custom scrollbar */
        .chat-messages::-webkit-scrollbar {
            width: 8px;
        }

        .chat-messages::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .chat-messages::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>

<body>

    <header class="main-header">
        <nav class="navbar">
            <div class="logo">
                <a href="beranda.php">Pesapean</a>
            </div>
            <ul class="nav-links">
                <li><a href="beranda.php">Beranda</a></li>
                <li><a href="peta.php">Peta Interaktif</a></li>
                <li><a href="data_sapi.php?jenis=sonok">Data Sapi</a></li>
                <li><a href="lelang.php">Lelang</a></li>
                <li><a href="pesan.php">Pesan</a></li>
            </ul>
            <div class="auth-links">
                <?php
                if (isset($_SESSION['id_user'])) {
                    echo '<a href="../auth/profile.php" class="btn btn-primary">Profile</a>'; // Path ke profil, naik satu level
                } else {
                    echo '<a href="../auth/login.php" class="btn btn-primary">Login</a>'; // Path ke login, naik satu level
                    echo '<a href="../auth/register.php" class="btn btn-outline-primary">Daftar</a>'; // Path ke daftar, naik satu level
                }
                ?>
            </div>
        </nav>
    </header>

    <div class="main-content">
        <div class="chat-container">
            <div class="chat-header">
                <a href="pesan.php" class="text-white"><i class="fas fa-arrow-left"></i></a>
                <h4>Chat dengan <?= htmlspecialchars($chat_partner_username) ?></h4>
                <small class="ms-auto">Sapi: <?= htmlspecialchars($sapi_nama_display) ?></small>
            </div>
            <div class="chat-messages" id="chat-messages">
            </div>
            <div class="chat-input">
                <form id="chat-form">
                    <input type="hidden" name="chatroom_id" value="<?= htmlspecialchars($chatroom_id) ?>">
                    <input type="hidden" name="sender_id" value="<?= htmlspecialchars($current_user_id) ?>">
                    <input type="text" name="message" id="message-input" class="form-control" placeholder="Ketik pesan Anda..." autocomplete="off">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Kirim</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            const chatMessages = $('#chat-messages');
            const chatForm = $('#chat-form');
            const messageInput = $('#message-input');
            const chatroomId = $('input[name="chatroom_id"]').val();
            const senderId = $('input[name="sender_id"]').val();

            function loadMessages() {
                $.ajax({
                    url: 'fetch_messages.php', // Path relatif di folder penjual
                    method: 'GET',
                    data: {
                        chatroom_id: chatroomId,
                        current_user_id: senderId,
                        recipient_id: <?= json_encode($recipient_id) ?>
                    },
                    success: function(response) {
                        const currentScrollPos = chatMessages[0].scrollTop;
                        const maxScrollPos = chatMessages[0].scrollHeight - chatMessages[0].clientHeight;

                        chatMessages.html(response);

                        // Scroll to bottom if user is near the bottom or at the very top (first load)
                        if (currentScrollPos >= maxScrollPos - 20 || maxScrollPos === 0) {
                            chatMessages.scrollTop(chatMessages[0].scrollHeight);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error loading messages: ", status, error);
                    }
                });
            }

            chatForm.on('submit', function(e) {
                e.preventDefault(); // Mencegah submit form standar yang akan merefresh halaman

                const messageText = messageInput.val().trim();
                if (messageText === '') {
                    return; // Jangan kirim pesan kosong
                }

                // --- KOSONGKAN INPUT FIELD SEGERA SETELAH TOMBOL KIRIM DITEKAN ---
                messageInput.val('');

                $.ajax({
                    url: 'send_message.php', // Path relatif di folder penjual
                    method: 'POST',
                    data: {
                        chatroom_id: chatroomId,
                        sender_id: senderId,
                        message: messageText,
                        recipient_id: <?= json_encode($recipient_id) ?> // Pastikan recipient_id juga dikirim
                    },
                    success: function(response) {
                        try {
                            const res = JSON.parse(response);
                            if (res.status === 'success') {
                                loadMessages(); // Refresh messages to show the new one
                            } else {
                                console.error('Gagal mengirim pesan: ' + res.message); // Log error to console
                                // Optional: You could display a non-intrusive message here if needed
                            }
                        } catch (e) {
                            console.error("Error parsing JSON response: ", e);
                            console.error("Response was: ", response);
                            // Optional: You could display a non-intrusive message here if needed
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error sending message: ", status, error); // Log error to console
                        // Optional: You could display a non-intrusive message here if needed
                    }
                });
            });

            // Muat pesan saat halaman pertama kali dibuka
            loadMessages();
            // Atur interval untuk memuat pesan secara berkala
            setInterval(loadMessages, 3000); // Perbarui pesan setiap 3 detik
        });
    </script>
    <?php include '../footer.php'; ?>
</body>

</html>