<?php
session_start();
include 'config/db.php';

// Ensure no output before JSON
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

$response = ['success' => false, 'units' => 0, 'error' => ''];

if (!isset($_SESSION['user_id'])) {
    $response['error'] = 'Unauthorized access.';
    echo json_encode($response);
    exit;
}

$hospital_id = $_GET['hospital_id'] ?? 0;
$blood_group = $_GET['blood_group'] ?? '';

$valid_blood_groups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
if (!in_array($blood_group, $valid_blood_groups) || empty($hospital_id)) {
    $response['error'] = 'Invalid hospital ID or blood group.';
    echo json_encode($response);
    exit;
}

$sql = "SELECT SUM(units) as total_units 
        FROM blood_inventory 
        WHERE hospital_id = ? AND blood_group = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    $response['error'] = 'Database error: ' . $conn->error;
    echo json_encode($response);
    exit;
}
$stmt->bind_param("is", $hospital_id, $blood_group);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$response['success'] = true;
$response['units'] = $row['total_units'] ?? 0;

$stmt->close();
$conn->close();

echo json_encode($response);
exit;
?>