<h2 class="text-center"><span class="nam">Notifications</span></h2>

<?php
include_once '../config/config.php';

// Initialize variables messages
$successMessage = '';
$errorMessage = '';

// Check user role
if ($_SESSION['role'] != 'administrator') {
    header('Location: login.php');//redirect to logged in if role not valide
    exit();
}


// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}      


// Assuming user ID is stored in session after login
$user_id = $_SESSION['user_id'];
     

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
 
<form method="POST">  
    <textarea name="message" required placeholder="Enter your notification message"></textarea>
  <button type="submit" name="send_notification">Send Notification</button>
</form>

<div id="notification" class="notification" style="display: none;"></div>

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
    