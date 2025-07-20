<?php
//session_start();
include 'navbar.php'; 
include 'sidebar.php';
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$hospital_id = $_SESSION['user_id'];

// Fetch donors
$sql_donors = "SELECT id, donor_name, blood_group, nic, hospital_id, created_at 
               FROM donors 
               WHERE hospital_id = ?";
$stmt_donors = $conn->prepare($sql_donors);
if (!$stmt_donors) {
    error_log("Prepare failed: " . $conn->error);
    die("Database error: " . $conn->error);
}
$stmt_donors->bind_param("i", $hospital_id);
$stmt_donors->execute();
$result_donors = $stmt_donors->get_result();

$donors = [];
$blood_groups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
while ($donor = $result_donors->fetch_assoc()) {
    $donors[] = $donor;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background: linear-gradient(135deg, #ffffff, #f1f3f5);
            color: #212529;
            overflow-x: hidden;
            min-height: 100vh;
            box-sizing: border-box;
        }

        .main-content {
            padding: 20px;
            transition: padding-left 0.3s ease;
        }

        .container {
            max-width: calc(1400px - 250px);
            margin: auto;
            padding: 30px 20px;
            width: 100%;
        }

        .table-responsive {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 50px;
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

        .btn-success-custom {
            background: #28a745;
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
            margin-bottom: 20px;
        }

        .btn-success-custom:hover {
            background: linear-gradient(45deg, #28a745, #218838);
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(40, 167, 69, 0.5);
        }

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

        .modal_fade {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            padding: 0 10px;
        }

        .modal_fade.show {
            display: flex;
        }

        .modal-dialog {
            max-width: 500px;
            width: 100%;
        }

        @media (max-width: 992px) {
            body {
                padding-left: 0;
                padding-top: 60px;
            }

            .main-content {
                padding: 15px;
            }

            .container {
                max-width: 100%;
                padding: 20px 15px;
            }

            .sidebar {
                width: 200px;
                transform: translateX(-200px);
                transition: transform 0.3s ease;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .navbar .menu-toggle {
                display: inline-block;
                background: none;
                border: none;
                color: #fff;
                font-size: 1.2rem;
                cursor: pointer;
            }
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
    </style>
</head>
<body>
<div class="navbar-fixed">
        <?php include 'navbar.php'; ?>
</div>
<div class="content-wrapper">
         <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="container">
            <h3 class="text-center my-4"><i class="fas fa-user-nurse me-2"></i> Donor Management</h3>
            
            <!-- Add Donor Button -->
            <button class="btn-success-custom" onclick="showAddDonorModal()">
                <i class="fas fa-plus"></i> Add New Donor
            </button>
            
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Donor Name</th>
                            <th>Blood Group</th>
                            <th>NIC</th>
                            <th>Hospital ID</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="donorTableBody">
                        <?php if (empty($donors)): ?>
                            <tr>
                                <td colspan="7" class="text-center"><i class="fas fa-circle-info me-2"></i> No donors found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($donors as $donor): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($donor['id']); ?></td>
                                    <td><?php echo htmlspecialchars($donor['donor_name']); ?></td>
                                    <td><?php echo htmlspecialchars($donor['blood_group']); ?></td>
                                    <td><?php echo htmlspecialchars($donor['nic']); ?></td>
                                    <td><?php echo htmlspecialchars($donor['hospital_id']); ?></td>
                                    <td><?php echo htmlspecialchars($donor['created_at']); ?></td>
                                    <td>
                                        <button class="btn-custom btn-sm me-1" onclick="editDonor(<?php echo $donor['id']; ?>, '<?php echo htmlspecialchars(addslashes($donor['donor_name'])); ?>', '<?php echo $donor['blood_group']; ?>', '<?php echo $donor['nic']; ?>')"><i class="fas fa-pen"></i> Update</button>
                                        <button class="btn-custom btn-sm" onclick="deleteDonor(<?php echo $donor['id']; ?>)"><i class="fas fa-trash-can"></i> Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Donor Modal -->
        <div class="modal_fade" id="addDonorModal" tabindex="-1" aria-labelledby="addDonorModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content form-modal">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addDonorModalLabel"><i class="fas fa-plus me-2"></i> Add New Donor</h5>
                        <button type="button" class="btn-close" onclick="hideAddDonorModal()" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addDonorForm">
                            <div class="mb-3">
                                <label for="addDonorName" class="form-label"><i class="fas fa-user-tag"></i> Donor Name</label>
                                <input type="text" id="addDonorName" name="donorName" class="form-control" required>
                                <div id="addNameError" class="error" style="display: none;"><i class="fas fa-exclamation-circle"></i> Donor name is required.</div>
                            </div>
                            <div class="mb-3">
                                <label for="addDonorNIC" class="form-label"><i class="fas fa-id-badge"></i> Donor NIC</label>
                                <input type="text" id="addDonorNIC" name="donorNIC" class="form-control" required pattern="[0-9]{12}" title="NIC must be 12 digits">
                                <div id="addNICError" class="error" style="display: none;"><i class="fas fa-exclamation-circle"></i> Valid 12-digit NIC is required.</div>
                            </div>
                            <div class="mb-3">
                                <label for="addBloodGroup" class="form-label"><i class="fas fa-tint"></i> Blood Group</label>
                                <select id="addBloodGroup" name="bloodGroup" class="form-select" required>
                                    <option value="">Select Blood Group</option>
                                    <?php foreach ($blood_groups as $group): ?>
                                        <option value="<?php echo $group; ?>"><?php echo $group; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div id="addBloodGroupError" class="error" style="display: none;"><i class="fas fa-exclamation-circle"></i> Please select a blood group.</div>
                            </div>
                            <button type="submit" class="btn-success-custom"><i class="fas fa-plus"></i> Add Donor</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Update Donor Modal -->
        <div class="modal_fade" id="updateDonorModal" tabindex="-1" aria-labelledby="updateDonorModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content form-modal">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateDonorModalLabel"><i class="fas fa-pen-to-square me-2"></i> Update Donor</h5>
                        <!-- <button type="button" class="btn-close" onclick="hideUpdateDonorModal()" aria-label="Close"></button> -->
                    </div>
                    <div class="modal-body">
                        <form id="updateDonorForm">
                            <input type="hidden" id="updateDonorId" name="donorId">
                            <div class="mb-3">
                                <label for="updateDonorName" class="form-label"><i class="fas fa-user-tag"></i> Donor Name</label>
                                <input type="text" id="updateDonorName" name="donorName" class="form-control" required>
                                <div id="updateNameError" class="error" style="display: none;"><i class="fas fa-exclamation-circle"></i> Donor name is required.</div>
                            </div>
                            <div class="mb-3">
                                <label for="updateDonorNIC" class="form-label"><i class="fas fa-id-badge"></i> Donor NIC</label>
                                <input type="text" id="updateDonorNIC" name="donorNIC" class="form-control" required pattern="[0-9]{12}" title="NIC must be 12 digits">
                                <div id="updateNICError" class="error" style="display: none;"><i class="fas fa-exclamation-circle"></i> Valid 12-digit NIC is required.</div>
                            </div>
                            <div class="mb-3">
                                <label for="updateBloodGroup" class="form-label"><i class="fas fa-tint"></i> Blood Group</label>
                                <select id="updateBloodGroup" name="bloodGroup" class="form-select" required>
                                    <option value="">Select Blood Group</option>
                                    <?php foreach ($blood_groups as $group): ?>
                                        <option value="<?php echo $group; ?>"><?php echo $group; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div id="updateBloodGroupError" class="error" style="display: none;"><i class="fas fa-exclamation-circle"></i> Please select a blood group.</div>
                            </div>
                            <button type="submit" class="btn-custom"><i class="fas fa-pen-to-square"></i> Update Donor</button>
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
    
    <script>
        // Modal functions
        function showAddDonorModal() {
            document.getElementById('addDonorModal').classList.add('show');
            document.getElementById('addDonorForm').reset();
            hideAllErrors('add');
        }

        function hideAddDonorModal() {
            document.getElementById('addDonorModal').classList.remove('show');
        }

        function showUpdateDonorModal() {
            document.getElementById('updateDonorModal').classList.add('show');
        }

        function hideUpdateDonorModal() {
            document.getElementById('updateDonorModal').classList.remove('show');
        }

        function hideAllErrors(prefix) {
            document.getElementById(prefix + 'NameError').style.display = 'none';
            document.getElementById(prefix + 'NICError').style.display = 'none';
            document.getElementById(prefix + 'BloodGroupError').style.display = 'none';
        }

        // Sidebar toggle
        document.querySelector('.menu-toggle')?.addEventListener('click', () => {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Add Donor form submission
        document.getElementById('addDonorForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const donorName = document.getElementById('addDonorName').value.trim();
            const donorNIC = document.getElementById('addDonorNIC').value.trim();
            const bloodGroup = document.getElementById('addBloodGroup').value;
            const nameError = document.getElementById('addNameError');
            const nicError = document.getElementById('addNICError');
            const bloodGroupError = document.getElementById('addBloodGroupError');

            // Client-side validation
            let hasError = false;
            if (!donorName) {
                nameError.style.display = 'block';
                hasError = true;
            }
            if (!donorNIC || !/^[0-9]{12}$/.test(donorNIC)) {
                nicError.style.display = 'block';
                hasError = true;
            }
            if (!bloodGroup) {
                bloodGroupError.style.display = 'block';
                hasError = true;
            }

            if (hasError) return;

            hideAllErrors('add');

            const formData = new FormData(document.getElementById('addDonorForm'));

            try {
                const response = await fetch('add_donor.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    alert('Donor added successfully!');
                    hideAddDonorModal();
                    window.location.reload();
                } else {
                    alert(data.error || 'Error adding donor.');
                }
            } catch (error) {
                console.error('Error adding donor:', error);
                alert('Error adding donor: ' + error.message);
            }
        });

        // Edit Donor
        function editDonor(id, name, bloodGroup, nic) {
            document.getElementById('updateDonorId').value = id;
            document.getElementById('updateDonorName').value = name;
            document.getElementById('updateDonorNIC').value = nic;
            document.getElementById('updateBloodGroup').value = bloodGroup;
            hideAllErrors('update');
            showUpdateDonorModal();
        }

        // Update Donor form submission
        document.getElementById('updateDonorForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const donorName = document.getElementById('updateDonorName').value.trim();
            const donorNIC = document.getElementById('updateDonorNIC').value.trim();
            const bloodGroup = document.getElementById('updateBloodGroup').value;
            const nameError = document.getElementById('updateNameError');
            const nicError = document.getElementById('updateNICError');
            const bloodGroupError = document.getElementById('updateBloodGroupError');

            // Client-side validation
            let hasError = false;
            if (!donorName) {
                nameError.style.display = 'block';
                hasError = true;
            }
            if (!donorNIC || !/^[0-9]{12}$/.test(donorNIC)) {
                nicError.style.display = 'block';
                hasError = true;
            }
            if (!bloodGroup) {
                bloodGroupError.style.display = 'block';
                hasError = true;
            }

            if (hasError) return;

            hideAllErrors('update');

            const formData = new FormData(document.getElementById('updateDonorForm'));

            try {
                const response = await fetch('update_donor.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    alert('Donor updated successfully!');
                    hideUpdateDonorModal();
                    window.location.reload();
                } else {
                    alert(data.error || 'Error updating donor.');
                }
            } catch (error) {
                console.error('Error updating donor:', error);
                alert('Error updating donor: ' + error.message);
            }
        });

        // Delete Donor
        async function deleteDonor(id) {
            if (confirm('Are you sure you want to delete this donor?')) {
                try {
                    const response = await fetch('delete_donor.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `donorId=${encodeURIComponent(id)}`
                    });
                    const data = await response.json();

                    if (data.success) {
                        alert('Donor deleted successfully!');
                        window.location.reload();
                    } else {
                        alert(data.error || 'Error deleting donor.');
                    }
                } catch (error) {
                    console.error('Error deleting donor:', error);
                    alert('Error deleting donor: ' + error.message);
                }
            }
        }

        // Close modals when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal_fade')) {
                if (e.target.id === 'addDonorModal') {
                    hideAddDonorModal();
                } else if (e.target.id === 'updateDonorModal') {
                    hideUpdateDonorModal();
                }
            }
        });
    </script>
</body>
</html>
<?php
$stmt_donors->close();
$conn->close();
?>