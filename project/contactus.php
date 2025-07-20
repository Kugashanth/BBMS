<?php
// Include the database connection file
include 'config/db.php';

$thankYouMessage = "";

// Handling form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $phone = htmlspecialchars($_POST['phone']);
    $inquiry_type = htmlspecialchars($_POST['inquiry_type']);
    $message = htmlspecialchars($_POST['message']);

    // Use prepared statements for security
    $stmt = $conn->prepare("INSERT INTO contact (name, email, phone, inquiry_type, message) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $phone, $inquiry_type, $message);
    
    if ($stmt->execute()) {
        $thankYouMessage = "Thank you for contacting us! We will get back to you soon.";
        echo json_encode(["message" => $thankYouMessage]);
        exit;
    } else {
        echo json_encode(["message" => "Error: " . $stmt->error]);
        exit;
    }
}
?>

<?php include 'navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Blood Bank Management</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        /* General Styling */
        body, h1, h2, h3, p {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        /* Contact Section */
        .contact {
            background: #f9f9f9;
            padding: 60px 20px;
            text-align: center;
            margin-top: 80px;
        }

        .contact h1 {
            font-size: 32px;
            color: #d32f2f;
            margin-bottom: 20px;
        }

        .contact p {
            font-size: 18px;
            color: #333;
            max-width: 800px;
            margin: auto;
            line-height: 1.8;
            margin-bottom: 30px;
        }

        /* Contact Form */
        .contact-form {
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            width: 60%;
            margin: auto;
        }

        .contact-form input, .contact-form select, .contact-form textarea {
            width: 100%;
            padding: 15px;
            margin: 10px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 16px;
            color: #333;
        }

        .contact-form button {
            width: 100%;
            padding: 15px;
            background-color: #d32f2f;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 18px;
            cursor: pointer;
        }

        .contact-form button:hover {
            background-color: #b71c1c;
        }

        /* Modal for Thank You Message */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5); /* Dark overlay */
            /* display: flex; */
            justify-content: center; /* Center horizontally */
            align-items: center; /* Center vertically */
            z-index: 9999;
        }

        /* Modal Content Box */
        .modal-content {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            width: 80%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transform: translateY(0);
            transition: all 0.3s ease-in-out;
        }

        /* Modal Heading */
        .modal-content h2 {
            color: #4CAF50;
            font-size: 24px;
        }

        /* Modal Message Text */
        .modal-content p {
            margin-top: 10px;
            color: #333;
            font-size: 18px;
        }

        /* Close Button */
        .modal-close {
            margin-top: 20px;
            background: #d32f2f;
            color: white;
            border: none;
            padding: 12px 20px;
            cursor: pointer;
            border-radius: 6px;
            font-size: 16px;
            transition: background 0.3s ease;
        }

        .modal-close:hover {
            background: #b71c1c;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .contact-form {
                width: 90%;
            }
        }
    </style>
</head>
<body>

    <section class="contact">
        <div class="container">
            <h1 data-aos="fade-up">Contact Us</h1>
            <p data-aos="fade-up" data-aos-delay="200">If you have any questions or need support regarding blood donation, feel free to reach out.</p>
            
            <div class="contact-form" data-aos="fade-up" data-aos-delay="400">
                <form id="contactForm" method="POST">
                    <input type="text" name="name" placeholder="Your Name" required>
                    <input type="email" name="email" placeholder="Your Email" required>
                    <input type="tel" name="phone" pattern="[0-9]{10}" placeholder="Your Phone Number (Optional)">
                    <select name="inquiry_type" required>
                        <option value="">Type of Inquiry</option>
                        <option value="General Inquiry">General Inquiry</option>
                        <option value="Blood Donation">Blood Donation</option>
                        <option value="Feedback">Feedback</option>
                        <option value="Technical Support">Technical Support</option>
                    </select>
                    <textarea name="message" placeholder="Your Message" required></textarea>
                    <button type="submit">Send Message</button>
                </form>
            </div>
            
            <div class="modal">
                <div class="modal-content">
                    <h2>Thank You!</h2>
                    <p></p>
                    <button class="modal-close" onclick="closeModal()">Close</button>
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>
   
    <script>
        AOS.init();

        function closeModal() {
            document.querySelector(".modal").style.display = 'none';
        }

        $('#contactForm').submit(function(event) {
            event.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: 'contactus.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    let jsonResponse = JSON.parse(response);
                    $('.modal-content p').text(jsonResponse.message);
                    $('.modal').css('display', 'flex'); // Show the modal
                    $('#contactForm')[0].reset();
                },
                error: function() {
                    alert('Error occurred while submitting the form.');
                }
            });
        });
    </script>
</body>
</html>
