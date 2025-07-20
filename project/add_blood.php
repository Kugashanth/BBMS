<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$hospital_id = $_POST['hospitalId'] ?? 0;
$camp_id = $_POST['campId'] ?? 0;
$donor_id = $_POST['donorId'] ?? 0;
$blood_group = $_POST['bloodGroup'] ?? '';
$units = $_POST['units'] ?? 1;

if (!$hospital_id || !$camp_id || !$blood_group) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit();
}

$valid_blood_groups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
if (!in_array($blood_group, $valid_blood_groups)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid blood group']);
    exit();
}

// Validate camp belongs to hospital
$sql_camp = "SELECT id FROM blood_camps WHERE id = ? AND hospital_id = ?";
$stmt_camp = $conn->prepare($sql_camp);
$stmt_camp->bind_param("ii", $camp_id, $hospital_id);
$stmt_camp->execute();
if ($stmt_camp->get_result()->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid camp']);
    $stmt_camp->close();
    exit();
}
$stmt_camp->close();

// If donor_id provided, validate donor and 4-month gap
if ($donor_id) {
    // Check if donor exists
    $sql_donor = "SELECT id, blood_group FROM donors WHERE id = ?";
    $stmt_donor = $conn->prepare($sql_donor);
    $stmt_donor->bind_param("i", $donor_id);
    $stmt_donor->execute();
    $donor_result = $stmt_donor->get_result();
    if ($donor_result->num_rows === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid donor']);
        $stmt_donor->close();
        exit();
    }
    $donor = $donor_result->fetch_assoc();
    if ($donor['blood_group'] !== $blood_group) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Blood group mismatch']);
        $stmt_donor->close();
        exit();
    }
    $stmt_donor->close();

    // Check 4-month donation gap (120 days)
    $sql_last_donation = "SELECT updated_at 
                         FROM blood_inventory 
                         WHERE donor_id = ? 
                         ORDER BY updated_at DESC 
                         LIMIT 1";
    $stmt_last_donation = $conn->prepare($sql_last_donation);
    $stmt_last_donation->bind_param("i", $donor_id);
    $stmt_last_donation->execute();
    $last_donation_result = $stmt_last_donation->get_result();
    if ($last_donation_result->num_rows > 0) {
        $last_donation = $last_donation_result->fetch_assoc()['updated_at'];
        $last_donation_date = new DateTime($last_donation);
        $today = new DateTime('2025-05-18');
        $interval = $today->diff($last_donation_date);
        $days = $interval->days;
        if ($days < 120) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Donor must wait 4 months since last donation (' . $last_donation . ')']);
            $stmt_last_donation->close();
            exit();
        }
    }
    $stmt_last_donation->close();
}

// Insert blood inventory
$sql_insert = "INSERT INTO blood_inventory (blood_group, units, hospital_id, camp_id, donor_id, updated_at) 
               VALUES (?, ?, ?, ?, ?, NOW())";
$stmt_insert = $conn->prepare($sql_insert);
if (!$stmt_insert) {
    error_log('Prepare failed: ' . $conn->error);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit();
}

$stmt_insert->bind_param("siiii", $blood_group, $units, $hospital_id, $camp_id, $donor_id);
if ($stmt_insert->execute()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} else {
    error_log('Execute failed: ' . $stmt_insert->error);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Error adding blood']);
}

$stmt_insert->close();
$conn->close();
?>