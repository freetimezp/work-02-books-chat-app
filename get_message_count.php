<?php
require_once 'connectDB.php';

// Decode the JSON payload
$data = json_decode(file_get_contents("php://input"), true);

//session and role
$sessionToken = isset($data['session_token']) ? $data['session_token'] : null;
$role = isset($data['role']) ? $data['role'] : null;
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if ($sessionToken) {
    if ($role === 'manager' || $role === 'admin') {
        // Count messages related to the manager's topics or sent by the manager
        $query = "SELECT COUNT(*) AS message_count FROM messages";
        // Ensure session token is considered for the manager

        $stmt = $conn->prepare($query);
        // Bind manager's user ID to both conditions (manager's topics and their own messages)
        //$stmt->bind_param("ss", $userId, $userId);
    } else {
        // Regular user, only count messages related to their session token
        $query = "SELECT COUNT(*) AS message_count 
                  FROM messages";
        //   WHERE session_token = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $sessionToken);  // Bind session token for the user
    }

    // Execute and return result
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Ensure the response is in JSON format
    header('Content-Type: application/json');
    echo json_encode($row['message_count']);  // Return message count in JSON format

} else {
    // Return error if session token is not provided
    header('Content-Type: application/json');
    echo json_encode(['message' => 'Session token not provided!']);
}
