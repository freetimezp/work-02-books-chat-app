<?php
require_once 'connectDB.php';
session_start();

// Decode the JSON payload
$data = json_decode(file_get_contents("php://input"), true);

// Check if 'session_token' is set and not null
$chatSessionToken = isset($data['session_token']) ? $data['session_token'] : null;

if (!$chatSessionToken) {
    echo json_encode(["message" => "No session token provided."]);
    exit;
}

//error_log("Chat session token: " . $chatSessionToken); // Log the token to check its value

// Prepare and execute the SQL query
$query = "SELECT * FROM messages WHERE session_token = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $chatSessionToken);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];

// Collect all messages
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

// Return the messages if found, otherwise return a "No messages found" response
if (count($messages) > 0) {
    echo json_encode($messages);
} else {
    echo json_encode(["message" => "No messages found."]);
}
