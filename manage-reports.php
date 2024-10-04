<h2 class="text-center"><span class="nam">Manage Reports</span></h2>
<?php
//session_start();
include_once '../config/config.php';


// Check user role
if ($_SESSION['role'] != 'administrator') {
    header('Location: .../login.php');//redirect to logged in if role not valide
    exit();
}


// Check if user is logged in    
if (!isset($_SESSION['user_id'])) {
    header('Location: .../login.php'); // Redirect to login if not logged in
    exit();
}    

// Handle report generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_report'])) {
    $reportType = $_POST['report_type'];

    if ($reportType === 'notification') {
        $result = $mysqli->query("SELECT * FROM notification ORDER BY created_at DESC");
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $filename = "notifications_report_" . date('Y-m-d') . ".csv";
    } elseif ($reportType === 'transaction') {
        $result = $mysqli->query("SELECT * FROM transaction ORDER BY Date DESC");
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $filename = "transactions_report_" . date('Y-m-d') . ".csv";
   } elseif ($reportType === 'users') {
        $result = $mysqli->query("SELECT * FROM users ORDER BY username ASC");
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $filename = "users_report_" . date('Y-m-d') . ".csv";
    } else {
        die("Invalid report type.");
    }

    // Generate CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // Output header based on report type
    if ($reportType === 'notification') {
        fputcsv($output, ['ID', 'Message', 'Created At']);
    } elseif ($reportType === 'transaction') {
        fputcsv($output, ['ID', 'Amount', 'Date', 'User ID']);
    } elseif ($reportType === 'users') {
        fputcsv($output, ['User ID', 'Username', 'Email', 'Role']);
    }

    // Output data
    foreach ($data as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit();
}
?>


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
       