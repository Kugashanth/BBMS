<?php
session_start();

include 'config/db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$hospital_id = $_SESSION['user_id'];
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$blood_groups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];

$sql = "SELECT bc.*, COALESCE(SUM(bi.units), 0) as total_units 
        FROM blood_camps bc 
        LEFT JOIN blood_inventory bi ON bc.id = bi.camp_id 
        WHERE bc.hospital_id = ?";
$params = [$hospital_id];
$types = "i";

switch ($filter) {
    case 'today':
        $sql .= " AND bc.camp_date = CURDATE()";
        break;
    case 'tomorrow':
        $sql .= " AND bc.camp_date = CURDATE() + INTERVAL 1 DAY";
        break;
    case 'this_week':
        $sql .= " AND bc.camp_date BETWEEN 
                 DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY) AND 
                 DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), INTERVAL 6 DAY)";
        break;
    case 'next_week':
        $sql .= " AND bc.camp_date BETWEEN 
                 DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), INTERVAL 7 DAY) AND 
                 DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), INTERVAL 13 DAY)";
        break;
    case 'this_year':
        $sql .= " AND YEAR(bc.camp_date) = YEAR(CURDATE())";
        break;
    case 'all':
    default:
        // No additional condition
        break;
}

$sql .= " GROUP BY bc.id";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$camps = [];
while ($camp = $result->fetch_assoc()) {
    $sql_group_units = "SELECT blood_group, SUM(units) as units 
                       FROM blood_inventory 
                       WHERE camp_id = ? AND hospital_id = ? 
                       GROUP BY blood_group";
    $stmt_group_units = $conn->prepare($sql_group_units);
    $stmt_group_units->bind_param("ii", $camp['id'], $hospital_id);
    $stmt_group_units->execute();
    $result_group_units = $stmt_group_units->get_result();
    
    $group_units = array_fill_keys($blood_groups, 0);
    while ($row = $result_group_units->fetch_assoc()) {
        $group_units[$row['blood_group']] = $row['units'];
    }
    
    $camp['group_units'] = $group_units;
    $camp['camp_name'] = htmlspecialchars($camp['camp_name']);
    $camp['location'] = htmlspecialchars($camp['location']);
    $camps[] = $camp;
    $stmt_group_units->close();
}

header('Content-Type: application/json');
echo json_encode(['success' => true, 'camps' => $camps]);

$stmt->close();
$conn->close();
?>