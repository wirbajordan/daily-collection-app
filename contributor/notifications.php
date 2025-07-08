<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include ('../config/config.php');

// Check user role
if ($_SESSION['role'] != 'contributor') {
    header('Location: ../login.php');
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$notifications = [];
$transactions = [];

// Fetch notifications for the logged-in user
$stmt = $mysqli->prepare("SELECT notification_id, message, created_at FROM notification WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt->close();

// Fetch contributions (transactions) for the logged-in user
$stmt = $mysqli->prepare("SELECT amount, transaction_type, Date, transaction_id FROM transaction WHERE user_id = ? ORDER BY Date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}
$stmt->close();
?>

<div class="container" style="margin-top: 30px;">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading" style="background: #495057; color: white;">
                    <h3 class="panel-title"><span class="glyphicon glyphicon-bell"></span> Notifications</h3>
                </div>
                <div class="panel-body">
                    <?php if (empty($notifications)): ?>
                        <div class="alert alert-info">No notifications found.</div>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($notifications as $notification): ?>
                                <li class="list-group-item">
                                    <div style="font-size: 15px; color: #333;"><b><?php echo htmlspecialchars($notification['message']); ?></b></div>
                                    <div style="font-size: 12px; color: #888;">Received: <?php echo htmlspecialchars($notification['created_at']); ?></div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            <div class="panel panel-default" style="margin-top: 30px;">
                <div class="panel-heading" style="background: #495057; color: white;">
                    <h3 class="panel-title"><span class="glyphicon glyphicon-list-alt"></span> Your Contributions</h3>
                </div>
                <div class="panel-body">
                    <?php if (empty($transactions)): ?>
                        <div class="alert alert-info">No contributions found.</div>
                    <?php else: ?>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Transaction Type</th>
                                    <th>Transaction ID</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $txn): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($txn['Date']); ?></td>
                                        <td><?php echo htmlspecialchars($txn['amount']); ?></td>
                                        <td><?php echo htmlspecialchars($txn['transaction_type']); ?></td>
                                        <td><?php echo htmlspecialchars($txn['transaction_id']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>