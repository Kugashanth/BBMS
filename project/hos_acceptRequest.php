<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$hospital_id = $_SESSION['user_id'];

// Fetch blood inventory for the logged-in hospital, grouped by blood_group
$sql_inventory = "SELECT blood_group, COALESCE(SUM(units), 0) AS units 
                 FROM blood_inventory 
                 WHERE hospital_id = ? 
                 GROUP BY blood_group 
                 ORDER BY blood_group";
$stmt_inventory = $conn->prepare($sql_inventory);
$inventory = [];
if ($stmt_inventory) {
    $stmt_inventory->bind_param("i", $hospital_id);
    $stmt_inventory->execute();
    $result_inventory = $stmt_inventory->get_result();
    while ($row = $result_inventory->fetch_assoc()) {
        $inventory[] = $row;
        error_log("Initial inventory fetch: Blood Group: {$row['blood_group']}, Units: {$row['units']}");
    }
    $stmt_inventory->close();
} else {
    error_log("Inventory query prepare failed: " . $conn->error);
}

// Ensure all blood groups are present
$blood_groups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
$inventory_map = array_column($inventory, 'units', 'blood_group');
$inventory = [];
foreach ($blood_groups as $group) {
    $inventory[] = [
        'blood_group' => $group,
        'units' => isset($inventory_map[$group]) ? (int)$inventory_map[$group] : 0
    ];
}

// Fetch initial requests, ensuring unique IDs
$sql_requests = "SELECT DISTINCT br.id, br.requesting_hospital_id, h.name AS hospital_name, 
                br.blood_group, br.unit AS requested_units, br.status, br.created_at,
                COALESCE(bi.units, 0) AS available_units
                FROM blood_requests br
                INNER JOIN hospitals h ON br.requesting_hospital_id = h.id
                LEFT JOIN blood_inventory bi ON bi.hospital_id = br.requested_hospital_id 
                AND bi.blood_group = br.blood_group
                WHERE br.requested_hospital_id = ?
                GROUP BY br.id";
$stmt_requests = $conn->prepare($sql_requests);
$requests = [];
$last_created = 0;
if ($stmt_requests) {
    $stmt_requests->bind_param("i", $hospital_id);
    $stmt_requests->execute();
    $result_requests = $stmt_requests->get_result();
    while ($request = $result_requests->fetch_assoc()) {
        $requests[] = $request;
        $created_at = strtotime($request['created_at']);
        $last_created = max($last_created, $created_at);
        error_log("Initial request fetch: ID: {$request['id']}, Blood Group: {$request['blood_group']}, Status: {$request['status']}, Available Units: {$request['available_units']}");
    }
    $stmt_requests->close();
} else {
    error_log("Requests query prepare failed: " . $conn->error);
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blood Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #ffffff, #f1f3f5);
            color: #212529;
            overflow-x: hidden;
        }

        .navbar-fixed {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .content-wrapper {
            padding-top: 80px;
        }

        .hero-section {
            padding: 0 100px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translate(-50%, -60%) scale(0.9); }
            to { opacity: 1; transform: translate(-50%, -50%) scale(1); }
        }

        .container {
            max-width: 1400px;
            margin: auto;
            padding: 50px 20px;
            width: 100%;
        }

        .inventory-section {
            margin-bottom: 40px;
        }

        .inventory-section h3 {
            color: #dc3545;
            font-weight: 600;
            font-size: 1.8rem;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
        }

        .inventory-section h3 i {
            margin-right: 12px;
        }

        .inventory-card {
            background: #fff;
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            position: relative;
            height: 100%;
        }

        .inventory-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 30px rgba(220, 53, 69, 0.3);
        }

        .inventory-card-header {
            background: linear-gradient(45deg, #dc3545, #c82333);
            color: #fff;
            padding: 15px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

        .inventory-card-body {
            padding: 20px;
            text-align: center;
        }

        .inventory-card-body .units {
            font-size: 2rem;
            font-weight: 700;
            color: #dc3545;
            margin-bottom: 10px;
        }

        .inventory-card-body .label {
            font-size: 1rem;
            color: #6c757d;
        }

        .inventory-card-icon {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .table-responsive {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            background: #fff;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            background: linear-gradient(45deg, #dc3545, #c82333);
            color: #fff;
            font-weight: 600;
            border: none;
            padding: 15px;
            text-align: center;
            vertical-align: middle;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            transition: background 0.3s ease;
        }

        .table th.sortable:hover {
            background: linear-gradient(45deg, #c82333, #dc3545);
        }

        .table th.sortable::after {
            content: '\f0dc';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            margin-left: 8px;
            opacity: 0.6;
            font-size: 0.9rem;
        }

        .table th.sort-asc::after {
            content: '\f0de';
            opacity: 1;
        }

        .table th.sort-desc::after {
            content: '\f0dd';
            opacity: 1;
        }

        .table td {
            padding: 15px;
            vertical-align: middle;
            border: none;
            background: #fff;
            transition: all 0.3s ease;
            animation: rowFadeIn 0.5s ease;
            text-align: center;
        }

        .table tr:nth-child(even) td {
            background: #f8f9fa;
        }

        .table tr:hover td {
            background: #ffe5e7;
            transform: translateY(-2px);
        }

        .table .action-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin: 0 5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            border: none;
        }

        .table .action-btn.accept-btn {
            background: #28a745;
            color: #fff;
        }

        .table .action-btn.reject-btn {
            background: #dc3545;
            color: #fff;
        }

        .table .action-btn:hover {
            transform: scale(1.15);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }

        .table .action-btn.accept-btn:hover {
            background: #218838;
        }

        .table .action-btn.reject-btn:hover {
            background: #c82333;
        }

        .table .action-btn.disabled {
            background: #6c757d;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .table .action-btn i {
            margin: 0;
        }

        .table .badge {
            padding: 8px 14px;
            font-size: 0.9rem;
            transition: transform 0.3s ease;
        }

        .table .badge:hover {
            transform: scale(1.05);
        }

        @keyframes rowFadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .btn-custom {
            background: #dc3545;
            color: #fff;
            border: none;
            border-radius: 14px;
            padding: 12px 24px;
            font-weight: 500;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .btn-custom i {
            margin-right: 8px;
            transition: transform 0.3s ease;
        }

        .btn-custom:hover {
            background: linear-gradient(45deg, #dc3545, #c82333);
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(220, 53, 69, 0.5);
        }

        .btn-custom:hover i {
            transform: translateX(4px);
        }

        .btn-custom:active {
            transform: scale(0.98);
            box-shadow: 0 2px 5px rgba(220, 53, 69, 0.2);
        }

        .btn-custom::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.4s ease, height 0.4s ease;
        }

        .btn-custom:hover::after {
            width: 300px;
            height: 300px;
        }

        .btn-custom.bg-danger {
            background: #c82333;
        }

        .btn-custom.bg-danger:hover {
            background: linear-gradient(45deg, #c82333, #b21f2d);
        }

        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1055;
        }

        .toast {
            border-radius: 14px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            animation: slideInRight 0.5s ease;
        }

        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(100px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .toast-header {
            background: linear-gradient(45deg, #dc3545, #c82333);
            color: #fff;
            border-radius: 14px 14px 0 0;
            padding: 12px 20px;
        }

        .toast-header.bg-danger {
            background: linear-gradient(45deg, #c82333, #b21f2d);
        }

        .toast-header.bg-warning {
            background: linear-gradient(45deg, #ffc107, #e0a800);
        }

        .toast-header.bg-info {
            background: linear-gradient(45deg, #17a2b8, #138496);
        }

        .toast-body {
            background: #fff;
            color: #333;
            padding: 20px;
            position: relative;
        }

        .toast-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 4px;
            background: #dc3545;
            animation: toastProgress 5s linear forwards;
        }

        @keyframes toastProgress {
            from { width: 100%; }
            to { width: 0; }
        }

        .modal-content {
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: fadeIn 0.3s ease;
        }

        .modal-header {
            background: linear-gradient(45deg, #dc3545, #c82333);
            color: #fff;
            border-radius: 14px 14px 0 0;
        }

        .modal-header.bg-danger {
            background: linear-gradient(45deg, #c82333, #b21f2d);
        }

        .modal-body {
            padding: 20px;
            font-size: 1.1rem;
        }

        .modal-footer .btn-custom {
            margin: 0 10px;
        }

        @media (max-width: 992px) {
            .container {
                padding: 30px 15px;
            }

            .inventory-section {
                padding: 20px;
            }

            .inventory-section .row > div {
                margin-bottom: 15px;
            }

            .inventory-card {
                margin-bottom: 20px;
            }
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 0 20px;
            }

            .table-responsive {
                overflow-x: auto;
            }

            .table th, .table td {
                font-size: 0.85rem;
                padding: 12px;
            }

            .table .action-btn {
                width: 36px;
                height: 36px;
                font-size: 1rem;
            }

            .inventory-section h3 {
                font-size: 1.4rem;
            }
        }

        @media (max-width: 576px) {
            .table th, .table td {
                font-size: 0.8rem;
                padding: 10px;
            }

            .table .action-btn {
                width: 32px;
                height: 32px;
                font-size: 0.9rem;
            }

            .toast {
                max-width: 90%;
            }
        }
    </style>
</head>
<body>
    <div class="navbar-fixed">
        <?php include 'navbar.php'; ?>
    </div>
    <div class="content-wrapper">
        <?php include 'sidebar.php'; ?>
        <div class="hero-section">
            <h3 class="text-center my-4"><i class="fas fa-list me-2"></i> Blood Requests</h3>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th class="sortable" data-sort="hospital_name">Requesting Hospital</th>
                            <th class="sortable" data-sort="blood_group">Blood Group</th>
                            <th class="sortable" data-sort="requested_units">Requested Units</th>
                            <th class="sortable" data-sort="available_units">Available Units</th>
                            <th class="sortable" data-sort="status">Status</th>
                            <th class="sortable" data-sort="created_at">Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="requestsTableBody">
                        <?php if (empty($requests)): ?>
                            <tr><td colspan="7" class="text-center"><i class="fas fa-info-circle me-2"></i> No requests found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($requests as $request): ?>
                                <tr data-request-id="<?php echo $request['id']; ?>">
                                    <td><?php echo htmlspecialchars($request['hospital_name']); ?></td>
                                    <td><?php echo htmlspecialchars($request['blood_group']); ?></td>
                                    <td><?php echo $request['requested_units']; ?></td>
                                    <td><?php echo $request['available_units']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php
                                            echo $request['status'] === 'Pending' ? 'warning' : 
                                                 ($request['status'] === 'Accepted' ? 'success' : 
                                                 ($request['status'] === 'Rejected' ? 'danger' : 'secondary'));
                                        ?>">
                                            <?php echo $request['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $request['created_at']; ?></td>
                                    <td>
                                        <?php if ($request['status'] === 'Pending'): ?>
                                            <button class="action-btn accept-btn" 
                                                    data-request-id="<?php echo $request['id']; ?>"
                                                    data-available-units="<?php echo $request['available_units']; ?>"
                                                    data-blood-group="<?php echo $request['blood_group']; ?>"
                                                    data-units="<?php echo $request['requested_units']; ?>">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="action-btn reject-btn"
                                                    data-request-id="<?php echo $request['id']; ?>"
                                                    data-blood-group="<?php echo $request['blood_group']; ?>"
                                                    data-units="<?php echo $request['requested_units']; ?>">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="toast-container">
                <div id="successToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong class="me-auto">Success</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body" id="successToastMessage"></div>
                    <div class="toast-progress"></div>
                </div>
                <div id="errorToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header bg-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong class="me-auto">Error</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body" id="errorToastMessage"></div>
                    <div class="toast-progress"></div>
                </div>
                <div id="warningToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header bg-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong class="me-auto">Warning</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body" id="warningToastMessage"></div>
                    <div class="toast-progress"></div>
                </div>
                <div id="processingToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header bg-info">
                        <i class="fas fa-spinner fa-spin me-2"></i>
                        <strong class="me-auto">Processing</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body" id="processingToastMessage"></div>
                    <div class="toast-progress"></div>
                </div>
            </div>

            <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="confirmModalLabel"><i class="fas fa-question-circle me-2"></i> Confirm Action</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="confirmModalMessage"></div>
                        <div class="modal-footer">
                            <button type="button" class="btn-custom bg-danger" data-bs-dismiss="modal"><i class="fas fa-times"></i> Cancel</button>
                            <button type="button" class="btn-custom" id="confirmActionBtn"><i class="fas fa-check"></i> Confirm</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container">
                <div class="inventory-section">
                    <h3><i class="fas fa-tint"></i> Blood Inventory</h3>
                    <div class="row" id="inventoryCardContainer">
                        <?php foreach ($inventory as $item): ?>
                            <div class="col-6 col-md-4 col-lg-3 mb-4">
                                <div class="inventory-card">
                                    <div class="inventory-card-header">
                                        <?php echo htmlspecialchars($item['blood_group']); ?>
                                        <i class="fas fa-tint inventory-card-icon"></i>
                                    </div>
                                    <div class="inventory-card-body">
                                        <div class="units"><?php echo $item['units']; ?></div>
                                        <div class="label">Available Units</div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="content2">
            <?php include 'footer.php'; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        const successToast = new bootstrap.Toast(document.getElementById('successToast'), { delay: 5000 });
        const errorToast = new bootstrap.Toast(document.getElementById('errorToast'), { delay: 5000 });
        const warningToast = new bootstrap.Toast(document.getElementById('warningToast'), { delay: 5000 });
        const processingToast = new bootstrap.Toast(document.getElementById('processingToast'), { delay: 5000 });
        const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));

        let lastCreated = <?php echo $last_created; ?>;
        let isProcessing = false;
        let seenRequestIds = new Set(<?php echo json_encode(array_column($requests, 'id')); ?>);

        function fetchRequests(forcePolling = false, retryCount = 0) {
            if (isProcessing && !forcePolling) {
                console.log('Skipping fetch: Processing in progress');
                return;
            }
            const maxRetries = 2;
            fetch(`get_requests_manage.php?hospital_id=<?php echo $hospital_id; ?>&lastCreated=${lastCreated}&t=${new Date().getTime()}`, {
                method: 'GET',
                cache: 'no-cache'
            })
                .then(response => {
                    console.log('Fetch requests response status:', response.status);
                    if (!response.ok) {
                        return response.text().then

(text => {
                            throw new Error(`HTTP error: ${response.status}, Response: ${text}`);
                        });
                    }
                    return response.text();
                })
                .then(text => {
                    console.log('Fetch requests raw response:', text);
                    try {
                        const data = JSON.parse(text);
                        console.log('Fetch requests parsed data:', data);
                        if (data.success) {
                            if (data.lastCreated > lastCreated || forcePolling) {
                                lastCreated = data.lastCreated;
                                console.log('Last Created updated:', lastCreated);
                                renderRequests(data);
                                renderInventory(data.inventory);
                            } else {
                                console.log('No new updates, skipping render');
                            }
                        } else {
                            document.getElementById('requestsTableBody').innerHTML = 
                                `<tr><td colspan="7" class="text-center"><i class="fas fa-exclamation-circle me-2"></i> ${data.error || 'Error fetching requests'}</td></tr>`;
                        }
                    } catch (e) {
                        throw new Error(`JSON parse error: ${e.message}, Response: ${text}`);
                    }
                })
                .catch(error => {
                    console.error('Fetch requests error:', error);
                    if (retryCount < maxRetries) {
                        console.log(`Retrying fetch request (${retryCount + 1}/${maxRetries})`);
                        setTimeout(() => fetchRequests(forcePolling, retryCount + 1), 1000);
                    } else {
                        document.getElementById('requestsTableBody').innerHTML = 
                            `<tr><td colspan="7" class="text-center"><i class="fas fa-exclamation-circle me-2"></i> Failed to load requests: ${error.message}</td></tr>`;
                        document.getElementById('errorToastMessage').textContent = 
                            `Failed to load requests: ${error.message}`;
                        errorToast.show();
                    }
                });
        }

        function renderRequests(data) {
            const tbody = document.getElementById('requestsTableBody');
            const newRequestIds = new Set(data.requests.map(r => r.id));
            const currentIds = new Set(Array.from(tbody.querySelectorAll('tr')).map(tr => parseInt(tr.getAttribute('data-request-id'))));

            // Remove rows not in new dataset
            currentIds.forEach(id => {
                if (!newRequestIds.has(id)) {
                    const row = tbody.querySelector(`tr[data-request-id="${id}"]`);
                    if (row) {
                        console.log(`Removing outdated row ID: ${id}`);
                        row.remove();
                        seenRequestIds.delete(id);
                    }
                }
            });

            if (data.requests && data.requests.length > 0) {
                data.requests.forEach(request => {
                    console.log(`Processing request ID: ${request.id}, Status: ${request.status}, Seen: ${seenRequestIds.has(request.id)}`);
                    const existingRow = tbody.querySelector(`tr[data-request-id="${request.id}"]`);
                    if (existingRow) {
                        // Update existing row
                        console.log(`Updating existing row ID: ${request.id} to status: ${request.status}`);
                        existingRow.cells[4].innerHTML = `
                            <span class="badge bg-${
                                request.status === 'Pending' ? 'warning' : 
                                request.status === 'Accepted' ? 'success' : 
                                request.status === 'Rejected' ? 'danger' : 'secondary'
                            }">${request.status}</span>
                        `;
                        existingRow.cells[3].textContent = request.available_units; // Update available units
                        existingRow.cells[6].innerHTML = request.status === 'Pending' ? 
                            `<button class="action-btn accept-btn" 
                                     data-request-id="${request.id}"
                                     data-available-units="${request.available_units}"
                                     data-blood-group="${request.blood_group}"
                                     data-units="${request.requested_units}">
                                 <i class="fas fa-check"></i>
                             </button>
                             <button class="action-btn reject-btn"
                                     data-request-id="${request.id}"
                                     data-blood-group="${request.blood_group}"
                                     data-units="${request.requested_units}">
                                 <i class="fas fa-times"></i>
                             </button>` : '';
                    } else if (!seenRequestIds.has(request.id)) {
                        // Add new row
                        const tr = document.createElement('tr');
                        tr.setAttribute('data-request-id', request.id);
                        tr.innerHTML = `
                            <td>${request.hospital_name}</td>
                            <td>${request.blood_group}</td>
                            <td>${request.requested_units}</td>
                            <td>${request.available_units}</td>
                            <td><span class="badge bg-${
                                request.status === 'Pending' ? 'warning' : 
                                request.status === 'Accepted' ? 'success' : 
                                request.status === 'Rejected' ? 'danger' : 'secondary'
                            }">${request.status}</span></td>
                            <td>${request.created_at}</td>
                            <td>${
                                request.status === 'Pending' ? 
                                `<button class="action-btn accept-btn" 
                                         data-request-id="${request.id}"
                                         data-available-units="${request.available_units}"
                                         data-blood-group="${request.blood_group}"
                                         data-units="${request.requested_units}">
                                     <i class="fas fa-check"></i>
                                 </button>
                                 <button class="action-btn reject-btn"
                                         data-request-id="${request.id}"
                                         data-blood-group="${request.blood_group}"
                                         data-units="${request.requested_units}">
                                     <i class="fas fa-times"></i>
                                 </button>` : ''
                            }</td>
                        `;
                        tbody.appendChild(tr);
                        seenRequestIds.add(request.id);
                        console.log(`Rendered new request ID: ${request.id}, Hospital: ${request.hospital_name}, Blood Group: ${request.blood_group}, Status: ${request.status}`);
                    }
                    if (request.available_units === 0) {
                        document.getElementById('warningToastMessage').textContent = 
                            `Warning: No units available for ${request.blood_group} at this hospital.`;
                        warningToast.show();
                    }
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center"><i class="fas fa-info-circle me-2"></i> No requests found.</td></tr>';
                console.log('No requests to render');
            }
        }

        function renderInventory(inventory) {
            const container = document.getElementById('inventoryCardContainer');
            container.innerHTML = '';
            const bloodGroups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
            const inventoryMap = {};
            inventory.forEach(item => {
                if (!inventoryMap[item.blood_group]) {
                    inventoryMap[item.blood_group] = item.units;
                }
            });

            bloodGroups.forEach(group => {
                const units = inventoryMap[group] ?? 0;
                const div = document.createElement('div');
                div.className = 'col-6 col-md-4 col-lg-3 mb-4';
                div.innerHTML = `
                    <div class="inventory-card">
                        <div class="inventory-card-header">
                            ${group}
                            <i class="fas fa-tint inventory-card-icon"></i>
                        </div>
                        <div class="inventory-card-body">
                            <div class="units">${units}</div>
                            <div class="label">Available Units</div>
                        </div>
                    </div>
                `;
                container.appendChild(div);
                console.log(`Rendered inventory card: Blood Group: ${group}, Units: ${units}`);
            });
        }

        let sortColumn = 'created_at';
        let sortDirection = 'desc';
        document.querySelectorAll('.sortable').forEach(th => {
            th.addEventListener('click', () => {
                const column = th.getAttribute('data-sort');
                if (column === sortColumn) {
                    sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    sortColumn = column;
                    sortDirection = 'asc';
                }
                document.querySelectorAll('.sortable').forEach(t => {
                    t.classList.remove('sort-asc', 'sort-desc');
                });
                th.classList.add(`sort-${sortDirection}`);
                console.log(`Sorting by ${sortColumn} ${sortDirection}`);
                sortTable();
            });
        });

        function sortTable() {
            const tbody = document.getElementById('requestsTableBody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            rows.sort((a, b) => {
                let aValue, bValue;
                switch (sortColumn) {
                    case 'hospital_name':
                        aValue = a.cells[0].textContent.toLowerCase();
                        bValue = b.cells[0].textContent.toLowerCase();
                        break;
                    case 'blood_group':
                        aValue = a.cells[1].textContent;
                        bValue = b.cells[1].textContent;
                        break;
                    case 'requested_units':
                    case 'available_units':
                        aValue = parseInt(a.cells[sortColumn === 'requested_units' ? 2 : 3].textContent);
                        bValue = parseInt(b.cells[sortColumn === 'requested_units' ? 2 : 3].textContent);
                        break;
                    case 'status':
                        aValue = a.cells[4].textContent;
                        bValue = b.cells[4].textContent;
                        break;
                    case 'created_at':
                        aValue = new Date(a.cells[5].textContent);
                        bValue = new Date(b.cells[5].textContent);
                        break;
                }
                if (aValue < bValue) return sortDirection === 'asc' ? -1 : 1;
                if (aValue > bValue) return sortDirection === 'asc' ? 1 : -1;
                return 0;
            });
            tbody.innerHTML = '';
            rows.forEach(row => tbody.appendChild(row));
        }

        document.getElementById('requestsTableBody').addEventListener('click', (e) => {
            const button = e.target.closest('.action-btn');
            if (button && !button.disabled) {
                const requestId = button.getAttribute('data-request-id');
                const action = button.classList.contains('accept-btn') ? 'accept' : 'reject';
                const availableUnits = button.classList.contains('accept-btn') ? 
                                      parseInt(button.getAttribute('data-available-units')) : null;
                const bloodGroup = button.getAttribute('data-blood-group');
                const units = parseInt(button.getAttribute('data-units'));

                console.log(`Clicked ${action} for request ID: ${requestId}, Blood Group: ${bloodGroup}, Units: ${units}, Available Units: ${availableUnits}`);

                if (action === 'accept') {
                    if (isNaN(availableUnits) || availableUnits < units || availableUnits - units < 3) {
                        document.getElementById('errorToastMessage').textContent = 
                            `Cannot accept request for ${bloodGroup}: Available units must be at least ${units} and leave at least 3 units.`;
                        errorToast.show();
                        console.log('Accept failed: Insufficient units');
                        return;
                    }
                }

                document.getElementById('confirmModalMessage').textContent = 
                    `Are you sure you want to ${action} the request for ${units} units of ${bloodGroup}?`;
                document.getElementById('confirmModalLabel').innerHTML = 
                    `<i class="fas fa-question-circle me-2"></i> Confirm ${action.charAt(0).toUpperCase() + action.slice(1)}`;
                document.querySelector('#confirmModal .modal-header').className = 
                    `modal-header ${action === 'accept' ? '' : 'bg-danger'}`;
                confirmModal.show();

                document.getElementById('confirmActionBtn').onclick = () => {
                    confirmModal.hide();
                    const row = button.closest('tr');
                    const buttons = row.querySelectorAll('.action-btn');
                    buttons.forEach(btn => {
                        btn.disabled = true;
                        btn.innerHTML = `<i class="fas fa-spinner fa-spin"></i>`;
                    });
                    isProcessing = true;

                    document.getElementById('processingToastMessage').textContent = 
                        `Processing ${action} request for ${units} units of ${bloodGroup}...`;
                    processingToast.show();

                    function processRequest(retryCount = 0) {
                        const maxRetries = 2;
                        fetch('process_request.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `request_id=${encodeURIComponent(requestId)}&action=${encodeURIComponent(action)}`
                        })
                            .then(response => {
                                console.log('Process request response status:', response.status);
                                if (!response.ok) {
                                    return response.text().then(text => {
                                        throw new Error(`HTTP error: ${response.status}, Response: ${text}`);
                                    });
                                }
                                return response.text();
                            })
                            .then(text => {
                                console.log('Process request raw response:', text);
                                try {
                                    const data = JSON.parse(text);
                                    console.log('Process request response data:', data);
                                    if (data.success) {
                                        const delay = action === 'accept' ? 5000 : 0;
                                        setTimeout(() => {
                                            isProcessing = false;
                                            processingToast.hide();
                                            document.getElementById('successToastMessage').textContent = 
                                                `Request for ${bloodGroup} (${units} units) ${data.status.toLowerCase()} successfully!`;
                                            successToast.show();
                                            row.cells[4].innerHTML = `
                                                <span class="badge bg-${
                                                    data.status === 'Accepted' ? 'success' : 'danger'
                                                }">${data.status}</span>
                                            `;
                                            row.cells[3].textContent = data.available_units || row.cells[3].textContent;
                                            row.cells[6].innerHTML = '';
                                            console.log(`Updated row ID: ${requestId} to status: ${data.status}`);
                                            fetchRequests(true);
                                        }, delay);
                                    } else {
                                        isProcessing = false;
                                        processingToast.hide();
                                        buttons.forEach(btn => {
                                            btn.disabled = false;
                                            btn.innerHTML = `<i class="fas fa-${btn.classList.contains('accept-btn') ? 'check' : 'times'}"></i>`;
                                        });
                                        document.getElementById('errorToastMessage').textContent = 
                                            data.error.includes('already processed') 
                                            ? `Request for ${bloodGroup} has already been processed as ${data.current_status}.`
                                            : `Error ${action}ing request for ${bloodGroup}: ${data.error}`;
                                        errorToast.show();
                                    }
                                } catch (e) {
                                    throw new Error(`JSON parse error: ${e.message}, Response: ${text}`);
                                }
                            })
                            .catch(error => {
                                console.error('Process request error:', error);
                                isProcessing = false;
                                processingToast.hide();
                                if (retryCount < maxRetries) {
                                    console.log(`Retrying process request (${retryCount + 1}/${maxRetries})`);
                                    setTimeout(() => processRequest(retryCount + 1), 1000);
                                } else {
                                    buttons.forEach(btn => {
                                        btn.disabled = false;
                                        btn.innerHTML = `<i class="fas fa-${btn.classList.contains('accept-btn') ? 'check' : 'times'}"></i>`;
                                    });
                                    document.getElementById('errorToastMessage').textContent = 
                                        `Error ${action}ing request for ${bloodGroup}: ${error.message}`;
                                    errorToast.show();
                                }
                            });
                    }

                    processRequest();
                };
            }
        });

        function startPolling() {
            setInterval(() => {
                if (!isProcessing) {
                    console.log('Polling for new requests and inventory...');
                    fetchRequests(true);
                } else {
                    console.log('Polling skipped: Processing in progress');
                }
            }, 10000);
        }

        window.onload = () => {
            fetchRequests();
            sortTable();
            startPolling();
        };
    </script>
</body>
</html>