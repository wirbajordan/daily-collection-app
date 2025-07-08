<?php
// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start output buffering to prevent accidental output before JSON
ob_start();

include_once '../config/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$successMessage = '';
$errorMessage = '';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'administrator') {
    header('Location: ../login.php');
    exit();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['send_notification'])) {
        $message = trim(strtolower($mysqli->real_escape_string($_POST['message'])));
        $recipient = $_POST['recipient'] ?? 'all';
        $user_id = $_SESSION['user_id'];
        $roleFilter = '';
        if ($recipient === 'contributors') $roleFilter = "AND role = 'contributor'";
        if ($recipient === 'collectors') $roleFilter = "AND role = 'collector'";
        $userCheck = $mysqli->prepare("SELECT COUNT(*) FROM users WHERE user_id = ?");
        $userCheck->bind_param('i', $user_id);
        $userCheck->execute();
        $userCheck->bind_result($exists);
        $userCheck->fetch();
        $userCheck->close();
        if ($exists > 0) {
            $stmt = $mysqli->prepare("SELECT user_id FROM users WHERE role IN ('contributor', 'collector') $roleFilter");
            $stmt->execute();
            $result = $stmt->get_result();
            $sent = 0;
            while ($row = $result->fetch_assoc()) {
                $recipient_user_id = $row['user_id'];
                $checkStmt = $mysqli->prepare("SELECT COUNT(*) FROM notification WHERE user_id = ? AND TRIM(LOWER(message)) = ? AND created_at >= (NOW() - INTERVAL 1 DAY)");
                $checkStmt->bind_param('is', $recipient_user_id, $message);
                $checkStmt->execute();
                $checkStmt->bind_result($exists);
                $checkStmt->fetch();
                $checkStmt->close();
                if ($exists == 0) {
                    $notificationStmt = $mysqli->prepare("INSERT INTO notification (user_id, message) VALUES (?, ?)");
                    $notificationStmt->bind_param('is', $recipient_user_id, $_POST['message']);
                    $notificationStmt->execute();
                    $notificationStmt->close();
                    $sent++;
                }
            }
            $response = ['status' => 'success', 'message' => 'Notification sent successfully!'];
        } else {
            $response = ['status' => 'error', 'message' => 'User does not exist.'];
        }
        header('Content-Type: application/json');
        // Clean (discard) any previous output before sending JSON
        ob_clean();
        echo json_encode($response);
        // Flush output buffer and end buffering
        ob_end_flush();
        exit();
    }
    if (isset($_POST['delete_notification'])) {
        $notificationId = intval($_POST['notification_id']);
        $stmt = $mysqli->prepare("DELETE FROM notification WHERE notification_id = ?");
        $stmt->bind_param('i', $notificationId);
        if ($stmt->execute()) {
            $_SESSION['successMessage'] = 'Notification deleted successfully!';
        } else {
            $_SESSION['errorMessage'] = 'Failed to delete notification.';
        }
        $stmt->close();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}
if (isset($_SESSION['successMessage'])) {
    $successMessage = $_SESSION['successMessage'];
    unset($_SESSION['successMessage']);
}
if (isset($_SESSION['errorMessage'])) {
    $errorMessage = $_SESSION['errorMessage'];
    unset($_SESSION['errorMessage']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Notifications</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6fa; }
        .container-main { max-width: 900px; margin: 40px auto; padding: 32px 20px; background: #fff; border-radius: 1.2rem; box-shadow: 0 2px 16px rgba(0,0,0,0.08); }
        .notification-form textarea { min-height: 100px; font-size: 1.1em; }
        .notification-form .form-select, .notification-form textarea { margin-bottom: 1rem; }
        .delete-btn { color: #dc3545; border: none; background: none; }
        .delete-btn:hover { text-decoration: underline; }
        .modal-confirm .modal-content { border-radius: 1rem; }
    </style>
</head>
<body>
<div class="container-main">
    <h2 class="mb-4 text-center"><i class="fas fa-bell"></i> Admin Notifications</h2>
    <div id="notificationAlert">
    <?php if ($successMessage): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?= $successMessage ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?= $errorMessage ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    </div>
    <div class="card mb-4 notification-form">
        <div class="card-header bg-primary text-white"><i class="fas fa-paper-plane"></i> Send Notification</div>
        <div class="card-body">
            <form method="POST" autocomplete="off" id="notificationForm" action="../notification.php">
                <div class="row g-2 align-items-center mb-2">
                    <div class="col-md-4">
                        <select name="recipient" class="form-select" required>
                            <option value="all">All (Contributors & Collectors)</option>
                            <option value="contributors">Contributors Only</option>
                            <option value="collectors">Collectors Only</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <textarea name="message" class="form-control" required placeholder="Enter your notification message..."></textarea>
                    </div>
                </div>
                <div class="text-end">
                    <button type="submit" name="send_notification" class="btn btn-primary px-4"><i class="fas fa-paper-plane"></i> Send</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function(){
    $('#notificationForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var formData = form.serialize();
        $.ajax({
            url: '../notification.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                console.log(response); // Debugging
                var alertBox = '';
                if (response.status === 'success') {
                    alertBox = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                        '<i class="fas fa-check-circle"></i> ' + response.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>';
                    form[0].reset();
                } else {
                    alertBox = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                        '<i class="fas fa-exclamation-triangle"></i> ' + response.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>';
                }
                $('#notificationAlert').html(alertBox);
            },
            error: function(xhr, status, error) {
                console.log('AJAX error:', status, error);
                var alertBox = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                    '<i class="fas fa-exclamation-triangle"></i> An error occurred.' +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                    '</div>';
                $('#notificationAlert').html(alertBox);
            }
        });
    });
});
</script>
</body>
</html>
    