<?php
session_start();
include ('../config/config.php');


// Check user role
if ($_SESSION['role'] != 'contributor') {
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
        <script src="../ubjs/jquery.js"></script>
        <script src="../ubjs/ajaxWorks.js"></script>
        <script src="../ubjs/bootstrap.min.js"></script>
        <script src="../ubjs/holder.js"></script>
        <meta charset="UTF-8">

        <link rel="stylesheet" type="text/css" href="../ubcss/bootstrap.css"> 
        <link rel="stylesheet" type="text/css" href="../ubcss/admin.css"> 
        <meta charset="utf-8">
        <meta content="width=device-width, initial-scale=1.0" name="viewport">
        <title>Daily Collection</title>

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
        <link rel="stylesheet" href="contributor_dashboard_css/style.css">

         


                <!-- Template Main CSS File -->
       <link href="../assets/css/main.css" rel="stylesheet">


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
                height:202px;
            }

            .logo{
                color: white;
                font-size: 30px;
            }
            .panel{
                border-color:#eee;
                margin:40px;
                padding:20px;
                font: 15px "Century Gothic", "Times Roman", sans-serif;
            }
            .start{
                display: inline-block;
                color: #666;
                background: #f4f4f4;
                border: 1px dotted #ccc;
                padding: 6px 13px;
            }
            .current{
                display: inline-block;
                color: #666;
                background: #f4f4f4;
                border: 1px dotted #ccc;
                padding: 6px 13px;
            }  

            .dailycollect {
                width: 10%;
                height: 10%;
            }

            .coins {
              
            }

        </style>


    </head>
    
    <body style="color:black; background-color: #eee;">

    <img src="../images/vision-finance-logo.png" class="coins" alt="Vision Finance Logo" style="height: 65px; width: auto; margin-top: 10px;"> 
                   

        <div class="header" style="background-color: ">
            <div class="container-fluid">
                <div class="col-lg-12">
                    <span class="logo"><span style="margin-left: ;">Vision Finance</span></span>
                    <?php
                    if ((!($_SESSION ["password"]))) {
                        session_destroy();
                        header("location:.../login.php");
                    } else {
                        $email = $_SESSION['email'];
                        $username = $_SESSION['username'];
                        $password = $_SESSION['password'];

                        include_once ('../config/config.php');

                 
                        echo '<span class="pull-right top title1" style="margin-left:40px;"><span style="color:white"><span class="glyphicon glyphicon-user" aria-hidden="true"></span>&nbsp;&nbsp;&nbsp;&nbsp;Hello,</span> <span class="log log1" style="color:lightyellow">' . $username . '&nbsp;&nbsp;|&nbsp;&nbsp;'
                        . '<a href="../home.php"  style="color:lightyellow"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span>&nbsp;Logout</button></a></span>';
                    }
                    $result = mysqli_query($mysqli, "SELECT * FROM users WHERE username='$username'") or die('Error');
                    ($row = mysqli_fetch_array($result));
                    //$user_id = $row['user_id'];
                    //$branch = $row['branch'];

                  
                    ?>
l
                </div>
      
                <!-- navbar -->
                <nav id="navbar" class="navbar">
                    <img src="../images/vision-finance-logo.png" class="dailycollect" width="65" height="65" alt="Vision Finance" style="margin-left:-13%;"> 
                    <ul>                        
                        <li <?php if (@$_GET['q'] == 1) echo 'class="active"'; ?>><a href="contributor_dashboard.php?q=1" style="color: white;" >Home<span class="sr-only" >(current)</span></a></li>
                        <li <?php if (@$_GET['q'] == 2) echo 'class="active"'; ?>><a href="contributor.php?q=2 & page=<?php echo base64_encode('contributor_deposite'); ?>" style="color: white;">Deposit Contribution<span class="sr-only">(current)</span></a></li>
                        
                        <li <?php if (@$_GET['q'] == 2) echo 'class="active"'; ?>><a href="contributor.php?q=2 & page=<?php echo base64_encode('contributor_dashboard'); ?>" style="color: white;">Consult notifications<span class="sr-only">(current)</span></a></li>
                        <li <?php if (@$_GET['q'] == 2) echo 'class="active"'; ?>><a href="contributor.php?q=2 & page=<?php echo base64_encode('call_customer_support'); ?>" style="color: white;">Call customer support<span class="sr-only">(current)</span></a></li>
                        
                        <li <?php if (@$_GET['q'] == 2) echo 'class="active"'; ?>><a href="contributor.php?q=2 & page=<?php echo base64_encode(''); ?>&branch=<?php echo'' . $branch . '' ?>&uid=<?php echo'' . $user_id . '' ?>" style="color: white;">   <span class="sr-only">(current)</span></a></li>
                        <li <?php if (@$_GET['q'] == 2) echo 'class="active"'; ?>><a href="contributor.php?q=2" style="color: white;"><span class="sr-only">(current)</span></a></li>                         
                    </ul>
                </nav>

                <!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
                <?php $user_id = ['user_id']; ?>
                <div class="container">
                    <div class="row">
                        <div class=" col-md-12">

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
                                    include ('contributor.php'); 
                                }
                            }
                            ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>

    </body>
</html>






