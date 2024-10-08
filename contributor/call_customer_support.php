<?php
//session_start(); // Start the session to use session variables

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "dailycollect";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle support request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $userId = $_POST['user_id'];
    $message = $_POST['message'];

    // Get assigned collector ID from assignments table
    $stmt = $conn->prepare("SELECT collector_id FROM assignments WHERE contributor_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($collectorId);
    $stmt->fetch();
    $stmt->close();

    // Store notification in notifications table
    $stmt = $conn->prepare("INSERT INTO notification (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $collectorId, $message);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Support request submitted successfully!";
    } else {
        $_SESSION['message'] = "Error submitting support request.";
    }
    $stmt->close();
}

// Retrieve notifications for the collector
$collectorId = 1; // Example collector ID; this should be dynamically set based on logged-in user
$notifications = [];
$stmt = $conn->prepare("SELECT notification_id, message, created_at FROM notification WHERE user_id = ? ");
$stmt->bind_param("i", $collectorId);
$stmt->execute();
$stmt->bind_result($id, $message, $createdAt);

while ($stmt->fetch()) {
    $notifications[] = ['notification_id' => $id, 'message' => $message, 'created_at' => $createdAt];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Call Customer Support</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: auto; padding: 20px; }
        textarea { width: 100%; margin: 10px 0; padding: 10px; font-size: 20px }
        button { padding: 10px; background-color: #28a745; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #218838; }
        .notifications { margin-top: 20px; }
        .notification { border: 1px solid #ccc; padding: 10px; margin-bottom: 10px; }
        .notification button { background-color: #007bff; }
        .notification button:hover { background-color: #0056b3; }
        .alert { padding: 10px; margin-bottom: 20px; border: 1px solid; }
        .alert.success { border-color: #28a745; color: #28a745; font-size: 20px;}
        .alert.error { border-color: #dc3545; color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Call Customer Support</h2>
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert <?php echo strpos($_SESSION['message'], 'Error') !== false ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($_SESSION['message']); ?>
                <?php unset($_SESSION['message']); // Clear message after displaying ?>
            </div>
        <?php endif; ?>
        
        <form id="supportForm" method="POST">
            <textarea name="message" rows="5" placeholder="Describe your issue..." required></textarea>
            <input type="hidden" name="user_id" value="58"> <!-- Replace with actual user ID -->
            <button type="submit">Send Support Request</button>
        </form>

        <div class="notifications">
            <h3>Notifications</h3>
            <?php if (empty($notifications)) : ?>
                <p>No new notifications.</p>
            <?php else : ?>
                <?php foreach ($notifications as $note) : ?>
                    <div class="notification">
                        <p><strong><?php echo htmlspecialchars($note['message']); ?></strong></p>
                        <p><em><?php echo htmlspecialchars($note['created_at']); ?></em></p>
                        <button onclick="markAsRead(<?php echo $note['notification_id']; ?>)">Mark as Read</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function markAsRead(notificationId) {
            fetch('mark_as_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: notificationId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Notification marked as read.');
                    location.reload(); // Reload the page to update notifications
                } else {
                    alert('Error marking notification as read.');
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>