<?php
session_start();
include '../koneksi.php'; // Sesuaikan path ini jika file koneksi.php berada di lokasi berbeda

// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Periksa apakah admin sudah login dan memiliki peran 'Admin'
if (!isset($_SESSION['id_user']) || $_SESSION['id_role'] != 1) { // Asumsi id_role = 1 untuk Admin
    header("Location: ../auth/login.php"); // Redirect ke halaman login jika tidak berwenang
    exit();
}

$current_admin_id = $_SESSION['id_user'];
$selected_recipient_id = null; // ID user yang sedang di-chat admin (ini adalah chat_partner_id dari pesan.php)
$selected_sapi_id = null; // ID sapi jika ini adalah chat terkait sapi
$selected_chat_type = null; // Tipe chat (admin_chat atau sapi_chat)
$id_chat_room = null; // ID chat room yang ditemukan atau dibuat

// --- TANGANI PARAMETER DARI URL (dari pesan.php) ---
if (isset($_GET['recipient_id']) && isset($_GET['chat_type'])) {
    $selected_recipient_id = mysqli_real_escape_string($koneksi, $_GET['recipient_id']);
    $selected_chat_type = mysqli_real_escape_string($koneksi, $_GET['chat_type']);
    $selected_sapi_id = isset($_GET['sapi_id']) ? mysqli_real_escape_string($koneksi, $_GET['sapi_id']) : null;

    // Jika sapi_id adalah '0', ubah menjadi NULL untuk query database
    if ($selected_sapi_id == '0') {
        $selected_sapi_id = null;
    }

    // Cari atau buat chat room berdasarkan recipient_id, current_admin_id, sapi_id, dan chat_type
    $query_find_chatroom = "SELECT id_chatRooms FROM chatrooms
                            WHERE (user1_id = $current_admin_id AND user2_id = $selected_recipient_id)
                            OR (user1_id = $selected_recipient_id AND user2_id = $current_admin_id)
                            AND chat_type = '$selected_chat_type'";

    // Tambahkan kondisi id_sapi jika chat_type adalah 'sapi_chat'
    if ($selected_chat_type == 'sapi_chat') {
        if ($selected_sapi_id !== null) {
            $query_find_chatroom .= " AND id_sapi = $selected_sapi_id";
        } else {
            $query_find_chatroom .= " AND id_sapi IS NULL";
        }
    } else { // Jika admin_chat, pastikan id_sapi adalah NULL
        $query_find_chatroom .= " AND id_sapi IS NULL";
    }

    $result_find_chatroom = mysqli_query($koneksi, $query_find_chatroom);

    if ($result_find_chatroom && mysqli_num_rows($result_find_chatroom) > 0) {
        $row_chatroom = mysqli_fetch_assoc($result_find_chatroom);
        $id_chat_room = $row_chatroom['id_chatRooms'];
    } else {
        echo "<script>console.error('Chat room not found for selected parameters. ID Admin: $current_admin_id, Recipient ID: $selected_recipient_id, Sapi ID: $selected_sapi_id, Chat Type: $selected_chat_type');</script>";
    }
}

// Ambil semua chat room di mana admin terlibat, dan kelompokkan berdasarkan pengguna lain
$chat_rooms_overview = [];
$query_chat_rooms_overview = "
    SELECT
        cr.id_chatRooms,
        cr.id_sapi,
        cr.chat_type,
        ds.jenis_kelamin AS sapi_jenis_kelamin,
        ms.name AS sapi_jenis_sapi,
        CASE
            WHEN cr.user1_id = ? THEN u2.id_user
            ELSE u1.id_user
        END AS other_user_id,
        CASE
            WHEN cr.user1_id = ? THEN u2.username
            ELSE u1.username
        END AS other_username,
        (SELECT pesan FROM chatMessage WHERE id_chatRooms = cr.id_chatRooms ORDER BY waktu_kirim DESC LIMIT 1) AS last_message,
        (SELECT waktu_kirim FROM chatMessage WHERE id_chatRooms = cr.id_chatRooms ORDER BY waktu_kirim DESC LIMIT 1) AS last_message_time,
        (SELECT sender_id FROM chatMessage WHERE id_chatRooms = cr.id_chatRooms ORDER BY waktu_kirim DESC LIMIT 1) AS last_sender_id
    FROM
        chatrooms cr
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
        last_message_time DESC, cr.updatedAt DESC;
";

$stmt_overview = mysqli_prepare($koneksi, $query_chat_rooms_overview);
if (!$stmt_overview) {
    die("Error prepared statement for overview: " . mysqli_error($koneksi));
}
mysqli_stmt_bind_param($stmt_overview, "iiii", $current_admin_id, $current_admin_id, $current_admin_id, $current_admin_id);
mysqli_stmt_execute($stmt_overview);
$result_chat_rooms_overview = mysqli_stmt_get_result($stmt_overview);

if ($result_chat_rooms_overview) {
    while ($row = mysqli_fetch_assoc($result_chat_rooms_overview)) {
        $chat_rooms_overview[] = $row;
    }
} else {
    echo "Error fetching chat room overview: " . mysqli_error($koneksi);
}


// Tangani pengiriman pesan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message_content']) && isset($_POST['selected_room_id'])) {
    $message_content = mysqli_real_escape_string($koneksi, $_POST['message_content']);
    $post_chat_room_id = mysqli_real_escape_string($koneksi, $_POST['selected_room_id']);
    $post_recipient_id = mysqli_real_escape_string($koneksi, $_POST['selected_recipient_id_for_redirect']);
    $post_sapi_id = mysqli_real_escape_string($koneksi, $_POST['selected_sapi_id_for_redirect']);
    $post_chat_type = mysqli_real_escape_string($koneksi, $_POST['selected_chat_type_for_redirect']);

    if (!empty($message_content) && $post_chat_room_id) {
        $insert_message_query = "INSERT INTO chatmessage (id_chatRooms, sender_id, pesan, waktu_kirim)
                                 VALUES ($post_chat_room_id, $current_admin_id, '$message_content', NOW())";
        if (!mysqli_query($koneksi, $insert_message_query)) {
            echo "Error sending message: " . mysqli_error($koneksi);
        }
    }
    // Redirect kembali ke chat room yang sama untuk mencegah pengiriman ulang form
    header("Location: chat_admin.php?sapi_id=$post_sapi_id&recipient_id=$post_recipient_id&chat_type=$post_chat_type");
    exit();
}

// Ambil pesan chat untuk chat room yang dipilih
$messages = [];
if ($id_chat_room) {
    $query_messages = "SELECT cm.pesan, cm.waktu_kirim, u.username AS sender_username, u.id_user AS sender_id
                       FROM chatmessage cm
                       JOIN users u ON cm.sender_id = u.id_user
                       WHERE cm.id_chatRooms = $id_chat_room
                       ORDER BY cm.waktu_kirim ASC";
    $result_messages = mysqli_query($koneksi, $query_messages);
    if ($result_messages) {
        while ($row = mysqli_fetch_assoc($result_messages)) {
            $messages[] = $row;
        }
    } else {
        echo "Error fetching messages: " . mysqli_error($koneksi);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Chat Interface</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f8f9fa;
            display: flex;
            height: 100vh;
            margin: 0;
            overflow: hidden;
        }

        .chat-sidebar {
            width: 300px;
            background-color: #343a40;
            color: white;
            padding: 20px;
            overflow-y: auto;
            border-right: 1px solid #495057;
        }

        .sidebar-header {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
        }

        .chat-list .list-group-item {
            background-color: #495057;
            color: white;
            border: none;
            margin-bottom: 8px;
            border-radius: 8px;
            transition: background-color 0.2s ease;
        }

        .chat-list .list-group-item:hover,
        .chat-list .list-group-item.active {
            background-color: rgb(204, 145, 96);
        }

        .chat-list .list-group-item .last-message {
            font-size: 0.85rem;
            color: #ced4da;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chat-main {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin: 20px;
        }

        .chat-header {
            background-color: rgb(204, 145, 96);
            color: white;
            padding: 15px 20px;
            font-size: 1.2rem;
            font-weight: 600;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            /* Tambahan untuk tata letak tombol X */
            justify-content: space-between;
            /* Tambahan untuk tata letak tombol X */
            align-items: center;
            /* Tambahan untuk tata letak tombol X */
        }

        .chat-header .close-btn {
            color: white;
            font-size: 1.5rem;
            text-decoration: none;
            margin-left: 15px;
            /* Sesuaikan jarak jika perlu */
            transition: color 0.2s ease;
        }

        .chat-header .close-btn:hover {
            color: #f0f0f0;
        }

        .chat-messages {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
            border-bottom: 1px solid #e9ecef;
        }

        .message-bubble {
            display: flex;
            margin-bottom: 10px;
        }

        .message-bubble.sent {
            justify-content: flex-end;
        }

        .message-bubble.received {
            justify-content: flex-start;
        }

        .message-content {
            padding: 10px 15px;
            border-radius: 20px;
            max-width: 70%;
            word-wrap: break-word;
            font-size: 0.95rem;
        }

        .message-bubble.sent .message-content {
            background-color: rgb(204, 145, 96);
            color: white;
        }

        .message-bubble.received .message-content {
            background-color: #e2e6ea;
            color: #333;
        }

        .message-info {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 5px;
            text-align: right;
        }

        .message-bubble.received .message-info {
            text-align: left;
        }

        .chat-input {
            padding: 15px 20px;
            background-color: #f1f3f4;
            display: flex;
            border-top: 1px solid #dee2e6;
        }

        .chat-input input[type="text"] {
            flex-grow: 1;
            border: 1px solid #ced4da;
            border-radius: 25px;
            padding: 10px 15px;
            margin-right: 10px;
            font-size: 1rem;
        }

        .chat-input button {
            background-color: rgb(204, 145, 96);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 10px 20px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
        }

        .chat-input button:hover {
            background-color: #e09b5f;
        }
    </style>
</head>

<body>
    <div class="chat-sidebar">
        <div class="sidebar-header">
            Pesan Masuk
        </div>
        <div class="list-group chat-list">
            <?php if (empty($chat_rooms_overview)): ?>
                <p class="text-muted text-center">No active chats.</p>
            <?php else: ?>
                <?php foreach ($chat_rooms_overview as $room_summary): ?>
                    <?php
                    // Tentukan sapi_id untuk link. Gunakan 0 jika null agar parameter tetap ada
                    $link_sapi_id = $room_summary['id_sapi'] ?? '0';
                    ?>
                    <a href="chat_admin.php?sapi_id=<?= htmlspecialchars($link_sapi_id) ?>&recipient_id=<?= htmlspecialchars($room_summary['other_user_id']) ?>&chat_type=<?= htmlspecialchars($room_summary['chat_type']) ?>"
                        class="list-group-item list-group-item-action 
                        <?php
                        // Periksa apakah chatroom saat ini aktif berdasarkan ID chat room, recipient_id, sapi_id, dan chat_type
                        if (
                            $id_chat_room == $room_summary['id_chatRooms'] &&
                            $selected_recipient_id == $room_summary['other_user_id'] &&
                            $selected_chat_type == $room_summary['chat_type'] &&
                            (($selected_sapi_id === null && ($room_summary['id_sapi'] === null || $room_summary['id_sapi'] === '0')) || $selected_sapi_id == $room_summary['id_sapi'])
                        ) {
                            echo 'active';
                        }
                        ?>">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1"><?= htmlspecialchars($room_summary['other_username']) ?></h5>
                            <small><?php echo ($room_summary['last_message_time']) ? date('H:i', strtotime($room_summary['last_message_time'])) : ''; ?></small>
                        </div>
                        <p class="mb-1 last-message">
                            <?php
                            if ($room_summary['last_message']) {
                                echo ($room_summary['last_sender_id'] == $current_admin_id) ? 'You: ' : '';
                                echo htmlspecialchars($room_summary['last_message']);
                            } else {
                                echo "Start a conversation";
                            }

                            // Tampilkan info sapi jika ada
                            if ($room_summary['chat_type'] == 'sapi_chat' && !empty($room_summary['id_sapi'])) {
                                echo ' <span class="text-info">(Sapi: ' . htmlspecialchars($room_summary['sapi_jenis_sapi'] ?? 'N/A') . ')</span>';
                            } else if ($room_summary['chat_type'] == 'admin_chat') {
                                echo ' <span class="text-secondary"></span>';
                            }
                            ?>
                        </p>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="chat-main">
        <div class="chat-header">
            <div>
                <?php
                if ($selected_recipient_id) {
                    // Ambil username dari pengguna yang dipilih
                    $query_selected_user = "SELECT username FROM users WHERE id_user = $selected_recipient_id";
                    $result_selected_user = mysqli_query($koneksi, $query_selected_user);
                    $chat_partner_username_display = "User"; // Default

                    if ($result_selected_user && mysqli_num_rows($result_selected_user) > 0) {
                        $user_row = mysqli_fetch_assoc($result_selected_user);
                        $chat_partner_username_display = htmlspecialchars($user_row['username']);
                    }
                    echo "" . $chat_partner_username_display;

                    // Tambahkan info sapi atau tipe chat jika relevan
                    if ($selected_chat_type == 'sapi_chat' && $selected_sapi_id !== null) {
                        $query_sapi_info = "SELECT ds.jenis_kelamin, ms.name AS sapi_jenis_sapi FROM data_sapi ds JOIN macamSapi ms ON ds.id_macamSapi = ms.id_macamSapi WHERE ds.id_sapi = $selected_sapi_id LIMIT 1";
                        $result_sapi_info = mysqli_query($koneksi, $query_sapi_info);
                        if ($result_sapi_info && mysqli_num_rows($result_sapi_info) > 0) {
                            $sapi_info = mysqli_fetch_assoc($result_sapi_info);
                            echo ' <small class="text-light">(Sapi: ' . htmlspecialchars($sapi_info['sapi_jenis_sapi']) . ' - ' . htmlspecialchars($sapi_info['jenis_kelamin']) . ')</small>';
                        }
                    } else if ($selected_chat_type == 'admin_chat') {
                        echo ' <small class="text-light"></small>';
                    }
                } else {
                    echo "Select a conversation";
                }
                ?>
            </div>
            <a href="../admin/pesan.php" class="close-btn" title="Back to Messages">
                <i class="fas fa-times"></i>
            </a>
        </div>
        <div class="chat-messages" id="chatMessages">
            <?php if (!$id_chat_room): ?>
                <p class="text-center text-muted">Please select a conversation from the left sidebar.</p>
            <?php elseif (empty($messages)): ?>
                <p class="text-center text-muted">No messages in this conversation yet.</p>
            <?php else: ?>
                <?php foreach ($messages as $message): ?>
                    <div class="message-bubble <?php echo ($message['sender_id'] == $current_admin_id) ? 'sent' : 'received'; ?>">
                        <div>
                            <div class="message-content">
                                <?php echo htmlspecialchars($message['pesan']); ?>
                            </div>
                            <div class="message-info">
                                <?php echo htmlspecialchars($message['sender_username']); ?> - <?php echo date('H:i', strtotime($message['waktu_kirim'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="chat-input">
            <form action="chat_admin.php" method="POST" style="display: flex; width: 100%;">
                <input type="hidden" name="selected_room_id" value="<?php echo htmlspecialchars($id_chat_room); ?>">
                <input type="hidden" name="selected_recipient_id_for_redirect" value="<?php echo htmlspecialchars($selected_recipient_id); ?>">
                <input type="hidden" name="selected_sapi_id_for_redirect" value="<?php echo htmlspecialchars($selected_sapi_id ?? '0'); ?>">
                <input type="hidden" name="selected_chat_type_for_redirect" value="<?php echo htmlspecialchars($selected_chat_type); ?>">
                <input type="text" name="message_content" placeholder="Type your message..." <?php echo ($id_chat_room ? '' : 'disabled'); ?> required>
                <button type="submit" <?php echo ($id_chat_room ? '' : 'disabled'); ?>>Send</button>
            </form>
        </div>
    </div>

    <script>
        // Auto-scroll ke bagian bawah pesan chat
        var chatMessages = document.getElementById("chatMessages");
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    </script>
</body>

</html>