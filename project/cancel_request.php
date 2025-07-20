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
    error_log('Cancel request failed: Unauthorized access');
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['error'] = 'Invalid request method.';
    error_log('Cancel request failed: Invalid request method');
    echo json_encode($response);
    exit;
}

$request_id = $_POST['request_id'] ?? 0;
if (!is_numeric($request_id) || $request_id <= 0) {
    $response['error'] = 'Invalid request ID.';
    error_log('Cancel request failed: Invalid request ID - ' . $request_id);
    echo json_encode($response);
    exit;
}

// Verify request exists, belongs to the hospital, and is pending
$sql_check = "SELECT status FROM blood_requests WHERE id = ? AND requesting_hospital_id = ?";
$stmt_check = $conn->prepare($sql_check);
if (!$stmt_check) {
    $response['error'] = 'Database error: ' . $conn->error;
    error_log('Cancel request failed: Database error - ' . $conn->error);
    echo json_encode($response);
    exit;
}
$stmt_check->bind_param("ii", $request_id, $_SESSION['user_id']);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 0) {
    $response['error'] = 'Request not found or unauthorized.';
    error_log('Cancel request failed: Request ID ' . $request_id . ' not found or unauthorized for user ' . $_SESSION['user_id']);
    echo json_encode($response);
    exit;
}

$row = $result_check->fetch_assoc();
if ($row['status'] !== 'Pending') {
    $response['error'] = 'Only pending requests can be cancelled.';
    error_log('Cancel request failed: Request ID ' . $request_id . ' is not Pending, status: ' . $row['status']);
    echo json_encode($response);
    exit;
}
$stmt_check->close();

// Delete request
$sql_delete = "DELETE FROM blood_requests WHERE id = ? AND requesting_hospital_id = ?";
$stmt_delete = $conn->prepare($sql_delete);
if (!$stmt_delete) {
    $response['error'] = 'Database error: ' . $conn->error;
    error_log('Cancel request failed: Database error on delete - ' . $conn->error);
    echo json_encode($response);
    exit;
}
$stmt_delete->bind_param("ii", $request_id, $_SESSION['user_id']);

if ($stmt_delete->execute()) {
    if ($stmt_delete->affected_rows > 0) {
        $response['success'] = true;
        error_log('Cancel request succeeded: Request ID ' . $request_id . ' deleted by user ' . $_SESSION['user_id']);
    } else {
        $response['error'] = 'Request not found or already deleted.';
        error_log('Cancel request failed: Request ID ' . $request_id . ' not found or already deleted');
    }
} else {
    $response['error'] = 'Error cancelling request: ' . $conn->error;
    error_log('Cancel request failed: Error deleting request ID ' . $request_id . ' - ' . $conn->error);
}
$stmt_delete->close();

$conn->close();
echo json_encode($response);
exit;
?>