<?php
ob_start();
include_once ('../config/config.php');

// Handle user deletion
if (isset($_GET['page']) && isset($_GET['user_id']) && base64_decode($_GET['page']) === 'ubpages/ubsettings/user/userAjax') {
    $user_id = $_GET['user_id'];

    // First, delete notifications for the user
    $sql_notifications = "DELETE FROM notification WHERE user_id = ?";
    if ($stmt_notifications = $mysqli->prepare($sql_notifications)) {
        $stmt_notifications->bind_param("i", $user_id);
        if (!$stmt_notifications->execute()) {
            echo "Error deleting notifications: " . $mysqli->error;
            exit;
        }
        $stmt_notifications->close();
    } else {
        echo "Error preparing statement for notifications: " . $mysqli->error;
        exit;
    }

    // Then, delete the user
    $sql_user = "DELETE FROM users WHERE user_id = ?";
    if ($stmt_user = $mysqli->prepare($sql_user)) {
        $stmt_user->bind_param("i", $user_id);
        if ($stmt_user->execute()) {
            // On success, redirect back to the referrer page
            if (isset($_SERVER['HTTP_REFERER'])) {
                header("Location: " . $_SERVER['HTTP_REFERER']);
            } else {
                // Fallback if referrer is not available
                header("Location: admin.php?q=1");
            }
            exit;
        } else {
            echo "Error deleting user: " . $mysqli->error;
            exit;
        }
        $stmt_user->close();
    } else {
        echo "Error preparing statement for user deletion: " . $mysqli->error;
        exit;
    }
}

// Handle un-assignment (now via POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unassign_contributor_id'])) {
    $contributor_id_to_unassign = intval($_POST['unassign_contributor_id']);
    // Debug: Log the unassign attempt
    error_log("Unassign attempt for contributor ID: " . $contributor_id_to_unassign);
    // Add error handling and logging
    if ($contributor_id_to_unassign > 0) {
        // First check if the assignment exists
        $check_stmt = $mysqli->prepare("SELECT contributor_id FROM assignments WHERE contributor_id = ?");
        if ($check_stmt) {
            $check_stmt->bind_param("i", $contributor_id_to_unassign);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            if ($check_result->num_rows > 0) {
                // Assignment exists, proceed with deletion
                $unassign_stmt = $mysqli->prepare("DELETE FROM assignments WHERE contributor_id = ?");
                if ($unassign_stmt) {
                    $unassign_stmt->bind_param("i", $contributor_id_to_unassign);
                    if ($unassign_stmt->execute()) {
                        if ($unassign_stmt->affected_rows > 0) {
                            $_SESSION['message'] = 'Assignment successfully removed.';
                            error_log("Assignment removed successfully for contributor ID: " . $contributor_id_to_unassign);
                        } else {
                            $_SESSION['error'] = 'No assignment found to remove.';
                            error_log("No rows affected when trying to remove assignment for contributor ID: " . $contributor_id_to_unassign);
                        }
                    } else {
                        $_SESSION['error'] = 'Failed to remove assignment: ' . $mysqli->error;
                        error_log("Failed to execute unassign query: " . $mysqli->error);
                    }
                    $unassign_stmt->close();
                } else {
                    $_SESSION['error'] = 'Database error: ' . $mysqli->error;
                    error_log("Failed to prepare unassign statement: " . $mysqli->error);
                }
            } else {
                $_SESSION['error'] = 'No assignment found for this contributor.';
                error_log("No assignment found for contributor ID: " . $contributor_id_to_unassign);
            }
            $check_stmt->close();
        } else {
            $_SESSION['error'] = 'Database error checking assignment: ' . $mysqli->error;
            error_log("Failed to prepare check statement: " . $mysqli->error);
        }
    } else {
        $_SESSION['error'] = 'Invalid contributor ID.';
        error_log("Invalid contributor ID provided: " . $_POST['unassign_contributor_id']);
    }
    // Redirect back to the same admin dashboard page
    $redirect_url = 'admin.php?q=2&page=' . urlencode(base64_encode('admin_dashboard'));
    header('Location: ' . $redirect_url);
    exit();
}

session_start();

// Initialize variables messages
$successMessage = '';
$errorMessage = '';

// Check user role
if ($_SESSION['role'] != 'administrator') {
    header('Location: ../login.php'); //redirect to logged in if role not valid
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php'); // Redirect to login if not logged in
    exit();
}    

// Assuming user ID is stored in session after login
$user_id = $_SESSION['user_id'];

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
        <link  rel="stylesheet" href="admin_dashboard_css/styles.css">
        <script src="admin_dashboard_js/scripts.js" defer></script>

        <style>
            /* this is the css for the admin.php*/
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

        
     /* part of the css for notification text area */
     textarea {
        width: 50%;
        height: 200px;
        margin-bottom: 10px;
        font-size: 25px;
        }

        body {
        font-family: Arial, sans-serif;
        color: #333;
        background-size: cover;
        background-position: center;
        height: 100vh;
        margin: 0;
        padding: 20px;
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
                        . '<a href="../home.php" style="color:lightyellow">'
                        . '<span class="glyphicon glyphicon-log-out" aria-hidden="true"></span>&nbsp;Logout</button></a></span>';
                    }
                    $result = mysqli_query($mysqli, "SELECT * FROM users WHERE username='$username'") or die('Error');
                    ($row = mysqli_fetch_array($result));
                    $user_id = $row['user_id'];

                    ?>

                    <div class="sidebar"  id="mySidebar" style="background-image: url(../assets/img/hero-carousel/html1.jpg)">
                        <div class="side-header">
                            <img src="../images/vision-finance-logo.png"  width="100" height="100" alt="Daily Collection"> 
                        </div>
                        <hr style="border:1px solid; background-color:#4cae4c; border-color:#3B3131; color: white;">                       
                        <li class="menu"> <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">x  </a></li>
                        <a  <?php if (@$_GET['q'] == 1) echo 'class="active"'; ?> href="admin.php?q=1"><span class="fa fa-house" style="color:white; font-size: 15px;">  Home<span class="sr-only">(current)</span></span></a><br><br><br>
                        <a  <?php if (@$_GET['q'] == 2) echo 'class="active"'; ?> href="admin.php?q=2&page=<?php echo base64_encode('../collector_list'); ?>"><span class="fa fa-user" style="color:white; font-size: 15px;">   Collectors<span class="sr-only">(current)</span></span></a><br><br><br>
                        <a  <?php if (@$_GET['q'] == 2) echo 'class="active"'; ?> href="admin.php?q=2&page=<?php echo base64_encode('../contributor_list'); ?>"><span class="fa fa-user" style="color:white; font-size: 15px;">  Contributors<span class="sr-only">(current)</span></span></a><br><br><br>
                        <a  <?php if (@$_GET['q'] == 2) echo 'class="active"'; ?> href="admin.php?q=2 & page=<?php echo base64_encode('admin_dashboard'); ?>"><span class="fa fa-user" style="color:white; font-size: 15px;">Assign  Collector<span class="sr-only">(current)</span></span></a><br><br><br>
                        <a  <?php if (@$_GET['q'] == 2) echo 'class="active"'; ?> href="admin.php?q=2 & page=<?php echo base64_encode('../manage-reports'); ?>"><span class="fa fa-book" style="color:white; font-size: 15px;">  Report<span class="sr-only">(current)</span></span></a><br><br><br>
                        <a  <?php if (@$_GET['q'] == 2) echo 'class="active"'; ?> href="admin.php?q=2 & page=<?php echo base64_encode('../notification'); ?>"><span class="fa fa-book" style="color:white; font-size: 15px;">  Notification<span class="sr-only">(current)</span></span></a><br><br><br>
                        <a  <?php if (@$_GET['q'] == 2) echo 'class="active"'; ?> href="admin.php?q=2&page=<?php echo base64_encode('admin_collector_ratings'); ?>"><span class="fa fa-star" style="color:white; font-size: 15px;">  Collector Ratings<span class="sr-only">(current)</span></span></a><br><br><br>
                    </div>
                    <!---->
                </div>
                <div id="main">
                    <button class="openbtn" onclick="openNav()" style="background-image: url(assets/img/hero-carousel/mysql2.png)"><i class="bi bi-house-fill"></i></button>
                </div>

                <?php
                if (@$_GET['q'] == 1) { ?>
                <!-- Dashboard Cards Container -->
                <div class="container-fluid" style="margin-left: 270px; width: calc(100% - 280px); margin-top: 3%; min-height: 150vh; padding-bottom: 100px;">
                    <!-- First Row -->
                    <div class="row">
                        <div class="col-sm-4" style="padding: 0 10px;">
                            <a href="admin.php?q=2&page=<?php echo base64_encode('../collector_list'); ?>" class="card" style="background-color:skyblue; text-align: center; padding: 20px; margin-bottom: 30px; min-height: 200px;">
                                <i class="fa fa-users mb-3" style="font-size: 60px; color:white;"></i>
                                <h4 style="color:white; font-size: 16px;">Total Collectors</h4>
                                <h5 style="color:white; font-size: 18px;">
                                <?php    $sql = "SELECT * from users where role='collector'";
                                    $result = $mysqli->query($sql);
                                    $count = 0;
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $count = $count + 1;
                                        }
                                    }
                                    echo $count; ?>
                                </h5>
                            </a>
                        </div>

                        <div class="col-sm-4" style="padding: 0 10px;">
                            <a href="admin.php?q=2&page=<?php echo base64_encode('../contributor_list'); ?>" class="card" style="background-image: url(../assets/img/hero-carousel/java2.jpg); text-align: center; padding: 20px; margin-bottom: 30px; min-height: 200px;">
                                <i class="fa fa-users mb-3" style="font-size: 60px; color:white;"></i>
                                <h4 style="color:white; font-size: 16px;">Total Contributors</h4>
                                <h5 style="color:white; font-size: 18px;">
                                <?php  $sql = "SELECT * from users where role='contributor'";
                                    $result = $mysqli->query($sql);
                                    $count = 0;
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $count = $count + 1;
                                        }
                                    }
                                    echo $count; ?>
                                </h5>
                            </a>
                        </div>

                        <div class="col-sm-4" style="padding: 0 10px;">
                            <a href="admin.php?q=2 & page=<?php echo base64_encode('../manage-reports'); ?>" class="card" style="background-color:#28a745; text-align: center; padding: 20px; margin-bottom: 30px; min-height: 200px;">
                                <i class="fa fa-money mb-3" style="font-size: 60px; color:white;"></i>
                                <h4 style="color:white; font-size: 16px;">Total Collections</h4>
                                <h5 style="color:white; font-size: 18px;">
                                <?php  $sql = "SELECT COALESCE(SUM(amount), 0) as total from transaction";
                                    $result = $mysqli->query($sql);
                                    $total = 0;
                                    if ($result->num_rows > 0) {
                                        $row = $result->fetch_assoc();
                                        $total = $row['total'];
                                    }
                                    echo number_format($total, 2) . ' CFA'; ?>
                                </h5>
                            </a>
                        </div>
                    </div>

                    <!-- Second Row -->
                    <div class="row">
                        <div class="col-sm-4" style="padding: 0 10px;">
                            <a href="admin.php?q=2 & page=<?php echo base64_encode('admin_dashboard'); ?>" class="card" style="background-color:#17a2b8; text-align: center; padding: 20px; margin-bottom: 30px; min-height: 200px;">
                                <i class="fa fa-link mb-3" style="font-size: 60px; color:white;"></i>
                                <h4 style="color:white; font-size: 16px;">Active Assignments</h4>
                                <h5 style="color:white; font-size: 18px;">
                                <?php  $sql = "SELECT COUNT(*) as count from assignments";
                                    $result = $mysqli->query($sql);
                                    $assignments = 0;
                                    if ($result->num_rows > 0) {
                                        $row = $result->fetch_assoc();
                                        $assignments = $row['count'];
                                    }
                                    echo $assignments; ?>
                                </h5>
                            </a>
                        </div>

                        <div class="col-sm-4" style="padding: 0 10px;">
                            <a href="admin.php?q=2 & page=<?php echo base64_encode('../notification'); ?>" class="card" style="background-color:#dc3545; text-align: center; padding: 20px; margin-bottom: 30px; min-height: 200px;">
                                <i class="fa fa-bell mb-3" style="font-size: 60px; color:white;"></i>
                                <h4 style="color:white; font-size: 16px;">Pending Notifications</h4>
                                <h5 style="color:white; font-size: 18px;">
                                <?php  $sql = "SELECT COUNT(*) as count from notification WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
                                    $result = $mysqli->query($sql);
                                    $notifications = 0;
                                    if ($result->num_rows > 0) {
                                        $row = $result->fetch_assoc();
                                        $notifications = $row['count'];
                                    }
                                    echo $notifications; ?>
                                </h5>
                            </a>
                        </div>

                        <div class="col-sm-4" style="padding: 0 10px;">
                            <a href="admin.php?q=2 & page=<?php echo base64_encode('../manage-reports'); ?>" class="card" style="background-color:#6f42c1; text-align: center; padding: 20px; margin-bottom: 30px; min-height: 200px;">
                                <i class="fa fa-chart-line mb-3" style="font-size: 60px; color:white;"></i>
                                <h4 style="color:white; font-size: 16px;">This Month</h4>
                                <h5 style="color:white; font-size: 18px;">
                                <?php  $sql = "SELECT COALESCE(SUM(amount), 0) as total from transaction WHERE MONTH(Date) = MONTH(CURDATE()) AND YEAR(Date) = YEAR(CURDATE())";
                                    $result = $mysqli->query($sql);
                                    $month_total = 0;
                                    if ($result->num_rows > 0) {
                                        $row = $result->fetch_assoc();
                                        $month_total = $row['total'];
                                    }
                                    echo number_format($month_total, 2) . ' CFA'; ?>
                                </h5>
                            </a>
                        </div>
                    </div>

                    <!-- Third Row -->
                    <div class="row">
                        <div class="col-sm-4" style="padding: 0 10px;">
                            <a href="admin.php?q=2 & page=<?php echo base64_encode('../manage-reports'); ?>" class="card" style="background-color:#ffc107; text-align: center; padding: 20px; margin-bottom: 30px; min-height: 200px;">
                                <i class="fa fa-calendar mb-3" style="font-size: 60px; color:white;"></i>
                                <h4 style="color:white; font-size: 16px;">Today's Collections</h4>
                                <h5 style="color:white; font-size: 18px;">
                                <?php  $sql = "SELECT COALESCE(SUM(amount), 0) as total from transaction WHERE DATE(Date) = CURDATE()";
                                    $result = $mysqli->query($sql);
                                    $today_total = 0;
                                    if ($result->num_rows > 0) {
                                        $row = $result->fetch_assoc();
                                        $today_total = $row['total'];
                                    }
                                    echo number_format($today_total, 2) . ' CFA'; ?>
                                </h5>
                            </a>
                        </div>

                        <div class="col-sm-4" style="padding: 0 10px;">
                            <a href="admin.php?q=2 & page=<?php echo base64_encode('../contributor_list'); ?>" class="card" style="background-color:#ff9800; text-align: center; padding: 20px; margin-bottom: 30px; min-height: 200px;">
                                <i class="fa fa-star mb-3" style="font-size: 60px; color:white;"></i>
                                <h4 style="color:white; font-size: 16px;">Top Contributor</h4>
                                <h5 style="color:white; font-size: 18px;">
                                <?php
                                    $sql = "SELECT t.username, SUM(t.amount) AS total_contribution FROM transaction t JOIN users u ON t.username = u.username AND u.role = 'contributor' GROUP BY t.username ORDER BY total_contribution DESC LIMIT 1";
                                    $result = $mysqli->query($sql);
                                    $top_contributor = 'N/A';
                                    if ($result && $result->num_rows > 0) {
                                        $row = $result->fetch_assoc();
                                        $top_contributor = $row['username'];
                                    }
                                    echo $top_contributor;
                                ?>
                                </h5>
                            </a>
                        </div>

                        <div class="col-sm-4" style="padding: 0 10px;">
                            <a href="admin.php?q=2 & page=<?php echo base64_encode('../manage-reports'); ?>" class="card" style="background-color:#fd7e14; text-align: center; padding: 20px; margin-bottom: 30px; min-height: 200px;">
                                <i class="fa fa-chart-bar mb-3" style="font-size: 60px; color:white;"></i>
                                <h4 style="color:white; font-size: 16px;">Weekly Average</h4>
                                <h5 style="color:white; font-size: 18px;">
                                <?php  $sql = "SELECT COALESCE(AVG(daily_total), 0) as weekly_avg FROM (
                                    SELECT DATE(Date) as date, SUM(amount) as daily_total 
                                    FROM transaction 
                                    WHERE Date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                                    GROUP BY DATE(Date)
                                ) as daily_totals";
                                    $result = $mysqli->query($sql);
                                    $weekly_avg = 0;
                                    if ($result->num_rows > 0) {
                                        $row = $result->fetch_assoc();
                                        $weekly_avg = $row['weekly_avg'];
                                    }
                                    echo number_format($weekly_avg, 2) . ' CFA'; ?>
                                </h5>
                            </a>
                        </div>
                    </div>

                    <!-- Fourth Row -->
                    <div class="row">
                        <div class="col-sm-4" style="padding: 0 10px;">
                            <a href="admin.php?q=2 & page=<?php echo base64_encode('../manage-reports'); ?>" class="card" style="background-color:#20c997; text-align: center; padding: 20px; margin-bottom: 30px; min-height: 200px;">
                                <i class="fa fa-user-plus mb-3" style="font-size: 60px; color:white;"></i>
                                <h4 style="color:white; font-size: 16px;">Unassigned Contributors</h4>
                                <h5 style="color:white; font-size: 18px;">
                                <?php  $sql = "SELECT COUNT(*) as count FROM users u 
                                    LEFT JOIN assignments a ON u.user_id = a.contributor_id 
                                    WHERE u.role = 'contributor' AND a.contributor_id IS NULL";
                                    $result = $mysqli->query($sql);
                                    $unassigned = 0;
                                    if ($result->num_rows > 0) {
                                        $row = $result->fetch_assoc();
                                        $unassigned = $row['count'];
                                    }
                                    echo $unassigned; ?>
                                </h5>
                            </a>
                        </div>

                        <div class="col-sm-4" style="padding: 0 10px;">
                            <a href="admin.php?q=2 & page=<?php echo base64_encode('../manage-reports'); ?>" class="card" style="background-color:#e83e8c; text-align: center; padding: 20px; margin-bottom: 30px; min-height: 200px;">
                                <i class="fa fa-clock mb-3" style="font-size: 60px; color:white;"></i>
                                <h4 style="color:white; font-size: 16px;">Recent Transactions</h4>
                                <h5 style="color:white; font-size: 18px;">
                                <?php  $sql = "SELECT COUNT(*) as count FROM transaction WHERE Date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
                                    $result = $mysqli->query($sql);
                                    $recent_transactions = 0;
                                    if ($result->num_rows > 0) {
                                        $row = $result->fetch_assoc();
                                        $recent_transactions = $row['count'];
                                    }
                                    echo $recent_transactions; ?>
                                </h5>
                            </a>
                        </div>

                        <div class="col-sm-4" style="padding: 0 10px;">
                            <a href="admin.php?q=2 & page=<?php echo base64_encode('../manage-reports'); ?>" class="card" style="background-color:#6f42c1; text-align: center; padding: 20px; margin-bottom: 30px; min-height: 200px;">
                                <i class="fa fa-trophy mb-3" style="font-size: 60px; color:white;"></i>
                                <h4 style="color:white; font-size: 16px;">Top Collector</h4>
                                <h5 style="color:white; font-size: 18px;">
                                <?php  $sql = "SELECT u.username, COALESCE(SUM(t.amount), 0) as total 
                                    FROM users u 
                                    LEFT JOIN transaction t ON u.user_id = t.user_id 
                                    WHERE u.role = 'collector' 
                                    GROUP BY u.user_id, u.username 
                                    ORDER BY total DESC 
                                    LIMIT 1";
                                    $result = $mysqli->query($sql);
                                    $top_collector = 'N/A';
                                    if ($result->num_rows > 0) {
                                        $row = $result->fetch_assoc();
                                        $top_collector = $row['username'];
                                    }
                                    echo $top_collector; ?>
                                </h5>
                            </a>
                        </div>
                    </div>
                </div>
                <?php } ?>
                
                <?php
                if (@$_GET['q'] == 2) {
                    if (isset($_REQUEST ["page"])) {
                        $page = base64_decode($_REQUEST ["page"]) . ".php";
                        if (file_exists($page)) {
                            // Define constant to indicate this is included from admin.php
                            if (strpos($page, 'admin_dashboard') !== false) {
                                define('INCLUDED_FROM_ADMIN', true);
                            }
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
<?php ob_end_flush(); ?>




