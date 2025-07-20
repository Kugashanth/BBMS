<?php
session_start();
include 'config/db.php';

// Initialize error message
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $username = trim($_POST['username']);

    // Check if all fields are filled
    if (empty($email) || empty($password) || empty($confirm_password) || empty($username)) {
        $error = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        // Check if passwords match
        $error = "Passwords do not match!";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM hospitals WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            // Email already exists
            $error = "Email is already registered!";
        } else {
            // Check if username already exists
            $stmt = $conn->prepare("SELECT id FROM hospitals WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = "Username is already taken!";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert new user into the database
                $stmt = $conn->prepare("INSERT INTO hospitals (email, username, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $email, $username, $hashed_password);
                if ($stmt->execute()) {
                    $_SESSION['user_id'] = $stmt->insert_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = 'Hospital';
                    header("Location: hospital_dashboard.php");
                    exit();
                } else {
                    $error = "Something went wrong. Please try again!";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Blood Bank</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="container">
        <img src="img/logo.png" alt="Blood Bank Logo" class="logo">
        <h2>Register</h2>
        <p class="subtitle">Create a new account</p>

        <!-- Show error message inside the form -->
        <div id="form-error">
            <?php if (!empty($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
        </div>

        <form id="registerForm" method="POST">
            <div class="input-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required>
                <span id="username-error"></span>
            </div>

            <div class="input-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
                <span id="email-error"></span>
            </div>

            <div class="input-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>

            <div class="input-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>

            <button type="submit">Register</button>
            <p>Already have an account? <a href="login.php">Login</a></p>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Check if the email is already taken
            $("#email").on("blur", function() {
                var email = $(this).val();
                $.ajax({
                    url: "check_user.php",
                    type: "POST",
                    data: { type: "email", value: email },
                    success: function(response) {
                        if (response == "taken") {
                            $("#email-error").text("Email is already registered").css("color", "red");
                        } else {
                            $("#email-error").text("");
                        }
                    }
                });
            });

            // Check if the username is already taken
            $("#username").on("blur", function() {
                var username = $(this).val();
                $.ajax({
                    url: "check_user.php",
                    type: "POST",
                    data: { type: "username", value: username },
                    success: function(response) {
                        if (response == "taken") {
                            $("#username-error").text("Username is already taken").css("color", "red");
                        } else {
                            $("#username-error").text("");
                        }
                    }
                });
            });

            // Handle form submission
            $("#registerForm").on("submit", function(event) {
                event.preventDefault();  // Prevent form submission

                var email = $("#email").val();
                var username = $("#username").val();
                var password = $("#password").val();
                var confirm_password = $("#confirm_password").val();

                // Check if email and username are available
                if ($("#email-error").text() || $("#username-error").text()) {
                    $("#form-error").html("<p class='error'>Please resolve the issues before submitting.</p>");
                    return false; // Prevent form submission if there are errors
                }

                if (password !== confirm_password) {
                    $("#form-error").html("<p class='error'>Passwords do not match!</p>");
                    return false;
                }

                // If everything is good, submit the form
                this.submit();
            });
        });
    </script>
</body>
</html>
