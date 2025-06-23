<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../config/config.php';

// Handle report generation
$data = [];
$reportType = $_GET['report_type'] ?? 'users';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

function getCount($mysqli, $table) {
    $result = $mysqli->query("SELECT COUNT(*) as cnt FROM $table");
    $row = $result->fetch_assoc();
    return $row['cnt'];
}

$totalUsers = getCount($mysqli, 'users');
$totalTransactions = getCount($mysqli, 'transaction');
$totalNotifications = getCount($mysqli, 'notification');

$where = '';
if ($reportType === 'transaction' && $dateFrom && $dateTo) {
    $where = "WHERE Date >= '" . $mysqli->real_escape_string($dateFrom) . "' AND Date <= '" . $mysqli->real_escape_string($dateTo) . "'";
} elseif ($reportType === 'notification' && $dateFrom && $dateTo) {
    $where = "WHERE created_at >= '" . $mysqli->real_escape_string($dateFrom) . "' AND created_at <= '" . $mysqli->real_escape_string($dateTo) . "'";
}

if ($reportType === 'notification') {
    $result = $mysqli->query("SELECT * FROM notification $where ORDER BY created_at DESC");
    $data = $result->fetch_all(MYSQLI_ASSOC);
} elseif ($reportType === 'transaction') {
    $result = $mysqli->query("SELECT * FROM transaction $where ORDER BY Date DESC");
    $data = $result->fetch_all(MYSQLI_ASSOC);
} elseif ($reportType === 'users') {
    $result = $mysqli->query("SELECT * FROM users ORDER BY username ASC");
    $data = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background:#f4f6fa;
        }
        .container {
            max-width: 1200px;
            min-height: 90vh;
            margin: 40px auto 40px auto;
            padding: 40px 32px 40px 32px;
            background: #f4f6fa;
        }
        .summary-card {
            border-radius: 1rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            padding: 2.2rem 1.2rem;
            text-align: center;
            background: #fff;
            min-height: 170px;
        }
        .summary-icon {
            font-size: 2.7rem;
            margin-bottom: 0.7rem;
        }
        .tab-content {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            padding: 2.5rem 1.5rem;
            min-height: 500px;
        }
        .filter-row {
            margin-bottom: 2rem;
        }
        .table-responsive {
            margin-top: 2rem;
        }
        .export-btn {
            float: right;
        }
        #searchInput {
            max-width: 350px;
            margin-bottom: 1.2rem;
        }
        #reportSection { display: none; }
        .close-report-btn {
            float: right;
            margin-bottom: 10px;
        }
        .show-report-btn {
            margin-bottom: 20px;
        }
        @media (max-width: 900px) {
            .container { max-width: 98vw; padding: 10px 2vw; }
            .tab-content { padding: 1.2rem 0.2rem; }
        }
        @media (max-width: 600px) {
            .summary-card { font-size: 0.95rem; min-height: 120px; }
            .tab-content { padding: 0.7rem 0.1rem; min-height: 300px; }
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center mb-4"><i class="fas fa-chart-bar"></i> Admin Reports & Analytics</h2>
    <div class="row mb-4 g-3 justify-content-center">
        <div class="col-md-4">
            <div class="summary-card">
                <div class="summary-icon text-primary"><i class="fas fa-users"></i></div>
                <div class="fw-bold">Total Users</div>
                <div class="fs-4"><?= $totalUsers ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-card">
                <div class="summary-icon text-success"><i class="fas fa-exchange-alt"></i></div>
                <div class="fw-bold">Total Transactions</div>
                <div class="fs-4"><?= $totalTransactions ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-card">
                <div class="summary-icon text-warning"><i class="fas fa-bell"></i></div>
                <div class="fw-bold">Total Notifications</div>
                <div class="fs-4"><?= $totalNotifications ?></div>
            </div>
        </div>
    </div>
    <ul class="nav nav-tabs mb-3" id="reportTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link <?= $reportType==='users'?'active':'' ?>" href="manage-reports.php?report_type=users" onclick="return hideReportSection();">Users</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link <?= $reportType==='transaction'?'active':'' ?>" href="manage-reports.php?report_type=transaction" onclick="return hideReportSection();">Transactions</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link <?= $reportType==='notification'?'active':'' ?>" href="manage-reports.php?report_type=notification" onclick="return hideReportSection();">Notifications</a>
        </li>
    </ul>
    <div class="tab-content">
        <div class="filter-row row align-items-end">
            <?php if ($reportType === 'transaction' || $reportType === 'notification'): ?>
                <form class="col-md-8 row g-2" method="get" action="">
                    <input type="hidden" name="report_type" value="<?= htmlspecialchars($reportType) ?>">
                    <div class="col-auto">
                        <label for="date_from" class="form-label mb-0">From</label>
                        <input type="date" class="form-control" name="date_from" id="date_from" value="<?= htmlspecialchars($dateFrom) ?>">
                    </div>
                    <div class="col-auto">
                        <label for="date_to" class="form-label mb-0">To</label>
                        <input type="date" class="form-control" name="date_to" id="date_to" value="<?= htmlspecialchars($dateTo) ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-outline-primary">Filter</button>
                    </div>
                </form>
            <?php endif; ?>
            <div class="col text-end">
                <a href="admin_db_greport.php?report_type=<?= htmlspecialchars($reportType) ?>&date_from=<?= htmlspecialchars($dateFrom) ?>&date_to=<?= htmlspecialchars($dateTo) ?>" class="btn btn-success export-btn"><i class="fas fa-file-csv"></i> Export CSV</a>
            </div>
        </div>
        <div class="text-center mb-3 show-report-btn">
            <button class="btn btn-primary px-4 py-2" id="showReportBtn"><i class="fas fa-table"></i> Show Report</button>
        </div>
        <div id="reportSection">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div></div>
                <button class="btn btn-outline-danger close-report-btn" id="closeReportBtn"><i class="fas fa-times"></i> Close Report</button>
            </div>
            <div class="table-responsive">
                <input class="form-control mb-2" id="searchInput" type="text" placeholder="Search in table...">
                <table class="table table-bordered table-hover align-middle" id="reportTable">
                    <thead class="table-light">
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
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Table search
    $(document).ready(function(){
        $('#searchInput').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#reportTable tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
        $('#showReportBtn').on('click', function(){
            $('#reportSection').fadeIn();
            $('html, body').animate({
                scrollTop: $('#reportSection').offset().top - 80
            }, 400);
        });
        $('#closeReportBtn').on('click', function(){
            $('#reportSection').fadeOut();
            $('html, body').animate({
                scrollTop: $('.show-report-btn').offset().top - 80
            }, 400);
        });
    });
    function hideReportSection() {
        $('#reportSection').hide();
        return true;
    }
</script>
</body>
</html> 