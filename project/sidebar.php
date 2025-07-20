<?php
// session_start();
include 'config/db.php';
$admin['img'] = $_SESSION['img'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sidebar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Sidebar Styling */
        .sidebar {
            width: 60px; /* Default width */
            height: calc(100vh - 80px); /* Adjusted height so it starts below navbar */
            position: fixed;
            top: 80px; /* Below the navbar */
            left: 0;
            overflow: hidden;
            z-index: 1000;
            border-top: 1px hidden #ccc;
            transition: width 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
            background: linear-gradient(180deg, #f1f1f1, #dfe6e9); /* Gradient effect */
            transition: transform 0.3s ease-in-out;
        }

        /* Hover effect for sidebar */
        .sidebar:hover {
            width: 250px; /* Expands on hover */
        }

        /* Sidebar Logo Styling */
        .sidebar .logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px 10px 0 10px;
            transition: opacity 0.3s ease-in-out;
        }

        /* Logo image transition effect */
        .sidebar .logo img {
            display: none;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            transition: transform 0.3s ease, filter 0.3s ease;
        }

        /* Logo name styling */
        .sidebar .logo h2 {
            display: none;
            color: #2c3e50;
            font-size: 24px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-weight: 600;
            margin: 10px 0;
            text-align: center;
            letter-spacing: 1px;
            text-transform: capitalize;
            background: linear-gradient(45deg, #2c3e50, #3498db); /* Gradient text effect */
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            transition: opacity 0.4s ease-in-out, transform 0.3s ease;
        }

        /* Show logo text and image when sidebar is hovered */
        .sidebar:hover .logo h2 {
            display: block;
            opacity: 1;
            transform: translateY(0); /* Smooth slide-in effect */
        }

        .sidebar:hover .logo img {
            display: block;
            opacity: 1;
        }

        /* Hover effect for logo name */
        .sidebar .logo h2:hover {
            transform: scale(1.05); /* Slight zoom effect */
        }

        /* Sidebar Links Styling */
        .sidebar .nav-links {
            list-style: none;
            padding: 0;
            margin-top: 30px;
        }

        .sidebar .nav-links li {
            position: relative;
        }

        .sidebar .nav-links li a {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #555;
            padding: 18px;
            font-size: 18px;
            transition: all 0.3s ease;
            white-space: nowrap;
            border-radius: 5px;
            position: relative;
            transition: transform 0.3s ease, background-color 0.3s ease;
        }

        /* Icon styles */
        .sidebar .nav-links li a i {
            margin-right: 15px;
            font-size: 22px;
            transition: color 0.3s ease;
        }

        /* Hide text initially */
        .sidebar .nav-links li a span {
            display: none;
            transition: opacity 0.3s ease-in-out;
        }

        /* Show text on hover */
        .sidebar:hover .nav-links li a span {
            display: inline;
        }

        /* Hover effect for sidebar links */
        .sidebar .nav-links li a:hover {
            background: #b2bec3; /* Soft hover background */
            color: #2c3e50;
            transform: translateX(10px); /* Slide effect */
        }

        /* Change color of icons on hover */
        .sidebar .nav-links li a:hover i {
            color: #2c3e50;
        }

        /* Adding a subtle border on the sidebar items */
        .sidebar .nav-links li a {
            border-bottom: 1px solid #ddd;
        }

        .sidebar .nav-links li a:hover {
            border-bottom: 1px solid #2c3e50;
        }

        /* Active link styling */
        .sidebar .nav-links li a.active {
            background: #7f8c8d;
            color: white;
        }

        /* Logo hover effect */
        .sidebar .logo:hover img {
            filter: brightness(0.8); /* Darken logo */
        }

        /* Custom scrollbar for sidebar */
        .sidebar::-webkit-scrollbar {
            width: 8px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #7f8c8d;
            border-radius: 10px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #95a5a6;
        }

        /* Hide scrollbar for user sidebar */
        .user-sidebar::-webkit-scrollbar {
            display: none;
        }

        .user-sidebar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
            overflow-y: auto;
        }

        /* Advanced animation for menu links */
        .sidebar .nav-links li a {
            position: relative;
            animation: fadeIn 0.5s ease-out;
        }

        /* Responsive styling for smaller screens */
        @media (max-width: 768px) {
            .sidebar {
                width: 50px;
            }

            .sidebar:hover {
                width: 200px;
            }

            .sidebar .logo h2 {
                font-size: 20px;
            }

            .sidebar .nav-links li a {
                font-size: 16px;
                padding: 12px;
            }

            .sidebar .nav-links li a i {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="<?php echo isset($_SESSION['user_id']) ? 'sidebar user-sidebar' : 'sidebar'; ?>">
        <?php if (isset($_SESSION['admin_id'])): ?>
            <div class="logo">
                <img src="<?php echo $admin['img']; ?>" alt="Admin" class="profile-img">
                <h2>Admin Panel</h2>
            </div>
            <ul class="nav-links">
                <li><a href="manage_hospitals.php" data-tooltip="Manage Hospitals"><i class="fas fa-hospital"></i> <span>Manage Hospitals</span></a></li>
                <li><a href="view_feedback.php" data-tooltip="View Feedback"><i class="fas fa-comments"></i> <span>View Feedback</span></a></li>
                <li><a href="settings.php" data-tooltip="Settings"><i class="fas fa-cogs"></i> <span>Settings</span></a></li>
                <li><a href="logout.php" data-tooltip="Logout"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
            </ul>
        <?php elseif (isset($_SESSION['user_id'])): ?>
            <div class="logo">
                <h2><?php echo htmlspecialchars($_SESSION['name']); ?></h2>
            </div>
            <ul class="nav-links">
                <li><a href="hos_availableBlood.php" data-tooltip="Available Blood"><i class="fas fa-hospital"></i> <span>Available Blood</span></a></li>
                <li><a href="hos_campRegistration.php" data-tooltip="Create Blood Camp"><i class="fas fa-comments"></i> <span>Create Blood Camp</span></a></li>
                <li><a href="hos_acceptRequest.php" data-tooltip="Accept Blood"><i class="fas fa-comments"></i> <span>Accept Request </span></a></li>
                <li><a href="hos_requestBlood.php" data-tooltip="Request Blood"><i class="fas fa-comments"></i> <span>Request Blood</span></a></li>
                <li><a href="hos_manageDonor.php" data-tooltip="Manage Donor"><i class="fas fa-user"></i> <span>Manage Donor</span></a></li>
                <li><a href="hos_userprofile.php" data-tooltip="Settings"><i class="fas fa-cogs"></i> <span>Settings</span></a></li>
                <li><a href="logout.php" data-tooltip="Logout"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
            </ul>
        <?php endif; ?>
    </div>

    <script>
        // Automatically set the active link based on current URL
        const currentPath = window.location.pathname;
        const sidebarLinks = document.querySelectorAll('.nav-links li a');
        sidebarLinks.forEach(link => {
            if (link.href.includes(currentPath)) {
                link.classList.add('active');
            }
        });
    </script>
</body>
</html>