<?php
// Include database connection
require_once 'connectDB.php';

session_start();

// Get the JSON data from the request
$data = json_decode(file_get_contents("php://input"), true);

$role = isset($_SESSION['role']) ? $_SESSION['role'] : "user";

if ($data) {
    print_r($data);
    // Default values from the data
    $user_name = $data['user_name'];
    $user_id = $data['user_id'] || null;
    $message_topic = $data['message_topic'];
    $message_text = $data['message_text'];
    $message_token = $data['message_token'];
    $session_token = $data['session_token'];
    $answer_to = $data['answer_to'] || null; // New field for answer
    $created_at = date("Y-m-d H:i:s", null);

    //Insert the message into the database
    $query = "INSERT INTO messages 
        (user_name, user_id, message_topic, message_text, message_token, session_token, answer_to, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param(
            "ssssssss",
            $user_name,
            $user_id,
            $message_topic,
            $message_text,
            $message_token,
            $session_token,
            $answer_to,
            $created_at
        );
        $stmt->execute();
        $stmt->close();

        echo json_encode(["status" => "success", "message" => "Message sent!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to send message"]);
    }
}
