<?php
include_once '../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: ../login.php');
    exit();
}


// Check user role
if ($_SESSION['role'] != 'contributor') {
    header('Location: ../login.php');
    exit();
}

// Assuming user ID is stored in session after login
$user_id = $_SESSION['user_id'];

// Initialize variables
$userName = '';
$totalSum = 0;
$transactions = [];
$notifications = [];

// Fetch user details
$stmt = $mysqli->prepare("SELECT username FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($userName);
$stmt->fetch();
$stmt->close();

// Fetch total balance
$stmt = $mysqli->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transaction WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($totalSum);
$stmt->fetch();
$stmt->close();

// Fetch transactions with more details
$stmt = $mysqli->prepare("SELECT t.amount, t.transaction_type, t.Date, t.username, t.transaction_id 
                         FROM transaction t 
                         WHERE t.user_id = ? 
                         ORDER BY t.Date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}
$stmt->close();

// Fetch notifications for the logged-in user
$stmt = $mysqli->prepare("SELECT notification_id, message, created_at FROM notification WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
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

// After session and role checks