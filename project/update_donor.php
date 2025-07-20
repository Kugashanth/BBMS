<?php
session_start();
include 'config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized: Session not found']);
    exit;
}

if (!isset($_POST['donorId']) || !isset($_POST['donorName']) || !isset($_POST['donorNIC']) || !isset($_POST['bloodGroup'])) {
    echo json_encode(['success' => false, 'error' => 'All fields are required']);
    exit;
}

$donor_id = intval($_POST['donorId']);
$donor_name = trim($_POST['donorName']);
$donor_nic = trim($_POST['donorNIC']);
$blood_group = $_POST['bloodGroup'];
$hospital_id = $_SESSION['user_id'];

$blood_groups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];

// Log request
error_log("Update Donor: donor_id=$donor_id, name=$donor_name, nic=$donor_nic, blood_group=$blood_group, hospital_id=$hospital_id");

// Validate inputs
if (empty($donor_name)) {
    echo json_encode(['success' => false, 'error' => 'Donor name is required']);
    exit;
}
if (!preg_match('/^[0-9]{12}$/', $donor_nic)) {
    echo json_encode(['success' => false, 'error' => 'NIC must be 12 digits']);
    exit;
}
if (!in_array($blood_group, $blood_groups)) {
    echo json_encode(['success' => false, 'error' => 'Invalid blood group']);
    exit;
}

// Check if NIC is unique (excluding current donor)
$sql_check_nic = "SELECT id FROM donors WHERE nic = ? AND id != ? AND hospital_id = ?";
$stmt_check_nic = $conn->prepare($sql_check_nic);
if (!$stmt_check_nic) {
    error_log("Prepare failed (check NIC): " . $conn->error);
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit;
}
$stmt_check_nic->bind_param("sii", $donor_nic, $donor_id, $hospital_id);
$stmt_check_nic->execute();
if ($stmt_check_nic->get_result()->num_rows > 0) {
    error_log("NIC already exists: nic=$donor_nic, donor_id=$donor_id");
    echo json_encode(['success' => false, 'error' => 'NIC already exists']);
    $stmt_check_nic->close();
    exit;
}
$stmt_check_nic->close();

// Verify donor belongs to hospital
$sql_check = "SELECT id FROM donors WHERE id = ? AND hospital_id = ?";
$stmt_check = $conn->prepare($sql_check);
if (!$stmt_check) {
    error_log("Prepare failed (check donor): " . $conn->error);
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit;
}
$stmt_check->bind_param("ii", $donor_id, $hospital_id);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows === 0) {
    error_log("Donor not found or unauthorized: donor_id=$donor_id, hospital_id=$hospital_id");
    echo json_encode(['success' => false, 'error' => 'Donor not found or unauthorized']);
    $stmt_check->close();
    exit;
}
$stmt_check->close();

// Update donor
$sql_update = "UPDATE donors SET donor_name = ?, blood_group = ?, nic = ? WHERE id = ? AND hospital_id = ?";
$stmt_update = $conn->prepare($sql_update);
if (!$stmt_update) {
    error_log("Prepare failed (update): " . $conn->error);
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit;
}
$stmt_update->bind_param("sssii", $donor_name, $blood_group, $donor_nic, $donor_id, $hospital_id);

if ($stmt_update->execute()) {
    error_log("Donor updated successfully: donor_id=$donor_id");
    echo json_encode(['success' => true]);
} else {
    error_log("Update failed: " . $stmt_update->error);
    echo json_encode(['success' => false, 'error' => 'Failed to update donor: ' . $stmt_update->error]);
}

$stmt_update->close();
$conn->close();
?>