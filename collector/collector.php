<?php
session_start();
include ('../config/config.php');
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
        <title>DC Daily Collection</title>

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
                height:110px;
            }
            .logo{
                color: white;
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

            body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #4CAF50;
        }

        /* Button Styles */
        button {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 12px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 10px 0;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #45a049;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        /* Form Styles */
        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="number"],
        input[type="password"],
        select {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        input[type="number"]:focus,
        input[type="password"]:focus,
        select:focus {
            border-color: #4CAF50;
            outline: none;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.5);
        }

        /* Message Styles */
        #message {
            margin-top: 15px;
            font-weight: bold;
            text-align: center;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }
        </style>


    </head>
    <body style="color:black; background-color: #eee;">

        <div class="header" style="background-color: black">
            <div class="container-fluid">
                <div class="col-lg-12">
                    <span class="logo"><span style="margin-left:6%;">Daily Collect</span></span>
                    <?php
                    if ((!($_SESSION ["password"]))) {
                        session_destroy();
                        header("location:login.php");
                    } else {
                        $email = $_SESSION['email'];
                        $username = $_SESSION['username'];
                        $password = $_SESSION['password'];

                        include_once ('../config/config.php');
                        echo '<span class="pull-right top title1" style="margin-left:40px;"><span style="color:white"><span class="glyphicon glyphicon-user" aria-hidden="true"></span>&nbsp;&nbsp;&nbsp;&nbsp;Hello,</span> <span class="log log1" style="color:lightyellow">' . $username . '&nbsp;&nbsp;|&nbsp;&nbsp;'
                        . '<a href="javascript:deconnexion()" style="color:lightyellow"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span>&nbsp;Logout</button></a></span>';
                    }
                    $result = mysqli_query($mysqli, "SELECT * FROM users WHERE username='$username'") or die('Error');
                    ($row = mysqli_fetch_array($result));
                    //$user_id = $row['user_id'];
                    //$branch = $row['branch'];

                  
                    ?>

                </div>
                <!-- navbar -->
                <nav id="navbar" class="navbar">
                    <img src="../assets/img/dailycollect.png"  width="65" height="65" alt="DC Daily Collection" style="margin-left:-13%;"> 
                    <ul>                        
                        <li <?php if (@$_GET['q'] == 1) echo 'class="active"'; ?>><a href="collector.php?q=1" style="color: white;" >Home<span class="sr-only" >(current)</span></a></li>
                        <li <?php if (@$_GET['q'] == 2) echo 'class="active"'; ?>><a href="collector.php?q=2 & page=<?php echo base64_encode('collector_register_contribution'); ?>" style="color: white;">Register Contribution<span class="sr-only">(current)</span></a></li>
                        <li <?php if (@$_GET['q'] == 2) echo 'class="active"'; ?>><a href="collector.php?q=2 & page=<?php echo base64_encode('collector_dashboard'); ?>" style="color: white;">consult notifications<span class="sr-only">(current)</span></a></li>
                        <li <?php if (@$_GET['q'] == 2) echo 'class="active"'; ?>><a href="collector.php?q=2 & page=<?php echo base64_encode('collector_view_registered_contribution'); ?>&branch=<?php echo'' . $branch . ''?>" style="color: white;">View_registered_contribution    <span class="sr-only">(current)</span></a></li>
                        <li <?php if (@$_GET['q'] == 2) echo 'class="active"'; ?>><a href="collector.php?q=2" style="color: white;">show Qrcode<span class="sr-only">(current)</span></a></li>                         
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
                                    include ('collector.php');
                                }
                            }
                            ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>






