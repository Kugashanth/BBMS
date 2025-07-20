<?php
include 'config/db.php';

$blood_group = $_GET['blood_group'] ?? '';
$province = $_GET['province'] ?? '';
$district = $_GET['district'] ?? '';

$query = "SELECT 
    blood_inventory.blood_group, 
    blood_inventory.units, 
    hospitals.name AS hospital_name,
    hospitals.province AS province, 
    hospitals.district AS district
FROM 
    blood_inventory 
JOIN 
    hospitals ON blood_inventory.hospital_id = hospitals.id
WHERE 1";

// Apply filters dynamically
if ($blood_group) {
    $query .= " AND blood_inventory.blood_group = '$blood_group'";
}

if ($province) {
    $query .= " AND hospitals.province = '$province'";
}

if ($district) {
    $query .= " AND hospitals.district = '$district'";
}

$result = mysqli_query($conn, $query);


while ($row = mysqli_fetch_assoc($result)) {
    echo "<div class='blood-card'>";
    echo "<p>Blood Group: " . htmlspecialchars($row['blood_group']) . "</p>";
    echo "<p>Hospital: " . htmlspecialchars($row['hospital_name']) . "</p>";
    echo "<p>Province: " . htmlspecialchars($row['province']) . "</p>";
    echo "<p>District: " . htmlspecialchars($row['district']) . "</p>";
    echo "<p>Units Available: " . htmlspecialchars($row['units']) . "</p>";
    echo "</div>";
}
?>
