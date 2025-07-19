<?php
session_start();
include '../koneksi.php'; // Pastikan path ini benar

header('Content-Type: application/json');

// Pastikan user sudah login
if (!isset($_SESSION['id_user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit();
}

$current_user_id = $_SESSION['id_user'];
$chatroom_id = isset($_POST['chatroom_id']) ? (int)$_POST['chatroom_id'] : 0;
$sender_id = isset($_POST['sender_id']) ? (int)$_POST['sender_id'] : 0;
$message_text = isset($_POST['message']) ? trim($_POST['message']) : '';
$chat_type = isset($_POST['chat_type']) ? $_POST['chat_type'] : 'sapi_chat'; // Ambil chat_type

// Validasi input
if ($chatroom_id <= 0 || $sender_id <= 0 || empty($message_text)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data provided.']);
    exit();
}

// Pastikan sender_id yang dikirim sama dengan user yang login
if ($sender_id != $current_user_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid sender ID.']);
    exit();
}

// Verifikasi chatroom_id dan pastikan user yang login adalah salah satu pesertanya
$stmt_check_chatroom = mysqli_prepare($koneksi, "SELECT user1_id, user2_id FROM chatRooms WHERE id_chatRooms = ? AND chat_type = ?");
mysqli_stmt_bind_param($stmt_check_chatroom, "is", $chatroom_id, $chat_type);
mysqli_stmt_execute($stmt_check_chatroom);
$result_check_chatroom = mysqli_stmt_get_result($stmt_check_chatroom);
$chatroom_info = mysqli_fetch_assoc($result_check_chatroom);
mysqli_stmt_close($stmt_check_chatroom);

if (!$chatroom_info || ($chatroom_info['user1_id'] != $current_user_id && $chatroom_info['user2_id'] != $current_user_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Anda tidak memiliki akses untuk mengirim pesan ke chatroom ini.']);
    exit();
}

// Masukkan pesan ke database
$query_insert = "INSERT INTO chatmessage (id_chatRooms, sender_id, message, createdAt) VALUES (?, ?, ?, NOW())";
$stmt_insert = mysqli_prepare($koneksi, $query_insert);
mysqli_stmt_bind_param($stmt_insert, "iis", $chatroom_id, $sender_id, $message_text);

if (mysqli_stmt_execute($stmt_insert)) {
    echo json_encode(['status' => 'success', 'message' => 'Message sent.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to send message: ' . mysqli_error($koneksi)]);
}

mysqli_stmt_close($stmt_insert);
mysqli_close($koneksi);
