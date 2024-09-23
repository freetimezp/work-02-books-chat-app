<?php
ob_start(); // Start output buffering


require_once 'connectDB.php';
session_start();

// Decode the JSON payload
$data = json_decode(file_get_contents("php://input"), true);

//check role
$role = isset($_SESSION['role']) ? $_SESSION['role'] : "";

// Get the current user's ID from the session
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!empty($_GET['debug'])) {
    echo json_encode(["user_id" => $userId]); // Only for debugging
    exit; // Exit after debug response
}

// Check if 'session_token' is set and not null
$chatSessionToken = isset($data['session_token']) ? $data['session_token'] : null;

if ($role !== 'admin' && $role !== 'manager' && !$chatSessionToken) {
    echo json_encode(["message" => "No session token provided."]);
    exit;
}

//error_log("Chat session token: " . $chatSessionToken); // Log the token to check its value

// Prepare the SQL query based on the role
if ($role === 'admin') {
    // Admin can see all messages
    $query = "SELECT * FROM messages";
} elseif ($role === 'manager') {
    // Manager sees messages related to topics they manage
    $query = "SELECT m.* FROM messages m 
              JOIN topics t ON m.message_topic = t.title 
              WHERE t.manager_id = ?"; // Filter by manager_id
} else {
    // Regular users see messages for their session token
    $query = "SELECT * FROM messages WHERE session_token = ?";
}

$stmt = $conn->prepare($query);

if ($role === 'manager') {
    // Bind the user_id for the manager
    $stmt->bind_param("s", $userId);
} elseif ($role !== 'admin') {
    $stmt->bind_param("s", $chatSessionToken);
}

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


ob_end_flush(); // Send the output