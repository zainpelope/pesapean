<?php
session_start();
include '../koneksi.php';

header('Content-Type: text/html'); // Pastikan ini adalah HTML, bukan JSON

if (!isset($_SESSION['id_user'])) {
    echo "Unauthorized";
    exit();
}

$chatroom_id = isset($_GET['chatroom_id']) ? (int)$_GET['chatroom_id'] : 0;
$current_user_id = $_SESSION['id_user'];
$recipient_id = isset($_GET['recipient_id']) ? (int)$_GET['recipient_id'] : 0; // ID penjual dari URL

if ($chatroom_id <= 0 || $recipient_id <= 0) {
    echo "Invalid chatroom ID or recipient ID.";
    exit();
}

// Validasi akses: Pastikan user yang sedang login adalah salah satu dari user1_id atau user2_id di chatroom ini
$stmt_check_access = mysqli_prepare($koneksi, "SELECT user1_id, user2_id FROM chatRooms WHERE id_chatRooms = ?");
mysqli_stmt_bind_param($stmt_check_access, "i", $chatroom_id);
mysqli_stmt_execute($stmt_check_access);
$result_access = mysqli_stmt_get_result($stmt_check_access);
$chatroom_users = mysqli_fetch_assoc($result_access);
mysqli_stmt_close($stmt_check_access);

if (!$chatroom_users || ($current_user_id != $chatroom_users['user1_id'] && $current_user_id != $chatroom_users['user2_id'])) {
    echo "Access Denied. You are not a participant in this chat.";
    exit();
}

// Ambil pesan
$query = "SELECT cm.pesan, cm.waktu_kirim, cm.sender_id, u.username
          FROM chatMessage cm
          LEFT JOIN users u ON cm.sender_id = u.id_user
          WHERE cm.id_chatRooms = ?
          ORDER BY cm.waktu_kirim ASC";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $chatroom_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $message_class = ($row['sender_id'] == $current_user_id) ? 'sent' : 'received';
        $sender_name = ($row['sender_id'] == $current_user_id) ? 'Anda' : htmlspecialchars($row['username'] ?? 'Pengguna');
        $time_formatted = date('H:i', strtotime($row['waktu_kirim']));
        echo "<div class='message-bubble {$message_class}'>";
        echo "<strong>" . $sender_name . ":</strong> " . htmlspecialchars($row['pesan']);
        echo "<small>" . $time_formatted . "</small>";
        echo "</div>";
    }
} else {
    echo "<p class='text-center text-muted'>Mulai percakapan Anda di sini.</p>";
}

mysqli_stmt_close($stmt);
mysqli_close($koneksi);
