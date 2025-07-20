<?php
session_start();
include 'config/db.php';

// Ensure no output before JSON
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ob_start();
header('Content-Type: application/json; charset=utf-8');

$response = ['success' => false, 'hospitals' => []];

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized access.');
    }

    $hospital_id = $_SESSION['user_id'];
    $location = $_GET['location'] ?? '';
    $blood_group = $_GET['bloodGroup'] ?? '';
    $sort = $_GET['sort'] ?? 'name_asc';

    // Validate inputs
    $valid_blood_groups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
    if ($blood_group && !in_array($blood_group, $valid_blood_groups)) {
        throw new Exception('Invalid blood group: ' . htmlspecialchars($blood_group));
    }
    $valid_sorts = ['name_asc', 'name_desc', 'units_asc', 'units_desc'];
    if (!in_array($sort, $valid_sorts)) {
        throw new Exception('Invalid sort option: ' . htmlspecialchars($sort));
    }

    // Build query
    $sql = "SELECT h.id, h.name, COALESCE(h.location, 'Unknown') AS location, 
            COALESCE(SUM(bi.units), 0) AS total_units 
            FROM hospitals h 
            LEFT JOIN blood_inventory bi ON h.id = bi.hospital_id";
    $params = [$hospital_id];
    $types = "i";

    $where_clauses = ["h.id != ?"];
    if ($location) {
        $where_clauses[] = "LOWER(COALESCE(h.location, 'Unknown')) = LOWER(?)";
        $params[] = $location;
        $types .= "s";
    }
    if ($blood_group) {
        $where_clauses[] = "EXISTS (
            SELECT 1 FROM blood_inventory bi2 
            WHERE bi2.hospital_id = h.id 
            AND bi2.blood_group = ? 
            AND bi2.units > 0
        )";
        $params[] = $blood_group;
        $types .= "s";
    }

    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(" AND ", $where_clauses);
    }

    $sql .= " GROUP BY h.id, h.name, h.location";

    $sort_sql = [
        'name_asc' => 'h.name ASC',
        'name_desc' => 'h.name DESC',
        'units_asc' => 'total_units ASC, h.name ASC',
        'units_desc' => 'total_units DESC, h.name ASC'
    ];
    $sql .= " ORDER BY " . $sort_sql[$sort];

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }
    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) {
        throw new Exception('Database execute error: ' . $stmt->error);
    }
    $result = $stmt->get_result();

    $hospitals = [];
    while ($hospital = $result->fetch_assoc()) {
        $sql_inventory = "SELECT blood_group, COALESCE(SUM(units), 0) as total_units 
                         FROM blood_inventory 
                         WHERE hospital_id = ?";
        if ($blood_group) {
            $sql_inventory .= " AND blood_group = ?";
            $stmt_inventory = $conn->prepare($sql_inventory);
            $stmt_inventory->bind_param("is", $hospital['id'], $blood_group);
        } else {
            $sql_inventory .= " GROUP BY blood_group";
            $stmt_inventory = $conn->prepare($sql_inventory);
            $stmt_inventory->bind_param("i", $hospital['id']);
        }
        if (!$stmt_inventory) {
            throw new Exception('Inventory prepare error: ' . $conn->error);
        }
        if (!$stmt_inventory->execute()) {
            throw new Exception('Inventory execute error: ' . $stmt_inventory->error);
        }
        $result_inventory = $stmt_inventory->get_result();

        $inventory = array_fill_keys($valid_blood_groups, 0);
        while ($row = $result_inventory->fetch_assoc()) {
            $inventory[$row['blood_group']] = $row['total_units'];
        }

        $hospital['inventory'] = $inventory;
        $hospitals[] = $hospital;
        $stmt_inventory->close();
    }

    $stmt->close();
    $response['success'] = true;
    $response['hospitals'] = $hospitals;

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
    error_log('Filter hospitals failed: ' . $e->getMessage());
}

$conn->close();
echo json_encode($response);
ob_end_flush();
exit;
?>