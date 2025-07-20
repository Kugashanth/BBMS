
<?php
session_start();

include 'config/db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$hospital_id = $_SESSION['user_id'];

// Fetch distinct locations for filter dropdown
$sql_locations = "SELECT DISTINCT COALESCE(location, 'Unknown') AS location 
                 FROM hospitals 
                 WHERE id != ? 
                 ORDER BY location";
$stmt_locations = $conn->prepare($sql_locations);
$stmt_locations->bind_param("i", $hospital_id);
$stmt_locations->execute();
$result_locations = $stmt_locations->get_result();
$locations = [];
while ($row = $result_locations->fetch_assoc()) {
    $locations[] = $row['location'];
}
$stmt_locations->close();

// Fetch all hospitals except the logged-in one (initial load)
$sql_hospitals = "SELECT h.id, h.name, COALESCE(h.location, 'Unknown') AS location 
                 FROM hospitals h 
                 WHERE h.id != ?";
$stmt_hospitals = $conn->prepare($sql_hospitals);
$stmt_hospitals->bind_param("i", $hospital_id);
$stmt_hospitals->execute();
$result_hospitals = $stmt_hospitals->get_result();

$hospitals = [];
$blood_groups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
while ($hospital = $result_hospitals->fetch_assoc()) {
    $sql_inventory = "SELECT blood_group, COALESCE(SUM(units), 0) as total_units 
                     FROM blood_inventory 
                     WHERE hospital_id = ? 
                     GROUP BY blood_group";
    $stmt_inventory = $conn->prepare($sql_inventory);
    $stmt_inventory->bind_param("i", $hospital['id']);
    $stmt_inventory->execute();
    $result_inventory = $stmt_inventory->get_result();

    $inventory = array_fill_keys($blood_groups, 0);
    $total_units = 0;
    while ($row = $result_inventory->fetch_assoc()) {
        $inventory[$row['blood_group']] = $row['total_units'];
        $total_units += $row['total_units'];
    }

    $hospital['inventory'] = $inventory;
    $hospital['total_units'] = $total_units;
    $hospitals[] = $hospital;
    $stmt_inventory->close();
}

$stmt_hospitals->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Blood</title>
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
            width: 100vw;
        }

        /* Hero Section */
        .hero-section {
            padding-left:100px;
            padding-right:100px;
        }

       

        @keyframes fadeIn {
            from { opacity: 0; transform: translate(-50%, -60%) scale(0.9); }
            to { opacity: 1; transform: translate(-50%, -50%) scale(1); }
        }

        .container {
            max-width: 1300px;
            margin: auto;
            padding: 50px 20px;
            width: 100%;
        }

        /* Filter Section */
        .filter-section {
            background: rgba(255, 255, 255, 0.98);
            border: 2px solid transparent;
            border-image: linear-gradient(to bottom, #dc3545, #c82333) 1;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .filter-icon-btn {
            background: #dc3545;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-size: 1rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .filter-icon-btn:hover {
            background: #c82333;
            transform: scale(1.05);
        }

        .filter-icon-btn i {
            margin-right: 8px;
        }

        .filter-content {
            display: none;
            animation: slideDown 0.3s ease;
        }

        .filter-content.show {
            display: block;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .filter-section h4 {
            color: #dc3545;
            font-weight: 600;
            font-size: 1.5rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .filter-section h4 i {
            margin-right: 10px;
        }

        .filter-section .form-control, .filter-section .form-select {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 10px;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }

        .filter-section .form-control:focus, .filter-section .form-select:focus {
            border-color: #dc3545;
            box-shadow: 0 0 12px rgba(220, 53, 69, 0.4);
            outline: none;
        }

        .filter-section .form-select.active-filter {
            border-color: #dc3545;
            box-shadow: 0 0 12px rgba(220, 53, 69, 0.6);
        }

        .filter-section .btn-custom {
            margin-right: 10px;
            margin-top: 10px;
        }

        .filter-section .badge {
            background: #dc3545;
            color: #fff;
            padding: 6px 12px;
            border-radius: 12px;
            margin-right: 10px;
            margin-bottom: 10px;
            display: inline-flex;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-section .badge:hover {
            background: #c82333;
        }

        .filter-section .badge i {
            margin-left: 6px;
        }

        .loading-spinner {
            display: none;
            font-size: 1.2rem;
            color: #dc3545;
            margin-left: 10px;
        }

        /* Hospital Cards */
        .hospital-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 50px;
            width: 100%;
        }

        .hospital-card {
            background: rgba(255, 255, 255, 0.95);
            border: 2px solid transparent;
            border-image: linear-gradient(to bottom, #dc3545, #c82333) 1;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(10px);
            transition: transform 0.4s, box-shadow 0.4s;
            position: relative;
            overflow: hidden;
            perspective: 1000px;
            cursor: pointer;
            animation: cardFadeIn 0.5s ease;
        }

        .hospital-card:hover {
            transform: translateY(-15px) rotateX(5deg) rotateY(5deg);
            box-shadow: 0 12px 40px rgba(220, 53, 69, 0.5);
        }

        @keyframes cardFadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* .hospital-card::before {
            content: '\f4ba';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 3rem;
            color: #dc3545;
            opacity: 0.2;
            transition: transform 0.4s, opacity 0.4s, color 0.4s;
        }

        .hospital-card:hover::before {
            transform: scale(1.2);
            opacity: 0.3;
            color: #c82333;
            animation: pulseIcon 1.5s infinite;
        } */

        @keyframes pulseIcon {
            0% { transform: scale(1.2); }
            50% { transform: scale(1.4); }
            100% { transform: scale(1.2); }
        }

        .hospital-card h5 {
            color: #dc3545;
            font-weight: 700;
            font-size: 1.6rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            position: relative;
            letter-spacing: 0.8px;
        }

        .hospital-card h5::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(to right, #dc3545, #c82333);
            border-radius: 2px;
        }
      
        .hospital-card h5 i {
            margin-right: 12px;
            color: #dc3545;
            font-size: 1.4rem;
            transition: transform 0.3s, color 0.3s;
        }

        .hospital-card:hover h5 i {
            transform: scale(1.2);
            color: #c82333;
        }

        .hospital-card p {
            margin: 12px 0;
            font-size: 0.95rem;
            line-height: 1.8;
            color: #333;
            letter-spacing: 0.5px;
        }

        .hospital-card p i {
            margin-right: 8px;
            color: #dc3545;
            font-size: 1.2rem;
            transition: transform 0.3s, color 0.3s;
        }

        .hospital-card p:hover i {
            transform: scale(1.2);
            color: #c82333;
        }

        .hospital-card p strong {
            color: #dc3545;
            font-weight: 600;
        }

        .blood-group-list {
            display: block;
            margin-left: 30px;
        }

        /* Requests Table */
        .table-responsive {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            background: linear-gradient(#dc3545, #c82333);
            color: #fff;
            font-weight: 500;
            border: none;
            padding: 15px;
        }

        .table td {
            padding: 15px;
            vertical-align: middle;
            border: none;
            background: #fff;
            transition: all 0.3s ease;
        }

        .table tr:nth-child(even) td {
            background: #f8f9fa;
        }

        .table tr:hover td {
            background: #ffe5e7;
        }

        .table .btn-custom {
            padding: 8px 12px;
            font-size: 0.85rem;
        }

        .table .btn-custom i {
            margin-right: 5px;
        }

        .table .btn-custom:hover i {
            transform: scale(1.2);
        }

        .table .badge {
            padding: 6px 12px;
            font-size: 0.85rem;
        }

        /* Buttons */
        .btn-custom {
            background: #dc3545;
            color: #fff;
            border: none;
            border-radius: 12px;
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
            transition: transform 0.3s;
        }

        .btn-custom:hover {
            background: linear-gradient(45deg, #dc3545, #c82333);
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(220, 53, 69, 0.5);
        }

        .btn-custom:hover i {
            transform: translateX(3px);
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
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.4s, height 0.4s;
        }

        .btn-custom:hover::after {
            width: 200px;
            height: 200px;
        }

        /* Modal */
        .form-modal {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            animation: zoomIn 0.5s ease;
        }

        @keyframes zoomIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .form-modal input, .form-modal select {
            width: 100%;
            padding: 12px 40px 12px 15px;
            margin: 12px 0;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            transition: all 0.3s ease;
            background: #f8f9fa;
            font-size: 0.95rem;
        }

        .form-modal input:focus, .form-modal select:focus {
            border-color: #dc3545;
            box-shadow: 0 0 12px rgba(220, 53, 69, 0.4);
            transform: scale(1.02);
            outline: none;
        }

        .form-modal .form-label {
            font-weight: 500;
            color: #333;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
        }

        .form-modal .form-label i {
            margin-right: 8px;
            color: #dc3545;
        }

        .form-modal .error {
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 5px;
            display: flex;
            align-items: center;
        }

        .form-modal .error i {
            margin-right: 6px;
        }

        /* Modal Headers and Footers */
        .modal-header {
            border-bottom: 2px solid #dc3545;
            background: linear-gradient(#fff, #f8f9fa);
            border-radius: 20px 20px 0 0;
        }

        .modal-footer {
            border-top: 2px solid #dc3545;
            background: linear-gradient(#f8f9fa, #fff);
            border-radius: 0 0 20px 20px;
        }

        .modal-header .btn-close {
            background: #dc3545;
            color: #fff;
            border-radius: 50%;
            opacity: 1;
            padding: 8px;
            transition: transform 0.3s;
        }

        .modal-header .btn-close:hover {
            transform: rotate(180deg);
        }

        /* Toast */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1055;
        }

        .toast {
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .toast-header {
            background: #dc3545;
            color: #fff;
            border-radius: 12px 12px 0 0;
        }

        .toast-body {
            background: #fff;
            color: #333;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-text {
                padding: 20px 30px;
            }

            .hero-text h1 {
                font-size: 2rem;
            }

            .hero-text p {
                font-size: 1.1rem;
            }

            .hero-image {
                height: 300px;
            }

            .hospital-list {
                grid-template-columns: 1fr;
            }

            .hospital-card {
                padding: 15px;
            }

            .hospital-card h5 {
                font-size: 1.4rem;
            }

            .hospital-card h5 i {
                font-size: 1.2rem;
            }

            .hospital-card p i {
                font-size: 1rem;
            }

            .form-modal {
                padding: 20px;
            }

            .table th, .table td {
                font-size: 0.85rem;
                padding: 10px;
            }

            .table .btn-custom {
                padding: 6px 10px;
                font-size: 0.8rem;
            }

            .filter-section {
                padding: 15px;
            }

            .filter-section .row > div {
                margin-bottom: 15px;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 20px 15px;
            }

            .hero-text {
                padding: 15px 20px;
            }

            .hero-text h1 {
                font-size: 1.6rem;
            }

            .hero-text p {
                font-size: 0.95rem;
            }

            .form-modal input, .form-modal select {
                font-size: 0.9rem;
                padding: 10px 35px 10px 12px;
            }

            .btn-custom {
                padding: 10px 20px;
                font-size: 0.9rem;
            }

            .btn-custom i {
                font-size: 0.9rem;
            }

            .hospital-card h5::after {
                width: 30px;
            }

            .filter-section h4 {
                font-size: 1.3rem;
            }

            .filter-icon-btn {
                width: 100%;
                justify-content: center;
            }
        }
          .navbar-fixed {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .content-wrapper {
            padding-top: 80px; /* Adjust based on navbar height */
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
      
        
    

            <!-- Toast Container -->
            <div class="toast-container">
                <div id="successToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong class="me-auto">Success</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body" id="successToastMessage"></div>
                </div>
                <div id="errorToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header bg-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong class="me-auto">Error</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body" id="errorToastMessage"></div>
                </div>
            </div>

            <div class="container">
                <div class="filter-section">
                    <button class="filter-icon-btn" id="filterToggle">
                        <
                        <i class="fas fa-filter"></i> Filter (<span id="filterCount">0</span>)
                    </button>
                    <div id="filterContent" class="filter-content">
                        <h4><i class="fas fa-filter"></i> Filter Hospitals</h4>
                        <div id="activeFilters" class="mb-3"></div>
                        <form id="filterForm">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="filterLocation" class="form-label"><i class="fas fa-map-marker-alt"></i> Location</label>
                                    <select id="filterLocation" name="location" class="form-select">
                                        <option value="">Any Location</option>
                                        <?php foreach ($locations as $location): ?>
                                            <option value="<?php echo htmlspecialchars($location); ?>">
                                                <?php echo htmlspecialchars($location); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="filterBloodGroup" class="form-label"><i class="fas fa-tint"></i> Blood Group</label>
                                    <select id="filterBloodGroup" name="bloodGroup" class="form-select">
                                        <option value="">Any Blood Group</option>
                                        <?php foreach ($blood_groups as $group): ?>
                                            <option value="<?php echo $group; ?>"><?php echo $group; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="filterSort" class="form-label"><i class="fas fa-sort"></i> Sort By</label>
                                    <select id="filterSort" name="sort" class="form-select">
                                        <option value="name_asc">Name (A-Z)</option>
                                        <option value="name_desc">Name (Z-A)</option>
                                        <option value="units_desc">Total Units (High-Low)</option>
                                        <option value="units_asc">Total Units (Low-High)</option>
                                    </select>
                                </div>
                            </div>
                            <button type="button" id="clearFilters" class="btn-custom"><i class="fas fa-times"></i> Clear Filters</button>
                            <i class="fas fa-spinner fa-spin loading-spinner"></i>
                        </form>
                    </div>
                </div>

                <h3 class="text-center my-4"><i class="fas fa-hospital me-2"></i> Available Hospitals</h3>
                <div class="hospital-list" id="hospitalList">
                    <?php foreach ($hospitals as $hospital): ?>
                        <div class="hospital-card" 
                            data-bs-toggle="modal" 
                            data-bs-target="#requestBloodModal"
                            data-hospital-id="<?php echo $hospital['id']; ?>"
                            data-hospital-name="<?php echo htmlspecialchars($hospital['name']); ?>">
                            <h5><i class="fas fa-hospital"></i> <?php echo htmlspecialchars($hospital['name']); ?></h5>
                            <p><i class="fas fa-map-marker-alt"></i> <strong>Location:</strong> <?php echo htmlspecialchars($hospital['location']); ?></p>
                            <p><i class="fas fa-droplet"></i> <strong>Total Units:</strong> <?php echo $hospital['total_units']; ?></p>
                            <p><i class="fas fa-vials"></i> <strong>Blood Groups:</strong><br>
                                <span class="blood-group-list">
                                    <?php foreach ($blood_groups as $group): ?>
                                        <?php if ($hospital['inventory'][$group] > 0): ?>
                                            <?php echo "$group: {$hospital['inventory'][$group]} units<br>"; ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </span>
                            </p>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($hospitals)): ?>
                        <div class="alert alert-info w-100 text-center"><i class="fas fa-circle-info me-2"></i> No other hospitals found.</div>
                    <?php endif; ?>
                </div>

                <h3 class="text-center my-4"><i class="fas fa-list me-2"></i> Your Blood Requests</h3>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Requested Hospital</th>
                                <th>Blood Group</th>
                                <th>Units</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="requestsTableBody">
                            <!-- Requests loaded dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Request Blood Modal -->
            <div class="modal fade" id="requestBloodModal" tabindex="-1" aria-labelledby="requestBloodModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content form-modal">
                        <div class="modal-header">
                            <h5 class="modal-title" id="requestBloodModalLabel"><i class="fas fa-syringe me-2"></i> Request Blood</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="requestBloodForm">
                                <input type="hidden" id="requestingHospitalId" name="requestingHospitalId" value="<?php echo $hospital_id; ?>">
                                <input type="hidden" id="requestedHospitalId" name="requestedHospitalId">
                                <div class="mb-3">
                                    <label for="hospitalNameDisplay" class="form-label"><i class="fas fa-hospital"></i> Requested Hospital</label>
                                    <input type="text" id="hospitalNameDisplay" class="form-control" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="bloodGroup" class="form-label"><i class="fas fa-tint"></i> Blood Group</label>
                                    <select id="bloodGroup" name="bloodGroup" class="form-select" required>
                                        <option value="">Select Blood Group</option>
                                        <?php foreach ($blood_groups as $group): ?>
                                            <option value="<?php echo $group; ?>"><?php echo $group; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div id="bloodGroupError" class="error" style="display: none;"><i class="fas fa-exclamation-circle"></i> This blood group is not available.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="units" class="form-label"><i class="fas fa-droplet"></i> Units</label>
                                    <input type="number" id="units" name="units" class="form-control" min="1" required>
                                    <div id="unitsError" class="error" style="display: none;"><i class="fas fa-exclamation-circle"></i> Units must be a positive number.</div>
                                    <div id="unitsAvailError" class="error" style="display: none;"><i class="fas fa-exclamation-circle"></i> Requested units exceed available stock.</div>
                                </div>
                                <button type="submit" class="btn-custom"><i class="fas fa-syringe"></i> Submit Request</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>
<div class="content2">
    <?php include 'footer.php'; ?>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        // Initialize modal and toasts
        const requestBloodModal = new bootstrap.Modal(document.getElementById('requestBloodModal'), { backdrop: 'static' });
        const successToast = new bootstrap.Toast(document.getElementById('successToast'));
        const errorToast = new bootstrap.Toast(document.getElementById('errorToast'));

        // Toggle filter visibility
        document.getElementById('filterToggle').addEventListener('click', () => {
            const filterContent = document.getElementById('filterContent');
            filterContent.classList.toggle('show');
            console.log('Filter toggled:', filterContent.classList.contains('show') ? 'Visible' : 'Hidden');
        });

        // Update filter badges and count
        function updateFilterBadges() {
            const location = document.getElementById('filterLocation').value;
            const bloodGroup = document.getElementById('filterBloodGroup').value;
            const sort = document.getElementById('filterSort').value;
            const activeFilters = document.getElementById('activeFilters');
            activeFilters.innerHTML = '';
            let count = 0;

            if (location) {
                activeFilters.innerHTML += `<span class="badge" onclick="clearFilter('filterLocation')"><i class="fas fa-map-marker-alt me-1"></i> ${location} <i class="fas fa-times"></i></span>`;
                document.getElementById('filterLocation').classList.add('active-filter');
                count++;
            } else {
                document.getElementById('filterLocation').classList.remove('active-filter');
            }
            if (bloodGroup) {
                activeFilters.innerHTML += `<span class="badge" onclick="clearFilter('filterBloodGroup')"><i class="fas fa-tint me-1"></i> ${bloodGroup} <i class="fas fa-times"></i></span>`;
                document.getElementById('filterBloodGroup').classList.add('active-filter');
                count++;
            } else {
                document.getElementById('filterBloodGroup').classList.remove('active-filter');
            }
            if (sort !== 'name_asc') {
                const sortText = {
                    'name_desc': 'Name (Z-A)',
                    'units_desc': 'Units (High-Low)',
                    'units_asc': 'Units (Low-High)'
                }[sort] || 'Sort';
                activeFilters.innerHTML += `<span class="badge" onclick="clearFilter('filterSort')"><i class="fas fa-sort me-1"></i> ${sortText} <i class="fas fa-times"></i></span>`;
                document.getElementById('filterSort').classList.add('active-filter');
                count++;
            } else {
                document.getElementById('filterSort').classList.remove('active-filter');
            }

            document.getElementById('filterCount').textContent = count;
            console.log('Filter badges updated, count:', count);
        }

        // Clear individual filter
        function clearFilter(field) {
            const element = document.getElementById(field);
            if (field === 'filterSort') {
                element.value = 'name_asc';
            } else {
                element.value = '';
            }
            updateFilterBadges();
            console.log(`Cleared filter: ${field}`);
            filterHospitals();
        }

        // Filter hospitals
        function filterHospitals() {
            const spinner = document.querySelector('.loading-spinner');
            spinner.style.display = 'inline-block';
            const form = document.getElementById('filterForm');
            const formData = new FormData(form);

            const params = new URLSearchParams();
            formData.forEach((value, key) => {
                if (value) {
                    params.append(key, value);
                }
            });
            const queryString = params.toString();

            // Check cache
            const cached = sessionStorage.getItem(`filter_${queryString}`);
            if (cached) {
                console.log('Cache hit for query:', queryString);
                spinner.style.display = 'none';
                const data = JSON.parse(cached);
                renderHospitals(data);
                updateFilterBadges();
                return;
            }

            fetch('filter_hospitals.php?' + queryString, {
                method: 'GET'
            })
                .then(response => {
                    console.log('Filter hospitals response status:', response.status);
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`HTTP error: ${response.status}, Response: ${text}`);
                        });
                    }
                    return response.text();
                })
                .then(text => {
                    console.log('Filter hospitals raw response:', text);
                    try {
                        const data = JSON.parse(text);
                        console.log('Filter hospitals parsed data:', data);
                        spinner.style.display = 'none';
                        if (data.success) {
                            sessionStorage.setItem(`filter_${queryString}`, JSON.stringify(data));
                            console.log('Cached result for query:', queryString);
                            renderHospitals(data);
                        } else {
                            document.getElementById('hospitalList').innerHTML = 
                                `<div class="alert alert-danger w-100 text-center"><i class="fas fa-exclamation-circle me-2"></i> ${data.error || 'Error filtering hospitals'}</div>`;
                        }
                        updateFilterBadges();
                    } catch (e) {
                        throw new Error(`JSON parse error: ${e.message}, Response: ${text}`);
                    }
                })
                .catch(error => {
                    console.error('Filter hospitals error:', error);
                    spinner.style.display = 'none';
                    document.getElementById('hospitalList').innerHTML = 
                        `<div class="alert alert-danger w-100 text-center"><i class="fas fa-exclamation-circle me-2"></i> Error filtering hospitals: ${error.message}</div>`;
                    updateFilterBadges();
                });
        }

        // Render hospitals
        function renderHospitals(data) {
            const hospitalList = document.getElementById('hospitalList');
            hospitalList.innerHTML = '';
            if (data.hospitals && data.hospitals.length > 0) {
                const bloodGroups = <?php echo json_encode($blood_groups); ?>;
                data.hospitals.forEach(hospital => {
                    const card = document.createElement('div');
                    card.className = 'hospital-card';
                    card.setAttribute('data-bs-toggle', 'modal');
                    card.setAttribute('data-bs-target', '#requestBloodModal');
                    card.setAttribute('data-hospital-id', hospital.id);
                    card.setAttribute('data-hospital-name', hospital.name);
                    let bloodGroupHtml = '';
                    bloodGroups.forEach(group => {
                        if (hospital.inventory[group] > 0) {
                            bloodGroupHtml += `${group}: ${hospital.inventory[group]} units<br>`;
                        }
                    });
                    card.innerHTML = `
                        <h5><i class="fas fa-hospital"></i> ${hospital.name}</h5>
                        <p><i class="fas fa-map-marker-alt"></i> <strong>Location:</strong> ${hospital.location}</p>
                        <p><i class="fas fa-droplet"></i> <strong>Total Units:</strong> ${hospital.total_units}</p>
                        <p><i class="fas fa-vials"></i> <strong>Blood Groups:</strong><br>
                            <span class="blood-group-list">${bloodGroupHtml || 'None'}</span>
                        </p>
                    `;
                    hospitalList.appendChild(card);
                });
            } else {
                hospitalList.innerHTML = '<div class="alert alert-info w-100 text-center"><i class="fas fa-circle-info me-2"></i> No hospitals match the filters.</div>';
            }
        }

        // Auto-filter on change
        document.getElementById('filterLocation').addEventListener('change', () => {
            console.log('Location changed:', document.getElementById('filterLocation').value);
            filterHospitals();
        });
        document.getElementById('filterBloodGroup').addEventListener('change', () => {
            console.log('Blood group changed:', document.getElementById('filterBloodGroup').value);
            filterHospitals();
        });
        document.getElementById('filterSort').addEventListener('change', () => {
            console.log('Sort changed:', document.getElementById('filterSort').value);
            filterHospitals();
        });

        // Clear filters
        document.getElementById('clearFilters').addEventListener('click', () => {
            const form = document.getElementById('filterForm');
            form.reset();
            document.getElementById('filterLocation').value = '';
            document.getElementById('filterBloodGroup').value = '';
            document.getElementById('filterSort').value = 'name_asc';
            console.log('Filters cleared');
            sessionStorage.clear();
            filterHospitals();
        });

        // Handle card click
        document.getElementById('hospitalList').addEventListener('click', (e) => {
            const card = e.target.closest('.hospital-card');
            if (card) {
                const hospitalId = card.getAttribute('data-hospital-id');
                const hospitalName = card.getAttribute('data-hospital-name');
                document.getElementById('requestedHospitalId').value = hospitalId;
                document.getElementById('hospitalNameDisplay').value = hospitalName;
                document.getElementById('requestBloodForm').reset();
                document.getElementById('requestedHospitalId').value = hospitalId;
                document.getElementById('hospitalNameDisplay').value = hospitalName;
                document.getElementById('bloodGroupError').style.display = 'none';
                document.getElementById('unitsError').style.display = 'none';
                document.getElementById('unitsAvailError').style.display = 'none';
                requestBloodModal.show();
                console.log('Hospital card clicked:', hospitalName);
            }
        });

        // Check blood group availability
        document.getElementById('bloodGroup').addEventListener('change', () => {
            const bloodGroup = document.getElementById('bloodGroup').value;
            const hospitalId = document.getElementById('requestedHospitalId').value;
            const bloodGroupError = document.getElementById('bloodGroupError');
            const unitsAvailError = document.getElementById('unitsAvailError');
            bloodGroupError.style.display = 'none';
            unitsAvailError.style.display = 'none';

            if (bloodGroup && hospitalId) {
                fetch(`check_inventory.php?hospital_id=${hospitalId}&blood_group=${encodeURIComponent(bloodGroup)}`)
                    .then(response => {
                        console.log('Check inventory response status:', response.status);
                        if (!response.ok) throw new Error(`HTTP error: ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Check inventory response data:', data);
                        if (!data.success || data.units <= 0) {
                            bloodGroupError.style.display = 'block';
                            document.getElementById('units').disabled = true;
                        } else {
                            bloodGroupError.style.display = 'none';
                            document.getElementById('units').disabled = false;
                            document.getElementById('units').setAttribute('data-max-units', data.units);
                        }
                    })
                    .catch(error => {
                        console.error('Check inventory error:', error);
                        bloodGroupError.innerHTML = `<i class="fas fa-exclamation-circle"></i> Error checking inventory: ${error.message}`;
                        bloodGroupError.style.display = 'block';
                    });
            }
        });

        // Form submission
        document.getElementById('requestBloodForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const form = document.getElementById('requestBloodForm');
            const formData = new FormData(form);
            const units = parseInt(formData.get('units') || 0);
            const maxUnits = parseInt(document.getElementById('units').getAttribute('data-max-units') || 0);
            const bloodGroupError = document.getElementById('bloodGroupError');
            const unitsError = document.getElementById('unitsError');
            const unitsAvailError = document.getElementById('unitsAvailError');

            // Client-side validation
            bloodGroupError.style.display = 'none';
            unitsError.style.display = 'none';
            unitsAvailError.style.display = 'none';

            if (units <= 0) {
                unitsError.style.display = 'block';
                return;
            }
            if (maxUnits > 0 && units > maxUnits) {
                unitsAvailError.style.display = 'block';
                return;
            }

            fetch('add_request.php', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    console.log('Add request response status:', response.status);
                    if (!response.ok) throw new Error(`HTTP error: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    console.log('Add request response data:', data);
                    if (data.success) {
                        document.getElementById('successToastMessage').textContent = 'Blood request submitted successfully!';
                        successToast.show();
                        requestBloodModal.hide();
                        loadRequests();
                        form.reset();
                        document.getElementById('bloodGroupError').style.display = 'none';
                        document.getElementById('unitsError').style.display = 'none';
                        document.getElementById('unitsAvailError').style.display = 'none';
                    } else {
                        if (data.error === 'blood_group_unavailable') {
                            bloodGroupError.style.display = 'block';
                        } else if (data.error === 'insufficient_units') {
                            unitsAvailError.style.display = 'block';
                        } else {
                            document.getElementById('errorToastMessage').textContent = data.error || 'Error submitting request.';
                            errorToast.show();
                        }
                    }
                })
                .catch(error => {
                    console.error('Add request error:', error);
                    document.getElementById('errorToastMessage').textContent = `Error submitting request: ${error.message}`;
                    errorToast.show();
                });
        });

        // Load requests
        function loadRequests() {
            fetch(`get_requests.php?hospital_id=<?php echo $hospital_id; ?>`)
                .then(response => {
                    console.log('Load requests response status:', response.status);
                    if (!response.ok) throw new Error(`HTTP error: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    console.log('Load requests response data:', data);
                    const tbody = document.getElementById('requestsTableBody');
                    tbody.innerHTML = '';
                    if (data.success && data.requests && data.requests.length > 0) {
                        data.requests.forEach(request => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>${request.hospital_name}</td>
                                <td>${request.blood_group}</td>
                                <td>${request.unit}</td>
                                <td><span class="badge bg-${
                                    request.status === 'Pending' ? 'warning' :
                                    request.status === 'Accepted' ? 'success' :
                                    request.status === 'Rejected' ? 'danger' :
                                    'secondary'
                                }">${request.status}</span></td>
                                <td>${request.created_at}</td>
                                <td>
                                    ${request.status === 'Pending' ? 
                                        `<button class="btn-custom btn-sm cancel-btn" onclick="cancelRequest(${request.id})"><i class="fas fa-times"></i> Cancel</button>` : 
                                        ''}
                                </td>
                            `;
                            tbody.appendChild(tr);
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="6" class="text-center"><i class="fas fa-circle-info me-2"></i> No requests found.</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Load requests error:', error);
                    document.getElementById('requestsTableBody').innerHTML = 
                        `<tr><td colspan="6" class="text-center"><i class="fas fa-exclamation-circle me-2"></i> Error loading requests: ${error.message}</td></tr>`;
                });
        }

        // Cancel request
        function cancelRequest(id) {
            console.log('Attempting to cancel request ID:', id);
            if (confirm('Are you sure you want to cancel this request?')) {
                fetch('cancel_request.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `request_id=${encodeURIComponent(id)}`
                })
                    .then(response => {
                        console.log('Cancel request response status:', response.status);
                        if (!response.ok) throw new Error(`HTTP error: ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Cancel request response data:', data);
                        if (data.success) {
                            document.getElementById('successToastMessage').textContent = 'Request cancelled successfully!';
                            successToast.show();
                            loadRequests();
                        } else {
                            document.getElementById('errorToastMessage').textContent = data.error || 'Error cancelling request.';
                            errorToast.show();
                        }
                    })
                    .catch(error => {
                        console.error('Cancel request error:', error);
                        document.getElementById('errorToastMessage').textContent = `Error cancelling request: ${error.message}`;
                        errorToast.show();
                    });
            }
        }

        // Load requests and update badges on page load
        window.onload = () => {
            loadRequests();
            updateFilterBadges();
        };

        // Reset form on modal close
        document.getElementById('requestBloodModal').addEventListener('hidden.bs.modal', () => {
            document.getElementById('requestBloodForm').reset();
            document.getElementById('bloodGroupError').style.display = 'none';
            document.getElementById('unitsError').style.display = 'none';
            document.getElementById('unitsAvailError').style.display = 'none';
            document.getElementById('units').disabled = false;
            document.getElementById('units').removeAttribute('data-max-units');
        });
    </script>
</body>
</html>
