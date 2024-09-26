<?php
session_start();
include_once '../config/config.php';


// Check user role
if ($_SESSION['role'] != 'contributor') {
    header('Location: ../forms_logic/login_register.php' );
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../forms_logic/login_register.php'); // Redirect to login if not logged in
    exit();
}

// Assuming user ID is stored in session after login
$user_id = $_SESSION['user_id'];

// Check user role (ALLOW both collector and contributor)
//if (!in_array($_SESSION['role'], ['collector', 'contributor'])) {
    //header('Location: ../forms_logic/login_register.php'); 
  //  exit();
//}

$notifications = [];

// Fetch notifications for the logged-in user
$stmt = $mysqli->prepare("SELECT notification_id, message, created_at FROM notification WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($notification_id, $message, $created_at);

while ($stmt->fetch()) {
    $notifications[] = ['id' => $notification_id, 'message' => $message, 'created_at' => $created_at];
}
$stmt->close();

// Handle notification deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_notification'])) {
    $notification_id = intval($_POST['notification_id']);
    
    // Prepare and execute delete statement
    $deleteStmt = $mysqli->prepare("DELETE FROM notification WHERE notification_id = ? AND user_id = ?");
    $deleteStmt->bind_param('ii', $notification_id, $user_id);
    
    if ($deleteStmt->execute()) {
        $successMessage = 'Notification deleted successfully!';
    } else {
        $errorMessage = 'Failed to delete notification.';
    }
    
    $deleteStmt->close();
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome To The Collector Dashboard DailyCollect</title>
    <link rel="stylesheet" href="../contributor_dashboard_css/style.css">
    <style>
       /*this is the ccs for notifications, and the delete notification button*/  
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 20px;
}


.container {
    max-width: 1200px;
    height: 600px;
    margin: auto;
    background: white;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}
.notification {
    background-color: #e7f3fe;
    border-left: 6px solid #2196F3;
    margin: 10px 0;
    padding: 10px;
    position: relative; /* For positioning the delete button */
}
.delete-button {
    position: absolute;
    top: 10px;
    right: 10px;
    background: red;
    color: white;
    border: none;
    padding: 5px 10px;
    cursor: pointer;
}
.notification-button {
    position: relative;
    display: inline-block;
    cursor: pointer;
}
.notification-count {
    position: absolute;
    top: -5px;
    right: -10px;
    background: red;
    color: white;
    border-radius: 50%;
    padding: 5px;
    font-size: 12px;
}

</style>
   <!-- here is the js for the delete notification button -->
    <script> 
        function confirmDeletion(event) {
            if (!confirm("Are you sure you want to delete this notification?")) {
                event.preventDefault(); // Prevent form submission if the user cancels
            }
        }
    </script><!-- end of delete notification js -->

</head>
<body>
    <div class="container">
            <h1>Hello Welcome TO Dailycollect Contributor Dashboard</h1>

            <div class="notification-button">
                Notifications
                <?php if (count($notifications) > 0): ?>
                    <span class="notification-count"><?php echo count($notifications); ?></span>
                <?php endif; ?>
            </div>

            <!-- Display notifications -->
            <?php if (!empty($notifications)): ?>
                <h2>Your Notifications</h2>
                <div class="notifications">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification">
                            <?php echo htmlspecialchars($notification['message']); ?> 
                            <span class="date"><?php echo $notification['created_at']; ?></span>
                            <form method="POST" style="display:inline;" onsubmit="confirmDeletion(event);">
                                <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                <button type="submit" name="delete_notification" class="delete-button">Delete</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No new notifications.</p>
            <?php endif; ?>

        <!-- Here begins the deposite contribution button and it JS -->
            <a href="contributor_deposite.php" class="deposit-button">
            <button>Please Deposit Your Contribution Here</button>
            </a>
    
      <script>
            // Optional JavaScript for additional functionality
            document.querySelector('.deposit-button button').onclick = function() {
                alert('Redirecting to deposit contribution page...');
            };
      </script><!-- the end of deposite contribution button and it JS -->


        <!-- From here begins the button fro tranaction and its JS -->
            <a href="contributor_deposite.php" class="deposit-button" id="depositLink">
                <button>View Your Transactions and Account Balance Here</button>
            </a>

        <script>
            document.getElementById('depositLink').onclick = function(event) {
                // Optionally, prevent default behavior if you want to perform an action before navigation
                // event.preventDefault();

                // Example: Show a confirmation dialog
                const confirmation = confirm("Do you want to view your transactions and account balance?");
                if (confirmation) {
                    // If the user confirms, allow the navigation
                    window.location.href = this.href; // Navigate to the link's href
                } else {
                    // If the user cancels, do nothing (navigation is prevented)
                    event.preventDefault();
                }
            };
        </script><!--the end of transaction button and it JS -->

    </div>
</body>
</html>