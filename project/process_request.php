<?php
session_start();
include 'config/db.php';

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ob_start();
header('Content-Type: application/json; charset=utf-8');

$response = ['success' => false];

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized access.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    $request_id = $_POST['request_id'] ?? null;
    $action = $_POST['action'] ?? null;

    if (!$request_id || !in_array($action, ['accept', 'reject'])) {
        throw new Exception('Invalid request ID or action.');
    }

    $hospital_id = $_SESSION['user_id'];
    error_log("Processing request ID: $request_id, Action: $action, Hospital ID: $hospital_id");

    // Begin transaction
    $conn->begin_transaction();

    // Fetch request details
    $sql = "SELECT br.requesting_hospital_id, br.requested_hospital_id, br.blood_group, br.unit, br.status
            FROM blood_requests br
            WHERE br.id = ? AND br.requested_hospital_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }
    $stmt->bind_param("ii", $request_id, $hospital_id);
    if (!$stmt->execute()) {
        throw new Exception('Database execute error: ' . $stmt->error);
    }
    $result = $stmt->get_result();
    $request = $result->fetch_assoc();
    $stmt->close();

    if (!$request) {
        throw new Exception('Request not found or you are not authorized to process it.');
    }

    if ($request['status'] !== 'Pending') {
        throw new Exception('Request has already been processed as ' . $request['status'] . '.', $request['status']);
    }

    if ($action === 'accept') {
        // Ensure inventory exists for both hospitals
        $sql_init = "INSERT IGNORE INTO blood_inventory (hospital_id, blood_group, units)
                     SELECT ?, ?, 0
                     WHERE NOT EXISTS (
                         SELECT 1 FROM blood_inventory 
                         WHERE hospital_id = ? AND blood_group = ?
                     )";
        $stmt_init = $conn->prepare($sql_init);
        if (!$stmt_init) {
            throw new Exception('Init inventory prepare error: ' . $conn->error);
        }
        foreach ([$hospital_id, $request['requesting_hospital_id']] as $hid) {
            $stmt_init->bind_param("isis", $hid, $request['blood_group'], $hid, $request['blood_group']);
            $stmt_init->execute();
            error_log("Initialized inventory for hospital_id: $hid, Blood Group: {$request['blood_group']}");
        }
        $stmt_init->close();

        // Remove duplicate inventory rows
        $sql_clean_inventory = "DELETE bi1 FROM blood_inventory bi1
                               INNER JOIN blood_inventory bi2
                               WHERE bi1.id < bi2.id
                               AND bi1.hospital_id = bi2.hospital_id
                               AND bi1.blood_group = bi2.blood_group
                               AND bi1.hospital_id IN (?, ?)";
        $stmt_clean = $conn->prepare($sql_clean_inventory);
        if (!$stmt_clean) {
            throw new Exception('Clean inventory prepare error: ' . $conn->error);
        }
        $stmt_clean->bind_param("ii", $hospital_id, $request['requesting_hospital_id']);
        $stmt_clean->execute();
        error_log("Cleaned duplicate inventory rows for hospital_ids: $hospital_id, {$request['requesting_hospital_id']}");
        $stmt_clean->close();

        // Check available units
        $sql_inventory = "SELECT units FROM blood_inventory 
                         WHERE hospital_id = ? AND blood_group = ?";
        $stmt_inventory = $conn->prepare($sql_inventory);
        if (!$stmt_inventory) {
            throw new Exception('Inventory prepare error: ' . $conn->error);
        }
        $stmt_inventory->bind_param("is", $hospital_id, $request['blood_group']);
        $stmt_inventory->execute();
        $result_inventory = $stmt_inventory->get_result();
        $inventory = $result_inventory->fetch_assoc();
        $stmt_inventory->close();

        $available_units = $inventory['units'] ?? 0;
        if ($available_units < $request['unit'] || $available_units - $request['unit'] < 3) {
            throw new Exception('Insufficient units or post-transaction units would be less than 3.');
        }

        // Update requested hospital inventory (decrease)
        $sql_update = "UPDATE blood_inventory 
                       SET units = units - ? 
                       WHERE hospital_id = ? AND blood_group = ?";
        $stmt_update = $conn->prepare($sql_update);
        if (!$stmt_update) {
            throw new Exception('Update prepare error: ' . $conn->error);
        }
        $stmt_update->bind_param("iis", $request['unit'], $hospital_id, $request['blood_group']);
        if (!$stmt_update->execute()) {
            throw new Exception('Update execute error: ' . $stmt_update->error);
        }
        if ($stmt_update->affected_rows === 0) {
            throw new Exception('No inventory rows updated for requested hospital.');
        }
        error_log("Decreased inventory: hospital_id: $hospital_id, Blood Group: {$request['blood_group']}, Units: -{$request['unit']}");
        $stmt_update->close();

        // Update requesting hospital inventory (increase)
        $sql_requesting = "UPDATE blood_inventory 
                          SET units = units + ? 
                          WHERE hospital_id = ? AND blood_group = ?";
        $stmt_requesting = $conn->prepare($sql_requesting);
        if (!$stmt_requesting) {
            throw new Exception('Requesting update prepare error: ' . $conn->error);
        }
        $stmt_requesting->bind_param("iis", $request['unit'], $request['requesting_hospital_id'], $request['blood_group']);
        if (!$stmt_requesting->execute()) {
            throw new Exception('Requesting update execute error: ' . $stmt_requesting->error);
        }
        if ($stmt_requesting->affected_rows === 0) {
            throw new Exception('No inventory rows updated for requesting hospital.');
        }
        error_log("Increased inventory: hospital_id: {$request['requesting_hospital_id']}, Blood Group: {$request['blood_group']}, Units: +{$request['unit']}");
        $stmt_requesting->close();
    }

    // Update request status
    $sql_status = "UPDATE blood_requests SET status = ? WHERE id = ? AND requested_hospital_id = ?";
    $stmt_status = $conn->prepare($sql_status);
    if (!$stmt_status) {
        throw new Exception('Status prepare error: ' . $conn->error);
    }
    $status = $action === 'accept' ? 'Accepted' : 'Rejected';
    $stmt_status->bind_param("sii", $status, $request_id, $hospital_id);
    if (!$stmt_status->execute()) {
        throw new Exception('Status execute error: ' . $stmt_status->error);
    }
    if ($stmt_status->affected_rows === 0) {
        throw new Exception('No request rows updated for status change.');
    }
    error_log("Updated request ID: $request_id, Status: $status");
    $stmt_status->close();

    // Fetch updated inventory, grouped by blood_group
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
        error_log("Updated inventory: Blood Group: {$row['blood_group']}, Units: {$row['units']}");
    }
    $stmt_inventory->close();

    $conn->commit();
    $response['success'] = true;
    $response['inventory'] = $inventory;
    $response['status'] = $status; // Return new status for UI confirmation
    error_log("Successfully processed request ID: $request_id, Action: $action, New Status: $status");

} catch (Exception $e) {
    $conn->rollback();
    $response['error'] = $e->getMessage();
    $response['current_status'] = $e->getCode() ?: null;
    error_log('Process request failed: ' . $e->getMessage() . ' | Request ID: ' . ($request_id ?? 'N/A') . ' | Hospital ID: ' . ($hospital_id ?? 'N/A'));
}

$conn->close();
echo json_encode($response);
ob_end_flush();
exit;
?>