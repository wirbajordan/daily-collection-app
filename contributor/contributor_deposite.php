<?php
include_once '../config/config.php'; // Assumes $mysqli is provided

// Handle dismiss action for success message
if (isset($_GET['dismiss_success'])) {
    unset($_SESSION['transaction_success']);
    // Redirect to remove the dismiss_success param from URL
    $redirectUrl = "contributor.php?page=" . base64_encode('contributor_deposite');
    if (headers_sent()) {
        echo "<script>window.location.href='" . $redirectUrl . "';</script>";
        exit();
    } else {
        header("Location: $redirectUrl");
        exit();
    }
}

// Fetch user details
$userId = $_SESSION['user_id'];
$userQuery = $mysqli->prepare("SELECT username FROM users WHERE user_id = ?");
$userQuery->bind_param("i", $userId);
$userQuery->execute();
$userQuery->bind_result($userName);
$userQuery->fetch();
$userQuery->close();

// Initialize the success message flag
$successMessage = false;

// Handle form submission for deposits
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount'])) {
    $amount = $_POST['amount'];
    $stmt = $mysqli->prepare("INSERT INTO transaction (user_id, username, amount, transaction_type) VALUES (?, ?, ?, 'deposite')");
    if ($stmt) {
        $stmt->bind_param("isd", $userId, $userName, $amount);
        if ($stmt->execute()) {
            $_SESSION['transaction_success'] = true;
            // Notify assigned collector
            $collectorId = null;
            $collectorStmt = $mysqli->prepare("SELECT collector_id FROM assignments WHERE contributor_id = ?");
            $collectorStmt->bind_param("i", $userId);
            $collectorStmt->execute();
            $collectorStmt->bind_result($collectorId);
            $collectorStmt->fetch();
            $collectorStmt->close();
            if (!$collectorId) {
                $collectorId = 1; // Default collector if not assigned
            }
            $notificationMsg = "Contributor $userName has deposited $amount CFA.";
            $notifyStmt = $mysqli->prepare("INSERT INTO notification (user_id, message) VALUES (?, ?)");
            $notifyStmt->bind_param("is", $collectorId, $notificationMsg);
            $notifyStmt->execute();
            $notifyStmt->close();
            // Redirect to avoid form resubmission and show success message
            $redirectUrl = "contributor.php?page=" . base64_encode('contributor_deposite');
            if (headers_sent()) {
                echo "<script>window.location.href='" . $redirectUrl . "';</script>";
                exit();
            } else {
                header("Location: $redirectUrl");
                exit();
            }
        } else {
            echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
        }
        $stmt->close();
    } else {
        echo "<div class='alert alert-danger'>Statement preparation failed: " . $mysqli->error . "</div>";
    }
}

// Fetch all transactions for the user
$transactions = [];
$totalSum = 0;
$transactionQuery = $mysqli->prepare("SELECT amount, transaction_type, Date FROM transaction WHERE user_id = ?");
$transactionQuery->bind_param("i", $userId);
$transactionQuery->execute();
$transactionQuery->bind_result($amount, $transactionType, $date);
while ($transactionQuery->fetch()) {
    $transactions[] = ['amount' => $amount, 'transaction_type' => $transactionType, 'date' => $date];
    $totalSum += $amount;
}
$transactionQuery->close();

// Check if the transaction was successful for display
if (isset($_SESSION['transaction_success']) && $_SESSION['transaction_success']) {
    $successMessage = true;
}
?>

<!-- Deposit Form -->
<div class="container" style="margin-top: 3rem;">
    <div class="text-center mb-2 fw-bold" style="color: #174ea6;">Welcome, <?php echo htmlspecialchars($userName); ?>!</div>
    <div class="card shadow-sm deposit-form-container" style="max-width: 350px; margin: 0 auto;">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Make a New Contribution</h5>
        </div>
        <div class="card-body">
            <?php if ($successMessage): ?>
                <div class="alert alert-success" style="position: relative;">
                    <i class="fas fa-check-circle"></i> Your contribution has been recorded successfully!
                    <a href="?dismiss_success=1" style="position: absolute; right: 10px; top: 10px; color: #155724; text-decoration: none; font-weight: bold; font-size: 1.5rem;">&times;</a>
                </div>
            <?php endif; ?>
            <form id="depositForm" method="POST" action="">
                <div class="mb-3">
                    <label for="userName" class="form-label">User Name</label>
                    <input type="text" class="form-control" id="userName" name="username" value="<?php echo htmlspecialchars($userName); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label for="amount" class="form-label">Contribution Amount (CFA)</label>
                    <input type="number" class="form-control" id="amount" name="amount" required min="0" step="0.01" placeholder="Enter amount">
                </div>
                <button type="submit" class="btn btn-success w-100">
                    <i class="fas fa-check"></i> Submit Contribution
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Transaction History Table with Toggle Button -->
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-2" style="max-width: 600px; margin: 0 auto;">
        <h5 class="mb-0">Transaction History</h5>
        <button class="btn btn-secondary btn-sm" id="toggleTransactionHistory">Show/Hide</button>
    </div>
    <div class="card shadow-sm transaction-history-container" style="max-width: 600px; margin: 0 auto; display: block;" id="transactionHistoryCard">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="fas fa-history"></i> Transaction History</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
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
                                    <span class="badge bg-success">
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

<script>
document.getElementById('depositForm').addEventListener('submit', function(event) {
    const amount = document.getElementById('amount').value;
    if (amount <= 0) {
        event.preventDefault();
        alert('Please enter a valid amount greater than 0');
    }
});

document.getElementById('toggleTransactionHistory').addEventListener('click', function() {
    var card = document.getElementById('transactionHistoryCard');
    if (card.style.display === 'none') {
        card.style.display = 'block';
    } else {
        card.style.display = 'none';
    }
});
</script>

</rewritten_file>