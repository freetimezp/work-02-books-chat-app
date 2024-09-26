<?php

require_once 'connectDB.php';
session_start();

// Decode the JSON payload
$data = json_decode(file_get_contents("php://input"), true);

// Check if the session token is set in the POST request
$sessionToken = isset($data['session_token']) ? $data['session_token'] : null;

// Check role and user id
$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if ($sessionToken) {
    try {
        // Prepare the SQL query based on the role
        if ($role === "admin") {
            // Admin sees all messages
            $query = "SELECT messages.*, users.role AS user_role
                      FROM messages 
                      LEFT JOIN users ON messages.user_id = users.user_id
                      ORDER BY created_at ASC";
            $stmt = $conn->prepare($query);
        } else if ($role === "manager" && $userId) {
            // Manager sees messages related to their topics or their own messages
            $query = "SELECT messages.*, users.role AS user_role
                      FROM messages 
                      LEFT JOIN topics ON messages.message_topic = topics.title
                      LEFT JOIN users ON messages.user_id = users.user_id
                      WHERE topics.manager_id = ? OR messages.user_id = ?
                      ORDER BY messages.created_at ASC";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $userId, $userId);
        } else {
            // Regular user sees all messages associated with their session token or replies to their messages
            $query = "SELECT messages.*, users.role AS user_role
                      FROM messages 
                      LEFT JOIN users ON messages.user_id = users.user_id
                      WHERE session_token = ? 
                      OR messages.answer_to IN (
                          SELECT message_token FROM messages WHERE session_token = ?
                      )
                      ORDER BY created_at ASC";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $sessionToken, $sessionToken);
        }

        // Execute the query and fetch results
        $stmt->execute();
        $result = $stmt->get_result();
        $messages = $result->fetch_all(MYSQLI_ASSOC);

        // Return the messages in JSON format
        header('Content-Type: application/json');
        echo json_encode($messages);
    } catch (Exception $e) {
        // Error handling in case of failure
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Failed to retrieve messages', 'details' => $e->getMessage()]);
    }
} else {
    // Return an error if session token is not provided
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Session token not provided!']);
}
