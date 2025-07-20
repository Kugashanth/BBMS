<?php
include 'config/db.php';

if (isset($_GET['id'])) {
    $hospital_id = $_GET['id'];
    
    // Fetch hospital details based on the ID
    $query = "SELECT * FROM hospitals WHERE id = $hospital_id";
    $result = mysqli_query($conn, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        // Return the hospital details in HTML format
        echo "<h2>Hospital Details</h2>";
        echo "<p><i class='fas fa-hospital-alt'></i> <strong>Name:</strong> " . $row['name'] . "</p>";
        echo "<p><i class='fas fa-map-marker-alt'></i> <strong>Location:</strong> " . $row['location'] . "</p>";
        echo "<p><i class='fas fa-phone-alt'></i> <strong>Phone:</strong> " . $row['phone'] . "</p>";
        echo "<p><i class='fas fa-globe'></i> <strong>Website:</strong> <a href='" . $row['website'] . "' target='_blank'>" . $row['website'] . "</a></p>";
        echo "<p><i class='fas fa-city'></i> <strong>District:</strong> " . $row['district'] . "</p>";
        echo "<p><i class='fas fa-flag'></i> <strong>Province:</strong> " . $row['province'] . "</p>";
        echo "<p><i class='fas fa-envelope'></i> <strong>Email:</strong> " . $row['email'] . "</p>";
        echo "<p><i class='fas fa-user'></i> <strong>Username:</strong> " . $row['username'] . "</p>";

        // Change Password Form (hidden initially)
        echo "<hr>";
        echo "<button id='show-password-btn' class='pw-btn' onclick='showChangePasswordForm()'><i class='fas fa-key'></i> Change Password</button>";
        echo "<div id='change-password-form' style='display:none; margin-top: 20px;'>";
        echo "<form action='change_password.php' method='POST'>";
        echo "<input type='hidden' name='hospital_id' value='" . $row['id'] . "'>";
        echo "<label for='new-password'><i class='fas fa-key'></i> Change Password:</label><br>";
        echo "<input type='password' id='new-password' name='new-password' required><br><br>";
        echo "<button type='submit' class='pw-btn'><i class='fas fa-lock'></i> Change Password</button>";
        echo "</form>";
        echo "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Details</title>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow: auto;
        }

        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px 30px;
            border: 1px solid #888;
            width: 90%;
            max-width: 450px;
            border-radius: 10px;
        }
        .modal-content h2{
            text-align: center;
        }

        .modal-header .close {
            font-size: 30px;
            cursor: pointer;
            color: #aaa;
            float: right;
        }

        .modal-header .close:hover {
            color: #d9534f;
        }

        .modal-body {
            padding: 0 10px 10px 10px;
            font-size: 1.2em;
            line-height: 1.6;
        }

        .hospital-image {
            width: 100%;
            height: auto;
            margin-top: 20px;
            border-radius: 10px;
        }

        .pw-btn {
            padding: 12px 20px;
            background-color:  #1E3A8A;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            width: 100%;
            margin-top: 3px;
        }

        .pw-btn:hover {
            background-color:  #1E3A8A;
        }

        .pw-btn i {
            margin-right: 10px;
        }

        .pw-btn:focus {
            outline: none;
        }

        /* New styles for the input field */
        input[type="password"] {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-top: 3px;
            box-sizing: border-box;
        }

        input[type="password"]:focus {
            border-color: #007BFF;
            outline: none;
        }

    </style>
</head>
<body>

<script>
    // Function to show the Change Password form, hide the button and apply CSS
    function showChangePasswordForm() {
        document.getElementById('show-password-btn').style.display = 'none';  // Hide the button
        document.getElementById('change-password-form').style.display = 'block'; // Show the form
    }

    // Close the modal when clicking outside the modal content
    window.onclick = function(event) {
        var modal = document.getElementById('hospital-modal');
        if (event.target === modal) {
            closeModal();
        }
    }
</script>

</body>
</html>
