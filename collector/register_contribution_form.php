<?php
// Only start session and connect if not already done
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($conn) || !$conn->ping()) {
    $conn = new mysqli('localhost', 'root', '', 'dailycollect');
}
$successMsg = $errorMsg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_contribution_final'])) {
    $contributor_username = trim($_POST['contributor_username']);
    $amount = floatval($_POST['amount']);
    $signature = $_POST['signature'] ?? '';
    if ($contributor_username && $amount > 0 && $signature) {
        $collector_id = $_SESSION['user_id'];
        $collector_username = $_SESSION['username'];
        $date = date('Y-m-d H:i:s');
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO transaction (amount, Date, user_id, username, transaction_type, signature) VALUES (?, ?, ?, ?, 'register', ?)");
            $stmt->bind_param("dsiss", $amount, $date, $collector_id, $contributor_username, $signature);
            if ($stmt->execute()) {
                $contributorStmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? AND role = 'contributor'");
                $contributorStmt->bind_param("s", $contributor_username);
                $contributorStmt->execute();
                $contributorStmt->bind_result($contributor_user_id);
                $contributorStmt->fetch();
                $contributorStmt->close();
                if ($contributor_user_id) {
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
        $errorMsg = "Please complete all fields and provide a signature.";
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
            <div class="mb-3">
                <label for="collector_password" class="form-label">Collector Password</label>
                <input type="password" name="collector_password" id="collector_password" class="form-control" required placeholder="Enter your password" autocomplete="current-password">
                <div id="passwordError" class="text-danger mt-1" style="display:none;"></div>
            </div>
            <div id="signatureSection" style="display:none;">
                <label class="form-label">Contributor Signature</label>
                <div class="border rounded mb-2" style="background:#fff; width:100%; height:180px;">
                    <canvas id="signaturePad" width="400" height="180" style="touch-action: none;"></canvas>
                </div>
                <button type="button" class="btn btn-secondary btn-sm mb-2" id="clearSignature">Clear Signature</button>
                <input type="hidden" name="signature" id="signatureInput">
            </div>
            <button type="button" id="validatePasswordBtn" class="btn btn-primary w-100 mb-2">
                <i class="fas fa-key"></i> Validate Password
            </button>
            <button type="submit" name="register_contribution_final" id="validateTransactionBtn" class="btn btn-success w-100" style="display:none;">
                <i class="fas fa-check"></i> Validate Transaction
            </button>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.6/dist/signature_pad.umd.min.js"></script>
<script>
const passwordInput = document.getElementById('collector_password');
const validatePasswordBtn = document.getElementById('validatePasswordBtn');
const passwordError = document.getElementById('passwordError');
const signatureSection = document.getElementById('signatureSection');
const validateTransactionBtn = document.getElementById('validateTransactionBtn');
const signaturePadCanvas = document.getElementById('signaturePad');
const signatureInput = document.getElementById('signatureInput');
let signaturePad;

validatePasswordBtn.addEventListener('click', function(e) {
    e.preventDefault();
    passwordError.style.display = 'none';
    signatureSection.style.display = 'none';
    validateTransactionBtn.style.display = 'none';
    // AJAX to validate password
    fetch('validate_collector_password.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ password: passwordInput.value })
    })
    .then(res => res.text())
    .then(text => {
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            passwordError.textContent = 'Server error: ' + text;
            passwordError.style.display = 'block';
            return;
        }
        if (data.valid) {
            signatureSection.style.display = 'block';
            validateTransactionBtn.style.display = 'block';
            if (!signaturePad) {
                signaturePad = new SignaturePad(signaturePadCanvas);
            } else {
                signaturePad.clear();
            }
        } else {
            passwordError.textContent = data.error ? data.error : 'Invalid password. Please try again.';
            passwordError.style.display = 'block';
        }
    });
});
document.getElementById('clearSignature').addEventListener('click', function() {
    if (signaturePad) signaturePad.clear();
});
document.getElementById('registerContributionForm').addEventListener('submit', function(e) {
    if (signatureSection.style.display === 'block') {
        if (signaturePad.isEmpty()) {
            e.preventDefault();
            alert('Please provide a signature.');
            return false;
        }
        signatureInput.value = signaturePad.toDataURL();
    }
});
</script>