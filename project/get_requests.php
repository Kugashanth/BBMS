<?php
session_start();
include 'config/db.php';

$response = ['success' => false, 'requests' => []];

if (!isset($_SESSION['user_id'])) {
    $response['error'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

$hospital_id = $_GET['hospital_id'] ?? 0;

$sql = "SELECT br.id, br.blood_group, br.unit, br.status, br.created_at, h.name as hospital_name
        FROM blood_requests br
        JOIN hospitals h ON br.requested_hospital_id = h.id
        WHERE br.requesting_hospital_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $hospital_id);
$stmt->execute();
$result = $stmt->get_result();

$requests = [];
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}

$response['success'] = true;
$response['requests'] = $requests;

$stmt->close();
$conn->close();
echo json_encode($response);
?>