<?php
include 'config/db.php';

// Set default limit
$default_limit = 10;
$limit_options = ['All',10, 25, 50, 100, 500,1000];

// Get the selected limit from the dropdown (default is 10)
$limit = isset($_GET['limit']) && in_array($_GET['limit'], $limit_options) ? $_GET['limit'] : $default_limit;

// If "all" is selected, fetch all feedbacks, otherwise use the limit
if ($limit === 'All') {
    $query = "SELECT COUNT(*) as total FROM contact";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $limit = $row['total']; // Fetch all rows
} else {
    $limit = (int)$limit; // Ensure limit is an integer
}

// Get the current page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch total number of feedbacks
$total_query = "SELECT COUNT(*) as total FROM contact";
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_feedbacks = $total_row['total'];

// Calculate total pages
$total_pages = ($limit == 0) ? 1 : ceil($total_feedbacks / $limit);

// Fetch feedback with pagination
$query = "SELECT id, name, email, phone, inquiry_type, message, submitted_at FROM contact 
          ORDER BY submitted_at DESC 
          LIMIT $limit OFFSET $offset";
$feedback_result = mysqli_query($conn, $query);
?>

<?php include 'navbar.php'; ?>
<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Feedback</title>
    <style>
       /* Pagination container */
        .pagination-container {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            margin-bottom: 20px;
            font-size: 16px;
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Label styling */
        .pagination-container label {
            font-weight: 600;
            margin-right: 12px;
            color: #444;
            text-transform: uppercase;
        }

        /* Styling for the select dropdown */
        .pagination-container select {
            padding: 8px 15px;
            font-size: 14px;
            border: 1px solid #28a745;
            border-radius: 5px;
            background-color: #fff;
            color: #333;
            cursor: pointer;
            transition: border-color 0.3s, box-shadow 0.3s;
            width: auto;
            max-width: 160px;
        }

        /* Select dropdown focused style */
        .pagination-container select:focus {
            outline: none;
            border-color: #17a2b8;
            box-shadow: 0 0 5px rgba(23, 162, 184, 0.4);
        }

        /* Hover effect for the select dropdown */
        .pagination-container select:hover {
            border-color: #17a2b8;
            background-color: #f1f1f1;
        }

        /* Option styling */
        .pagination-container select option {
            padding: 10px;
            font-size: 14px;
        }

        /* Responsive design for smaller screens */
        @media (max-width: 600px) {
            .pagination-container {
                font-size: 14px;
            }
            .pagination-container select {
                font-size: 12px;
                padding: 6px 10px;
            }
        }

        /* Content Adjustment */
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
            /* background-image: url('https://www.transparenttextures.com/patterns/asfalt-light.png');  */
        }

        .sidebar:hover ~ .content {
            margin-left: 250px; /* Adjust when sidebar expands */
            width: calc(100% - 300px);
        }
        /* Content adjustment when sidebar expands */
        .content2 {
            transition: margin-left 0.3s ease, width 0.3s ease; /* Smooth transition */
        }

        .sidebar:hover ~ .content2 {
            margin-left: 250px; /* Adjust when sidebar expands */
            width: calc(100% - 250px);
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 20px;
        }
        .container {
            width: 80%;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
            margin-bottom: 100px;
            
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }   

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        .pagination {
            margin-top: 20px;
            text-align: center;
        }
        .pagination a {
            padding: 10px 15px;
            margin: 5px;
            border: 1px solid #007BFF;
            color: #007BFF;
            text-decoration: none;
            border-radius: 5px;
        }
        .pagination a.active {
            background-color: #007BFF;
            color: white;
        }
        .pagination a:hover {
            background-color: #0056b3;
            color: white;
        }
        /* Responsive design */
        @media (max-width: 600px) {
            table {
                font-size: 12px;
            }
        }
        .det{
            color: red;
            text-decoration: none;
        }
        .det:hover{
           font-weight: bold;
        }
        .alert {
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 20px;
        text-align: center;
        font-size: 16px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

    </style>
</head>
<body>

<div class="content">
    <div class="container" style="overflow: scroll">
        <h2>Hospital Feedback</h2>

        <!-- Dropdown for selecting number of feedbacks per page -->
        <div class="pagination-container">
            <label for="recordsPerPage">Show: </label>
            <select id="recordsPerPage" name="limit" onchange="changeLimit()">
            <?php foreach ($limit_options as $option) { ?>
            <option value="<?php echo $option; ?>" <?php echo ($option == $limit || ($option === 'All' && $limit === 'all')) ? 'selected' : ''; ?>>
                <?php echo ($option === 'All') ? 'All' : $option; ?>
            </option>
            <?php } ?>

            </select> Records
        </div>

        <!-- Pagination Links -->
        <div class="pagination" >
                <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                    <a href="view_feedback.php?page=<?php echo $i; ?>&limit=<?php echo $limit; ?>"
                       class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php } ?>
            </div>
            
        <?php if (mysqli_num_rows($feedback_result) > 0) { ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Inquiry Type</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Delete</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($feedback_result)) { ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['inquiry_type']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>
                        <td><?php echo $row['submitted_at']; ?></td>
                        <td><a href="delete_feedback.php?id=<?php echo $row['id']; ?>" class="det">Delete</a></td>
                    </tr>
                <?php } ?>
            </table>

            
        <?php } else { ?>
            <p>No feedback available.</p>
        <?php } ?>
    </div>
</div>
<div class="content2">
    <?php include 'footer.php'; ?>
</div>

<script>
    function changeLimit() {
        var limit = document.getElementById("recordsPerPage").value;
        window.location.href = "view_feedback.php?limit=" + limit + "&page=1";
    }
</script>

</body>
</html>
