<?php
session_start();
session_destroy();

$_SESSION['url'] = $_SERVER['REQUEST_URI'];

if (isset($_SERVER['HTTP_REFERER'])) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
} else {
    header('Location: index.php');
}
exit;
