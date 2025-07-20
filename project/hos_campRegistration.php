<?php
//session_start();
include 'navbar.php';
include 'sidebar.php';
include 'config/db.php';



// Enable error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php_errors.log');

// Check database connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    header('Location: register_camp.php?error=Database connection failed');
    exit;
}

// Check if hospital is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    error_log("No user_id in session, redirecting to login.php");
    header('Location: login.php?error=Please login to continue');
    exit;
}

// Verify hospital existence
$hospital_id = (int)$_SESSION['user_id'];
$sql = "SELECT id FROM hospitals WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Prepare failed for hospital check: " . $conn->error);
    header('Location: login.php?error=Session error');
    exit;
}
$stmt->bind_param("i", $hospital_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    error_log("Hospital not found for ID: $hospital_id");
    session_unset();
    session_destroy();
    header('Location: login.php?error=Invalid session');
    exit;
}
$stmt->close();

// Check if blood_camps table exists
$sql = "SHOW TABLES LIKE 'blood_camps'";
$result = $conn->query($sql);
if ($result->num_rows === 0) {
    error_log("Table blood_camps does not exist");
    header('Location: register_camp.php?error=Table blood_camps missing');
    exit;
}

// Verify table schema (check if required columns exist)
$sql = "DESCRIBE blood_camps";
$result = $conn->query($sql);
$required_columns = ['id', 'camp_name', 'camp_date', 'location', 'hospital_id'];
$found_columns = [];
while ($row = $result->fetch_assoc()) {
    $found_columns[] = $row['Field'];
}
foreach ($required_columns as $col) {
    if (!in_array($col, $found_columns)) {
        error_log("Missing column $col in blood_camps table");
        header('Location: register_camp.php?error=Missing table column: ' . $col);
        exit;
    }
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Sanitization function
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "Invalid CSRF token";
        error_log("CSRF token validation failed");
    } else {
        $camp_name = sanitize($_POST['camp_name'] ?? '');
        $camp_date = sanitize($_POST['camp_date'] ?? '');
        $location = sanitize($_POST['location'] ?? '');
        $hospital_id = (int)$_SESSION['user_id'];

        // Validation
        if (empty($camp_name)) {
            $errors[] = "Camp name is required";
        } elseif (strlen($camp_name) > 255) {
            $errors[] = "Camp name must be less than 255 characters";
        }

        if (empty($camp_date)) {
            $errors[] = "Camp date is required";
        } elseif (strtotime($camp_date) < strtotime(date('Y-m-d'))) {
            $errors[] = "Camp date cannot be in the past";
        }

        if (empty($location)) {
            $errors[] = "Location is required";
        } elseif (strlen($location) > 255) {
            $errors[] = "Location must be less than 255 characters";
        }

        if (empty($errors)) {
            // Log insertion attempt
            error_log("Inserting: camp_name='$camp_name', camp_date='$camp_date', location='$location', hospital_id=$hospital_id");

            $sql = "INSERT INTO blood_camps (camp_name, camp_date, location, hospital_id) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                $errors[] = "Failed to prepare query: " . $conn->error;
                error_log("Prepare failed: " . $conn->error);
            } else {
                $stmt->bind_param("sssi", $camp_name, $camp_date, $location, $hospital_id);
                if ($stmt->execute()) {
                    $success = "Blood camp registered successfully!";
                    $_POST = [];
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    error_log("Inserted successfully, ID: " . $stmt->insert_id);
                } else {
                    $errors[] = "Failed to insert data: " . $stmt->error;
                    error_log("Insert failed: " . $stmt->error);
                }
                $stmt->close();
            }
        } else {
            error_log("Validation errors: " . implode(", ", $errors));
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Camp Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
      
      body
      {
            padding-top:100px;
      }
        .form-container {
            max-width: 32rem;
            margin: 3rem auto;
            padding: 2rem;
            background: #ffffff;
            border-radius: 0.75rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .form-heading {
            font-size: 1.875rem;
            font-weight: 700;
            color: #dc2626;
            text-align: center;
            margin-bottom: 2rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            display: block;
            margin-bottom: 0.5rem;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 1rem;
        }
        .form-group input:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }
        .error {
            color: #dc3545;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }
        .success {
            background: #22c55e;
            color: #ffffff;
            padding: 1rem;
            border-radius: 0.375rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .submit-btn {
            width: 100%;
            background: #dc2626;
            color: #ffffff;
            padding: 0.75rem;
            border-radius: 0.375rem;
            font-size: 1rem;
            font-weight: 500;
        }
        .submit-btn:hover {
            background: #b91c1c;
        }
        @media (max-width: 640px) {
            .form-container {
                margin: 1.5rem;
                padding: 1.5rem;
            }
            .form-heading {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body >
    <div class="form-container">
        <h1 class="form-heading">Register New Blood Camp</h1>

        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-group">
                <label for="camp_name">Camp Name</label>
                <input type="text" id="camp_name" name="camp_name" value="<?php echo isset($_POST['camp_name']) ? htmlspecialchars($_POST['camp_name']) : ''; ?>" maxlength="255" required>
            </div>

            <div class="form-group">
                <label for="camp_date">Camp Date</label>
                <input type="date" id="camp_date" name="camp_date" value="<?php echo isset($_POST['camp_date']) ? htmlspecialchars($_POST['camp_date']) : ''; ?>" min="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" id="location" name="location" value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>" maxlength="255" required>
            </div>

            <button type="submit" class="submit-btn">Register Camp</button>
        </form>
    </div>
</body>
</html>
 <?php include 'footer.php'; ?>