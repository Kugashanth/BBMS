<?php
session_start();

include 'config/db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$hospital_id = $_SESSION['user_id'];
$inventory_id = isset($_POST['inventoryId']) ? (int)$_POST['inventoryId'] : 0;
$donor_id = isset($_POST['donorId']) && $_POST['donorId'] !== '' ? (int)$_POST['donorId'] : null;
$blood_group = isset($_POST['bloodGroup']) ? trim($_POST['bloodGroup']) : '';
$units = 1;

error_log("update_blood.php: inventory_id=$inventory_id, donor_id=" . ($donor_id ?? 'null') . ", blood_group=$blood_group, hospital_id=$hospital_id");

// Validate inputs
$valid_blood_groups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
if (!$inventory_id) {
    error_log("Invalid inventory ID: $inventory_id");
    echo json_encode(['success' => false, 'error' => 'Invalid inventory ID']);
    exit();
}
if (!in_array($blood_group, $valid_blood_groups)) {
    error_log("Invalid blood group: $blood_group");
    echo json_encode(['success' => false, 'error' => 'Invalid blood group']);
    exit();
}

// Validate inventory record
$sql = "SELECT camp_id FROM blood_inventory WHERE id = ? AND hospital_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $inventory_id, $hospital_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    error_log("Inventory ID $inventory_id not found for hospital $hospital_id");
    echo json_encode(['success' => false, 'error' => 'Invalid inventory record']);
    exit();
}
$camp_id = $result->fetch_assoc()['camp_id'];
$stmt->close();

// Validate donor
if ($donor_id) {
    $sql = "SELECT blood_group FROM donors WHERE id = ? AND hospital_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $donor_id, $hospital_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        error_log("Invalid donor ID: $donor_id");
        echo json_encode(['success' => false, 'error' => 'Invalid donor']);
        exit();
    }
    $donor = $result->fetch_assoc();
    if ($donor['blood_group'] !== $blood_group) {
        error_log("Blood group mismatch: donor={$donor['blood_group']}, input=$blood_group");
        echo json_encode(['success' => false, 'error' => 'Blood group does not match donor']);
        exit();
    }
    $stmt->close();

    // Check 4-month restriction
    $sql = "SELECT MAX(updated_at) as last_donation FROM blood_inventory WHERE donor_id = ? AND hospital_id = ? AND id != ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $donor_id, $hospital_id, $inventory_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $last_donation = $result->fetch_assoc()['last_donation'];
    $stmt->close();
    if ($last_donation) {
        $last_donation_date = new DateTime($last_donation);
        $today_date = new DateTime($today);
        $interval = $last_donation_date->diff($today_date);
        $days = $interval->days;
        if ($days < 120) {
            error_log("Donor $donor_id last donated on $last_donation, $days days ago");
            echo json_encode(['success' => false, 'error' => '4 months is not over']);
            exit();
        }
    }
}

// Update inventory
$sql = "UPDATE blood_inventory 
        SET blood_group = ?, units = ?, donor_id = ?, updated_at = NOW() 
        WHERE id = ? AND hospital_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("siiii", $blood_group, $units, $donor_id, $inventory_id, $hospital_id);
if (!$stmt->execute()) {
    error_log("Update failed: " . $stmt->error);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $stmt->error]);
    exit();
}

header('Content-Type: application/json');
echo json_encode(['success' => true]);

$stmt->close();
$conn->close();
?>