
<?php
//session_start();
include_once '../config/config.php';

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
     
 // here begins the login for assigning collector to contributor and notififying both
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['send_notification'])) {
        $message = $mysqli->real_escape_string($_POST['message']);
        $user_id = $_SESSION['user_id']; // Get the user ID from the session

        // Check if the user exists
        $userCheck = $mysqli->prepare("SELECT COUNT(*) FROM users WHERE user_id = ?");
        $userCheck->bind_param('i', $user_id);
        $userCheck->execute();
        $userCheck->bind_result($exists);
        $userCheck->fetch();
        $userCheck->close();

        if ($exists > 0) {
            // Fetch all contributors and collectors
            $stmt = $mysqli->prepare("SELECT user_id, username FROM users WHERE role IN ('contributor', 'collector')");
            $stmt->execute();
            $result = $stmt->get_result();

            // Insert notification for each user
            while ($row = $result->fetch_assoc()) {
                $recipient_user_id = $row['user_id'];
                $notificationStmt = $mysqli->prepare("INSERT INTO notification (user_id, username, message) VALUES (?, ?, ?)");
                $notificationStmt->bind_param('iis', $recipient_user_id, $message);
                $notificationStmt->execute();
                $notificationStmt->close();
            }

            $successMessage = 'Notification sent successfully to all users!';
            echo "<script>alert('Notification sent: $message');</script>";
        } else {
            $errorMessage = 'User does not exist.';
        }
    }
}
         
    // Handle deleting notifications
    if (isset($_POST['delete_notification'])) {
        $notificationId = intval($_POST['notification_id']);
        $stmt = $mysqli->prepare("DELETE FROM notification WHERE notification_id = ?");
        $stmt->bind_param('i', $notificationId);
        
        if ($stmt->execute()) {
            $successMessage = 'Notification deleted successfully!';
        } else {
            $errorMessage = 'Failed to delete notification.';
        }
        $stmt->close();
    }
    
// Fetch notifications
$notifications = $mysqli->query("SELECT * FROM notification ORDER BY created_at DESC");
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin_dashboard_css/styles.css">
    <script src="admin_dashboard_js/scripts.js" defer></script>
    <title>Admin Dashboard</title>

    <link rel="stylesheet" type="text/css" href="ubcss/bootstrap-3.0.0/dist/css/bootstrap.css">
        <script src="ubjs/script.js"></script>
        <script src="ubjs/jquery.js"></script>
        <script src="ubjs/ajaxWorks.js"></script>
        <script src="ubjs/bootstrap.min.js"></script>
        <script src="ubjs/holder.js"></script>
        <meta charset="UTF-8">

        <link rel="stylesheet" type="text/css" href="ubcss/bootstrap.css"> 
        <link rel="stylesheet" type="text/css" href="ubcss/admin.css"> 
        <meta charset="utf-8">
        

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

    <style>
/* css for generate report*/

    </style>
   
</head>
<body style="background-image: url('../images/money_background.jpg');">
    <div class="container">
        <h1>Hello Welcom To Dailycollect Admin Dashboard</h1>

        <?php if ($successMessage): ?>
            <div class="success-message"><?php echo $successMessage; ?></div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="error-message"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <!-- <h2>Send Notification</h2>
      
        <form method="POST">
            <textarea name="message" required placeholder="Enter your notification message"></textarea>
            <button type="submit" name="send_notification">Send Notification</button>
        </form> -->

        <!-- <h2>Generate Report</h2>
        <form method="POST" action="admin_db_greport.php">
            <label for="report_type">Select Report Type:</label>
            <select name="report_type" id="report_type" required>
                <option value="">--Select--</option>
                <option value="notification">Notifications</option>
                <option value="transaction">Transactions</option>
                <option value="users">Users</option>
            </select>
            <button type="submit" name="generate_report">Generate Report</button>
        </form> -->

    <!-- <div id="notification" class="notification" style="display: none;"></div>
     -->
    <!-- <div class="button-container">
            <button class="button" onclick="showList('collectors')">View Collectors List</button>
            <button class="button" onclick="showList('contributors')">View Contributors List</button>
        </div> -->

        <!-- <button id="closeButton" class="button" onclick="closeList()">Close</button>
        <div id="listContainer"></div> -->

    <a href="admin_assign_collect_con.php"><button id="assignButton">Assign Collector to Contributor</button></a>

    <a href="admin_view_collect.html"><button id="assignButton">View Collectors and Contributors Location</button></a>

    <h2>Notifications</h2>
<ul>
    <?php while ($notification = $notifications->fetch_assoc()): ?>
    <li>
        <?php echo $notification['message']; ?> (<?php echo $notification['created_at']; ?>)
        <form method="POST" style="display:inline;">
            <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
            <button type="submit" name="delete_notification" class="delete_noti">Delete</button>
        </form>
    </li>
    <?php endwhile; ?>     
</ul>


</div>

</body>
</html>