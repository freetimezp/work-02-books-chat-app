<?php

require_once 'connectDB.php';
session_start();

// Decode the JSON payload
$data = json_decode(file_get_contents("php://input"), true);

// Check if the session token is set in the POST request
$sessionToken = isset($data['session_token']) ? $data['session_token'] : null;

//check role
$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;

//check user id
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if ($sessionToken) {
    // Handle the logic to fetch new messages based on the session token

    if ($role === "admin") {
        //admin see all messages
        $query = "SELECT * FROM messages ORDER BY created_at ASC";
    } else if ($role === "manager") {
        $query = "SELECT messages.* 
        FROM messages 
        JOIN topics ON messages.message_topic = topics.title
        WHERE topics.manager_id = ? 
        ORDER BY messages.created_at ASC";
    } else {
        //user see all messages with own session_token
        $query = "SELECT * FROM messages WHERE session_token = ? ORDER BY created_at ASC";
    }

    $stmt = $conn->prepare($query);

    if ($role !== "manager" && $role !== "admin") {
        $stmt->bind_param("s", $sessionToken);
    }

    if ($role === "manager") {
        $stmt->bind_param("s", $userId);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    // Output the messages in a format your frontend can handle
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($messages);
} else {
    header('Content-Type: application/json');
    echo json_encode(['message' => 'Session token not provided!']);
}
