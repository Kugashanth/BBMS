<?php
include 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hospital_id = $_POST['hospital_id'];
    $new_password = password_hash($_POST['new-password'], PASSWORD_BCRYPT); // Secure password hashing

    // Update the hospital's password
    $query = "UPDATE hospitals SET password = '$new_password' WHERE id = $hospital_id";
    if (mysqli_query($conn, $query)) {
        // Redirect to Manage Hospital page with a success message
        header("Location: manage_hospitals.php?status=success");
        exit(); // Ensure no further code is executed after the redirect
    } else {
        // Redirect to Manage Hospital page with an error message
        header("Location: manage_hospitals.php?status=error");
        exit(); // Ensure no further code is executed after the redirect
    }
}
?>
