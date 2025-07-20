<?php
// config/function.php

// Sanitize input data
function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

// Check if a user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirect to a URL
function redirect($url) {
    header("Location: " . $url);
    exit();
}
?>
