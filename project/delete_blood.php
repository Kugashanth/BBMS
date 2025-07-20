<?php
session_start();

include 'config/db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$hospital_id = $_SESSION['user_id'];
$inventory_id = isset($_POST['inventoryId']) ? (int)$_POST['inventoryId'] : 0;

error_log("delete_blood.php: inventory_id=$inventory_id, hospital_id=$hospital_id");

if (!$inventory_id) {
    error_log("Invalid inventory ID: $inventory_id");
    echo json_encode(['success' => false, 'error' => 'Invalid inventory ID']);
    exit();
}

// Validate inventory record
$sql = "SELECT id FROM blood_inventory WHERE id = ? AND hospital_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $inventory_id, $hospital_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    error_log("Inventory ID $inventory_id not found for hospital $hospital_id");
    echo json_encode(['success' => false, 'error' => 'Invalid inventory record']);
    exit();
}
$stmt->close();

// Delete record
$sql = "DELETE FROM blood_inventory WHERE id = ? AND hospital_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $inventory_id, $hospital_id);
if (!$stmt->execute()) {
    error_log("Delete failed: " . $stmt->error);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $stmt->error]);
    exit();
}

header('Content-Type: application/json');
echo json_encode(['success' => true]);

$stmt->close();
$conn->close();
?>