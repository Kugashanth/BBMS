<?php
session_start();
include 'config/db.php'; 

// Response array for AJAX
$response = ['status' => 'error', 'message' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle AJAX request for login
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        if (!empty($email) && !empty($password)) {
            // Prevent SQL injection
                $stmt = $conn->prepare("SELECT id, username, password ,img FROM admin WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($id, $username, $hashed_password,$img);
                $stmt->fetch();
            
            if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
                // Store user data in session
                $_SESSION['admin_id'] = $id;
                $_SESSION['username'] = $username;
                $_SESSION['img'] = $img;

                if ($_SESSION['admin_id']) {
                    $response = ['status' => 'success', 'redirect' => 'index.php'];
                } 
            } else {
              
                $stmt = $conn->prepare("SELECT id, username, password ,img,name FROM hospitals WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($id, $username, $hashed_password,$img,$name);
                $stmt->fetch();

                if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
                    // Store user data in session
                    $_SESSION['user_id'] = $id;
                    $_SESSION['username'] = $username;
                    $_SESSION['img'] = $img;
                    $_SESSION['name'] = $name;
    
                    if ($_SESSION['user_id']) {
                        $response = ['status' => 'success', 'redirect' => 'index.php'];
                    } 
                } 
                else{
                    $response = ['status' => 'error', 'message' => 'Invalid email or password!'];
                }
            }
        } else {
            $response = ['status' => 'error', 'message' => 'Please fill in all fields!'];
        }

        // Return response as JSON
        echo json_encode($response);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Blood Bank</title>
    <link rel="stylesheet" href="css/login.css">
</head>

<body>
    <div class="container">
        <img src="img/logo.png" alt="Blood Bank Logo" class="logo">
        <h2>Welcome Back</h2>
        <p class="subtitle">Log in to access your dashboard</p>

        <div id="error-message" class="error"></div>

        <form id="loginForm">
            <div class="input-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
            </div>

            <div class="input-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>

            <button type="submit">Login</button>
            <p>Don't have an account? <a href="register.php">Register</a></p>
        </form>
    </div>

    <script>
        document.getElementById("loginForm").addEventListener("submit", function(event) {
            event.preventDefault();  // Prevent form submission

            var email = document.getElementById("email").value;
            var password = document.getElementById("password").value;

            // Validate form fields
            if (email === "" || password === "") {
                alert("Please fill in all fields!");
                return false;
            }

            // Send data via AJAX
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "login.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            // Handle server response
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var response = JSON.parse(xhr.responseText);
                    
                    if (response.status === 'success') {
                        window.location.href = response.redirect;  // Redirect to dashboard
                    } else {
                        document.getElementById("error-message").innerHTML = response.message;  // Show error message
                    }
                }
            };

            // Send email and password to PHP for verification
            xhr.send("email=" + encodeURIComponent(email) + "&password=" + encodeURIComponent(password));
        });
    </script>
</body>
</html>
