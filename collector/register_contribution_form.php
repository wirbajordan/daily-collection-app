<?php
// Only start session and connect if not already done
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($conn) || !$conn->ping()) {
    $conn = new mysqli('localhost', 'root', '', 'dailycollect');
}
$successMsg = $errorMsg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_contribution'])) {
    $contributor_username = trim($_POST['contributor_username']);
    $amount = floatval($_POST['amount']);
    if ($contributor_username && $amount > 0) {
        $collector_id = $_SESSION['user_id'];
        $collector_username = $_SESSION['username'];
        $date = date('Y-m-d H:i:s');
        
        // Start transaction to ensure data consistency
        $conn->begin_transaction();
        
        try {
            // Insert the transaction
            $stmt = $conn->prepare("INSERT INTO transaction (amount, Date, user_id, username, transaction_type) VALUES (?, ?, ?, ?, 'register')");
            $stmt->bind_param("dsis", $amount, $date, $collector_id, $contributor_username);
            
            if ($stmt->execute()) {
                // Get contributor's user_id
                $contributorStmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? AND role = 'contributor'");
                $contributorStmt->bind_param("s", $contributor_username);
                $contributorStmt->execute();
                $contributorStmt->bind_result($contributor_user_id);
                $contributorStmt->fetch();
                $contributorStmt->close();
                
                if ($contributor_user_id) {
                    // Send notification to contributor
                    $notificationMessage = "Your contribution of " . number_format($amount, 2) . " CFA has been registered by collector " . htmlspecialchars($collector_username) . " on " . date('d M Y H:i');
                    $notifyStmt = $conn->prepare("INSERT INTO notification (user_id, message) VALUES (?, ?)");
                    $notifyStmt->bind_param("is", $contributor_user_id, $notificationMessage);
                    $notifyStmt->execute();
                    $notifyStmt->close();
                }
                
                $conn->commit();
                $successMsg = "Contribution registered successfully. Notification sent to contributor.";
            } else {
                throw new Exception("Transaction failed. Please try again.");
            }
            $stmt->close();
        } catch (Exception $e) {
            $conn->rollback();
            $errorMsg = $e->getMessage();
        }
    } else {
        $errorMsg = "Please select a contributor and enter a valid amount.";
    }
}
?>
<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="card-title" style="color:#174ea6;"><i class="fas fa-edit"></i> Register Contribution</h5>
        
        <?php if ($successMsg): ?>
            <div class="alert alert-success mt-3"><?php echo $successMsg; ?></div>
        <?php elseif ($errorMsg): ?>
            <div class="alert alert-danger mt-3"><?php echo $errorMsg; ?></div>
        <?php endif; ?>

        <form method="POST" action="collector_dashboard.php" id="registerContributionForm" autocomplete="off" class="mt-3">
            <div class="mb-3">
                <label for="contributor_username" class="form-label">Contributor Username</label>
                <select name="contributor_username" id="contributor_username" class="form-select" required>
                    <option value="">Select contributor...</option>
                    <?php
                    $contributors = $conn->query("SELECT username FROM users WHERE role='contributor'");
                    while ($row = $contributors->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($row['username']) . '">' . htmlspecialchars($row['username']) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="amount" class="form-label">Amount</label>
                <input type="number" name="amount" id="amount" class="form-control" required min="0.01" step="0.01" placeholder="Enter amount">
            </div>
            <button type="submit" name="register_contribution" class="btn btn-primary w-100">
                <i class="fas fa-check"></i> Validate Transaction
            </button>
        </form>
    </div>
</div>