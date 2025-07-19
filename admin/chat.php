<?php
// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../koneksi.php'; // Pastikan path ini benar (dari admin ke root)

// --- PENTING: Cek status login dan role admin ---
if (!isset($_SESSION['id_user']) || $_SESSION['id_role'] != 1) { // Asumsi id_role 1 adalah Admin
    header("Location: ../auth/login.php?error=Anda tidak memiliki akses sebagai Admin.");
    exit();
}

$current_user_id = $_SESSION['id_user']; // ID admin yang sedang login

// Admin akan menerima 'recipient_id' (ID pembeli) dari daftar chat/pengguna
$recipient_id = isset($_GET['recipient_id']) ? (int)$_GET['recipient_id'] : 0;
$chat_type_param = 'admin_chat'; // Tetapkan langsung sebagai 'admin_chat'
$sapi_id = NULL; // Untuk chat admin, sapi_id selalu NULL

// Validasi input recipient_id
if ($recipient_id <= 0) {
    echo "<script>alert('ID Penerima (Pembeli) tidak valid.'); window.location.href='dashboard.php';</script>"; // Ganti ke halaman admin yang relevan
    exit();
}

// Ambil info lawan bicara (pembeli)
$chat_partner_username = 'Pengguna Tidak Dikenal';
$stmt_partner = mysqli_prepare($koneksi, "SELECT username FROM users WHERE id_user = ?");
mysqli_stmt_bind_param($stmt_partner, "i", $recipient_id);
mysqli_stmt_execute($stmt_partner);
$result_partner = mysqli_stmt_get_result($stmt_partner);
$partner_info = mysqli_fetch_assoc($result_partner);
if ($partner_info) {
    $chat_partner_username = $partner_info['username'];
}
mysqli_stmt_close($stmt_partner);


// Cek atau Buat Chatroom
// Pastikan user1_id selalu lebih kecil dari user2_id untuk konsistensi
$user_id_a = min($current_user_id, $recipient_id);
$user_id_b = max($current_user_id, $recipient_id);
$chatroom_id = null;

// Query untuk mencari chatroom admin
// Penting: id_sapi IS NULL untuk chat admin
$stmt_chatroom = mysqli_prepare($koneksi, "SELECT id_chatRooms FROM chatRooms
                                          WHERE user1_id = ? AND user2_id = ? AND chat_type = ? AND id_sapi IS NULL");
mysqli_stmt_bind_param($stmt_chatroom, "iis", $user_id_a, $user_id_b, $chat_type_param);
mysqli_stmt_execute($stmt_chatroom);
$result_chatroom = mysqli_stmt_get_result($stmt_chatroom);
$chatroom = mysqli_fetch_assoc($result_chatroom);

if ($chatroom) {
    $chatroom_id = $chatroom['id_chatRooms'];
} else {
    // Buat chatroom baru jika belum ada
    $stmt_insert_chatroom = mysqli_prepare($koneksi, "INSERT INTO chatRooms (user1_id, user2_id, id_sapi, chat_type, createdAt, updatedAt) VALUES (?, ?, ?, ?, NOW(), NOW())");

    // id_sapi_for_insert selalu NULL untuk chat admin
    $sapi_id_for_insert = NULL;

    // Perhatikan 's' untuk string di $chat_type_param
    mysqli_stmt_bind_param($stmt_insert_chatroom, "iiis", $user_id_a, $user_id_b, $sapi_id_for_insert, $chat_type_param);
    if (mysqli_stmt_execute($stmt_insert_chatroom)) {
        $chatroom_id = mysqli_insert_id($koneksi);
    } else {
        echo "<script>alert('Gagal membuat chatroom admin. Silakan coba lagi. Error: " . mysqli_error($koneksi) . "'); window.location.href='dashboard.php';</script>"; // Ganti ke halaman admin yang relevan
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Admin dengan <?= htmlspecialchars($chat_partner_username) ?></title>
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
            background-color: var(--light-bg);
            /* Sesuaikan variabel CSS Anda */
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
            /* Sesuaikan variabel CSS Anda */
            border-radius: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            /* Sesuaikan variabel CSS Anda */
            display: flex;
            flex-direction: column;
            height: 70vh;
        }

        .chat-header {
            background-color: var(--primary-color);
            /* Sesuaikan variabel CSS Anda */
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
            align-self: flex-end;
            border-bottom-right-radius: 2px;
        }

        .message-bubble.received {
            background-color: #e0e0e0;
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
            /* Sesuaikan variabel CSS Anda */
            background-color: var(--white-bg);
            /* Sesuaikan variabel CSS Anda */
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
                <a href="dashboard.php">Admin Panel</a>
            </div>
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="manage_users.php">Manajemen User</a></li>
                <li><a href="manage_sapi.php">Manajemen Sapi</a></li>
                <li><a href="chat_list.php">Daftar Chat</a></li>
            </ul>
            <div class="auth-links">
                <a href="../auth/logout.php" class="btn btn-danger">Logout</a>
            </div>
        </nav>
    </header>

    <div class="main-content">
        <div class="chat-container">
            <div class="chat-header">
                <a href="chat_list.php" class="text-white"><i class="fas fa-arrow-left"></i></a>
                <h4>Chat Admin dengan <?= htmlspecialchars($chat_partner_username) ?></h4>
            </div>
            <div class="chat-messages" id="chat-messages">
            </div>
            <div class="chat-input">
                <form id="chat-form">
                    <input type="hidden" name="chatroom_id" value="<?= htmlspecialchars($chatroom_id) ?>">
                    <input type="hidden" name="sender_id" value="<?= htmlspecialchars($current_user_id) ?>">
                    <input type="hidden" name="chat_type" value="<?= htmlspecialchars($chat_type_param) ?>">
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
            const chatType = $('input[name="chat_type"]').val();

            function loadMessages() {
                $.ajax({
                    url: 'fetch_messages.php', // Mengarah ke fetch_messages.php di folder admin
                    method: 'GET',
                    data: {
                        chatroom_id: chatroomId,
                        current_user_id: senderId,
                        chat_type: chatType
                    },
                    success: function(response) {
                        const currentScrollPos = chatMessages[0].scrollTop;
                        const maxScrollPos = chatMessages[0].scrollHeight - chatMessages[0].clientHeight;

                        chatMessages.html(response);

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
                    url: 'send_message.php', // Mengarah ke send_message.php di folder admin
                    method: 'POST',
                    data: {
                        chatroom_id: chatroomId,
                        sender_id: senderId,
                        message: messageText,
                        chat_type: chatType
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
            setInterval(loadMessages, 3000);
        });
    </script>
    <?php include '../footer.php'; ?>
</body>

</html>