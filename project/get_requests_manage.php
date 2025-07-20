<?php
session_start();
include 'config/db.php';

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ob_start();
header('Content-Type: application/json; charset=utf-8');

$response = ['success' => false, 'requests' => [], 'inventory' => []];

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized access.');
    }

    $hospital_id = $_GET['hospital_id'] ?? $_SESSION['user_id'];
    $filter_hospital = $_GET['hospital'] ?? '';
    $blood_group = $_GET['bloodGroup'] ?? '';
    $status = $_GET['status'] ?? '';
    $last_created = $_GET['lastCreated'] ?? 0;

    // Ensure blood_inventory has entries for all blood groups
    $sql_init_inventory = "INSERT IGNORE INTO blood_inventory (hospital_id, blood_group, units)
                          SELECT ?, bg.blood_group, 0
                          FROM (
                              SELECT 'A+' AS blood_group UNION SELECT 'A-' UNION SELECT 'B+' UNION SELECT 'B-'
                              UNION SELECT 'O+' UNION SELECT 'O-' UNION SELECT 'AB+' UNION SELECT 'AB-'
                          ) bg
                          WHERE NOT EXISTS (
                              SELECT 1 FROM blood_inventory 
                              WHERE hospital_id = ? AND blood_group = bg.blood_group
                          )";
    $stmt_init = $conn->prepare($sql_init_inventory);
    if (!$stmt_init) {
        error_log("Init inventory prepare failed: " . $conn->error);
        throw new Exception('Failed to initialize inventory.');
    }
    $stmt_init->bind_param("ii", $hospital_id, $hospital_id);
    $stmt_init->execute();
    $stmt_init->close();
    error_log("Initialized inventory for hospital_id: $hospital_id");

    // Remove duplicate inventory rows
    $sql_clean_inventory = "DELETE bi1 FROM blood_inventory bi1
                           INNER JOIN blood_inventory bi2
                           WHERE bi1.id < bi2.id
                           AND bi1.hospital_id = bi2.hospital_id
                           AND bi1.blood_group = bi2.blood_group";
    $conn->query($sql_clean_inventory);
    error_log("Cleaned duplicate inventory rows for hospital_id: $hospital_id");

    // Fetch inventory, grouped by blood_group
    $sql_inventory = "SELECT blood_group, COALESCE(SUM(units), 0) AS units 
                     FROM blood_inventory 
                     WHERE hospital_id = ? 
                     GROUP BY blood_group 
                     ORDER BY blood_group";
    $stmt_inventory = $conn->prepare($sql_inventory);
    if (!$stmt_inventory) {
        throw new Exception('Inventory prepare error: ' . $conn->error);
    }
    $stmt_inventory->bind_param("i", $hospital_id);
    $stmt_inventory->execute();
    $result_inventory = $stmt_inventory->get_result();
    $inventory = [];
    while ($row = $result_inventory->fetch_assoc()) {
        $inventory[] = $row;
        error_log("Fetched inventory: Blood Group: {$row['blood_group']}, Units: {$row['units']}");
    }
    $stmt_inventory->close();

    // Fetch requests, ensuring unique IDs
    $sql = "SELECT DISTINCT br.id, br.requesting_hospital_id, h.name AS hospital_name, 
                   br.blood_group, br.unit AS requested_units, br.status, br.created_at, 
                   UNIX_TIMESTAMP(br.created_at) AS created_at_ts,
                   COALESCE(bi.units, 0) AS available_units
            FROM blood_requests br
            INNER JOIN hospitals h ON br.requesting_hospital_id = h.id
            LEFT JOIN blood_inventory bi ON bi.hospital_id = br.requested_hospital_id 
            AND bi.blood_group = br.blood_group
            WHERE br.requested_hospital_id = ?
            GROUP BY br.id";
    $params = [$hospital_id];
    $types = "i";

    $where_clauses = [];
    if ($filter_hospital) {
        $where_clauses[] = "br.requesting_hospital_id = ?";
        $params[] = $filter_hospital;
        $types .= "i";
    }
    if ($blood_group) {
        $where_clauses[] = "br.blood_group = ?";
        $params[] = $blood_group;
        $types .= "s";
    }
    if ($status) {
        $where_clauses[] = "br.status = ?";
        $params[] = $status;
        $types .= "s";
    }

    if (!empty($where_clauses)) {
        $sql .= " AND " . implode(" AND ", $where_clauses);
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }
    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) {
        throw new Exception('Database execute error: ' . $stmt->error);
    }
    $result = $stmt->get_result();

    $requests = [];
    $max_created = $last_created;
    while ($request = $result->fetch_assoc()) {
        $requests[] = $request;
        $max_created = max($max_created, $request['created_at_ts']);
        error_log("Fetched request ID: {$request['id']}, Hospital: {$request['hospital_name']}, Blood Group: {$request['blood_group']}, Requested Units: {$request['requested_units']}, Available Units: {$request['available_units']}");
    }

    $stmt->close();
    $response['success'] = true;
    $response['requests'] = $requests;
    $response['inventory'] = $inventory;
    $response['lastCreated'] = $max_created;

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
    error_log('Get requests failed: ' . $e->getMessage() . ' | Hospital ID: ' . ($hospital_id ?? 'N/A'));
}

$conn->close();
echo json_encode($response);
ob_end_flush();
exit;
?>