<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../koneksi.php';

header('Content-Type: application/json'); // Respon dalam format JSON

if (!isset($_SESSION['id_user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$chatroom_id = isset($_POST['chatroom_id']) ? (int)$_POST['chatroom_id'] : 0;
$sender_id = isset($_POST['sender_id']) ? (int)$_POST['sender_id'] : 0;
$message_text = isset($_POST['message']) ? trim($_POST['message']) : '';
$recipient_id = isset($_POST['recipient_id']) ? (int)$_POST['recipient_id'] : 0; // ID penerima (penjual)

// Validasi input
if ($chatroom_id <= 0 || $sender_id <= 0 || empty($message_text) || $recipient_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap atau tidak valid.']);
    exit();
}

// Pastikan pengirim adalah user yang sedang login
if ($sender_id != $_SESSION['id_user']) {
    echo json_encode(['status' => 'error', 'message' => 'ID Pengirim tidak cocok dengan sesi login.']);
    exit();
}

// Validasi akses: Pastikan user yang mengirim pesan adalah user1_id atau user2_id di chatroom ini
$stmt_check_access = mysqli_prepare($koneksi, "SELECT user1_id, user2_id FROM chatRooms WHERE id_chatRooms = ?");
mysqli_stmt_bind_param($stmt_check_access, "i", $chatroom_id);
mysqli_stmt_execute($stmt_check_access);
$result_access = mysqli_stmt_get_result($stmt_check_access);
$chatroom_users = mysqli_fetch_assoc($result_access);
mysqli_stmt_close($stmt_check_access);

if (!$chatroom_users || ($sender_id != $chatroom_users['user1_id'] && $sender_id != $chatroom_users['user2_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Akses ke chatroom ditolak. Anda bukan peserta chat ini.']);
    exit();
}

// Optional: Anda bisa menambahkan validasi bahwa recipient_id yang diterima dari frontend
// memang cocok dengan salah satu user di chatroom selain sender_id.
// Ini adalah lapisan keamanan tambahan jika diperlukan.


// Simpan pesan ke database
$query = "INSERT INTO chatMessage (id_chatRooms, sender_id, pesan, waktu_kirim) VALUES (?, ?, ?, NOW())";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "iis", $chatroom_id, $sender_id, $message_text);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['status' => 'success', 'message' => 'Pesan berhasil dikirim.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan pesan: ' . mysqli_error($koneksi)]);
}

mysqli_stmt_close($stmt);
mysqli_close($koneksi);
