<?php
// fetch_messages.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include '../koneksi.php';

if (!isset($_SESSION['id_user'])) {
    exit(); // Atau redirect ke login jika Anda ingin memaksakan login
}

$chatroom_id = isset($_GET['chatroom_id']) ? (int)$_GET['chatroom_id'] : 0;
$current_user_id = isset($_GET['current_user_id']) ? (int)$_GET['current_user_id'] : 0; // Dapatkan dari JS

if ($chatroom_id <= 0 || $current_user_id <= 0) {
    echo "Pilih chatroom untuk melihat pesan.";
    exit();
}

// Pastikan user yang login adalah salah satu peserta chatroom
$stmt_validate_chatroom_user = mysqli_prepare($koneksi, "SELECT id_chatRooms FROM chatRooms WHERE id_chatRooms = ? AND (user1_id = ? OR user2_id = ?)");
if ($stmt_validate_chatroom_user) {
    mysqli_stmt_bind_param($stmt_validate_chatroom_user, "iii", $chatroom_id, $current_user_id, $current_user_id);
    mysqli_stmt_execute($stmt_validate_chatroom_user);
    $result_validate = mysqli_stmt_get_result($stmt_validate_chatroom_user);
    if (mysqli_num_rows($result_validate) == 0) {
        echo "Anda tidak memiliki akses ke chatroom ini.";
        exit();
    }
    mysqli_stmt_close($stmt_validate_chatroom_user);
} else {
    echo "Error validasi chatroom: " . mysqli_error($koneksi);
    exit();
}


$query = "SELECT cm.pesan, cm.waktu_kirim, cm.sender_id, u.username AS sender_username
          FROM chatmessage cm
          JOIN users u ON cm.sender_id = u.id_user
          WHERE cm.id_chatRooms = ?
          ORDER BY cm.waktu_kirim ASC";

$stmt = mysqli_prepare($koneksi, $query);
if (!$stmt) {
    echo "Error prepared statement: " . mysqli_error($koneksi);
    exit();
}
mysqli_stmt_bind_param($stmt, "i", $chatroom_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    while ($message = mysqli_fetch_assoc($result)) {
        $is_sent = ($message['sender_id'] == $current_user_id) ? 'sent' : 'received';
        $time = date('H:i', strtotime($message['waktu_kirim']));
        echo '<div class="message-bubble ' . $is_sent . '">';
        echo htmlspecialchars($message['pesan']);
        echo '<small>' . htmlspecialchars($time) . '</small>';
        echo '</div>';
    }
} else {
    echo '<div class="text-center text-muted">Belum ada pesan dalam chat ini.</div>';
}

mysqli_stmt_close($stmt);
mysqli_close($koneksi);
