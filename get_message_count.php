<?php
require_once 'connectDB.php';

// Decode the JSON payload
$data = json_decode(file_get_contents("php://input"), true);

$sessionToken = isset($data['session_token']) ? $data['session_token'] : null;

if ($sessionToken) {
    $query = "SELECT COUNT(*) AS message_count FROM messages WHERE session_token = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $sessionToken);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    header('Content-Type: application/json');
    echo json_encode($row['message_count']);
} else {
    header('Content-Type: application/json');
    echo json_encode(['message' => 'Session token not provided! Try count messages']);
}
