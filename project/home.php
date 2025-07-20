

<?php
include 'config/db.php';
include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Bank Home</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }

        .hero-section {
            margin-top: 80px;
            position: relative;
            text-align: center;
        }

        .hero-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }

        .hero-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 24px;
            background: rgba(0, 0, 0, 0.6);
            padding: 20px;
            border-radius: 10px;
        }

        .container {
            width: 90%;
            margin: auto;
            padding: 20px;
        }

        .blood-stock, .camp-list, .blood-results {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .stock-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 150px;
        }
        .camp-filters{
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 40px;
        }

        .camp-filters button, .search-filters button {
            padding: 10px 15px;
            margin: 5px;
            border: none;
            background: #dc3545;
            color: white;
            cursor: pointer;
            border-radius: 5px;
            font-weight: bold;
        }

        .camp-filters button:hover, .search-filters button:hover {
            background: #c82333;
        }

        .search-filters select {
            padding: 10px;
            margin: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
</head>
<body>

    <!-- Hero Section -->
    <div class="hero-section">
        <img src="img/login-bg.jpg" alt="Blood Donation" class="hero-image">
        <div class="hero-text">
            <h1>Donate Blood, Save Lives</h1>
            <p>Your contribution can save lives. Find blood donors or donate today!</p>
        </div>
    </div>

    <div class="container">
        <!-- Blood Count Section -->
        <h2>Available Blood Stock</h2>
        <div class="blood-stock">
            <?php 
            $query = "SELECT blood_group, SUM(units) as total_units FROM blood_inventory GROUP BY blood_group";
            $result = mysqli_query($conn, $query);
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<div class='stock-card'>";
                echo "<h3>" . $row['blood_group'] . "</h3>";
                echo "<p>Units Available: " . $row['total_units'] . "</p>";
                echo "</div>";
            }
            ?>
        </div>

        <!-- Upcoming Blood Camps with Filters -->
        <h2>Upcoming Blood Camps</h2>
        <div class="camp-filters">
            <button onclick="filterCamps('all')">All</button>
            <button onclick="filterCamps('today')">Today</button>
            <button onclick="filterCamps('tomorrow')">Tomorrow</button>
            <button onclick="filterCamps('this_week')">This Week</button>
            <button onclick="filterCamps('next_week')">Next Week</button>
            <button onclick="filterCamps('this_month')">This Month</button>
            <button onclick="filterCamps('next_month')">Next Month</button>
            <button onclick="filterCamps('this_year')">This Year</button>
        </div>
        <div class="camp-list" id="camp-results">
            <!-- Blood Camps will be loaded dynamically here -->
        </div>

        <!-- Find Blood Section -->
        <h2>Find Blood</h2>
        <div class="search-filters">
            <label>Blood Group:</label>
            <select id="blood_group"onchange="findBlood()">
                <option value="">All</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
            </select>
            
            <label>Province:</label>
            <select id="province" onchange="loadDistricts();findBlood();">
                <option value="">All</option>
                <option value="Northern">Northern</option>
                <option value="Western">Western</option>
                <option value="Central">Central</option>
                <option value="Eastern">Eastern</option>
                <option value="Southern">Southern</option>
                <option value="North Central">North Central</option>
                <option value="North Western">North Western</option>
                <option value="Uva">Uva</option>
                <option value="Sabaragamuwa">Sabaragamuwa</option>
            </select>
            
            <label>District:</label>
            <select id="district"onchange="findBlood()">
                <option value="">All</option>
            </select>
            
            <!-- <button onclick="findBlood()">Search</button> -->
        </div>
        <div class="blood-results" id="blood-results">
            <!-- Blood availability results will be loaded dynamically here -->
        </div>
    </div>

<?php include 'footer.php'; ?>

<script>


function filterCamps(filter) {
    fetch(`fetch_camps.php?filter=${filter}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('camp-results').innerHTML = data;
        });
}
document.addEventListener("DOMContentLoaded", function() {
        filterCamps('this_week'); // Call function on page load
    });

function loadDistricts() {
    let province = document.getElementById("province").value;
    let districtDropdown = document.getElementById("district");
    districtDropdown.innerHTML = '<option value="">All</option>';
    
        const districts = {
        "Northern": ["Jaffna", "Kilinochchi", "Mannar", "Mullaitivu", "Vavuniya"],
        "Western": ["Colombo", "Gampaha", "Kalutara"],
        "Central": ["Kandy", "Matale", "Nuwara Eliya"],
        "Eastern": ["Trincomalee", "Batticaloa", "Ampara"],
        "Southern": ["Galle", "Matara", "Hambantota"],
        "North Western": ["Kurunegala", "Puttalam"],
        "North Central": ["Anuradhapura", "Polonnaruwa"],
        "Uva": ["Badulla", "Monaragala"],
        "Sabaragamuwa": ["Ratnapura", "Kegalle"]
    };

    
    if (province in districts) {
        districts[province].forEach(district => {
            let option = document.createElement("option");
            option.value = district;
            option.textContent = district;
            districtDropdown.appendChild(option);
        });
    }
}


function findBlood() {
    let bloodGroup = encodeURIComponent(document.getElementById("blood_group").value);
    let province = encodeURIComponent(document.getElementById("province").value);
    let district = encodeURIComponent(document.getElementById("district").value);

    fetch(`fetch_blood.php?blood_group=${bloodGroup}&province=${province}&district=${district}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('blood-results').innerHTML = data;
        });
}
</script>

</body>
</html>
