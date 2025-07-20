<?php include 'navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Blood Bank Management</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to external CSS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script> <!-- AOS for animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>

    /* General Reset */
    body, h1, h2, h3, p {
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
    }

    /* Container */
    .container {
        max-width: 1100px;
        margin: auto;
        padding: 20px;
    }
    .container h2{
      margin-top:20px;
      margin-bottom:20px;
    }

    /* About Section */
    .about {
        margin-top: 80px;
        text-align: center;
        padding: 60px 20px;
        background: #f9f9f9;
    }

    .about h1 {
        font-size: 32px;
        color: #d32f2f;
        margin-bottom: 15px;
    }

    .about p {
        text-align: justify;
        font-size: 18px;
        color: #333;
        max-width: 800px;
        margin: auto;
        line-height: 1.8;
    }

    /* Info Cards */
    .info-cards {
        display: flex;
        justify-content: center;
        gap: 30px;
        margin-top: 40px;
    }

    .card {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        width: 250px;
        text-align: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
        transform: translateY(-10px);
        box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.3);
    }

    .card i {
        font-size: 40px;
        color: #d32f2f;
        margin-bottom: 10px;
    }

    .card h3 {
        font-size: 22px;
        color: #333;         
    }

    .card p {
        font-size: 16px;
        color: #666;
    }
    /* Why Choose Us Section */
    .why-choose {
        background: #ffffff;
        padding: 10px 20px 60px;
        text-align: center;
    }

    .why-choose h2 {
        font-size: 28px;
        color: #d32f2f;
        margin-bottom: 20px;
    }

    .features {
        display: flex;
        justify-content: center;
        gap: 40px;
        flex-wrap: wrap;
    }

    .feature {
        background: #f1f1f1;
        padding: 20px;
        border-radius: 8px;
        width: 280px;
        text-align: center;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .feature i {
        font-size: 40px;
        color: #d32f2f;
        margin-bottom: 10px;
    }
    .feature:hover{
      transform: translateY(-10px);
      box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.3);
    }

    /* Back to Top Button */
    #backToTop {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #d32f2f;
        color: white;
        border: none;
        padding: 10px 15px;
        font-size: 20px;
        border-radius: 50%;
        cursor: pointer;
        display: none;
        transition: 0.3s;
    }

    #backToTop:hover {
        background: #b71c1c;
    }

    /* Dark Mode */
    body.dark-mode {
        background: #121212;
        color: white;
    }

    .dark-mode .about {
        background: #1e1e1e;
    }

    .dark-mode .card {
        background: #333;
        color: white;
    }

    #themeToggle {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 10px;
        border: none;
        cursor: pointer;
        background: #d32f2f;
        color: white;
        border-radius: 5px;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .info-cards {
            flex-direction: column;
            align-items: center;
        }

        .card {
            width: 90%;
        }

        button {
            font-size: 18px;
            padding: 15px 20px;
        }
    }
    </style>
</head>
<body>

    <button id="themeToggle">🌙 Dark Mode</button>

    <!-- About Section -->
    <section class="about">
        <div class="container">
            <h1 data-aos="fade-up">About Our Blood Bank Management System</h1>
            <p data-aos="fade-up" data-aos-delay="200">
                Our Blood Bank Management System is designed to efficiently manage blood inventory 
                across hospitals in Sri Lanka. Our mission is to ensure that blood donations and 
                transfusions are well-managed, reducing shortages and improving healthcare.
            </p>

            <h2 data-aos="fade-left">How It Works</h2>
            <div class="info-cards">
                <div class="card" data-aos="zoom-in">
                    <i class="fas fa-user-shield"></i>
                    <h3>Admin</h3>
                    <p>Manages blood bank data, user accounts, and system operations.</p>
                </div>
                <div class="card" data-aos="zoom-in" data-aos-delay="200">
                    <i class="fas fa-hospital"></i>
                    <h3>Hospitals</h3>
                    <p>Request blood units, update stock levels, and manage donor records.</p>
                </div>
            </div>

            <h2 data-aos="fade-right">Our Mission</h2>
            <p data-aos="fade-up" data-aos-delay="200">
                We aim to create a seamless platform that connects blood banks and hospitals, 
                ensuring timely availability of blood for patients in need.
            </p>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-choose">
        <h2 data-aos="fade-up">Why Choose Us?</h2>
        <div class="features">
            <div class="feature" data-aos="fade-up" data-aos-delay="100">
                <i class="fas fa-clock"></i>
                <h3>24/7 Availability</h3>
                <p>Hospitals can access real-time blood stock updates anytime.</p>
            </div>
            <div class="feature" data-aos="fade-up" data-aos-delay="200">
                <i class="fas fa-shield-alt"></i>
                <h3>Secure System</h3>
                <p>Data is protected with encrypted security measures.</p>
            </div>
            <div class="feature" data-aos="fade-up" data-aos-delay="300">
                <i class="fas fa-user"></i>
                <h3>User-Friendly</h3>
                <p>Easy-to-use interface for both hospitals and admins.</p>
            </div>
        </div>
    </section>


    <!-- Back to Top Button -->
    <button id="backToTop"><i class="fas fa-arrow-up"></i></button>

    <?php include 'footer.php'; ?>

    <script>
        AOS.init();

        // Back to Top Button
        window.onscroll = function () {
            let btn = document.getElementById("backToTop");
            if (document.documentElement.scrollTop > 200) {
                btn.style.display = "block";
            } else {
                btn.style.display = "none";
            }
        };

        document.getElementById("backToTop").onclick = function () {
            window.scrollTo({ top: 0, behavior: "smooth" });
        };

        // Dark Mode
        document.getElementById("themeToggle").onclick = function () {
            document.body.classList.toggle("dark-mode");
            this.innerHTML = document.body.classList.contains("dark-mode") ? "☀️ Light Mode" : "🌙 Dark Mode";
        };
    </script>

</body>
</html>
