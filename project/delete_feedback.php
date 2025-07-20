<?php
session_start(); // Start the session
include 'config/db.php';

if (isset($_GET['id'])) {
    $feedback_id = intval($_GET['id']); // Sanitize input

    // Prepare the delete query
    $query = "DELETE FROM contact WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $feedback_id);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) > 0) {
            $_SESSION['success_msg'] = "Feedback deleted successfully!";
        } else {
            $_SESSION['error_msg'] = "Error: Feedback not found or already deleted!";
        }

        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error_msg'] = "Database error! Please try again.";
    }

    mysqli_close($conn);
    header("Location: view_feedback.php");
    exit();
}
?>
