<?php
require_once 'connectDB.php';
session_start();

// Check if the role and user ID are set in the session
$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

$query = "SELECT * FROM messages ORDER BY created_at ASC";

$stmt = $conn->prepare($query);

$stmt->execute();
$result = $stmt->get_result();

$messages = [];

while ($row = $result->fetch_assoc()) {
    $messages[] = $row;  // Collect all message rows
}

// Check if there are messages and display them
if (count($messages) > 0) {
    // Loop through the messages and display them
    foreach ($messages as $message) {
        echo '
            <div class="chat-message-block' . '" onclick="replyToMessage(\'' . $message['message_token'] . '\')">
                <div class="chat-message-block__header">
                    <div class="chat-message-block__header-left">
                        <div class="chat-message-avatar">
                        ' . substr(htmlspecialchars($message['user_name']), 0, 1) . ' 
                        </div>
                        <div class="chat-message-name">
                            ' . htmlspecialchars($message['user_name']) . '  
                        </div>
                    </div>
                    <div class="chat-message-block__header-right">
                        <div class="chat-message-topic">
                        ' . htmlspecialchars($message['message_topic']) . ' 
                        </div>
                        <div class="chat-message-date">
                            ' . htmlspecialchars($message['created_at']) . ' 
                        </div>
                    </div>
                </div>
                <div class="chat-message-block__content">
                    <p>
                        ' . htmlspecialchars($message['message_text']) . '
                    </p>
                </div>
            </div>
        ';
    }
} else {
    echo "No messages found.";
}
