<?php
session_start();
include '../koneksi.php'; // Your database connection file

// Check if the user is logged in
if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit();
}

$current_user_id = $_SESSION['id_user'];

// Find the admin user ID. Assuming 'Admin' role has id_role = 1
$admin_id = null;
$query_admin_id = "SELECT id_user FROM users WHERE id_role = (SELECT id_role FROM role WHERE nama_role = 'Admin') LIMIT 1";
$result_admin_id = mysqli_query($koneksi, $query_admin_id);
if ($result_admin_id && mysqli_num_rows($result_admin_id) > 0) {
    $admin_row = mysqli_fetch_assoc($result_admin_id);
    $admin_id = $admin_row['id_user'];
} else {
    // Handle case where admin user is not found
    die("Admin user not found. Please ensure an admin user exists in the database.");
}

// Check for an existing chat room between the current user and the admin
$id_chat_room = null;
$query_chatroom = "SELECT id_chatRooms FROM chatrooms
                    WHERE (user1_id = $current_user_id AND user2_id = $admin_id)
                    OR (user1_id = $admin_id AND user2_id = $current_user_id)
                    LIMIT 1";
$result_chatroom = mysqli_query($koneksi, $query_chatroom);

if ($result_chatroom && mysqli_num_rows($result_chatroom) > 0) {
    $chatroom_row = mysqli_fetch_assoc($result_chatroom);
    $id_chat_room = $chatroom_row['id_chatRooms'];
} else {
    // If no chat room exists, create a new one
    $insert_chatroom_query = "INSERT INTO chatrooms (user1_id, user2_id, chat_type, createdAt, updatedAt)
                              VALUES ($current_user_id, $admin_id, 'admin_chat', NOW(), NOW())";
    if (mysqli_query($koneksi, $insert_chatroom_query)) {
        $id_chat_room = mysqli_insert_id($koneksi); // Get the ID of the newly created chat room
    } else {
        die("Error creating chat room: " . mysqli_error($koneksi));
    }
}

// Handle sending messages
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message_content'])) {
    $message_content = mysqli_real_escape_string($koneksi, $_POST['message_content']);
    if (!empty($message_content) && $id_chat_room) {
        $insert_message_query = "INSERT INTO chatmessage (id_chatRooms, sender_id, pesan, waktu_kirim)
                                 VALUES ($id_chat_room, $current_user_id, '$message_content', NOW())";
        if (!mysqli_query($koneksi, $insert_message_query)) {
            echo "Error sending message: " . mysqli_error($koneksi);
        }
    }
    // Redirect to prevent form resubmission on refresh
    header("Location: chat_admin.php");
    exit();
}

// Fetch chat messages
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
    <title>Chat with Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f8f9fa;
        }

        .chat-container {
            max-width: 800px;
            margin: 50px auto;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 70vh;
            /* Adjust as needed */
        }

        .chat-header {
            background-color: rgb(204, 145, 96);
            color: white;
            padding: 15px 20px;
            font-size: 1.2rem;
            font-weight: 600;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            /* Added for alignment */
            justify-content: space-between;
            /* Added for alignment */
            align-items: center;
            /* Added for alignment */
        }

        .close-button {
            color: white;
            font-size: 1.5rem;
            text-decoration: none;
            cursor: pointer;
        }

        .close-button:hover {
            color: #f8d7da;
            /* Lighter color on hover */
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
            /* For sent messages */
        }

        .message-bubble.received .message-info {
            text-align: left;
            /* For received messages */
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
    <div class="chat-container">
        <div class="chat-header">
            Chat with Admin
            <a href="../pembeli/beranda.php" class="close-button" aria-label="Close chat">
                <i class="fas fa-times"></i>
            </a>
        </div>
        <div class="chat-messages" id="chatMessages">
            <?php if (empty($messages)): ?>
                <p class="text-center text-muted">Start a conversation with the admin!</p>
            <?php else: ?>
                <?php foreach ($messages as $message): ?>
                    <div class="message-bubble <?php echo ($message['sender_id'] == $current_user_id) ? 'sent' : 'received'; ?>">
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
                <input type="text" name="message_content" placeholder="Type your message..." required>
                <button type="submit">Send</button>
            </form>
        </div>
    </div>

    <script>
        // Auto-scroll to the bottom of the chat messages
        var chatMessages = document.getElementById("chatMessages");
        chatMessages.scrollTop = chatMessages.scrollHeight;
    </script>
</body>

</html>