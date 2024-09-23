<?php
require_once 'connect.php';

//$userId = $_SESSION['user_id'];

$query = "SELECT COUNT(*) AS message_count FROM messages";
$stmt = $conn->prepare($query);
// $stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo $row['message_count']; // Return the count of messages