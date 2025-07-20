<?php
// navbar.php
SESSION_START();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link rel="icon" href="img/blood.png" sizes="10">
  <title>Blood Wave</title>
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- FontAwesome for icons -->
  <style>
    /* Reset body margins */
    body {
      margin: 0;
      padding: 0;
     
      
    }
    /* Fix the navbar */

    /* Navbar Styling */
    .navbar {
       position: fixed;
      background-color: #F1F1F1;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 80px;
      z-index: 1000;
      padding: 0 20px;
      transition: all 0.3s ease;
    }

    .navbar .brand {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .navbar .brand img.blood-drop {
      height: 60px;
      margin-right: 10px;
    }

    .navbar .brand span {
      font-size: 24px;
      font-weight: bold;
      color: #1E3A8A;
    }

    .navbar .nav-links {
      display: flex;
      gap: 20px;
      align-items: center;
      transition: opacity 0.5s ease;
      padding: 0 50px;
    }

    .navbar .nav-links a {
      text-decoration: none;
      color: #1E3A8A;
      font-size: 18px;
      font-weight: bold;
      position: relative;
      padding: 10px 50px;
      transition: color 0.3s ease, background-color 0.3s ease;
      display: flex;
      align-items: center;
    }

    .navbar .nav-links a i {
      margin-right: 10px;
      font-size: 20px;
    }

    .navbar .nav-links a:hover {
      color:#D32F2F;
      text-decoration:underline;
    }

    /* Mobile Menu Button */
    .navbar .menu-icon {
      display: none;
      font-size: 30px;
      cursor: pointer;
      color: #1E3A8A;
    }

    /* Mobile & Tablet Responsiveness */
    @media screen and (max-width: 768px) {
      .navbar {
        padding: 15px 20px;
        flex-direction: column;
        justify-content: center;
        height: auto;
        box-shadow: none; /* Remove box-shadow on mobile */
        z-index: 1001;
      }

      .navbar .brand img.blood-drop {
        height: 50px;
      }

      .navbar .nav-links {
        flex-direction: column;
        margin-top: 10px;
        align-items: center;
        display: none; /* Hide menu on mobile */
        width: 100%;
        background-color: #F1F1F1; /* Background color for dropdown menu */
        border-radius: 10px;
        padding: 15px 0;
      }

      .navbar .nav-links a {
        font-size: 16px;
        padding: 0 20px;
        width: 100%; /* Make links take full width */
        text-align: center;
        border-bottom: 1px solid #ddd; /* Add divider between links */
        border-radius: 5px;
        transition: all 0.3s ease;
      }

      .navbar .nav-links a:hover {
        color: #ffffff;
        background-color: #D32F2F;
      }

      .navbar .menu-icon {
        display: block;
      }

      .navbar .nav-links.show {
        display: flex; /* Show menu when active */
        opacity: 1;
      }

      .navbar .nav-links a {
        transition: background-color 0.3s ease;
      }
    }

    /* Large screen adjustments */
    @media screen and (max-width: 1024px) {
      .navbar .nav-links {
        gap: 15px;
      }

      .navbar .nav-links a {
        padding: 8px 15px;
      }
    }

    /* Extra small devices (mobile) */
    @media screen and (max-width: 480px) {
      .navbar {
        padding: 10px;
      }

      .navbar .nav-links a {
        font-size: 14px;
        padding: 12px;
      }

      .navbar .brand span {
        font-size: 20px;
      }
    }

    /* Add smooth transitions to the navbar */
    .navbar {
      transition: background-color 0.3s ease, height 0.3s ease;
    }

  </style>
</head>
<body>
<div class="fix">
  
<div class="navbar">
  <div class="brand">
    <img src="img/logo.png" class="blood-drop">
    <span>Blood Wave</span>
  </div>
  <div class="nav-links">
    <a href="index.php"><i class="fas fa-home"></i> Home</a>
    <a href="about.php"><i class="fas fa-info-circle"></i> About</a>
    <a href="contactUs.php"><i class="fas fa-envelope"></i> Contact Us</a>
    <?php if (isset($_SESSION['admin_id'] )||isset($_SESSION['user_id'] )): ?>
      <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    <?php else: ?>
      <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
    <?php endif; ?>
  </div>
  <div class="menu-icon" onclick="toggleMenu()">
    <i class="fas fa-bars"></i> <!-- Hamburger icon -->
  </div>
</div>



<script>
  function toggleMenu() {
    const navLinks = document.querySelector('.nav-links');
    navLinks.classList.toggle('show');
  }
</script>


</div>
</body>
</html>
