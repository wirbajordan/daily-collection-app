<?php
include_once ('../config/config.php');
session_start();

// Initialize variables messages
$successMessage = '';
$errorMessage = '';



// Check user role
if ($_SESSION['role'] != 'administrator') {
    header('Location: .../login.php');//redirect to logged in if role not valide
    exit();
}


// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: .../login.php'); // Redirect to login if not logged in
    exit();
}    

?>





<!DOCTYPE html>   
<html>
    <head>
       
        <link rel="stylesheet" type="text/css" href="../ubcss/bootstrap-3.0.0/dist/css/bootstrap.css">
        <script src="../ubjs/script.js"></script>
        <script src="../ubjs/jquery.js"></script>
        <script src="../ubjs/ajaxWorks.js"></script>
        <script src="../ubjs/bootstrap.min.js"></script>
        <script src="../ubjs/holder.js"></script>
        <meta charset="UTF-8">

        <link rel="stylesheet" type="text/css" href="../ubcss/bootstrap.css"> 
        <link rel="stylesheet" type="text/css" href="../ubcss/admin.css"> 
        <meta charset="utf-8">
        <meta content="width=device-width, initial-scale=1.0" name="viewport">
        <title>Daily Collect</title>

        <!-- Favicons -->
        <link href="../assets/img/favicon.png" rel="icon">
        <link href="../assets/img/apple-touch-icon.png" rel="apple-touch-icon">

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link
            href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,600;1,700&family=Roboto:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=Work+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
            rel="stylesheet">

        <!-- Vendor CSS Files -->
        <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
        <link href="../assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
        <link href="../assets/vendor/aos/aos.css" rel="stylesheet">
        <link href="../assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
        <link href="../assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

        <!-- Template Main CSS File -->
        <link href="../assets/css/main.css" rel="stylesheet">
        <link href=" admin_dashboard_css/styles.css" rel="stylesheet">
        <script src="admin_dashboard_js/scripts.js" defer></script>


        <style>
            .tit{
                margin-left: 40px;
            }
            .title{
                font-family:'typo';
            }
            .title1{
                font: 12px "Century Gothic", "Times Roman", sans-serif;
            }
            .header{
                background:#495057;
                height:111px;
            }
            .logo{
                color: white;
            }
            .panel{
                border-color:#eee;
                margin:20px;
                padding:20px;
                font: 15px "Century Gothic", "Times Roman", sans-serif;
            }
            .body{
                background-color: pink;
            }

            .fm{
                position: absolute;
                margin-left: 40%;
                margin-top: -1.5%;
                height: 20px;
                width: 250px;
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
            .fa.active{
                background: #fff;
            }

        </style>

    </head>
    <body style="color:black; background-color: #eee;">


        <div class="header" style="background-image: url(../assets/img/hero-carousel/html1.jpg)">
            <div class="container-fluid ">
                <div class="col-lg-12">
                    <span class="logo">Daily Collect</span>

                    <?php
                    if ((!($_SESSION ["email"]))) {
                        session_destroy();
                        header("location:.../login.php");
                    } else {

                        $email = $_SESSION['email'];
                        $username = $_SESSION['username'];
                        $password = $_SESSION['password'];
                        include_once ('../config/config.php');
                        echo '<span class="pull-right top title1" style="margin-left:40px;">'
                        . '<span style="color:white"><span class="glyphicon glyphicon-user" aria-hidden="true"></span>&nbsp;&nbsp;&nbsp;&nbsp;Hello,</span>'
                        . ' <span class="log log1" style="color:lightyellow">' . $username . '&nbsp;&nbsp;|&nbsp;&nbsp;'
                        . '<a href="javascript:deconnexion()" style="color:lightyellow">'
                        . '<span class="glyphicon glyphicon-log-out" aria-hidden="true"></span>&nbsp;Logout</button></a></span>';
                    }
                    $result = mysqli_query($mysqli, "SELECT * FROM users WHERE username='$username'") or die('Error');
                    ($row = mysqli_fetch_array($result));
                    $user_id = $row['user_id'];

                    ?>

                    <div class="sidebar"  id="mySidebar" style="background-image: url(../assets/img/hero-carousel/html1.jpg)">
                        <div class="side-header">
                            <img src="./assets/img/ub2.png"  width="100" height="100" alt="Daily Collection"> 
                        </div>
                        <hr style="border:1px solid; background-color:#4cae4c; border-color:#3B3131; color: white;">                       
                        <li class="menu"> <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">x  </a></li>
                        <a  <?php if (@$_GET['q'] == 1) echo 'class="active"'; ?> href="admin.php?q=1"><span class="fa fa-house" style="color:white; font-size: 15px;">  Home<span class="sr-only">(current)</span></span></a><br><br><br>
                        <a  <?php if (@$_GET['q'] == 2) echo 'class="active"'; ?> href="admin.php?q=2 & page=<?php echo base64_encode('../collector_list'); ?>"><span class="fa fa-user" style="color:white; font-size: 15px;">   Collectors<span class="sr-only">(current)</span></span></a><br><br><br>
                        <a  <?php if (@$_GET['q'] == 2) echo 'class="active"'; ?> href="admin.php?q=2 & page=<?php echo base64_encode('../contributor_list'); ?>"><span class="fa fa-user" style="color:white; font-size: 15px;">  Contributors<span class="sr-only">(current)</span></span></a><br><br><br>
                        <a  <?php if (@$_GET['q'] == 2) echo 'class="active"'; ?> href="admin.php?q=2 & page=<?php echo base64_encode('admin_dashboard'); ?>"><span class="fa fa-user" style="color:white; font-size: 15px;">Assign  Collector<span class="sr-only">(current)</span></span></a><br><br><br>

                        <a  <?php if (@$_GET['q'] == 2) echo 'class="active"'; ?> href="admin.php?q=2 & page=<?php echo base64_encode('../manage-reports'); ?>"><span class="fa fa-book" style="color:white; font-size: 15px;">  Report<span class="sr-only">(current)</span></span></a><br><br><br>
                        <a  <?php if (@$_GET['q'] == 2) echo 'class="active"'; ?> href="admin.php?q=2 & page=<?php echo base64_encode('../notification'); ?>"><span class="fa fa-book" style="color:white; font-size: 15px;">  Notification<span class="sr-only">(current)</span></span></a><br><br><br>
                    </div>
                    <!---->
                </div>
                <div id="main">
                    <button class="openbtn" onclick="openNav()" style="background-image: url(assets/img/hero-carousel/mysql2.png)"><i class="bi bi-house-fill"></i></button>
                </div>

                <?php
                if (@$_GET['q'] == 1) { ?>
                <div class = "col-sm-3" style="margin-left:18%; margin-top:3%; data-aos="fade-up data-aos-delay="100">
                <a href="admin.php?q=2 & page=<?php echo base64_encode('../collector.php'); ?>"class = "card" style="background-color:skyblue">
                <i class = "fa fa-users  mb-5" style = "font-size: 70px; color:white;"></i>
                <h4 style = "color:white;">Total Collectors</h4>
                <h5 style = "color:white;">

                <?php    $sql = "SELECT * from users where role='collector'";
                    $result = $mysqli->query($sql);
                    $count = 0;
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {

                            $count = $count + 1;
                        }
                    }
                    echo $count; ?>
                    </h5></a></div>

                <div class = "col-sm-3" style="margin-top:3%;">
                <a href="admin.php?q=2 & page=<?php echo base64_encode('ubpages/ubusers/supervisor/viewSupervisor'); ?>" class = "card" style="background-image: url(../assets/img/hero-carousel/java2.jpg)">
                <i class = "fa fa-users  mb-5" style = "font-size: 70px; color:white;"></i>
                <h4 style = "color:white;">Total Contributors</h4>
                <h5 style = "color:white;">

                <?php  $sql = "SELECT * from users where role='contributor'";
                    $result = $mysqli->query($sql);
                    $count = 0;
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {

                            $count = $count + 1;
                        }
                    }
                    echo $count; ?>
                    </h5></a></div>
                <?php } ?>
                
                <?php
                if (@$_GET['q'] == 2) {
                    if (isset($_REQUEST ["page"])) {
                        $page = base64_decode($_REQUEST ["page"]) . ".php";
                        if (file_exists($page)) {
                            include ($page);
                        } else {
                            echo 'page dos not exist';
                        }
                    } else {
                        include ('admin.php');
                    }
                }
                ?>

            </div>
        </div>
    </body>
</html>




