<?php

session_start();
require_once 'connectDB.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST['chat-login-email']) ? $_POST['chat-login-email'] : "";
    $password = isset($_POST['chat-login-pass']) ? $_POST['chat-login-pass'] : "";

    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    //var_dump($user);
    //print_r($_SESSION['url']);

    if ($user && password_verify($password, $user['password'])) {
        // After successful login
        $_SESSION['chat-login-name'] = $user['name'];
        $_SESSION['chat-login-email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['user_id'] = $user['user_id'];

        //redirect to main page
        header("Location: http://" . $_SERVER['HTTP_HOST'] . $_SESSION['url']);
        //exit;
    } else {
        //echo "Invalid login!";
    }
}
