<?php
// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../koneksi.php'; // Pastikan path ini benar

// --- PENTING: Cek status login pengguna ---
if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php?error=Anda harus login untuk mengakses chat.");
    exit();
}

$current_user_id = $_SESSION['id_user']; // ID user yang sedang login

// Untuk chat admin, recipient_id akan diambil dari URL dan chat_type_param akan selalu 'admin_chat'
$recipient_id = isset($_GET['recipient_id']) ? (int)$_GET['recipient_id'] : 0;
$chat_type_param = 'admin_chat'; // Tetapkan langsung sebagai 'admin_chat'
$sapi_id = NULL; // Tetapkan sapi_id sebagai NULL untuk chat admin

// Validasi input
if ($recipient_id <= 0) {
    echo "<div class='alert alert-danger'>ID Penerima (Admin) tidak valid. Kembali ke <a href='beranda.php'>Beranda</a>.</div>";
    exit();
}

// Ambil info lawan bicara (admin)
$chat_partner_username = 'Admin'; // Default
$stmt_partner = mysqli_prepare($koneksi, "SELECT username FROM users WHERE id_user = ? AND id_role = (SELECT id_role FROM role WHERE nama_role = 'Admin')");
if ($stmt_partner === false) {
    die("<div class='alert alert-danger'>Error mempersiapkan statement partner: " . mysqli_error($koneksi) . "</div>");
}
mysqli_stmt_bind_param($stmt_partner, "i", $recipient_id);
mysqli_stmt_execute($stmt_partner);
$result_partner = mysqli_stmt_get_result($stmt_partner);
$partner_info = mysqli_fetch_assoc($result_partner);
if ($partner_info) {
    $chat_partner_username = $partner_info['username'];
} else {
    // Jika recipient_id bukan admin atau tidak ditemukan
    echo "<div class='alert alert-danger'>Penerima bukan Admin atau tidak ditemukan. Kembali ke <a href='beranda.php'>Beranda</a>.</div>";
    exit();
}
mysqli_stmt_close($stmt_partner);


// Cek atau Buat Chatroom
// Pastikan user1_id selalu lebih kecil dari user2_id untuk konsistensi
$user_id_a = min($current_user_id, $recipient_id);
$user_id_b = max($current_user_id, $recipient_id);
$chatroom_id = null;

// Query untuk mencari chatroom admin
$stmt_chatroom = mysqli_prepare($koneksi, "SELECT id_chatRooms FROM chatRooms
                                          WHERE user1_id = ? AND user2_id = ? AND chat_type = 'admin_chat' AND id_sapi IS NULL");
if ($stmt_chatroom === false) {
    die("<div class='alert alert-danger'>Error mempersiapkan statement chatroom: " . mysqli_error($koneksi) . "</div>");
}
mysqli_stmt_bind_param($stmt_chatroom, "ii", $user_id_a, $user_id_b);
mysqli_stmt_execute($stmt_chatroom);
$result_chatroom = mysqli_stmt_get_result($stmt_chatroom);
$chatroom = mysqli_fetch_assoc($result_chatroom);

if ($chatroom) {
    $chatroom_id = $chatroom['id_chatRooms'];
} else {
    // Buat chatroom baru jika belum ada
    $stmt_insert_chatroom = mysqli_prepare($koneksi, "INSERT INTO chatRooms (user1_id, user2_id, id_sapi, chat_type, createdAt, updatedAt) VALUES (?, ?, ?, ?, NOW(), NOW())");
    if ($stmt_insert_chatroom === false) {
        die("<div class='alert alert-danger'>Error mempersiapkan statement insert chatroom: " . mysqli_error($koneksi) . "</div>");
    }

    // id_sapi_for_insert selalu NULL untuk chat admin
    $sapi_id_for_insert = NULL;

    mysqli_stmt_bind_param($stmt_insert_chatroom, "iiis", $user_id_a, $user_id_b, $sapi_id_for_insert, $chat_type_param);
    if (mysqli_stmt_execute($stmt_insert_chatroom)) {
        $chatroom_id = mysqli_insert_id($koneksi);
    } else {
        // --- INI BAGIAN PENTING UNTUK MENGAMBIL ERROR MYSQL YANG SEBENARNYA ---
        echo "<div class='alert alert-danger'>Gagal membuat chatroom admin. Silakan coba lagi. Pesan Error MySQL: " . mysqli_error($koneksi) . " Kembali ke <a href='beranda.php'>Beranda</a>.</div>";
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
            border-radius: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            height: 70vh;
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
            background-color: var(--white-bg);
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
                    echo '<a href="../auth/profile.php" class="btn btn-primary">Profile</a>';
                } else {
                    echo '<a href="../auth/login.php" class="btn btn-primary">Login</a>';
                    echo '<a href="../auth/register.php" class="btn btn-outline-primary">Daftar</a>';
                }
                ?>
            </div>
        </nav>
    </header>

    <div class="main-content">
        <div class="chat-container">
            <div class="chat-header">
                <a href="pesan.php" class="text-white"><i class="fas fa-arrow-left"></i></a>
                <h4>Chat dengan <?= htmlspecialchars($chat_partner_username) ?> <small>(Admin)</small></h4>
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
            const chatType = $('input[name="chat_type"]').val(); // Ambil chat_type dari hidden input

            function loadMessages() {
                $.ajax({
                    url: 'fetch_messages.php', // Path relatif di folder pembeli
                    method: 'GET',
                    data: {
                        chatroom_id: chatroomId,
                        current_user_id: senderId,
                        // recipient_id tidak perlu dikirim ke fetch_messages lagi jika sudah ada chatroom_id
                        chat_type: chatType // Kirim chat_type ke fetch_messages
                    },
                    success: function(response) {
                        const currentScrollPos = chatMessages[0].scrollTop;
                        const maxScrollPos = chatMessages[0].scrollHeight - chatMessages[0].clientHeight;

                        chatMessages.html(response);

                        // Hanya scroll ke bawah jika sudah di paling bawah atau chat baru dimulai
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
                    url: 'send_message.php', // Path relatif di folder pembeli
                    method: 'POST',
                    data: {
                        chatroom_id: chatroomId,
                        sender_id: senderId,
                        message: messageText,
                        // recipient_id tidak perlu dikirim ke send_message lagi jika sudah ada chatroom_id
                        chat_type: chatType // Kirim chat_type ke send_message
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

            loadMessages(); // Muat pesan saat halaman pertama kali dibuka
            setInterval(loadMessages, 3000); // Muat pesan setiap 3 detik
        });
    </script>
    <?php include '../footer.php'; ?>
</body>

</html>