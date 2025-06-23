<?php
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
        $message = $mysqli->real_escape_string($_POST['message']);
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
            while ($row = $result->fetch_assoc()) {
                $recipient_user_id = $row['user_id'];
                $notificationStmt = $mysqli->prepare("INSERT INTO notification (user_id, message) VALUES (?, ?)");
                $notificationStmt->bind_param('is', $recipient_user_id, $message);
                $notificationStmt->execute();
                $notificationStmt->close();
            }
            $_SESSION['successMessage'] = 'Notification sent successfully!';
        } else {
            $_SESSION['errorMessage'] = 'User does not exist.';
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
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
$notifications = $mysqli->query("SELECT * FROM notification ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6fa; }
        .container-main { max-width: 900px; margin: 40px auto; padding: 32px 20px; background: #fff; border-radius: 1.2rem; box-shadow: 0 2px 16px rgba(0,0,0,0.08); }
        .notification-form textarea { min-height: 100px; font-size: 1.1em; }
        .notification-form .form-select, .notification-form textarea { margin-bottom: 1rem; }
        .notification-table { max-height: 350px; overflow-y: auto; }
        .table thead th { position: sticky; top: 0; background: #f8f9fa; }
        .search-bar { max-width: 350px; margin-bottom: 1.2rem; }
        .delete-btn { color: #dc3545; border: none; background: none; }
        .delete-btn:hover { text-decoration: underline; }
        .modal-confirm .modal-content { border-radius: 1rem; }
    </style>
</head>
<body>
<div class="container-main">
    <h2 class="mb-4 text-center"><i class="fas fa-bell"></i> Admin Notifications</h2>
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
    <div class="card mb-4 notification-form">
        <div class="card-header bg-primary text-white"><i class="fas fa-paper-plane"></i> Send Notification</div>
        <div class="card-body">
            <form method="POST" autocomplete="off">
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
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list"></i> Recent Notifications</h5>
        <input class="form-control search-bar" id="searchNotifications" type="text" placeholder="Search notifications...">
    </div>
    <div class="notification-table table-responsive">
        <table class="table table-bordered table-hover align-middle" id="notificationsTable">
            <thead class="table-light">
                <tr>
                    <th>Message</th>
                    <th>Recipient User ID</th>
                    <th>Sent At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($notification = $notifications->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($notification['message']) ?></td>
                    <td><?= htmlspecialchars($notification['user_id']) ?></td>
                    <td><?= htmlspecialchars($notification['created_at']) ?></td>
                    <td>
                        <button class="delete-btn" data-id="<?= $notification['notification_id'] ?>"><i class="fas fa-trash"></i> Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>     
            </tbody>
        </table>
    </div>
    <!-- Modal -->
    <div class="modal fade modal-confirm" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Are you sure you want to delete this notification?
          </div>
          <div class="modal-footer">
            <form method="POST" id="deleteForm">
                <input type="hidden" name="notification_id" id="modalNotificationId">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="delete_notification" class="btn btn-danger">Delete</button>
            </form>
          </div>
        </div>
      </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Search/filter notifications
    $(document).ready(function(){
        $('#searchNotifications').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#notificationsTable tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
        // Modal delete
        $('.delete-btn').on('click', function(){
            var id = $(this).data('id');
            $('#modalNotificationId').val(id);
            var myModal = new bootstrap.Modal(document.getElementById('deleteModal'), {});
            myModal.show();
        });
    });
</script>
</body>
</html>
    