<?php
session_start();
include ('config/config.php');
?>

<?php
if (isset($_GET["locks"])) {
    session_unset();
    session_destroy();
    header("location: login.php");
    exit();
}
?>
<!-- r=query, rs=answer, req=request -->
<?php
if (isset($_POST ["ok"])) {
    $email = addslashes($_POST["email"]);
    $pass = addslashes(sha1($_POST["pass"]));

    $sql = $mysqli->query("SELECT * FROM users WHERE email = '" . $email . "' and password = '" . $pass . "' ");
    $answer = mysqli_num_rows($sql);
    if ($answer == 0) {
        echo '<script language = javascript>alert("incorrect identifier")</script>';
    } else {
        $request = mysqli_fetch_assoc($sql);
        $_SESSION["user_id"] = $request["user_id"];
        $_SESSION["username"] = $request["username"];
        $_SESSION["email"] = $request["email"];
        $_SESSION["phone_number"] = $request["phone_number"];
        $_SESSION["password"] = $request["password"];
        $_SESSION["profile_picture"] = $request["profile_picture"];
        $_SESSION["role"] = $request["role"];
        $_SESSION["latitude"] = $request["latitude"];
        $_SESSION["longitude"] = $request["longitude"];

        if (isset($_SESSION ["email"]) && ($_SESSION ["password"])) {
            if (($_SESSION ["role"] == "administrator") ) {
                header("location: administrator/admin.php?q=1");
            } else if (($_SESSION ["role"] == "collector") ) {
                header("location: collector/collector.php?q=1");
            } else {
                header("location: contributor/contributor.php?q=1");
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <link rel="stylesheet" type="text/css" href="ubcss/bootstrap.css"> 
        <meta charset="utf-8">
        <meta content="width=device-width, initial-scale=1.0" name="viewport">

        <title>Dailycollect</title>
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
                background-color: #4cae4c;
            }

            .fm{

                position: absolute;
                margin-left: 42%;
                margin-top: -10%;
                height: 50px;
                width: 250px;
                align-content: center;
            }



            .form-control:focus {
                z-index: 10;
                height: 40px;
                border-color: #4cae4c;
            }

            .btns{
                background-color: #7a43b6;
            }
            .info{
                width: 600;
            }

        </style>
    </head>

    <body>

        <!-- ======= Header ======= -->
        <header id="header" class="header d-flex align-items-center">
            <div class="container-fluid container-xl d-flex align-items-center justify-content-between">   <!-- 1.d-flex: Aligns Ub att with nav bar /// 2.justify-content-between: Means keeping a space between the UB attestation and the nav bar  -->

                <a href="index.html" class="logo d-flex align-items-center">
                    <img src="assets/img/logo.png" alt="">
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
                        <li><a href="register.php"><button class="btn btn-info">REGISTER</button></a></li> 
                    </ul>
                </nav><!-- .navbar -->

            </div>
        </header><!-- End Header -->


        <!-- ======= Hero Section ======= -->
        <section id="hero" class="hero">
            <div class="info d-flex align-items-center">

                <span class="fm"><form name="" method="POST" action="">
                        <label style=" margin-left: 40%; color: white;"><b>LOGIN</b></label><br><br>
                        <label class="bi bi-person-fill" style="color: white; height: 20px;"></label>
                        <input type="email" required="" class="form-control" placeholder="Enter your email" name="email" value="" autofocus=""><br>   
                        <label class="glyphicon-lock"></label>
                        <input  type="password" required="" class="form-control" placeholder="Enter your Password" name="pass" value="" autofocus=""><br>
                        <a href=""><button name="ok" class="btn btn-success" style="height: 30px; width: 250px"> login </button></a><br><br>
                    </form>
                    <span style="color: white;"><?php
                        echo "Don't have an account?";
                        ?></span>
                    <a href="register.php"><button class="btn btn-primary" style="width: 138px">sign in</button></a>
                </span>

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
