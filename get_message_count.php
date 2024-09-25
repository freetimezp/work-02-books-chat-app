<?php
require_once 'connectDB.php';

// Decode the JSON payload
$data = json_decode(file_get_contents("php://input"), true);

//session and role
$sessionToken = isset($data['session_token']) ? $data['session_token'] : null;
$role = isset($data['role']) ? $data['role'] : null;


if ($sessionToken) {
    if ($role === 'manager') {
        // If the manager, count messages related to their topics
        $query = "SELECT COUNT(*) AS message_count 
                  FROM messages 
                  LEFT JOIN topics ON messages.message_topic = topics.title
                  WHERE topics.manager_id = ? 
                  OR messages.user_id = ?"; // Include manager's own messages

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $_SESSION['user_id'], $_SESSION['user_id']);
    } else {
        // Regular user, only count messages for their session token
        $query = "SELECT COUNT(*) AS message_count 
                  FROM messages 
                  WHERE session_token = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $sessionToken);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    header('Content-Type: application/json');
    echo json_encode($row['message_count']);
} else {
    header('Content-Type: application/json');
    echo json_encode(['message' => 'Session token not provided! Try count messages']);
}
