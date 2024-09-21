<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "books_chat_app";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// if ($conn) {
//     echo ("connection success");
// }

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


return $conn;
