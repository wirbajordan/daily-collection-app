<?php
session_start();
include_once '../config/config.php';

// Initialize variables messages
$successMessage = '';
$errorMessage = '';

// Check user role
if ($_SESSION['role'] != 'administrator') {
    header('Location: ../forms_logic/login_register.php' );//redirect to logged in if role not valide
    exit();
}

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
            $stmt = $mysqli->prepare("SELECT user_id FROM users WHERE role IN ('contributor', 'collector')");
            $stmt->execute();
            $result = $stmt->get_result();

            // Insert notification for each user
            while ($row = $result->fetch_assoc()) {
                $recipient_user_id = $row['user_id'];
                $notificationStmt = $mysqli->prepare("INSERT INTO notification (user_id, message) VALUES (?, ?)");
                $notificationStmt->bind_param('is', $recipient_user_id, $message);
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
        // Logic to send notification (e.g., email)
       // $subject = "Admin Notification";
        //$headers = "From: admin@example.com"; // Change to your admin email

        // For demonstration purposes, just echo a success message

        // Send email to all contributors
        //$contributorEmails = $mysqli->query("SELECT email FROM contributor");
        // while ($row = $contributorEmails->fetch_assoc()) {
           // mail($row['email'], $subject, $message, $headers);
         //}
         
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
    // Add more functionalities here (like generating reports, etc.)


// Fetch notifications
$notifications = $mysqli->query("SELECT * FROM notification ORDER BY created_at DESC");
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../admin_dashboard_css/styles.css">
    <script src="../admin_dashboard_js/scripts.js" defer></script>
    <title>Admin Dashboard</title>
    <style>
    
/* css for generate report*/
form {
    margin: 20px 0;
}

label {
    margin-right: 10px;
}

select, button {
    padding: 10px;
    margin: 5px;
}
    
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

        <h2>Send Notification</h2>
      
        <form method="POST">
            <textarea name="message" required placeholder="Enter your notification message"></textarea>
            <button type="submit" name="send_notification">Send Notification</button>
        </form>

        <h2>Generate Report</h2>
        <form method="POST" action="admin_db_greport.php">
            <label for="report_type">Select Report Type:</label>
            <select name="report_type" id="report_type" required>
                <option value="">--Select--</option>
                <option value="notification">Notifications</option>
                <option value="transaction">Transactions</option>
                <option value="users">Users</option>
            </select>
            <button type="submit" name="generate_report">Generate Report</button>
        </form>

    <div id="notification" class="notification" style="display: none;"></div>
    
    <div class="button-container">
            <button class="button" onclick="showList('collectors')">View Collectors List</button>
            <button class="button" onclick="showList('contributors')">View Contributors List</button>
        </div>

        <button id="closeButton" class="button" onclick="closeList()">Close</button>
        <div id="listContainer"></div>

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