<?php include 'navbar.php'; ?>
<?php

// if(!isset($_SESSION['admin_id']))
// {
//     header("Location:login.php");
//     exit();
// }

?>

<?php include 'config/db.php';?>
<?php include 'sidebar.php'; ?>

<?php
// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $deleteQuery = "DELETE FROM hospitals WHERE id = $delete_id";
    
    if (mysqli_query($conn, $deleteQuery)) {
        // Redirect with success message
        header("Location: manage_hospital.php?status=delete_success");
        exit();
    } else {
        // Redirect with error message
        header("Location: manage_hospital.php?status=delete_error");
        exit();
    }
}

// Pagination configuration
$limit = 6; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Query to get hospitals with pagination
$query = "SELECT * FROM hospitals LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

// Query to count total hospitals
$countQuery = "SELECT COUNT(*) FROM hospitals";
$countResult = mysqli_query($conn, $countQuery);
$totalHospitals = mysqli_fetch_row($countResult)[0];
$totalPages = ceil($totalHospitals / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Hospitals - Admin</title>
   
    <style>
        .container {
            margin-top: 100px;
            width: 80%;
            margin: 20px auto;
        }
        .container h1{
            text-align: center;
            margin-bottom:2px;
            margin-top:0;

        }

        .search-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .search-bar input {
            width: 30%;
            padding: 8px 12px;
            font-size: 16px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .action-btn {
            padding: 6px 12px;
            background-color:  #1E3A8A;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 14px;
            border-radius: 4px;
            margin-right: 5px;
        }

        .action-btn:hover {
            background-color: #0056b3;
        }

        .delete-btn {
            padding: 6px 12px;
            background-color: #dc3545;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 14px;
            border-radius: 4px;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }

        .content {
        margin-left: 60px; /* Align content with sidebar */
        padding: 20px 30px 10px 20px;
        width: calc(100% - 120px);
        transition: margin-left 0.3s ease-in-out;
        margin-top: 80px; /* To avoid being overlapped by navbar */
        overflow-y: hidden;
        }
        
        /* Content Adjustment */
        .content2 {
            margin-left: 58px;
            width: calc(100% - 60px);
            transition: margin-left 0.3s ease-in-out;
        
        }
        /* Content adjustment when sidebar expands */
        .content {
            transition: margin-left 0.3s ease, width 0.3s ease; /* Smooth transition */
            /* background-image: url('https://www.transparenttextures.com/patterns/asfalt-dark.png'); */
           
        }

        .sidebar:hover ~ .content {
            margin-left: 250px; /* Adjust when sidebar expands */
            width: calc(100% - 300px);
        }
        .content2 {
            transition: margin-left 0.3s ease, width 0.3s ease; /* Smooth transition */
        }

        .sidebar:hover ~ .content2 {
            margin-left: 250px; 
            width: calc(100% - 250px);
        }

        /* Confirmation Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from {opacity: 0;}
            to {opacity: 1;}
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: none;
            width: 400px;
            max-width: 90%;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            animation: slideIn 0.3s;
        }

        @keyframes slideIn {
            from {transform: translateY(-50px); opacity: 0;}
            to {transform: translateY(0); opacity: 1;}
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }

        .modal-header h2 {
            margin: 0;
            color: #333;
            font-size: 1.5em;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
        }

        .modal-body {
            margin-bottom: 20px;
            text-align: center;
        }

        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .confirm-btn {
            padding: 10px 20px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .confirm-btn:hover {
            background-color: #c82333;
        }

        .cancel-btn {
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .cancel-btn:hover {
            background-color: #5a6268;
        }
    </style>
    <style>
        /* Table Styles */
        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        /* Table Header */
        table th, table td {
            padding: 15px 20px;
            text-align: center;
            border: 1px solid #e0e0e0;
            font-size: 16px;
            color: #333;
            transition: background-color 0.3s ease;
        }

        /* Header Background */
        table th {
            background-color: #1E3A8A;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Table Rows */
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Hover Effect on Table Rows */
        table tr:hover {
            background-color: #f1f1f1;
            cursor: pointer;
            transform: scale(1.02);
            transition: transform 0.2s ease-in-out;
        }

        /* Add Shadow Effect on Row Hover */
        table tr:hover td {
            border:none;
        }

        /* Column Sorting Indicators */
        table th.sortable:hover {
            cursor: pointer;
            background-color: #0056b3;
        }

        table th.sortable:after {
            content: " ▼";
            font-size: 12px;
            color: #ccc;
        }

        table th.sorted-asc:after {
            content: " ▲";
        }

        table th.sorted-desc:after {
            content: " ▼";
        }

        /* Zebra Stripes and Enhanced Hover Effect */
        table tr:nth-child(odd) {
            background-color: #f7f7f7;
        }

        table tr:nth-child(odd):hover {
            background-color: #e6e6e6;
        }

        /* Table Footer (Optional) */
        table tfoot {
            font-weight: bold;
            background-color: #f4f4f4;
        }

        /* Pagination Styles */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            list-style: none;
        }

        .pagination li {
            margin: 0 5px;
        }

        .pagination a {
            text-decoration: none;
            padding: 8px 16px;
            background-color: #0056b3;
            color: white;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .pagination a:hover {
            background-color: #0056b3;
        }

        .pagination .active a {
            background-color:  #1E3A8A;
        }

        .pagination .disabled a {
            background-color: #ddd;
            pointer-events: none;
        }
</style>

</head>

<div class="content">
    <!-- Container for managing hospitals -->
    <div class="container">
        <h1>Manage Hospitals</h1>
        <?php
            if (isset($_GET['status'])) {
                if ($_GET['status'] == 'success') {
                    echo "<p style='color: green; font-size:20px; text-align:center;'>Password changed successfully!</p>";
                } elseif ($_GET['status'] == 'error') {
                    echo "<p style='color: red;font-size:20px; text-align:center;'>Error occurred while changing the password. Please try again.</p>";
                } elseif ($_GET['status'] == 'delete_success') {
                    echo "<p style='color: green; font-size:20px; text-align:center;'>Hospital deleted successfully!</p>";
                } elseif ($_GET['status'] == 'delete_error') {
                    echo "<p style='color: red;font-size:20px; text-align:center;'>Error occurred while deleting the hospital. Please try again.</p>";
                }
            }
        ?>
        <!-- Search bar -->
        <div class="search-bar">
            <input type="text" id="search-name" placeholder="Search by Name" onkeyup="searchHospitals()">
            <input type="text" id="search-district" placeholder="Search by District" onkeyup="searchHospitals()">
            <input type="text" id="search-province" placeholder="Search by Province" onkeyup="searchHospitals()">
        </div>

        <!-- Pagination Links -->
    <ul class="pagination">
        <li class="<?= $page <= 1 ? 'disabled' : '' ?>">
            <a href="?page=<?= $page - 1 ?>">Previous</a>
        </li>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="<?= $i == $page ? 'active' : '' ?>">
                <a href="?page=<?= $i ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <li class="<?= $page >= $totalPages ? 'disabled' : '' ?>">
            <a href="?page=<?= $page + 1 ?>">Next</a>
        </li>
    </ul>

        <!-- Table for displaying hospital data -->
        <table id="hospital-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Contact</th>
                    <th>District</th>
                    <th>Province</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="hospital-tbody">
                <?php
                // Loop through and display each hospital
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . $row['name'] . "</td>";
                    echo "<td>" . $row['location'] . "</td>";
                    echo "<td>" . $row['phone'] . "</td>";
                    echo "<td>" . $row['district'] . "</td>";
                    echo "<td>" . $row['province'] . "</td>";
                    echo "<td>
                            <button class='action-btn' onclick='viewHospital(" . $row['id'] . ")'>View</button>
                            <button class='delete-btn' onclick='confirmDelete(" . $row['id'] . ", \"" . addslashes($row['name']) . "\")'>Delete</button>
                          </td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        
    </div>
</div>

<!-- Modal for showing full hospital details -->
<div id="hospital-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="close" onclick="closeModal()">&times;</span>
            <!-- <h2>Hospital Details</h2> -->
        </div>
        <div class="modal-body" id="hospital-details">
            <!-- Full hospital details will be inserted here dynamically -->
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Confirm Delete</h2>
            <span class="close" onclick="closeDeleteModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete <strong id="hospital-name-to-delete"></strong>?</p>
            <p style="color: #dc3545; font-size: 14px;">This action cannot be undone.</p>
        </div>
        <div class="modal-buttons">
            <button class="confirm-btn" id="confirm-delete-btn">Delete</button>
            <button class="cancel-btn" onclick="closeDeleteModal()">Cancel</button>
        </div>
    </div>
</div>

<div class="content2">
    <?php include 'footer.php'; ?>
</div>

<script>
    // Function to search hospitals without reloading the page
    function searchHospitals() {
        var name = document.getElementById('search-name').value.toLowerCase();
        var district = document.getElementById('search-district').value.toLowerCase();
        var province = document.getElementById('search-province').value.toLowerCase();

        // Get all table rows
        var rows = document.getElementById('hospital-tbody').getElementsByTagName('tr');

        // Loop through each row and hide those that don't match the search criteria
        for (var i = 0; i < rows.length; i++) {
            var row = rows[i];
            var rowName = row.getElementsByTagName('td')[0].textContent.toLowerCase();
            var rowDistrict = row.getElementsByTagName('td')[3].textContent.toLowerCase();
            var rowProvince = row.getElementsByTagName('td')[4].textContent.toLowerCase();

            // Check if row matches search criteria
            if (rowName.indexOf(name) > -1 && rowDistrict.indexOf(district) > -1 && rowProvince.indexOf(province) > -1) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    }

    // Function to view the hospital details in a modal
    function viewHospital(hospitalId) {
        // Open modal and load hospital details using AJAX
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "view_hospital.php?id=" + hospitalId, true);
        xhr.onload = function() {
            if (xhr.status == 200) {
                document.getElementById('hospital-details').innerHTML = xhr.responseText;
                document.getElementById('hospital-modal').style.display = "block";
            }
        };
        xhr.send();
    }

    // Function to close the hospital details modal
    function closeModal() {
        document.getElementById("hospital-modal").style.display = "none";
    }

    // Function to show delete confirmation modal
    function confirmDelete(hospitalId, hospitalName) {
        document.getElementById('hospital-name-to-delete').textContent = hospitalName;
        document.getElementById('delete-modal').style.display = 'block';
        
        // Set up the confirm button to delete the hospital
        document.getElementById('confirm-delete-btn').onclick = function() {
            deleteHospital(hospitalId);
        };
    }

    // Function to close delete confirmation modal
    function closeDeleteModal() {
        document.getElementById('delete-modal').style.display = 'none';
    }

    // Function to delete hospital
    function deleteHospital(hospitalId) {
        window.location.href = '?delete_id=' + hospitalId;
    }

    // Show the Change Password form
    function showChangePasswordForm() {
        document.getElementById('show-password-btn').style.display = 'none';  // Hide the button
        document.getElementById('change-password-form').style.display = 'block'; // Show the form
    }

    // Close modals when clicking outside the modal content
    window.onclick = function(event) {
        var hospitalModal = document.getElementById('hospital-modal');
        var deleteModal = document.getElementById('delete-modal');
        
        if (event.target === hospitalModal) {
            closeModal();
        }
        if (event.target === deleteModal) {
            closeDeleteModal();
        }
    }
</script>
</body>
</html>