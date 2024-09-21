<?php
require_once 'connect.php';

$userId = $_SESSION['user_id']; // Assuming user is logged in and the ID is stored in the session

$query = "SELECT COUNT(*) AS message_count FROM messages WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo $row['message_count']; // Return the count of messages