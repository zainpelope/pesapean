<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include '../koneksi.php'; // Pastikan path ini benar

// Pastikan user sudah login
if (!isset($_SESSION['id_user'])) {
    header('Location: ../login.php');
    exit();
}

$current_user_id = $_SESSION['id_user']; // ID user yang sedang login

$sapi_id = isset($_GET['sapi_id']) ? (int)$_GET['sapi_id'] : 0;
$recipient_id = isset($_GET['recipient_id']) ? (int)$_GET['recipient_id'] : 0; // ID penjual

// Validasi input
if ($sapi_id <= 0 || $recipient_id <= 0) {
    echo "<script>alert('ID Sapi atau ID Penerima tidak valid.'); window.location.href='data_sapi.php';</script>";
    exit();
}

// Ambil info lawan bicara (penjual)
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
    // Buat chatroom baru jika belum ada
    $stmt_insert_chatroom = mysqli_prepare($koneksi, "INSERT INTO chatRooms (user1_id, user2_id, id_sapi, chat_type, createdAt, updatedAt) VALUES (?, ?, ?, ?, NOW(), NOW())");
    mysqli_stmt_bind_param($stmt_insert_chatroom, "iiis", $user_id_a, $user_id_b, $sapi_id, $chat_type);
    if (mysqli_stmt_execute($stmt_insert_chatroom)) {
        $chatroom_id = mysqli_insert_id($koneksi);
    } else {
        echo "<script>alert('Gagal membuat chatroom. Silakan coba lagi. Error: " . mysqli_error($koneksi) . "'); window.location.href='data_sapi.php';</script>";
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
        /* CSS yang sama seperti sebelumnya, pastikan warna-warna (primary-color, light-color, dll) sudah didefinisikan di style.css atau di sini */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: var(--light-color);
        }

        .main-content {
            flex: 1;
            padding-top: 20px;
            padding-bottom: 20px;
        }

        .chat-container {
            max-width: 800px;
            margin: 20px auto;
            background-color: var(--white-color);
            border-radius: 1rem;
            box-shadow: 0 4px 15px var(--shadow-color);
            display: flex;
            flex-direction: column;
            height: 70vh;
            /* Tinggi tetap untuk chat */
        }

        .chat-header {
            background-color: var(--primary-color);
            color: var(--white-color);
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
            background-color: var(--white-color);
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
                <a href="../pembeli/beranda.php">Pesapean</a>
            </div>
            <ul class="nav-links">
                <li><a href="../pembeli/beranda.php">Beranda</a></li>
                <li><a href="../pembeli/peta.php">Peta Interaktif</a></li>
                <li><a href="../pembeli/data_sapi.php?jenis=sonok">Data Sapi</a></li>
                <li><a href="../pembeli/lelang.php">Lelang</a></li>
                <li><a href="../pembeli/pesan.php">Pesan</a></li>
            </ul>
            <div class="auth-links">
                <?php
                if (isset($_SESSION['id_user'])) {
                    echo '<a href="../profile.php" class="btn btn-primary">Profile</a>';
                } else {
                    echo '<a href="../login.php" class="btn btn-primary">Login</a>';
                    echo '<a href="../register.php" class="btn btn-outline-primary">Daftar</a>';
                }
                ?>
            </div>
        </nav>
    </header>

    <div class="main-content">
        <div class="chat-container">
            <div class="chat-header">
                <a href="data_sapi.php" class="text-white"><i class="fas fa-arrow-left"></i></a>
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
                    url: 'fetch_messages.php',
                    method: 'GET',
                    data: {
                        chatroom_id: chatroomId,
                        current_user_id: senderId, // Kirim ID pengguna saat ini
                        recipient_id: <?= json_encode($recipient_id) ?> // Kirim ID penerima (penjual)
                    },
                    success: function(response) {
                        const currentScrollPos = chatMessages[0].scrollTop;
                        const maxScrollPos = chatMessages[0].scrollHeight - chatMessages[0].clientHeight;

                        chatMessages.html(response);

                        // Hanya gulir ke bawah jika sudah di paling bawah atau jika baru dimuat
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
                e.preventDefault();

                const messageText = messageInput.val().trim();
                if (messageText === '') {
                    return;
                }

                $.ajax({
                    url: 'send_message.php',
                    method: 'POST',
                    data: {
                        chatroom_id: chatroomId,
                        sender_id: senderId,
                        message: messageText,
                        recipient_id: <?= json_encode($recipient_id) ?> // Kirim juga recipient_id untuk validasi di backend
                    },
                    success: function(response) {
                        const res = JSON.parse(response);
                        if (res.status === 'success') {
                            messageInput.val('');
                            loadMessages();
                        } else {
                            alert('Gagal mengirim pesan: ' + res.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error sending message: ", status, error);
                        alert('Terjadi kesalahan saat mengirim pesan.');
                    }
                });
            });

            loadMessages();
            setInterval(loadMessages, 3000); // Perbarui pesan setiap 3 detik
        });
    </script>
    <?php include '../footer.php'; ?>
</body>

</html>