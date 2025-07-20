<?php
session_start();
include 'config/db.php'; // Database connection

$admin_id = $_SESSION['admin_id'] ?? 5; // Use session admin ID or default to 5

// Update Password
if (isset($_POST['update_password'])) {
    $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $query = "UPDATE admin SET password = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $new_password, $admin_id);
    if ($stmt->execute()) {
        echo "Password updated successfully.";
    } else {
        echo "Error updating password.";
    }
    exit();
}

// Update Profile Image
if (!empty($_FILES['img']['name'])) {
    $target_dir = "img/profile/";
    $target_file = $target_dir . basename($_FILES["img"]["name"]);
    if (move_uploaded_file($_FILES["img"]["tmp_name"], $target_file)) {
        $query = "UPDATE admin SET img = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $target_file, $admin_id);
        if ($stmt->execute()) {
            echo "Profile image updated successfully.";
        } else {
            echo "Error updating image.";
        }
    } else {
        echo "Error uploading file.";
    }
    exit();
}
?>
