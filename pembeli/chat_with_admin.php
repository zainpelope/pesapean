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

$chatroom_id = isset($_GET['chatroom_id']) ? (int)$_GET['chatroom_id'] : 0;
$recipient_id = isset($_GET['recipient_id']) ? (int)$_GET['recipient_id'] : 0; // Ini adalah ID Admin

// Validasi input
if ($chatroom_id <= 0 || $recipient_id <= 0) {
    echo "<script>alert('ID Chatroom atau ID Penerima tidak valid.'); window.location.href='pesan.php';</script>";
    exit();
}

// Ambil info lawan bicara (Admin)
$chat_partner_username = 'Admin';
$stmt_partner = mysqli_prepare($koneksi, "SELECT username, id_role FROM users WHERE id_user = ?");
mysqli_stmt_bind_param($stmt_partner, "i", $recipient_id);
mysqli_stmt_execute($stmt_partner);
$result_partner = mysqli_stmt_get_result($stmt_partner);
$partner_info = mysqli_fetch_assoc($result_partner);

// Dapatkan ID role Admin
$admin_role_id = 0;
$stmt_get_admin_role_id = mysqli_prepare($koneksi, "SELECT id_role FROM role WHERE nama_role = 'Admin'");
if ($stmt_get_admin_role_id) {
    mysqli_stmt_execute($stmt_get_admin_role_id);
    $result_get_admin_role_id = mysqli_stmt_get_result($stmt_get_admin_role_id);
    $admin_role_row = mysqli_fetch_assoc($result_get_admin_role_id);
    if ($admin_role_row) {
        $admin_role_id = $admin_role_row['id_role'];
    }
    mysqli_stmt_close($stmt_get_admin_role_id);
}


if ($partner_info && $partner_info['id_role'] == $admin_role_id) {
    $chat_partner_username = $partner_info['username'];
} else {
    echo "<script>alert('Lawan bicara bukan Admin yang valid.'); window.location.href='pesan.php';</script>";
    exit();
}
mysqli_stmt_close($stmt_partner);


// Pastikan chatroom_id ini memang milik user dan admin yang bersangkutan, dan bertipe admin_chat
$stmt_validate_chatroom = mysqli_prepare($koneksi, "SELECT id_chatRooms FROM chatRooms
                                                     WHERE id_chatRooms = ?
                                                     AND ((user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?))
                                                     AND chat_type = 'admin_chat'"); // Pastikan ini sesuai dengan tipe chat admin

$user_id_a = min($current_user_id, $recipient_id);
$user_id_b = max($current_user_id, $recipient_id);

mysqli_stmt_bind_param($stmt_validate_chatroom, "iiiii", $chatroom_id, $user_id_a, $user_id_b, $user_id_b, $user_id_a);
mysqli_stmt_execute($stmt_validate_chatroom);
$result_validate_chatroom = mysqli_stmt_get_result($stmt_validate_chatroom);
if (mysqli_num_rows($result_validate_chatroom) === 0) {
    echo "<script>alert('Akses chatroom tidak diizinkan atau tidak ditemukan.'); window.location.href='pesan.php';</script>";
    exit();
}
mysqli_stmt_close($stmt_validate_chatroom);

// Jika chatroom belum ada, buat (Ini mungkin sudah dilakukan di pesan.php, tapi sebagai fallback)
// Logika ini biasanya tidak perlu di sini jika alur dimulai dari 'pesan.php' atau 'data_sapi.php'
// Namun, jika ada kasus langsung akses chat_with_admin.php, maka ini penting.
$stmt_check_chatroom_exists = mysqli_prepare($koneksi, "SELECT id_chatRooms FROM chatRooms
                                                          WHERE ((user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?))
                                                          AND chat_type = 'admin_chat'
                                                          LIMIT 1");
mysqli_stmt_bind_param($stmt_check_chatroom_exists, "iiii", $user_id_a, $user_id_b, $user_id_b, $user_id_a);
mysqli_stmt_execute($stmt_check_chatroom_exists);
$result_check_chatroom_exists = mysqli_stmt_get_result($stmt_check_chatroom_exists);
$existing_chatroom = mysqli_fetch_assoc($result_check_chatroom_exists);

if (!$existing_chatroom) {
    // Buat chatroom baru jika belum ada
    $stmt_insert_chatroom = mysqli_prepare($koneksi, "INSERT INTO chatRooms (user1_id, user2_id, chat_type, createdAt, updatedAt) VALUES (?, ?, ?, NOW(), NOW())");
    $admin_chat_type = 'admin_chat';
    mysqli_stmt_bind_param($stmt_insert_chatroom, "iis", $user_id_a, $user_id_b, $admin_chat_type);
    if (mysqli_stmt_execute($stmt_insert_chatroom)) {
        $chatroom_id = mysqli_insert_id($koneksi);
    } else {
        echo "<script>alert('Gagal membuat chatroom admin. Silakan coba lagi. Error: " . mysqli_error($koneksi) . "'); window.location.href='pesan.php';</script>";
        exit();
    }
} else {
    $chatroom_id = $existing_chatroom['id_chatRooms'];
}
mysqli_stmt_close($stmt_check_chatroom_exists);

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat dengan Admin <?= htmlspecialchars($chat_partner_username) ?></title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* CSS yang sama atau disesuaikan dari chat.php */
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
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
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
                <a href="pesan.php" class="text-white"><i class="fas fa-arrow-left"></i></a>
                <h4>Chat dengan Admin <?= htmlspecialchars($chat_partner_username) ?></h4>
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
            const recipientId = <?= json_encode($recipient_id); ?>; // Ambil ID penerima (admin) dari PHP

            function loadMessages() {
                $.ajax({
                    url: 'fetch_messages.php',
                    method: 'GET',
                    data: {
                        chatroom_id: chatroomId,
                        current_user_id: senderId
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

                // --- PERUBAHAN DI SINI ---
                // Kosongkan input field SEBELUM mengirim pesan via AJAX
                // Ini akan membuat input kosong segera setelah tombol KIRIM ditekan.
                messageInput.val('');
                // --- AKHIR PERUBAHAN ---

                $.ajax({
                    url: 'send_message.php',
                    method: 'POST',
                    data: {
                        chatroom_id: chatroomId,
                        sender_id: senderId,
                        message: messageText,
                        recipient_id: recipientId
                    },
                    success: function(response) {
                        const res = JSON.parse(response);
                        if (res.status === 'success') {
                            // Jika ingin hanya mengosongkan setelah sukses, pindahkan messageInput.val(''); ke sini lagi
                            // Tapi untuk debugging, lebih baik kosongkan di awal.
                            loadMessages();
                        } else {
                            alert('Gagal mengirim pesan: ' + res.message);
                            // Jika pesan gagal dikirim, Anda mungkin ingin mengembalikan teks ke input
                            // messageInput.val(messageText); // Tambahkan ini jika ingin mengembalikan teks saat gagal
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error sending message: ", status, error);
                        alert('Terjadi kesalahan saat mengirim pesan.');
                        // Jika terjadi error, Anda mungkin ingin mengembalikan teks ke input
                        // messageInput.val(messageText); // Tambahkan ini jika ingin mengembalikan teks saat error
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