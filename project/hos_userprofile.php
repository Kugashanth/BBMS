<?php include 'navbar.php'; ?>
<?php include 'sidebar.php'; ?>

<?php
// Check if hospital admin is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$hospital_id = $_SESSION['user_id'];

// Fetch hospital details
$query = "SELECT * FROM hospitals WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $hospital_id);
$stmt->execute();
$result = $stmt->get_result();
$hospital = $result->fetch_assoc();

// Update password
if (isset($_POST['update_password'])) {
    $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $query = "UPDATE hospitals SET password = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $new_password, $hospital_id);
    $stmt->execute();
    header("Location: hospital_profile.php");
    exit();
}

// Update profile image
if (isset($_POST['update_img'])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["img"]["name"]);
    move_uploaded_file($_FILES["img"]["tmp_name"], $target_file);
    $query = "UPDATE hospitals SET img = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $target_file, $hospital_id);
    $stmt->execute();
    header("Location: hospital_profile.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Hospital Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/setting.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Content Adjustment */
        .content {
            margin-left: 60px; /* Align content with sidebar */
            padding: 20px 30px 10px 20px;
            width: calc(100% - 120px);
            transition: margin-left 0.3s ease-in-out;
            margin-top: 80px; /* To avoid being overlapped by navbar */
            overflow-y: hidden;
        }

         .profilecontainer{
         /* width: 190px;
        height: 254px; */
        border-radius: 30px;
        background:rgb(255, 255, 255);
        box-shadow: 15px 15px 30px #bebebe,
                   -15px -15px 30px #ffffff;
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
        
        /* Content adjustment when sidebar expands */
        .content2 {
            transition: margin-left 0.3s ease, width 0.3s ease; /* Smooth transition */
        }

        .sidebar:hover ~ .content2 {
            margin-left: 250px; /* Adjust when sidebar expands */
            width: calc(100% - 250px);
        }
    </style>
</head>

<body>
<div class="content">
    <div class="profilecontainer">
        <p id="password-message"></p>
        <p id="img-message"></p>
        <h2>Hospital Profile</h2>
        <img src="<?php echo $hospital['img'] ?: 'default.jpg'; ?>" width="100" height="100" id="profile-img">
        <p>Name: <span id="name-display"><?php echo $hospital['name']; ?></span></p>
        <p>Username: <?php echo $hospital['username']; ?></p>
        <p>Email: <?php echo $hospital['email']; ?></p>
        <p>Location: <?php echo $hospital['location']; ?></p>
        <p>Contact: <?php echo $hospital['contact']; ?></p>
        <p>Phone: <?php echo $hospital['phone']; ?></p>
        <p>Province: <?php echo $hospital['province']; ?></p>
        <p>District: <?php echo $hospital['district']; ?></p>
        <p>Website: <?php echo $hospital['website'] ?: 'Not provided'; ?></p>
        
        <button id="change-password-btn">Change Password</button>
        <button id="change-img-btn">Change Profile Image</button>
    </div>

    <!-- Password Modal -->
    <div id="password-modal" class="modal">
        <div class="modal-content">
            <p class="close-btn" onclick="closeModal('password-modal')">
                <i class="fa fa-times"></i>
            </p>
            <h3>Change Password</h3>
            <form id="password-form">
                <input type="password" name="password" id="password" required placeholder="Enter New Password">
                <button type="submit">Update</button>
            </form>
        </div>
    </div>

    <!-- Image Modal -->
    <div id="img-modal" class="modal">
        <div class="modal-content">
            <p class="close-btn" onclick="closeModal('img-modal')">
                <i class="fa fa-times"></i>
            </p>
            <h3>Change Profile Image</h3>
            <form id="img-form" enctype="multipart/form-data">
                <input type="file" name="img" id="img" required>
                <button type="submit">Update</button>
            </form>
        </div>
    </div>
</div>
<div class="content2">
    <?php include 'footer.php'; ?>
</div>

<script>
    $(document).ready(function() {
        $("#password-form").submit(function(e) {
            e.preventDefault();
            let password = $("#password").val();
            $.ajax({
                url: "update_hospital.php",
                type: "POST",
                data: { update_password: true, password: password },
                success: function(response) {
                    $("#password-message").text(response);
                    setTimeout(() => { closeModal('password-modal'); }, 500);
                }
            });
        });

        $("#img-form").submit(function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajax({
                url: "update_hospital.php",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $("#img-message").text(response);
                    setTimeout(() => { closeModal('img-modal'); location.reload(); }, 10);
                }
            });
        });
    });

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = "none";
    }

    document.getElementById("change-password-btn").addEventListener("click", function() {
        document.getElementById("password-modal").style.display = "flex";
    });

    document.getElementById("change-img-btn").addEventListener("click", function() {
        document.getElementById("img-modal").style.display = "flex";
    });
</script>
</body>
</html>