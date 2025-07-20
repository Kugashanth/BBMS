<?php
session_start();
include 'config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized: Session not found']);
    exit;
}

if (!isset($_POST['donorId'])) {
    echo json_encode(['success' => false, 'error' => 'Donor ID is required']);
    exit;
}

$donor_id = intval($_POST['donorId']);
$hospital_id = $_SESSION['user_id'];

// Log request
error_log("Delete Donor: donor_id=$donor_id, hospital_id=$hospital_id");

// Verify donor belongs to hospital
$sql_check = "SELECT id FROM donors WHERE id = ? AND hospital_id = ?";
$stmt_check = $conn->prepare($sql_check);
if (!$stmt_check) {
    error_log("Prepare failed (check): " . $conn->error);
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

// Check for foreign key constraints (e.g., blood_inventory)
$sql_constraint = "SELECT COUNT(*) as count FROM blood_inventory WHERE donor_id = ?";
$stmt_constraint = $conn->prepare($sql_constraint);
if ($stmt_constraint) {
    $stmt_constraint->bind_param("i", $donor_id);
    $stmt_constraint->execute();
    $result = $stmt_constraint->get_result()->fetch_assoc();
    if ($result['count'] > 0) {
        error_log("Cannot delete donor due to blood inventory records: donor_id=$donor_id");
        echo json_encode(['success' => false, 'error' => 'Cannot delete donor: Associated blood inventory records exist']);
        $stmt_constraint->close();
        exit;
    }
    $stmt_constraint->close();
} else {
    error_log("Prepare failed (constraint check): " . $conn->error);
}

// Delete donor
$sql_delete = "DELETE FROM donors WHERE id = ? AND hospital_id = ?";
$stmt_delete = $conn->prepare($sql_delete);
if (!$stmt_delete) {
    error_log("Prepare failed (delete): " . $conn->error);
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit;
}
$stmt_delete->bind_param("ii", $donor_id, $hospital_id);

if ($stmt_delete->execute()) {
    error_log("Donor deleted successfully: donor_id=$donor_id");
    echo json_encode(['success' => true]);
} else {
    error_log("Delete failed: " . $stmt_delete->error);
    echo json_encode(['success' => false, 'error' => 'Failed to delete donor: ' . $stmt_delete->error]);
}

$stmt_delete->close();
$conn->close();
?>