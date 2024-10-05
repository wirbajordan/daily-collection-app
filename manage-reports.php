<h2 class="text-center"><span>Manage Reports</span></h2>
<?php
//session_start();
include_once '../config/config.php';

// Check user role
if ($_SESSION['role'] != 'administrator') {
    header('Location: .../login.php'); // Redirect to logged in if role not valid
    exit();
}

// Check if user is logged in    
if (!isset($_SESSION['user_id'])) {
    header('Location: .../login.php'); // Redirect to login if not logged in
    exit();
}    

// Handle report generation
$data = [];
$reportType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_report'])) {
    $reportType = $_POST['report_type'];

    if ($reportType === 'notification') {
        $result = $mysqli->query("SELECT * FROM notification ORDER BY created_at DESC");
        $data = $result->fetch_all(MYSQLI_ASSOC);
    } elseif ($reportType === 'transaction') {
        $result = $mysqli->query("SELECT * FROM transaction ORDER BY Date DESC");
        $data = $result->fetch_all(MYSQLI_ASSOC);
    } elseif ($reportType === 'users') {
        $result = $mysqli->query("SELECT * FROM users ORDER BY username ASC");
        $data = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        die("Invalid report type.");
    }
}
?>
 
   
<div class="container">
    <form method="POST" action="admin_db_greport.php" class="centered-form">
        <label for="report_type">Select Report Type:</label>
        <select name="report_type" id="report_type" required>
            <option value="">--Select--</option>
            <option value="notification" <?= $reportType === 'notification' ? 'selected' : '' ?>>Notifications</option>
            <option value="transaction" <?= $reportType === 'transaction' ? 'selected' : '' ?>>Transactions</option>
            <option value="users" <?= $reportType === 'users' ? 'selected' : '' ?>>Users</option>
        </select>
        <button type="submit" name="generate_report">Generate Report</button>
    </form>

    <?php if (!empty($data)): ?>
        <h3 class="text-center">Report for <?= htmlspecialchars($reportType) ?></h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <?php if ($reportType === 'notification'): ?>
                        <th>ID</th>
                        <th>Message</th>
                        <th>Created At</th>
                    <?php elseif ($reportType === 'transaction'): ?>
                        <th>ID</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>User ID</th>
                    <?php elseif ($reportType === 'users'): ?>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row): ?>
                    <tr>
                        <?php foreach ($row as $cell): ?>
                            <td><?= htmlspecialchars($cell) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<style>
.container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.table th, .table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}

.table th {
    background-color: #f2f2f2;
}

@media (max-width: 600px) {
    .table {
        font-size: 14px;
    }
}
</style>