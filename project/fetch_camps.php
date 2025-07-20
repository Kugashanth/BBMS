<?php
include 'config/db.php';

$filter = $_GET['filter'] ?? 'all';
$current_date = date('Y-m-d');

$query = "SELECT * FROM blood_camps WHERE ";
if ($filter == 'today') {
    $query .= "DATE(camp_date) = '$current_date'";
} elseif ($filter == 'tomorrow') {
    $tomorrow_date = date('Y-m-d', strtotime('+1 day'));
    $query .= "DATE(camp_date) = '$tomorrow_date'";
} elseif ($filter == 'this_week') {
    $start_of_week = date('Y-m-d', strtotime('monday this week'));
    $end_of_week = date('Y-m-d', strtotime('sunday this week'));
    $query .= "DATE(camp_date) BETWEEN '$start_of_week' AND '$end_of_week'";
} elseif ($filter == 'next_week') {
    $start_of_next_week = date('Y-m-d', strtotime('next monday'));
    $end_of_next_week = date('Y-m-d', strtotime('next sunday'));
    $query .= "DATE(camp_date) BETWEEN '$start_of_next_week' AND '$end_of_next_week'";
} elseif ($filter == 'this_month') {
    $start_of_month = date('Y-m-01');
    $end_of_month = date('Y-m-t');
    $query .= "DATE(camp_date) BETWEEN '$start_of_month' AND '$end_of_month'";
} elseif ($filter == 'next_month') {
    $start_of_next_month = date('Y-m-01', strtotime('first day of next month'));
    $end_of_next_month = date('Y-m-t', strtotime('last day of next month'));
    $query .= "DATE(camp_date) BETWEEN '$start_of_next_month' AND '$end_of_next_month'";
} elseif ($filter == 'this_year') {
    $start_of_year = date('Y-01-01');
    $end_of_year = date('Y-12-31');
    $query .= "DATE(camp_date) BETWEEN '$start_of_year' AND '$end_of_year'";
} else {
    $query .= "1";  // Get all camps
}

$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    echo "<div class='stock-card'>";
    echo "<h3>" . $row['camp_name'] . "</h3>";
    echo "<p>Date: " . $row['camp_date'] . "</p>";
    echo "<p>Location: " . $row['location'] . "</p>";
    echo "</div>";
}
?>
