<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit();
}

$hospital_id = $_SESSION['user_id'];
$camp_id = $_POST['campId'] ?? '';

if (empty($camp_id)) {
    echo json_encode(['success' => false, 'error' => 'Camp ID is required']);
    exit();
}

try {
    // Check for associated inventory records
    $sql_check = "SELECT COUNT(*) as count FROM blood_inventory WHERE camp_id = ? AND hospital_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    if (!$stmt_check) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }
    $stmt_check->bind_param("ii", $camp_id, $hospital_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row = $result_check->fetch_assoc();

    if ($row['count'] > 0) {
        echo json_encode(['success' => false, 'error' => 'Cannot delete camp with associated blood inventory']);
        exit();
    }

    // Proceed with deletion
    $sql = "DELETE FROM blood_camps WHERE id = ? AND hospital_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }
    $stmt->bind_param("ii", $camp_id, $hospital_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No camp found or unauthorized']);
    }

    $stmt_check->close();
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
?>