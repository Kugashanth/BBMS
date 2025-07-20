<?php
session_start();
include 'config/db.php';

// Ensure no output before JSON
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

$response = ['success' => false, 'error' => ''];

if (!isset($_SESSION['user_id'])) {
    $response['error'] = 'Unauthorized access.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['error'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

$requesting_hospital_id = $_POST['requestingHospitalId'] ?? '';
$requested_hospital_id = $_POST['requestedHospitalId'] ?? '';
$blood_group = $_POST['bloodGroup'] ?? '';
$units = isset($_POST['units']) ? (int)$_POST['units'] : 0;

// Validate inputs
$valid_blood_groups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
if (!in_array($blood_group, $valid_blood_groups)) {
    $response['error'] = 'Invalid blood group.';
    echo json_encode($response);
    exit;
}

if ($units <= 0) {
    $response['error'] = 'Units must be a positive number.';
    echo json_encode($response);
    exit;
}

if (empty($requesting_hospital_id) || empty($requested_hospital_id)) {
    $response['error'] = 'Hospital IDs are required.';
    echo json_encode($response);
    exit;
}

// Check if hospitals exist
$sql_check_hospitals = "SELECT id FROM hospitals WHERE id IN (?, ?)";
$stmt_check_hospitals = $conn->prepare($sql_check_hospitals);
if (!$stmt_check_hospitals) {
    $response['error'] = 'Database error: ' . $conn->error;
    echo json_encode($response);
    exit;
}
$stmt_check_hospitals->bind_param("ii", $requesting_hospital_id, $requested_hospital_id);
$stmt_check_hospitals->execute();
$result_check_hospitals = $stmt_check_hospitals->get_result();
if ($result_check_hospitals->num_rows !== 2) {
    $response['error'] = 'One or both hospitals do not exist.';
    echo json_encode($response);
    exit;
}
$stmt_check_hospitals->close();

// Check blood group availability and units
$sql_check_inventory = "SELECT SUM(units) as total_units 
                       FROM blood_inventory 
                       WHERE hospital_id = ? AND blood_group = ?";
$stmt_check_inventory = $conn->prepare($sql_check_inventory);
if (!$stmt_check_inventory) {
    $response['error'] = 'Database error: ' . $conn->error;
    echo json_encode($response);
    exit;
}
$stmt_check_inventory->bind_param("is", $requested_hospital_id, $blood_group);
$stmt_check_inventory->execute();
$result_inventory = $stmt_check_inventory->get_result();
$inventory = $result_inventory->fetch_assoc();
$available_units = $inventory['total_units'] ?? 0;
$stmt_check_inventory->close();

if ($available_units <= 0) {
    $response['error'] = 'blood_group_unavailable';
    echo json_encode($response);
    exit;
}

if ($units > $available_units) {
    $response['error'] = 'insufficient_units';
    echo json_encode($response);
    exit;
}

// Insert request
$sql_insert = "INSERT INTO blood_requests (requesting_hospital_id, requested_hospital_id, blood_group, unit, status) 
               VALUES (?, ?, ?, ?, 'Pending')";
$stmt_insert = $conn->prepare($sql_insert);
if (!$stmt_insert) {
    $response['error'] = 'Database error: ' . $conn->error;
    echo json_encode($response);
    exit;
}
$stmt_insert->bind_param("iisi", $requesting_hospital_id, $requested_hospital_id, $blood_group, $units);

if ($stmt_insert->execute()) {
    $response['success'] = true;
} else {
    $response['error'] = 'Error saving request: ' . $conn->error;
}

$stmt_insert->close();
$conn->close();

echo json_encode($response);
exit;
?>