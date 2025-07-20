<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    error_log('Unauthorized access attempt to search_donors.php');
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$query = $_GET['query'] ?? '';

error_log('Search donors NIC query: ' . $query);

$sql = "SELECT id, donor_name, NIC, blood_group, hospital_id 
        FROM donors 
        WHERE NIC LIKE ? 
        LIMIT 10";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log('Prepare failed: ' . $conn->error);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit();
}

$like_query = "%$query%";
$stmt->bind_param("s", $like_query);
$stmt->execute();
$result = $stmt->get_result();

$donors = [];
while ($row = $result->fetch_assoc()) {
    $donors[] = [
        'id' => $row['id'],
        'donor_name' => $row['donor_name'],
        'NIC' => $row['NIC'],
        'blood_group' => $row['blood_group'],
        'hospital_id' => $row['hospital_id']
    ];
}

error_log('Search donors result count: ' . count($donors));

header('Content-Type: application/json');
echo json_encode(['success' => true, 'data' => $donors]);

$stmt->close();
$conn->close();
?>