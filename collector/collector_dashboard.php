<?php
include_once '../config/config.php';
// Check user role
if ($_SESSION['role'] != 'collector') {
    header('Location: .../login.php'); // redirect to logged in if role not valid
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: .../login.php'); // Redirect to login if not logged in
    exit();
}

// Assuming user ID is stored in session after login
$user_id = $_SESSION['user_id'];

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

// Handle notification reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_notification'])) {
    $reply_message = trim($_POST['reply_message']);
    $notification_id = intval($_POST['notification_id']);
    
    if (!empty($reply_message)) {
        // Prepare and execute insert statement for reply
        $insertStmt = $mysqli->prepare("INSERT INTO replies (notification_id, user_id, message) VALUES (?, ?, ?)");
        $insertStmt->bind_param('iis', $notification_id, $user_id, $reply_message);
        
        if ($insertStmt->execute()) {
            // Get the original notification's user ID to notify them
            $userStmt = $mysqli->prepare("SELECT user_id FROM notification WHERE notification_id = ?");
            $userStmt->bind_param('i', $notification_id);
            $userStmt->execute();
            $userStmt->bind_result($recipient_id);
            $userStmt->fetch();
            $userStmt->close();

            // Insert a new notification for the recipient
            $notifyStmt = $mysqli->prepare("INSERT INTO notification (user_id, message) VALUES (?, ?)");
            $notify_message = "Reply from collector: " . $reply_message;
            $notifyStmt->bind_param('is', $recipient_id, $notify_message);
            $notifyStmt->execute();
            $notifyStmt->close();

            $successMessage = 'Reply sent successfully!';
        } else {
            $errorMessage = 'Failed to send reply.';
        }
        
        $insertStmt->close();
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Collector Dashboard</title>
    <link rel="stylesheet" href="collector_dashboard_css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            height: auto;
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
            padding: 20px;
            position: relative;
            font-size: 18px;
        }
        .delete-button, .reply-button {
            position: absolute;
            top: 10px;
            background: red;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }
        .reply-button {
            right: 80px;
            background: green;
        }
        .reply-form {
            margin-top: 10px;
            display: none;
        }
        .reply-input {
            width: calc(100% - 120px);
            padding: 5px;
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
    <script>
        function confirmDeletion(event) {
            if (!confirm("Are you sure you want to delete this notification?")) {
                event.preventDefault(); // Prevent form submission if the user cancels
            }
        }

        function toggleReplyForm(notificationId) {
            const form = document.getElementById('reply-form-' + notificationId);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</head>
<body>

<div class="container">
    <h1>Welcome to Collector DailyCollect Dashboard</h1>

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
                    <button class="reply-button" onclick="toggleReplyForm(<?php echo $notification['id']; ?>)">Reply</button>
                    
                    <form id="reply-form-<?php echo $notification['id']; ?>" class="reply-form" method="POST">
                        <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                        <input type="text" name="reply_message" class="reply-input" placeholder="Type your reply..." required>
                        
                        <button type="submit" name="reply_notification" class="reply-button">Send</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No new notifications.</p>
    <?php endif; ?>

    <!-- Display success or error message -->
    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
    <?php elseif (isset($errorMessage)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage); ?></div>
    <?php endif; ?>
</div>
</body>
</html>