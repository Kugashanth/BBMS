<?php
session_start();

include 'config/db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$hospital_id = $_SESSION['user_id'];

// Fetch blood camps with total units
$sql_camps = "SELECT bc.*, COALESCE(SUM(bi.units), 0) as total_units 
              FROM blood_camps bc 
              LEFT JOIN blood_inventory bi ON bc.id = bi.camp_id 
              WHERE bc.hospital_id = ? 
              GROUP BY bc.id";
$stmt_camps = $conn->prepare($sql_camps);
if (!$stmt_camps) {
    die("Database error: " . $conn->error);
}
$stmt_camps->bind_param("i", $hospital_id);
$stmt_camps->execute();
$result_camps = $stmt_camps->get_result();

$camps = [];
$blood_groups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
while ($camp = $result_camps->fetch_assoc()) {
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
    $camps[] = $camp;
    $stmt_group_units->close();
}

// Fetch overall inventory
$sql_inventory = "SELECT blood_group, SUM(units) as total_units 
                 FROM blood_inventory 
                 WHERE hospital_id = ? 
                 GROUP BY blood_group";
$stmt_inventory = $conn->prepare($sql_inventory);
$stmt_inventory->bind_param("i", $hospital_id);
$stmt_inventory->execute();
$result_inventory = $stmt_inventory->get_result();

$inventory = array_fill_keys($blood_groups, 0);
while ($row = $result_inventory->fetch_assoc()) {
    $inventory[$row['blood_group']] = $row['total_units'];
}

// Fetch total units
$sql_total = "SELECT SUM(units) as total FROM blood_inventory WHERE hospital_id = ?";
$stmt_total = $conn->prepare($sql_total);
$stmt_total->bind_param("i", $hospital_id);
$stmt_total->execute();
$total_units = $stmt_total->get_result()->fetch_assoc()['total'] ?? 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Camps Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        :root {
            --primary-color: #b91c1c;
            --primary-hover: #991b1b;
            --secondary-color: #1f2937;
            --card-bg: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background: #f3f4f6;
            color: #111827;
            overflow-x: hidden;
        }

        .navbar-fixed {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            background: var(--card-bg);
            box-shadow: var(--shadow);
        }

        .content-wrapper {
            padding-top: 80px;
        }

        .hero-section {
            padding: 0 60px;
        }

        .container {
            max-width: 1400px;
            margin: auto;
            padding: 40px 20px;
        }

        .section-title {
            font-size: 1.875rem;
            font-weight: 600;
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 40px;
            position: relative;
        }

        .section-title::after {
            content: '';
            width: 60px;
            height: 4px;
            background: var(--primary-color);
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
        }

        .filter-panel {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 40px;
            background: var(--card-bg);
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        .filter-btn {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-btn:hover {
            background: var(--primary-color);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .filter-btn.active {
            background: var(--primary-color);
            color: #fff;
            box-shadow: var(--shadow);
        }

        .filter-btn.reset {
            border-color: var(--secondary-color);
            color: var(--secondary-color);
        }

        .filter-btn.reset:hover {
            background: var(--secondary-color);
            color: #fff;
        }

        .camp-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 24px;
            margin-bottom: 60px;
        }

        .blood-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 24px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .blood-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 6px;
            height: 100%;
            background: var(--primary-color);
            transition: width 0.3s ease;
        }

        .blood-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
        }

        .blood-card:hover::before {
            width: 12px;
        }

        .blood-card h5 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .blood-card p {
            font-size: 0.95rem;
            color: #4b5563;
            line-height: 1.6;
            margin: 8px 0;
        }

        .blood-card .info-btn {
            position: absolute;
            top: 16px;
            right: 16px;
            background: transparent;
            border: none;
            color: var(--primary-color);
            font-size: 1.25rem;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .blood-card .info-btn:hover {
            color: var(--primary-hover);
        }

        .blood-stock {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 20px;
            margin-bottom: 60px;
        }

        .stock-card {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            text-align: center;
            transition: all 0.3s ease;
        }

        .stock-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-hover);
        }

        .stock-card h5 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
        }

        .stock-card p {
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--secondary-color);
        }

        .stock-card i {
            font-size: 1.75rem;
            color: var(--primary-color);
            margin-bottom: 12px;
        }

        .total-units {
            font-size: 2.25rem;
            font-weight: 700;
            color: var(--primary-color);
            text-align: center;
            background: var(--card-bg);
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            margin: 40px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .btn-custom {
            background: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-size: 0.95rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
        }

        .btn-custom:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .btn-delete {
            background: var(--secondary-color);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-size: 0.95rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
        }

        .btn-delete:hover {
            background: #111827;
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .modal-content {
            border-radius: 16px;
            box-shadow: var(--shadow-hover);
            border: none;
        }

        .modal-header {
            background: #f9fafb;
            border-bottom: 2px solid var(--primary-color);
            padding: 20px;
        }

        .modal-header h5 {
            font-weight: 600;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modal-body {
            padding: 24px;
        }

        .modal-footer {
            border-top: 2px solid var(--primary-color);
            background: #f9fafb;
            padding: 16px;
        }

        .form-modal input, .form-modal select {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-modal input:focus, .form-modal select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 8px rgba(185, 28, 28, 0.2);
            outline: none;
        }

        .form-label {
            font-weight: 500;
            color: var(--secondary-color);
            font-size: 0.95rem;
            margin-bottom: 8px;
        }

        .table-responsive {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .table th {
            background: var(--primary-color);
            color: #fff;
            font-weight: 500;
            padding: 14px;
            font-size: 0.95rem;
        }

        .table td {
            padding: 14px;
            vertical-align: middle;
            font-size: 0.9rem;
            color: var(--secondary-color);
        }

        .table tr {
            transition: background 0.2s ease;
        }

        .table tr:hover {
            background: #f9fafb;
        }

        .donor-search-container {
            position: relative;
        }

        .donor-search-container i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-size: 1.1rem;
        }

        .donor-search-container input {
            padding-left: 40px;
        }

        .donor-search-results {
            max-height: 200px;
            overflow-y: auto;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            margin-top: 8px;
            background: var(--card-bg);
            box-shadow: var(--shadow);
        }

        .donor-search-results div {
            padding: 12px;
            cursor: pointer;
            font-size: 0.9rem;
            color: var(--secondary-color);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .donor-search-results div:hover {
            background: var(--primary-color);
            color: #fff;
        }

        .donor-search-results div:hover i {
            color: #fff;
        }

        .add-donor-result {
            text-align: center;
            padding: 12px;
            background: var(--primary-color);
            color: #fff;
            border-radius: 8px;
            margin: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .add-donor-result:hover {
            background: var(--primary-hover);
        }

        .error {
            color: var(--primary-color);
            font-size: 0.85rem;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 0 20px;
            }

            .container {
                padding: 20px;
            }

            .blood-card {
                padding: 20px;
            }

            .filter-btn {
                padding: 8px 16px;
                font-size: 0.9rem;
            }

            .section-title {
                font-size: 1.5rem;
            }

            .total-units {
                font-size: 1.75rem;
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
            <div class="container">
                <h3 class="section-title"><i class="fas fa-campground"></i> Your Blood Camps</h3>
                <div class="filter-panel">
                    <button class="filter-btn" data-filter="all"><i class="fas fa-list"></i> All Camps</button>
                    <button class="filter-btn active" data-filter="today"><i class="fas fa-calendar-day"></i> Today</button>
                    <button class="filter-btn" data-filter="tomorrow"><i class="fas fa-calendar-alt"></i> Tomorrow</button>
                    <button class="filter-btn" data-filter="this_week"><i class="fas fa-calendar-week"></i> This Week</button>
                    <button class="filter-btn" data-filter="next_week"><i class="fas fa-calendar"></i> Next Week</button>
                    <button class="filter-btn" data-filter="this_year"><i class="fas fa-calendar-check"></i> This Year</button>
                    <button class="filter-btn reset" data-filter="all"><i class="fas fa-sync-alt"></i> Reset</button>
                </div>
                <div class="camp-list" id="campList">
                    <?php if (count($camps) > 0): ?>
                        <?php foreach ($camps as $camp): ?>
                            <div class="blood-card" data-bs-toggle="modal" data-bs-target="#inventoryModal" 
                                 data-camp-id="<?php echo $camp['id']; ?>" data-camp-date="<?php echo $camp['camp_date']; ?>">
                                <button class="info-btn" data-bs-toggle="modal" data-bs-target="#updateCampModal"
                                        data-camp-id="<?php echo $camp['id']; ?>" 
                                        data-camp-name="<?php echo htmlspecialchars($camp['camp_name']); ?>" 
                                        data-camp-location="<?php echo htmlspecialchars($camp['location']); ?>" 
                                        data-camp-date="<?php echo $camp['camp_date']; ?>">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                                <h5><i class="fas fa-campground"></i> <?php echo htmlspecialchars($camp['camp_name']); ?></h5>
                                <p>
                                    <i class="fas fa-calendar-day"></i> <strong>Date:</strong> <?php echo $camp['camp_date']; ?><br>
                                    <i class="fas fa-map-marker-alt"></i> <strong>Location:</strong> <?php echo htmlspecialchars($camp['location']); ?><br>
                                    <i class="fas fa-tint"></i> <strong>Total Units:</strong> <?php echo $camp['total_units']; ?><br>
                                    <i class="fas fa-vials"></i> <strong>Blood Groups:</strong><br>
                                    <span class="blood-group-list">
                                        <?php foreach ($blood_groups as $group): ?>
                                            <?php if ($camp['group_units'][$group] > 0): ?>
                                                <i class="fas fa-tint"></i> <?php echo "$group: " . $camp['group_units'][$group] . " units<br>"; ?>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </span>
                                    <i class="fas fa-clock"></i> <strong>Created:</strong> <?php echo $camp['created_at']; ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-info w-100 text-center"><i class="fas fa-info-circle"></i> No blood camps found for your hospital.</div>
                    <?php endif; ?>
                </div>

                <h3 class="section-title"><i class="fas fa-vials"></i> Blood Inventory</h3>
                <div class="blood-stock">
                    <?php foreach ($blood_groups as $group): ?>
                        <div class="stock-card">
                            <i class="fas fa-tint"></i>
                            <h5><?php echo $group; ?></h5>
                            <p><?php echo $inventory[$group]; ?> Units</p>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="total-units">
                    <i class="fas fa-tint"></i> Total Units: <?php echo $total_units; ?>
                </div>
            </div>

            <!-- Inventory Modal -->
            <div class="modal fade" id="inventoryModal" tabindex="-1" aria-labelledby="inventoryModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="inventoryModalLabel"><i class="fas fa-vials"></i> Blood Inventory for Camp</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <button class="btn-custom mb-3" id="addBloodBtn"><i class="fas fa-plus-circle"></i> Add Blood</button>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><i class="fas fa-tint"></i> Blood Group</th>
                                            <th><i class="fas fa-vial"></i> Units</th>
                                            <th><i class="fas fa-user"></i> Donor Name</th>
                                            <th><i class="fas fa-id-card"></i> Donor NIC</th>
                                            <th><i class="fas fa-hospital"></i> Hospital ID</th>
                                            <th><i class="fas fa-clock"></i> Updated At</th>
                                            <th><i class="fas fa-cogs"></i> Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="inventoryTableBody">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Blood Modal -->
            <div class="modal fade" id="addBloodModal" tabindex="-1" aria-labelledby="addBloodModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content form-modal">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addBloodModalLabel"><i class="fas fa-plus-circle"></i> Add Blood Inventory</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="dateError" class="error" style="display: none;"><i class="fas fa-exclamation-circle"></i> Form only accessible on camp date (<?php echo date('Y-m-d'); ?>).</div>
                            <form id="addBloodForm">
                                <input type="hidden" id="campId" name="campId">
                                <input type="hidden" id="hospitalId" name="hospitalId" value="<?php echo $hospital_id; ?>">
                                <div class="mb-3 donor-search-container">
                                    <label for="donorNICSearch" class="form-label"><i class="fas fa-id-card"></i> Search Donor by NIC</label>
                                    <i class="fas fa-search"></i>
                                    <input type="text" id="donorNICSearch" name="donorNICSearch" class="form-control" placeholder="Enter donor NIC">
                                    <div id="donorSearchResults" class="donor-search-results"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="donorId" class="form-label"><i class="fas fa-id-badge"></i> Donor ID</label>
                                    <input type="text" id="donorId" name="donorId" class="form-control" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="donorNameDisplay" class="form-label"><i class="fas fa-user"></i> Donor Name</label>
                                    <input type="text" id="donorNameDisplay" class="form-control" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="donorNICDisplay" class="form-label"><i class="fas fa-id-card"></i> Donor NIC</label>
                                    <input type="text" id="donorNICDisplay" class="form-control" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="bloodGroup" class="form-label"><i class="fas fa-tint"></i> Blood Group</label>
                                    <select id="bloodGroup" name="bloodGroup" class="form-select" required>
                                        <option value="">Select Blood Group</option>
                                        <?php foreach ($blood_groups as $group): ?>
                                            <option value="<?php echo $group; ?>"><?php echo $group; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn-custom"><i class="fas fa-plus"></i> Add Blood</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Donor Modal -->
            <div class="modal fade" id="addDonorModal" tabindex="-1" aria-labelledby="addDonorModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content form-modal">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addDonorModalLabel"><i class="fas fa-user-plus"></i> Add New Donor</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="addDonorForm">
                                <input type="hidden" name="hospitalId" value="<?php echo $hospital_id; ?>">
                                <div class="mb-3">
                                    <label for="donorNIC" class="form-label"><i class="fas fa-id-card"></i> Donor NIC</label>
                                    <input type="text" id="donorNIC" name="donorNIC" class="form-control" required>
                                    <div id="nicError" class="error" style="display: none;"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="donorName" class="form-label"><i class="fas fa-user"></i> Donor Name</label>
                                    <input type="text" id="donorName" name="donorName" class="form-control" required>
                                    <div id="nameError" class="error" style="display: none;"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="donorBloodGroup" class="form-label"><i class="fas fa-tint"></i> Blood Group</label>
                                    <select id="donorBloodGroup" name="donorBloodGroup" class="form-select" required>
                                        <option value="">Select Blood Group</option>
                                        <?php foreach ($blood_groups as $group): ?>
                                            <option value="<?php echo $group; ?>"><?php echo $group; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn-custom"><i class="fas fa-user-plus"></i> Register Donor</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Update Blood Modal -->
            <div class="modal fade" id="updateBloodModal" tabindex="-1" aria-labelledby="updateBloodModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content form-modal">
                        <div class="modal-header">
                            <h5 class="modal-title" id="updateBloodModalLabel"><i class="fas fa-edit"></i> Update Blood Inventory</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="updateBloodForm">
                                <input type="hidden" id="updateInventoryId" name="inventoryId">
                                <div class="mb-3 donor-search-container">
                                    <label for="updateDonorNICSearch" class="form-label"><i class="fas fa-id-card"></i> Search Donor by NIC</label>
                                    <i class="fas fa-search"></i>
                                    <input type="text" id="updateDonorNICSearch" class="form-control" placeholder="Enter donor NIC">
                                    <div id="updateDonorSearchResults" class="donor-search-results"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="updateDonorId" class="form-label"><i class="fas fa-id-badge"></i> Donor ID</label>
                                    <input type="text" id="updateDonorId" name="donorId" class="form-control" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="updateDonorNameDisplay" class="form-label"><i class="fas fa-user"></i> Donor Name</label>
                                    <input type="text" id="updateDonorNameDisplay" class="form-control" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="updateDonorNICDisplay" class="form-label"><i class="fas fa-id-card"></i> Donor NIC</label>
                                    <input type="text" id="updateDonorNICDisplay" class="form-control" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="updateBloodGroup" class="form-label"><i class="fas fa-tint"></i> Blood Group</label>
                                    <select id="updateBloodGroup" name="bloodGroup" class="form-select" required>
                                        <option value="">Select Blood Group</option>
                                        <?php foreach ($blood_groups as $group): ?>
                                            <option value="<?php echo $group; ?>"><?php echo $group; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn-custom"><i class="fas fa-save"></i> Update Blood</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Update Camp Modal -->
            <div class="modal fade" id="updateCampModal" tabindex="-1" aria-labelledby="updateCampModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content form-modal">
                        <div class="modal-header">
                            <h5 class="modal-title" id="updateCampModalLabel"><i class="fas fa-edit"></i> Update Camp Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="updateCampForm">
                                <input type="hidden" id="updateCampId" name="campId">
                                <div class="mb-3">
                                    <label for="updateCampName" class="form-label"><i class="fas fa-campground"></i> Camp Name</label>
                                    <input type="text" id="updateCampName" name="campName" class="form-control" required>
                                    <div id="campNameError" class="error" style="display: none;"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="updateCampLocation" class="form-label"><i class="fas fa-map-marker-alt"></i> Location</label>
                                    <input type="text" id="updateCampLocation" name="campLocation" class="form-control" required>
                                    <div id="campLocationError" class="error" style="display: none;"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="updateCampDate" class="form-label"><i class="fas fa-calendar-day"></i> Camp Date</label>
                                    <input type="date" id="updateCampDate" name="campDate" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                                    <div id="campDateError" class="error" style="display: none;"></div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn-custom"><i class="fas fa-save"></i> Update Camp</button>
                                    <button type="button" id="deleteCampBtn" class="btn-delete"><i class="fas fa-trash"></i> Delete Camp</button>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        let currentCampId = null;
        let currentCampDate = null;
        const today = '<?php echo date('Y-m-d'); ?>';
        const bloodGroups = <?php echo json_encode($blood_groups); ?>;
        let currentFilter = 'today';

        // Initialize modals
        let inventoryModal, addBloodModal, addDonorModal, updateBloodModal, updateCampModal;
        try {
            inventoryModal = new bootstrap.Modal('#inventoryModal', { backdrop: 'static' });
            addBloodModal = new bootstrap.Modal('#addBloodModal', { backdrop: 'static' });
            addDonorModal = new bootstrap.Modal('#addDonorModal', { backdrop: 'static' });
            updateBloodModal = new bootstrap.Modal('#updateBloodModal', { backdrop: 'static' });
            updateCampModal = new bootstrap.Modal('#updateCampModal', { backdrop: 'static' });
            console.log('Modals initialized');
        } catch (error) {
            console.error('Modal initialization error:', error);
            alert('Error initializing modals. Please refresh the page.');
        }

        // Auto-load "Today" filter
        document.addEventListener('DOMContentLoaded', () => {
            try {
                const todayBtn = document.querySelector('.filter-btn[data-filter="today"]');
                if (todayBtn) todayBtn.click();
            } catch (error) {
                console.error('Error triggering Today filter:', error);
            }
        });

        // Debounce function
        function debounce(func, wait) {
            let timeout;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func(...args), wait);
            };
        }

        // Refresh camp list
        function refreshCampList(filter) {
            try {
                console.log('Refreshing camps with filter:', filter);
                fetch(`filter_camps.php?filter=${filter}&hospital_id=<?php echo $hospital_id; ?>`)
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        const campList = document.getElementById('campList');
                        campList.style.opacity = '0';
                        setTimeout(() => {
                            campList.innerHTML = '';
                            if (data.success && data.camps?.length) {
                                data.camps.forEach(camp => {
                                    const card = document.createElement('div');
                                    card.className = 'blood-card';
                                    card.dataset.bsToggle = 'modal';
                                    card.dataset.bsTarget = '#inventoryModal';
                                    card.dataset.campId = camp.id;
                                    card.dataset.campDate = camp.camp_date;
                                    card.innerHTML = `
                                        <button class="info-btn" data-bs-toggle="modal" data-bs-target="#updateCampModal"
                                                data-camp-id="${camp.id}" 
                                                data-camp-name="${camp.camp_name}" 
                                                data-camp-location="${camp.location}" 
                                                data-camp-date="${camp.camp_date}">
                                            <i class="fas fa-info-circle"></i>
                                        </button>
                                        <h5><i class="fas fa-campground"></i> ${camp.camp_name}</h5>
                                        <p>
                                            <i class="fas fa-calendar-day"></i> <strong>Date:</strong> ${camp.camp_date}<br>
                                            <i class="fas fa-map-marker-alt"></i> <strong>Location:</strong> ${camp.location}<br>
                                            <i class="fas fa-tint"></i> <strong>Total Units:</strong> ${camp.total_units}<br>
                                            <i class="fas fa-vials"></i> <strong>Blood Groups:</strong><br>
                                            <span class="blood-group-list">
                                                ${bloodGroups.map(group => camp.group_units[group] > 0 ? 
                                                    `<i class="fas fa-tint"></i> ${group}: ${camp.group_units[group]} units<br>` : '').join('')}
                                            </span>
                                            <i class="fas fa-clock"></i> <strong>Created:</strong> ${camp.created_at}
                                        </p>
                                    `;
                                    campList.appendChild(card);
                                });

                                // Bind blood card click
                                document.querySelectorAll('.blood-card').forEach(card => {
                                    card.removeEventListener('click', handleBloodCardClick);
                                    card.addEventListener('click', handleBloodCardClick);
                                });

                                // Bind info buttons
                                document.querySelectorAll('.info-btn').forEach(btn => {
                                    btn.removeEventListener('click', handleInfoClick);
                                    btn.addEventListener('click', handleInfoClick);
                                });
                            } else {
                                campList.innerHTML = '<div class="alert alert-info w-100 text-center"><i class="fas fa-info-circle"></i> No camps found for this filter.</div>';
                            }
                            campList.style.opacity = '1';
                        }, 300);
                    })
                    .catch(error => {
                        console.error('Filter error:', error);
                        campList.innerHTML = `<div class="alert alert-danger w-100 text-center"><i class="fas fa-exclamation-circle"></i> Error: ${error.message}</div>`;
                        campList.style.opacity = '1';
                    });
            } catch (error) {
                console.error('Refresh camp list error:', error);
                alert('Error refreshing camps. Please try again.');
            }
        }

        // Handle blood card click
        function handleBloodCardClick(e) {
            try {
                if (e.target.closest('.info-btn')) return;
                const card = e.currentTarget;
                currentCampId = card.dataset.campId;
                currentCampDate = card.dataset.campDate;
                document.getElementById('campId').value = currentCampId;
                console.log('Camp clicked: ID=', currentCampId, 'Date=', currentCampDate);
                loadInventory(currentCampId);
                inventoryModal.show();
            } catch (error) {
                console.error('Blood card click error:', error);
                alert('Error opening inventory. Please try again.');
            }
        }

        // Handle Info button click
        function handleInfoClick(e) {
            try {
                const btn = e.currentTarget;
                const campId = btn.dataset.campId;
                const campName = btn.dataset.campName;
                const campLocation = btn.dataset.campLocation;
                const campDate = btn.dataset.campDate;
                console.log('Info clicked: ID=', campId);
                document.getElementById('updateCampId').value = campId;
                document.getElementById('updateCampName').value = campName;
                document.getElementById('updateCampLocation').value = campLocation;
                document.getElementById('updateCampDate').value = campDate;
                document.getElementById('campNameError').style.display = 'none';
                document.getElementById('campLocationError').style.display = 'none';
                document.getElementById('campDateError').style.display = 'none';
                updateCampModal.show();
            } catch (error) {
                console.error('Info click error:', error);
                alert('Error opening camp details. Please try again.');
            }
        }

        // Filter buttons
        try {
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const filter = btn.dataset.filter;
                    currentFilter = filter;
                    console.log('Filter selected:', filter);
                    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    refreshCampList(filter);
                });
            });
        } catch (error) {
            console.error('Filter button setup error:', error);
            alert('Error setting up filters. Please refresh the page.');
        }

        // Load inventory
        function loadInventory(campId) {
            try {
                if (!campId) {
                    throw new Error('No camp ID provided');
                }
                console.log('Loading inventory for camp ID:', campId);
                fetch(`get_inventory.php?camp_id=${campId}&hospital_id=<?php echo $hospital_id; ?>`)
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        const tbody = document.getElementById('inventoryTableBody');
                        tbody.innerHTML = '';
                        if (data.success && data.data?.length) {
                            data.data.forEach(row => {
                                const tr = document.createElement('tr');
                                tr.innerHTML = `
                                    <td><i class="fas fa-tint"></i> ${row.blood_group || 'N/A'}</td>
                                    <td><i class="fas fa-vial"></i> ${row.units || 0}</td>
                                    <td><i class="fas fa-user"></i> ${row.donor_name || 'N/A'}</td>
                                    <td><i class="fas fa-id-card"></i> ${row.donor_nic || 'N/A'}</td>
                                    <td><i class="fas fa-hospital"></i> ${row.donor_hospital_id || 'N/A'}</td>
                                    <td><i class="fas fa-clock"></i> ${row.updated_at || 'N/A'}</td>
                                    <td>
                                        <button class="btn-custom btn-sm me-1" onclick="editInventory(${row.id}, '${row.blood_group}', ${row.donor_id || 'null'}, '${row.donor_name || ''}', '${row.donor_nic || ''}')"><i class="fas fa-edit"></i> Update</button>
                                        <button class="btn-custom btn-sm" onclick="deleteInventory(${row.id})"><i class="fas fa-trash"></i> Delete</button>
                                    </td>
                                `;
                                tbody.appendChild(tr);
                            });
                        } else {
                            tbody.innerHTML = '<tr><td colspan="7" class="text-center"><i class="fas fa-info-circle"></i> No inventory found.</td></tr>';
                        }
                    })
                    .catch(error => {
                        console.error('Inventory load error:', error);
                        document.getElementById('inventoryTableBody').innerHTML = 
                            `<tr><td colspan="7" class="text-center"><i class="fas fa-exclamation-circle"></i> Error: ${error.message}</td></tr>`;
                    });
            } catch (error) {
                console.error('Load inventory error:', error);
                alert('Error loading inventory. Please try again.');
            }
        }

        // Add Blood button
        try {
            document.getElementById('addBloodBtn').addEventListener('click', () => {
                console.log('Add Blood: campId=', currentCampId, 'campDate=', currentCampDate);
                if (!currentCampId) {
                    alert('Please select a camp first.');
                    return;
                }
                if (currentCampDate !== today) {
                    document.getElementById('dateError').style.display = 'block';
                    document.getElementById('addBloodForm').style.display = 'none';
                } else {
                    document.getElementById('dateError').style.display = 'none';
                    document.getElementById('addBloodForm').style.display = 'block';
                    document.getElementById('addBloodForm').reset();
                    document.getElementById('campId').value = currentCampId;
                    document.getElementById('donorId').value = '';
                    document.getElementById('donorNameDisplay').value = '';
                    document.getElementById('donorNICDisplay').value = '';
                    document.getElementById('bloodGroup').value = '';
                    document.getElementById('donorNICSearch').value = '';
                    document.getElementById('donorSearchResults').innerHTML = '';
                    addBloodModal.show();
                }
            });
        } catch (error) {
            console.error('Add Blood button error:', error);
        }

        // Donor search
        function setupDonorSearch(searchInputId, resultsId, donorIdInputId, donorNameInputId, donorNICInputId, bloodGroupSelectId) {
            try {
                const searchInput = document.getElementById(searchInputId);
                const resultsDiv = document.getElementById(resultsId);
                if (!searchInput || !resultsDiv) throw new Error('Search elements not found');

                resultsDiv.style.display = 'block';
                resultsDiv.style.visibility = 'visible';

                const searchDonors = debounce(query => {
                    console.log('Searching donors:', query);
                    fetch(`search_donors.php?query=${encodeURIComponent(query)}`)
                        .then(response => {
                            if (!response.ok) throw new Error(`HTTP ${response.status}`);
                            return response.json();
                        })
                        .then(data => {
                            resultsDiv.innerHTML = '';
                            if (data.success && data.data?.length) {
                                data.data.forEach(donor => {
                                    const div = document.createElement('div');
                                    div.innerHTML = `<i class="fas fa-id-card"></i> ${donor.NIC} (Name: ${donor.donor_name}, ID: ${donor.id}, Blood: ${donor.blood_group})`;
                                    div.addEventListener('click', () => {
                                        document.getElementById(donorIdInputId).value = donor.id;
                                        document.getElementById(donorNameInputId).value = donor.donor_name;
                                        document.getElementById(donorNICInputId).value = donor.NIC;
                                        document.getElementById(bloodGroupSelectId).value = donor.blood_group;
                                        resultsDiv.innerHTML = '';
                                        console.log('Selected donor:', donor);
                                    });
                                    resultsDiv.appendChild(div);
                                });
                            } else {
                                const addDonorDiv = document.createElement('div');
                                addDonorDiv.className = 'add-donor-result';
                                addDonorDiv.innerHTML = `<i class="fas fa-user-plus"></i> No donors found. Add "${query}"?`;
                                addDonorDiv.addEventListener('click', () => {
                                    document.getElementById('donorNIC').value = query;
                                    document.getElementById('donorName').value = '';
                                    document.getElementById('donorBloodGroup').value = '';
                                    addDonorModal.show();
                                });
                                resultsDiv.appendChild(addDonorDiv);
                            }
                        })
                        .catch(error => {
                            console.error('Donor search error:', error);
                            resultsDiv.innerHTML = `<div class="error"><i class="fas fa-exclamation-circle"></i> Error: ${error.message}</div>`;
                        });
                }, 300);

                searchInput.addEventListener('input', () => {
                    const query = searchInput.value.trim();
                    if (query.length >= 1) searchDonors(query);
                    else resultsDiv.innerHTML = '';
                });
            } catch (error) {
                console.error('Donor search setup error:', error);
            }
        }

        try {
            setupDonorSearch('donorNICSearch', 'donorSearchResults', 'donorId', 'donorNameDisplay', 'donorNICDisplay', 'bloodGroup');
            setupDonorSearch('updateDonorNICSearch', 'updateDonorSearchResults', 'updateDonorId', 'updateDonorNameDisplay', 'updateDonorNICDisplay', 'updateBloodGroup');
        } catch (error) {
            console.error('Donor search setup error:', error);
        }

        // Add Blood form
        try {
            document.getElementById('addBloodForm').addEventListener('submit', e => {
                e.preventDefault();
                const formData = new FormData(e.target);
                formData.append('units', 1);
                if (!formData.get('bloodGroup')) {
                    alert('Please select a blood group.');
                    return;
                }
                if (!formData.get('campId')) {
                    alert('No camp selected.');
                    return;
                }
                console.log('Adding blood:', Object.fromEntries(formData));
                fetch('add_blood.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert('Blood added successfully!');
                            addBloodModal.hide();
                            loadInventory(currentCampId);
                            e.target.reset();
                            document.getElementById('donorId').value = '';
                            document.getElementById('donorNameDisplay').value = '';
                            document.getElementById('donorNICDisplay').value = '';
                            document.getElementById('bloodGroup').value = '';
                            document.getElementById('donorNICSearch').value = '';
                            document.getElementById('donorSearchResults').innerHTML = '';
                        } else {
                            alert(data.error || 'Error adding blood.');
                        }
                    })
                    .catch(error => {
                        console.error('Add blood error:', error);
                        alert(`Error adding blood: ${error.message}`);
                    });
            });
        } catch (error) {
            console.error('Add blood form error:', error);
        }

        // Add Donor form
        try {
            document.getElementById('addDonorForm').addEventListener('submit', e => {
                e.preventDefault();
                const nic = document.getElementById('donorNIC').value.trim();
                const name = document.getElementById('donorName').value.trim();
                const bloodGroup = document.getElementById('donorBloodGroup').value;
                const nicError = document.getElementById('nicError');
                const nameError = document.getElementById('nameError');

                if (!nic) {
                    nicError.innerHTML = '<i class="fas fa-exclamation-circle"></i> Donor NIC required.';
                    nicError.style.display = 'block';
                    return;
                }
                if (!name) {
                    nameError.innerHTML = '<i class="fas fa-exclamation-circle"></i> Donor name required.';
                    nameError.style.display = 'block';
                    return;
                }
                if (!bloodGroup) {
                    nameError.innerHTML = '<i class="fas fa-exclamation-circle"></i> Select blood group.';
                    nameError.style.display = 'block';
                    return;
                }
                nicError.style.display = 'none';
                nameError.style.display = 'none';

                const formData = new FormData(e.target);
                fetch('add_donor.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert('Donor added successfully!');
                            addDonorModal.hide();
                            document.getElementById('donorId').value = data.donor_id;
                            document.getElementById('donorNameDisplay').value = name;
                            document.getElementById('donorNICDisplay').value = nic;
                            document.getElementById('bloodGroup').value = bloodGroup;
                            document.getElementById('donorNICSearch').value = nic;
                            e.target.reset();
                        } else {
                            nicError.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${data.error || 'Error adding donor.'}`;
                            nicError.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Add donor error:', error);
                        nicError.innerHTML = `<i class="fas fa-exclamation-circle"></i> Error: ${error.message}`;
                        nicError.style.display = 'block';
                    });
            });
        } catch (error) {
            console.error('Add donor form error:', error);
        }

        // Edit Inventory
        function editInventory(id, bloodGroup, donorId, donorName, donorNIC) {
            try {
                console.log('Editing inventory:', { id, bloodGroup, donorId });
                document.getElementById('updateInventoryId').value = id;
                document.getElementById('updateBloodGroup').value = bloodGroup;
                document.getElementById('updateDonorId').value = donorId || '';
                document.getElementById('updateDonorNameDisplay').value = donorName || '';
                document.getElementById('updateDonorNICDisplay').value = donorNIC || '';
                document.getElementById('updateDonorNICSearch').value = donorNIC || '';
                document.getElementById('updateDonorSearchResults').innerHTML = '';
                updateBloodModal.show();
            } catch (error) {
                console.error('Edit inventory error:', error);
                alert('Error editing inventory. Please try again.');
            }
        }

        // Update Blood form
        try {
            document.getElementById('updateBloodForm').addEventListener('submit', e => {
                e.preventDefault();
                const formData = new FormData(e.target);
                formData.append('units', 1);
                if (!formData.get('bloodGroup')) {
                    alert('Please select a blood group.');
                    return;
                }
                console.log('Updating blood:', Object.fromEntries(formData));
                fetch('update_blood.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert('Blood updated successfully!');
                            updateBloodModal.hide();
                            loadInventory(currentCampId);
                            e.target.reset();
                            document.getElementById('updateDonorId').value = '';
                            document.getElementById('updateDonorNameDisplay').value = '';
                            document.getElementById('updateDonorNICDisplay').value = '';
                            document.getElementById('updateBloodGroup').value = '';
                            document.getElementById('updateDonorNICSearch').value = '';
                            document.getElementById('updateDonorSearchResults').innerHTML = '';
                        } else {
                            alert(data.error || 'Error updating blood.');
                        }
                    })
                    .catch(error => {
                        console.error('Update blood error:', error);
                        alert(`Error updating blood: ${error.message}`);
                    });
            });
        } catch (error) {
            console.error('Update blood form error:', error);
        }

        // Delete Inventory
        function deleteInventory(id) {
            try {
                if (!confirm('Are you sure you want to delete this record?')) return;
                console.log('Deleting inventory:', id);
                fetch('delete_blood.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `inventoryId=${id}`
                })
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert('Blood record deleted successfully!');
                            loadInventory(currentCampId);
                        } else {
                            alert(data.error || 'Error deleting blood.');
                        }
                    })
                    .catch(error => {
                        console.error('Delete blood error:', error);
                        alert(`Error deleting blood: ${error.message}`);
                    });
            } catch (error) {
                console.error('Delete inventory error:', error);
                alert('Error deleting inventory. Please try again.');
            }
        }

        // Update Camp form
        try {
            document.getElementById('updateCampForm').addEventListener('submit', e => {
                e.preventDefault();
                const campName = document.getElementById('updateCampName').value.trim();
                const campLocation = document.getElementById('updateCampLocation').value.trim();
                const campDate = document.getElementById('updateCampDate').value;
                const errors = {
                    name: document.getElementById('campNameError'),
                    location: document.getElementById('campLocationError'),
                    date: document.getElementById('campDateError')
                };

                errors.name.style.display = 'none';
                errors.location.style.display = 'none';
                errors.date.style.display = 'none';

                if (!campName) {
                    errors.name.innerHTML = '<i class="fas fa-exclamation-circle"></i> Camp name required.';
                    errors.name.style.display = 'block';
                    return;
                }
                if (!campLocation) {
                    errors.location.innerHTML = '<i class="fas fa-exclamation-circle"></i> Location required.';
                    errors.location.style.display = 'block';
                    return;
                }
                if (!campDate) {
                    errors.date.innerHTML = '<i class="fas fa-exclamation-circle"></i> Camp date required.';
                    errors.date.style.display = 'block';
                    return;
                }

                const selectedDate = new Date(campDate);
                const todayDate = new Date(today);
                todayDate.setHours(0, 0, 0, 0);
                if (selectedDate < todayDate) {
                    errors.date.innerHTML = '<i class="fas fa-exclamation-circle"></i> Camp date cannot be in the past.';
                    errors.date.style.display = 'block';
                    return;
                }

                const formData = new FormData(e.target);
                console.log('Updating camp:', Object.fromEntries(formData));
                fetch('update_camp.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert('Camp updated successfully!');
                            updateCampModal.hide();
                            refreshCampList(currentFilter);
                        } else {
                            errors.name.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${data.error || 'Error updating camp.'}`;
                            errors.name.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Update camp error:', error);
                        errors.name.innerHTML = `<i class="fas fa-exclamation-circle"></i> Error: ${error.message}`;
                        errors.name.style.display = 'block';
                    });
            });
        } catch (error) {
            console.error('Update camp form error:', error);
            alert('Error setting up camp update. Please refresh the page.');
        }

        // Delete Camp
        try {
            document.getElementById('deleteCampBtn').addEventListener('click', () => {
                const campId = document.getElementById('updateCampId').value;
                console.log('Deleting camp: ID=', campId);
                if (!campId) {
                    alert('No camp selected.');
                    return;
                }
                if (!confirm('Are you sure you want to permanently delete this camp?')) return;

                fetch('delete_camp.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `campId=${campId}&hospitalId=<?php echo $hospital_id; ?>`
                })
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert('Camp deleted successfully!');
                            updateCampModal.hide();
                            refreshCampList(currentFilter);
                        } else {
                            alert(data.error || 'Error deleting camp.');
                        }
                    })
                    .catch(error => {
                        console.error('Delete camp error:', error);
                        alert(`Error deleting camp: ${error.message}`);
                    });
            });
        } catch (error) {
            console.error('Delete camp button error:', error);
            alert('Error setting up camp deletion. Please refresh the page.');
        }
    </script>
</body>
</html>

<?php
$stmt_camps->close();
$stmt_inventory->close();
$stmt_total->close();
$conn->close();
?>