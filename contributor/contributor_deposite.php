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

// Database connection parameters
$servername = "localhost"; // Change if necessary
$username = "root"; // Change to your DB username
$password = ""; // Change to your DB password
$dbname = "dailycollect"; // Change to your DB name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user details
$userId = $_SESSION['user_id'];
$userQuery = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
$userQuery->bind_param("i", $userId);
$userQuery->execute();
$userQuery->bind_result($userName);
$userQuery->fetch();
$userQuery->close();

// Initialize the success message flag
$successMessage = false;

// Handle form submission for deposits
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount'])) {
    // Get the form data
    $amount = $_POST['amount'];

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO transaction (user_id, username, amount, transaction_type) VALUES (?, ?, ?, 'deposite')");
    if ($stmt) {
        $stmt->bind_param("isd", $userId, $userName, $amount);

        // Execute the statement
        if ($stmt->execute()) {
            // Set success message flag and session variable
            $successMessage = true;
            $_SESSION['transaction_success'] = true; // Set session variable

            // Notify assigned collector
            $collectorId = null;
            $collectorStmt = $conn->prepare("SELECT collector_id FROM assignments WHERE contributor_id = ?");
            $collectorStmt->bind_param("i", $userId);
            $collectorStmt->execute();
            $collectorStmt->bind_result($collectorId);
            $collectorStmt->fetch();
            $collectorStmt->close();
            if (!$collectorId) {
                $collectorId = 1; // Default collector if not assigned
            }
            $notificationMsg = "Contributor $userName has deposited $amount CFA.";
            $notifyStmt = $conn->prepare("INSERT INTO notification (user_id, message) VALUES (?, ?)");
            $notifyStmt->bind_param("is", $collectorId, $notificationMsg);
            $notifyStmt->execute();
            $notifyStmt->close();
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close statement
        $stmt->close();
    } else {
        echo "Statement preparation failed: " . $conn->error;
    }
}

// Fetch all transactions for the user
$transactions = [];
$totalSum = 0;

$transactionQuery = $conn->prepare("SELECT amount, transaction_type, Date FROM transaction WHERE user_id = ?");
$transactionQuery->bind_param("i", $userId);
$transactionQuery->execute();
$transactionQuery->bind_result($amount, $transactionType, $date);

while ($transactionQuery->fetch()) {
    $transactions[] = ['amount' => $amount, 'transaction_type' => $transactionType, 'date' => $date];
    $totalSum += $amount; // Calculate total sum
}

$transactionQuery->close();
$conn->close();

// Check if the transaction was successful for display
if (isset($_SESSION['transaction_success']) && $_SESSION['transaction_success']) {
    $successMessage = true;
    unset($_SESSION['transaction_success']); // Clear the session variable
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make Deposit - Vision Finance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --main: #2563eb;
            --main-dark: #174ea6;
            --accent: #e74c3c;
            --background: #f4f6fa;
            --card-bg: #fff;
            --shadow: 0 6px 24px rgba(37,99,235,0.08);
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .main-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .dashboard-card {
            background: var(--card-bg);
            border-radius: 18px;
            box-shadow: var(--shadow);
            padding: 2.5rem 2rem 2rem 2rem;
            max-width: 480px;
            width: 100%;
            margin: 2rem auto;
        }
        .dashboard-card .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--main-dark);
            margin-bottom: 1.2rem;
            letter-spacing: 0.5px;
        }
        .balance-box {
            background: linear-gradient(90deg, var(--main) 60%, var(--main-dark) 100%);
            color: #fff;
            border-radius: 12px;
            padding: 1.5rem 1.2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 12px rgba(37,99,235,0.10);
            text-align: center;
        }
        .balance-box .balance-label {
            font-size: 1rem;
            opacity: 0.85;
        }
        .balance-box .balance-amount {
            font-size: 2.2rem;
            font-weight: 700;
            letter-spacing: 1px;
            margin-top: 0.2rem;
        }
        .balance-box .currency {
            font-size: 1.1rem;
            font-weight: 400;
            margin-right: 0.2rem;
        }
        .form-label {
            font-weight: 500;
            color: var(--main-dark);
        }
        .form-control {
            border-radius: 8px;
            padding: 0.85rem;
            border: 1px solid #e3e6ed;
            font-size: 1.05rem;
        }
        .form-control:focus {
            border-color: var(--main);
            box-shadow: 0 0 0 0.15rem rgba(37,99,235,0.10);
        }
        .currency-input {
            position: relative;
        }
        .currency-input input {
            padding-left: 3.2rem;
        }
        .currency-input .currency-symbol {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--main-dark);
            font-weight: 600;
            font-size: 1.1rem;
            opacity: 0.8;
        }
        .btn-main {
            background: linear-gradient(90deg, var(--main) 60%, var(--main-dark) 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.85rem 2.2rem;
            font-size: 1.1rem;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(37,99,235,0.10);
            transition: background 0.2s, box-shadow 0.2s;
        }
        .btn-main:hover, .btn-main:focus {
            background: var(--main-dark);
            color: #fff;
            box-shadow: 0 4px 16px rgba(37,99,235,0.13);
        }
        .success-message {
            background: #e6f9ed;
            color: #1a7f4c;
            border-left: 5px solid #1a7f4c;
            border-radius: 8px;
            padding: 1rem 1.2rem;
            margin-bottom: 1.2rem;
            font-size: 1.05rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .success-message i {
            margin-right: 0.7rem;
            font-size: 1.3rem;
        }
        .transactions-section {
            margin-top: 2.5rem;
        }
        .transactions-table {
            width: 100%;
            border-radius: 10px;
            overflow: hidden;
            background: #f9fafc;
            box-shadow: 0 1px 4px rgba(37,99,235,0.04);
        }
        .transactions-table th {
            background: var(--main-dark);
            color: #fff;
            font-weight: 500;
            padding: 0.95rem 0.7rem;
            border: none;
        }
        .transactions-table td {
            padding: 0.95rem 0.7rem;
            border-bottom: 1px solid #e3e6ed;
            font-size: 1.01rem;
        }
        .transactions-table tr:last-child td {
            border-bottom: none;
        }
        .transactions-table tr:hover {
            background: #f1f5fb;
        }
        .badge-success {
            background: #1a7f4c !important;
            color: #fff !important;
            font-size: 0.98em;
            padding: 0.45em 1em;
            border-radius: 6px;
        }
        @media (max-width: 600px) {
            .dashboard-card {
                padding: 1.2rem 0.5rem;
            }
            .transactions-section {
                margin-top: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="dashboard-card">
            <div class="balance-box mb-4">
                <div class="balance-label">Your Total Balance</div>
                <div class="balance-amount">
                    <span class="currency">CFA</span> <?php echo number_format($totalSum, 2); ?> 
                </div>
            </div>
            <?php if ($successMessage): ?>
                <div class="success-message" id="successMessage">
                    <span><i class="fas fa-check-circle"></i> Your contribution has been recorded successfully!</span>
                    <button class="btn btn-sm btn-outline-danger" onclick="dismissMessage()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>
            <div class="mb-4">
                <div class="section-title"><i class="fas fa-plus-circle"></i> Make a New Contribution</div>
                <form id="depositForm" method="POST" action="" style="margin-top: 2rem;">
                    <div class="mb-3">
                        <label for="userName" class="form-label">User Name</label>
                        <input type="text" class="form-control" id="userName" name="username" value="<?php echo htmlspecialchars($userName); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Contribution Amount</label>
                        <div class="currency-input">
                            <span class="currency-symbol">CFA</span>
                            <input type="number" class="form-control" id="amount" name="amount" required min="0" step="0.01" placeholder="Enter amount">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-main w-100 mt-2">
                        <i class="fas fa-check"></i> Submit Contribution
                    </button>
                </form>
            </div>
            <div class="transactions-section">
                <div class="section-title"><i class="fas fa-history"></i> Transaction History</div>
                <div class="table-responsive">
                    <table class="transactions-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td><?php echo date('d M Y H:i', strtotime($transaction['date'])); ?></td>
                                    <td>
                                        <span class="badge badge-success">
                                            <?php echo ucfirst(htmlspecialchars($transaction['transaction_type'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="currency">CFA</span> <?php echo number_format($transaction['amount'], 2); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function dismissMessage() {
            document.getElementById('successMessage').style.display = 'none';
        }
        document.getElementById('depositForm').addEventListener('submit', function(event) {
            const amount = document.getElementById('amount').value;
            if (amount <= 0) {
                event.preventDefault();
                alert('Please enter a valid amount greater than 0');
            }
        });
    </script>
</body>
</html>