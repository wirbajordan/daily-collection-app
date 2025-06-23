<?php
session_start(); // Start the session if needed

include_once 'config/config.php';

// Create a new mysqli instance
$mysqli = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Check if the form is submitted  
if (isset($_POST["ok"])) {
    // Get the input values and sanitize
    $user_name = trim($_POST['username']);
    $user_email = trim($_POST['email']);
    $user_phone_number = trim($_POST['phone_number']);
    $user_password = trim($_POST['password']);
    $user_role = trim($_POST['role']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $profile_image_path = 'default.png'; // Default image

    // Handle file upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $upload_dir = 'uploads/profile_images/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['profile_image']['type'];

        if (in_array($file_type, $allowed_types)) {
            $file_name = uniqid() . '-' . basename($_FILES['profile_image']['name']);
            $target_file = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                $profile_image_path = $file_name;
            }
        }
    }

    // Basic validation
    if (empty($user_name) || empty($user_email) || empty($user_phone_number) || empty($user_password)) {
        $_SESSION['error_message'] = "All fields are required.";
        header('Location: register.php'); // Redirect back to the registration form
        exit();
    } else {
        // Hash the password
        $passwordhash = sha1($user_password);
       //$hashed_password = password_hash($user_password, PASSWORD_DEFAULT);

        // Prepare and execute the SQL statement
        $stmt = $mysqli->prepare("INSERT INTO users (username, email, phone_number,  password, role, first_name, last_name, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt) {
            // Bind parameters
            $stmt->bind_param("ssssssss", $user_name, $user_email, $user_phone_number,   $passwordhash, $user_role, $first_name, $last_name, $profile_image_path);

            // Execute and check for success
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Registration successful! You can now log in.";
                header('Location: login.php'); // Redirect to login page

             // In your registration_logic.php after successful registration
           

            } else {
                $_SESSION['error_message'] = "Registration failed: " . $stmt->error;
                header('Location: register.php'); // Redirect back to the registration form
            }

            // Close the statement
            $stmt->close();
        } else {
            $_SESSION['error_message'] = "Preparation failed: " . $mysqli->error;
            header('Location: register.php'); // Redirect back to the registration form
        }
    }
}

// Close the database connection
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <link rel="stylesheet" type="text/css" href="ubcss/bootstrap.css"> 
        <meta charset="utf-8">
        <meta content="width=device-width, initial-scale=1.0" name="viewport">

        <title>DailyCollect</title>
        <meta content="" name="description">
        <meta content="" name="keywords">

        <!-- Favicons -->
        <link href="assets/img/favicon.png" rel="icon">
        <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link
            href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,600;1,700&family=Roboto:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=Work+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
            rel="stylesheet">

        <!-- Vendor CSS Files -->
        <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
        <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
        <link href="assets/vendor/aos/aos.css" rel="stylesheet">
        <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
        <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

        <!-- Template Main CSS File -->
        <link href="assets/css/main.css" rel="stylesheet">

        <!-- =======================================================
        * Template Name: UpConstruction - v1.3.0
        * Template URL: https://bootstrapmade.com/upconstruction-bootstrap-construction-website-template/
        * Author: BootstrapMade.com
        * License: https://bootstrapmade.com/license/
        ======================================================== -->
        <style>
            .body{
                background-color: pink;
            }

            .hero {
                height: 100vh;
                min-height: 100vh;
                position: relative;
            }

            .fm{
                position: absolute;
                margin-left: 40%;
                margin-top: 0;
                width: 280px;
                top: 50%;
                transform: translateY(-50%);
                z-index: 10;
                max-height: 90vh;
                overflow-y: auto;
                padding: 15px;
                background: rgba(0,0,0,0.5);
                border-radius: 10px;
            }

            .well{
                background-color: green;
            }

            .form-control:focus {
                z-index: 10;
                border-color: #4cae4c;
            }

            .btns{
                background-color: #7a43b6;
            }

            .txt{
                color: white;
            }

            #hero-carousel {
                height: 100vh;
                min-height: 100vh;
            }

            .carousel-item {
                height: 100vh;
                min-height: 100vh;
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
            }

        </style>
    </head>

    <body>

        <!-- ======= Header ======= -->
        <header id="header" class="header d-flex align-items-center">
            <div class="container-fluid container-xl d-flex align-items-center justify-content-between">   <!-- 1.d-flex: Aligns Ub att with nav bar /// 2.justify-content-between: Means keeping a space between the UB attestation and the nav bar  -->

            <a href="index.html" class="logo d-flex align-items-center">
                    <img src="assets/img/dailycollect.png" alt="DailyCollect Logo">
                    <h1>DAILYCOLLECT<span style="color:blue">.</span></h1>
                </a>

                <i class="mobile-nav-toggle mobile-nav-show bi bi-list"></i>
                <i class="mobile-nav-toggle mobile-nav-hide d-none bi bi-x"></i>
                <nav id="navbar" class="navbar">
                    <ul>
                        <li><a href="home.php" class="active">Home</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="contact.html">Contact</a></li>
                        <li><a href="login.php"><button class="btn btn-success">LOGIN</button></a></li> 
                        <li><a href="signup.php"><button class="btn btn-info">REGISTER</button></a></li> 
                    </ul>
                </nav><!-- .navbar -->

            </div>
        </header><!-- End Header -->


        <!-- ======= Hero Section ======= -->
        <section id="hero" class="hero">
            <div class="info d-flex align-items-center">

                <span class="fm"><form name="" method="POST" action="" enctype="multipart/form-data">
                        <label class="txt" style=" margin-left: 35%;"><b>REGISTER</b></label><br>
                        <label class="txt">First Name</label>
                        <input type="text" required="" class="form-control" placeholder="Enter your first name" name="first_name" value="" autofocus="" ><br>
                        <label class="txt">Last Name</label>
                        <input type="text" required="" class="form-control" placeholder="Enter your last name" name="last_name" value="" autofocus="" ><br>
                        <label class="txt">Profile Picture</label>
                        <input type="file" class="form-control" name="profile_image" accept="image/*"><br>
                        <label class="txt">Username</label>
                        <input type="text" required="" class="form-control" placeholder="Enter your username" name="username" value="" autofocus="" ><br>
                        <label class="txt">Email</label>
                        <input type="text" required="" class="form-control" placeholder="Enter your email" name="email" value="" autofocus="" ><br>
                        <label class="txt">Phone Number</label>
                        <input type="number" required="" class="form-control" placeholder="Enter your phone number" name="phone_number" value="" autofocus="" ><br>
                        <label class="txt">Password</label>
                        <input type="password" required="" class="form-control" placeholder="Enter your password" name="password" value="" autofocus="" ><br>
                         
                        <label class="txt">role</label>
                        <select id="role" name="role" placeholder="Select your role" class="form-control input-md" >
                            <option value="" <?php
                            if (!isset($_GET['role']))
                                echo "selected";
                            ?>>Select Role</option>
                            <option value="collector" <?php
                            if (isset($_GET['role'])) {
                                if ($_GET['role'] == "collector")
                                    echo "selected";
                            }
                            ?>>collector</option>
                            <option value="contributor" <?php
                            if (isset($_GET['role'])) {
                                if ($_GET['role'] == "contributor")
                                    echo "selected";
                            }
                            ?>>contributor</option> </select>
                            <br>
                        <a href="login.php"><button name="ok" class="btn btn-primary" style="height: 37px; width: 250px"> register </button></a><br><br>
                    </form></span>

            </div>


            <div id="hero-carousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="500000">

            <div class="carousel-item active" style="background-image: url(assets/img/hero-carousel/five.jpg)">
                </div>
                <div class="carousel-item" style="background-image: url(assets/img/hero-carousel/coins.jpg)"></div>
                <div class="carousel-item" style="background-image: url(assets/img/hero-carousel/coins2.jpg)"></div>
                <div class="carousel-item" style="background-image: url(assets/img/hero-carousel/coins3.jpg)"></div>
                <div class="carousel-item" style="background-image: url(assets/img/hero-carousel/ten1.jpg)"></div>

                <a class="carousel-control-prev" href="#hero-carousel" role="button" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon bi bi-chevron-left" aria-hidden="true"></span>
                </a>

                <a class="carousel-control-next" href="#hero-carousel" role="button" data-bs-slide="next">
                    <span class="carousel-control-next-icon bi bi-chevron-right" aria-hidden="true"></span>
                </a>

            </div>

        </section><!-- End Hero Section -->


        <!-- Vendor JS Files -->
        <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="assets/vendor/aos/aos.js"></script>
        <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
        <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
        <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
        <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
        <script src="assets/vendor/php-email-form/validate.js"></script>

        <!-- Template Main JS File -->
        <script src="assets/js/main.js"></script>

        <link rel="stylesheet" type="text/css" href="css/bootstrap.css"> 
        <title>LOGIN</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!-- Vendor CSS Files -->
        <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
        <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
        <link href="assets/vendor/aos/aos.css" rel="stylesheet">
        <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
        <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

        <!-- Template Main CSS File -->
        <link href="assets/css/main.css" rel="stylesheet">


    </head>        
</body>
</html>

