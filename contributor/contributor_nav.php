<?php
// contributor_nav.php
if (session_status() === PHP_SESSION_NONE) session_start();
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
?>
<div class="header" style="background-color: ">
    <div class="container-fluid">
        <div class="col-lg-12">
            <span class="logo"><span style="margin-left: ;">Vision Finance</span></span>
            <?php
            echo '<span class="pull-right top title1" style="margin-left:40px;"><span style="color:white"><span class="glyphicon glyphicon-user" aria-hidden="true"></span>&nbsp;&nbsp;&nbsp;&nbsp;Hello,</span> <span class="log log1" style="color:lightyellow">' . htmlspecialchars($username) . '&nbsp;&nbsp;|&nbsp;&nbsp;'
            . '<a href="../home.php"  style="color:lightyellow"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span>&nbsp;Logout</a></span>';
            ?>
        </div>
        <nav id="navbar" class="navbar">
            <img src="../images/vision-finance-logo.png" class="dailycollect" width="65" height="65" alt="Vision Finance" style="margin-left:-13%;"> 
            <ul>                        
                <li><a href="contributor_dashboard.php" style="color: white;">Home<span class="sr-only">(current)</span></a></li>
                <li><a href="contributor_deposite.php" style="color: white;">Deposit Contribution<span class="sr-only">(current)</span></a></li>
                <li><a href="contributor_dashboard.php" style="color: white;">Consult Notifications<span class="sr-only">(current)</span></a></li>
                <li><a href="call_customer_support.php" style="color: white;">Call Customer Support<span class="sr-only">(current)</span></a></li>
                <li><a href="rate_collector.php" style="color: white;">Rate Collector</a></li>
            </ul>
        </nav>
    </div>
</div> 