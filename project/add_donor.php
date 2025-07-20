<?php
session_start();
include 'config/db.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit();
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

try {
    // Get form data
    $donorName = trim($_POST['donorName'] ?? '');
    $donorNIC = trim($_POST['donorNIC'] ?? '');
    $bloodGroup = trim($_POST['bloodGroup'] ?? '');
    $hospital_id = $_SESSION['user_id'];

    // Validate input
    if (empty($donorName)) {
        echo json_encode(['success' => false, 'error' => 'Donor name is required']);
        exit();
    }

    if (empty($donorNIC) || !preg_match('/^[0-9]{12}$/', $donorNIC)) {
        echo json_encode(['success' => false, 'error' => 'Valid 12-digit NIC is required']);
        exit();
    }

    if (empty($bloodGroup)) {
        echo json_encode(['success' => false, 'error' => 'Blood group is required']);
        exit();
    }

    // Validate blood group
    $valid_blood_groups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
    if (!in_array($bloodGroup, $valid_blood_groups)) {
        echo json_encode(['success' => false, 'error' => 'Invalid blood group']);
        exit();
    }

    // Check if NIC already exists for this hospital
    $check_sql = "SELECT id FROM donors WHERE nic = ? AND hospital_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    if (!$check_stmt) {
        throw new Exception("Database prepare error: " . $conn->error);
    }
    
    $check_stmt->bind_param("si", $donorNIC, $hospital_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'A donor with this NIC already exists']);
        exit();
    }
    $check_stmt->close();

    // Insert new donor
    $insert_sql = "INSERT INTO donors (donor_name, blood_group, nic, hospital_id, created_at) VALUES (?, ?, ?, ?, NOW())";
    $insert_stmt = $conn->prepare($insert_sql);
    
    if (!$insert_stmt) {
        throw new Exception("Database prepare error: " . $conn->error);
    }
    
    $insert_stmt->bind_param("sssi", $donorName, $bloodGroup, $donorNIC, $hospital_id);
    
    if ($insert_stmt->execute()) {
        $new_donor_id = $conn->insert_id;
        echo json_encode([
            'success' => true, 
            'message' => 'Donor added successfully',
            'donor_id' => $new_donor_id
        ]);
    } else {
        throw new Exception("Failed to insert donor: " . $insert_stmt->error);
    }
    
    $insert_stmt->close();

} catch (Exception $e) {
    error_log("Add donor error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>