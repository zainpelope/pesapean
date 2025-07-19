<?php
session_start();
include '../koneksi.php'; // Pastikan path ini benar

// Pastikan user sudah login
if (!isset($_SESSION['id_user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit();
}

$current_user_id = $_SESSION['id_user'];
$chatroom_id = isset($_GET['chatroom_id']) ? (int)$_GET['chatroom_id'] : 0;
$chat_type = isset($_GET['chat_type']) ? $_GET['chat_type'] : 'sapi_chat'; // Ambil chat_type

if ($chatroom_id <= 0) {
    echo '<p>Invalid chatroom ID.</p>';
    exit();
}

// Ambil pesan berdasarkan chatroom_id
// Pastikan chatroom_id valid dan user ini adalah salah satu pesertanya
$stmt_check_chatroom = mysqli_prepare($koneksi, "SELECT user1_id, user2_id FROM chatRooms WHERE id_chatRooms = ?");
mysqli_stmt_bind_param($stmt_check_chatroom, "i", $chatroom_id);
mysqli_stmt_execute($stmt_check_chatroom);
$result_check_chatroom = mysqli_stmt_get_result($stmt_check_chatroom);
$chatroom_info = mysqli_fetch_assoc($result_check_chatroom);
mysqli_stmt_close($stmt_check_chatroom);

if (!$chatroom_info || ($chatroom_info['user1_id'] != $current_user_id && $chatroom_info['user2_id'] != $current_user_id)) {
    echo '<p>Anda tidak memiliki akses ke chatroom ini.</p>';
    exit();
}

// Ambil pesan dari chatmessage
$query_messages = "SELECT cm.message, cm.sender_id, cm.createdAt, u.username
                   FROM chatmessage cm
                   JOIN users u ON cm.sender_id = u.id_user
                   WHERE cm.id_chatRooms = ?
                   ORDER BY cm.createdAt ASC";
$stmt_messages = mysqli_prepare($koneksi, $query_messages);
mysqli_stmt_bind_param($stmt_messages, "i", $chatroom_id);
mysqli_stmt_execute($stmt_messages);
$result_messages = mysqli_stmt_get_result($stmt_messages);

if (mysqli_num_rows($result_messages) > 0) {
    while ($row = mysqli_fetch_assoc($result_messages)) {
        $message_class = ($row['sender_id'] == $current_user_id) ? 'sent' : 'received';
        $timestamp = (new DateTime($row['createdAt']))->format('H:i'); // Format waktu HH:MM
        $sender_name = ($row['sender_id'] == $current_user_id) ? 'Anda' : htmlspecialchars($row['username']);

        echo '<div class="message-bubble ' . $message_class . '">';
        echo '<span>' . htmlspecialchars($row['message']) . '</span>';
        echo '<small>' . $sender_name . ' - ' . $timestamp . '</small>';
        echo '</div>';
    }
} else {
    echo '<p class="text-center text-muted">Belum ada pesan dalam obrolan ini.</p>';
}

mysqli_stmt_close($stmt_messages);
mysqli_close($koneksi);
