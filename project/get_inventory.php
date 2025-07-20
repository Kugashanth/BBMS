<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$hospital_id = $_GET['hospital_id'] ?? 0;
$camp_id = $_GET['camp_id'] ?? 0;

if (!$hospital_id || !$camp_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit();
}

$sql = "SELECT bi.id, bi.blood_group, bi.units, bi.donor_id, bi.updated_at, 
               d.donor_name, d.NIC AS donor_nic, d.hospital_id AS donor_hospital_id
        FROM blood_inventory bi
        LEFT JOIN donors d ON bi.donor_id = d.id
        WHERE bi.camp_id = ? AND bi.hospital_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log('Prepare failed: ' . $conn->error);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit();
}

$stmt->bind_param("ii", $camp_id, $hospital_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode(['success' => true, 'data' => $data]);

$stmt->close();
$conn->close();
?>