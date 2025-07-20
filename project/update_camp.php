<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit();
}

$hospital_id = $_SESSION['user_id'];
$camp_id = $_POST['campId'] ?? '';
$camp_name = trim(htmlspecialchars($_POST['campName'] ?? ''));
$camp_location = trim(htmlspecialchars($_POST['campLocation'] ?? ''));
$camp_date = $_POST['campDate'] ?? '';

if (empty($camp_id) || empty($camp_name) || empty($camp_location) || empty($camp_date)) {
    echo json_encode(['success' => false, 'error' => 'All fields are required']);
    exit();
}

// Validate date format
if (!DateTime::createFromFormat('Y-m-d', $camp_date)) {
    echo json_encode(['success' => false, 'error' => 'Invalid date format']);
    exit();
}

// Validate date is not in the past
$today = date('Y-m-d');
if ($camp_date < $today) {
    echo json_encode(['success' => false, 'error' => 'Camp date cannot be in the past']);
    exit();
}

try {
    $sql = "UPDATE blood_camps SET camp_name = ?, location = ?, camp_date = ? WHERE id = ? AND hospital_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }
    $stmt->bind_param("sssii", $camp_name, $camp_location, $camp_date, $camp_id, $hospital_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No camp found or no changes made']);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
?>